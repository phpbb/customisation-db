<?php
/**
*
* @package Titania
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

$queue_id = request_var('q', 0);
$queue_type = request_var('queue', '');
$tag = request_var('tag', 0);

// Force the queue_type if we have a queue_id
if ($queue_id)
{
	$sql = 'SELECT queue_type FROM ' . TITANIA_QUEUE_TABLE . '
		WHERE queue_id = ' . $queue_id;
	phpbb::$db->sql_query($sql);
	$queue_type = phpbb::$db->sql_fetchfield('queue_type');
}
else
{
	$queue_type = titania_types::type_from_url($queue_type);
}

// Setup the base url we will use
$base_url = titania_url::build_url('manage/queue');

if ($queue_type === false)
{
	// We need to select the queue if they only have one that they can access, else display the list
	$authed = titania_types::find_authed('view');

	if (empty($authed))
	{
		titania::needs_auth();
	}
	else if (sizeof($authed) == 1)
	{
		$queue_type = $authed[0];
	}
	else
	{
		foreach ($authed as $type_id)
		{
			$sql = 'SELECT COUNT(queue_id) AS cnt FROM ' . TITANIA_QUEUE_TABLE . '
				WHERE queue_type = ' . (int) $type_id . '
					AND queue_status > 0';
			phpbb::$db->sql_query($sql);
			$cnt = phpbb::$db->sql_fetchfield('cnt');
			phpbb::$db->sql_freeresult();

			phpbb::$template->assign_block_vars('categories', array(
				'U_VIEW_CATEGORY'	=> titania_url::append_url($base_url, array('queue' => titania_types::$types[$type_id]->url)),
				'CATEGORY_NAME'		=> titania_types::$types[$type_id]->lang,
				'CATEGORY_CONTRIBS' => $cnt,
			));
		}

		phpbb::$template->assign_vars(array(
			'S_QUEUE_LIST'	=> true,
		));

		titania::page_header('VALIDATION_QUEUE');
		titania::page_footer(true, 'manage/queue.html');
	}
}
else
{
	if (!titania_types::$types[$queue_type]->acl_get('view'))
	{
		titania::needs_auth();
	}
}

// Add the queue type to the base url
$base_url = titania_url::append_url($base_url, array('queue' => titania_types::$types[$queue_type]->url));

// Add to Breadcrumbs
titania::generate_breadcrumbs(array(
	titania_types::$types[$queue_type]->lang	=> $base_url,
));

// Main output
if ($queue_id)
{
	phpbb::$user->add_lang('viewforum');

	$action = request_var('action', '');
	
	if ($tag)
	{
		// Add tag to Breadcrumbs
		titania::generate_breadcrumbs(array(
			titania_tags::get_tag_name($tag)	=> titania_url::append_url($base_url, array('tag' => $tag)),
		));	
	}

	switch ($action)
	{
		case 'in_progress' :
			$queue = queue_overlord::get_queue_object($queue_id, true);
			$queue->in_progress();
			redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
		break;

		case 'no_progress' :
			$queue = queue_overlord::get_queue_object($queue_id, true);
			$queue->no_progress();
			redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
		break;

		case 'delete_queue' :
			if (phpbb::$user->data['user_type'] != USER_FOUNDER)
			{
				titania::needs_auth();
			}

			if (titania::confirm_box(true))
			{
				$queue = queue_overlord::get_queue_object($queue_id, true);

				// Update the revision queue id to 0
				$revision = $queue->get_revision();
				if ($revision->revision_status == TITANIA_REVISION_NEW)
				{
					$revision->change_status(TITANIA_REVISION_PULLED_OTHER);
				}
				$revision->revision_queue_id = 0;
				$revision->submit();

				// Delete the queue
				$queue->delete();

				redirect(titania_url::append_url($base_url));
			}
			else
			{
				titania::confirm_box(false, 'DELETE_QUEUE');
			}
		break;

		case 'approve' :
		case 'deny' :
			$queue = queue_overlord::get_queue_object($queue_id, true);

			// Load the contribution
			$contrib = new titania_contribution();
			$contrib->load((int) $queue->contrib_id);

			// Do not allow to approve your own contributions, except for founders...
			if (!titania::$config->allow_self_validation && (phpbb::$user->data['user_type'] != USER_FOUNDER) && ($action == 'approve') && ($contrib->is_author || $contrib->is_active_coauthor || $contrib->is_coauthor))
			{
				titania::needs_auth();
			}

			if (!titania_types::$types[$contrib->contrib_type]->acl_get('validate'))
			{
				titania::needs_auth();
			}

			// Load the message object for the validation reason
			$queue->message_fields_prefix = 'message_validation';
			$message_object = new titania_message($queue);
			$message_object->set_auth(array(
				'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
				'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			));
			$message_object->set_settings(array(
				'display_subject'	=> false,
			));

			$error = array();
			$public_notes = utf8_normalize_nfc(request_var('public_notes', '', true));

			if ($message_object->submit_check())
			{
				// Check form key
				if (($form_error = $message_object->validate_form_key()) !== false)
				{
					$error[] = $form_error;
				}

				if (!sizeof($error))
				{

					if ($action == 'approve')
					{
						$queue->approve($public_notes);

						// Install the style on the demo board?
						if ($contrib->contrib_type == TITANIA_TYPE_STYLE && isset($_POST['style_demo_install']) && titania::$config->demo_style_path)
						{
							// Reload the contrib, it hath changed
							$contrib->load((int) $queue->contrib_id);

							$revision = $queue->get_revision();

							$sql = 'SELECT attachment_directory, physical_filename FROM ' . TITANIA_ATTACHMENTS_TABLE . '
								WHERE attachment_id = ' . (int) $revision->attachment_id;
							$result = phpbb::$db->sql_query($sql);
							$row = phpbb::$db->sql_fetchrow($result);
							phpbb::$db->sql_freeresult($result);

							$contrib_tools = new titania_contrib_tools(titania::$config->upload_path . utf8_basename($row['attachment_directory']) . '/' . utf8_basename($row['physical_filename']));
							if (!($style_id = $contrib_tools->install_demo_style(TITANIA_ROOT . titania::$config->demo_style_path, $contrib)))
							{
								// Oh noez, we habz error
								trigger_error(implode('<br />', $contrib_tools->error));
							}
							else
							{
								// Update the demo link
								$contrib->contrib_demo = sprintf(titania::$config->demo_style_url, $style_id);
								$contrib->submit();
							}
						}
					}
					else
					{
						$queue->deny();
					}

					redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
				}
			}

			$message_object->display();

			// Build the preview message
			titania::add_lang('contributions');
			$revision = $queue->get_revision();

			$public_notes_preview = false;
			if (titania_types::$types[$contrib->contrib_type]->update_public)
			{
				$public_notes_preview = ($action == 'deny') ? false : sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->update_public],
					$revision->revision_version
				) . (($public_notes) ? sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->update_public . '_NOTES'], $public_notes) : '');
				$uid = $bitfield = $options = false;
				generate_text_for_storage($public_notes_preview, $uid, $bitfield, $options, true, true, true);
				$public_notes_preview = titania_generate_text_for_display($public_notes_preview, $uid, $bitfield, $options);
			}

			phpbb::$template->assign_vars(array(
				'ERROR'						=> implode('<br />', $error),
				'L_TOPIC_REVIEW'			=> phpbb::$user->lang['QUEUE_REVIEW'],
				'PAGE_TITLE_EXPLAIN'		=> phpbb::$user->lang[(($action == 'approve') ? 'APPROVE_QUEUE' : 'DENY_QUEUE') . '_CONFIRM'],

				'PUBLIC_MESSAGE'			=> $public_notes,
				'PUBLIC_PREVIEW_SUBJECT'	=> (isset($_POST['preview'])) ? 'Re: ' . $contrib->contrib_name : false,
				'PUBLIC_PREVIEW_MESSAGE'	=> (isset($_POST['preview'])) ? $public_notes_preview : false,

				'S_CONTRIB_APPROVE'				=> ($action == 'approve') ? true : false,
				'S_STYLE_DEMO_INSTALL'			=> ($action == 'approve' && $contrib->contrib_type == TITANIA_TYPE_STYLE && titania::$config->demo_style_path) ? true : false,
				'S_STYLE_DEMO_INSTALL_CHECKED'	=> (isset($_POST['style_demo_install'])) ? true : false,
				'S_PUBLIC_NOTES'				=> ($action == 'approve' && titania_types::$types[$contrib->contrib_type]->update_public) ? true : false,
				'TOPIC_TITLE'					=> $contrib->contrib_name,
			));

			// Setup the sort tool
			$topic_sort = posts_overlord::build_sort();
			$topic_sort->set_defaults(false, false, 'd');

			// Load the topic
			$topic = new titania_topic;
			$topic->load($queue->queue_topic_id);

			// Display the posts for review
			posts_overlord::display_topic($topic, $topic_sort);

			titania::page_header(phpbb::$user->lang[(($action == 'approve') ? 'APPROVE_QUEUE' : 'DENY_QUEUE')] . ': ' . $contrib->contrib_name);
			titania::page_footer(false, 'manage/queue_validate.html');
		break;

		case 'notes' :
			$queue = queue_overlord::get_queue_object($queue_id, true);

			// Load the message object
			$message_object = new titania_message($queue);
			$message_object->set_auth(array(
				'bbcode'		=> true,
				'smilies'		=> true,
			));
			$message_object->set_settings(array(
				'display_subject'	=> false,
			));

			// Submit check...handles running $post->post_data() if required
			$submit = $message_object->submit_check();

			if ($submit)
			{
				$queue->submit();
				redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
			}

			$message_object->display();

			// Common stuff
			phpbb::$template->assign_vars(array(
				'S_POST_ACTION'		=> titania_url::$current_page_url,
				'L_POST_A'			=> phpbb::$user->lang['EDIT_VALIDATION_NOTES'],
			));
			titania::page_header('EDIT_VALIDATION_NOTES');
			titania::page_footer(true, 'manage/queue_post.html');
		break;

		case 'rebuild' :
			$queue = queue_overlord::get_queue_object($queue_id, true);

			$queue->update_first_queue_post();

			redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
		break;

		case 'allow_author_repack' :
			$queue = queue_overlord::get_queue_object($queue_id, true);
			
			$topic = $queue->get_queue_discussion_topic();
			$post = new titania_post(TITANIA_QUEUE_DISCUSSION, $topic);
			$post->__set_array(array(
				'post_subject'		=> 'Re: ' . $post->topic->topic_subject,
			));

			// Load the message object
			$message_object = new titania_message($post);
			$message_object->set_auth(array(
				'bbcode'		=> true,
				'smilies'		=> true,
			));
			$message_object->set_settings(array(
				'display_subject'	=> false,
			));

			// Submit check...handles running $post->post_data() if required
			$submit = $message_object->submit_check();

			if ($submit)
			{
				$queue->allow_author_repack = true;
				
				$contrib = contribs_overlord::get_contrib_object($queue->contrib_id, true);
				
				$for_edit = $post->generate_text_for_edit();
				$post->post_text = $for_edit['message'] . "\n\n[url=" . titania_url::append_url($contrib->get_url('revision'), array('repack' => $queue->revision_id)) . ']' . phpbb::$user->lang['AUTHOR_REPACK_LINK'] . '[/url]';
				$post->generate_text_for_storage($for_edit['allow_bbcode'], $for_edit['allow_smilies'], $for_edit['allow_urls']);
				$post->submit();
				
				$queue->submit();
				
				$queue->topic_reply('QUEUE_REPLY_ALLOW_REPACK');	
				$queue->submit();
				
				redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
			}

			$message_object->display();

			// Common stuff
			phpbb::$template->assign_vars(array(
				'S_POST_ACTION'		=> titania_url::$current_page_url,
				'L_POST_A'			=> phpbb::$user->lang['DISCUSSION_REPLY_MESSAGE'],
			));
			titania::page_header('DISCUSSION_REPLY_MESSAGE');
			titania::page_footer(true, 'manage/queue_post.html');
		break;

		case 'move' :
			$queue = queue_overlord::get_queue_object($queue_id, true);

			$tags = titania::$cache->get_tags(TITANIA_QUEUE);

			if (check_link_hash(request_var('hash', ''), 'quick_actions') || titania::confirm_box(true))
			{
				$new_tag = request_var('id', 0);

				if (!isset($tags[$new_tag]))
				{
					trigger_error('NO_TAG');
				}

				$queue->move($new_tag);
			}
			else
			{
				// Generate the list of tags we can move it to
				$extra = '<select name="id">';
				foreach ($tags as $tag_id => $row)
				{
					$extra .= '<option value="' . $tag_id . '">' . ((isset(phpbb::$user->lang[$row['tag_field_name']])) ? phpbb::$user->lang[$row['tag_field_name']] : $row['tag_field_name']) . '</option>';
				}
				$extra .= '</select>';
				phpbb::$template->assign_var('CONFIRM_EXTRA', $extra);

				titania::confirm_box(false, 'MOVE_QUEUE');
			}
			redirect(titania_url::append_url($base_url, array('q' => $queue->queue_id)));
		break;
	}

	// Display the main queue item
	$data = queue_overlord::display_queue_item($queue_id);

	// Handle replying/editing/etc
	$posting_helper = new titania_posting();
	$posting_helper->act('manage/queue_post.html');

	// Display the posts in the queue (after the posting helper acts)
	posts_overlord::display_topic_complete($data['topic']);

	titania::page_header(queue_overlord::$queue[$queue_id]['topic_subject']);
}
else
{
	// Subscriptions
	titania_subscriptions::handle_subscriptions(TITANIA_QUEUE, $queue_type, titania_url::$current_page_url);

	queue_overlord::display_queue($queue_type, $tag);
	queue_overlord::display_categories($queue_type, $tag);

	titania::page_header('VALIDATION_QUEUE');
}

titania::page_footer(true, 'manage/queue.html');
