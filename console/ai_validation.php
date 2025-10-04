<?php
namespace phpbb\oberon\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ai_validation extends Command
{
    /* @var \phpbb\db\driver\driver_interface $db */
    protected $db;

	/* @var \phpbb\oberon\manager\manager $manager */
	protected $manager;

    /* int $contribution_type */
    protected $contribution_type;

    private function get_openai_api_secret()
    {
        // TODO: this may need to change later, but for now it means we can use GitHub secrets
        return getenv('PHPBB_CUSTDB_TEST_CHATGPT_API_AI_KEY');
    }

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\oberon\manager\manager $manager)
    {        
        $this->db = $db;
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        // Run the command with: e.g., php bin/phpbbcli.php custdb:ai_validation 2 (the number has to be an id from phpbb_custdb_queue)
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
        // --- PRE-CLEANUP: Delete all files from OpenAI before starting ---
        $output->writeln("<info>Deleting ALL files from OpenAI before starting...</info>");
        $this->delete_all_openai_files($output);

        // Retrieve revision record from database
        $result = $this->manager->find_contribution_revision_for_queue_id($queue_id);
        $this->contribution_type = $result['contribution']['contribution_type'];

        // Get full path to uploaded ZIP
        $zip_path = $this->manager::FILE_UPLOAD_LOCATION . $result['revision']['revision_attachment'];

        $output->writeln("<info>Extracting ZIP: $zip_path</info>");

        // Extract ZIP
        $extract_dir = sys_get_temp_dir() . '/phpbb_ai_validation_' . uniqid();
        
        if (!is_dir($extract_dir)) 
        {
            mkdir($extract_dir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zip_path) === TRUE) 
        {
            $zip->extractTo($extract_dir);
            $zip->close();
            $output->writeln("<info>Extraction complete: $extract_dir</info>");
        } 
        
        else 
        {
            throw new \RuntimeException("Failed to open ZIP file: $zip_path");
        }

        // Upload all files to OpenAI
        $uploaded_files = $this->upload_files_to_openai($extract_dir, $output);

        // Create and upload manifest.json
        $manifest_id = $this->create_and_upload_manifest($uploaded_files, $output);

        // Create a vector store and attach files
        $vector_store_id = $this->create_vector_store($uploaded_files, $manifest_id, $output);

        // Create an Assistant configured as a phpBB customisation validator
        $assistant_id = $this->create_assistant($vector_store_id, $output);

        // Prompt OpenAI to read and analyze the uploaded files
        $this->run_validation($assistant_id, $output);

        // --- CLEANUP: Remove temp extraction directory ---
        $output->writeln("<info>Cleaning up extraction directory: $extract_dir</info>");
        $this->delete_directory_recursive($extract_dir);

        // --- CLEANUP: Delete uploaded files from OpenAI ---
        $output->writeln("<info>Deleting uploaded files from OpenAI...</info>");
        foreach ($uploaded_files as $rel_path => $info) {
            if (!empty($info['file_id'])) {
                $this->delete_openai_file($info['file_id'], $output);
            }
        }
        // Also delete manifest file
        if (!empty($manifest_id)) {
            $this->delete_openai_file($manifest_id, $output);
        }
    }

    /**
     * Recursively delete a directory and its contents
     */
    private function delete_directory_recursive($dir)
    {
        if (!is_dir($dir)) return;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($dir);
    }

    /**
     * Delete a file from OpenAI by file_id
     */
    private function delete_openai_file($file_id, OutputInterface $output)
    {
        $ch = curl_init("https://api.openai.com/v1/files/" . urlencode($file_id));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->get_openai_api_secret()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        //$output->writeln("  Deleted file $file_id: $response");
    }

    /**
     * Delete all files from OpenAI account
     */
    private function delete_all_openai_files(OutputInterface $output)
    {
        $ch = curl_init("https://api.openai.com/v1/files");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->get_openai_api_secret()
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        if (!empty($data['data'])) {
            foreach ($data['data'] as $file) {
                if (!empty($file['id'])) {
                    $this->delete_openai_file($file['id'], $output);
                }
            }
        }
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

        // Define allowed text-based file extensions we will convert and upload
        $text_extensions = [
            'php', 'html', 'js', 'css', 'json', 'xml', 'txt', 'md', 'yml', 'yaml', 'csv',
            'py', 'rb', 'ts', 'c', 'cpp', 'java', 'go', 'tex'
        ];

        foreach ($iterator as $file) 
        {
            if (!$file->isFile()) 
            {
                continue;
            }

            $rel_path = str_replace($root_dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $extension = strtolower(pathinfo($rel_path, PATHINFO_EXTENSION));
            $size = filesize($file->getPathname());

            // Skip zero-byte files, but record in manifest
            if ($size === 0) 
            {
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
            if (!in_array($extension, $text_extensions)) 
            {
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
            "Authorization: Bearer " . $this->get_openai_api_secret()
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "purpose" => $purpose,
            "file" => new \CURLFile($path)
        ]);
      
        $response = curl_exec($ch);
        if ($response === false) 
        {
            throw new \RuntimeException("cURL error: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['id'])) 
        {
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
            "Authorization: Bearer " . $this->get_openai_api_secret(),
            "Content-Type: application/json"
        ]);

        // Extract only file_id strings, skip nulls
        $file_ids = [];
        foreach ($uploaded_files as $file_info) 
        {
            if (!empty($file_info['file_id'])) 
            {
                $file_ids[] = $file_info['file_id'];
            }
        }
        // Add manifest file id
        $file_ids[] = $manifest_id;

        $body = [
            'name' => 'phpBB Customisation Validation',
            'file_ids' => $file_ids,
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['id'])) 
        {
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
            "Authorization: Bearer " . $this->get_openai_api_secret(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        $body = [
            'name' => 'phpBB Customisation Validator',
            'instructions' => $this->get_ai_prompt('system'),
            'model' => 'gpt-4.1',
            'tools' => [
                ['type' => 'file_search']
            ],
            // This is very important, we have to supply the vector store so OpenAI knows what files to process
            'tool_resources' => [
                'file_search' => [
                    'vector_store_ids' => [$vector_store_id]
                ]
            ]
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['id'])) 
        {
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

        // Create a thread with the initial validation request
        $threadData = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->get_ai_prompt('user'), // this is the user prompt
                ]
            ]
        ];

        $thread_id = $this->create_thread($threadData, $output);

        // Start the run
        $run_id = $this->start_run($assistant_id, $thread_id, $output);

        // Wait until the run is complete
        $this->wait_for_run_completion($thread_id, $run_id, $output);

        // Fetch the final report
        $final_report = $this->fetch_final_messages($thread_id, $output);

        $output->writeln("<info>Final Report Retrieved:</info>");
        $output->writeln($final_report);

        // Now, send this report to a topic in the private validation forum
        $array_report = json_decode($final_report, true);

        /* INTERNAL STATUS CHANGE */
        // TODO: potential error here... PHP Warning:  Trying to access array offset on null in /workspaces/phpbb/phpBB/ext/phpbb/oberon/console/ai_validation.php on line 446
        $this->manager->new_topic(2, '[Validation Report] ' . $array_report['name'], sprintf("Outcome: %s, Confidence: %s - ", $array_report['outcome'], $array_report['confidence']) . 'Report: ' . $array_report['report']);
    }

    private function create_thread(array $threadData, OutputInterface $output): string
    {
        $ch = curl_init("https://api.openai.com/v1/threads");
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->get_openai_api_secret(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($threadData));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (empty($data['id'])) 
        {
            throw new \RuntimeException("Failed to create thread: $response");
        }

        $output->writeln("Thread created: {$data['id']}");
        return $data['id'];
    }

    private function start_run(string $assistant_id, string $thread_id, OutputInterface $output): string
    {
        $ch = curl_init("https://api.openai.com/v1/threads/$thread_id/runs");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->get_openai_api_secret(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        $body = ['assistant_id' => $assistant_id];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (empty($data['id'])) 
        {
            throw new \RuntimeException("Failed to start run: $response");
        }

        $output->writeln("Run started: {$data['id']}");
        return $data['id'];
    }

    private function wait_for_run_completion(string $thread_id, string $run_id, OutputInterface $output)
    {
        $status = 'in_progress';
        $pollUrl = "https://api.openai.com/v1/threads/$thread_id/runs/$run_id";

        while ($status === 'in_progress' || $status === 'queued') 
        {
            sleep(2); // Poll every 2 seconds because it takes some time for OpenAI to index

            $ch = curl_init($pollUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $this->get_openai_api_secret(),
                "OpenAI-Beta: assistants=v2"
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);

            if (isset($data['status'])) 
            {
                $status = $data['status'];
                $output->writeln("Run status: $status");
            } 
            
            else 
            {
                throw new \RuntimeException("Unable to check run status: $response");
            }
        }

        if ($status !== 'completed') 
        {
            throw new \RuntimeException("Run ended unexpectedly with status: $status");
        }

        $output->writeln("<info>Run completed successfully!</info>");
    }

    private function fetch_final_messages(string $thread_id, OutputInterface $output): string
    {
        $ch = curl_init("https://api.openai.com/v1/threads/$thread_id/messages");
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->get_openai_api_secret(),
            "OpenAI-Beta: assistants=v2"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (empty($data['data'])) 
        {
            throw new \RuntimeException("No messages found: $response");
        }

        $report = '';
        foreach ($data['data'] as $message) 
        {
            if ($message['role'] === 'assistant' && isset($message['content'][0]['text']['value'])) 
            {
                $report .= $message['content'][0]['text']['value'] . "\n\n";
            }
        }

        return trim($report);
    }

    /**
     * $prompt_type can be "system" or "user"
     * Pulls from text files in this folder
     */
    private function get_ai_prompt(string $prompt_type)
    {
        switch ($this->contribution_type)
        {
            case $this->manager::TYPE_EXTENSIONS:
                return ($prompt_type === "system") ? file_get_contents('./ext/phpbb/oberon/console/system.prompt.txt') : file_get_contents('./ext/phpbb/oberon/console/user.prompt.txt');
                break;
            case $this->manager::TYPE_STYLES:
                // TODO
                break;
            case $this->manager::TYPE_TRANSLATIONS:
                // TODO
                break;
        }
    }

}
