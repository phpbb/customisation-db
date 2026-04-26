<?php
namespace phpbb\oberon\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class queue_manager extends Command
{
    /* @var \phpbb\db\driver\driver_interface $db */
    protected $db;

	/* @var \phpbb\oberon\manager\manager $manager */
	protected $manager;

    // TODO: add types
    protected $input;
    protected $output;

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\oberon\manager\manager $manager)
    {        
        $this->db = $db;
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        // Run the command with php bin/phpbbcli.php custdb:queue_manager
        $this
            ->setName('custdb:queue_manager')
            ->setDescription('Manage the Customisation DB queue')
            ->setHelp('Manage the Customisation DB queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Begin queue manager...</info>');
        $this->process($input, $output);
        $output->writeln('<info>End queue manager...</info>');
        return Command::SUCCESS;
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $output->writeln('<info>[process] Begin queue manager processing...</info>');

        // Cleanup queue items that have already been processed by a human (internal status denied/approved)
        $this->cleanup();

        // Find all items in the queue for processing and retrieve revision and customisation records
        $queue_items = $this->manager->find_queue_items_for_processing();

        foreach ($queue_items as $queue_id => $queue_item)
        {
            $contribution = $queue_item['contribution'];
            $revision = $queue_item['revision'];

            $output->writeln(sprintf('<info>[process] Processing queue ID %d, contribution ID %d, revision ID %d...</info>', $queue_id, $contribution, $revision));

            // Is it an extension, style or translation?
            // Then run the AI-validation, run the GitHub Codespaces integration
            if (in_array($contribution['contribution_type'], [$this->manager::TYPE_EXTENSIONS, $this->manager::TYPE_STYLES, $this->manager::TYPE_TRANSLATIONS]))
            {
                $this->process_extension_style_translation($queue_id, $contribution, $revision);
            }

            else
            {
                $this->process_tool_bbcode($queue_id, $contribution, $revision);
            }

//TODO:Remove this in future -- for testing I just want it to run once
die();
        }
    }

    // Process extensions, styles and translations separately - as these are things that we need a more thorough validation of
    public function process_extension_style_translation(int $queue_id, array $contribution, array $revision)
    {
        $queue_item = $this->manager->find_queue_item($queue_id);
        
        // Run AI validation (if internal queue status is unvalidated) for extensions, styles and translations
        if ($queue_item['queue_status'] == $this->manager::INTERNAL_STATUS_UNVALIDATED || $queue_item['queue_status'] == $this->manager::INTERNAL_STATUS_AWAITING_AI_VALIDATION)
        { 
            $this->output->writeln('<info>[process_extension_style_translation] Running AI validation on item ' . $queue_id . '</info>');

            // Call AI Validation and pass along arguments if needed
            $this->run_ai_validation_command([
                'queue_id' => $queue_id,
            ]);
        }

        // Run GitHub Codespace integration for testing (if internal queue status is completed ai validation)
        if ($queue_item['queue_status'] == $this->manager::INTERNAL_STATUS_COMPLETED_AI_VALIDATION)
        {
            // TODO: This may be too ambitious at the moment
        }
    }

    // Process tools and bbcodes, these require a different type of validation
    public function process_tool_bbcode(int $queue_id, array $contribution, array $revision)
    {
        // Set to awaiting testing
        $this->manager->update_internal_queue_status($queue_id, $this->manager::INTERNAL_STATUS_AWAITING_TESTING);
    }

    private function cleanup()
    {
        $this->output->writeln('<info>[cleanup] Clean up stale queue entries...</info>');
        $this->manager->remove_queue_entries();
    }

    private function run_ai_validation_command(array $arguments)
    {
        $this->output->writeln(sprintf('<info>[run_ai_validation_command] Calling ai_validation script for queue ID %d...</info>', $arguments['queue_id']));

        // Get the current application
        $application = $this->getApplication();

        if (!$application) 
        {
            throw new \RuntimeException('No application instance available.');
        }

        // Find the ai_validation command
        $command = $application->find('custdb:ai_validation');

        // Prepare input for the ai_validation command
        $input = new ArrayInput(array_merge(['command' => 'custdb:ai_validation'], $arguments));

        // Capture the output instead of sending directly to terminal
        $bufferedOutput = new BufferedOutput();

        // Run the ai_validation command
        $this->output->writeln(sprintf('<info>[run_ai_validation_command] Preparing to run external call for ai_validation script for queue ID %d...</info>', $arguments['queue_id']));
        $returnCode = $command->run($input, $bufferedOutput);

        // Display the captured output
        $this->output->writeln('<info>Output of ai_validation:</info>');
        $this->output->writeln($bufferedOutput->fetch());

        return $returnCode;
    }
}