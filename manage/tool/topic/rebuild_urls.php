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
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\manage\tool\base;
use phpbb\user;
use Symfony\Component\Console\Helper\ProgressHelper;

class rebuild_urls extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var type_collection */
	protected $types;

	/** @var string */
	protected $queue_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $topics_table;

	/** @var array */
	protected $steps = array(
		'queue',
		'contrib',
	);

	/**
	 * Constructor.
	 *
	 * @param db_driver_interface $db
	 * @param user $user
	 * @param type_collection $types
	 * @param ext_config $ext_config
	 */
	public function __construct(db_driver_interface $db, user $user, type_collection $types, ext_config $ext_config)
	{
		$this->db = $db;
		$this->user = $user;
		$this->types = $types;
		$table_prefix = $ext_config->__get('table_prefix');
		$this->contribs_table = $table_prefix . 'contribs';
		$this->queue_table = $table_prefix . 'queue';
		$this->posts_table = $table_prefix . 'posts';
		$this->topics_table = $table_prefix . 'topics';
		$this->set_limit(500);
	}

	/**
	 * Run tool.
	 *
	 * @param ProgressHelper|null $progress
	 * @return array
	 */
	public function run($progress = null)
	{
		$next_batch = false;

		switch ($this->get_step())
		{
			case 'queue':
				$next_batch = $this->sync_queue_topics($progress);
			break;

			case 'contrib':
				$next_batch = $this->sync_contrib_topics($progress);
			break;
		}

		if ($next_batch)
		{
			return $this->get_result(
				$this->user->lang('TOOL_PROGRESS', $next_batch),
				null,
				$next_batch,
				$this->get_step()
			);
		}
		$next_step = $this->get_next_step();

		if ($next_step)
		{
			$next_batch = 0;
			$msg = 'PLEASE_WAIT';
		}
		else
		{
			$msg = 'DONE';
			$next_batch = false;
		}

		return $this->get_result(
			$this->user->lang($msg),
			null,
			$next_batch,
			$next_step
		);
	}

	/**
	 * Update url field.
	 *
	 * @param string $table
	 * @param string $field_prefix
	 * @param string $value
	 * @param string $where
	 *
	 * @return null
	 */
	protected function update_field($table, $field_prefix, $value, $where)
	{
		$sql = "UPDATE $table
			SET {$field_prefix}_url = '" . $this->db->sql_escape($value) . "'
			WHERE $where";
		$this->db->sql_query($sql);
	}

	/**
	 * Synchronize queue topic url values.
	 *
	 * @return int|bool
	 */
	protected function sync_queue_topics($progress = null)
	{
		$i = 0;

		$sql = 'SELECT queue_id, queue_topic_id
			FROM ' . $this->queue_table . '
			WHERE queue_topic_id <> 0';
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$url = serialize(array(
				'id'	=> (int) $row['queue_id'],
			));

			$where = 'topic_id = ' . (int) $row['queue_topic_id'];

			$this->update_field($this->topics_table, 'topic', $url, $where);
			$this->update_field($this->posts_table, 'post', $url, $where);
			$i++;

			if ($progress)
			{
				$progress->advance();
			}
		}
		$this->db->sql_freeresult($result);

		if ($i == $this->limit)
		{
			return $this->start + $this->limit;
		}
		return false;
	}

	/**
	 * Synchronize queue topic url values.
	 *
	 * @return bool|int
	 */
	protected function sync_contrib_topics($progress)
	{
		$i = 0;

		$topic_type_where = $this->db->sql_in_set('topic_type',
			array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION)
		);

		$sql = 'SELECT contrib_id, contrib_type, contrib_name_clean
			FROM ' . $this->contribs_table;
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$url = serialize(array(
				'contrib_type'	=> $this->types->get($row['contrib_type'])->url,
				'contrib'		=> $row['contrib_name_clean'],
			));
			$where = 'parent_id = ' . (int) $row['contrib_id'] . '
				AND ' . $topic_type_where;

			$this->update_field($this->topics_table, 'topic', $url, $where);
			$i++;
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT topic_id, topic_url
			FROM {$this->topics_table}
			WHERE $topic_type_where";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$where = 'topic_id = ' . (int) $row['topic_id'];
			$this->update_field($this->posts_table, 'post', $row['topic_url'], $where);

			if ($progress)
			{
				$progress->advance();
			}
		}
		$this->db->sql_freeresult($result);

		if ($i === $this->limit)
		{
			return $this->start + $this->limit;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.topic.rebuild_urls';
	}
}
