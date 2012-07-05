<?php
/**
*
* @package Support Toolkit - Fix Left/Right ID's
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
	'FIX_LEFT_RIGHT_IDS'			=> 'Corregir Left/Right ID’s',
	'FIX_LEFT_RIGHT_IDS_CONFIRM'	=> '¿Estás seguro que quieres arreglar? La izquierda y la derecha ID<br /><br /><strong>Copia de seguridad de la base de datos antes de ejecutar esta herramienta!</strong>',

	'LEFT_RIGHT_IDS_FIX_SUCCESS'	=> 'La izquierda/derecha de identificación ha sido correctamente fijada.',
	'LEFT_RIGHT_IDS_NO_CHANGE'		=> 'La herramienta ha terminado de pasar por todos los de la izquierda y la derecha Identificación y todas las filas ya están correctos, por lo que no se introdujeron cambios.',
));
