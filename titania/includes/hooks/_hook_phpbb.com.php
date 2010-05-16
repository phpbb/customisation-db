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

titania::$hook->register_ary('phpbb_com_', array(
	'titania_page_header',
	'titania_page_footer',
	array('titania_queue', 'update_first_queue_post'),
	array('titania_topic', '__construct'),
	array('titania_post', 'post'),
	array('titania_queue', 'approve'),
	array('titania_queue', 'deny'),
	array('titania_queue', 'delete'),
));

// Do we need to install the DB stuff?
if (!isset(phpbb::$config['titania_hook_phpbb_com']))
{
	phpbb::_include('../umil/umil', false, 'umil');

	$umil = new umil(true, phpbb::$db);

	$umil->run_actions('update', array(
		'1.0.0' => array(
			'table_column_add' => array(
				array(TITANIA_TOPICS_TABLE, 'phpbb_topic_id', array('UINT', 0)),
			),
		),
	),
	'titania_hook_phpbb_com');

	unset($umil);
}

/**
* .com custom header and footer
*/

function phpbb_com_titania_page_header($hook, $page_title)
{
	phpbb::$template->assign_vars(array(
		'S_BODY_CLASS'		=> 'customise customisation-database',
		'S_IS_WEBSITE'		=> true,
	));

	global $auth, $phpEx, $template, $user;
	$root_path = TITANIA_ROOT . '../../';
	$base_path = generate_board_url(true) . '/';
	include($root_path . 'vars.' . PHP_EXT);

	// Setup the phpBB.com header
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/', 'website');
	phpbb::$template->set_filenames(array(
		'phpbb_com_header'		=> 'overall_header.html',
	));
	phpbb::$template->assign_display('phpbb_com_header', 'PHPBB_COM_HEADER', false);

	titania::set_custom_template();
}

function phpbb_com_titania_page_footer($hook, $run_cron, $template_body)
{
	// Setup the phpBB.com footer
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/', 'website');
	phpbb::$template->set_filenames(array(
		'phpbb_com_footer'		=> 'overall_footer.html',
	));
	phpbb::$template->assign_display('phpbb_com_footer', 'PHPBB_COM_FOOTER', false);

	titania::set_custom_template();
}

/**
* Copy new posts for queue discussion, queue to the forum
*/
function phpbb_com_titania_queue_update_first_queue_post($hook, &$post_object, $queue_object)
{
	if ($queue_object->queue_status == TITANIA_QUEUE_HIDE || !$queue_object->queue_topic_id)
	{
		return;
	}

	// First we copy over the queue discussion topic if required
	$sql = 'SELECT topic_id, phpbb_topic_id, topic_category FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE parent_id = ' . $queue_object->contrib_id . '
			AND topic_type = ' . TITANIA_QUEUE_DISCUSSION;
	$result = phpbb::$db->sql_query($sql);
	$topic_row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	// Do we need to create the queue discussion topic or not?
	if ($topic_row['topic_id'] && !$topic_row['phpbb_topic_id'])
	{
		$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, TITANIA_QUEUE_DISCUSSION);

		$temp_post = new titania_post;

		// Go through any posts in the queue discussion topic and copy them
		$topic_id = false;
		$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . ' WHERE topic_id = ' . $topic_row['topic_id'];
		$result = phpbb::$db->sql_query($sql);
		while($row = phpbb::$db->sql_fetchrow($result))
		{
			$temp_post->__set_array($row);

			$post_text = $row['post_text'];
			titania_decode_message($post_text, $row['post_text_uid']);

			$post_text .= "\n\n" . $temp_post->get_url();

			$options = array(
				'poster_id'				=> $row['post_user_id'],
				'forum_id' 				=> $forum_id,
				'topic_title'			=> $row['post_subject'],
				'post_text'				=> $post_text,
			);

			titania::_include('functions_posting', 'phpbb_posting');

			if ($topic_id)
			{
				phpbb_posting('reply', $options);
			}
			else
			{
				switch ($topic_row['topic_category'])
				{
					case TITANIA_TYPE_MOD :
						$options['poster_id'] = titania::$config->forum_mod_robot;
					break;

					case TITANIA_TYPE_STYLE :
						$options['poster_id'] = titania::$config->forum_style_robot;
					break;
				}

				$topic_id = phpbb_posting('post', $options);
			}
		}
		phpbb::$db->sql_freeresult($result);

		if ($topic_id)
		{
			$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
				SET phpbb_topic_id = ' . $topic_id . '
				WHERE topic_id = ' . $topic_row['topic_id'];
			phpbb::$db->sql_query($sql);
		}

		unset($temp_post);
	}

	// Does a queue topic already exist?  If so, don't repost.
	$sql = 'SELECT phpbb_topic_id FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE topic_id = ' . $queue_object->queue_topic_id;
	phpbb::$db->sql_query($sql);
	$phpbb_topic_id = phpbb::$db->sql_fetchfield('phpbb_topic_id');
	phpbb::$db->sql_freeresult();
	if ($phpbb_topic_id)
	{
		return;
	}

	$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, $post_object->topic->topic_type);

	if (!$forum_id)
	{
		return;
	}

	$post_object->submit();

	titania::_include('functions_posting', 'phpbb_posting');

	// Need some stuff
	titania::add_lang('contributions');
	$contrib->load((int) $queue_object->contrib_id);
	$revision = $queue_object->get_revision();
	$contrib->get_download($revision->revision_id);
	
	switch ($post_object->topic->topic_category)
	{
		case TITANIA_TYPE_MOD :
			$post_object->topic->topic_first_post_user_id = titania::$config->forum_mod_robot;
			$lang_var = 'MOD_QUEUE_TOPIC';
		break;

		case TITANIA_TYPE_STYLE :
			$post_object->topic->topic_first_post_user_id = titania::$config->forum_style_robot;
			$lang_var = 'STYLE_QUEUE_TOPIC';
		break;
	}
	
	$post_text = sprintf(phpbb::$user->lang[$lang_var],
		$contrib->contrib_name,
		$contrib->author->get_url(),
		users_overlord::get_user($contrib->author->user_id, '_username'),
		$contrib->contrib_desc,
		$revision->revision_version,
		titania_url::build_url('download', array('id' => $revision->attachment_id)),
		$contrib->download['real_filename'],
		$contrib->download['filesize']
	);
	
	$post_text .= "\n\n" . $post_object->post_text;
	titania_decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . $post_object->get_url();

	$options = array(
		'poster_id'				=> $post_object->topic->topic_first_post_user_id,
		'forum_id' 				=> $forum_id,
		'topic_title'			=> $post_object->topic->topic_subject,
		'post_text'				=> $post_text,
	);

	$topic_id = phpbb_posting('post', $options);

	$post_object->topic->phpbb_topic_id = $topic_id;

	$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
		SET phpbb_topic_id = ' . (int) $topic_id . '
		WHERE topic_id = ' . $post_object->topic->topic_id;
	phpbb::$db->sql_query($sql);
}


function phpbb_com_titania_post_post($hook, &$post_object)
{
	if (defined('IN_TITANIA_CONVERT') || !$post_object->topic->phpbb_topic_id)
	{
		return;
	}

	$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, $post_object->post_type);

	if (!$forum_id)
	{
		return;
	}

	titania::_include('functions_posting', 'phpbb_posting');

	$post_text = $post_object->post_text;
	titania_decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . $post_object->get_url();

	$options = array(
		'poster_id'				=> $post_object->post_user_id,
		'forum_id' 				=> $forum_id,
		'topic_id'				=> $post_object->topic->phpbb_topic_id,
		'topic_title'			=> $post_object->post_subject,
		'post_text'				=> $post_text,
	);

	phpbb_posting('reply', $options);
}

function phpbb_com_titania_topic___construct($hook, &$topic_object)
{
	$topic_object->object_config = array_merge($topic_object->object_config, array(
		'phpbb_topic_id'	=> array('default' => 0),
	));
}

/**
* Move queue topics to the trash can
*/

function phpbb_com_titania_queue_approve($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_titania_queue_deny($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_titania_queue_delete($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_move_queue_topic($queue_object)
{
	$sql = 'SELECT phpbb_topic_id, topic_category FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE topic_id = ' . (int) $queue_object->queue_topic_id;
	$result = phpbb::$db->sql_query($sql);
	$row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$row['phpbb_topic_id'])
	{
		return;
	}

	phpbb::_include('functions_admin', 'move_topics');

	move_topics($row['phpbb_topic_id'], phpbb_com_forum_id($row['topic_category'], 'trash'));
}

function phpbb_com_forum_id($type, $mode)
{
	switch ($type)
	{
		case TITANIA_TYPE_MOD :
			switch ($mode)
			{
				case TITANIA_QUEUE_DISCUSSION :
					return 61;
				break;

				case TITANIA_QUEUE :
					return 38;
				break;

				case 'trash' :
					return 28;
				break;
			}
		break;

		case TITANIA_TYPE_STYLE :
			switch ($mode)
			{
				case TITANIA_QUEUE_DISCUSSION :
					return 87;
				break;

				case TITANIA_QUEUE :
					return 40;
				break;

				case 'trash' :
					return 83;
				break;
			}
		break;
	}

	return false;
}