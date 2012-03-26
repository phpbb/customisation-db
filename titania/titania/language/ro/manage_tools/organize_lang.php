<?php
/**
*
* @package Support Tool Kit - Organize Language Files
* @copyright (c) 2009 phpBB Group
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
	'NO_FILE'						=> 'Fişierul cerut nu există.',

	'ORGANIZE_LANG'					=> 'Organizare fişiere limbă',
	'ORGANIZE_LANG_EXPLAIN'			=> 'Aceasta vă permite să organizaţi un fişier sau director de limbă. Pentru mai multe informaţii <a href="http://www.lithiumstudios.org/forum/viewtopic.php?f=9&t=841">citiţi acest subiect</a>.',
	'ORGANIZE_LANG_FILE'			=> 'Fişier',
	'ORGANIZE_LANG_FILE_EXPLAIN'	=> 'Introduceţi numele fişierului sau directorului pe care vreţi să-l organizaţi.<br />Exemplu: en/mods/ pentru language/en/mods/, sau en/common pentru language/en/common.php',
	'ORGANIZE_LANG_SUCCESS'			=> 'Fişierul sau directorul de limbă a fost cu succes organizat.',
));
