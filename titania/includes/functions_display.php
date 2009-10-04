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

/**
* Get version string from version number
*
* @param int|string $version The version number 2/20/200 for 2.0.0, 3/30/300 for 3.0.0, 32/320 for 3.2.0
*/
function get_version_string($version)
{
	$version = (string) $version;

	$major = (isset($version[0])) ? $version[0] : 0;
	$minor = (isset($version[1])) ? $version[1] : 0;
	$revision = (isset($version[2])) ? substr($version, 2) : 0;

	return sprintf('%u.%u.%u', $major, $minor, $revision);
}

/**
* Assign user details (prepared for output to template)
*
* @param mixed $row
*/
function assign_user_details($row)
{
	$poster_id = $row['user_id'];

	phpbb::_include('functions_display', 'get_user_rank');

	get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_image'], $row['rank_image_src']);

	// End signature parsing, only if needed
	if ($row['user_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
	{
		$row['user_sig'] = generate_text_for_display($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], 7);
	}

	return array(
		'USER_FULL'				=> get_username_string('full', $poster_id, $row['username'], $row['user_colour']),
		'USER_COLOUR'			=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour']),
		'USERNAME'				=> get_username_string('username', $poster_id, $row['username'], $row['user_colour']),

		'RANK_TITLE'			=> $row['rank_title'],
		'RANK_IMG'				=> $row['rank_image'],
		'RANK_IMG_SRC'			=> $row['rank_image_src'],
		'USER_JOINED'			=> phpbb::$user->format_date($row['user_regdate']),
		'USER_POSTS'			=> $row['user_posts'],
		'USER_FROM'				=> $row['user_from'],
		'USER_AVATAR'			=> (phpbb::$user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '',
		'USER_WARNINGS'			=> $row['user_warnings'],
//		'USER_AGE'				=> $row['age'],
		'USER_SIG'				=> $row['user_sig'],

		'ICQ_STATUS_IMG'		=> (!empty($row['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '',
//		'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !phpbb::$config['load_onlinetrack']) ? '' : (($row['online']) ? phpbb::$user->img('icon_user_online', 'ONLINE') : phpbb::$user->img('icon_user_offline', 'OFFLINE')),
//		'S_ONLINE'				=> ($poster_id == ANONYMOUS || !phpbb::$config['load_onlinetrack']) ? false : (($row['online']) ? true : false),
//		'S_FRIEND'				=> ($row['friend']) ? true : false,

// @todo: info link...need to build the mcp stuff first.
//		'U_INFO'				=> ($auth->acl_get('m_info', $forum_id)) ? phpbb::append_sid('mcp', "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, phpbb::$user->session_id) : '',
		'U_USER_PROFILE'		=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour']),
		'U_SEARCH'				=> (phpbb::$auth->acl_get('u_search')) ? phpbb::append_sid('search', "author_id=$poster_id&amp;sr=posts") : '',
		'U_PM'					=> ($poster_id != ANONYMOUS && phpbb::$config['allow_privmsg'] && phpbb::$auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || phpbb::$auth->acl_gets('a_', 'm_') || phpbb::$auth->acl_getf_global('m_'))) ? phpbb::append_sid('ucp', 'i=pm&amp;mode=compose') : '',
		'U_EMAIL'				=> (!empty($row['user_allow_viewemail']) || phpbb::$auth->acl_get('a_email')) ? ((phpbb::$config['board_email_form'] && phpbb::$config['email_enable']) ? phpbb::append_sid('memberlist', "mode=email&amp;u=$poster_id") : ((phpbb::$config['board_hide_emails'] && !phpbb::$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email'])) : '',
		'U_WWW'					=> $row['user_website'],
		'U_ICQ'					=> (!empty($row['user_icq'])) ? 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'] : '',
		'U_AIM'					=>($row['user_aim'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=aim&amp;u=$poster_id") : '',
		'U_MSN'					=> ($row['user_msnm'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=msnm&amp;u=$poster_id") : '',
		'U_YIM'					=> ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($row['user_yim']) . '&amp;.src=pg' : '',
		'U_JABBER'				=> ($row['user_jabber'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=jabber&amp;u=$poster_id") : '',
	);
}

/**
* Display categories
*
* @param int $parent_id The parent id/name (only show categories under this category)
* @param string $blockname The name of the template block to use (categories by default)
*/
function titania_display_categories($parent_id = 0, $blockname = 'categories')
{
	titania::load_object('category');

	$sql = 'SELECT * FROM ' . TITANIA_CATEGORIES_TABLE . '
		WHERE parent_id = ' . (int) $parent_id . '
			AND category_visible = 1
		ORDER BY left_id ASC';
	$result = phpbb::$db->sql_query($sql);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$category = new titania_category();
		$category->__set_array($row);

		phpbb::$template->assign_block_vars($blockname, $category->assign_display(true));

		unset($category);
	}
	phpbb::$db->sql_freeresult($result);
}

/**
* Display contributions
*
* @param string $mode The mode (category, author)
* @param int $id The parent id (only show contributions under this category, author, etc)
* @param string $blockname The name of the template block to use (contribs by default)
*/
function titania_display_contribs($mode, $id, $blockname = 'contribs')
{
	titania::load_object(array('contribution', 'author'));
	titania::load_tool(array('sort', 'pagination'));

	// Setup sorting.
	$sort = new titania_sort();
	$sort->sort_request('');

	switch ($mode)
	{
		case 'author' :
			$sort->set_sort_keys(array(
				array('SORT_CONTRIB_NAME',		'c.contrib_name', 'default' => true),
			));

			$sql = 'SELECT * FROM ' . TITANIA_CONTRIBS_TABLE . ' c, ' . USERS_TABLE . ' u
				WHERE c.contrib_user_id = ' . (int) $id . '
					AND u.user_id = c.contrib_user_id
					AND c.contrib_visible = 1
				ORDER BY ' . $sort->get_order_by();
		break;

		case 'category' :
			$sort->set_sort_keys(array(
				array('SORT_CONTRIB_NAME',			'c.contrib_name', 'default' => true),
			));

			$sql = phpbb::$db->sql_build_query('SELECT', array(
				// DO NOT change to *, we do not need all rows from ANY table with the query!
				'SELECT'	=> 'c.contrib_name, c.contrib_name_clean, c.contrib_status, c.contrib_downloads, c.contrib_views, c.contrib_rating, c.contrib_rating_count, c.contrib_type, u.username, u.user_colour, u.username_clean',

				'FROM'		=> array(
					TITANIA_CONTRIB_IN_CATEGORIES_TABLE 	=> 'cic',
				),

				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(TITANIA_CONTRIBS_TABLE	=> 'c'),
						'ON'	=> 'cic.contrib_id = c.contrib_id'
					),
					array(
						'FROM'	=> array(USERS_TABLE	=> 'u'),
						'ON'	=> 'u.user_id = c.contrib_user_id'
					),
				),

				'WHERE'		=> 'cic.category_id = ' . (int) $id . '
					AND c.contrib_visible = 1',

				'ORDER_BY'	=> $sort->get_order_by(),
			));
		break;
	}

	// Setup pagination.
	$pagination = new titania_pagination();
	$start = $pagination->get_start(0);
	$limit = $pagination->get_limit();

	$result = phpbb::$db->sql_query_limit($sql, $limit, $start);

	$contrib_type = 0;
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$contrib = new titania_contribution();
		$contrib->__set_array($row);

		$author = new titania_author();
		$author->__set_array($row);

		phpbb::$template->assign_block_vars($blockname, array(
			'CONTRIB_USERNAME'			=> $contrib->username,
			'CONTRIB_USERNAME_FULL'		=> $author->get_username_string(),
			'CONTRIB_NAME'				=> $contrib->contrib_name,
			'CONTRIB_TYPE'				=> titania::$types[$contrib->contrib_type]->lang,
			'CONTRIB_STATUS'			=> $contrib->contrib_status,
			'CONTRIB_DOWNLOADS'			=> $contrib->contrib_downloads,
			'CONTRIB_VIEWS'				=> $contrib->contrib_views,
			'CONTRIB_RATING'			=> $contrib->contrib_rating,
			'CONTRIB_RATING_COUNT'		=> $contrib->contrib_rating_count,

			'U_VIEW_CONTRIB'			=> $contrib->get_url(),

			'S_CONTRIB_TYPE'			=> $contrib->contrib_type,
		));

		$contrib_type = $row['contrib_type'];

		unset($contrib, $author);
	}
	phpbb::$db->sql_freeresult($result);

	$pagination->set_params(array(
		'sk'		=> $sort->get_sort_key(false),
		'sd'		=> $sort->get_sort_dir(false),
	));

	$pagination->build_pagination('');

	phpbb::$template->assign_vars(array(
		'U_ACTION'			=> titania::$url->current_page,
		'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
		'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
	));
}

/**
* Display "forum" like section for support/tracker/etc
*
* @param string $type The type (support, review, queue, tracker, author_support, author_tracker) author_ for displaying posts from the areas the given author is involved in (either an author/co-author)
* @param object|boolean $object The object (for contrib related (support, review, queue, tracker) and author_ modes)
* @param object|boolean $sort The sort object (includes/tools/sort.php)
* @param array $options Extra options (limit, category (for tracker))
*/
function titania_display_forums($type, $object = false, $sort = false, $options = array('start' => 0, 'limit' => 10))
{
	titania::load_object('topic');

	$sql_ary = array(
		'SELECT' => 't.*, c.contrib_type, c.contrib_name_clean',
		'FROM'		=> array(
			TITANIA_TOPICS_TABLE => 't',
			TITANIA_CONTRIBS_TABLE => 'c',
		),
		'WHERE' => 't.topic_access >= ' . titania::$access_level . '
			AND c.contrib_id = t.contrib_id',
		'ORDER_BY'	=> 't.topic_sticky DESC',
	);

	// Sort options
	if ($sort !== false)
	{
		$sql_ary['ORDER_BY'] .= ', ' . $sort->get_order_by();
	}
	else
	{
		$sql_ary['ORDER_BY'] .= ', t.topic_last_post_time DESC';
	}

	// If they are not moderators we need to add some more checks
	if (!phpbb::$auth->acl_get('titania_post_mod'))
	{
		$sql_ary['WHERE'] .= ' AND t.topic_deleted = 0';
		$sql_ary['WHERE'] .= ' AND t.topic_approved = 1';
	}

	// type specific things
	switch ($type)
	{
		case 'tracker' :
			$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
			$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_TRACKER;

			if (isset($options['category']))
			{
				$sql_ary['WHERE'] .= ' AND t.topic_category = ' . (int) $options['category'];
			}
		break;

		case 'queue' :
			$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
			$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_QUEUE;
		break;

		case 'author_support' :
			$contrib_ids = titania::$cache->get_author_contribs($object->user_id);
			$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', $contrib_ids);

			$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_DEFAULT;
			$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', titania::$cache->get_author_contribs($object->user_id));
		break;

		case 'author_tracker' :
			$contrib_ids = titania::$cache->get_author_contribs($object->user_id);
			$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', $contrib_ids);

			$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_TRACKER;
			$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', titania::$cache->get_author_contribs($object->user_id));
		break;

		case 'support' :
		default :
			$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
			$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_DEFAULT;
		break;
	}

	// Main SQL Query
	$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

	// Count SQL Query
	$sql_ary['SELECT'] = 'COUNT(topic_id) AS cnt';
	$count_sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
	phpbb::$db->sql_query($count_sql);
	$count = phpbb::$db->sql_fetchfield('cnt');
	phpbb::$db->sql_freeresult();

	// Get the data
	$topics = $topic_ids = array();
	$result = phpbb::$db->sql_query_limit($sql, $options['limit'], $options['start']);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$topics[$row['topic_id']] = $row;
		$topic_ids[] = $row['topic_id'];
	}
	phpbb::$db->sql_freeresult($result);

	// Get the read info

	// Loop de loop
	$last_was_sticky = false;
	foreach ($topics as $row)
	{
		$topic = new titania_topic($row['topic_type']);
		$topic->__set_array($row);

		phpbb::$template->assign_block_vars('topics', array_merge($topic->assign_details(), array(
			'S_TOPIC_TYPE_SWITCH'		=> ($last_was_sticky && !$topic->topic_sticky) ? true : false,
		)));

		$last_was_sticky = $topic->topic_sticky;

		unset($topic);
	}
	phpbb::$db->sql_freeresult($result);

	phpbb::$template->assign_vars(array(
		'REPORTED_IMG'		=> phpbb::$user->img('icon_topic_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'	=> phpbb::$user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
		'NEWEST_POST_IMG'	=> phpbb::$user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
	));
}

/**
* Display topic section for support/tracker/etc
*
 @param object $topic The topic object
 @param object|boolean $sort The sort object (includes/tools/sort.php)
* @param array $options Extra options (limit, category (for tracker))
*/
function titania_display_topic($topic, $sort = false, $options = array('start' => 0, 'limit' => 10))
{
	titania::load_object('post');

	$sql_ary = array(
		'SELECT' => 'p.*, u.*, z.friend, z.foe',
		'FROM'		=> array(
			TITANIA_POSTS_TABLE => 'p',
			USERS_TABLE => 'u',
		),
		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(ZEBRA_TABLE => 'z'),
				'ON'	=> 'z.user_id = ' . phpbb::$user->data['user_id'] . ' AND z.zebra_id = p.post_user_id'
			)
		),
		'WHERE' => 'p.post_access >= ' . titania::$access_level . '
			AND p.topic_id = ' . (int) $topic->topic_id . '
			AND u.user_id = p.post_user_id',
		'ORDER_BY'	=> 'p.post_time ASC',
	);

	// Sort options
	if ($sort !== false)
	{
		$sql_ary['ORDER_BY'] = $sort->get_order_by();
	}

	// Main SQL Query
	$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

	// Count SQL Query
	$sql_ary['SELECT'] = 'COUNT(post_id) AS cnt';
	$count_sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
	phpbb::$db->sql_query($count_sql);
	$count = phpbb::$db->sql_fetchfield('cnt');
	phpbb::$db->sql_freeresult();

	// Get the data
	$posts = $user_ids = array();
	$result = phpbb::$db->sql_query_limit($sql, $options['limit'], $options['start']);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$posts[$row['post_id']] = $row;
		$user_ids[] = $row['user_id'];
	}
	phpbb::$db->sql_freeresult($result);

	// Load custom profile fields
	$profile_fields_cache = array();
	if (phpbb::$config['load_cpf_viewtopic'])
	{
		phpbb::_include('functions_profile_fields', false, 'custom_profile');

		$cp = new custom_profile();

		// Grab all profile fields from users in id cache for later use - similar to the poster cache
		$profile_fields_tmp = $cp->generate_profile_fields_template('grab', $user_ids);

		// filter out fields not to be displayed on viewtopic. Yes, it's a hack, but this shouldn't break any MODs.
		foreach ($profile_fields_tmp as $profile_user_id => $profile_fields)
		{
			$profile_fields_cache[$profile_user_id] = array();
			foreach ($profile_fields as $used_ident => $profile_field)
			{
				if ($profile_field['data']['field_show_on_vt'])
				{
					$profile_fields_cache[$profile_user_id][$used_ident] = $profile_field;
				}
			}
		}
		unset($profile_fields_tmp);
	}

	// Generate online information for user
	$online_cache = array();
	if (phpbb::$config['load_onlinetrack'] && sizeof($user_ids))
	{
		$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
			FROM ' . SESSIONS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('session_user_id', $user_ids) . '
			GROUP BY session_user_id';
		$result = phpbb::$db->sql_query($sql);

		$update_time = phpbb::$config['load_online_time'] * 60;
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$online_cache[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || phpbb::$auth->acl_get('u_viewonline'))) ? true : false;
		}
		phpbb::$db->sql_freeresult($result);
	}

	// Loop de loop
	foreach ($posts as $row)
	{
		$post = new titania_post($topic->topic_type, $topic);
		$post->__set_array($row);

		phpbb::$template->assign_block_vars('posts', array_merge($post->assign_details(), assign_user_details($row)));
//S_IGNORE_POST
//POST_ICON_IMG
//MINI_POST_IMG
		unset($post);
	}
	phpbb::$db->sql_freeresult($result);

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