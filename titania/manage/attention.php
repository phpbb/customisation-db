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

if (!phpbb::$auth->acl_gets('u_titania_mod_author_mod', 'u_titania_mod_contrib_mod', 'u_titania_mod_faq_mod', 'u_titania_mod_post_mod') && !sizeof(titania_types::find_authed('moderate')))
{
	titania::needs_auth();
}

phpbb::$user->add_lang('mcp');

$attention_id = request_var('a', 0);
$object_type = request_var('type', 0);
$object_id = request_var('id', 0);

$close = (isset($_POST['close'])) ? true : false;
$approve = (isset($_POST['approve'])) ? true : false;
$disapprove = (isset($_POST['disapprove'])) ? true : false;
$delete = (isset($_POST['delete'])) ? true : false;

if ($attention_id || ($object_type && $object_id))
{
	if ($attention_id)
	{
		$row = attention_overlord::load_attention($attention_id);
		if (!$row)
		{
			trigger_error('NO_ATTENTION_ITEM');
		}

		// Setup
		$attention_object = new titania_attention;
		$attention_object->__set_array($row);
		$object_type = (int) $attention_object->attention_object_type;
		$object_id = (int) $attention_object->attention_object_id;
	}

	// Close, approve, or disapprove the items
	if ($close || $approve || $disapprove || $delete)
	{
		if (!check_form_key('attention'))
		{
			trigger_error('FORM_INVALID');
		}

		if ($delete)
		{
			$sql = 'DELETE FROM ' . TITANIA_ATTENTION_TABLE . '
					WHERE attention_object_id = ' . (int) $object_id . '
						AND attention_object_type = ' . (int) $object_type . '
						AND attention_close_time = 0
						AND attention_type = ' . TITANIA_ATTENTION_REPORTED;
		}
		else
		{
			$sql_ary = array(
				'attention_close_time'	=> titania::$time,
				'attention_close_user'	=> phpbb::$user->data['user_id'],
			);

			$sql = 'UPDATE ' . TITANIA_ATTENTION_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE attention_object_id = ' . (int) $object_id . '
					AND attention_object_type = ' . (int) $object_type . '
					AND attention_type = ' . (($close) ? TITANIA_ATTENTION_REPORTED : TITANIA_ATTENTION_UNAPPROVED);
		}
		phpbb::$db->sql_query($sql);
	}
	add_form_key('attention');

	// Display the current attention items
	$options = array(
		'attention_object_id'	=> $object_id,
	);
	attention_overlord::display_attention_list($options);

	// Display the old (closed) attention items
	$options['only_closed'] = true;
	$options['template_block'] = 'attention_closed';
	attention_overlord::display_attention_list($options);

	switch ($object_type)
	{
		case TITANIA_POST :
			$post = new titania_post;
			$post->post_id = $object_id;
			if (!$post->load())
			{
				$attention_object->delete();
				trigger_error('NO_POST');
			}

			// Close or approve the report
			if ($close)
			{
				$post->post_reported = false;
				$post->submit();

				$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
					WHERE topic_id = ' . $post->topic_id . '
						AND post_reported = 1';
				phpbb::$db->sql_query($sql);
				$cnt = phpbb::$db->sql_fetchfield('cnt');
				phpbb::$db->sql_freeresult();

				if (!$cnt)
				{
					$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
						SET topic_reported = 0
						WHERE topic_id = ' . $post->topic_id;
					phpbb::$db->sql_query($sql);
				}
			}
			if ($approve)
			{
				$post->post_approved = 1;
				$post->submit();

				// Load z topic
				$post->topic->topic_id = $post->topic_id;
				$post->topic->load();

				// Subscriptions?
				if ($post->topic->topic_last_post_id == $post->post_id)
				{
					$email_vars = array(
						'NAME'		=> $post->topic->topic_subject,
						'U_VIEW'	=> titania_url::append_url($post->topic->get_url(), array('view' => 'unread', '#' => 'unread')),
					);
					titania_subscriptions::send_notifications(TITANIA_TOPIC, $post->topic_id, 'subscribe_notify.txt', $email_vars, $post->post_user_id);
				}

				$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
					WHERE topic_id = ' . $post->topic_id . '
						AND post_approved = 0';
				phpbb::$db->sql_query($sql);
				$cnt = phpbb::$db->sql_fetchfield('cnt');

				if (!$cnt)
				{
					$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
						SET topic_approved = 1
						WHERE topic_id = ' . $post->topic_id;
					phpbb::$db->sql_query($sql);

					// Subscriptions
					if ($post->topic->topic_last_post_id == $post->post_id)
					{
						$email_vars = array(
							'NAME'		=> $post->topic->topic_subject,
							'U_VIEW'	=> $post->topic->get_url(),
						);
						titania_subscriptions::send_notifications($post->post_type, $post->topic->parent_id, 'subscribe_notify_forum.txt', $email_vars, $post->post_user_id);
					}
				}
			}

			users_overlord::load_users(array($post->post_user_id, $post->post_edit_user, $post->post_delete_user));
			users_overlord::assign_details($post->post_user_id, 'POSTER_', true);

			phpbb::$template->assign_vars(array(
				'POST_SUBJECT'		=> censor_text($post->post_subject),
				'POST_DATE'			=> phpbb::$user->format_date($post->post_time),
				'POST_TEXT'			=> $post->generate_text_for_display(),
				'EDITED_MESSAGE'	=> ($post->post_edited) ? sprintf(phpbb::$user->lang['EDITED_MESSAGE'], users_overlord::get_user($post->post_edit_user, '_full'), phpbb::$user->format_date($post->post_edited)) : '',
				'DELETED_MESSAGE'	=> ($post->post_deleted != 0) ? sprintf(phpbb::$user->lang['DELETED_MESSAGE'], users_overlord::get_user($post->post_delete_user, '_full'), phpbb::$user->format_date($post->post_deleted), $post->get_url('undelete')) : '',
				'POST_EDIT_REASON'	=> censor_text($post->post_edit_reason),

				'U_VIEW'			=> $post->get_url(),
				'U_EDIT'			=> $post->get_url('edit'),
			));
		break;

		case TITANIA_CONTRIB :
			$contrib = new titania_contribution;
			if (!$contrib->load($object_id))
			{
				$attention_object->delete();
				trigger_error('NO_CONTRIB');
			}

			users_overlord::load_users(array($contrib->contrib_user_id));
			users_overlord::assign_details($contrib->contrib_user_id, 'POSTER_', true);

			phpbb::$template->assign_vars(array(
				'POST_SUBJECT'		=> censor_text($contrib->contrib_name),
				'POST_DATE'			=> phpbb::$user->format_date($contrib->contrib_last_update),
				'POST_TEXT'			=> $contrib->generate_text_for_display(),

				'U_VIEW'			=> $contrib->get_url(),
				'U_EDIT'			=> $contrib->get_url('manage'),
			));
		break;

		default :
			trigger_error('NO_ATTENTION_TYPE');
		break;
	}

	titania::page_header('ATTENTION');

	titania::page_footer(true, 'manage/attention_details.html');
}
else
{
	$type = request_var('type', '');
	if (isset($_POST['sort']))
	{
		$closed = (isset($_POST['closed'])) ? true : false;
		$open = (isset($_POST['open']) || !$closed) ? true : false;
	}
	else
	{
		$closed = request_var('closed', false);
		$open = (request_var('open', false) || !$closed) ? true : false;
	}

	/*$close = (isset($_POST['close'])) ? true : false;
	$id_list = request_var('id_list', array(0));

	if ($close && sizeof($id_list))
	{
		$attention_object = new titania_attention;
		foreach ($id_list as $attention_id)
		{
			$attention_object->attention_id = $attention_id;
			$attention_object->load();
		}
	}*/

	switch ($type)
	{
		case 'reported' :
			$type = TITANIA_ATTENTION_REPORTED;
		break;

		case 'unapproved' :
			$type = TITANIA_ATTENTION_UNAPPROVED;
		break;

		default :
			$type = false;
		break;
	}


	$options = array(
		'attention_type'	=> $type,
		'display_closed'	=> $closed,
		'only_closed'		=> (!$open && $closed) ? true : false,
	);
	attention_overlord::display_attention_list($options);

	$additional = array();
	if (!$open)
	{
		$additional['open'] = 0;
	}
	if ($closed)
	{
		$additional['closed'] = 1;
	}

	phpbb::$template->assign_vars(array(
		'S_ACTION'			=> titania_url::build_url('manage/attention', $additional),
		'S_OPEN_CHECKED'	=> $open,
		'S_CLOSED_CHECKED'	=> $closed,
	));

	// Subscriptions
	titania_subscriptions::handle_subscriptions(TITANIA_ATTENTION, 0, titania_url::build_url('manage/attention', $additional));

	titania::page_header('ATTENTION');

	titania::page_footer(true, 'manage/attention.html');
}