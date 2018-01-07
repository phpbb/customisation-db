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
	'CLI_DESCRIPTION_REBUILD_COMPOSER'		=> 'Rebuild Composer repository',
	'CLI_REBUILD_COMPOSER_FORCE'			=> 'Force rebuild even if a build is in progress',
	'CLI_REBUILD_COMPOSER_FROM_FILE'		=> 'Rebuild repository from revision zip files',

	'CLI_DESCRIPTION_EXTENSION_REPACK'	 	=> 'Repack extension revisions',
	'CLI_EXTENSION_REPACK_EXT_NAME'			=> 'vendor/extname to repack. If omitted, all revisions of all extensions are repacked.',
	'CLI_EXTENSION_REPACK_MESSAGE'			=> 'Message to post in each extension’s validation discussion topic.',
	'CLI_EXTENSION_REPACK_POST_NOTE'		=> '[size=80]This post was created via CLI command [i]%1$s[/i][/size]',
	'CLI_EXTENSION_REPACK_ERROR'			=> 'Error while repacking revision %1$d (%2$s) : %3$s', // 1=revision id, 2=package name, 3=error message
	'CLI_EXTENSION_REPACK_FINISHED'			=> array(
		0 => 'No revisions to repack.',
		1 => 'Successful repacked revisions: %1$d',
	),
));
