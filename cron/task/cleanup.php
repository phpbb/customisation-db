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

namespace phpbb\titania\cron\task;

use phpbb\titania\ext;

class cleanup extends \phpbb\cron\task\base
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\attachment\operator */
	protected $attachments;

	/** @var string */
	protected $ext_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\attachment\operator $attachments
	 * @param $ext_root_path
	 * @param $php_ext
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\titania\config\config $ext_config, \phpbb\titania\attachment\operator $attachments, $ext_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->ext_config = $ext_config;
		$this->attachments = $attachments;
		$this->ext_root_path = $ext_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Check whether the task can run.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return $this->ext_config->cleanup_titania;
	}

	/**
	 * Check whether the cron task should run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return (time() - (int) $this->config['titania_last_cleanup']) > (3600 * 6);
	}

	/**
	 * Run cron task
	 */
	public function run()
	{
		require($this->ext_root_path . 'common.' . $this->php_ext);

		$this->config->set('titania_last_cleanup', time(), true);

		$revisions = $this->get_incomplete_revisions();
		$this->delete_revisions(array_keys($revisions));
		$attachments = array_merge(
			$this->get_orphan_attachments(),
			$this->get_revision_attachments($revisions)
		);

		$this->delete_attachments($attachments);
	}

	/**
	 * Get revisions that were not submitted completely.
	 *
	 * @return array
	 */
	public function get_incomplete_revisions()
	{
		$revisions = array();
		$time_limit = time() - (3600 * 4);

		// Select revisions that were stopped at one of the submission steps
		$sql = 'SELECT revision_id, attachment_id
			FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE revision_submitted = 0
				AND revision_time < ' . $time_limit; // Unlikely to happen, but set a time limit to ensure that we don't remove revisions that may be in the process of being submitted.
		$result = $this->db->sql_query_limit($sql, 25);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$revisions[(int) $row['revision_id']] = (int) $row['attachment_id'];
		}
		$this->db->sql_freeresult($result);

		return $revisions;
	}

	/**
	 * Delete attachments
	 *
	 * @param array $attachments	Array of attachments
	 */
	protected function delete_attachments(array $attachments)
	{
		$this->attachments
			->store($attachments)
			->delete_all();
	}

	/**
	 * Get attachments
	 *
	 * @param string $conditions	SQL conditions
	 * @return array
	 */
	protected function get_attachments($conditions)
	{
		$sql = 'SELECT *
			FROM ' . TITANIA_ATTACHMENTS_TABLE . "
			WHERE $conditions";
		$result = $this->db->sql_query_limit($sql, 25);
		$attachments = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $attachments;
	}

	/**
	 * Get revision attachments
	 *
	 * @param array $ids	Attachment id's
	 * @return array
	 */
	protected function get_revision_attachments(array $ids)
	{
		if (empty($ids))
		{
			return array();
		}

		return $this->get_attachments(
			'object_type = ' . ext::TITANIA_CONTRIB . '
				AND ' . $this->db->sql_in_set('attachment_id', $ids)
		);
	}

	/**
	 * Get orphan attachments
	 *
	 * @return array
	 */
	protected function get_orphan_attachments()
	{
		$time_limit = time() - (3600 * 4);

		return $this->get_attachments(
			'object_type <> ' . ext::TITANIA_CONTRIB . '
				AND is_orphan = 1 AND filetime < ' . $time_limit
		);
	}

	/**
	 * Delete revisions
	 *
	 * @param array $revisions
	 */
	protected function delete_revisions(array $revisions)
	{
		if (!empty($revisions))
		{
			$sql = 'DELETE
				FROM ' . TITANIA_REVISIONS_TABLE . '
				WHERE ' . $this->db->sql_in_set('revision_id', $revisions);
			$this->db->sql_query($sql);

			$sql = 'DELETE
				FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
				WHERE ' . $this->db->sql_in_set('revision_id', $revisions);
			$this->db->sql_query($sql);
		}
	}
}
