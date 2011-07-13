<?php
/**
*
* @package Support Tool Kit - Organize Language Files
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
	'NO_FILE'						=> 'The requested file does not exist.',

	'ORGANIZE_LANG'					=> 'Organize Language Files',
	'ORGANIZE_LANG_EXPLAIN'			=> 'This allows you to organize a language file or directory.  For more info <a href="http://www.lithiumstudios.org/forum/viewtopic.php?f=9&t=841">read this topic</a>.',
	'ORGANIZE_LANG_FILE'			=> 'File',
	'ORGANIZE_LANG_FILE_EXPLAIN'	=> 'Enter the file name or directory you would like to organize.<br />Example: en/mods/ for language/en/mods/, or en/common for language/en/common.php',
	'ORGANIZE_LANG_SUCCESS'			=> 'The language file or directory has been successfully organized.',
));
