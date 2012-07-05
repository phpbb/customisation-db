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
	'ADMINISTRATION'			=> 'Administración',
	'ALLOW_AUTHOR_REPACK'		=> 'Permitir al autor Reempaquetar',
	'ALTER_NOTES'				=> 'Alterar notas de validación',
	'APPROVE'					=> 'Aprobar',
	'APPROVE_QUEUE'				=> 'Aprobar',
	'APPROVE_QUEUE_CONFIRM'		=> '¿Está seguro que desea aprobar <strong> </ strong> este artículo?',
	'ATTENTION'					=> 'Atención',
	'AUTHOR_REPACK_LINK'		=> 'Haga clic aquí para Reempaquetar la revisión',

	'CATEGORY_NAME_CLEAN'		=> 'URL de categoría',
	'CHANGE_STATUS'				=> 'Cambia estado/mover',
	'CLOSED_ITEMS'				=> 'Artículos cerrados',

	'DELETE_QUEUE'				=> 'Eliminar la entrada de la cola',
	'DELETE_QUEUE_CONFIRM'		=> '¿Está seguro que desea eliminar esta entrada de la cola? Todos los puestos de la cola se perderán y la revisión será fijada a desechado si es nuevo.',
	'DENY'						=> 'negar',
	'DENY_QUEUE'				=> 'negar',
	'DENY_QUEUE_CONFIRM'		=> '¿Está seguro que desea <strong>negar</strong> este artículo?',
    'DISAPPROVE_ITEM'			=> 'Desaprobada',
	'DISAPPROVE_ITEM_CONFIRM'	=> '¿Seguro que deseas <strong>rechazar</strong> este artículo?',
	'DISCUSSION_REPLY_MESSAGE'	=> 'Respuesta en la cola de discusión',	

	'EDIT_VALIDATION_NOTES'		=> 'Editar notas de validación',

	'MANAGE_CATEGORIES'			=> 'Administrar categorías',
	'MARK_IN_PROGRESS'			=> 'Marca "en curso"',
	'MARK_NO_PROGRESS'			=> 'Desmarcar "en curso"',
	'MOVE_QUEUE'				=> 'Mover la cola',
	'MOVE_QUEUE_CONFIRM'		=> 'Seleccione la ubicación de la nueva cola y confirmar.',

	'NO_ATTENTION'				=> 'No hay artículos que necesiten atención.',
	'NO_ATTENTION_ITEM'			=> 'Atención el tema no existe.',
	'NO_ATTENTION_TYPE'			=> 'Tipo de atención inadecuado',
	'NO_NOTES'					=> 'No hay notas',
	'NO_QUEUE_ITEM'				=> 'No existe elemento de cola',

	'OLD_VALIDATION_AUTOMOD'	=> 'prueba de reempaquetado de AutoMOD',
	'OLD_VALIDATION_MPV'		=> 'MPV Notas de reempaquetado',
	'OPEN_ITEMS'				=> 'Temas abiertos',

	'PUBLIC_NOTES'				=> 'Notas publicas',

	'QUEUE_APPROVE'				=> 'Esperando aprobación',
	'QUEUE_ATTENTION'			=> 'Atención',
	'QUEUE_DENY'				=> 'Esperando denegación',
	'QUEUE_DISCUSSION_TOPIC'	=> 'Cola de tema de discusión',
	'QUEUE_NEW'					=> 'Nuevo',
	'QUEUE_REPACK'				=> 'Reempaquetado',
	'QUEUE_REPACK_ALLOWED'		=> 'Permitir Reempaquetado',
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Reempaquetado <strong>no</strong> permitido',
	'QUEUE_REPLY_ALLOW_REPACK'	=> 'Permitir al autor Reempaquetar',
	'QUEUE_REPLY_APPROVED'		=> 'Revisión %1$s [b]Aprobada[/b] por la razón:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'Revisión %1$s [b]Denegado/b] por la razón:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_IN_PROGRESS'	=> 'Marcado como en curso',
	'QUEUE_REPLY_MOVE'			=> 'Se movió %1$s a %2$s',
	'QUEUE_REPLY_NO_PROGRESS'	=> 'Desmarcado como en curso',
	'QUEUE_REVIEW'				=> 'Revisión de la cola',
	'QUEUE_STATUS'				=> 'Estado de la cola',
	'QUEUE_TESTING'				=> 'Prueba',
	'QUEUE_VALIDATING'			=> 'Validación',

	'REBUILD_FIRST_POST'		=> 'Reconstruir primer mensaje',
	'REPACK'					=> 'Reempaquetar',
	'REPORTED'					=> 'Reportar',
	'RETEST_AUTOMOD'			=> 'Repetir prueba AutoMOD',
	'RETEST_MPV'				=> 'Repetir MPV',
	'REVISION_REPACKED'			=> 'Esta revisión ha sido Reempaquetada de nuevo',

	'SUBMIT_TIME'				=> 'Tiempo de presentación',
	'SUPPORT_ALL_VERSIONS'		=> 'Soporte para todas las versiones de phpBB',

	'UNAPPROVED'				=> 'No Aprobado',
	'UNKNOWN'					=> 'Desconocido',

	'VALIDATION'				=> 'Validación',
	'VALIDATION_AUTOMOD'		=> 'Prueba de AutoMOD',
	'VALIDATION_MESSAGE'		=> 'Validación Mensaje/razón',
	'VALIDATION_MPV'			=> 'Notas MPV',
	'VALIDATION_NOTES'			=> 'Notas de validación',
	'VALIDATION_QUEUE'			=> 'Cola de validación',
	'VALIDATION_SUBMISSION'		=> 'Validación de presentación',
));
