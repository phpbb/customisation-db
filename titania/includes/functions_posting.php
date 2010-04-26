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

/*
 * Generate the category select (much is from the make_jumpbox function)
 *
 * @param array $selected
 * @return void
 */
function generate_category_select($selected = false, $is_manage = false)
{
	if (!is_array($selected))
	{
		$selected = array($selected);
	}

	$right = $padding = 0;
	$padding_store = array('0' => 0);

	$categories = titania::$cache->get_categories();

	foreach ($categories as $row)
	{
		if ($row['left_id'] < $right)
		{
			$padding++;
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : $padding;
		}

		$right = $row['right_id'];

		if ($row['category_type'] == 0 && ($row['left_id'] + 1 == $row['right_id']) && !$is_manage)
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		phpbb::$template->assign_block_vars('category_select', array(
			'S_SELECTED'		=> (in_array($row['category_id'], $selected)) ? true : false,
			'S_DISABLED'		=> ($row['category_type'] == 0) ? true : false,

			'VALUE'				=> $row['category_id'],
			'TYPE'				=> $row['category_type'],
			'NAME'				=> (isset(phpbb::$user->lang[$row['category_name']])) ? phpbb::$user->lang[$row['category_name']] : $row['category_name'],
		));

		for ($i = 0; $i < $padding; $i++)
		{
			phpbb::$template->assign_block_vars('category_select.level', array());
		}
	}
}

/*
 * Create a select with the contrib types
 *
 * @param array $selected
 * @return void
 */
function generate_type_select($selected = false)
{
	phpbb::$template->assign_block_vars('type_select', array(
		'S_IS_SELECTED'		=> ($selected === false) ? true : false,

		'VALUE'				=> 0,
		'NAME'				=> (isset(phpbb::$user->lang['SELECT_CONTRIB_TYPE'])) ? phpbb::$user->lang['SELECT_CONTRIB_TYPE'] : '--',
	));

	foreach (titania_types::$types as $key => $type)
	{
		phpbb::$template->assign_block_vars('type_select', array(
			'S_IS_SELECTED'		=> ($key == $selected) ? true : false,

			'VALUE'				=> $key,
			'NAME'				=> (isset(phpbb::$user->lang['SELECT_CONTRIB_TYPE'])) ? $type->lang : $type->langs,
		));
	}
}

/*
 * Create a select with the phpBB versions
 *
 * @param array $selected
 * @return void
 */
function generate_phpbb_version_select($selected = false)
{
	$branches = get_allowed_phpbb_branches();

	// Only display if more than one branch is allowed
	if (sizeof($branches) == 1)
	{
		return;
	}

	foreach ($branches as $branch => $row)
	{
		phpbb::$template->assign_block_vars('phpbb_branches', array(
			'S_IS_SELECTED'		=> (is_array($selected) && in_array($branch, $selected)) ? true : false,

			'VALUE'				=> $branch,
			'NAME'				=> $row['name'],
		));
	}
}

/**
* Get the branches we are allowed to upload to
*/
function get_allowed_phpbb_branches()
{
	$versions = array();

	foreach (titania::$config->phpbb_versions as $branch => $row)
	{
		if (!$row['allow_uploads'])
		{
			continue;
		}

		$versions[$branch] = $row;
	}

	return $versions;
}

/**
* Generate the _options flag from the given settings
*
* @param bool $bbcode
* @param bool $smilies
* @param bool $url
* @return int options flag
*/
function get_posting_options($bbcode, $smilies, $url)
{
	return (($bbcode) ? OPTION_FLAG_BBCODE : 0) + (($smilies) ? OPTION_FLAG_SMILIES : 0) + (($url) ? OPTION_FLAG_LINKS : 0);
}

/**
* Reverses the posting options
*
* @param int $options The given posting options
* @param bool $bbcode
* @param bool $smilies
* @param bool $url
*/
function reverse_posting_options($options, &$bbcode, &$smilies, &$url)
{
	$bbcode = ($options & OPTION_FLAG_BBCODE) ? true : false;
	$smilies = ($options & OPTION_FLAG_SMILIES) ? true : false;
	$url = ($options & OPTION_FLAG_LINKS) ? true : false;
}

/*
 * Create select with Titania's accesses
 *
 * @param integer $default
 * @return string
 */
function titania_access_select($default = false)
{
	if (titania::$access_level == TITANIA_ACCESS_PUBLIC)
	{
		return '';
	}

	$access_types = array(
		TITANIA_ACCESS_TEAMS 	=> 'ACCESS_TEAMS',
		TITANIA_ACCESS_AUTHORS 	=> 'ACCESS_AUTHORS',
		TITANIA_ACCESS_PUBLIC 	=> 'ACCESS_PUBLIC',
	);

	if ($default === false)
	{
		$default = TITANIA_ACCESS_PUBLIC;
	}

	$s_options = '';

	foreach ($access_types as $type => $lang_key)
	{
		if (titania::$access_level > $type)
		{
			continue;
		}

		$selected = ($default == $type) ? ' selected="selected"' : '';

		$s_options .= '<option value="' . $type . '"' . $selected . '>' . phpbb::$user->lang[$lang_key] . '</option>';
	}

	return $s_options;
}

/**
* Get the author user_ids from the list of usernames
*
* @param string $list the list of usernames (after executed it will be an array of the user_ids)
* @param array $missing array of usernames that could not be found (will be populated if any)
* @param string $separator the delimiter
*/
function get_author_ids_from_list(&$list, &$missing, $separator = "\n")
{
	if (!$list)
	{
		$list = $missing = array();
		return true;
	}

	$usernames = explode($separator, $list);
	$list = array();

	foreach ($usernames as &$username)
	{
		$missing[$username] = $username;
		$username = utf8_clean_string($username);
	}

	$sql = 'SELECT username, username_clean, user_id FROM ' . USERS_TABLE . '
		WHERE ' . phpbb::$db->sql_in_set('username_clean', $usernames) . '
		AND user_type != ' . USER_IGNORE;
	$result = phpbb::$db->sql_query($sql);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		unset($missing[$row['username']], $missing[$row['username_clean']]);

		$list[$row['username']] = $row['user_id'];
	}

	if (sizeof($missing))
	{
		return false;
	}

	return true;
}

/**
 * Add a new topic to the database.
 * @param $options array Array with post data, see our documentation for exact required items
 * @param $poll array Array with poll options.
 * @return mixed false if there was an error, topic_id when the new topic was created.
 */
function phpbb_topic_add(&$options, $poll = array())
{
	phpbb::_include('bbcode', false, 'bbcode');
	phpbb::_include('message_parser', false, 'parse_message');
	phpbb::_include('functions_posting', 'submit_post', false);

	$options_global = array(
		'enable_bbcode'			=> 1,
		'enable_urls'			=> 1,
		'enable_smilies'		=> 1,
		'enable_sig'			=> 1,
		'topic_time_limit'		=> 0,
		'icon_id'				=> 0,
		'post_time'				=> time(),
		'poster_ip'				=> phpbb::$user->ip,
		'post_edit_locked'		=> 0,
		'topic_type'			=> POST_NORMAL,
		'post_approved'			=> true,
	);

	$options = array_merge($options, $options_global);

	// Get correct data from forums table to be sure all data is there.
	$sql = 'SELECT forum_parents, forum_name, enable_indexing
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id = ' . $options['forum_id'];
	$result = phpbb::$db->sql_query($sql);
	$forum_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$forum_data)
	{
		return false;
	}

	$message_parser = new parse_message();
	$message_parser->message = &$options['post_text'];
	unset($options['post_text']);

	// Some data for the ugly fix below :P
	$sql = 'SELECT username, user_colour, user_permissions, user_type
		FROM ' . USERS_TABLE . '
		WHERE user_id = ' . (int) $options['poster_id'];
	$result = phpbb::$db->sql_query($sql);
	$user_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$user_data)
	{
		return false;
	}

	// Ugly fix, to be sure it is posted for the right user ;)
	$old_data = phpbb::$user->data;
	phpbb::$user->data['user_id'] = $options['poster_id'];
	phpbb::$user->data['username'] = $user_data['username'];
	phpbb::$user->data['user_colour'] = $user_data['user_colour'];
	phpbb::$user->data['user_permissions'] = $user_data['user_permissions'];
	phpbb::$user->data['user_type'] = $user_data['user_type'];

	// Same for auth, be sure its posted with correct permissions :)
	$old_auth = phpbb::$auth;

	phpbb::$auth = new auth();
	phpbb::$auth->acl(phpbb::$user->data);

	if ($options['enable_bbcode'])
	{
		$message_parser->parse($options['enable_bbcode'], $options['enable_urls'], $options['enable_smilies'], (bool) phpbb::$auth->acl_get('f_img', $options['forum_id']), (bool) phpbb::$auth->acl_get('f_flash', $options['forum_id']),  (bool) phpbb::$auth->acl_get('f_reply', $options['forum_id']), phpbb::$config['allow_post_links']);
	}

	$data = array(
		'topic_title'			=> $options['topic_title'],
		'topic_first_post_id'	=> 0,
		'topic_last_post_id'	=> 0,
		'topic_time_limit'		=> $options['topic_time_limit'],
		'topic_attachment'		=> 0,
		'post_id'				=> 0,
		'topic_id'				=> 0,
		'forum_id'				=> $options['forum_id'],
		'icon_id'				=> (int) $options['icon_id'],
		'poster_id'				=> (int) $options['poster_id'],
		'enable_sig'			=> (bool) $options['enable_sig'],
		'enable_bbcode'			=> (bool) $options['enable_bbcode'],
		'enable_smilies'		=> (bool) $options['enable_smilies'],
		'enable_urls'			=> (bool) $options['enable_urls'],
		'enable_indexing'		=> (bool) $forum_data['enable_indexing'],
		'message_md5'			=> (string) md5($message_parser->message),
		'post_time'				=> $options['post_time'],
		'post_checksum'			=> '',
		'post_edit_reason'		=> '',
		'post_edit_user'		=> 0,
		'forum_parents'			=> $forum_data['forum_parents'],
		'forum_name'			=> $forum_data['forum_name'],
		'notify'				=> false,
		'notify_set'			=> 0,
		'poster_ip'				=> $options['poster_ip'],
		'post_edit_locked'		=> (int) $options['post_edit_locked'],
		'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
		'bbcode_uid'			=> $message_parser->bbcode_uid,
		'message'				=> $message_parser->message,
		'attachment_data'		=> array(),
		'filename_data'			=> array(),
		'force_approved_state'	=> true,
	);

	// Aaaand, submit it.
	submit_post('post', $options['topic_title'], $user_data['username'], $options['topic_type'], $poll, $data, true);

	// Change the status?
	if (isset($options['topic_status']))
	{
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET topic_status = ' . (int) $options['topic_status'] . '
			WHERE topic_id = ' . $data['topic_id'] . '
				AND topic_moved_id = 0';
		phpbb::$db->sql_query($sql);
	}

	// And restore it
	phpbb::$user->data = $old_data;
	$auth = $old_auth;

	return $data['topic_id'];
}

/**
 * Add a new post to a existing topic.
 * @param $options array list with options, see for items/values our documentation
 * @return mixed false if there was an error, post_id when the post was added to the topic.
 */
function phpbb_post_add(&$options)
{
	phpbb::_include('bbcode', false, 'bbcode');
	phpbb::_include('message_parser', false, 'parse_message');
	phpbb::_include('functions_posting', 'submit_post', false);

	$options_global = array(
		'enable_bbcode'			=> 1,
		'enable_urls'			=> 1,
		'enable_smilies'		=> 1,
		'enable_sig'			=> 1,
		'topic_time_limit'		=> 0,
		'icon_id'				=> 0,
		'post_time'				=> time(),
		'poster_ip'				=> phpbb::$user->ip,
		'post_edit_locked'		=> 0,
		'topic_type'			=> POST_NORMAL,
		'post_approved'			=> true,
	);

	$options = array_merge($options, $options_global);

	// Check forum data, and if forum_id is the same.
	// Also get topic data.
	$sql = 'SELECT f.*, t.*
		FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
		WHERE t.topic_id = ' . $options['topic_id'] . '
			AND (f.forum_id = t.forum_id
				OR f.forum_id = ' . $options['forum_id'] . ')';
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$post_data)
	{
		return false;
	}

	if ($options['forum_id'] != $post_data['forum_id'])
	{
		$options['forum_id'] = (int)$post_data['forum_id'];
	}

	$sql = 'SELECT forum_parents, forum_name, enable_indexing
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id = ' . $options['forum_id'];
	$result = phpbb::$db->sql_query($sql);
	$forum_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$forum_data)
	{
		return false;
	}

	$message_parser = new parse_message();
	$message_parser->message = &$options['post_text'];
	unset($options['post_text']);

	// Get the data for our ugly fix later.
	$sql = 'SELECT username, user_colour, user_permissions, user_type
		FROM ' . USERS_TABLE . '
		WHERE user_id = ' . (int) $options['poster_id'];
	$result = phpbb::$db->sql_query($sql);
	$user_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$user_data)
	{
		return false;
	}

	// Ugly fix, to be sure it is posted for the right user ;)
	$old_data = phpbb::$user->data;
	phpbb::$user->data['user_id'] = $options['poster_id'];
	phpbb::$user->data['username'] = $user_data['username'];
	phpbb::$user->data['user_colour'] = $user_data['user_colour'];
	phpbb::$user->data['user_permissions'] = $user_data['user_permissions'];
	phpbb::$user->data['user_type'] = $user_data['user_type'];

	// And the permissions
	$old_auth = phpbb::$auth;

	phpbb::$auth = new auth();
	phpbb::$auth->acl(phpbb::$user->data);

	if ($options['enable_bbcode'])
	{
		$message_parser->parse($options['enable_bbcode'], $options['enable_urls'], $options['enable_smilies'], (bool) phpbb::$auth->acl_get('f_img', $options['forum_id']), (bool) phpbb::$auth->acl_get('f_flash', $options['forum_id']),  (bool) phpbb::$auth->acl_get('f_reply', $options['forum_id']), phpbb::$config['allow_post_links']);
	}

	$data = array(
		'topic_title'			=> $options['topic_title'],
		'topic_first_post_id'	=> $post_data['topic_first_post_id'],
		'topic_last_post_id'	=> $post_data['topic_last_post_id'],
		'topic_time_limit'		=> $options['topic_time_limit'],
		'topic_attachment'		=> 0,
		'post_id'				=> 0,
		'topic_id'				=> $options['topic_id'],
		'forum_id'				=> $options['forum_id'],
		'icon_id'				=> (int) $options['icon_id'],
		'poster_id'				=> (int) $options['poster_id'],
		'enable_sig'			=> (bool) $options['enable_sig'],
		'enable_bbcode'			=> (bool) $options['enable_bbcode'],
		'enable_smilies'		=> (bool) $options['enable_smilies'],
		'enable_urls'			=> (bool) $options['enable_urls'],
		'enable_indexing'		=> (bool) $forum_data['enable_indexing'],
		'message_md5'			=> (string) md5($message_parser->message),
		'post_time'				=> $options['post_time'],
		'post_checksum'			=> '',
		'post_edit_reason'		=> '',
		'post_edit_user'		=> 0,
		'forum_parents'			=> $forum_data['forum_parents'],
		'forum_name'			=> $forum_data['forum_name'],
		'notify'				=> false,
		'notify_set'			=> 0,
		'poster_ip'				=> $options['poster_ip'],
		'post_edit_locked'		=> (int) $options['post_edit_locked'],
		'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
		'bbcode_uid'			=> $message_parser->bbcode_uid,
		'message'				=> $message_parser->message,
		'attachment_data'		=> array(),
		'filename_data'			=> array(),
		'topic_replies'			=> false,
		'force_approved_state'	=> true,
	);

	$poll = array();

	submit_post('reply', $options['topic_title'], $user_data['username'], $options['topic_type'], $poll, $data, true);

	// And restore the permissions.
	phpbb::$user->data = $old_data;
	$auth = $old_auth;

	return $data['post_id'];
}
