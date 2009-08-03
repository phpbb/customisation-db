<?php
/**
 *
 * @package titania
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

titania::load_object(array('topic', 'post', 'message'));

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);

// Load the topic and contrib items
if ($post_id)
{
	$sql = 'SELECT t.* FROM ' . TITANIA_POSTS_TABLE . ' p, ' . TITANIA_TOPICS_TABLE . ' t
		WHERE p.post_id = ' . $post_id . '
			AND t.topic_id = p.post_id';
	$result = phpbb::$db->sql_query($sql);
	$topic_row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$topic_row)
	{
		trigger_error('NO_TOPIC');
	}

	// Load the topic object
	$topic_id = $topic_row['topic_id'];
	$topic = new titania_topic(TITANIA_POST_DEFAULT, $topic_id);
	$topic->__set_array($topic_row);
	unset($topic_row);

	// Load the contrib item
	load_contrib($topic->contrib_id);
}
else if ($topic_id)
{
	$topic = new titania_topic(TITANIA_POST_DEFAULT, $topic_id);
	if (!$topic->load())
	{
		trigger_error('NO_TOPIC');
	}

	// Load the contrib item
	load_contrib($topic->contrib_id);
}
else
{
	// Load the contrib item
	load_contrib();
}

$action = request_var('action', '');

switch ($action)
{
	case 'post' :
		$post_object = new titania_post('normal');

		$message = new titania_message($post_object);
		$message->display();

		titania::page_header('NEW_TOPIC');
		titania::page_footer(true, 'contributions/contribution_support_post.html');
	break;

	case 'reply' :
		titania::page_header('NEW_REPLY');
		titania::page_footer(true, 'contributions/contribution_support_post.html');
	break;

	case 'edit' :
		titania::page_header('EDIT_MESSAGE');
		titania::page_footer(true, 'contributions/contribution_support_post.html');
	break;

	case 'delete' :
		if (titania::confirm_box(true))
		{

		}
		else
		{
			titania::confirm_box(false, 'CONTRIB_SUPPORT_DELETE');
		}
		redirect(titania::$contrib->get_url('support'));
	break;

	default :
		phpbb::$user->add_lang('viewforum');

		titania_display_forums('contrib', titania::$contrib);

		phpbb::$template->assign_vars(array(
			'U_CREATE_TOPIC'		=> (phpbb::$auth->acl_get('titania_post')) ? titania::$url->append_url(titania::$contrib->get_url('support'), array('action' => 'post')) : '',
		));

		titania::page_header('CONTRIB_SUPPORT');
		titania::page_footer(true, 'contributions/contribution_support.html');
	break;
}