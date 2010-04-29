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

load_contrib();

// Editing a revision can only be done by moderators
if (!(phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate')))
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
$revision->load_phpbb_versions();
foreach ($revision->phpbb_versions as $row)
{
	$revision_phpbb_versions[] = $phpbb_versions[$row['phpbb_version_branch'] . $row['phpbb_version_revision']];
}

// Submit the revision
if (isset($_POST['submit']))
{
	if (!check_form_key('postform'))
	{
		$error[] = phpbb::$user->lang['FORM_INVALID'];
	}

	// Grab the phpbb versions and do some simple error checking
	$revision_phpbb_versions = request_var('revision_phpbb_versions', array(''));
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

	// If no errors, submit
	if (!sizeof($error))
	{
		$revision->phpbb_versions = array();
		$revision->__set_array(array(
			// @todo option to change the revision_validated later on (need to check more stuff on the contribution if it's changed too)
			//'revision_validated'		=> ((isset($_POST['validated'])) ? true : false) : $revision->revision_validated,
			'revision_name'			=> utf8_normalize_nfc(request_var('revision_name', $revision->revision_name, true)),
		));

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

		$revision->submit();

		redirect(titania::$contrib->get_url());
	}
}

// Display the list of phpBB versions available
foreach ($phpbb_versions as $version => $name)
{
	$template->assign_block_vars('phpbb_versions', array(
		'VERSION'		=> $name,
		'S_SELECTED'	=> (in_array($name, $revision_phpbb_versions)) ? true : false,
	));
}

// Display the rest of the page
phpbb::$template->assign_vars(array(
	'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
	'REVISION_NAME'		=> $revision->revision_name,

	'S_POST_ACTION'		=> titania::$contrib->get_url('revision_edit', array('revision' => $revision_id)),
));

add_form_key('postform');

titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['EDIT_REVISION']);
titania::page_footer(true, 'contributions/contribution_revision_edit.html');