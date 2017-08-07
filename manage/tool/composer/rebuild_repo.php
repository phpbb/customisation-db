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

namespace phpbb\titania\manage\tool\composer;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\path_helper;
use phpbb\titania\composer\repository;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\controller\helper;
use phpbb\titania\entity\package;
use phpbb\titania\ext;
use phpbb\titania\manage\tool\base;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Finder\SplFileInfo;

class rebuild_repo extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var ext_config */
	protected $ext_config;

	/** @var type_collection */
	protected $types;

	/** @var repository */
	protected $repo;

	/** @var helper */
	protected $controller_helper;

	/** @var path_helper */
	protected $path_helper;

	/** @var package */
	protected $package;

	/** @var string */
	protected $attachments_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $revisions_table;

	/** @var int */
	protected $total;

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param ext_config $ext_config
	 * @param type_collection $types
	 * @param repository $repo
	 * @param helper $controller_helper
	 * @param path_helper $path_helper
	 */
	public function __construct(db_driver_interface $db, ext_config $ext_config, type_collection $types, repository $repo, helper $controller_helper, path_helper $path_helper)
	{
		$this->db = $db;
		$this->ext_config = $ext_config;
		$this->types = $types;
		$this->repo = $repo;
		$this->controller_helper = $controller_helper;
		$this->path_helper = $path_helper;
		$this->package = new package;

		$table_prefix = $this->ext_config->__get('table_prefix');
		$this->attachments_table = $table_prefix . 'attachments';
		$this->contribs_table = $table_prefix . 'contribs';
		$this->revisions_table = $table_prefix . 'revisions';
	}

	/**
	 * Get total items to be processed.
	 *
	 * @return int
	 */
	public function get_total()
	{
		if ($this->total === null)
		{
			$types = $this->types->use_composer();

			if (empty($types))
			{
				$this->total = 0;
				return $this->total;
			}

			$sql = 'SELECT COUNT(r.revision_id) AS cnt
				FROM ' . $this->contribs_table . ' c, ' .
				$this->revisions_table . ' r
				WHERE c.contrib_id = r.contrib_id
					AND c.contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
					AND r.revision_status = ' . ext::TITANIA_REVISION_APPROVED . '
					AND ' . $this->db->sql_in_set('c.contrib_type', $types);
			$this->db->sql_query($sql);
			$this->total = (int) $this->db->sql_fetchfield('cnt');
		}
		return $this->total;
	}

	/**
	 * Get batch to process.
	 *
	 * @param bool $fetch_attach_data	Whether to fetch attachment data.
	 * @return array
	 */
	protected function get_batch($fetch_attach_data)
	{
		$types = $this->types->use_composer();

		if (empty($types))
		{
			return array();
		}
		$attach_fields = $attach_table = $attach_where = '';

		if ($fetch_attach_data)
		{
			$attach_fields = ', a.attachment_directory, a.physical_filename';
			$attach_table = ", {$this->attachments_table} a";
			$attach_where = 'AND a.attachment_id = r.attachment_id';
		}

		$sql = 'SELECT c.contrib_id, c.contrib_name_clean, c.contrib_type, r.revision_id,
				r.attachment_id, r.revision_composer_json' . $attach_fields . '
			FROM ' . $this->contribs_table . ' c, ' .
			$this->revisions_table . ' r ' .
			$attach_table . '
			WHERE c.contrib_id = r.contrib_id ' .
			$attach_where . '
				AND c.contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
				AND r.revision_status = ' . ext::TITANIA_REVISION_APPROVED . '
				AND ' . $this->db->sql_in_set('c.contrib_type', $types) . '
			ORDER BY c.contrib_id ASC, r.revision_id ASC';
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);
		$contribs = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$contrib_id = (int) $row['contrib_id'];

			if (!isset($contribs[$contrib_id]))
			{
				$contribs[$contrib_id] = array();
			}
			$contribs[$contrib_id][] = $row;
		}
		$this->db->sql_freeresult($result);

		return $contribs;
	}

	/**
	 * Run tool.
	 *
	 * @param bool|false $from_file	Rebuild packages using composer.json's
	 * 	from the revision zip files
	 * @param bool|false $force		Force tool to run if a build is already
	 * 	in progress
	 * @param ProgressHelper|null $progress
	 * @return array
	 */
	public function run($from_file = false, $force = false, $progress = null)
	{
		$batch = $this->get_batch($from_file);

		$group_count = 1;
		$group = $this->limit > 0 ? ($this->start / $this->limit + 1) : 1;

		if ($group === 1)
		{
			$this->repo->prepare_build_dir($force);
		}

		$last_type = $last_contrib = '';
		$packages = array();

		foreach ($batch as $contrib_id => $revisions)
		{
			$added = false;

			foreach ($revisions as $index => $revision)
			{
				if ($from_file)
				{
					$revision = $this->rebuild_from_file($revision);
				}
				if ($progress)
				{
					$progress->advance();
				}

				if (!$revision['revision_composer_json'])
				{
					unset($batch[$contrib_id][$index]);

					continue;
				}
				$added = true;

				if ($last_type !== '' && $last_type != $revision['contrib_type'])
				{
					$group_count = $group = 1;
				}
				$last_type = $revision['contrib_type'];
				$download_url = $this->path_helper->strip_url_params(
					$this->controller_helper->route('phpbb.titania.download',
						array(
							'id'	=> (int) $revision['attachment_id'],
							'type'	=> 'composer',
						)
					),
					'sid'
				);
				$contrib_url = $this->path_helper->strip_url_params(
					$this->controller_helper->route('phpbb.titania.contrib',
						array(
							'contrib_type'	=> $this->types->get($revision['contrib_type'])->url,
							'contrib'	=> $revision['contrib_name_clean'],
						)
					),
					'sid'
				);

				$packages = $this->repo->set_release(
					$packages,
					$revision['revision_composer_json'],
					$download_url,
					$contrib_url
				);
				unset($batch[$contrib_id][$index]);
			}

			if (!$added)
			{
				continue;
			}

			if (($group_count % 50) === 0)
			{
				$this->dump_include($last_type, $group, $packages);
				$group_count = 0;
				$group++;
				$packages = array();
			}
			$group_count++;
		}
		if (!empty($packages))
		{
			$this->dump_include($last_type, $group, $packages);
		}

		$next_batch = $this->start + $this->limit;

		if ($next_batch >= $this->get_total())
		{
			$this->repo->deploy_build();
		}

		return $this->get_result(
			'COMPOSER_REPO_REBUILT',
			$this->get_total(),
			$next_batch < $this->get_total() ? $next_batch : false
		);
	}

	/**
	 * Dump include file.
	 *
	 * @param string $type		Contrib type name
	 * @param int $group		Group id
	 * @param array $packages	Packages
	 */
	protected function dump_include($type, $group, array $packages)
	{
		$type_name = $this->types->get($type)->name;
		$this->repo->dump_include("packages-$type_name-$group.json", $packages);
	}

	/**
	 * Get composer.json contents from a zip file.
	 *
	 * @param string $file Path to the zip file
	 * @return string Returns the content of the composer.json
	 */
	protected function get_composer_json($file)
	{
		$path = $this->package
			->set_source($file)
			->set_temp_path($this->ext_config->__get('contrib_temp_path'), true)
			->extract()
			->find_directory(
				array(
					'files' => array(
						'required' => 'composer.json',
					),
				),
				'vendor'
			)
		;
		$composer_json = '';

		if ($path !== null)
		{
			$composer_json = file_get_contents($this->package->get_temp_path() . '/' . $path . '/composer.json');
		}
		$this->package->cleanup();

		return $composer_json;
	}

	/**
	 * Force rebuild of the revision_composer_json value in the revisions table
	 * by extracting the revision and fetching the contents of the composer.json.
	 *
	 * @param array $revision
	 * @return array Returns the $revision array with the revision_composer_json value set.
	 */
	protected function rebuild_from_file(array $revision)
	{
		$file = $this->ext_config->__get('upload_path') .
			utf8_basename($revision['attachment_directory']) . '/' .
			utf8_basename($revision['physical_filename']);
		$composer_json = $this->get_composer_json($file);

		if ($composer_json)
		{
			$sql = 'UPDATE ' . $this->revisions_table . '
				SET revision_composer_json = "' . $this->db->sql_escape($composer_json) . '"
				WHERE revision_id = ' . (int) $revision['revision_id'];
			$this->db->sql_query($sql);

			$revision['revision_composer_json'] = $composer_json;
		}
		return $revision;
	}

	/**
	 * @{inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.composer.rebuild_repo';
	}
}
