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
	'ACCESS'							=> 'Nivel de acceso',
	'ACCESS_AUTHORS'					=> 'Acceso de autor',
	'ACCESS_PUBLIC'						=> 'Acceso publico',
	'ACCESS_TEAMS'						=> 'Acceso de equipo',
	'ATTACH'							=> 'Adjuntar',

	'FILE_DELETED'						=> 'Este archivo se elimina cuando se envíe',

	'HARD_DELETE_TOPIC_CONFIRM'			=> 'Estás seguro de que desea <strong>definitivamente</strong> Eliminar este tema? <br /> En este tema se ha ido para siempre!',

	'QUEUE_DISCUSSION_TOPIC_MESSAGE'	=> 'Este tema será objeto de debate entre los autores de colaboraciones y validadores.

Todo lo publicado en este tema será leído para valorar su colaboración, para por favor, puesto que aquí en lugar de utilizar mensajes privados a los validadores.

Equipo de  validación también pueden enviar preguntas a los autores aquí, así que por favor, responda con información útil para ellos como lo puede ser requerida para proceder con el procedimiento de validación.

Tenga en cuenta que por defecto este tema es privado entre los autores y los validadores y no puede ser visto por el público.',
	'QUEUE_DISCUSSION_TOPIC_TITLE'		=> 'Discusión de validación - %s',
	'QUOTE'								=> 'Citar',

	'REPORT_POST_CONFIRM'				=> 'Utilice este formulario para reportar el mensaje seleccionado a los moderadores del foro y los administradores del foro. Información general, deberían usarse sólo si el mensaje incumple las reglas del foro.',

	'SET_PREVIEW_FILE'					=> 'Establecer como vista previa',
	'SOFT_DELETE_TOPIC_CONFIRM'			=> 'Estás seguro de que desea <strong>provisoriamente</strong> Eliminar este tema?',
	'STICKIES'							=> 'Fijos',
	'STICKY_TOPIC'						=> 'Tema fijo',

	'UNDELETE_FILE'						=> 'Cancelar eliminación',
	'UNDELETE_POST'						=> 'Recuperar mensaje',
	'UNDELETE_POST_CONFIRM'				=> 'Estás seguro de que desea recuperar este mensaje?',
	'UNDELETE_TOPIC_CONFIRM'			=> 'Estás seguro de que desea recuperar este tema?',
));
