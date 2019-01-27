<?php
/**
 *
 * This file is part of the phpBB Customisation Database package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\titania\console\command\extension;

use phpbb\db\driver\driver_interface as db;
use phpbb\language\language;
use phpbb\titania\ext;
use phpbb\user;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class events
 *
 * A script that can be run which will find the latest revision of all approved extensions, one by one unpackage them
 * and then scan the files for a) template events; b) php files for anything extending EventSubscriberInterface and if
 * it is, then find the subscribed events. Finally, output the results to show the most commonly referenced events.
 * battye was here in 2019
 *
 * @package phpbb\titania\console\command\extension
 */
class events extends \phpbb\console\command\command
{
	// To execute this script, run: php bin/phpbbcli.php titania:extension:events
	const COMMAND_NAME = 'titania:extension:events';
	const COMMAND_TMP_DIRECTORY = 'ext/phpbb/titania/files/contrib_temp/tmp';

	/** @var language */
	protected $language;

	/** @var db */
	protected $db;

	/** @var $root_path */
	protected $root_path;

	/** @var OutputInterface */
	protected $output;

	/** @var InputInterface */
	protected $input;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $revisions_table;

	/** @var string */
	protected $attachments_table;

	/** @var array */
	protected $event_listing;

	/** @var array */
	protected $custom_php_events;

	/** @var string */
	protected $tmp_folder;

	/**
	 * Constructor
	 *
	 * @param user $user
	 * @param language $language
	 * @param db $db
	 * @param string $root_path
	 * @param string $php_ext
	 */
	public function __construct(user $user, language $language, db $db, $root_path, $php_ext)
	{
		if (!defined('TITANIA_CONTRIBS_TABLE'))
		{
			include($root_path . 'ext/phpbb/titania/common.' . $php_ext);
		}

		// Set up the injected properties
		$this->language = $language;
		$this->db = $db;
		$this->root_path = $root_path;

		// Save our table names
		$this->contribs_table = TITANIA_CONTRIBS_TABLE;
		$this->revisions_table = TITANIA_REVISIONS_TABLE;
		$this->attachments_table = TITANIA_ATTACHMENTS_TABLE;

		// Set up empty structure
		$this->event_listing = [
			'template' 	=> [],
			'php' 		=> [],
		];

		// Custom events
		$this->custom_php_events = [];

		// Temporary folder
		$this->tmp_folder = $this->root_path . COMMAND_TMP_DIRECTORY;

		$language_files = ['console'];
		$this->language->add_lang($language_files, 'phpbb/titania');

		parent::__construct($user);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setName(self::COMMAND_NAME)
			->setDescription($this->language->lang('CLI_DESCRIPTION_EXTENSION_EVENTS'));
	}

	/**
	 * Execute the script
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Store the IO object for use later
		$this->input = $input;
		$this->output = $output;

		// Show quick explanation
		$this->output->writeln($this->language->lang('CLI_DESCRIPTION_EXTENSION_EVENTS_EXPLAIN'));

		// Run the script - could take a while if there's a lot of extensions to unzip and scan
		$start = time();
		$count = $this->analyse();
		$total = time() - $start;

		// Show the execution time
		$total_time = $this->execution_time($total);
		$average_time = $this->execution_time($total, $count);

		$this->output->writeln($this->language->lang('CLI_EXTENSION_EVENTS_EXECUTION_TIME',
			$total_time['h'], $total_time['m'], $total_time['s'], $average_time['h'], $average_time['m'], $average_time['s']));
	}

	/**
	 * Scan the files for events
	 * @return int
	 * @throws \Exception
	 */
	private function analyse()
	{
		// Get the latest approved revision of each extension
		$revisions = $this->get_latest_approved_revisions();

		$progress = $this->create_progress_bar(
			count($revisions),
			new SymfonyStyle($this->input, $this->output),
			$this->output,
			true
		);

		// Start the progress bar
		$progress->setMessage($this->language->lang('CLI_EXTENSION_EVENTS_SCAN_START'));
		$progress->start();

		// Remove temp folder - clean up before we begin
		$this->remove_temporary_folder();

		foreach ($revisions as $revision)
		{
			// Increase the progress
			$progress->setMessage($this->language->lang('CLI_EXTENSION_EVENTS_SCAN_CONTRIB', $revision['contrib_name'], $revision['revision_id'], $revision['real_filename']));
			$progress->advance();

			// Read the attachment
			if ($revision['extension'] === 'zip')
			{
				$path = $this->root_path . 'ext/phpbb/titania/files/revisions/' . $revision['physical_filename'];

				$zip = new \ZipArchive();
				$result = $zip->open($path);

				// If we ever want a revision by revision breakdown of the events used, we could output it inside here.
				if ($result)
				{
					// Unzip the revision to a temporary folder
					$zip->extractTo($this->tmp_folder);
					$zip->close();

					// Find template events
					$this->find_template_events();

					// Find PHP events
					$this->find_php_events();

					// Remove temp folder before we unzip the next one.
					$this->remove_temporary_folder();
				}
			}
		}

		// Complete
		$progress->setMessage($this->language->lang('CLI_EXTENSION_EVENTS_SCAN_FINISH'));
		$progress->finish();

		// Output the listing statistics
		$this->output->writeln('');

		// Sort by usages
		arsort($this->event_listing['template']);
		arsort($this->event_listing['php']);

		// Template stats
		$this->output->writeln($this->language->lang('CLI_EXTENSION_EVENTS_TEMPLATE'));
		$this->display_stats_table('template');

		// PHP stats
		$this->output->writeln($this->language->lang('CLI_EXTENSION_EVENTS_PHP'));
		$this->display_stats_table('php');

		// Custom events
		$this->output->writeln($this->language->lang('CLI_EXTENSION_EVENTS_CUSTOM'));
		$this->display_custom_events_table();

		return count($revisions);
	}

	/**
	 * Get the latest approved revision of each extension
	 * @return array
	 */
	private function get_latest_approved_revisions()
	{
		$revisions = [];

		// Start transaction
		$this->db->sql_transaction('begin');

		$sql = 'SELECT *
				FROM ' . $this->contribs_table . '
				WHERE contrib_type = ' . ext::TITANIA_TYPE_EXTENSION;

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// For every extension, get the latest approved revision
			$revision_sql = 'SELECT r.*, a.*
				FROM ' . $this->revisions_table . ' r
					LEFT JOIN ' . $this->attachments_table . ' a
						ON a.attachment_id = r.attachment_id
				WHERE r.contrib_id = ' . (int) $row['contrib_id'] . '
				AND r.revision_status = ' . ext::TITANIA_REVISION_APPROVED . '
				ORDER BY r.revision_id DESC';

			$revision_result = $this->db->sql_query_limit($revision_sql, 1);
			$revision_row = $this->db->sql_fetchrow($revision_result);

			if ($revision_row)
			{
				// Only bother if the contribution has approved revisions
				$revision_row['contrib_name'] = $row['contrib_name'];
				$revisions[] = $revision_row;
			}
		}

		// End transaction
		$this->db->sql_freeresult($result);
		$this->db->sql_transaction('commit');

		return $revisions;
	}

	/**
	 * Render table to show the data
	 * @param array $table_header
	 * @param array $table_content
	 */
	private function render_table($table_header, $table_content)
	{
		$table = new Table($this->output);
		$table->setHeaders($table_header)
			->setRows($table_content)
			->render();

		$this->output->writeln('');
	}

	/**
	 * Display the output in a table
	 * @param string $type
	 */
	private function display_stats_table($type)
	{
		$usages = [];

		foreach ($this->event_listing[$type] as $name => $count)
		{
			$usages[] = [$count, $name];
		}

		// Show the table
		$table_header = [$this->language->lang('CLI_EXTENSION_EVENTS_USAGES'), $this->language->lang('CLI_EXTENSION_EVENTS_NAME')];
		$this->render_table($table_header, $usages);
	}

	/**
	 * Show the custom extension events
	 */
	private function display_custom_events_table()
	{
		// Show the custom PHP events
		// Firstly, convert our custom event list to a proper map
		$map = [];

		// Sort alphabetically
		ksort($this->custom_php_events);

		foreach ($this->custom_php_events as $extension => $events)
		{
			ksort($events);

			// Grab each event
			foreach ($events as $event)
			{
				$map[] = [$extension, $event];
			}
		}

		// Show the table
		$table_header = [$this->language->lang('CLI_EXTENSION_EVENTS_EXTENSION_NAME'), $this->language->lang('CLI_EXTENSION_EVENTS_NAME')];
		$this->render_table($table_header, $map);
	}

	/**
	 * Find template events
	 * @return array
	 */
	private function find_template_events()
	{
		$revision_template_events = [];

		// Find all of the template events being used using Symfony Finder and wildcards
		$finder = new Finder();
		$finder->in($this->tmp_folder . '/*/*/styles/*/template/event');

		/** @var SplFileInfo $file */
		foreach ($finder as $file)
		{
			$event_name = $file->getBasename('.html');

			// Save the event name
			if (!in_array($event_name, $revision_template_events))
			{
				$revision_template_events[] = $event_name;
			}
		}

		// Save to the master list
		$this->save_events_to_master_list($revision_template_events, 'template');

		return $revision_template_events;
	}

	/**
	 * Find PHP events
	 * @return array
	 * @throws \Exception
	 */
	private function find_php_events()
	{
		$revision_php_events = $custom_php_events = [];

		// Find the PHP events
		$finder = new Finder();
		$finder->in($this->tmp_folder)->files()->name('*.php');

		/** @var SplFileInfo $file */
		foreach ($finder as $file)
		{
			// Parse the array text
			$matches = \array_parser::check_events($file->getRealPath());
			$event_list = array_keys($matches);

			foreach ($event_list as $event_name)
			{
				if (!in_array($event_name, $revision_php_events))
				{
					$revision_php_events[] = $event_name;
				}
			}

			// Check for new events
			$extension_relative_path = explode('/', $file->getRelativePath());
			$extension_path = $extension_relative_path[0] . '/' . $extension_relative_path[1];

			// Ignore anything in the vendor folder
			if ($extension_relative_path[2] !== 'vendor')
			{
				$exporter = new \extracted_php_exporter($this->root_path, $this->tmp_folder);
				$exporter->crawl_php_file($file);
				$new_events = $exporter->get_events();

				if (count($new_events) > 0)
				{
					if (!array_key_exists($extension_path, $custom_php_events))
					{
						$custom_php_events[$extension_path] = [];
					}

					// Store the custom events
					foreach ($new_events as $event)
					{
						$custom_php_events[$extension_path][] = $event['event'];
					}
				}
			}
		}

		// Save to the master list
		$this->save_events_to_master_list($revision_php_events, 'php');
		$this->save_custom_events($custom_php_events);

		return $revision_php_events;
	}

	/**
	 * Save the events to the master list
	 * @param array $list
	 * @param string $type Must be either 'template' or 'php'
	 */
	private function save_events_to_master_list($list, $type)
	{
		foreach ($list as $revision_event)
		{
			if (!array_key_exists($revision_event, $this->event_listing[$type]))
			{
				// Create counter if no key already exists
				$this->event_listing[$type][$revision_event] = 1;
			}

			else
			{
				// Increment counter because it does exist
				$this->event_listing[$type][$revision_event]++;
			}
		}
	}

	/**
	 * Save the custom events that users have added themselves (non core events)
	 * @param array $custom_php_events
	 */
	private function save_custom_events($custom_php_events)
	{
		foreach ($custom_php_events as $extension => $names)
		{
			if (!array_key_exists($extension, $this->custom_php_events))
			{
				$this->custom_php_events[$extension] = [];
			}

			foreach ($names as $name)
			{
				// Save each name individually
				$this->custom_php_events[$extension][] = $name;
			}
		}
	}

	/**
	 * Remove directory
	 */
	private function remove_temporary_folder()
	{
		// This deletes the temporary folder
		$system = new Filesystem();
		$system->remove($this->tmp_folder);
	}

	/**
	 * Generate a human readable execution time
	 * @param int $total
	 * @param int $count
	 * @return array
	 */
	private function execution_time($total, $count = 0)
	{
		$hours = $minutes = $seconds = 0;

		if ($count > 0)
		{
			// If calculating an average, divide the total by the count
			$total = floor($total / $count);
		}

		if ($total > 0)
		{
			// Pad each number to two digits
			$hours = str_pad(floor($total / 3600), 2, '0', STR_PAD_LEFT);
			$minutes = str_pad(floor(($total / 60) % 60), 2, '0', STR_PAD_LEFT);
			$seconds = str_pad($total % 60, 2, '0', STR_PAD_LEFT);
		}

		// Return a simple array
		return array(
			'h' => $hours,
			'm' => $minutes,
			's' => $seconds
		);
	}
}
