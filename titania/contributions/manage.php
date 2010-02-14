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

if (!titania::$contrib->is_author && !titania::$contrib->is_active_coauthor && !phpbb::$auth->acl_get('m_titania_contrib_mod') && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
{
	trigger_error('NO_AUTH');
}
else if (titania::$contrib->contrib_status == TITANIA_CONTRIB_CLEANED && !(phpbb::$auth->acl_get('m_titania_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')))
{
	// Editing cleaned contribs requires moderation permissions
	trigger_error('NO_AUTH');
}

// Set some main vars up
$submit = (isset($_POST['submit']) || isset($_POST['new_revision'])) ? true : false;
$change_owner = request_var('change_owner', '', true); // Blame Nathan, he said this was okay
$contrib_categories = request_var('contrib_category', array(0));
$active_coauthors = $active_coauthors_list = utf8_normalize_nfc(request_var('active_coauthors', '', true));
$nonactive_coauthors = $nonactive_coauthors_list = utf8_normalize_nfc(request_var('nonactive_coauthors', '', true));

/**
* ---------------------------- Create a new revision ----------------------------
*/
if (request_var('new_revision_step', 0) > 0)
{
	if (titania::$contrib->in_queue())
	{
		trigger_error('REVISION_IN_QUEUE');
	}

	// Each different type requires different handling of revisions
	if (method_exists(titania_types::$types[titania::$contrib->contrib_type], 'create_revision'))
	{
		titania_types::$types[titania::$contrib->contrib_type]->create_revision(titania::$contrib);
	}
	else
	{
		// Basic creation, needs nothing more
		$error = array();
		if (!check_form_key('postform'))
		{
			$error[] = phpbb::$user->lang['FORM_INVALID'];
		}

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

		if (sizeof($error))
		{
			// Start over...
			phpbb::$template->assign_vars(array(
				'REVISION_UPLOADER'		=> $revision_attachment->parse_uploader('posting/attachments/revisions.html'),
				'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
				'STEP'					=> (sizeof($error)) ? ($new_revision_step - 1) : $new_revision_step,
				'NEXT_STEP'				=> (sizeof($error)) ? $new_revision_step : ($new_revision_step + 1),

				'S_NEW_REVISION'		=> true,
			));

			add_form_key('postform');

			titania::page_header('NEW_REVISION');
			titania::page_footer(true, 'contributions/contribution_manage.html');
		}
		else
		{
			// Success, create a new revision
			$revision = new titania_revision(titania::$contrib);
			$revision->__set_array(array(
				'attachment_id'			=> $revision_attachment->attachment_id,
				'revision_name'			=> utf8_normalize_nfc(request_var('revision_name', '', true)),
				'revision_version'		=> $revision_version,
				'revision_submitted'	=> true,
			));
			$revision->submit();
		}
	}

	$submit = false; // Set submit as false to keep the main stuff from being resubmitted again
	titania::error_box('SUCCESS', 'REVISION_SUBMITTED', TITANIA_SUCCESS);
}

/**
* ---------------------------- Confirm main author change ----------------------------
*/
if (titania::confirm_box(true))
{
	if (!titania::$contrib->is_author && !phpbb::$auth->acl_get('m_titania_contrib_mod') && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
	{
		trigger_error('NO_AUTH');
	}

	$change_owner_id = request_var('change_owner_id', 0);

	if ($change_owner_id !== ANONYMOUS && $change_owner_id)
	{
		titania::$contrib->set_contrib_user_id($change_owner_id);

		titania::$contrib->load(utf8_normalize_nfc(request_var('c', '', true))); // Reload the contrib (to make sure the authors list is updated)
		$submit = false; // Set submit as false to keep the main stuff from being resubmitted again
		titania::error_box('SUCCESS', 'CONTRIB_OWNER_UPDATED', TITANIA_SUCCESS);
	}
}

/**
* ---------------------------- Main Page ----------------------------
*/

// Load the message object
$message = new titania_message(titania::$contrib);
$message->set_auth(array(
	'bbcode'	=> phpbb::$auth->acl_get('u_titania_bbcode'),
	'smilies'	=> phpbb::$auth->acl_get('u_titania_smilies'),
));
$message->set_settings(array(
	'display_error'		=> false,
	'display_subject'	=> false,
	'subject_name'		=> 'name',
));

if (isset($_POST['preview']))
{
	titania::$contrib->post_data($message);

	$message->preview();
}
else if ($submit)
{
	titania::$contrib->post_data($message);

	// Begin Error checking
	$error = titania::$contrib->validate($contrib_categories);

	if (($validate_form_key = $message->validate_form_key()) !== false)
	{
		$error[] = $validate_form_key;
	}

	$missing_active = $missing_nonactive = array();
	get_author_ids_from_list($active_coauthors_list, $missing_active);
	get_author_ids_from_list($nonactive_coauthors_list, $missing_nonactive);
	if (sizeof($missing_active) || sizeof($missing_nonactive))
	{
		$error[] = sprintf(phpbb::$user->lang['COULD_NOT_FIND_USERS'], implode(', ', array_merge($missing_active, $missing_nonactive)));
	}
	if (array_intersect($active_coauthors_list, $nonactive_coauthors_list))
	{
		$error[] = sprintf(phpbb::$user->lang['DUPLICATE_AUTHORS'], implode(', ', array_keys(array_intersect($active_coauthors_list, $nonactive_coauthors_list))));
	}
	if ($change_owner != '')
	{
		$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . phpbb::$db->sql_escape(utf8_clean_string($change_owner)) . "'";
			$result = phpbb::$db->sql_query($sql);
			$change_owner_id = (int) phpbb::$db->sql_fetchfield('user_id');
			phpbb::$db->sql_freeresult($result);

		if ($change_owner_id < 1)
		{
			$error[] = sprintf(phpbb::$user->lang['CONTRIB_CHANGE_OWNER_NOT_FOUND'], $change_owner);
		}
	}

	// Did we succeed or have an error?
	if (!sizeof($error))
	{
		titania::$contrib->submit();

		titania::$contrib->set_coauthors($active_coauthors_list, $nonactive_coauthors_list, true);

		// Create relations
		titania::$contrib->put_contrib_in_categories($contrib_categories);

		if ($change_owner == '' && !isset($_POST['new_revision']))
		{
			titania::error_box('SUCCESS', 'CONTRIB_UPDATED', TITANIA_SUCCESS);
		}
		else if ($change_owner)
		{
			$s_hidden_fields = array(
				'submit'			=> true,
				'change_owner'		=> $change_owner,
				'change_owner_id'	=> $change_owner_id,
			);

			titania::confirm_box(false, sprintf(phpbb::$user->lang['CONTRIB_CONFIRM_OWNER_CHANGE'], '<a href="' .  phpbb::append_sid('memberlist', 'mode=viewprofile&amp;u=' . $change_owner_id) . '">' . $change_owner . '</a>'), titania_url::append_url(titania_url::$current_page), $s_hidden_fields);
		}

		// Begin the stuff for uploading a new revision (this is continued above on the next page submission)
		if (isset($_POST['new_revision']))
		{
			if (titania::$contrib->in_queue())
			{
				trigger_error('REVISION_IN_QUEUE');
			}

			$revision_attachment = new titania_attachment(TITANIA_CONTRIB, titania::$contrib->contrib_id);
			phpbb::$template->assign_vars(array(
				'REVISION_UPLOADER'		=> $revision_attachment->parse_uploader('posting/attachments/revisions.html'),
				'STEP'					=> 0,
				'NEXT_STEP'				=> 1,

				'S_NEW_REVISION'		=> true,
			));
		}
	}
}
else
{
	$sql = 'SELECT category_id
		FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
		WHERE contrib_id = ' . titania::$contrib->contrib_id;
	$result = phpbb::$db->sql_query($sql);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$contrib_categories[] = $row['category_id'];
	}
	phpbb::$db->sql_freeresult($result);

	$active_coauthors = $nonactive_coauthors = array();
	foreach (titania::$contrib->coauthors as $row)
	{
		if ($row['active'])
		{
			$active_coauthors[] = $row['username'];
		}
		else
		{
			$nonactive_coauthors[] = $row['username'];
		}
	}
	$active_coauthors = implode("\n", $active_coauthors);
	$nonactive_coauthors = implode("\n", $nonactive_coauthors);
}

// Generate some stuff
generate_type_select(titania::$contrib->contrib_type);
generate_category_select($contrib_categories);
titania::$contrib->assign_details();
$message->display();

phpbb::$template->assign_vars(array(
	'S_POST_ACTION'				=> titania::$contrib->get_url('manage'),
	'S_SUBMIT_NEW_REVISION'		=> true,//(!titania::$contrib->in_queue()),  Do we show them the link even if they can not upload?  I say yes so that they receive the error and know why they can not submit a new revision

	'ERROR_MSG'					=> ($submit && sizeof($error)) ? implode('<br />', $error) : false,
	'ACTIVE_COAUTHORS'			=> $active_coauthors,
	'NONACTIVE_COAUTHORS'		=> $nonactive_coauthors,
));

titania::page_header('MANAGE_CONTRIBUTION');
titania::page_footer(true, 'contributions/contribution_manage.html');
