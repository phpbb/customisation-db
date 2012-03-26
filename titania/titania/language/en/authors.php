<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
	'AUTHOR_CONTRIBS'			=> 'Contributions',
	'AUTHOR_DATA_UPDATED'		=> 'The author’s information has been updated.',
	'AUTHOR_DESC'				=> 'Author Description',
	'AUTHOR_DETAILS'			=> 'Author Details',
	'AUTHOR_MODS'				=> '%d Modifications',
	'AUTHOR_MODS_ONE'			=> '1 Modification',
	'AUTHOR_NOT_FOUND'			=> 'Author not found',
	'AUTHOR_PROFILE'			=> 'Author Profile',
	'AUTHOR_RATING'				=> 'Author Rating',
	'AUTHOR_REAL_NAME'			=> 'Real Name',
	'AUTHOR_SNIPPETS'			=> '%d Snippets',
	'AUTHOR_SNIPPETS_ONE'		=> '1 Snippet',
	'AUTHOR_STATISTICS'			=> 'Author Statistics',
	'AUTHOR_STYLES'				=> '%d Styles',
	'AUTHOR_STYLES_ONE'			=> '1 Style',
	'AUTHOR_SUPPORT'			=> 'Support',

	'ENHANCED_EDITOR'			=> 'Enhanced Editor',
	'ENHANCED_EDITOR_EXPLAIN'	=> 'Enable/disable the enhanced editor (captures tabs and automatically expands textareas).',

	'MANAGE_AUTHOR'				=> 'Manage Author',

	'NO_AVATAR'					=> 'No avatar',

	'PHPBB_PROFILE'				=> 'phpBB.com profile',

	'USER_INFORMATION'			=> '’s user information',

	'VIEW_USER_PROFILE'			=> 'View user profile',
));
