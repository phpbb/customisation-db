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
        $this->process($queue_id);

        $output->writeln('<info>FINISHED RUNNING THE AI VALIDATOR</info>');
        return Command::SUCCESS;
    }

    private function process($queue_id)
    {
        // Retrieve queue, contribution, revision records from database and loop (and batch?)
        $result = $this->manager->find_contribution_revision_for_queue_id($queue_id);

        //var_dump($result);

        // Extract the contribution files to prepare to send to ChatGPT
        $file = $this->manager::FILE_UPLOAD_LOCATION . $result['revision']['revision_attachment'];
        $this->send_openai_request($file);
    }

    private function send_openai_request(string $file_path)
    {
        $api_key = self::OPENAI_API_KEY;

        echo "Starting validation process...\n";

        // Step 1: Upload the file
        $file_id = $this->upload_file_to_openai($file_path, $api_key);

        if (!$file_id) {
            echo "File upload failed. Stopping.\n";
            return;
        }

        // Step 2: Send file + prompt to GPT
        $this->send_validation_request($file_id, $api_key);
    }

    private function upload_file_to_openai(string $file_path, string $api_key): ?string
    {
        $url = "https://api.openai.com/v1/files";

        $cfile = new \CURLFile($file_path, 'application/zip', basename($file_path));

        $post_fields = [
            'purpose' => 'assistants', // Required for GPT to access the file
            'file'    => $cfile,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $api_key",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields,
        ]);
      
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "File Upload cURL error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['id'])) {
            echo "File uploaded successfully! File ID: " . $result['id'] . "\n";
            return $result['id'];
        } else {
            echo "Error uploading file:\n$response\n";
            return null;
        }
    }

    private function send_validation_request(string $file_id, string $api_key)
    {
        $url = "https://api.openai.com/v1/responses";

        // ✅ Correct structure for Responses API with required roles
        $data = [
            "model" => "gpt-4.1",
            "input" => [
                [
                    "role" => "system",
                    "content" => [
                        [
                            "type" => "input_text",
                            "text" => "You are a phpBB extension validator. Your job is to check the uploaded ZIP file for compliance with phpBB’s validation policies and coding guidelines."
                        ]
                    ]
                ],
                [
                    "role" => "user",
                    "content" => [
                        [
                            "type" => "input_text",
                            "text" => "Please generate a validation report for this extension package."
                        ],
                        [
                            "type" => "input_file",
                            "file_id" => $file_id
                        ]
                    ]
                ]
            ],
            "temperature" => 0.5,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer $api_key",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "Chat Request cURL error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return;
        }

        curl_close($ch);

        $result = json_decode($response, true);

        // ✅ Safely check and output GPT response
        if (isset($result['output'][0]['content'][0]['text'])) {
            echo "Validation Report:\n";
            echo $result['output'][0]['content'][0]['text'] . "\n";
        } else {
            echo "Error in API response:\n$response\n";
        }
    }
}
