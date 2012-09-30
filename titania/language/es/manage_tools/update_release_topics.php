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
	'UPDATE_RELEASE_TOPICS'				=> 'Actualización de todos los temas publicados en la base de datos del foro',
	'UPDATE_RELEASE_TOPICS_COMPLETE'	=> 'Todos los temas liberados se han actualizado!',
	'UPDATE_RELEASE_TOPICS_CONFIRM'		=> '¿Está seguro que desea actualizar todos los temas publicados en la base de datos del foro? Esto puede tomar un período considerablemente largo de tiempo.',
	'UPDATE_RELEASE_TOPICS_PROGRESS'	=> '%1$d temas completos de %2$d. Por favor espere...',
));
