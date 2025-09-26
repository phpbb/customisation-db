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

        $output->writeln('<info>RUNNING THE AI VALIDATOR</info>');
        $output->writeln('<info>TEST</info>');
        return Command::SUCCESS;
    }

    private function process()
    {
        // Retrieve queue, contribution, revision records from database and loop (and batch?)
        // ...

        // Extract the contribution files to prepare to send to ChatGPT
        // ...

        /* 
        // Your OpenAI API key
        $apiKey = "";
        
        // The API endpoint
        $url = "https://api.openai.com/v1/chat/completions";

        // The messages to send to the model
        $data = [
            "model" => "gpt-4.1-mini",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are a helpful assistant that provides clear and concise answers."
                ],
                [
                    "role" => "user",
                    "content" => "Write a short, friendly greeting."
                ]
            ],
            "temperature" => 0.7 // Optional: controls randomness (0.0 = deterministic, 1.0 = creative)
        ];

        // Initialize cURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey"
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute the request
        $response = curl_exec($ch);

        // Handle errors
        if (curl_errno($ch)) {
            echo "cURL error: " . curl_error($ch);
            curl_close($ch);
            exit;
        }

        curl_close($ch);

        // Decode the JSON response
        $result = json_decode($response, true);

        // Print the model's reply
        if (isset($result['choices'][0]['message']['content'])) {
            echo "ChatGPT says: " . $result['choices'][0]['message']['content'] . "\n";
        } else {
            echo "Error: " . $response . "\n";
        }
        */
    }
}