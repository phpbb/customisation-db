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

use phpbb\config\config;
use phpbb\db\driver\driver_interface as db;
use phpbb\language\language;
use phpbb\titania\config\config as titania_config;
use phpbb\titania\ext;
use phpbb\user;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class events extends \phpbb\console\command\command
{
	const COMMAND_NAME = 'titania:extension:events';

	/** @var language */
	protected $language;

	/** @var config */
	protected $config;

	/** @var db */
	protected $db;

	/** @var $root_path */
	protected $root_path;

	/** @var titania_config */
	protected $titania_config;

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

	/** @var string */
	protected $tmp_folder;

	/**
	 * Constructor
	 *
	 * @param user $user
	 * @param language $language
	 * @param config $config
	 * @param db $db
	 * @param string $root_path
	 * @param string $php_ext
	 */
	public function __construct(user $user, language $language, config $config, db $db, $root_path, $php_ext)
	{
		if (!defined('TITANIA_CONTRIBS_TABLE'))
		{
			include($root_path . 'ext/phpbb/titania/common.' . $php_ext);
		}

		$this->language				= $language;
		$this->config				= $config;
		$this->db					= $db;
		$this->root_path			= $root_path;

		$this->contribs_table		= TITANIA_CONTRIBS_TABLE;
		$this->revisions_table		= TITANIA_REVISIONS_TABLE;
		$this->attachments_table	= TITANIA_ATTACHMENTS_TABLE;

		// Set up empty structure
		$this->event_listing = [
			'template' 	=> [],
			'php' 		=> [],
		];

		// Temporary folder
		$this->tmp_folder = $this->root_path . 'ext/phpbb/titania/files/contrib_temp/tmp';

		$language_files = array('console');
		$this->language->add_lang($language_files, 'phpbb/titania');

		// As we're in the CLI, we need to force server vars so that the route helper generates correct version check URLs
		$this->config['force_server_vars'] = true; // TODO: ???

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

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Store the IO object for use later
		$this->input = $input;
		$this->output = $output;

		// Show quick explanation
		$this->output->writeln($this->language->lang('CLI_DESCRIPTION_EXTENSION_EVENTS_EXPLAIN'));

		// Run the script - could take a while if there's a lot of extensions to unzip and scan
		$this->analyse();
	}

	/**
	 * Scan the files for events
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
			if ($revision['extension'] == 'zip')
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

					// Remove temp folder before we unzip the next one
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
	}

	/**
	 * Display the output in a table
	 * @param $type
	 */
	private function display_stats_table($type)
	{
		$table = new Table($this->output);
		$usages = [];

		foreach ($this->event_listing[$type] as $name => $count)
		{
			$usages[] = [$count, $name];
		}

		$table_header = [$this->language->lang('CLI_EXTENSION_EVENTS_USAGES'), $this->language->lang('CLI_EXTENSION_EVENTS_NAME')];
		$table->setHeaders($table_header)
			->setRows($usages)
			->render();

		$this->output->writeln('');
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
		$revision_php_events = [];

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
		}

		// Save to the master list
		$this->save_events_to_master_list($revision_php_events, 'php');

		return $revision_php_events;
	}

	/**
	 * Get the latest approved revision of each extension
	 * @return array
	 */
	private function get_latest_approved_revisions()
	{
		$revisions = [];

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

		$this->db->sql_freeresult($result);
		return $revisions;
	}

	/**
	 * Save the events to the master list
	 * @param $list
	 * @param $type Must be either 'template' or 'php'
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
	 * Remove directory
	 */
	private function remove_temporary_folder()
	{
		// This deletes the temporary folder
		$system = new Filesystem();
		$system->remove($this->tmp_folder);
	}

	public function set_titania_dependencies(titania_config $titania_config)
	{
		$this->titania_config = $titania_config;
	}
}
