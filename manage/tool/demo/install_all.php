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

namespace phpbb\titania\manage\tool\demo;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\style\demo\manager as demo_manager;
use phpbb\titania\ext;
use phpbb\titania\manage\tool\base;
use phpbb\user;

class install_all extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var ext_config */
	protected $ext_config;

	/** @var demo_manager */
	protected $demo_manager;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $revisions_table;

	/** @var string */
	protected $revisions_phpbb_table;

	/** @var int */
	protected $branch = 0;

	/** @var bool|null Whether the branch has a usable demo board */
	protected $board_ready;

	/** @var int Each item extracts a package and calls the demo board hook, so keep batches small. */
	protected $limit = 5;

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param user $user
	 * @param ext_config $ext_config
	 * @param demo_manager $demo_manager
	 */
	public function __construct(db_driver_interface $db, user $user, ext_config $ext_config, demo_manager $demo_manager)
	{
		$this->db = $db;
		$this->user = $user;
		$this->ext_config = $ext_config;
		$this->demo_manager = $demo_manager;
		$table_prefix = $ext_config->__get('table_prefix');
		$this->contribs_table = $table_prefix . 'contribs';
		$this->revisions_table = $table_prefix . 'revisions';
		$this->revisions_phpbb_table = $table_prefix . 'revisions_phpbb';
	}

	/**
	 * Set the phpBB branch to install style demos for.
	 *
	 * @param int $branch
	 * @return $this
	 */
	public function set_branch($branch)
	{
		$this->branch = (int) $branch;

		return $this;
	}

	/**
	 * Check that the branch has a usable demo board and connect to it.
	 *
	 * @return bool
	 */
	protected function board_ready()
	{
		if ($this->board_ready === null)
		{
			$this->board_ready = $this->demo_manager->set_branch($this->branch);
		}

		return $this->board_ready;
	}

	/**
	 * Count the styles the tool would install on the demo board.
	 *
	 * A style counts when it is not installed on the demo board and its latest
	 * validated revision for the branch is approved and submitted, mirroring
	 * what get_download() will select when the tool runs.
	 *
	 * @return int|bool Returns false if the branch has no usable demo board.
	 */
	public function count_pending()
	{
		if (!$this->board_ready())
		{
			return false;
		}
		$sql = 'SELECT c.contrib_id, c.contrib_name_clean, MAX(v.revision_id) AS revision_id
			FROM ' . $this->contribs_table . ' c
			JOIN ' . $this->revisions_phpbb_table . ' v
				ON (v.contrib_id = c.contrib_id
					AND v.phpbb_version_branch = ' . $this->branch . '
					AND v.revision_validated = 1)
			WHERE c.contrib_type = ' . ext::TITANIA_TYPE_STYLE . '
				AND c.contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
				AND c.contrib_visible = 1
			GROUP BY c.contrib_id, c.contrib_name_clean';
		$result = $this->db->sql_query($sql);
		$candidates = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->is_installed($row))
			{
				$candidates[(int) $row['revision_id']] = true;
			}
		}
		$this->db->sql_freeresult($result);

		if (empty($candidates))
		{
			return 0;
		}
		$sql = 'SELECT COUNT(revision_id) AS cnt
			FROM ' . $this->revisions_table . '
			WHERE ' . $this->db->sql_in_set('revision_id', array_keys($candidates)) . '
				AND revision_status = ' . ext::TITANIA_REVISION_APPROVED . '
				AND revision_submitted = 1';
		$result = $this->db->sql_query($sql);
		$pending = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $pending;
	}

	/**
	 * Run the tool.
	 *
	 * @param int $installed	Styles installed by previous batches
	 * @param int $already		Styles already installed, skipped by previous batches
	 * @param int $skipped		Styles without a package, skipped by previous batches
	 * @param int $failed		Styles failed in previous batches
	 * @return array
	 */
	public function run($installed = 0, $already = 0, $skipped = 0, $failed = 0)
	{
		if (!$this->board_ready())
		{
			return array_merge(
				$this->get_result('INSTALL_DEMO_STYLES_NOT_CONFIGURED', 0, false),
				array(
					'branch'	=> $this->branch,
					'installed'	=> 0,
					'already'	=> 0,
					'skipped'	=> 0,
					'failed'	=> 0,
				)
			);
		}

		$total = $this->get_total();

		foreach ($this->get_batch() as $row)
		{
			if ($this->is_installed($row))
			{
				$already++;
				continue;
			}
			$contrib = new \titania_contribution;

			if ($contrib->load((int) $row['contrib_id']) === false)
			{
				$failed++;
				continue;
			}
			$contrib->get_download();

			if (empty($contrib->download[$this->branch]))
			{
				$skipped++;
				continue;
			}
			$revision = new \titania_revision($contrib);
			$revision->__set_array($contrib->download[$this->branch]);

			if ($contrib->type->install_demo($contrib, $revision) === '')
			{
				$failed++;
			}
			else
			{
				$installed++;
			}
		}

		$next_batch = $this->start + $this->limit;

		if ($next_batch >= $total)
		{
			$result = $this->get_result(
				$this->user->lang('INSTALL_DEMO_STYLES_COMPLETE', $installed, $already, $skipped, $failed),
				$total,
				false
			);
		}
		else
		{
			$result = $this->get_result(
				$this->user->lang('INSTALL_DEMO_STYLES_PROGRESS', $next_batch, $total),
				$total,
				$next_batch
			);
		}

		return array_merge($result, array(
			'branch'	=> $this->branch,
			'installed'	=> $installed,
			'already'	=> $already,
			'skipped'	=> $skipped,
			'failed'	=> $failed,
		));
	}

	/**
	 * Check whether a contribution's style is already installed on the demo board.
	 *
	 * @param array $row	Contribution row with contrib_name_clean and contrib_id.
	 * @return bool
	 */
	protected function is_installed($row)
	{
		return $this->demo_manager->is_style_installed(
			$this->demo_manager->get_style_dir_name($row['contrib_name_clean'], $row['contrib_id'])
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_total()
	{
		if ($this->total === null)
		{
			$sql = 'SELECT COUNT(contrib_id) AS cnt
				FROM ' . $this->contribs_table . '
				WHERE contrib_type = ' . ext::TITANIA_TYPE_STYLE . '
					AND contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
					AND contrib_visible = 1';
			$result = $this->db->sql_query($sql);
			$this->total = (int) $this->db->sql_fetchfield('cnt');
			$this->db->sql_freeresult($result);
		}

		return $this->total;
	}

	/**
	 * Get the batch of contributions to process.
	 *
	 * @return array
	 */
	protected function get_batch()
	{
		$sql = 'SELECT contrib_id, contrib_name_clean
			FROM ' . $this->contribs_table . '
			WHERE contrib_type = ' . ext::TITANIA_TYPE_STYLE . '
				AND contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
				AND contrib_visible = 1
			ORDER BY contrib_id';
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.demo.install_all';
	}
}
