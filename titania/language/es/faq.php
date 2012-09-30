<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* Traducción hecha y revisada por nextgen <http://www.melvingarcia.com>
* Traductores anteriores angelismo y sof-teo
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
	'CREATE_FAQ'			=> 'Crear FAQ',

	'DELETE_FAQ'			=> 'Eliminar FAQ',
	'DELETE_FAQ_CONFIRM'	=> 'Esta seguro de quiere eliminar esta FAQ?',

	'EDIT_FAQ'				=> 'Editar FAQ',

	'FAQ_CREATED'			=> 'La FAQ ha sido creada con éxito',
	'FAQ_DELETED'			=> 'La entrada de FAQ ha sido eliminada',
	'FAQ_DETAILS'			=> 'Detalles de página FAQ',
	'FAQ_EDITED'			=> 'La FAQ ha sido editada con éxito.',
	'FAQ_EXPANDED'			=> 'Preguntas más frecuentes',
	'FAQ_LIST'				=> 'Lista de FAQ',
	'FAQ_NOT_FOUND'			=> 'La FAQ seleccionada no ha sido encontrada',

	'NO_FAQ'				=> 'No hay entradas de preguntas frecuentes',

	'QUESTIONS'				=> 'Preguntas',
));
