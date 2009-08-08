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

titania::load_object(array('topic', 'post'));
titania::add_lang('posting');

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);
$start = request_var('start', 0);
$limit = phpbb::$config['posts_per_page'];

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

	// Figure out the appropriate start position
	$sql = 'SELECT COUNT(post_id) as start FROM ' . TITANIA_POSTS_TABLE . '
		WHERE post_id < ' . $post_id . '
			AND topic_id = ' . $topic_id;
	phpbb::$db->sql_query($sql);
	$start = phpbb::$db->sql_fetchfield('start');
	phpbb::$db->sql_freeresult();

	$start = ($start > 0) ? (floor($start / $limit) * $limit) : 0;

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

$submit = (isset($_POST['submit'])) ? true : false;
$action = request_var('action', '');
$s_post_action = titania::$url->append_url(titania::$contrib->get_url('support'), array('action' => $action));

switch ($action)
{
	case 'post' :
		$post_object = new titania_post('normal');

		// Load the message object
		titania::load_tool('message');
		$message = new titania_message($post_object);
		$message->auth_bbcode = phpbb::$auth->acl_get('titania_bbcode');
		$message->auth_smilies = phpbb::$auth->acl_get('titania_smilies');

		if ($submit)
		{
			$post_data = $message->request_data();

			$post_object->__set_array(array(
				'post_subject'		=> $post_data['subject'],
				'post_text'			=> $post_data['message'],
				'post_access'		=> $post_data['access'],
			));
			$post_object->topic->contrib_id = titania::$contrib->contrib_id;

			$post_object->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);

			$error = $post_object->validate();

			if (!check_form_key('post_form'))
			{
				$error[] = phpbb::$user->lang['FORM_INVALID'];
			}

			if (!sizeof($error))
			{
				$template->assign_var('ERROR', implode('<br />', $error));
			}
			else
			{
				$post_object->submit();

				$redirect = titania::$contrib->get_url('support');
				$redirect = titania::$url->append_url($redirect, array($post_object->post_subject => $post_object->post_id));
				redirect($redirect);
			}
		}

		add_form_key('post_form');
		$message->display();

		phpbb::$template->assign_vars(array(
			'S_POST_ACTION'		=> $s_post_action,
		));

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

		if ($topic_id)
		{

			phpbb::$template->assign_vars(array(
				'U_POST_REPLY'			=> (phpbb::$auth->acl_get('titania_post')) ? titania::$url->append_url($topic->get_url(), array('action' => 'reply')) : '',
			));

			titania::page_header(phpbb::$user->lang['CONTRIB_SUPPORT'] . ' - ' . censor_text($topic->topic_subject));
		}
		else
		{
			titania_display_forums('contrib', titania::$contrib);

			phpbb::$template->assign_vars(array(
				'U_CREATE_TOPIC'		=> (phpbb::$auth->acl_get('titania_topic')) ? titania::$url->append_url(titania::$contrib->get_url('support'), array('action' => 'post')) : '',

				'S_TOPIC_LIST'			=> true,
			));

			titania::page_header('CONTRIB_SUPPORT');
		}

		titania::page_footer(true, 'contributions/contribution_support.html');
	break;
}