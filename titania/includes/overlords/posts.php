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

class posts_overlord
{
	/**
	* Posts array
	* Stores [id] => post row
	*
	* @var array
	*/
	public static $posts = array();

	public static $sort_by = array(
		'a' => array('AUTHOR', 'u.username_clean'),
		't' => array('POST_TIME', 'p.post_time', true),
		's' => array('SUBJECT', 'p.post_subject'),
	);

	/**
	 * Generate the permissions stuff for sql queries to the posts table (handles post_access, post_deleted, post_approved)
	 *
	 * @param <string> $prefix prefix for the query
	 * @param <bool> $where true to use WHERE, false if you already did use WHERE
	 * @return <string>
	 */
	public static function sql_permissions($prefix = 'p.', $where = false)
	{
		$sql = ($where) ? ' WHERE' : ' AND';

		$sql .= " ({$prefix}post_access >= " . titania::$access_level . " OR {$prefix}post_user_id = " . phpbb::$user->data['user_id'] . ')';

		if (!phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$sql .= " AND {$prefix}post_approved = 1";
			$sql .= " AND ({$prefix}post_deleted = 0 OR {$prefix}post_deleted = " . phpbb::$user->data['user_id'] . ')';
		}

		return $sql;
	}

	/**
	 * Load a post
	 *
	 * @param <int|array> $post_id The post_id or array of post_id's
	 */
	public static function load_post($post_id)
	{
		if (!is_array($post_id))
		{
			$post_id = array($post_id);
		}

		// Only get the rows for those we have not gotten already
		$post_id = array_diff($post_id, array_keys(self::$posts));

		if (!sizeof($post_id))
		{
			return;
		}

		$sql_ary = array(
			'SELECT'	=> '*',

			'FROM'		=> array(
				TITANIA_POSTS_TABLE	=> 'p',
			),

			'WHERE'		=> phpbb::$db->sql_in_set('p.post_id', array_map('intval', $post_id)) .
				self::sql_permissions('p.'),
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$posts[$row['post_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	/**
	 * Get the post object
	 *
	 * @param <int> $post_id
	 * @return <object|bool> False if the post does not exist in the self::$posts array (load it first!) post object if it exists
	 */
	public static function get_post_object($post_id)
	{
		if (!isset(self::$posts[$post_id]))
		{
			return false;
		}

		// One can hope...
		$topic = topics_overlord::get_topic_object(self::$posts[$post_id]['topic_id']);

		$post = new titania_post(self::$posts[$post_id]['post_type'], $topic);
		$post->__set_array(self::$posts[$post_id]);

		return $post;
	}

/*
user_post_show_days
user_post_sortby_type
user_post_sortby_dir

$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

// Post ordering options
$limit_post_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

$sort_by_post_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
$sort_by_post_sql = array('a' => 'u.username_clean', 't' => 'p.post_id', 's' => 'p.post_subject');

*/

	/**
	* Do everything we need to display the forum like page
	*
	* @param object $topic the topic object
	*/
	public static function display_topic_complete($topic)
	{
		phpbb::$user->add_lang('viewtopic');

		// Setup the sort tool
		$sort = new titania_sort();
		$sort->set_sort_keys(self::$sort_by);
		if (isset(self::$sort_by[phpbb::$user->data['user_post_sortby_type']]))
		{
			$sort->default_key = phpbb::$user->data['user_post_sortby_type'];
		}
		$sort->default_dir = phpbb::$user->data['user_post_sortby_dir'];

		// Setup the pagination tool
		$pagination = new titania_pagination();
		$pagination->default_limit = phpbb::$config['posts_per_page'];
		$pagination->request();

		// if a post_id was given we must start from the appropriate page
		$post_id = request_var('p', 0);
		if ($post_id)
		{
			$sql = 'SELECT COUNT(p.post_id) as start FROM ' . TITANIA_POSTS_TABLE . ' p
				WHERE p.post_id < ' . $post_id . '
					AND p.topic_id = ' . $topic->topic_id .
					self::sql_permissions('p.') . '
				ORDER BY ' . $sort->get_order_by();
			phpbb::$db->sql_query($sql);
			$start = phpbb::$db->sql_fetchfield('start');
			phpbb::$db->sql_freeresult();

			$pagination->start = ($start > 0) ? (floor($start / $pagination->limit) * $pagination->limit) : 0;
		}

		// check to see if they want to view the latest unread post
		if (request_var('view', '') == 'unread')
		{
			$mark_time = titania_tracking::get_track(TITANIA_TOPIC, $topic->topic_id);

			if ($mark_time > 0)
			{
				$sql = 'SELECT COUNT(p.post_id) as start FROM ' . TITANIA_POSTS_TABLE . ' p
					WHERE p.post_time <= ' . $mark_time . '
						AND p.topic_id = ' . $topic->topic_id .
						self::sql_permissions('p.') . '
					ORDER BY post_time ASC';
				phpbb::$db->sql_query($sql);
				$start = phpbb::$db->sql_fetchfield('start');
				phpbb::$db->sql_freeresult();

				$pagination->start = ($start > 0) ? (floor($start / $pagination->limit) * $pagination->limit) : 0;
			}
		}

/*
user_topic_show_days

$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
*/

		self::display_topic($topic, $sort, $pagination);
		self::assign_common();

		// Display the Quick Reply
		$message = new titania_message($topic);
		$message->display_quick_reply();

		phpbb::$template->assign_vars(array(
			'S_IS_LOCKED'		=> (bool) $topic->topic_locked,
		));
	}

	/**
	* Display topic section for support/tracker/etc
	*
	* @param object $topic The topic object
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	* @param object|boolean $pagination The pagination object (includes/tools/pagination.php)
	*/
	public static function display_topic($topic, $sort = false, $pagination = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_sort_keys(self::$sort_by);
			if (isset(self::$sort_by[phpbb::$user->data['user_post_sortby_type']]))
			{
				$sort->default_key = phpbb::$user->data['user_post_sortby_type'];
			}
			$sort->default_dir = phpbb::$user->data['user_post_sortby_dir'];
		}

		if ($pagination === false)
		{
			// Setup the pagination tool
			$pagination = new titania_pagination();
			$pagination->default_limit = phpbb::$config['posts_per_page'];
			$pagination->request();
		}
		$pagination->result_lang = 'TOTAL_POSTS';

		$sql_ary = array(
			'SELECT'	=> 'p.*',

			'FROM'		=> array(
				TITANIA_POSTS_TABLE => 'p',
			),

			'WHERE'		=> 'p.topic_id = ' . (int) $topic->topic_id .
				self::sql_permissions('p.'),

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		$pagination->sql_count($sql_ary, 'p.post_id');
		$pagination->build_pagination($topic->get_url());

		// Get the data
		$post_ids = $user_ids = array();
		$last_post_time = 0;  // tracking
		$result = phpbb::$db->sql_query_limit($sql, $pagination->limit, $pagination->start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$posts[$row['post_id']] = $row;
			self::$posts[$row['post_id']]['attachments'] = array();

			$post_ids[] = $row['post_id'];
			$user_ids[] = $row['post_user_id'];
			$user_ids[] = $row['post_edit_user'];

			$last_post_time = $row['post_time']; // to set tracking
		}
		phpbb::$db->sql_freeresult($result);

		// Grab any attachments
		if (sizeof($post_ids))
		{
			$sql = 'SELECT * FROM ' . TITANIA_ATTACHMENTS_TABLE . '
				WHERE object_type = ' . (int) $topic->topic_type . '
					AND ' . phpbb::$db->sql_in_set('object_id', array_map('intval', $post_ids));
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				self::$posts[$row['object_id']]['attachments'][] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}

		// Grab the tracking data
		$last_mark_time = titania_tracking::get_track(TITANIA_TOPIC, $topic->topic_id);

		// Store tracking data
		titania_tracking::track(TITANIA_TOPIC, $topic->topic_id, $last_post_time);

		// load the user data
		users_overlord::load($user_ids);

		$post = new titania_post($topic->topic_type, $topic);
		$attachments = new titania_attachment(false, false);

		// Loop de loop
		$prev_post_time = 0;
		foreach ($post_ids as $post_id)
		{
			$post->__set_array(self::$posts[$post_id]);

			$attachments->clear_attachments();
			$attachments->store_attachments(self::$posts[$post_id]['attachments']);

			// Parse attachments before outputting the message
			$parsed_attachments = $attachments->parse_attachments($post->post_text);

			phpbb::$template->assign_block_vars('posts', array_merge(
				$post->assign_details(),
				users_overlord::assign_details($post->post_user_id),
				array(
					'S_FIRST_UNREAD'		=> ($post->post_time >= $last_mark_time && $prev_post_time <= $last_mark_time) ? true : false,
				)
			));
	//S_IGNORE_POST
	//POST_ICON_IMG
	//MINI_POST_IMG

			foreach ($parsed_attachments as $attachment)
			{
				phpbb::$template->assign_block_vars('posts.attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment,
				));
			}

			$prev_post_time = $post->post_time;
		}

		unset($post, $attachments);
	}

	public static function assign_common()
	{
		phpbb::$template->assign_vars(array(
			'REPORT_IMG'		=> phpbb::$user->img('icon_post_report', 'REPORT_POST'),
			'REPORTED_IMG'		=> phpbb::$user->img('icon_topic_reported', 'TOPIC_REPORTED'),
			'UNAPPROVED_IMG'	=> phpbb::$user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
			'WARN_IMG'			=> phpbb::$user->img('icon_user_warn', 'WARN_USER'),

			'EDIT_IMG' 			=> phpbb::$user->img('icon_post_edit', 'EDIT_POST'),
			'DELETE_IMG' 		=> phpbb::$user->img('icon_post_delete', 'DELETE_POST'),
			'INFO_IMG' 			=> phpbb::$user->img('icon_post_info', 'VIEW_INFO'),
			'PROFILE_IMG'		=> phpbb::$user->img('icon_user_profile', 'READ_PROFILE'),
			'SEARCH_IMG' 		=> phpbb::$user->img('icon_user_search', 'SEARCH_USER_POSTS'),
			'PM_IMG' 			=> phpbb::$user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
			'EMAIL_IMG' 		=> phpbb::$user->img('icon_contact_email', 'SEND_EMAIL'),
			'WWW_IMG' 			=> phpbb::$user->img('icon_contact_www', 'VISIT_WEBSITE'),
			'ICQ_IMG' 			=> phpbb::$user->img('icon_contact_icq', 'ICQ'),
			'AIM_IMG' 			=> phpbb::$user->img('icon_contact_aim', 'AIM'),
			'MSN_IMG' 			=> phpbb::$user->img('icon_contact_msnm', 'MSNM'),
			'YIM_IMG' 			=> phpbb::$user->img('icon_contact_yahoo', 'YIM'),
			'JABBER_IMG'		=> phpbb::$user->img('icon_contact_jabber', 'JABBER') ,
		));
	}

	/**
	 * Find the next or previous post id in a topic
	 *
	 * @param <type> $topic_id the topic_id of the current item
	 * @param <type> $post_id the post_id of the current item
	 * @param <string> $dir the direction (next, prev)
	 * @param <bool> $try_other_dir Try the other direction if we can not find one
	 * @return <int> $post_id the requested id
	 */
	public static function next_prev_post_id($topic_id, $post_id, $dir = 'next', $try_other_dir = true)
	{
		$sql_ary = array(
			'SELECT'	=> 'post_id',

			'FROM'		=> array(
				TITANIA_POSTS_TABLE	=> 'p',
			),

			'WHERE'		=> 'p.topic_id = ' . (int) $topic_id . '
				AND p.post_id ' . (($dir == 'next') ? '> ' : '< ') . (int) $post_id .
				self::sql_permissions('p.'),

			'ORDER_BY'	=> 'p.post_id ' . (($dir == 'next') ? 'ASC' : 'DESC'),
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		phpbb::$db->sql_query_limit($sql, 1);
		$post_id = phpbb::$db->sql_fetchfield('post_id');
		phpbb::$db->sql_freeresult();

		if ($post_id == false && $try_other_dir)
		{
			// Could not find one in the direction we were going...try the other direction...
			return self::next_prev_post_id($topic_id, $post_id, (($dir == 'next') ? 'prev' : 'next'), false);
		}

		return $post_id;
	}
}