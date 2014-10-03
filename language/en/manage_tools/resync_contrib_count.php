<?php
/**
*
* @package Titania
* @copyright (c) 2012 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
	'RESYNC_CONTRIB_COUNT'				=> 'Resynchronise contribution counts',
	'RESYNC_CONTRIB_COUNT_COMPLETE'		=> 'All contribution counts have been resynchronised.',
	'RESYNC_CONTRIB_COUNT_CONFIRM'		=> 'Are you sure that you want to resynchronise all contribution counts?  This can take a significantly long period of time.',
	'RESYNC_CONTRIB_COUNT_PROGRESS'		=> '%1$d contributions completed of %2$d. Please wait…',
));
