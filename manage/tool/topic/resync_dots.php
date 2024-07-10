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

namespace phpbb\titania\manage\tool\topic;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\db\sql_insert_buffer;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\manage\tool\base;
use phpbb\user;
use Symfony\Component\Console\Helper\ProgressBar;

class resync_dots extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $topics_posted_table;

	/** @var int */
	protected $total;

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param user $user
	 * @param ext_config $ext_config
	 */
	public function __construct(db_driver_interface $db, user $user, ext_config $ext_config)
	{
		$this->db = $db;
		$this->user = $user;
		$table_prefix = $ext_config->__get('table_prefix');
		$this->posts_table = $table_prefix . 'posts';
		$this->topics_posted_table = $table_prefix . 'topics_posted';
		$this->set_limit(5);
	}

	/**
	 * Get total.
	 *
	 * @return int
	 */
	public function get_total()
	{
		if ($this->total === null)
		{
			$sql = 'SELECT COUNT(DISTINCT topic_id, post_user_id) AS cnt
				FROM ' . $this->posts_table . '
				WHERE post_approved = 1
					AND post_deleted = 0';
				$result = $this->db->sql_query($sql);
			$this->total = (int) $this->db->sql_fetchfield('cnt');
			$this->db->sql_freeresult($result);
		}

		return $this->total;
	}

	/**
	 * Get batch.
	 *
	 * @return array
	 */
	protected function get_batch()
	{
		$sql = 'SELECT DISTINCT topic_id, post_user_id
			FROM ' . $this->posts_table . '
			WHERE post_approved = 1
				AND post_deleted = 0';
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Run the tool
	 *
	 * @param ProgressBar|null $progress
	 * @return array
	 */
	public function run($progress = null)
	{
		if ($this->start == 0)
		{
			$this->db->sql_query('TRUNCATE ' . $this->topics_posted_table);
		}

		$insert_buffer = new sql_insert_buffer($this->db, $this->topics_posted_table);
		$batch = $this->get_batch();
		$total = $this->get_total();

		foreach ($batch as $row)
		{
			$insert_buffer->insert(array(
				'topic_id'		=> (int) $row['topic_id'],
				'user_id'		=> (int) $row['post_user_id'],
				'topic_posted'	=> 1
			));

			if ($progress)
			{
				$progress->advance();
			}
		}

		$insert_buffer->flush();
		$next_batch = $this->start + $this->limit;

		if ($next_batch >= $total)
		{
			return $this->get_result('RESYNC_DOTTED_TOPICS_COMPLETE', $total, false);
		}

		return $this->get_result(
			$this->user->lang('TOOL_PROGRESS_TOTAL', $next_batch, $total),
			$total,
			$next_batch
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.topic.resync_dots';
	}
}
