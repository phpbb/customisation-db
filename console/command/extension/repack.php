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
use phpbb\template\template;
use phpbb\titania\attachment\attachment;
use phpbb\titania\attachment\operator as attachments;
use phpbb\titania\config\config as titania_config;
use phpbb\titania\contribution\extension\type;
use phpbb\titania\entity\package;
use phpbb\titania\ext;
use phpbb\user;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class repack extends \phpbb\console\command\command
{
	const COMMAND_NAME = 'titania:extension:repack';

	/** @var language */
	protected $language;

	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var db */
	protected $db;

	/** @var titania_config */
	protected $titania_config;

	/** @var type */
	protected $ext_type;

	/** @var attachments */
	protected $attachments;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $revisions_table;

	/** @var \titania_revision */
	protected $revision;

	/** @var \titania_contribution */
	protected $contrib;

	/** @var \titania_queue */
	protected $queue;

	/** @var attachment */
	protected $attachment;

	/** @var package */
	protected $package;

	/**
	 * Constructor
	 *
	 * @param user		$user
	 * @param language	$language
	 * @param config	$config
	 * @param template	$template
	 * @param db		$db
	 * @param string	$root_path
	 * @param string	$php_ext
	 */
	public function __construct(
		user $user,
		language $language,
		config $config,
		template $template,
		db $db,
		$root_path,
		$php_ext
	)
	{
		if (!defined('TITANIA_CONTRIBS_TABLE'))
		{
			include($root_path . 'ext/phpbb/titania/common.' . $php_ext);
		}

		$this->language			= $language;
		$this->config			= $config;
		$this->template			= $template;
		$this->db				= $db;

		$this->contribs_table	= TITANIA_CONTRIBS_TABLE;
		$this->revisions_table	= TITANIA_REVISIONS_TABLE;

		$this->language->add_lang(array('console', 'manage', 'manage_tools', 'contributions'), 'phpbb/titania');

		// As we're in the CLI, we need to force server vars so that the route helper generates correct version check URLs
		$this->config['force_server_vars'] = true;

		// The parent constructor calls configure(), all properties need to be set up at this point
		parent::__construct($user);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName(self::COMMAND_NAME)
			->setDescription($this->language->lang('CLI_DESCRIPTION_EXTENSION_REPACK'))
			->addArgument('vendor/extname', InputArgument::OPTIONAL, $this->language->lang('CLI_EXTENSION_REPACK_EXT_NAME'))
			->addOption('message', 'm', InputOption::VALUE_REQUIRED, $this->language->lang('CLI_EXTENSION_REPACK_MESSAGE'))
		;
	}

	/**
	 * These Titania dependencies need to be instantiated after common.php
	 * has been included, otherwise database tables aren't defined.
	 *
	 * @param titania_config	$titania_config
	 * @param type				$ext_type
	 * @param attachments		$attachments
	 */
	public function set_titania_dependencies(
		titania_config $titania_config,
		type $ext_type,
		attachments $attachments
	)
	{
		$this->titania_config	= $titania_config;
		$this->ext_type			= $ext_type;
		$this->attachments		= $attachments;
	}

	/**
	 * Executes the command titania:extension:repack.
	 *
	 * Repacks one or all extensions.
	 *
	 * @param InputInterface	$input	An InputInterface instance
	 * @param OutputInterface	$output	An OutputInterface instance
	 *
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$ext_name = $input->getArgument('vendor/extname');
		$message = $input->getOption('message');

		$revisions = $this->get_extension_revisions($ext_name);
		$num_revisions = count(call_user_func_array('array_merge', array_values($revisions)));
		$num_repacks = 0;

		if ($num_revisions)
		{
			$progress = $this->create_progress_bar($num_revisions, new SymfonyStyle($input, $output), $output);
			$progress->start();
			$errors = array();

			foreach ($revisions as $contrib_name => $revision_ids)
			{
				foreach ($revision_ids as $revision_id)
				{
					try
					{
						$this->repack($revision_id);
						$num_repacks++;
					}
					catch (\Exception $e)
					{
						$errors[] = $this->language->lang('CLI_EXTENSION_REPACK_ERROR', $revision_id, $contrib_name, $e->getMessage());

						// Make sure we don't try to make a post with a defunct queue object
						$this->queue = null;
					}

					$progress->advance();
				}

				$this->post($this->ext_type->forum_robot, $message);
			}

			$progress->finish();

			foreach ($errors as $error)
			{
				$output->writeln('<error>' . $error . '</error>');
			}
		}

		$output->writeln('');
		$output->writeln('<info>' . $this->language->lang('CLI_EXTENSION_REPACK_FINISHED', $num_repacks) . '</info>');
	}

	/**
	 * Create a post in the current extension's validation discussion topic
	 *
	 * @param int $user_id
	 * @param string $message
	 */
	protected function post($user_id, $message)
	{
		if ($this->queue && $message)
		{
			$message .= "\n\n\n" . $this->language->lang('CLI_EXTENSION_REPACK_POST_NOTE', self::COMMAND_NAME);
			$this->queue->discussion_reply($message, false, $user_id);
		}
	}

	/**
	 * @param $ext_name 'vendor/extname' or empty
	 * @return array mapping from 'vendor/extname' to list of revision ids
	 */
	protected function get_extension_revisions($ext_name)
	{
		$sql_array = array(
			'SELECT'	=> 'r.revision_id, c.contrib_package_name',
			'FROM'		=> array(
				$this->revisions_table => 'r',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->contribs_table => 'c'),
					'ON'	=> 'r.contrib_id = c.contrib_id',
				),
			),
			'WHERE'		=> 'c.contrib_type = ' . ext::TITANIA_TYPE_EXTENSION,
			'ORDER_BY'	=> 'c.contrib_package_name ASC, r.revision_id ASC',
		);

		if ($ext_name)
		{
			$sql_array['WHERE'] .= " AND c.contrib_package_name = '" . $this->db->sql_escape($ext_name) . "'";
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$revisions = array();

		foreach ($rows as $row)
		{
			$revisions[$row['contrib_package_name']][] = (int) $row['revision_id'];
		}

		return $revisions;
	}

	/**
	 * @param int $revision_id
	 * @throws \Exception
	 */
	protected function repack($revision_id)
	{
		$this->load_objects($revision_id);

		$result = $this->ext_type->perform_repack(
			$this->contrib,
			$this->revision,
			$this->attachment,
			$this->attachment ? $this->attachment->get_url() : '',
			$this->package,
			$this->template
		);

		$this->revision->phpbb_versions = array();
		$this->revision->submit();

		if (!empty($result['error']))
		{
			throw new \Exception($result['error']);
		}
	}

	/**
	 * Load objects needed to run a tool.
	 *
	 * @param int $id	Revision id.
	 */
	protected function load_objects($id)
	{
		$this->load_revision($id);
		$this->load_contrib();
		$this->load_queue();
		$this->load_attachment();
		$this->load_package();
	}

	/**
	 * Load revision.
	 *
	 * @param $id int
	 * @throws \Exception Throws exception if no revision found.
	 */
	protected function load_revision($id)
	{
		$this->revision = new \titania_revision(false, $id);

		if (!$id || !$this->revision->load())
		{
			throw new \Exception($this->language->lang('NO_REVISION'));
		}
	}

	/**
	 * Load revision's parent contribution.
	 *
	 * @throws \Exception Throws exception if no contrib found.
	 */
	protected function load_contrib()
	{
		$this->contrib = new \titania_contribution;

		if (!$this->contrib->load($this->revision->contrib_id))
		{
			throw new \Exception($this->language->lang('CONTRIB_NOT_FOUND'));
		}

		$this->revision->contrib = $this->contrib;
	}

	/**
	 * Load revision's corresponding queue item.
	 */
	protected function load_queue()
	{
		$this->queue = $this->revision->get_queue();
	}

	/**
	 * Load revision attachment.
	 *
	 * @throws \Exception Throws exception if no attachment found.
	 */
	protected function load_attachment()
	{
		$this->attachments
			->configure(ext::TITANIA_CONTRIB, $this->contrib->contrib_id)
			->load(array($this->revision->attachment_id))
		;

		$this->attachment = $this->attachments->get($this->revision->attachment_id);

		if (!$this->attachment)
		{
			$this->language->add_lang('viewtopic');
			throw new \Exception($this->language->lang('ERROR_NO_ATTACHMENT'));
		}
	}

	/**
	 * Load revision package.
	 */
	protected function load_package()
	{
		$this->package = (new package)
			->set_source($this->attachment->get_filepath())
			->set_temp_path($this->titania_config->__get('contrib_temp_path'), true)
		;
	}
}
