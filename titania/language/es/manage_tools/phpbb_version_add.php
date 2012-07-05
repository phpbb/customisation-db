<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
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
	'CATEGORY_EXPLAIN'				=> 'Limitar el soporte a la nueva versión sólo en las categorías seleccionadas.',

	'NEW_PHPBB_VERSION'				=> 'Nueva versión de phpBB',
	'NEW_PHPBB_VERSION_EXPLAIN'		=> 'Nueva versión de phpBB en la revisión de la lista de soporte.',
	'NO_REVISIONS_UPDATED'			=> 'No hay revisiones actualizadas a partir de las limitaciones establecidas.',
	'NO_VERSION_SELECTED'			=> 'Debe dar su versión de phpBB.  Ej: 3.0.7 o 3.0.7-PL1.',

	'PHPBB_VERSION_ADD'				=> 'Añadir nueva versión de phpBB a las revisiones de soporte',

	'REVISIONS_UPDATED'				=> '%s las revisiones se han actualizado',

	'VERSION_RESTRICTION'			=> 'Versión restringida',
	'VERSION_RESTRICTION_EXPLAIN'	=> 'Limitar el soporte a la nueva versión sólo en las versiones seleccionadas.',
));
