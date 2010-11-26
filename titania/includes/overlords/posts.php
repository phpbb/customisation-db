<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
		$sort = self::build_sort();
		$sort->request();

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

			$sort->start = ($start > 0) ? (floor($start / $sort->limit) * $sort->limit) : 0;
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

				$sort->start = ($start > 0) ? (floor($start / $sort->limit) * $sort->limit) : 0;
			}
		}

/*
user_topic_show_days

$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
*/

		self::display_topic($topic, $sort);
		self::assign_common();

		// Build Quick Actions
		if ($topic->topic_type != TITANIA_QUEUE)
		{
			self::build_quick_actions($topic);
		}

		// Display the Quick Reply
		$post_object = new titania_post($topic->topic_type, $topic);
		if ($post_object->acl_get('reply'))
		{
			$message = new titania_message($topic);
			$message->display_quick_reply();
		}

		phpbb::$template->assign_vars(array(
			'S_IS_LOCKED'		=> (bool) $topic->topic_locked,
		));
	}

	/**
	* Display topic section for support/tracker/etc
	*
	* @param object $topic The topic object
	* @param titania_sort $sort The sort object (includes/tools/sort.php)
	*/
	public static function display_topic($topic, $sort = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = self::build_sort();
		}
		$sort->request();

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
		if (!$sort->sql_count($sql_ary, 'p.post_id'))
		{
			// No results...no need to query more...
			return;
		}

		$sort->build_pagination($topic->get_url());

		// Get the data
		$post_ids = $user_ids = array();
		$last_post_time = 0;  // tracking
		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$posts[$row['post_id']] = $row;
			self::$posts[$row['post_id']]['attachments'] = array();

			$post_ids[] = $row['post_id'];
			$user_ids[] = $row['post_user_id'];
			$user_ids[] = $row['post_edit_user'];
			$user_ids[] = $row['post_delete_user'];

			$last_post_time = $row['post_time']; // to set tracking
		}
		phpbb::$db->sql_freeresult($result);

		// Grab the tracking data
		$last_mark_time = titania_tracking::get_track(TITANIA_TOPIC, $topic->topic_id);

		// Store tracking data
		titania_tracking::track(TITANIA_TOPIC, $topic->topic_id, $last_post_time);

		// load the user data
		users_overlord::load($user_ids);

		phpbb::_include('functions_profile_fields', false, 'custom_profile');
		$cp = new custom_profile();
		$post = new titania_post($topic->topic_type, $topic);
		$attachments = new titania_attachment($topic->topic_type, false);

		// Grab all attachments
		$attachments_set = $attachments->load_attachments_set($post_ids);

		// Loop de loop
		$prev_post_time = 0;
		foreach ($post_ids as $post_id)
		{
			$post->__set_array(self::$posts[$post_id]);

			$attachments->clear_attachments();

			if (isset($attachments_set[$post_id]))
			{
				$attachments->store_attachments($attachments_set[$post_id]);
			}

			// Parse attachments before outputting the message
			$message = $post->generate_text_for_display();
			$parsed_attachments = $attachments->parse_attachments($message);

			// Prepare message text for use in javascript
			$message_decoded = censor_text($post->post_text);
			titania_decode_message($message_decoded, $post->post_text_uid);
			$message_decoded = bbcode_nl2br($message_decoded);

			// Build CP Fields
			$cp_row = array();
			if (isset(users_overlord::$cp_fields[$post->post_user_id]))
			{
				$cp_row = $cp->generate_profile_fields_template('show', false, users_overlord::$cp_fields[$post->post_user_id]);
			}
			$cp_row['row'] = (isset($cp_row['row']) && sizeof($cp_row['row'])) ? $cp_row['row'] : array();

			phpbb::$template->assign_block_vars('posts', array_merge(
				$post->assign_details(false),
				users_overlord::assign_details($post->post_user_id),
				$cp_row['row'],
				array(
					'POST_TEXT'				=> $message,
					'POST_TEXT_DECODED'		=> $message_decoded,
					'U_MINI_POST'			=> titania_url::append_url($topic->get_url(), array('p' => $post_id, '#p' => $post_id)),
					'MINI_POST_IMG'			=> ($post->post_time > $last_mark_time) ? phpbb::$user->img('icon_post_target_unread', 'NEW_POST') : phpbb::$user->img('icon_post_target', 'POST'),
					'S_FIRST_UNREAD'		=> ($post->post_time > $last_mark_time && $prev_post_time <= $last_mark_time) ? true : false,
				)
			));

			// Output CP Fields
			if (!empty($cp_row['blockrow']))
			{
				foreach ($cp_row['blockrow'] as $field_data)
				{
					phpbb::$template->assign_block_vars('posts.custom_fields', $field_data);
				}
			}
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

		// Increment the topic view count
		$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
			SET topic_views = topic_views + 1
			WHERE topic_id = ' . (int) $topic->topic_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	* Build the quick moderation actions for output for this topic
	*
	* @param mixed $topic
	*/
	public static function build_quick_actions($topic)
	{
		// Auth check
		$is_authed = false;
		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$is_authed = true;
		}
		else if (phpbb::$auth->acl_get('u_titania_post_mod_own'))
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $topic->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $topic->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $topic->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$is_authed = true;
				}
			}
		}

		if (!$is_authed)
		{
			return;
		}

		$actions = array(
			'MAKE_NORMAL'		=> ($topic->topic_sticky) ? titania_url::append_url(titania_url::$current_page_url, array('action' => 'unsticky_topic')) : false,
			'MAKE_STICKY'		=> (!$topic->topic_sticky) ? titania_url::append_url(titania_url::$current_page_url, array('action' => 'sticky_topic')) : false,
			'LOCK_TOPIC'		=> (!$topic->topic_locked) ? titania_url::append_url(titania_url::$current_page_url, array('action' => 'lock_topic')) : false,
			'UNLOCK_TOPIC'		=> ($topic->topic_locked) ? titania_url::append_url(titania_url::$current_page_url, array('action' => 'unlock_topic')) : false,
			'SOFT_DELETE_TOPIC'	=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'delete_topic')),
			'UNDELETE_TOPIC'	=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'undelete_topic')),
		);

		if (phpbb::$auth->acl_get('u_titania_post_hard_delete'))
		{
			$actions = array_merge($actions, array(
				'HARD_DELETE_TOPIC'	=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'hard_delete_topic')),
			));
		}

		phpbb::$template->assign_var('TOPIC_QUICK_ACTIONS', titania::build_quick_actions($actions));
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

	/**
	* Setup the sort tool and return it for posts display
	*
	* @return titania_sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = new titania_sort();
		$sort->set_sort_keys(self::$sort_by);

		if (isset(self::$sort_by[phpbb::$user->data['user_post_sortby_type']]))
		{
			$sort->default_sort_key = phpbb::$user->data['user_post_sortby_type'];
		}
		$sort->default_sort_dir = phpbb::$user->data['user_post_sortby_dir'];
		$sort->default_limit = phpbb::$config['posts_per_page'];

		$sort->result_lang = 'TOTAL_POSTS';

		return $sort;
	}
}