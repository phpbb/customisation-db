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
	'ACCESS'					=> 'Access',
	'ACCESS_TEAMS'				=> 'Teams',
	'ACCESS_AUTHORS'			=> 'Authors',
	'ACCESS_PUBLIC'				=> 'Public',
	'AUTHOR_BY'					=> 'By %s',
	'AUTHOR_PROFILE'			=> 'Your Author Profile',

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
	'CONTRIBUTIONS'				=> 'Contributions',
	'CREATE_CONTRIBUTION'		=> 'Create Contribution',
	'CUSTOMISATION_DATABASE'	=> 'Customisation Database',

	'DESCRIPTION'				=> 'Description',
	'DETAILS'					=> 'Details',
	'DOWNLOAD_ACCESS_DENIED'	=> 'You are not allowed to download the requested file.',
	'DOWNLOAD_NOT_FOUND'		=> 'The requested file could not be found.',

	'EDIT'						=> 'Edit',

	'LANGUAGE_PACK'				=> 'Language Pack',
	'LIST'						=> 'List',

	'MODIFICATION'				=> 'Modification',
	'MOVE_UP'					=> 'Move up',
	'MOVE_DOWN'					=> 'Move down',

	'NO_DESC'					 => 'You have to enter the description.',

	'PURGE_CACHE'				=> 'Purge Cache',

	'RATING'					=> 'Rating',
	'REMOVE_RATING'				=> 'Remove Rating',
	'RETURN_LAST_PAGE'			=> 'Return to the previous page',

	'SITE_INDEX'				=> 'Site Index',
	'SNIPPET'					=> 'Snippet',
	'STYLE'						=> 'Style',

	'TITANIA_INDEX'				=> 'Customisation Database',
));
