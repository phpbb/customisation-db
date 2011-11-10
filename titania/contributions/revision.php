<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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

if (!phpbb::$auth->acl_get('u_titania_contrib_submit'))
{
	titania::needs_auth();
}
else if (!titania::$contrib->is_author && !titania::$contrib->is_active_coauthor && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
{
	titania::needs_auth();
}
else if (in_array(titania::$contrib->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED)) && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
{
	// Editing cleaned/disabled contribs requires moderation permissions
	titania::needs_auth();
}

$step = request_var('step', 0);
$revision_id = request_var('revision_id', 0);
//$phpbb_versions = titania::$cache->get_phpbb_versions();
//$revision_phpbb_versions = request_var('revision_phpbb_versions', array(''));

$disagree = request_var('disagree', false);
if ($disagree)
{
	// Did not agree to the agreement.
	redirect(titania::$contrib->get_url());
}

// Repack a revision
$repack = request_var('repack', 0);
if ($repack)
{
	$old_revision = new titania_revision(titania::$contrib, $repack);
	if (!$old_revision->load())
	{
		trigger_error('NO_REVISION');
	}
	if (!($old_queue = $old_revision->get_queue()))
	{
		titania::add_lang('manage');
		trigger_error('NO_QUEUE_ITEM');
	}

	titania::$contrib->get_revisions();
	$last_rev_id = (int) max(array_keys(titania::$contrib->revisions));
	$last_rev_status = (int) titania::$contrib->revisions[$last_rev_id]['revision_status'];
	
	// Check auth
	if ((!titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate') && !$old_queue->allow_author_repack) || $last_rev_status == TITANIA_REVISION_DENIED)
	{
		titania::needs_auth();
	}

	$old_revision->load_phpbb_versions();
	generate_phpbb_version_select($old_revision->get_selected_branches());

	// Assign some defaults
	phpbb::$template->assign_vars(array(
		'REVISION_NAME'		=> $old_revision->revision_name,
		'REVISION_VERSION'	=> $old_revision->revision_version,
		'REVISION_LICENSE'	=> $old_revision->revision_license,

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

		$queue->post_data($message_object);
		$message_object->display();
	}
}

$revision_attachment = $revision = false;
$error = array();

if ($step == 1)
{
	// Upload the revision
	$revision_attachment = new titania_attachment(TITANIA_CONTRIB, titania::$contrib->contrib_id);
	$revision_attachment->is_orphan = false;
	$revision_attachment->upload();
	$revision_version = utf8_normalize_nfc(request_var('revision_version', '', true));
	$queue_allow_repack = request_var('queue_allow_repack', 0);
	$revision_license = utf8_normalize_nfc(request_var('revision_license', '', true));

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
	if (sizeof(titania_types::$types[titania::$contrib->contrib_type]->license_options) && !titania_types::$types[titania::$contrib->contrib_type]->license_allow_custom && !in_array($revision_license, titania_types::$types[titania::$contrib->contrib_type]->license_options))
	{
		$error[] = phpbb::$user->lang['INVALID_LICENSE'];
	}

	// Do some simple error checking on the versions
	/*if (empty($revision_phpbb_versions))
	{
		$error[] = phpbb::$user->lang['MUST_SELECT_ONE_VERSION'];
	}
	else
	{
		foreach ($revision_phpbb_versions as $revision_phpbb_version)
		{
			if (!$revision_phpbb_version || strlen($revision_phpbb_version) < 5 || $revision_phpbb_version[1] != '.' || $revision_phpbb_version[3] != '.')
			{
				$error[] = sprintf(phpbb::$user->lang['BAD_VERSION_SELECTED'], $revision_phpbb_version);
			}
		}
	}*/

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

	// Send the file to the type class so it can do custom error checks
	if ($revision_attachment->uploaded)
	{
		$error = array_merge($error, titania_types::$types[titania::$contrib->contrib_type]->upload_check($revision_attachment));
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
			'revision_license'		=> ($revision_license != phpbb::$user->lang['CUSTOM_LICENSE'] || !titania_types::$types[titania::$contrib->contrib_type]->license_allow_custom) ? $revision_license : utf8_normalize_nfc(request_var('revision_custom_license', '', true)),
		));
		$revision->phpbb_versions = $selected_branches;

		/*$revision->phpbb_versions = array();
		foreach ($revision_phpbb_versions as $revision_phpbb_version)
		{
			if (!isset($versions[(int) $revision_phpbb_version[0] . (int) $revision_phpbb_version[2] . substr($revision_phpbb_version, 4)]))
			{
				// Have we added some new phpBB version that does not exist?  We need to purge the cache then
				titania::$cache->destroy('_titania_phpbb_versions');
			}

			// Update the list of phpbb_versions for the revision to update
			$revision->phpbb_versions[] = array(
				'phpbb_version_branch'		=> (int) $revision_phpbb_version[0] . (int) $revision_phpbb_version[2],
				'phpbb_version_revision'	=> substr($revision_phpbb_version, 4),
			);
		}*/

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
			if (!titania_types::$types[titania::$contrib->contrib_type]->mpv_test && !titania_types::$types[titania::$contrib->contrib_type]->automod_test && !titania_types::$types[titania::$contrib->contrib_type]->validate_translation)
			{
				// Repack if that's what we want
				if ($repack)
				{
					$revision->repack($old_revision);
				}

				$revision->revision_submitted = true;
				$revision->allow_author_repack = false;
				$revision->submit();

				// After revision is set to submitted we must update the queue
				$revision->update_queue();

				if ($repack)
				{
					if (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate') && titania::$config->use_queue && titania_types::$types[titania::$contrib->contrib_type]->use_queue)
					{
						redirect(titania_url::build_url('manage/queue', array('q' => $revision->revision_queue_id)));
					}

					$old_queue->allow_author_repack = false;
					$old_queue->submit();

					redirect(titania::$contrib->get_url());
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

		if ($package_root === false)
		{
			$error[] = phpbb::$user->lang(titania_types::$types[titania::$contrib->contrib_type]->root_not_found_key);
		}
		else
		{
			$contrib_tools->restore_root($package_root);

			// Copy the modx install file
			if (titania_types::$types[titania::$contrib->contrib_type]->display_install_file)
			{
				$contrib_tools->copy_modx_install(titania::$config->modx_storage_path . $revision->revision_id);
			}
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
}
else if ($step > 1)
{
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

	// Start up the machine
	$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
	$contrib_tools = new titania_contrib_tools($zip_file);

	$download_package = titania_url::build_url('download', array('id' => $revision_attachment->attachment_id));

	// Now go through any special steps for this type

	$steps = titania_types::$types[titania::$contrib->contrib_type]->upload_steps;
	$step_cnt = 2;

	foreach ($steps as $step_function)
	{
		$result = false;

		if ($step == $step_cnt)
		{
			if (is_array($step_function) && $step_function[0] == 'contrib_tools')
			{
				$result = $contrib_tools->{$step_function[1]}(titania::$contrib, $revision, $revision_attachment, $download_package);
			}
			else if (is_array($step_function) && $step_function[0] == 'contrib_type')
			{
				$result = titania_types::$types[titania::$contrib->contrib_type]->{$step_function[1]}(titania::$contrib, $revision, $revision_attachment, $contrib_tools, $download_package);
			}
			else
			{
				$result = call_user_func($step_function, titania::$contrib, $revision, $revision_attachment, $contrib_tools, $download_package);
			}

			if (isset($result['notice']))
			{
				phpbb::$template->assign_var('NOTICE', implode('<br />', $result['notice']));
			}
			if (isset($result['error']))
			{
				$error = array_merge($error, $result['error']);
			}

			break;
		}

		$step_cnt++;
	}
}

// Final step
if ($step > sizeof(titania_types::$types[titania::$contrib->contrib_type]->upload_steps) + 1)
{
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

	// Update the attachment MD5, it may have changed
	$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
		SET hash = \'' . phpbb::$db->sql_escape($contrib_tools->md5_hash) . '\'
		WHERE attachment_id = ' . $revision_attachment->attachment_id;
	phpbb::$db->sql_query($sql);

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

phpbb::$template->assign_vars(array(
	'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
	'NEXT_STEP'				=> ($step + 1),
	'REVISION_ID'			=> $revision_id,
	'AGREEMENT_NOTICE'		=> (titania_types::$types[titania::$contrib->contrib_type]->upload_agreement) ? ((isset(phpbb::$user->lang[titania_types::$types[titania::$contrib->contrib_type]->upload_agreement])) ? nl2br(phpbb::$user->lang[titania_types::$types[titania::$contrib->contrib_type]->upload_agreement]): nl2br(titania_types::$types[titania::$contrib->contrib_type]->upload_agreement)) : false,
	'QUEUE_ALLOW_REPACK'	=> true,

	'S_POST_ACTION'			=> ($repack) ? titania_url::append_url(titania::$contrib->get_url('revision'), array('repack' => $repack)) : titania::$contrib->get_url('revision'),
));

// Output the available license options
foreach (titania_types::$types[titania::$contrib->contrib_type]->license_options as $option)
{
	phpbb::$template->assign_block_vars('license_options', array(
		'NAME'		=> $option,
		'VALUE'		=> $option,
	));
}

// Display the list of phpBB versions available
/*$allowed_branches = get_allowed_phpbb_branches();
foreach ($phpbb_versions as $version => $name)
{
	if (!isset($allowed_branches[substr($version, 0, 2)]))
	{
		continue;
	}

	$template->assign_block_vars('phpbb_versions', array(
		'VERSION'		=> $name,
		'S_SELECTED'	=> (in_array($name, $revision_phpbb_versions)) ? true : false,
	));
}*/

// Display the main page
if ($step == 0 || sizeof($error))
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
		'REVISION_NAME'				=> utf8_normalize_nfc(request_var('revision_name', '', true)),
		'REVISION_VERSION'			=> utf8_normalize_nfc(request_var('revision_version', '', true)),
		'REVISION_LICENSE'			=> utf8_normalize_nfc(request_var('revision_license', '', true)),
		'REVISION_CUSTOM_LICENSE'	=> utf8_normalize_nfc(request_var('revision_custom_license', '', true)),
		'QUEUE_ALLOW_REPACK'		=> request_var('queue_allow_repack', 1),

		'NEXT_STEP'					=> 1,

		'S_CUSTOM_LICENSE'					=> (utf8_normalize_nfc(request_var('revision_license', '', true)) == phpbb::$user->lang['CUSTOM_LICENSE']) ? true : false,
		'S_ALLOW_CUSTOM_LICENSE'			=> (titania_types::$types[titania::$contrib->contrib_type]->license_allow_custom) ? true : false,
	));

	// Assign separately so we can output some data first
	phpbb::$template->assign_var('REVISION_UPLOADER', $revision_attachment->parse_uploader('posting/attachments/revisions.html'));
}

if (isset($contrib_tools) && is_object($contrib_tools))
{
	$contrib_tools->remove_temp_files();
}

add_form_key('postform');

titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['NEW_REVISION']);
titania::page_footer(true, 'contributions/contribution_revision.html');
