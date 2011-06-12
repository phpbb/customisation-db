<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
	'ADD_FIELD'					=> 'Add Field',
	'AGREE'						=> 'I agree',
	'AGREEMENT'					=> 'Agreement',
	'ALL'						=> 'All',
	'ALL_CONTRIBUTIONS'			=> 'All contributions',
	'ALL_SUPPORT'				=> 'All support topics',
	'AUTHOR_BY'					=> 'By %s',

	'BAD_RATING'				=> 'Rating attempt failed.',
	'BY'						=> 'by',

	'CACHE_PURGED'				=> 'Cache has been successfully purged',
	'CATEGORY'					=> 'Category',
	'CATEGORY_CHILD_AS_PARENT'	=> 'The chosen parent category cannot be selected because it is a child of this category.',
	'CATEGORY_DELETED'			=> 'Category Deleted',
	'CATEGORY_DESC'				=> 'Category Description',
	'CATEGORY_DUPLICATE_PARENT'	=> 'Category cannot be its own parent.',
	'CATEGORY_HAS_CHILDREN'		=> 'This category cannot be deleted because it contains children categories.',
	'CATEGORY_INFORMATION'		=> 'Category Information',
	'CATEGORY_NAME'				=> 'Category Name',
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
	'CLOSED_BY'					=> 'Closed by',
	'CLOSED_ITEMS'				=> 'Closed Items',
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
	'CREATE_CONTRIBUTION'		=> 'Create Contribution',
	'CUSTOMISATION_DATABASE'	=> 'Customisation Database',

	'DATE_CLOSED'				=> 'Date closed',
	'DELETED_MESSAGE'			=> 'Last deleted by %1$s on %2$s - <a href="%3$s">Click here to undelete this message</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Delete all Contributions',
	'DELETE_CATEGORY'			=> 'Delete Category',
	'DELETE_SUBCATS'			=> 'Delete Subcategories',
	'DESCRIPTION'				=> 'Description',
	'DESTINATION_CAT_INVALID'	=> 'The destination category is not able to accept contributions.',
	'DETAILS'					=> 'Details',
	'DOWNLOAD'					=> 'Download',
	'DOWNLOADS'					=> 'Downloads',
	'DOWNLOAD_ACCESS_DENIED'	=> 'You are not allowed to download the requested file.',
	'DOWNLOAD_NOT_FOUND'		=> 'The requested file could not be found.',

	'EDIT'						=> 'Edit',
	'EDITED_MESSAGE'			=> 'Last edited by %1$s on %2$s',
	'EDIT_CATEGORY'				=> 'Edit Category',
	'ERROR'						=> 'Error',

	'FILE_NOT_EXIST'			=> 'File does not exist: %s',
	'FIND_CONTRIBUTION'			=> 'Find Contribution',

	'HARD_DELETE'				=> 'Hard Delete',
	'HARD_DELETE_EXPLAIN'		=> 'Select to permanently delete this item.',
	'HARD_DELETE_TOPIC'			=> 'Hard Delete Topic',

	'LANGUAGE_PACK'				=> 'Language Pack',
	'LIST'						=> 'List',

	'MAKE_CATEGORY_VISIBLE'		=> 'Make Category Visible',
	'MANAGE'					=> 'Manage',
	'MARK_CONTRIBS_READ'		=> 'Mark contributions read',
	'MOVE_CONTRIBS_TO'			=> 'Move Contributions to',
	'MOVE_DOWN'					=> 'Move down',
	'MOVE_SUBCATS_TO'			=> 'Move Subcategories to',
	'MOVE_UP'					=> 'Move up',
	'MULTI_SELECT_EXPLAIN'		=> 'Hold down CTRL and click to select multiple items.',
	'MY_CONTRIBUTIONS'			=> 'My Contributions',

	'NAME'						=> 'Name',
	'NEW_REVISION'				=> 'New Revision',
	'NOT_AGREE'					=> 'I do not agree',
	'NO_AUTH'					=> 'You are not authorised to see this page.',
	'NO_CATEGORY'				=> 'The requested category does not exist.',
	'NO_CATEGORY_NAME'			=> 'Enter the category name',
	'NO_CONTRIB'				=> 'The requested contribution does not exist.',
	'NO_CONTRIBS'				=> 'No contributions could be found',
	'NO_DESC'					=> 'You have to enter the description.',
	'NO_DESTINATION_CATEGORY'	=> 'No destination category could be found.',
	'NO_POST'					=> 'The requested post does not exist.',
	'NO_REVISION_NAME'			=> 'No revision name provided',
	'NO_TOPIC'					=> 'The requested topic does not exist.',

	'ORDER'						=> 'Order',

	'PARENT_CATEGORY'			=> 'Parent Category',
	'PARENT_NOT_EXIST'			=> 'Parent does not exist.',
	'POST_IP'					=> 'Post IP',
	'PURGE_CACHE'				=> 'Purge Cache',

	'QUEUE'						=> 'Queue',
	'QUEUE_DISCUSSION'			=> 'Queue Discussion',
	'QUICK_ACTIONS'				=> 'Quick Actions',

	'RATING'					=> 'Rating',
	'REMOVE_RATING'				=> 'Remove Rating',
	'REPORT'					=> 'Report',
	'RETURN_LAST_PAGE'			=> 'Return to the previous page',
	'ROOT'						=> 'Root',

	'SEARCH_UNAVAILABLE'		=> 'The search system is currently unavailable.  Please try again in a few minutes.',
	'SELECT_CATEGORY'			=> '-- Select category --',
	'SELECT_CATEGORY_TYPE'		=> '-- Select category type --',
	'SELECT_SORT_METHOD'		=> 'Sort By',
	'SHOW_ALL_REVISIONS'		=> 'Show all revisions',
	'SITE_INDEX'				=> 'Site Index',
	'SNIPPET'					=> 'Snippet',
	'SOFT_DELETE_TOPIC'			=> 'Soft Delete Topic',
	'SORT_CONTRIB_NAME'			=> 'Contribution Name',
	'STICKIES'					=> 'Stickies',
	'SUBSCRIBE'					=> 'Subscribe',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Subscription Notification',

	'TITANIA_DISABLED'			=> 'The Customisation Database is temporarily disabled, please try again in a few minutes.',
	'TITANIA_INDEX'				=> 'Customisation Database',
	'TOTAL_CONTRIBS'			=> '%d Contributions',
	'TOTAL_CONTRIBS_ONE'		=> '1 Contribution',
	'TOTAL_POSTS'				=> '%d Posts',
	'TOTAL_POSTS_ONE'			=> '1 Post',
	'TOTAL_RESULTS'				=> '%d Results',
	'TOTAL_RESULTS_ONE'			=> '1 Result',
	'TOTAL_TOPICS'				=> '%d Topics',
	'TOTAL_TOPICS_ONE'			=> '1 Topic',
	'TRANSLATION'				=> 'Translation',
	'TRANSLATIONS'				=> 'Translations',
	'TYPE'						=> 'Type',

	'UNDELETE_TOPIC'			=> 'Undelete Topic',
	'UNKNOWN'					=> 'Unknown',
	'UNSUBSCRIBE'				=> 'Unsubscribe',
	'UPDATE_TIME'				=> 'Updated',

	'VERSION'					=> 'Version',
	'VIEW'						=> 'View',
));
