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
	'REVISION'					=> 'Revision',
	'REVISIONS'					=> 'Revisions',

	'CANNOT_ADD_SELF_COAUTHOR'			=> 'You are the main author, you can not add yourself to the list of co-authors.',
	'CONTRIB'							=> 'Contribution',
	'CONTRIBUTIONS'						=> 'Contributions',
	'CONTRIB_ACTIVE_AUTHORS'			=> 'Active Co-Authors',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'	=> 'Active co-authors can manage most parts of the modification (need to get more details here after we finish more).',
	'CONTRIB_AUTHOR'					=> 'Contribution Author',
	'CONTRIB_AUTHORS_EXPLAIN'			=> 'Enter in the co-author names, one co-author username per line.',
	'CONTRIB_CATEGORY'					=> 'Contribution Category',
	'CONTRIB_CREATED'					=> 'The contribution has been created successfully',
	'CONTRIB_DESCRIPTION'				=> 'Contribution Description',
	'CONTRIB_DETAILS'					=> 'Contribution Details',
	'CONTRIB_EDITED'					=> 'The contribution has been successfully edited.',
	'CONTRIB_FAQ'						=> 'FAQ',
	'CONTRIB_NAME'						=> 'Contribution',
	'CONTRIB_NAME_EXISTS'				=> 'The unique name has already been reserved.',
	'CONTRIB_NONACTIVE_AUTHORS'			=> 'Non-Active Co-Authors (Past Contributors)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'	=> 'Nonactive co-authors can not manage anything for the modification and are only listed as previous authors.',
	'CONTRIB_NOT_FOUND'					=> 'The contribution you requested could not be found.',
	'CONTRIB_PERMALINK'					=> 'Contribution Permalink',
	'CONTRIB_RELEASE_DATE'				=> 'Release date',
	'CONTRIB_SUPPORT'					=> 'Discussion/Support',
	'CONTRIB_TYPE'						=> 'Contribution Type',
	'CONTRIB_UPDATE_DATE'				=> 'Last updated',
	'COULD_NOT_FIND_USERS'				=> 'Could not find the following users: %s',

	'DOWNLOADS'							=> 'Downloads',
	'DOWNLOADS_PER_DAY'					=> '%.2f Downloads per Day',
	'DOWNLOADS_TOTAL'					=> 'Total Downloads',
	'DOWNLOADS_VERSION'					=> 'Version Downloads',
	'DOWNLOAD_CHECKSUM'					=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'					=> 'You have the following authors listed as both active and non-active (they can not be both): %s',

	'EMPTY_CATEGORY'					=> 'Select one category at least',
	'EMPTY_CONTRIB_DESC'				=> 'Enter the contrib description',
	'EMPTY_CONTRIB_NAME'				=> 'Enter the contrib name',
	'EMPTY_CONTRIB_PERMALINK'			=> 'Enter your proposal for permalink for the contrib',
	'EMPTY_CONTRIB_TYPE'				=> 'Select one contrib type at least',
	'ERROR_CONTRIB_EMAIL_FRIEND'		=> 'You are not permitted to recommend this contribution to someone else.',

	'INVALID_PERMALINK'					=> 'You need to enter a valid permalink, for example: %s',

	'LOGIN_EXPLAIN_CONTRIB'				=> 'In order to create a new contribution you need to be registered',

	'MANAGE_CONTRIBUTION'				=> 'Manage Contribution',

	'NEW_CONTRIBUTION'					=> 'New Contribution',
	'NEW_TOPIC'							=> 'New Topic',
	'NO_CATEGORY'						=> 'The selected category does not exist',

	'SELECT_CONTRIB_TYPE'				=> '-- Select contribution type --',

	'WRONG_CATEGORY'					=> 'You can only put this contribution in the same category type as the contribution type.',

	'CONTRIB_CHANGE_OWNER'				=> 'Change Owner',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'		=> 'Input a username here to set this user as the owner. By changing this, you will be set as a Non-contributing Author.',
	'CONTRIB_CONFIRM_OWNER_CHANGE'		=> 'Are you sure you want to assign ownership to %s? This will prevent you from managing the project and cannot be undone.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'	=> 'The user you attempted to set as owner, %s, was not found.',
	'CONTRIB_OWNER_UPDATED'				=> 'The owner has been changed.',
));

?>