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

load_contrib();

// Editing a revision can only be done by moderators
if (!titania::$contrib->is_author && !titania::$contrib->is_active_coauthor && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
{
	titania::needs_auth();
}

// Setup some variables
$revision_id = request_var('revision', 0);
$error = $revision_phpbb_versions = array();
$phpbb_versions = titania::$cache->get_phpbb_versions();

// Load the revision
$revision = new titania_revision(titania::$contrib, $revision_id);
if (!$revision->load())
{
	trigger_error('NO_REVISION');
}

// Translations
$translation = new titania_attachment(TITANIA_TRANSLATION, $revision_id);
$translation->load_attachments();
$translation->upload();
$error = array_merge($error, $translation->error);

// Revision phpBB versions
$revision->load_phpbb_versions();
foreach ($revision->phpbb_versions as $row)
{
	$revision_phpbb_versions[] = $phpbb_versions[$row['phpbb_version_branch'] . $row['phpbb_version_revision']];
}

// Revision Status
$revision_status = request_var('revision_status', (int) $revision->revision_status);
$status_list = array(
	TITANIA_REVISION_NEW				=> 'REVISION_NEW',
	TITANIA_REVISION_APPROVED			=> 'REVISION_APPROVED',
	TITANIA_REVISION_DENIED				=> 'REVISION_DENIED',
	TITANIA_REVISION_PULLED_SECURITY	=> 'REVISION_PULLED_FOR_SECURITY',
	TITANIA_REVISION_PULLED_OTHER		=> 'REVISION_PULLED_FOR_OTHER',
	TITANIA_REVISION_REPACKED			=> 'REVISION_REPACKED',
	TITANIA_REVISION_RESUBMITTED		=> 'REVISION_RESUBMITTED',
);

if ($translation->uploaded || isset($_POST['submit']))
{
	$revision_license = utf8_normalize_nfc(request_var('revision_license', '', true));
	$revision->__set_array(array(
		'revision_name'			=> utf8_normalize_nfc(request_var('revision_name', $revision->revision_name, true)),
		'revision_license'		=> ($revision_license != phpbb::$user->lang['CUSTOM_LICENSE']) ? $revision_license : utf8_normalize_nfc(request_var('revision_custom_license', '', true)),
	));

	// Stuff that can be done by moderators only
	if (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
	{
		$revision_phpbb_versions = request_var('revision_phpbb_versions', array(''));
	}
}

// Submit the revision
if (isset($_POST['submit']))
{
	if (!check_form_key('postform'))
	{
		$error[] = phpbb::$user->lang['FORM_INVALID'];
	}
	if (sizeof(titania_types::$types[titania::$contrib->contrib_type]->license_options) && !titania_types::$types[titania::$contrib->contrib_type]->license_allow_custom && !in_array($revision->revision_license, titania_types::$types[titania::$contrib->contrib_type]->license_options))
	{
		$error[] = phpbb::$user->lang['INVALID_LICENSE'];
	}

	if (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
	{
		// Delete the revision if that is what we want
		if (isset($_POST['delete']) && !sizeof($error))
		{
			$revision->delete();

			redirect(titania::$contrib->get_url());
		}

		// Do some simple error checking on the versions
		if (empty($revision_phpbb_versions))
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
		}
	}

	// If no errors, submit
	if (!sizeof($error))
	{
		// Update the status
		if ($revision_status != $revision->revision_status && titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate') && !(!titania::$config->allow_self_validation && (phpbb::$user->data['user_type'] != USER_FOUNDER) && ($revision_status == TITANIA_REVISION_APPROVED) && ($contrib->is_author || $contrib->is_active_coauthor || $contrib->is_coauthor)))
		{
			$revision->change_status($revision_status);
		}

		// Update the phpBB versions
		$revision->phpbb_versions = array();
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
		}

		// Submit the translations
		$translation->submit();

		$revision->submit();

		redirect(titania::$contrib->get_url());
	}
}

// Output the available license options
foreach (titania_types::$types[titania::$contrib->contrib_type]->license_options as $option)
{
	phpbb::$template->assign_block_vars('license_options', array(
		'NAME'		=> $option,
		'VALUE'		=> $option,
	));
}

// Display the list of phpBB versions available
foreach ($phpbb_versions as $version => $name)
{
	$template->assign_block_vars('phpbb_versions', array(
		'VERSION'		=> $name,
		'S_SELECTED'	=> (in_array($name, $revision_phpbb_versions)) ? true : false,
	));
}

// Display the status list
foreach ($status_list as $status => $row)
{
	phpbb::$template->assign_block_vars('status_select', array(
		'S_SELECTED'		=> ($status == $revision_status) ? true : false,
		'VALUE'				=> $status,
		'NAME'				=> phpbb::$user->lang[$row],
	));
}

// Display the rest of the page
phpbb::$template->assign_vars(array(
	'ERROR_MSG'					=> (sizeof($error)) ? implode('<br />', $error) : '',
	'REVISION_NAME'				=> $revision->revision_name,
	'REVISION_LICENSE'			=> $revision->revision_license,
	'REVISION_CUSTOM_LICENSE'	=> (!in_array($revision->revision_license, titania_types::$types[titania::$contrib->contrib_type]->license_options)) ? $revision->revision_license : '',

	'TRANSLATION_UPLOADER'		=> (titania_types::$types[titania::$contrib->contrib_type]->extra_upload) ? $translation->parse_uploader('posting/attachments/simple.html') : '',

	'S_IS_MODERATOR'			=> (titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')) ? true : false,
	'S_POST_ACTION'				=> titania::$contrib->get_url('revision_edit', array('revision' => $revision_id)),
	'S_FORM_ENCTYPE'			=> ' enctype="multipart/form-data"',
	'S_CUSTOM_LICENSE'			=> (!in_array($revision->revision_license, titania_types::$types[titania::$contrib->contrib_type]->license_options)) ? true : false,
	'S_ALLOW_CUSTOM_LICENSE'	=> (titania_types::$types[titania::$contrib->contrib_type]->license_allow_custom) ? true : false,
));

add_form_key('postform');

titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['EDIT_REVISION']);
titania::page_footer(true, 'contributions/contribution_revision_edit.html');