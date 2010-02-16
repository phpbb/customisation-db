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
				TITANIA_CONTRIBS_TABLE	=> 'c',
			),

			'WHERE' => phpbb::$db->sql_in_set('q.queue_id', array_map('intval', $queue_id)) . '
				AND c.contrib_id = q.contrib_id'
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$queue[$row['queue_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Display forum-like list for queue
	*
	* @param string $type The type of queue (the contrib type)
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	* @param object|boolean $pagination The pagination object (includes/tools/pagination.php)
	*/
	public static function display_queue($type, $queue_status = TITANIA_QUEUE_NEW, $sort = false, $pagination = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_sort_keys(self::$sort_by);
			$sort->default_dir = 'a';
		}

		if ($pagination === false)
		{
			// Setup the pagination tool
			$pagination = new titania_pagination();
			$pagination->default_limit = phpbb::$config['topics_per_page'];
			$pagination->request();
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

			'WHERE' => 'q.queue_type = ' . (int) $type . '
				AND q.queue_status = ' . (int) $queue_status . '
				AND c.contrib_id = q.contrib_id
				AND r.revision_id = q.revision_id
				AND t.topic_id = q.queue_topic_id',

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		titania_tracking::get_track_sql($sql_ary, TITANIA_QUEUE, 'q.queue_id');

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		$pagination->sql_count($sql_ary, 'q.queue_id');
		$pagination->build_pagination(titania_url::$current_page, titania_url::$params);

		$queue_ids = $user_ids = array();

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $pagination->limit, $pagination->start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Store the tracking info we grabbed in the tool
			if (isset($row['track_time']))
			{
				titania_tracking::store_track(TITANIA_QUEUE, $row['queue_id'], $row['track_time']);
			}

			$queue_ids[] = $row['queue_id'];
			$user_ids[] = $row['topic_first_post_user_id'];
			$user_ids[] = $row['topic_last_post_user_id'];
			$user_ids[] = $row['submitter_user_id'];

			self::$queue[$row['queue_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		users_overlord::load_users($user_ids);

		foreach ($queue_ids as $queue_id)
		{
			phpbb::$template->assign_block_vars('topics', self::assign_details($queue_id, true));
		}

		phpbb::$template->assign_vars(array(
			'S_TOPIC_LIST'		=> true,
		));

		// Assign common stuff for topics list
		topics_overlord::assign_common();
	}

	public static function display_queue_item($queue_id)
	{
		titania::add_lang('contributions');

		$sql_ary = array(
			'SELECT' => '*',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE		=> 'q',
				TITANIA_REVISIONS_TABLE	=> 'r',
			),

			'WHERE' => 'q.queue_id = ' . (int) $queue_id . '
				AND r.revision_id = q.revision_id',
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
		$contrib->assign_details();

		// Load the topic
		$topic = new titania_topic();
		$topic->contrib = $contrib;
		$topic->topic_id = $row['queue_topic_id'];
		$topic->load();

		// Display the posts
		posts_overlord::display_topic_complete($topic);

		phpbb::$template->assign_vars(array(
			'S_DISPLAY_CONTRIBUTION'	=> true,

			'U_POST_REPLY'				=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'reply', 't' => $topic->topic_id)),
		));
	}

	/**
	* assign details
	*
	* @param array $db_data Data from the database for this queue item (containing the contrib and revision data)
	*/
	public static function assign_details($queue_id)
	{
		$row = self::$queue[$queue_id];

		$replies = 1;
		$flags = titania_count::get_flags(titania::$access_level);
		$replies = titania_count::from_db($row['topic_posts'], $flags);

		$is_unread = titania_tracking::is_unread(TITANIA_QUEUE, $row['queue_id'], $row['queue_submit_time']);
		$folder_img = $folder_alt = '';
		self::folder_img($is_unread, $folder_img, $folder_alt, $replies);

		$output = array(
			'TOPIC_SUBJECT'				=> $row['contrib_name'] . ' - ' . $row['revision_version'],
			'TOPIC_REPLIES'				=> ($replies - 1),
			'TOPIC_VIEWS'				=> $row['topic_views'],

			'TOPIC_FIRST_POST_USER_FULL'	=> users_overlord::get_user($row['submitter_user_id'], '_full'),
			'TOPIC_FIRST_POST_TIME'			=> phpbb::$user->format_date($row['queue_submit_time']),
			'TOPIC_LAST_POST_USER_FULL'		=> users_overlord::get_user($row['topic_last_post_user_id'], '_full'),
			'TOPIC_LAST_POST_TIME'			=> phpbb::$user->format_date($row['topic_last_post_time']),

			'U_VIEW_TOPIC'				=> titania_url::append_url(titania_url::$current_page_url, array('q' => $row['queue_id'])),
			'U_VIEW_CONTRIB'			=> titania_url::build_url(titania_types::$types[$row['queue_type']]->url . '/' . $row['contrib_name_clean'] . '/'),
			'U_VIEW_LAST_POST'			=> titania_url::append_url(titania_url::$current_page_url, array('p' => $row['topic_last_post_id'], '#p' => $row['topic_last_post_id'])),

			'S_UNREAD'					=> ($is_unread) ? true : false,

			'TOPIC_FOLDER_IMG'			=> phpbb::$user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_SRC'		=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
			'TOPIC_FOLDER_IMG_ALT'		=> phpbb::$user->lang[$folder_alt],
			'TOPIC_FOLDER_IMG_WIDTH'	=> phpbb::$user->img($folder_img, '', false, '', 'width'),
			'TOPIC_FOLDER_IMG_HEIGHT'	=> phpbb::$user->img($folder_img, '', false, '', 'height'),
		);

		return $output;
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
