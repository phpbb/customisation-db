<?php
/**
*
* @package Support Tool Kit - Organize Language Files
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'NO_FILE'						=> 'El archivo solicitado no existe',

	'ORGANIZE_LANG'					=> 'Organizar los archivos de idiomas',
	'ORGANIZE_LANG_EXPLAIN'			=> 'Esto le permite organizar un archivo de idioma o del directorio. Para obtener más información <a href="http://www.lithiumstudios.org/forum/viewtopic.php?f=9&t=841">leer este tema</a>.',
	'ORGANIZE_LANG_FILE'			=> 'Archivo',
	'ORGANIZE_LANG_FILE_EXPLAIN'	=> 'Escriba el nombre del archivo o directorio que desea organizar. <br />Ejemplo: es/mods/ para lenguage/es/mods/, o es/common para language/es/common.php',
	'ORGANIZE_LANG_SUCCESS'			=> 'El archivo de idioma o del directorio se ha organizado con éxito.',
));
