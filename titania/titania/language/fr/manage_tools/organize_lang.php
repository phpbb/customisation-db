<?php
/**
*
* @package Support Tool Kit - Organize Language Files
* @copyright (c) 2009 phpBB Group, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
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
	'NO_FILE'						=> 'Le fichier demandé n’existe pas.',

	'ORGANIZE_LANG'					=> 'Organiser les fichiers de langue',
	'ORGANIZE_LANG_EXPLAIN'			=> 'Ceci vous permet d’organiser un fichier ou un répertoire de langue.  Pour plus d’informations, veuillez <a href="http://www.lithiumstudios.org/forum/viewtopic.php?f=9&t=841">consulter ce sujet</a>.',
	'ORGANIZE_LANG_FILE'			=> 'Fichier',
	'ORGANIZE_LANG_FILE_EXPLAIN'	=> 'Saisissez le nom ou le répertoire du fichier que vous souhaitez organiser.<br />Par exemple : fr/mods/ pour language/fr/mods/, ou fr/common pour language/fr/common.php',
	'ORGANIZE_LANG_SUCCESS'			=> 'Le fichier ou le répertoire de langue a été organisé avec succès.',
));
