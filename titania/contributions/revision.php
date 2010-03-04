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
else if (titania::$contrib->contrib_status == TITANIA_CONTRIB_CLEANED && !(phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')))
{
	// Editing cleaned contribs requires moderation permissions
	titania::needs_auth();
}

if (titania::$contrib->in_queue())
{
	trigger_error('REVISION_IN_QUEUE');
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

	// Assign some defaults
	phpbb::$template->assign_vars(array(
		'REVISION_NAME'		=> $old_revision->revision_name,
		'REVISION_VERSION'	=> $old_revision->revision_version,
	));
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
			$revision_attachment->upload(TITANIA_ATTACH_EXT_CONTRIB);
			$revision_version = utf8_normalize_nfc(request_var('revision_version', '', true));

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

			if (!sizeof($error))
			{
				// Success, create a new revision to start
				$revision = new titania_revision(titania::$contrib);
				$revision->__set_array(array(
					'attachment_id'		=> $revision_attachment->attachment_id,
					'revision_name'		=> utf8_normalize_nfc(request_var('revision_name', '', true)),
					'revision_version'	=> $revision_version,
				));
				$revision->submit();
				$revision_id = $revision->revision_id;

				// Repack if that's what we want
				if ($repack)
				{
					$revision->repack($old_revision);
				}

				if (!titania_types::$types[titania::$contrib->contrib_type]->clean_and_restore_root)
				{
					// Skip the whole thing if we have nothing else to do
					if (!titania_types::$types[titania::$contrib->contrib_type]->mpv_test && !titania_types::$types[titania::$contrib->contrib_type]->automod_test)
					{
						$revision->revision_submitted = true;
						$revision->submit();

						if ($repack)
						{
							redirect(titania_url::build_url('manage/queue', array('q' => $revision->revision_queue_id)));
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
				$contrib_tools->restore_root();

				$error = array_merge($error, $contrib_tools->error);

				if (!sizeof($error))
				{
					phpbb::$template->assign_var('S_NEW_REVISION_SUBMITTED', true);

					// Replace the uploaded zip package with the new one
					$contrib_tools->replace_zip();

					// Remove our temp files
					$contrib_tools->remove_temp_files();

					if (titania_types::$types[titania::$contrib->contrib_type]->mpv_test)
					{
						phpbb::$template->assign_var('MPV_TEST_WARNING', true);
					}
				}
			}
		break;

		case 2 :
			if (!titania_types::$types[titania::$contrib->contrib_type]->mpv_test)
			{
				$step = 3;
				$try_again = true;
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

				$mpv_results = generate_text_for_display($mpv_results, $uid, $bitfield, $flags);
				phpbb::$template->assign_var('MPV_RESULTS', $mpv_results);
			}
		break;
	/* No Automod test for now, have to figure out how to best handle phpBB versions and some other issues
		case 3 :
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

			// Prepare the phpbb files for automod
			$phpbb_path = $contrib_tools->automod_phpbb_files($revision->phpbb_version);

			// Automod test
			$details = $results = '';
			$contrib_tools->automod($phpbb_path, $details, $results);

			phpbb::$template->assign_var('AUTOMOD_RESULTS', $results);
		break;
	*/
		case 3 :
			$revision = new titania_revision(titania::$contrib, $revision_id);
			if (!$revision->load())
			{
				trigger_error('NO_REVISION');
			}

			// Update the revision to be submitted, which unhides the queue topic and updates the contrib_last_update time
			$revision->revision_submitted = true;
			$revision->submit();

			if ($repack)
			{
				redirect(titania_url::build_url('manage/queue', array('q' => $revision->revision_queue_id)));
			}

			redirect(titania::$contrib->get_url());
		break;
	}
} while($try_again);

phpbb::$template->assign_vars(array(
	'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
	'NEXT_STEP'			=> $next_step,
	'REVISION_ID'		=> $revision_id,

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

titania::page_header('NEW_REVISION');
titania::page_footer(true, 'contributions/contribution_revision.html');