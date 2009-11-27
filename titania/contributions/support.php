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

titania::add_lang('posting');

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);

// Load the topic and contrib items
if ($post_id)
{
	$topic_id = topics_overlord::load_topic_from_post($post_id);

	// Load the topic into a topic object
	$topic = topics_overlord::get_topic_object($topic_id);
	if ($topic === false)
	{
		trigger_error('NO_TOPIC');
	}

	// Load the contrib item
	load_contrib($topic->contrib_id);
}
else if ($topic_id)
{
	topics_overlord::load_topic($topic_id);

	// Load the topic into a topic object
	$topic = topics_overlord::get_topic_object($topic_id);
	if ($topic === false)
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

switch ($action)
{
	case 'post' :
	case 'reply' :
		if (($action == 'post' && !phpbb::$auth->acl_get('titania_topic')) || ($action == 'reply' && (!$topic_id || !phpbb::$auth->acl_get('titania_post'))))
		{
			trigger_error('NO_AUTH');
		}

		if ($action == 'post')
		{
			$post_object = new titania_post('normal');
			$post_object->topic->contrib_id = titania::$contrib->contrib_id;
		}
		else
		{
			$post_object = new titania_post('normal', $topic);
		}

		// Load the message object
		$message = new titania_message($post_object);
		$message->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('titania_smilies'),
			'sticky_topic'	=> ($action == 'post' && (phpbb::$auth->acl_get('titania_post_mod') || titania::$contrib->is_author || titania::$contrib->is_active_coauthor)) ? true : false,
			'lock_topic'	=> (phpbb::$auth->acl_get('titania_post_mod') || phpbb::$auth->acl_get('titania_post_mod_own')) ? true : false,
		));
		$message->set_settings(array(
			'display_captcha'			=> (!phpbb::$user->data['is_registered']) ? true : false,
			'subject_default_override'	=> ($action == 'reply') ? 'Re: ' . $topic->topic_subject : false,
		));

		if ($submit)
		{
			$post_data = $message->request_data();

			$post_object->post_data($post_data);

			$error = $post_object->validate();

			if (($validate_form_key = $message->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}
			if (!phpbb::$user->data['is_registered'] && ($validate_captcha = $message->validate_captcha()) !== false)
			{
				$error[] = $validate_captcha;
			}

			if (sizeof($error))
			{
				phpbb::$template->assign_var('ERROR', implode('<br />', $error));
			}
			else
			{
				$post_object->submit();

				redirect($post_object->get_url());
			}
		}

		$message->display();

		phpbb::$template->assign_vars(array(
			'L_POST_A'			=> phpbb::$user->lang[(($action == 'post') ? 'POST_TOPIC' : 'POST_REPLY')],
			'S_POST_ACTION'		=> ($topic_id) ? titania::$url->append_url(titania::$contrib->get_url('support'), array('action' => $action, 't' => $topic_id)) : titania::$url->append_url(titania::$contrib->get_url('support'), array('action' => $action)),
		));

		titania::page_header(($action == 'post') ? 'NEW_TOPIC' : 'POST_REPLY');
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
			posts_overlord::display_topic_complete($topic);

			titania::page_header(phpbb::$user->lang['CONTRIB_SUPPORT'] . ' - ' . censor_text($topic->topic_subject));
		}
		else
		{
			topics_overlord::display_forums_complete('support', titania::$contrib);

			titania::page_header('CONTRIB_SUPPORT');
		}

		titania::page_footer(true, 'contributions/contribution_support.html');
	break;
}