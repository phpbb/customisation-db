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

class rebuild_topic_urls
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var array */
	protected $contrib_types;

	/** @var string */
	protected $queue_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $topics_table;

	/**
	* Constructor.
	*/
	public function __construct()
	{
		$this->db = \phpbb::$db;
		$this->user = \phpbb::$user;
		$this->template = \phpbb::$template;
		$this->request = \phpbb::$request;
		$this->controller_helper = \phpbb::$container->get('phpbb.titania.controller.helper');

		$this->contrib_types = \titania_types::$types;
		$this->contribs_table = TITANIA_CONTRIBS_TABLE;
		$this->queue_table = TITANIA_QUEUE_TABLE;
		$this->posts_table = TITANIA_POSTS_TABLE;
		$this->topics_table = TITANIA_TOPICS_TABLE;
	}

	/**
	* Get tool display options.
	*
	* @return string
	*/
	public function display_options()
	{
		return 'REBUILD_TOPIC_URLS';
	}

	/**
	* Run tool.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function run_tool()
	{
		$type = $this->request->variable('type', 'queue');
		$start = $this->request->variable('start', 0);
		$continue_start = null;

		switch ($type)
		{
			case 'queue':
				$continue_start = $this->sync_queue_topics($start);

				if ($continue_start === null)
				{
					$type = 'contrib';
					$continue_start = 0;
				}
			break;

			case 'contrib':
				$continue_start = $this->sync_contrib_topics($start);
			break;
		}

		if ($continue_start !== null)
		{
			$params = array(
				'tool'		=> 'rebuild_topic_urls',
				'type'		=> $type,
				'submit'	=> 1,
				'hash'		=> generate_link_hash('manage'),
				'start'		=> $continue_start,
			);
			meta_refresh(2, $this->controller_helper->route('phpbb.titania.administration.tool', $params));
		}

		$msg = ($continue_start !== null) ? 'PLEASE_WAIT_FOR_TOOL' : 'DONE';
		$this->template->assign_vars(array(
			'MESSAGE_TEXT'	=> $this->user->lang($msg),
			'MESSAGE_TITLE'	=> $this->user->lang('INFORMATION'),
		));
		return $this->controller_helper->render('message_body.html', $msg);
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
	* @return null
	*/
	protected function sync_queue_topics($start)
	{
		$i = 0;
		$limit = 250;

		$sql = 'SELECT queue_id, queue_topic_id
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_topic_id <> 0';
		$result = $this->db->sql_query($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$url = serialize(array(
				'id'	=> (int) $row['queue_id'],
			));

			$where = 'topic_id = ' . (int) $row['queue_topic_id'];

			$this->update_field($this->topics_table, 'topic', $url, $where);
			$this->update_field($this->posts_table, 'post', $url, $where);
			$i++;
		}
		$this->db->sql_freeresult($result);

		if ($i == $limit)
		{
			return $start + $limit;
		}
	}

	/**
	* Synchronize queue topic url values.
	*
	* @return null
	*/
	protected function sync_contrib_topics($start)
	{
		$i = 0;
		$limit = 250;

		$topic_type_where = $this->db->sql_in_set('topic_type',
			array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION)
		);

		$sql = 'SELECT contrib_id, contrib_type, contrib_name_clean
			FROM ' . $this->contribs_table;
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$url = serialize(array(
				'contrib_type'	=> $this->contrib_types[$row['contrib_type']]->url,
				'contrib'		=> $row['contrib_name_clean'],
			));
			$where = 'parent_id = ' . (int) $row['contrib_id'] . '
				AND ' . $topic_type_where;

			$this->update_field($this->topics_table, 'topic', $url, $where);
			$i++;
		}
		$this->db->sql_freeresult();

		$sql = "SELECT topic_id, topic_url
			FROM {$this->topics_table}
			WHERE $topic_type_where";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$where = 'topic_id = ' . (int) $row['topic_id'];
			$this->update_field($this->posts_table, 'post', $row['topic_url'], $where);
		}
		$this->db->sql_freeresult($result);

		if ($i === $limit)
		{
			return $start + $limit;
		}
	}
}
