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
        // Run AI validation (if internal queue status is unvalidated)
        if ($contribution['contribution_type'] == $this->manager::TYPE_EXTENSIONS)
        {   
            // Call AI Validation and pass along arguments if needed
            $this->run_ai_validation_command([
                'queue_id' => $queue_id,
            ]);

            $this->output->writeln('<info>[process_extension_style_translation] Running AI validation on item ' . $queue_id . '</info>');
        }

        // Run GitHub Codespace integration for testing (if internal queue status is completed ai validation)
    }

    // Process tools and bbcodes, these require a different type of validation
    public function process_tool_bbcode()
    {
        // Publish - set internal queue status to awaiting testing
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