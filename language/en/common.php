<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
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
	'AGREE'						=> 'I agree',
	'AGREEMENT'					=> 'Agreement',
	'ALL'						=> 'All',
	'ALL_BRANCHES'				=> 'All branches',
	'ALL_CONTRIBUTIONS'			=> 'All contributions',
	'ALL_SUPPORT'				=> 'All support topics',
	'APPROVED'					=> 'Approved',
	'APPROVED_BY'				=> 'Approved by',
	'AUTHOR_BY'					=> 'By %s',

	'BAD_RATING'				=> 'Rating attempt failed.',
	'BY'						=> 'by',

	'CACHE_PURGED'				=> 'Cache has been successfully purged',
	'CATEGORY'					=> 'Category',
	'CATEGORIES'				=> 'Categories',
	'CATEGORY_CHILD_AS_PARENT'	=> 'The chosen parent category cannot be selected because it is a child of this category.',
	'CATEGORY_DELETED'			=> 'Category Deleted',
	'CATEGORY_DESC'				=> 'Category Description',
	'CATEGORY_DUPLICATE_PARENT'	=> 'Category cannot be its own parent.',
	'CATEGORY_HAS_CHILDREN'		=> 'This category cannot be deleted because it contains children categories.',
	'CATEGORY_INFORMATION'		=> 'Category Information',
	'CATEGORY_NAME'				=> 'Category Name',
	'CATEGORY_OPTIONS'			=> 'Category Options',
	'CATEGORY_TYPE'				=> 'Category Type',
	'CATEGORY_TYPE_EXPLAIN'		=> 'The type of contributions this category will hold. Leave unset to not accept contributions.',
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
	'CLOSED'					=> 'Closed',
	'CLOSED_BY'					=> 'Closed by',
	'CLOSED_ITEMS'				=> 'Closed Items',
	'COLORIZEIT'				=> 'ColorizeIt',
	'COLORIZEIT_COLORS'         => 'Colour scheme',
	'COLORIZEIT_DOWNLOAD'       => 'Change colour scheme.',
	'COLORIZEIT_DOWNLOAD_STYLE' => 'Change colour scheme and download',
	'COLORIZEIT_MANAGE'         => 'ColorizeIt configuration',
	'COLORIZEIT_MANAGE_EXPLAIN' => 'In order to activate ColorizeIt for this style, you need to upload sample image and change default color scheme. Sample image must be in GIF format, should not be animated and size must be between 200x300 and 500x600 pixels. Sample should not be scaled, it should not include colours that are not available in style, it should not include anti aliased text. <a href="http://www.colorizeit.com/advanced.html?do=tutorial_sample">Follow this URL</a> to read a full tutorial.',
	'COLORIZEIT_SAMPLE'         => 'Show Colours Scheme Editor',
	'COLORIZEIT_SAMPLE_EXPLAIN' => 'Add colours to editor by picking them from a sample image, then copy colours scheme string from text field below editor to text field below this text and click "Submit" to save changes.',
	'CONFIRM_PURGE_CACHE'		=> 'Are you sure you want to purge the cache?',
	'CONTINUE'					=> 'Continue',
	'CONTRIBUTION'				=> 'Contribution',
	'CONTRIBUTIONS'				=> 'Contributions',
	'CONTRIB_FAQ'				=> 'FAQ',
	'CONTRIB_MANAGE'			=> 'Manage Contribution',
	'CONTRIB_SUPPORT'			=> 'Discussion/Support',
	'CREATE_CATEGORY'			=> 'Create Category',
	'CUSTOMISATION_DATABASE'	=> 'Customisation Database',

	'DATE_CLOSED'				=> 'Date closed',
	'DELETED_MESSAGE'			=> 'Last deleted by %1$s on %2$s - <a href="%3$s">Click here to undelete this message</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Delete all Contributions',
	'DELETE_CATEGORY'			=> 'Delete Category',
	'DELETE_SUBCATS'			=> 'Delete Subcategories',
	'DESCRIPTION'				=> 'Description',
	'DESTINATION_CAT_INVALID'	=> 'The destination category is not able to accept contributions.',
	'DETAILS'					=> 'Details',
	'DONE'						=> 'Done!',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOADS'					=> 'Downloads',
	'DOWNLOADS_ASC'				=> 'Least downloads',
	'DOWNLOADS_DESC'			=> 'Most downloads',
	'DOWNLOAD_ACCESS_DENIED'	=> 'You are not allowed to download the requested file.',
	'DOWNLOAD_NOT_FOUND'		=> 'The requested file could not be found.',

	'EDIT'						=> 'Edit',
	'EDITED_MESSAGE'			=> 'Last edited by %1$s on %2$s',
	'EDIT_CATEGORY'				=> 'Edit Category',
	'ERROR'						=> 'Error',

	'FORM_ERROR'				=> 'An error occurred while submitting the form.',
	'FILE_NOT_EXIST'			=> 'File does not exist: %s',
	'FIND_CONTRIBUTION'			=> 'Find Contribution',

	'HARD_DELETE'				=> 'Hard Delete',
	'HARD_DELETE_EXPLAIN'		=> 'Select to permanently delete this item.',
	'HARD_DELETE_TOPIC'			=> 'Hard Delete Topic',
	'HOUR'						=> 'Hour',

	'INVALID_ACTION'			=> 'Invalid action.',

	'LANGUAGE_PACK'				=> 'Language Pack',
	'LESS_THAN_A_MINUTE'		=> 'Less than a minute',
	'LIST'						=> 'List',

	'MAKE_CATEGORY_VISIBLE'		=> 'Make Category Visible',
	'MANAGE'					=> 'Manage',
	'MARK_CONTRIBS_READ'		=> 'Mark contributions read',
	'MINUTE'					=> 'Minute',
	'MOVE_CONTRIBS_TO'			=> 'Move Contributions to',
	'MOVE_DOWN'					=> 'Move down',
	'MOVE_SUBCATS_TO'			=> 'Move Subcategories to',
	'MOVE_UP'					=> 'Move up',
	'MULTI_SELECT_EXPLAIN'		=> 'Hold down CTRL and click to select multiple items.',
	'MY_CONTRIBUTIONS'			=> 'My Contributions',

	'NAME'						=> 'Name',
	'NEW_CONTRIBUTION'			=> 'New Contribution',
	'NEW_UNAPPROVED_POST'		=> 'New unapproved post',
	'NEW_REVISION'				=> 'New Revision',
	'NOT_AGREE'					=> 'I do not agree',
	'NO_AUTH'					=> 'You are not authorised to see this page.',
	'NO_CATEGORY'				=> 'The requested category does not exist.',
	'NO_CATEGORY_NAME'			=> 'Enter the category name',
	'NO_CATEGORY_URL'			=> 'Invalid category URL',
	'NO_CONTRIB'				=> 'The requested contribution does not exist.',
	'NO_CONTRIBS'				=> 'No contributions could be found',
	'NO_DESC'					=> 'You have to enter the description.',
	'NO_DESTINATION_CATEGORY'	=> 'No destination category could be found.',
	'NO_PAGE_FOUND'				=> 'The page you requested could not be found.',
	'NO_POST'					=> 'The requested post does not exist.',
	'NO_REVISION_NAME'			=> 'No revision name provided',
	'NO_TOPIC'					=> 'The requested topic does not exist.',
	'NUM_CONTRIBS'				=> array(
		1	=> '1 Contribution',
		2	=> '%d Contributions',
	),
	'NUM_POSTS'					=> array(
		1	=> '1 Post',
		2	=> '%d Posts',
	),
	'NUM_RESULTS'				=> array(
		1	=> '1 Result',
		2	=> '%d Results',
	),
	'NUM_TOPICS'				=> array(
		1	=> '1 Topic',
		2	=> '%d Topics',
	),

	'ORDER'						=> 'Order',

	'PAGE_REQUEST_INVALID'		=> 'The page request is invalid. Please try again.',
	'PARENT_CATEGORY'			=> 'Parent Category',
	'PARENT_CONTRIBUTION'		=> 'Parent Contribution',
	'PARENT_NOT_EXIST'			=> 'Parent does not exist.',
	'POST_IP'					=> 'Post IP',
	'PURGE_CACHE'				=> 'Purge Cache',

	'QUEUE'						=> 'Queue',
	'QUEUE_DISCUSSION'			=> 'Queue Discussion',
	'QUEUE_STATS'				=> 'Queue Statistics',
	'QUICK_ACTIONS'				=> 'Quick Actions',

	'RATING'					=> 'Rating',
	'RATING_ASC'				=> 'Worst rating',
	'RATING_DESC'				=> 'Best rating',
	'REMOVE_RATING'				=> 'Remove Rating',
	'REPORT'					=> 'Report',
	'RETURN_LAST_PAGE'			=> 'Return to the previous page',
	'ROOT'						=> 'Root',

	'SEARCH_UNAVAILABLE'		=> 'The search system is currently unavailable. Please try again in a few minutes.',
	'SELECT_CATEGORY'			=> '-- Select category --',
	'SELECT_CATEGORY_TYPE'		=> '-- Select category type --',
	'SELECT_SORT_METHOD'		=> 'Sort By',
	'SHOW_ALL_REVISIONS'		=> 'Show all revisions',
	'SITE_INDEX'				=> 'Site Index',
	'SNIPPET'					=> 'Snippet',
	'SOFT_DELETE_TOPIC'			=> 'Soft Delete Topic',
	'SORT'						=> 'Sort',
	'SORT_CONTRIB_NAME'			=> 'Contribution Name',
	'SORT_CONTRIB_NAME_ASC'		=> 'Ascending contribution name',
	'SORT_CONTRIB_NAME_DESC'	=> 'Descending contribution name',
	'STICKIES'					=> 'Stickies',
	'SUBSCRIBE'					=> 'Subscribe',
	'SUBSCRIBE_CATEGORY'		=> 'Subscribe to category',
	'SUBSCRIBE_CONTRIB'			=> 'Subscribe to contribution',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Subscription Notification',
	'SUBSCRIBE_QUEUE'			=> 'Subscribe to queue',
	'SUBSCRIBE_SUPPORT'			=> 'Subscribe to support section',
	'SUBSCRIBE_TOPIC'			=> 'Subscribe to topic',
	'SUCCESSBOX_TITLE'			=> 'Success',
	'SYNC_SUCCESS'				=> 'Sync Success',

	'TITANIA_DISABLED'			=> 'The Customisation Database is temporarily disabled, please try again in a few minutes.',
	'TITANIA_INDEX'				=> 'Customisation Database',
	'TRANSLATION'				=> 'Language Pack',
	'TRANSLATIONS'				=> 'Language Packs',
	'TYPE'						=> 'Type',

	'UNAPPROVED'				=> 'Unapproved',
	'UNDELETE_TOPIC'			=> 'Undelete Topic',
	'UNKNOWN'					=> 'Unknown',
	'UNSUBSCRIBE'				=> 'Unsubscribe',
	'UNSUBSCRIBE_CATEGORY'		=> 'Unsubscribe from category',
	'UNSUBSCRIBE_CONTRIB'		=> 'Unsubscribe from contribution',
	'UNSUBSCRIBE_QUEUE'			=> 'Unsubscribe from queue',
	'UNSUBSCRIBE_SUPPORT'		=> 'Unsubscribe from support section',
	'UNSUBSCRIBE_TOPIC'			=> 'Unsubscribe from topic',
	'UPDATE_TIME'				=> 'Updated',
	'UPDATE_TIME_ASC'			=> 'Least recently updated',
	'UPDATE_TIME_DESC'			=> 'Most recently updated',

	'VERSION'					=> 'Version',
	'VIEW'						=> 'View',
));
