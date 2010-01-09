<?php
/**
*
* mods [English]
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
	'AUTHOR_BY'					=> 'By %s',

	'BAD_RATING'				=> 'Rating attempt failed.',

	'CACHE_PURGED'				=> 'Cache has been successfully purged',
	'CATEGORY'					=> 'Category',
	'CAT_ADDONS'				=> 'Add-ons',
	'CAT_ADMIN_TOOLS'			=> 'Admin Tools',
	'CAT_ANTI_SPAM'				=> 'Anti-Spam',
	'CAT_COMMUNICATION'			=> 'Communication',
	'CAT_COSMETIC'				=> 'Cosmetic',
	'CAT_ENTERTAINMENT'			=> 'Entertainment',
	'CAT_LANGUAGE_PACKS'		=> 'Language Packs',
	'CAT_MODIFICATIONS'			=> 'Modifications',
	'CAT_PROFILE_UCP'			=> 'Profile/User Control Panel',
	'CAT_SECURITY'				=> 'Security',
	'CAT_SNIPPETS'				=> 'Snippets',
	'CAT_STYLES'				=> 'Styles',
	'CONFIRM_PURGE_CACHE'		=> 'Are you sure you want to purge the cache?',
	'CONTRIBUTION'				=> 'Contribution',
	'CONTRIBUTIONS'				=> 'Contributions',
	'CONTRIB_MANAGE'			=> 'Manage Contribution',
	'CREATE_CONTRIBUTION'		=> 'Create Contribution',
	'CUSTOMISATION_DATABASE'	=> 'Customisation Database',

	'DELETED_MESSAGE'			=> 'Last deleted by %1$s on %2$s - <a href="%3$s">Click here to undelete this message</a>',
	'DESCRIPTION'				=> 'Description',
	'DETAILS'					=> 'Details',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOAD_ACCESS_DENIED'	=> 'You are not allowed to download the requested file.',
	'DOWNLOAD_NOT_FOUND'		=> 'The requested file could not be found.',

	'EDIT'						=> 'Edit',
	'EDITED_MESSAGE'			=> 'Last edited by %1$s on %2$s',

	'LANGUAGE_PACK'				=> 'Language Pack',
	'LIST'						=> 'List',

	'MANAGE'					=> 'Manage',
	'MODIFICATION'				=> 'Modification',
	'MOVE_DOWN'					=> 'Move down',
	'MOVE_UP'					=> 'Move up',
	'MULTI_SELECT_EXPLAIN'		=> 'Hold down CTRL and click to select multiple.',
	'MY_CONTRIBUTIONS'			=> 'My Contributions',

	'NO_AUTH'					=> 'You are not authorized to see this page.',
	'NO_CONTRIBS'				=> 'No contributions could be found',
	'NO_DESC'					=> 'You have to enter the description.',
	'NO_POST'					=> 'The requested post does not exist.',
	'NO_TOPIC'					=> 'The requested topic does not exist.',

	'ORDER'						=> 'Order',

	'PURGE_CACHE'				=> 'Purge Cache',

	'RATING'					=> 'Rating',
	'REMOVE_RATING'				=> 'Remove Rating',
	'RETURN_LAST_PAGE'			=> 'Return to the previous page',

	'SELECT_CATEGORY'			=> '-- Select category --',
	'SELECT_SORT_METHOD'		=> 'Sort By',
	'SITE_INDEX'				=> 'Site Index',
	'SNIPPET'					=> 'Snippet',
	'SORT_CONTRIB_NAME'			=> 'Contribution Name',
	'STYLE'						=> 'Style',

	'TITANIA_INDEX'				=> 'Customisation Database',
));
