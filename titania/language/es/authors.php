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
	'AUTHOR_CONTRIBS'			=> 'Contribuciones',
	'AUTHOR_DATA_UPDATED'		=> 'La información del autor ha sido actualziada',
	'AUTHOR_DESC'				=> 'Descripción del autor',
	'AUTHOR_DETAILS'			=> 'Detalles del autor',
	'AUTHOR_MODS'				=> '%d Modificaciones',
	'AUTHOR_MODS_ONE'			=> '1 Modificación',
	'AUTHOR_NOT_FOUND'			=> 'Autor no encontrado',
	'AUTHOR_PROFILE'			=> 'Perfil del autor',
	'AUTHOR_RATING'				=> 'Evaluación de autor',
	'AUTHOR_REAL_NAME'			=> 'Nombre real del autor',
	'AUTHOR_SNIPPETS'			=> '%d Fragmentos',
	'AUTHOR_SNIPPETS_ONE'		=> '1 Fragmento',
	'AUTHOR_STATISTICS'			=> 'estadísticas del autor',
	'AUTHOR_STYLES'				=> '%d estilos',
	'AUTHOR_STYLES_ONE'			=> '1 estilo',
	'AUTHOR_SUPPORT'			=> 'Soporte',

	'ENHANCED_EDITOR'			=> 'Editor mejorado',
	'ENHANCED_EDITOR_EXPLAIN'	=> 'Habilitar / deshabilitar el editor mejorado .',

	'MANAGE_AUTHOR'				=> 'Administrar autor',

	'NO_AVATAR'					=> 'Sin avatar',

	'PHPBB_PROFILE'				=> ' perfil en phpBB.com ',

	'USER_INFORMATION'			=> '’s información de usuario',

	'VIEW_USER_PROFILE'			=> 'Ver perfil de usuario',


));
