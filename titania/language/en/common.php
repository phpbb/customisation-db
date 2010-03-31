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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACCESS_LIMIT_AUTHORS'		=> 'Author-level access limit',
	'ACCESS_LIMIT_TEAMS'		=> 'Team-level access limit',
	'ADD_CATEGORY'				=> 'Add Category',
	'ALL_CONTRIBUTIONS'			=> 'All contributions',
	'AUTHOR_BY'					=> 'By %s',

	'BAD_RATING'				=> 'Rating attempt failed.',
	'BY'						=> 'by',

	'CACHE_PURGED'				=> 'Cache has been successfully purged',
	'CATEGORY'					=> 'Category',
	'CATEGORY_DELETED'			=> 'Category Deleted',
	'CATEGORY_NAME'				=> 'Category Name',
	'CAT_ADDONS'				=> 'Add-ons',
	'CAT_ANTI_SPAM'				=> 'Anti-Spam',
	'CAT_AVATARS'				=> 'Avatars',
	'CAT_BOARD_STYLES'			=> 'Board Styles',
	'CAT_COMMUNICATION'			=> 'Communication',
	'CAT_COSMETIC'				=> 'Cosmetic',
	'CAT_ENTERTAINMENT'			=> 'Entertainment',
	'CAT_LANGUAGE_PACKS'		=> 'Language Packs',
	'CAT_MISC'					=> 'Miscellaneous',
	'CAT_MODIFICATIONS'			=> 'Modifications',
	'CAT_PROFILE_UCP'			=> 'Profile/User Control Panel',
	'CAT_RANKS'					=> 'Ranks',
	'CAT_SECURITY'				=> 'Security',
	'CAT_SMILIES'				=> 'Smilies',
	'CAT_SNIPPETS'				=> 'Snippets',
	'CAT_STYLES'				=> 'Styles',
	'CAT_TOOLS'					=> 'Tools',
	'CLOSED_BY'					=> 'Closed by',
	'CLOSED_ITEMS'				=> 'Closed Items',
	'CONFIRM_PURGE_CACHE'		=> 'Are you sure you want to purge the cache?',
	'CONTINUE'					=> 'Continue',
	'CONTRIBUTION'				=> 'Contribution',
	'CONTRIBUTIONS'				=> 'Contributions',
	'CONTRIB_MANAGE'			=> 'Manage Contribution',
	'CREATE_CONTRIBUTION'		=> 'Create Contribution',
	'CUSTOMISATION_DATABASE'	=> 'Customisation Database',

	'DATE_CLOSED'				=> 'Date closed',
	'DELETED_MESSAGE'			=> 'Last deleted by %1$s on %2$s - <a href="%3$s">Click here to undelete this message</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Delete all Contributions',
	'DELETE_CATEGORY'			=> 'Delete Category',
	'DELETE_SUBCATS'			=> 'Delete Subcategories',
	'DESCRIPTION'				=> 'Description',
	'DETAILS'					=> 'Details',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOADS'					=> 'Downloads',
	'DOWNLOAD_ACCESS_DENIED'	=> 'You are not allowed to download the requested file.',
	'DOWNLOAD_NOT_FOUND'		=> 'The requested file could not be found.',

	'EDIT'						=> 'Edit',
	'EDITED_MESSAGE'			=> 'Last edited by %1$s on %2$s',
	'EDIT_CATEGORY'				=> 'Edit Category',

	'FILE_NOT_EXIST'			=> 'File does not exist: %s',

	'LANGUAGE_PACK'				=> 'Language Pack',
	'LIST'						=> 'List',

	'MANAGE'					=> 'Manage',
	'MODIFICATION'				=> 'Modification',
	'MOVE_CONTRIBS_TO'			=> 'Move Contributions to',
	'MOVE_DOWN'					=> 'Move down',
	'MOVE_SUBCATS_TO'			=> 'Move Subcategories to',
	'MOVE_UP'					=> 'Move up',
	'MULTI_SELECT_EXPLAIN'		=> 'Hold down CTRL and click to select multiple items.',
	'MY_CONTRIBUTIONS'			=> 'My Contributions',

	'NEW_REVISION'				=> 'New Revision',
	'NO_AUTH'					=> 'You are not authorized to see this page.',
	'NO_CATEGORY'				=> 'The requested category does not exist.',
	'NO_CONTRIB'				=> 'The requested contribution does not exist.',
	'NO_CONTRIBS'				=> 'No contributions could be found',
	'NO_DESC'					=> 'You have to enter the description.',
	'NO_DESTINATION_CATEGORY'	=> 'No destination category could be found.',
	'NO_POST'					=> 'The requested post does not exist.',
	'NO_REVISION_NAME'			=> 'No revision name provided',
	'NO_TOPIC'					=> 'The requested topic does not exist.',

	'ORDER'						=> 'Order',

	'POST_IP'					=> 'Post IP',
	'PURGE_CACHE'				=> 'Purge Cache',

	'QUEUE'						=> 'Queue',
	'QUEUE_DISCUSSION'			=> 'Queue Discussion',
	'QUICK_ACTIONS'				=> 'Quick Actions',

	'RATING'					=> 'Rating',
	'REMOVE_RATING'				=> 'Remove Rating',
	'RETURN_LAST_PAGE'			=> 'Return to the previous page',

	'SELECT_CATEGORY'			=> '-- Select category --',
	'SELECT_SORT_METHOD'		=> 'Sort By',
	'SITE_INDEX'				=> 'Site Index',
	'SNIPPET'					=> 'Snippet',
	'SORT_CONTRIB_NAME'			=> 'Contribution Name',
	'STICKIES'					=> 'Stickies',
	'STYLE'						=> 'Style',
	'SUBSCRIBE'					=> 'Subscribe',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Subscription Notification',

	'TITANIA_INDEX'				=> 'Customisation Database',
	'TOTAL_CONTRIBS'			=> '%d Contributions',
	'TOTAL_CONTRIBS_ONE'		=> '1 Contribution',
	'TOTAL_POSTS'				=> '%d Posts',
	'TOTAL_POSTS_ONE'			=> '1 Post',
	'TOTAL_RESULTS'				=> '%d Results',
	'TOTAL_RESULTS_ONE'			=> '1 Result',
	'TOTAL_TOPICS'				=> '%d Topics',
	'TOTAL_TOPICS_ONE'			=> '1 Topic',
	'TYPE'						=> 'Type',

	'UNKNOWN'					=> 'Unknown',
	'UNSUBSCRIBE'				=> 'Unsubscribe',
	'UPDATE_TIME'				=> 'Updated',

	'VERSION'					=> 'Version',
	'VIEW'						=> 'View',
));
