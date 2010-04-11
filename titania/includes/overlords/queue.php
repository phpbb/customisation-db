<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

class queue_overlord
{
	/**
	* Queue array
	* Stores [id] => row
	*
	* @var array
	*/
	public static $queue = array();

	public static $sort_by = array(
		't' => array('SUBMIT_TIME', 'q.queue_submit_time', true),
	);

	/**
	* Load queue(s) from queue id(s)
	*
	* @param int|array $queue_id queue_id or an array of queue_ids
	*/
	public static function load_queue($queue_id)
	{
		if (!is_array($queue_id))
		{
			$queue_id = array($queue_id);
		}

		// Only get the rows for those we have not gotten already
		$queue_id = array_diff($queue_id, array_keys(self::$queue));

		if (!sizeof($queue_id))
		{
			return;
		}

		$sql_ary = array(
			'SELECT' => 'q.*',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE	=> 'q',
			),

			'WHERE' => phpbb::$db->sql_in_set('q.queue_id', array_map('intval', $queue_id))
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$queue[$row['queue_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	public static function get_queue_object($queue_id, $query = false)
	{
		if (!isset(self::$queue[$queue_id]) && $query)
		{
			self::load_queue($queue_id);
		}

		if (!isset(self::$queue[$queue_id]))
		{
			return false;
		}

		$queue = new titania_queue;
		$queue->__set_array(self::$queue[$queue_id]);

		return $queue;
	}

	/**
	* Display forum-like list for queue
	*
	* @param string $type The type of queue (the contrib type)
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	*/
	public static function display_queue($type, $queue_status = TITANIA_QUEUE_NEW, $sort = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_sort_keys(self::$sort_by);
			$sort->default_limit = phpbb::$config['topics_per_page'];
			$sort->request();
		}

		$queue_ids = array();

		$sql_ary = array(
			'SELECT' => '*',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE		=> 'q',
				TITANIA_CONTRIBS_TABLE	=> 'c',
				TITANIA_REVISIONS_TABLE	=> 'r',
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => 'q.queue_type = ' . (int) $type .
				(($queue_status) ? ' AND q.queue_status = ' . (int) $queue_status : ' AND q.queue_status > 0 ') . '
				AND c.contrib_id = q.contrib_id
				AND r.revision_id = q.revision_id
				AND t.topic_id = q.queue_topic_id',

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		titania_tracking::get_track_sql($sql_ary, TITANIA_TOPIC, 't.topic_id');

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if (!$sort->sql_count($sql_ary, 'q.queue_id'))
		{
			// No results...no need to query more...
			return;
		}

		$sort->build_pagination(titania_url::$current_page, titania_url::$params);

		$queue_ids = $user_ids = array();

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Store the tracking info we grabbed from the DB
			titania_tracking::store_from_db($row);

			$queue_ids[] = $row['queue_id'];
			$user_ids[] = $row['topic_first_post_user_id'];
			$user_ids[] = $row['topic_last_post_user_id'];
			$user_ids[] = $row['submitter_user_id'];

			self::$queue[$row['queue_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		users_overlord::load_users($user_ids);

		$topic = new titania_topic;

		foreach ($queue_ids as $queue_id)
		{
			$row = self::$queue[$queue_id];

			$topic->__set_array($row);

			phpbb::$template->assign_block_vars('topics', array_merge($topic->assign_details(), array(
				'TOPIC_SUBJECT'				=> $row['contrib_name'] . ' - ' . $row['revision_version'],
				'S_TOPIC_PROGRESS'			=> ($row['queue_progress']) ? true : false,
			)));
		}

		unset($topic);

		phpbb::$template->assign_vars(array(
			'S_TOPIC_LIST'		=> true,
		));

		// Assign common stuff for topics list
		topics_overlord::assign_common();
	}

	/**
	* Display a single queue item
	*
	* @param int $queue_id
	*/
	public static function display_queue_item($queue_id)
	{
		titania::add_lang('contributions');

		$sql_ary = array(
			'SELECT' => '*',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE		=> 'q',
				TITANIA_REVISIONS_TABLE	=> 'r',
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => 'q.queue_id = ' . (int) $queue_id . '
				AND r.revision_id = q.revision_id
				AND t.topic_id = q.queue_topic_id',
		);

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('NO_QUEUE_ITEM');
		}

		// Load the contribution
		$contrib = new titania_contribution();
		$contrib->load((int) $row['contrib_id']);
		$contrib->get_download($row['revision_id']);
		$contrib->get_revisions();
		$contrib->get_screenshots();
		$contrib->assign_details();

		// Load the topic
		$topic = new titania_topic;
		$topic->__set_array($row);

		// Display the posts
		posts_overlord::display_topic_complete($topic);

		// Some quick-actions
		$quick_actions = array();
		if ($row['queue_status'] > 0)
		{
			if (!$row['mpv_results'] && titania_types::$types[$contrib->contrib_type]->mpv_test)
			{
				$quick_actions['RETEST_MPV'] = titania_url::build_url('', array('action' => 'mpv', 'revision' => $row['revision_id']));
			}
			if (!$row['automod_results'] && titania_types::$types[$contrib->contrib_type]->automod_test)
			{
				$quick_actions['RETEST_AUTOMOD'] = titania_url::build_url('', array('action' => 'automod', 'revision' => $row['revision_id']));
			}

			if ($row['queue_progress'] == phpbb::$user->data['user_id'])
			{
				$quick_actions['MARK_NO_PROGRESS'] = titania_url::append_url(titania_url::$current_page_url, array('action' => 'no_progress'));
			}
			else if (!$row['queue_progress'])
			{
				$quick_actions['MARK_IN_PROGRESS'] = titania_url::append_url(titania_url::$current_page_url, array('action' => 'in_progress'));
			}

			$quick_actions['CHANGE_STATUS'] = titania_url::append_url(titania_url::$current_page_url, array('action' => 'move'));

			$quick_actions['REPACK'] = titania_url::append_url($contrib->get_url('revision'), array('repack' => $row['revision_id']));

			$quick_actions['ALTER_NOTES'] = titania_url::append_url(titania_url::$current_page_url, array('action' => 'notes'));

			if (titania_types::$types[$contrib->contrib_type]->acl_get('validate'))
			{
				$quick_actions['APPROVE'] = titania_url::append_url(titania_url::$current_page_url, array('action' => 'approve'));
				$quick_actions['DENY'] = titania_url::append_url(titania_url::$current_page_url, array('action' => 'deny'));
			}
		}

		phpbb::$template->assign_vars(array(
			'QUICK_ACTIONS'				=> titania::build_quick_actions($quick_actions),

			'S_DISPLAY_CONTRIBUTION'	=> true,
			'S_IN_QUEUE'				=> true,

			'U_POST_REPLY'				=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'reply', 't' => $topic->topic_id)),
			'U_NEW_REVISION'			=> false, // Prevent nubs from trying to submit a new revision when they want to actually repack
		));

		// Subscriptions
		titania_subscriptions::handle_subscriptions(TITANIA_TOPIC, $topic->topic_id, titania_url::$current_page_url);
	}

	/**
	* Display the categories (tags)
	*
	* @param int $type
	*/
	public static function display_categories($type, $selected = false)
	{
		$tags = titania::$cache->get_tags(TITANIA_QUEUE);
		$tag_count = array();
		$total = 0;

		$sql = 'SELECT queue_status, COUNT(queue_id) AS cnt FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . (int) $type . '
			GROUP BY queue_status';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$total += ($row['queue_status'] > 0) ? $row['cnt'] : 0;
			$tag_count[$row['queue_status']] = $row['cnt'];
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$template->assign_block_vars('queue_tags', array(
			'TAG_NAME'		=> phpbb::$user->lang['ALL'],
			'TAG_COUNT'		=> $total,
			'U_VIEW_TAG'	=> titania_url::append_url(titania_url::$current_page_url, array('tag' => 'all')),
			'S_SELECTED'	=> ($selected == 0) ? true : false,
		));

		foreach ($tags as $tag_id => $row)
		{
			if (!isset($tag_count[$tag_id]))
			{
				// Hide empty ones
				continue;
			}

			phpbb::$template->assign_block_vars('queue_tags', array(
				'TAG_NAME'		=> (isset(phpbb::$user->lang[$row['tag_field_name']])) ? phpbb::$user->lang[$row['tag_field_name']] : $row['tag_field_name'],
				'TAG_COUNT'		=> $tag_count[$tag_id],
				'U_VIEW_TAG'	=> titania_url::append_url(titania_url::$current_page_url, array('tag' => $tag_id)),
				'S_SELECTED'	=> ($selected == $tag_id) ? true : false,
			));
		}
	}

	/**
	* Generate topic status
	*/
	public static function folder_img($is_unread, &$folder_img, &$folder_alt, $replies = 0)
	{
		titania::_include('functions_display', 'titania_topic_folder_img');

		titania_topic_folder_img($folder_img, $folder_alt, $replies, $is_unread);
	}
}
