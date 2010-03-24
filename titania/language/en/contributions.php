<?php
/**
*
* contribution [English]
*
* @package Titania
* @version $Id$
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
	'ANNOUNCEMENT_TOPIC' 				=> 'Announcement topic',
	'ANNOUNCEMENT_TOPIC_SUPPORT'		=> 'Support topic',
	'ANNOUNCEMENT_TOPIC_VIEW'			=> '%sView%s',
	
	'NO_REVISIONS' => 'No Revisions',
	'AUTOMOD_RESULTS'					=> '<strong>Please check over the AutoMod install results and make sure that nothing needs to be fixed.<br /><br />If an error comes up and you are certain that the error is incorrect, just hit continue below.</strong>',
	'AUTOMOD_TEST'						=> 'The Mod will be tested against AutoMod and results will be shown (this may take a few moments, so please be patient).<br /><br />Please hit continue when you are ready.',

	'CANNOT_ADD_SELF_COAUTHOR'			=> 'You are the main author, you can not add yourself to the list of co-authors.',
	'CLEANED_CONTRIB'					=> 'Cleaned contribution',
	'CONTRIB'							=> 'Contribution',
	'CONTRIBUTIONS'						=> 'Contributions',
	'CONTRIB_ACTIVE_AUTHORS'			=> 'Active Co-Authors',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'	=> 'Active Co-Authors can manage most parts of the contribution (need to get more details here after we finish more).',
	'CONTRIB_AUTHOR'					=> 'Contribution Author',
	'CONTRIB_AUTHORS_EXPLAIN'			=> 'Enter in the Co-Author names, one Co-Author username per line.',
	'CONTRIB_CATEGORY'					=> 'Contribution Category',
	'CONTRIB_CHANGE_OWNER'				=> 'Change Owner',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'		=> 'Input a username here to set this user as the owner. By changing this, you will be set as a Non-contributing Author.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'	=> 'The user you attempted to set as owner, %s, was not found.',
	'CONTRIB_CONFIRM_OWNER_CHANGE'		=> 'Are you sure you want to assign ownership to %s? This will prevent you from managing the project and can not be undone.',
	'CONTRIB_CREATED'					=> 'The contribution has been created successfully',
	'CONTRIB_DESCRIPTION'				=> 'Contribution Description',
	'CONTRIB_DETAILS'					=> 'Contribution Details',
	'CONTRIB_EDITED'					=> 'The contribution has been successfully edited.',
	'CONTRIB_FAQ'						=> 'FAQ',
	'CONTRIB_NAME'						=> 'Contribution Name',
	'CONTRIB_NAME_EXISTS'				=> 'The unique name has already been reserved.',
	'CONTRIB_NONACTIVE_AUTHORS'			=> 'Non-Active Co-Authors (Past Contributors)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'	=> 'Non-Active Co-Authors can not manage anything for the contribution and are only listed as previous authors.',
	'CONTRIB_NOT_FOUND'					=> 'The contribution you requested could not be found.',
	'CONTRIB_OWNER_UPDATED'				=> 'The owner has been changed.',
	'CONTRIB_PERMALINK'					=> 'Contribution Permalink',
	'CONTRIB_PERMALINK_EXPLAIN'			=> 'Cleaned version of the contribution name, used to build the url for the contribution.<br /><strong>Leave blank to have one automatically created based on the contribution name.</strong>',
	'CONTRIB_RELEASE_DATE'				=> 'Release date',
	'CONTRIB_SUPPORT'					=> 'Discussion/Support',
	'CONTRIB_TYPE'						=> 'Contribution Type',
	'CONTRIB_UPDATED'					=> 'The contribution has been successfully updated.',
	'CONTRIB_UPDATE_DATE'				=> 'Last updated',
	'COULD_NOT_FIND_ROOT'				=> 'Could not find the main directory.  Please ensure there is an xml file with the name install in it somewhere in the zip package.',
	'COULD_NOT_FIND_USERS'				=> 'Could not find the following users: %s',
	'CO_AUTHORS'						=> 'Co-Authors',

	'DOWNLOADS_PER_DAY'					=> '%.2f Downloads per Day',
	'DOWNLOADS_TOTAL'					=> 'Total Downloads',
	'DOWNLOADS_VERSION'					=> 'Version Downloads',
	'DOWNLOAD_CHECKSUM'					=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'					=> 'You have the following authors listed as both active and non-active (they can not be both): %s',

	'EMPTY_CATEGORY'					=> 'Select one category at least',
	'EMPTY_CONTRIB_DESC'				=> 'Enter the contrib description',
	'EMPTY_CONTRIB_NAME'				=> 'Enter the contrib name',
	'EMPTY_CONTRIB_PERMALINK'			=> 'Enter your proposal for permalink for the contribution',
	'EMPTY_CONTRIB_TYPE'				=> 'Select at least one contribution type',
	'ERROR_CONTRIB_EMAIL_FRIEND'		=> 'You are not permitted to recommend this contribution to someone else.',

	'INVALID_PERMALINK'					=> 'You need to enter a valid permalink, for example: %s',

	'LOGIN_EXPLAIN_CONTRIB'				=> 'In order to create a new contribution you need to be registered',

	'MANAGE_CONTRIBUTION'				=> 'Manage Contribution',
	'MOD_CREATE_PUBLIC'					=> '[b]Modification name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Modification description[/b]: %4$s
[b]Modification version[/b]: %5$s
[b]Tested on phpBB version[/b]: See below

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s Bytes

[b]Modification overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]The phpBB Team is not responsible or required to give anyone support for this modification. By installing this MOD, you acknowledge that the phpBB Support Team or phpBB MODifications Team may not be able to provide support.[/b][/color]',
	'MOD_UPDATE_PUBLIC'					=> '[color=darkred][b]MOD Updated to version %s
See first post for Download Link[/b][/color]',
	'MPV_RESULTS'						=> '<strong>Please check over the MPV results and make sure that nothing needs to be fixed.<br /><br />If you do not think anything requires fixing or you are not sure, just hit continue below.</strong>',
	'MPV_TEST'							=> 'The Mod will be tested against MPV and results will be shown (this may take a few moments, so please be patient).<br /><br />Please hit continue when you are ready.',
	'MPV_TEST_FAILED'					=> 'Sorry, the automatic MPV test failed and your MPV test results are not available.  Please continue.',
	'MPV_TEST_FAILED_QUEUE_MSG'			=> 'Automated MPV test failed.  [url=%s]Click here to attempt running MPV automatically again[/url]',

	'NEW_CONTRIBUTION'					=> 'New Contribution',
	'NEW_REVISION'						=> 'New Revision',
	'NEW_REVISION_SUBMITTED'			=> 'New revision has been submitted successfully!',
	'NEW_TOPIC'							=> 'New Topic',
	'NOT_VALIDATED'						=> 'Not Validated',
	'NO_CATEGORY'						=> 'The selected category does not exist',
	'NO_PHPBB_BRANCH'					=> 'You must select a phpBB branch.',
	'NO_QUEUE_DISCUSSION_TOPIC'			=> 'No Queue Discussion topic could be found.  Have you submitted any revision for this contribution yet (it will be created when you do so)?',
	'NO_REVISION_ATTACHMENT'			=> 'Please select a file to upload',
	'NO_REVISION_VERSION'				=> 'Please enter a version for the revision',
	'NO_SCREENSHOT'						=> 'No screenshot',

	'PHPBB_BRANCH'						=> 'phpBB Branch',
	'PHPBB_BRANCH_EXPLAIN'				=> 'Select the phpBB branch that this revision supports.',
	'PHPBB_VERSION'						=> 'phpBB Version',

	'QUEUE_ALLOW_REPACK'				=> 'Allow Repacking',
	'QUEUE_ALLOW_REPACK_EXPLAIN'		=> 'Allow this contribution to be repacked for small errors?',
	'QUEUE_NOTES'						=> 'Validation Notes',
	'QUEUE_NOTES_EXPLAIN'				=> 'Message to the team.',

	'REVISION'							=> 'Revision',
	'REVISIONS'							=> 'Revisions',
	'REVISION_IN_QUEUE'					=> 'You already have a revision in the validation queue.  You must wait until the previous revision is approved or denied to submit a new one.',
	'REVISION_NAME'						=> 'Revision Name',
	'REVISION_SUBMITTED'				=> 'The revision has been submitted successfully.',
	'REVISION_VERSION'					=> 'Revision Version',

	'SCREENSHOTS'						=> 'Screenshots',
	'SELECT_CONTRIB_TYPE'				=> '-- Select contribution type --',
	'SELECT_PHPBB_BRANCH'				=> 'Select phpBB branch',
	'STYLE_CREATE_PUBLIC'				=> '[b]Style name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Style description[/b]: %4$s
[b]Style version[/b]: %5$s
[b]Tested on phpBB version[/b]: See below

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s Bytes

[b]Style overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]The phpBB Team is not responsible or required to give anyone support for this Style. By installing this MOD, you acknowledge that the phpBB Support Team or phpBB Styles Team may not be able to provide support.[/b][/color]',
	'STYLE_UPDATE_PUBLIC'				=> '[color=darkred][b]Style Updated to version %s
See first post for Download Link[/b][/color]',
	'SUBDIRECTORY_LIMIT'				=> 'Packages are not allowed to be more than 50 subdirectories deep at any point.',
	'SUBMIT_NEW_REVISION'				=> 'Submit and add new revision',

	'VIEW_DEMO'							=> 'View Demo',

	'WRONG_CATEGORY'					=> 'You can only put this contribution in the same category type as the contribution type.',
));
