<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
'CUSTOM_LICENSE' => 'Custom',
	'ANNOUNCEMENT_TOPIC'					=> 'Announcement topic',
	'ANNOUNCEMENT_TOPIC_SUPPORT'			=> 'Support topic',
	'ANNOUNCEMENT_TOPIC_VIEW'				=> '%sView%s',
	'ATTENTION_CONTRIB_CATEGORIES_CHANGED'	=> '<strong>Contribution categories changed from:</strong><br />%1$s<br /><br /><strong>to:</strong><br />%2$s',
	'ATTENTION_CONTRIB_DESC_CHANGED'		=> '<strong>Contribution description changed from:</strong><br />%1$s<br /><br /><strong>to:</strong><br />%2$s',
	'AUTOMOD_RESULTS'						=> '<strong>Please check over the AutoMod install results and make sure that nothing needs to be fixed.<br /><br />If an error comes up and you are certain that the error is incorrect, just hit continue below.</strong>',
	'AUTOMOD_TEST'							=> 'The Mod will be tested against AutoMod and results will be shown (this may take a few moments, so please be patient).<br /><br />Please hit continue when you are ready.',

	'BAD_VERSION_SELECTED'					=> '%s is not a proper phpBB version.',

	'CANNOT_ADD_SELF_COAUTHOR'				=> 'You are the main author, you can not add yourself to the list of co-authors.',
	'CLEANED_CONTRIB'						=> 'Cleaned contribution',
	'CONTRIB'								=> 'Contribution',
	'CONTRIBUTIONS'							=> 'Contributions',
	'CONTRIB_ACTIVE_AUTHORS'				=> 'Active Co-Authors',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'		=> 'Active Co-Authors can manage most parts of the contribution.',
	'CONTRIB_APPROVED'						=> 'Approved',
	'CONTRIB_AUTHOR'						=> 'Contribution Author',
	'CONTRIB_AUTHORS_EXPLAIN'				=> 'Enter in the Co-Author names, one Co-Author username per line.',
	'CONTRIB_CATEGORY'						=> 'Contribution Category',
	'CONTRIB_CHANGE_OWNER'					=> 'Change Owner',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'			=> 'Input a username here to set this user as the owner. By changing this, you will be set as a Non-contributing Author.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'		=> 'The user you attempted to set as owner, %s, was not found.',
	'CONTRIB_CLEANED'						=> 'Cleaned',
	'CONTRIB_CONFIRM_OWNER_CHANGE'			=> 'Are you sure you want to assign ownership to %s? This will prevent you from managing the project and can not be undone.',
	'CONTRIB_CREATED'						=> 'The contribution has been created successfully',
	'CONTRIB_DESCRIPTION'					=> 'Contribution Description',
	'CONTRIB_DETAILS'						=> 'Contribution Details',
	'CONTRIB_DISABLED'						=> 'Hidden + Disabled',
	'CONTRIB_DOWNLOAD_DISABLED'				=> 'Downloads Disabled',
	'CONTRIB_EDITED'						=> 'The contribution has been successfully edited.',
	'CONTRIB_HIDDEN'						=> 'Hidden',
	'CONTRIB_ISO_CODE'						=> 'ISO Code',
	'CONTRIB_ISO_CODE_EXPLAIN'				=> 'The ISO code according to the <a href="http://area51.phpbb.com/docs/coding-guidelines.html#translation">Translation Coding Guidelines</a>.',
	'CONTRIB_LOCAL_NAME'					=> 'Local name',
	'CONTRIB_LOCAL_NAME_EXPLAIN'			=> 'The localized name of the language, e.g. <em>Français</em>.',
	'CONTRIB_NAME'							=> 'Contribution Name',
	'CONTRIB_NAME_EXISTS'					=> 'The unique name has already been reserved.',
	'CONTRIB_NEW'							=> 'New',
	'CONTRIB_NONACTIVE_AUTHORS'				=> 'Non-Active Co-Authors (Past Contributors)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'		=> 'Non-Active Co-Authors can not manage anything for the contribution and are only listed as previous authors.',
	'CONTRIB_NOT_FOUND'						=> 'The contribution you requested could not be found.',
	'CONTRIB_OWNER_UPDATED'					=> 'The owner has been changed.',
	'CONTRIB_PERMALINK'						=> 'Contribution Permalink',
	'CONTRIB_PERMALINK_EXPLAIN'				=> 'Cleaned version of the contribution name, used to build the url for the contribution.<br /><strong>Leave blank to have one automatically created based on the contribution name.</strong>',
	'CONTRIB_RELEASE_DATE'					=> 'Release date',
	'CONTRIB_STATUS'						=> 'Contribution status',
	'CONTRIB_STATUS_EXPLAIN'				=> 'Change the contribution status',
	'CONTRIB_TYPE'							=> 'Contribution Type',
	'CONTRIB_UPDATED'						=> 'The contribution has been successfully updated.',
	'CONTRIB_UPDATE_DATE'					=> 'Last updated',
	'COULD_NOT_FIND_ROOT'					=> 'Could not find the main directory.  Please ensure there is an xml file with the name install in it somewhere in the zip package.',
	'COULD_NOT_FIND_USERS'					=> 'Could not find the following users: %s',
	'COULD_NOT_OPEN_MODX'					=> 'Could not open ModX file.',
	'CO_AUTHORS'							=> 'Co-Authors',

	'DELETE_CONTRIBUTION'					=> 'Delete Contribution',
	'DELETE_CONTRIBUTION_EXPLAIN'			=> 'Permanently remove this contribution (use the contribution status field if you need to hide it).',
	'DELETE_REVISION'						=> 'Delete Revision',
	'DELETE_REVISION_EXPLAIN'				=> 'Permanently remove this revision (use the revision status field if you need to hide it).',
	'DEMO_URL'								=> 'Demo URL',
	'DEMO_URL_EXPLAIN'						=> 'Location of the demonstration',
	'DOWNLOADS_PER_DAY'						=> '%.2f Downloads per Day',
	'DOWNLOADS_TOTAL'						=> 'Total Downloads',
	'DOWNLOADS_VERSION'						=> 'Version Downloads',
	'DOWNLOAD_CHECKSUM'						=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'						=> 'You have the following authors listed as both active and non-active (they can not be both): %s',

	'EDIT_REVISION'							=> 'Edit Revision',
	'EMPTY_CATEGORY'						=> 'Select one category at least',
	'EMPTY_CONTRIB_DESC'					=> 'Enter the contrib description',
	'EMPTY_CONTRIB_ISO_CODE'				=> 'Enter the ISO Code',
	'EMPTY_CONTRIB_LOCAL_NAME'				=> 'Enter the local name',
	'EMPTY_CONTRIB_NAME'					=> 'Enter the contrib name',
	'EMPTY_CONTRIB_PERMALINK'				=> 'Enter your proposal for permalink for the contribution',
	'EMPTY_CONTRIB_TYPE'					=> 'Select at least one contribution type',
	'ERROR_CONTRIB_EMAIL_FRIEND'			=> 'You are not permitted to recommend this contribution to someone else.',

	'INSTALL_LESS_THAN_1_MINUTE'			=> 'Less Than One Minute',
	'INSTALL_LEVEL'							=> 'Install Level',
	'INSTALL_LEVEL_1'						=> 'Easy',
	'INSTALL_LEVEL_2'						=> 'Intermediate',
	'INSTALL_LEVEL_3'						=> 'Advanced',
	'INSTALL_MINUTES'						=> 'About %s Minute(s)',
	'INSTALL_TIME'							=> 'Install Time',
	'INVALID_LICENSE'						=> 'Invalid License',
	'INVALID_PERMALINK'						=> 'You need to enter a valid permalink, for example: %s',

	'LICENSE'								=> 'License',
	'LICENSE_EXPLAIN'						=> 'License to release this work under.',
	'LICENSE_FILE_MISSING'					=> 'The package must contain a license.txt file containing the license terms either in the main directory or within one subdirectory level of the main directory.',
	'LOGIN_EXPLAIN_CONTRIB'					=> 'In order to create a new contribution you need to be registered',

	'MANAGE_CONTRIBUTION'					=> 'Manage Contribution',
	'MPV_RESULTS'							=> '<strong>Please check over the MPV results and make sure that nothing needs to be fixed.<br /><br />If you do not think anything requires fixing or you are not sure, just hit continue below.</strong>',
	'MPV_TEST'								=> 'The Mod will be tested against MPV and results will be shown (this may take a few moments, so please be patient).<br /><br />Please hit continue when you are ready.',
	'MPV_TEST_FAILED'						=> 'Sorry, the automatic MPV test failed and your MPV test results are not available.  Please continue.',
	'MPV_TEST_FAILED_QUEUE_MSG'				=> 'Automated MPV test failed.  [url=%s]Click here to attempt running MPV automatically again[/url]',
	'MUST_SELECT_ONE_VERSION'				=> 'You must select at least one phpBB version.',

	'NEW_CONTRIBUTION'						=> 'New Contribution',
	'NEW_REVISION'							=> 'New Revision',
	'NEW_REVISION_SUBMITTED'				=> 'New revision has been submitted successfully!',
	'NEW_TOPIC'								=> 'New Topic',
	'NOT_VALIDATED'							=> 'Not Validated',
	'NO_CATEGORY'							=> 'The selected category does not exist',
	'NO_PHPBB_BRANCH'						=> 'You must select a phpBB branch.',
	'NO_QUEUE_DISCUSSION_TOPIC'				=> 'No Queue Discussion topic could be found.  Have you submitted any revision for this contribution yet (it will be created when you do so)?',
	'NO_REVISIONS'							=> 'No Revisions',
	'NO_REVISION_ATTACHMENT'				=> 'Please select a file to upload',
	'NO_REVISION_VERSION'					=> 'Please enter a version for the revision',
	'NO_SCREENSHOT'							=> 'No screenshot',
	'NO_TRANSLATION'						=> 'The archive does not appear to be a valid language package. Please ensure it contains all the files found in the English language directory',

	'PHPBB_BRANCH'							=> 'phpBB Branch',
	'PHPBB_BRANCH_EXPLAIN'					=> 'Select the phpBB branch that this revision supports.',
	'PHPBB_VERSION'							=> 'phpBB Version(s)',

	'QUEUE_ALLOW_REPACK'					=> 'Allow Repacking',
	'QUEUE_ALLOW_REPACK_EXPLAIN'			=> 'Allow this contribution to be repacked for small errors?',
	'QUEUE_NOTES'							=> 'Validation Notes',
	'QUEUE_NOTES_EXPLAIN'					=> 'Message to the team.',

	'REPORT_CONTRIBUTION'					=> 'Report Contribution',
	'REPORT_CONTRIBUTION_CONFIRM'			=> 'Use this form to report the selected contribution to the moderators and administrators. Reporting should generally be used only if the contribution breaks forum rules.',
	'REVISION'								=> 'Revision',
	'REVISIONS'								=> 'Revisions',
	'REVISION_APPROVED'						=> 'Approved',
	'REVISION_DENIED'						=> 'Denied',
	'REVISION_IN_QUEUE'						=> 'You already have a revision in the validation queue.  You must wait until the previous revision is approved or denied to submit a new one.',
	'REVISION_NAME'							=> 'Revision Name',
	'REVISION_NAME_EXPLAIN'					=> 'Enter in an optional name for this version (ex: Furry Edition)',
	'REVISION_NEW'							=> 'New',
	'REVISION_PENDING'						=> 'Pending',
	'REVISION_PULLED_FOR_OTHER'				=> 'Pulled',
	'REVISION_PULLED_FOR_SECURITY'			=> 'Pulled - Security',
	'REVISION_REPACKED'						=> 'Repacked',
	'REVISION_RESUBMITTED'					=> 'Resubmitted',
	'REVISION_STATUS'						=> 'Revision Status',
	'REVISION_STATUS_EXPLAIN'				=> 'Change the revision status',
	'REVISION_SUBMITTED'					=> 'The revision has been submitted successfully.',
	'REVISION_VERSION'						=> 'Revision Version',
	'REVISION_VERSION_EXPLAIN'				=> 'The version number of this package',

	'SCREENSHOTS'							=> 'Screenshots',
	'SELECT_CONTRIB_TYPE'					=> '-- Select contribution type --',
	'SELECT_PHPBB_BRANCH'					=> 'Select phpBB branch',
	'SUBDIRECTORY_LIMIT'					=> 'Packages are not allowed to be more than 50 subdirectories deep at any point.',
	'SUBMIT_NEW_REVISION'					=> 'Submit and add new revision',

	'TOO_MANY_TRANSLATOR_LINKS'				=> 'You are currently using %d external links within the TRANSLATION/TRANSLATION_INFO line. Please only include <strong>one link</strong>. Including two links is allowed on a case-by-case basis - please post within the translations forum noting your reasoning behind putting more external links within the line.',

	'VALIDATION_TIME'						=> 'Validation time',
	'VIEW_DEMO'								=> 'View Demo',
	'VIEW_INSTALL_FILE'						=> 'View install file',

	'WRONG_CATEGORY'						=> 'You can only put this contribution in the same category type as the contribution type.',
));
