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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
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
 * Allow to create a new topic, to reply to a topic, to edit a post or the first_post of a topic in database
 * @param $mode post/reply/edit/edit_first_post/edit_last_post
 * @param $options array Array with post data, see our documentation for exact required items
 * @param $poll array Array with poll options.
 *
 * @return mixed false if there was an error, else topic_id when $mode is post, post_id when $mode is reply, true when mode is edit
 */
function phpbb_posting($mode, &$options, $poll = array())
{
	if (!in_array($mode, array('post', 'reply', 'edit', 'edit_first_post', 'edit_last_post')))
	{
		return false;
	}

	phpbb::_include('bbcode', false, 'bbcode');
	phpbb::_include('message_parser', false, 'parse_message');
	phpbb::_include('functions_posting', 'submit_post', false);

	// Set some defaults
	$options = array_merge($options, array(
		'enable_bbcode'			=> true,
		'enable_urls'			=> true,
		'enable_smilies'		=> true,
		'topic_type'			=> POST_NORMAL,
	));

	$message_parser = new parse_message($options['post_text']);

	// Get the data we need
	if ($mode == 'reply')
	{
		$sql = 'SELECT f.*, t.*
			FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t
			WHERE t.topic_id = ' . (int) $options['topic_id'] . '
				AND f.forum_id = t.forum_id';
		$result = phpbb::$db->sql_query($sql);
		$post_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
	}
	else if ($mode == 'edit')
	{
		$sql = 'SELECT f.*, t.*, p.*
			FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE p.post_id = ' . (int) $options['post_id'] . '
				AND t.topic_id = p.topic_id
				AND f.forum_id = t.forum_id';
		$result = phpbb::$db->sql_query($sql);
		$post_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
	}
	else if ($mode == 'edit_first_post')
	{
		$sql = 'SELECT f.*, t.*, p.*
			FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.topic_id = ' . (int) $options['topic_id'] . '
				AND p.post_id = t.topic_first_post_id
				AND f.forum_id = t.forum_id';
		$result = phpbb::$db->sql_query($sql);
		$post_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		//http://tracker.phpbb.com/browse/PHPBB3-9644
		$mode = 'edit';
	}
	else if ($mode == 'edit_last_post')
	{
		$sql = 'SELECT f.*, t.*, p.*
			FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.topic_id = ' . (int) $options['topic_id'] . '
				AND p.post_id = t.topic_last_post_id
				AND f.forum_id = t.forum_id';
		$result = phpbb::$db->sql_query($sql);
		$post_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		//http://tracker.phpbb.com/browse/PHPBB3-9644
		$mode = 'edit';
	}
	else // post
	{
		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $options['forum_id'];
		$result = phpbb::$db->sql_query($sql);
		$post_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
	}

	if (!$post_data)
	{
		return false;
	}

	// If we need to post the message as a different user other than the one logged in
	if (isset($options['poster_id']) && $options['poster_id'])
	{
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
		$old_user_data = phpbb::$user->data;
		phpbb::$user->data['user_id'] = $options['poster_id'];
		phpbb::$user->data['username'] = $user_data['username'];
		phpbb::$user->data['user_colour'] = $user_data['user_colour'];
		phpbb::$user->data['user_permissions'] = $user_data['user_permissions'];
		phpbb::$user->data['user_type'] = $user_data['user_type'];

		// Same for auth, be sure its posted with correct permissions :)
		$old_auth = phpbb::$auth;

		phpbb::$auth = new \phpbb\auth\auth();
		phpbb::$auth->acl(phpbb::$user->data);
	}

	// Parse the BBCode
	if ($options['enable_bbcode'])
	{
		$message_parser->parse($options['enable_bbcode'], (phpbb::$config['allow_post_links']) ? $options['enable_urls'] : false, $options['enable_smilies'], true, (bool) phpbb::$config['allow_post_flash'],  true, phpbb::$config['allow_post_links']);
	}

	// Setup the settings we need to send to submit_post
	$data = array(
		'topic_title'			=> $options['topic_title'],

		'enable_bbcode'			=> (bool) $options['enable_bbcode'],
		'enable_smilies'		=> (bool) $options['enable_smilies'],
		'enable_urls'			=> (bool) $options['enable_urls'],
		'message_md5'			=> (string) md5($message_parser->message),
		'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
		'bbcode_uid'			=> $message_parser->bbcode_uid,
		'message'				=> $message_parser->message,

		'force_approved_state'	=> true,

		// http://tracker.phpbb.com/browse/PHPBB3-9635
		'post_time'				=> time(),

		// False for both will not add nor remove notifications
		'notify_set'			=> false,
		'notify'				=> false,
	);

	switch ($mode)
	{
		case 'post':
			$data = array_merge(array(
				'icon_id'				=> (isset($options['icon_id'])) ? $options['icon_id'] : 0,
				'poster_id'				=> (isset($options['poster_id']) && $options['poster_id']) ? (int) $options['poster_id'] : phpbb::$user->data['user_id'],
				'enable_sig'			=> (isset($options['enable_sig'])) ? (bool) $options['enable_sig'] : true,
				'post_edit_locked'		=> (isset($options['post_edit_locked'])) ? $options['post_edit_locked'] : false,
			), $data);
		break;

		case 'reply':
			$data = array_merge(array(
				'poster_id'				=> (isset($options['poster_id']) && $options['poster_id']) ? (int) $options['poster_id'] : phpbb::$user->data['user_id'],
				'enable_sig'			=> (isset($options['enable_sig'])) ? (bool) $options['enable_sig'] : true,
				'post_edit_locked'		=> (isset($options['post_edit_locked'])) ? $options['post_edit_locked'] : false,
			), $data);
		break;
	}

	// Merge the data we grabbed from the forums/topics/posts tables
	$data = array_merge($post_data, $data);

	// In case bbcode_bitfield is not set when it should
	$data['bbcode_bitfield'] = ($data['bbcode_bitfield'] != '') ? $data['bbcode_bitfield'] : $message_parser->bbcode_bitfield;

	// Aaaand, submit it.
	switch ($mode)
	{
		case 'post' :
		case 'reply' :
			submit_post($mode, $options['topic_title'], ((isset($options['poster_id']) && $options['poster_id']) ? $user_data['username'] : phpbb::$user->data['username']), $options['topic_type'], $poll, $data);
		break;

		default :
			submit_post($mode, $options['topic_title'], phpbb::$user->data['username'], $options['topic_type'], $poll, $data);
		break;
	}

	// Change the status?  submit_post does not support setting this
	if (isset($options['topic_status']))
	{
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET topic_status = ' . (int) $options['topic_status'] . '
			WHERE topic_id = ' . $data['topic_id'] . '
				AND topic_moved_id = 0';
		phpbb::$db->sql_query($sql);
	}

	// Restore the user data
	if (isset($options['poster_id']) && $options['poster_id'])
	{
		phpbb::$user->data = $old_user_data;
		$auth = $old_auth;
	}

	// Add the new data to the options (to grab post/topic id/etc if we want it later)
	$options = array_merge($data, $options);

	if ($mode == 'post')
	{
		return $data['topic_id'];
	}
	else if ($mode == 'reply')
	{
		return $data['post_id'];
	}

	return true;
}

function handle_queue_attachments($post, &$post_text)
{
	if (!$post->post_attachment)
	{
		return;
	}

	$sort_order = (phpbb::$config['display_order']) ? 'ASC' : 'DESC';

	$sql = 'SELECT attachment_id, real_filename
		FROM ' . TITANIA_ATTACHMENTS_TABLE . '
		WHERE is_orphan = 0
			AND object_type = ' . (int) $post->post_type . '
			AND object_id = ' . (int) $post->post_id . '
		ORDER BY attachment_id ' . $sort_order;
	$result = phpbb::$db->sql_query($sql);
	$attachments = array();

	phpbb::$user->add_lang('viewtopic');
	$path_helper = phpbb::$container->get('path_helper');
	$controller_helper = phpbb::$container->get('controller.helper');

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$download_url = $path_helper->strip_url_params(
			$controller_helper->route('phpbb.titania.download', array('id' => $row['attachment_id'])),
			'sid'
		);
		$attachments[] = '[' . phpbb::$user->lang['ATTACHMENT'] . "] [url=$download_url]{$row['real_filename']}[/url]";
	}
	phpbb::$db->sql_freeresult($result);

	if (empty($attachments))
	{
		return;
	}

	preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $post_text, $matches, PREG_PATTERN_ORDER);

	$replace = array();
	foreach ($matches[0] as $num => $capture)
	{
		// Flip index if we are displaying the reverse way
		$index = (phpbb::$config['display_order']) ? ($tpl_size-($matches[1][$num] + 1)) : $matches[1][$num];

		$replace['from'][] = $matches[0][$num];
		$replace['to'][] = (isset($attachments[$index])) ? "\n$attachments[$index]\n" : '';

		unset($attachments[$index]);
	}

	if (isset($replace['from']))
	{
		$post_text = str_replace($replace['from'], $replace['to'], $post_text);
	}

	if (!empty($attachments))
	{
		$post_text .= "\n\n" . implode("\n", $attachments);
	}
}
