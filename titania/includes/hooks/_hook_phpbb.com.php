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

	// Does a topic already exist?  If so, don't repost.
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

	titania::_include('functions_posting', 'phpbb_post_add');

	$post_text = $post_object->post_text;
	decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . $post_object->get_url();

	$options = array(
		'poster_id'				=> $post_object->topic->topic_first_post_user_id,
		'forum_id' 				=> $forum_id,
		'topic_title'			=> $post_object->topic->topic_subject,
		'post_text'				=> $post_text,
	);

	$topic_id = phpbb_topic_add($options);

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

	titania::_include('functions_posting', 'phpbb_post_add');

	$post_text = $post_object->post_text;
	decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . $post_object->get_url();

	$options = array(
		'poster_id'				=> $post_object->post_user_id,
		'forum_id' 				=> $forum_id,
		'topic_id'				=> $post_object->topic->phpbb_topic_id,
		'topic_title'			=> $post_object->post_subject,
		'post_text'				=> $post_text,
	);

	phpbb_post_add($options);
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

	if (!$row['phpbb_topic_id'] || $row['topic_category'] != TITANIA_TYPE_MOD)
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