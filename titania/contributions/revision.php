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

titania::_include('functions_posting', 'generate_type_select');

load_contrib();

if (!titania::$contrib->is_author && !titania::$contrib->is_active_coauthor && !phpbb::$auth->acl_get('u_titania_mod_contrib_mod') && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
{
	titania::needs_auth();
}
else if (in_array(titania::$contrib->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED)) && !(phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')))
{
	// Editing cleaned/disabled contribs requires moderation permissions
	titania::needs_auth();
}

$step = request_var('step', 0);
$revision_id = request_var('revision_id', 0);

// Repack a revision (for those with moderator permissions)
$repack = (phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')) ? request_var('repack', 0) : 0;
if ($repack)
{
	$old_revision = new titania_revision(titania::$contrib, $repack);
	if (!$old_revision->load())
	{
		trigger_error('NO_REVISION');
	}
	$old_revision->load_phpbb_versions();
	generate_phpbb_version_select($old_revision->get_selected_branches());

	// Assign some defaults
	phpbb::$template->assign_vars(array(
		'REVISION_NAME'		=> $old_revision->revision_name,
		'REVISION_VERSION'	=> $old_revision->revision_version,
		'S_REPACK'			=> true,
	));
}
else
{
	if (titania::$contrib->in_queue())
	{
		trigger_error('REVISION_IN_QUEUE');
	}

	generate_phpbb_version_select();

	if (titania::$config->use_queue && titania_types::$types[titania::$contrib->contrib_type]->use_queue)
	{
		$queue = new titania_queue();
		// Load the message object
		$message_object = new titania_message($queue);
		$message_object->set_auth(array(
			'bbcode'      => phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'      => phpbb::$auth->acl_get('u_titania_smilies'),
		));
		$message_object->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
		));

		$message_object->display();
	}
}

do{
	$revision_attachment = $revision = false;
	$display_main = false; // Display the main upload page?
	$next_step = $step + 1; // Default to the next step...
	$try_again = false; // Try again?  Used when skip steps
	$error = array();

	switch ($step)
	{
		case 0 :
			$revision_attachment = new titania_attachment(TITANIA_CONTRIB, titania::$contrib->contrib_id);
			phpbb::$template->assign_var('REVISION_UPLOADER', $revision_attachment->parse_uploader('posting/attachments/revisions.html'));
		break;

		case 1 :
			// Upload the revision
			$revision_attachment = new titania_attachment(TITANIA_CONTRIB, titania::$contrib->contrib_id);
			$revision_attachment->is_orphan = false;
			$revision_attachment->upload();
			$revision_version = utf8_normalize_nfc(request_var('revision_version', '', true));
			$queue_allow_repack = request_var('queue_allow_repack', 0);

			// Check for errors
			$error = array_merge($error, $revision_attachment->error);
			if (!$revision_attachment->uploaded)
			{
				$error[] = phpbb::$user->lang['NO_REVISION_ATTACHMENT'];
			}
			if (!$revision_version)
			{
				$error[] = phpbb::$user->lang['NO_REVISION_VERSION'];
			}

			// phpBB branches
			$allowed_branches = array_keys(get_allowed_phpbb_branches());
			if (sizeof($allowed_branches) == 1)
			{
				$selected_branches = $allowed_branches;
			}
			else
			{
				$selected_branches = request_var('phpbb_branch', array(0));
				$selected_branches = array_intersect($selected_branches, $allowed_branches);

				if (!sizeof($selected_branches))
				{
					$error[] = phpbb::$user->lang['NO_PHPBB_BRANCH'];
				}
			}

			if (!sizeof($error))
			{
				// Success, create a new revision to start
				$revision = new titania_revision(titania::$contrib);
				$revision->__set_array(array(
					'attachment_id'			=> $revision_attachment->attachment_id,
					'revision_name'			=> utf8_normalize_nfc(request_var('revision_name', '', true)),
					'revision_version'		=> $revision_version,
					'queue_allow_repack'	=> $queue_allow_repack,
				));
				$revision->phpbb_versions = $selected_branches;
				$revision->submit();
				$revision_id = $revision->revision_id;

				// Create the queue
				$revision->update_queue();

				$queue = $revision->get_queue();

				// Add queue values to the queue table
				if ($queue)
				{
					// Load the message object
					$message_object = new titania_message($queue);
					$message_object->set_auth(array(
						'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
						'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
					));
					$message_object->set_settings(array(
						'display_error'		=> false,
						'display_subject'	=> false,
					));
					$queue->post_data($message_object);

					$queue->queue_allow_repack = $queue_allow_repack;
					$queue->submit();
				}

				if (!titania_types::$types[titania::$contrib->contrib_type]->clean_and_restore_root)
				{
					// Skip the whole thing if we have nothing else to do
					if (!titania_types::$types[titania::$contrib->contrib_type]->mpv_test && !titania_types::$types[titania::$contrib->contrib_type]->automod_test)
					{
						// Repack if that's what we want
						if ($repack)
						{
							$revision->repack($old_revision);
						}

						$revision->revision_submitted = true;
						$revision->submit();

						// After revision is set to submitted we must update the queue
						$revision->update_queue();

						if ($repack && titania::$config->use_queue && titania_types::$types[titania::$contrib->contrib_type]->use_queue)
						{
							redirect(titania_url::build_url('manage/queue', array('q' => $revision->revision_queue_id)));
						}

						// Subscriptions
						$queue = $revision->get_queue();
						if ($queue)
						{
							$email_vars = array(
								'NAME'		=> phpbb::$user->lang['VALIDATION'] . ' - ' . titania::$contrib->contrib_name . ' - ' . $revision->revision_version,
								'U_VIEW'	=> titania_url::build_url('manage/queue', array('q' => $queue->queue_id)),
							);
							titania_subscriptions::send_notifications(TITANIA_QUEUE, titania::$contrib->contrib_type, 'subscribe_notify_forum.txt', $email_vars, phpbb::$user->data['user_id']);
						}

						redirect(titania::$contrib->get_url());
					}

					phpbb::$template->assign_var('S_NEW_REVISION_SUBMITTED', true);

					break;
				}

				$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
				$new_dir_name = titania::$contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision_version));
				$download_package = titania_url::build_url('download', array('id' => $revision_attachment->attachment_id));

				// Start up the machine
				$contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);

				// Clean the package
				$contrib_tools->clean_package();

				// Restore the root package directory
				if (is_array(titania_types::$types[titania::$contrib->contrib_type]->root_search))
				{
					$package_root = $contrib_tools->find_root(false, titania_types::$types[titania::$contrib->contrib_type]->root_search);
				}
				else
				{
					$package_root = $contrib_tools->find_root();
				}
				$contrib_tools->restore_root($package_root);

				// Copy the modx install file
				if (titania_types::$types[titania::$contrib->contrib_type]->display_install_file)
				{
					$contrib_tools->copy_modx_install(titania::$config->modx_storage_path . $revision->revision_id);
				}

				$error = array_merge($error, $contrib_tools->error);

				if (!sizeof($error))
				{
					phpbb::$template->assign_var('S_NEW_REVISION_SUBMITTED', true);

					// Replace the uploaded zip package with the new one
					$contrib_tools->replace_zip();

					if (titania_types::$types[titania::$contrib->contrib_type]->mpv_test && titania::$config->use_queue && titania_types::$types[titania::$contrib->contrib_type]->use_queue)
					{
						phpbb::$template->assign_var('MPV_TEST_WARNING', true);
					}
				}

				// Remove our temp files
				$contrib_tools->remove_temp_files();
			}
		break;

		case 2 :
			if (!titania_types::$types[titania::$contrib->contrib_type]->mpv_test || !titania::$config->use_queue || !titania_types::$types[titania::$contrib->contrib_type]->use_queue)
			{
				$step = 3;
				$try_again = true;
				continue;
			}

			$revision = new titania_revision(titania::$contrib, $revision_id);
			if (!$revision->load())
			{
				trigger_error('NO_REVISION');
			}
			$revision_attachment = new titania_attachment(TITANIA_CONTRIB);
			$revision_attachment->attachment_id = $revision->attachment_id;
			if (!$revision_attachment->load())
			{
				trigger_error('ERROR_NO_ATTACHMENT');
			}

			$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
			$download_package = titania_url::build_url('download', array('id' => $revision_attachment->attachment_id));

			// Start up the machine
			$contrib_tools = new titania_contrib_tools($zip_file);

			// Run MPV
			$mpv_results = $contrib_tools->mpv($download_package);

			if ($mpv_results === false)
			{
				// Assign this error separately, it's not something wrong with the package but some server issue
				phpbb::$template->assign_var('NOTICE', implode('<br />', $contrib_tools->error));
			}
			else
			{
				$uid = $bitfield = $flags = false;
				generate_text_for_storage($mpv_results, $uid, $bitfield, $flags, true, true, true);

				// Add the MPV Results to the queue
				$queue = $revision->get_queue();
				$queue->mpv_results = $mpv_results;
				$queue->mpv_results_bitfield = $bitfield;
				$queue->mpv_results_uid = $uid;
				$queue->submit();

				$mpv_results = titania_generate_text_for_display($mpv_results, $uid, $bitfield, $flags);
				phpbb::$template->assign_var('MPV_RESULTS', $mpv_results);

				phpbb::$template->assign_var('S_AUTOMOD_TEST', titania_types::$types[titania::$contrib->contrib_type]->automod_test);
			}
		break;

		case 3 :
			if (!titania_types::$types[titania::$contrib->contrib_type]->automod_test || !titania::$config->use_queue || !titania_types::$types[titania::$contrib->contrib_type]->use_queue)
			{
				$step = 4;
				$try_again = true;
				continue;
			}

			$revision = new titania_revision(titania::$contrib, $revision_id);
			if (!$revision->load())
			{
				trigger_error('NO_REVISION');
			}
			$revision_attachment = new titania_attachment(TITANIA_CONTRIB);
			$revision_attachment->attachment_id = $revision->attachment_id;
			if (!$revision_attachment->load())
			{
				trigger_error('ERROR_NO_ATTACHMENT');
			}

			$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
			$download_package = titania_url::build_url('download', array('id' => $revision_attachment->attachment_id));
			$new_dir_name = titania::$contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version));

			// Start up the machine
			$contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);

			// Automod testing time
			$details = '';
			$html_results = $bbcode_results = array();
			$sql = 'SELECT row_id, phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
				WHERE revision_id = ' . $revision->revision_id;
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$version_string = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' .$row['phpbb_version_revision'];
				$phpbb_path = $contrib_tools->automod_phpbb_files($version_string);

				if ($phpbb_path === false)
				{
					$error = array_merge($error, $contrib_tools->error);
					continue;
				}

				phpbb::$template->assign_vars(array(
					'PHPBB_VERSION'		=> $version_string,
					'TEST_ID'			=> $row['row_id'],
				));

				$html_result = $bbcode_result = '';
				$installed = $contrib_tools->automod($phpbb_path, $details, $html_result, $bbcode_result);

				$html_results[] = $html_result;
				$bbcode_results[] = $bbcode_result;
			}
			phpbb::$db->sql_freeresult($result);

			$html_results = implode('<br /><br />', $html_results);
			$bbcode_results = implode("\n\n", $bbcode_results);

			// Update the queue with the results
			$queue = $revision->get_queue();
			$queue->automod_results = $bbcode_results;
			$queue->submit();

			phpbb::$template->assign_var('AUTOMOD_RESULTS', $html_results);

			// Remove our temp files
			$contrib_tools->remove_temp_files();
		break;

		// Translation validation
		case 4 :

		  if (!titania_types::$types[titania::$contrib->contrib_type]->validate_translation)
		  {
			$step = 5;
			$try_again = true;
			continue;
		  }

		  $validation_tools = new translation_validation($zip_file, $new_dir_name);

		  $missing_keys = $validation_tools->check_language_keys();

		  phpbb::$template->assign_var('MISSING_KEYS', $missing_keys);

		  $contrib_tools->remove_temp_files();

		break;

		case 5 :
			$revision = new titania_revision(titania::$contrib, $revision_id);
			if (!$revision->load())
			{
				trigger_error('NO_REVISION');
			}

			// Repack if that's what we want
			if ($repack)
			{
				$revision->repack($old_revision);
			}

			// Update the revision to be submitted, which unhides the queue topic and updates the contrib_last_update time
			$revision->revision_submitted = true;
			$revision->submit();

			// Update the queue (make visible)
			$revision->update_queue();

			if ($repack && titania::$config->use_queue && titania_types::$types[titania::$contrib->contrib_type]->use_queue)
			{
				redirect(titania_url::build_url('manage/queue', array('q' => $revision->revision_queue_id)));
			}

			// Subscriptions
			$queue = $revision->get_queue();
			if ($queue)
			{
				$email_vars = array(
					'NAME'		=> phpbb::$user->lang['VALIDATION'] . ' - ' . titania::$contrib->contrib_name . ' - ' . $revision->revision_version,
					'U_VIEW'	=> titania_url::build_url('manage/queue', array('q' => $queue->queue_id)),
				);
				titania_subscriptions::send_notifications(TITANIA_QUEUE, titania::$contrib->contrib_type, 'subscribe_notify_forum.txt', $email_vars, phpbb::$user->data['user_id']);
			}

			redirect(titania::$contrib->get_url());
		break;
	}
} while($try_again);

phpbb::$template->assign_vars(array(
	'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
	'NEXT_STEP'			=> $next_step,
	'REVISION_ID'		=> $revision_id,
	'AGREEMENT_NOTICE'	=> (titania_types::$types[titania::$contrib->contrib_type]->upload_agreement) ? ((isset(phpbb::$user->lang[titania_types::$types[titania::$contrib->contrib_type]->upload_agreement])) ? nl2br(phpbb::$user->lang[titania_types::$types[titania::$contrib->contrib_type]->upload_agreement]): nl2br(titania_types::$types[titania::$contrib->contrib_type]->upload_agreement)) : false,

	'S_POST_ACTION'		=> ($repack) ? titania_url::append_url(titania::$contrib->get_url('revision'), array('repack' => $repack)) : titania::$contrib->get_url('revision'),
));

if ($display_main || sizeof($error))
{
	if (sizeof($error))
	{
		if ($revision_attachment !== false)
		{
			$revision_attachment->delete();
		}
		if ($revision !== false)
		{
			$revision->delete();
		}
	}
	$revision_attachment = new titania_attachment(TITANIA_CONTRIB, titania::$contrib->contrib_id);
	phpbb::$template->assign_vars(array(
		'REVISION_UPLOADER'		=> $revision_attachment->parse_uploader('posting/attachments/revisions.html'),
		'NEXT_STEP'				=> 1,
	));
}

add_form_key('postform');

titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['NEW_REVISION']);
titania::page_footer(true, 'contributions/contribution_revision.html');