<?php
namespace phpbb\oberon\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ai_validation extends Command
{
    const OPENAI_API_KEY = '';

    /* @var \phpbb\db\driver\driver_interface $db */
    protected $db;

	/* @var \phpbb\oberon\manager\manager $manager */
	protected $manager;

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\oberon\manager\manager $manager)
    {        
        $this->db = $db;
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        // Run the command with php bin/phpbbcli.php custdb:ai_validation
        $this
            ->setName('custdb:ai_validation')
            ->setDescription('TEST')
            ->setHelp('TEST')
            ->addArgument(
                'queue_id', // Argument name
                InputArgument::REQUIRED, // Required argument
                'The ID of the queue item to process'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Retrieve argument
        $queue_id = (int) $input->getArgument('queue_id');

        $output->writeln('<info>START RUNNING THE AI VALIDATOR</info>');

        // Process
        $this->process_queue($queue_id, $output);

        $output->writeln('<info>FINISHED RUNNING THE AI VALIDATOR</info>');
        return Command::SUCCESS;
    }

    /**
     * Main processing logic
     */
    private function process_queue($queue_id, OutputInterface $output)
    {
        // Retrieve revision record from database
        $result = $this->manager->find_contribution_revision_for_queue_id($queue_id);

        // Get full path to uploaded ZIP
        $zip_path = $this->manager::FILE_UPLOAD_LOCATION . $result['revision']['revision_attachment'];

        $output->writeln("<info>Extracting ZIP: $zip_path</info>");

        // 1. Extract ZIP
        $extract_dir = sys_get_temp_dir() . '/phpbb_ai_validation_' . uniqid();
        if (!is_dir($extract_dir)) {
            mkdir($extract_dir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zip_path) === TRUE) {
            $zip->extractTo($extract_dir);
            $zip->close();
            $output->writeln("<info>Extraction complete: $extract_dir</info>");
        } else {
            throw new \RuntimeException("Failed to open ZIP file: $zip_path");
        }

        // 2. Upload all files to OpenAI
        $uploaded_files = $this->upload_files_to_openai($extract_dir, $output);

        // 3. Create & upload manifest.json
        $manifest_id = $this->create_and_upload_manifest($uploaded_files, $output);

        // 4. Create a vector store and attach files
        $vector_store_id = $this->create_vector_store($uploaded_files, $manifest_id, $output);

        // 5. Create an Assistant configured as a phpBB extension validator
        $assistant_id = $this->create_assistant($vector_store_id, $output);

        // 6. Prompt OpenAI to read and analyze the uploaded files
        $this->run_validation($assistant_id, $output);
    }

    /**
     * Recursively upload extracted files to OpenAI
     */
    private function upload_files_to_openai($root_dir, OutputInterface $output): array
    {
        $uploaded_files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root_dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        // Define allowed text-based extensions we will convert and upload
        $text_extensions = [
            'php','html','js','css','json','xml','txt','md','yml','yaml','csv',
            'py','rb','ts','c','cpp','java','go','tex'
        ];

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $rel_path = str_replace($root_dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $extension = strtolower(pathinfo($rel_path, PATHINFO_EXTENSION));
            $size = filesize($file->getPathname());

            // Skip zero-byte files, but record in manifest
            if ($size === 0) {
                $output->writeln("<comment>Skipping empty file: $rel_path</comment>");
                $uploaded_files[$rel_path] = [
                    'file_id' => null,
                    'original_extension' => $extension,
                    'size' => 0,
                    'status' => 'empty'
                ];
                continue;
            }

            // Skip unsupported/binary file types
            if (!in_array($extension, $text_extensions)) {
                $output->writeln("<comment>Skipping non-text/binary file: $rel_path ($extension)</comment>");
                $uploaded_files[$rel_path] = [
                    'file_id' => null,
                    'original_extension' => $extension,
                    'size' => $size,
                    'status' => 'binary_skipped'
                ];
                continue;
            }

            // Convert text-based files to a temporary .txt for upload
            $temp_path = sys_get_temp_dir() . '/' . uniqid('txt_', true) . '.txt';
            copy($file->getPathname(), $temp_path);

            $output->writeln("<info>Uploading as text: $rel_path ($size bytes) → $temp_path</info>");
            $file_id = $this->upload_single_file($temp_path, 'assistants');

            // Record original type and uploaded file_id
            $uploaded_files[$rel_path] = [
                'file_id' => $file_id,
                'original_extension' => $extension,
                'size' => $size,
                'status' => 'uploaded_as_text'
            ];
        }

        return $uploaded_files;
    }

    /**
     * Upload a single file to OpenAI Files API
     */
    private function upload_single_file(string $path, string $purpose): string
    {
        $ch = curl_init("https://api.openai.com/v1/files");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . self::OPENAI_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "purpose" => $purpose,
            "file" => new \CURLFile($path)
        ]);
      
        $response = curl_exec($ch);
        if ($response === false) {
            throw new \RuntimeException("cURL error: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['id'])) {
            throw new \RuntimeException("Upload failed: $response");
        }

        return $data['id'];
    }

    /**
     * Create and upload manifest.json
     */
    private function create_and_upload_manifest(array $uploaded_files, OutputInterface $output): string
    {
        $manifest = [
            'description' => 'Folder structure and mapping of uploaded files',
            'generated_at' => date('c'),
            'files' => $uploaded_files
        ];

        $manifest_path = sys_get_temp_dir() . '/manifest_' . uniqid() . '.json';
        file_put_contents($manifest_path, json_encode($manifest, JSON_PRETTY_PRINT));

        $output->writeln("Uploading manifest.json");

        return $this->upload_single_file($manifest_path, 'assistants');
    } 

    /**
     * Create a vector store for larger projects
     */
    private function create_vector_store(array $uploaded_files, string $manifest_id, OutputInterface $output): string
    {
        $output->writeln("Creating vector store...");

        $ch = curl_init("https://api.openai.com/v1/vector_stores");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . self::OPENAI_API_KEY,
            "Content-Type: application/json"
        ]);

        $body = [
            'name' => 'phpBB Extension Validation',
            'file_ids' => array_values($uploaded_files),
        ];

        // Include manifest
        $body['file_ids'][] = $manifest_id;

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['id'])) {
            throw new \RuntimeException("Vector store creation failed: $response");
        }

        $vector_store_id = $data['id'];
        $output->writeln("Vector store created: $vector_store_id");

        return $vector_store_id;
    }

    /**
     * Create an Assistant with the vector store attached
     */
    private function create_assistant(string $vector_store_id, OutputInterface $output): string
    {
        $output->writeln("Creating assistant...");

        $ch = curl_init("https://api.openai.com/v1/assistants");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . self::OPENAI_API_KEY,
            "Content-Type: application/json"
        ]);

        $body = [
            'name' => 'phpBB Extension Validator',
            'instructions' => 'You are a phpBB extension validator. Your job is to check the uploaded ZIP file for compliance with phpBB’s validation policies and coding guidelines.',
            'model' => 'gpt-4.1',
            'tools' => [
                ['type' => 'file_search']
            ],
            'vector_store_ids' => [$vector_store_id]
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['id'])) {
            throw new \RuntimeException("Assistant creation failed: $response");
        }

        $assistant_id = $data['id'];
        $output->writeln("Assistant created: $assistant_id");

        return $assistant_id;
    }

    /**
     * Run a validation job by prompting the Assistant
     */
    private function run_validation(string $assistant_id, OutputInterface $output)
    {
        $output->writeln("Starting validation run...");

        $ch = curl_init("https://api.openai.com/v1/threads/runs");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . self::OPENAI_API_KEY,
            "Content-Type: application/json"
        ]);

        $body = [
            'assistant_id' => $assistant_id,
            'thread' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Please read all the files in the vector store and check this extension for compliance with phpBB validation policies and coding guidelines. Provide a detailed report of any issues found.'
                    ]
                ]
            ]
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $output->writeln("Validation run started. Response:\n" . print_r($data, true));
    }
}
