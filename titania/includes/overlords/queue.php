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
		't' => array('POST_TIME', 'q.queue_submit_time', true),
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
			$sort->default_dir = phpbb::$user->data['user_topic_sortby_dir'];
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
				TITANIA_QUEUE_TABLE	=> 'q',
				TITANIA_CONTRIBS_TABLE	=> 'c',
				TITANIA_REVISIONS_TABLE	=> 'r',
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => 'q.queue_type = ' . (int) $type . '
				AND c.contrib_id = q.contrib_id
				AND r.revision_id = q.revision_id
				AND q.queue_status = ' . (int) $queue_status,

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
			$user_ids[] = $row['submitter_user_id'];

			self::$queue[$row['queue_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		users_overlord::load_users($user_ids);

		foreach ($queue_ids as $queue_id)
		{
			phpbb::$template->assign_block_vars('topics', array_merge(
				self::assign_details($queue_id, true),
				users_overlord::assign_details(self::$queue[$queue_id]['submitter_user_id'])
			));
		}

		phpbb::$template->assign_vars(array(
			'S_TOPIC_LIST'		=> true,
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

		$is_unread = titania_tracking::is_unread(TITANIA_QUEUE, $row['queue_id'], $row['queue_submit_time']);
		$folder_img = $folder_alt = '';
		self::folder_img($is_unread, $folder_img, $folder_alt);

		$output = array(
			'TOPIC_SUBJECT'				=> $row['contrib_name'] . ' - ' . $row['revision_version'],

			'U_VIEW_TOPIC'				=> phpbb::append_sid('viewtopic', 't=' . $row['queue_topic_id']),
			'U_VIEW_CONTRIB'			=> titania_url::build_url(titania_types::$types[$row['queue_type']]->url . '/' . $row['contrib_name_clean'] . '/'),

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
	public static function folder_img($is_unread, &$folder_img, &$folder_alt)
	{
		titania::_include('functions_display', 'titania_topic_folder_img');

		titania_topic_folder_img($folder_img, $folder_alt, 0, $is_unread);
	}
}
