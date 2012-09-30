<?php
/**
* acp_permissions_titania (Titania Permission Set) [English]
*
* @package language
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

/**
*	MODDERS PLEASE NOTE
*
*	You are able to put your permission sets into a separate file too by
*	prefixing the new file with permissions_ and putting it into the acp
*	language folder.
*
*	An example of how the file could look like:
*
*	<code>
*
*	if (empty($lang) || !is_array($lang))
*	{
*		$lang = array();
*	}
*
*	// Adding new category
*	$lang['permission_cat']['bugs'] = 'Bugs';
*
*	// Adding new permission set
*	$lang['permission_type']['bug_'] = 'Bug Permissions';
*
*	// Adding the permissions
*	$lang = array_merge($lang, array(
*		'acl_bug_view'		=> array('lang' => 'Can view bug reports', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'Can post bugs', 'cat' => 'post'), // Using a phpBB category here
*	));
*
*	</code>
*/

// Define categories and permission types
/*$lang = array_merge($lang, array(
	'permission_cat'	=> array(
		'actions'		=> 'Acciones',
		'content'		=> 'Contenido',
		'forums'		=> 'Foros',
		'misc'			=> 'Miscelaneos',
		'permissions'	=> 'Permisos',
		'pm'			=> 'Mensajes privados',
		'polls'			=> 'Encuentas',
		'post'			=> 'Mensajes',
		'post_actions'	=> 'Acciones de post',
		'posting'		=> 'Postear',
		'profile'		=> 'Perfíl',
		'settings'		=> 'Configuración',
		'topic_actions'	=> 'Acción de temas',
		'user_group'	=> 'Usuarios &amp; Grupos',
	),
));*/

$lang['permission_cat']['titania'] = 'Titania';
$lang['permission_cat']['titania_moderate'] = ' Moderación Titania';

$lang = array_merge($lang, array(
	// Common
	'acl_u_titania_contrib_submit'		=> array('lang' => 'Puede enviar contribuciones', 'cat' => 'titania'),
	'acl_u_titania_faq_create'			=> array('lang' => 'Puede crear  entradas en FAQ(para sus contribuciones)', 'cat' => 'titania'),
	'acl_u_titania_faq_edit'			=> array('lang' => 'Puede editar entradas en FAQ (psra sus contribuciones)', 'cat' => 'titania'),
	'acl_u_titania_faq_delete'			=> array('lang' => 'Puede eliminar entradas en FAQ  (para sus contribuciones)', 'cat' => 'titania'),
	'acl_u_titania_rate'				=> array('lang' => 'Puede dar evaluaciones', 'cat' => 'titania'),
	'acl_u_titania_topic'				=> array('lang' => 'Puede crear nuevos temas', 'cat' => 'titania'),
	'acl_u_titania_post'				=> array('lang' => 'Puede crear nuevos mensajes', 'cat' => 'titania'),
	'acl_u_titania_post_approved'		=> array('lang' => 'Puede crear mensajes<strong>sin</strong> aprobación', 'cat' => 'titania'),
	'acl_u_titania_post_edit_own'		=> array('lang' => 'Puede editar sus mensajes', 'cat' => 'titania'),
	'acl_u_titania_post_delete_own'		=> array('lang' => 'Puede borrar sus mensajes', 'cat' => 'titania'),
	'acl_u_titania_post_mod_own'		=> array('lang' => 'Puede moderar los temas de sus contribuciones', 'cat' => 'titania'),
	'acl_u_titania_post_attach'			=> array('lang' => 'Puede adjuntar archivos en los mensajes', 'cat' => 'titania'),
	'acl_u_titania_bbcode'				=> array('lang' => 'Puede usar BBCODE en los mensajes', 'cat' => 'titania'),
	'acl_u_titania_smilies'				=> array('lang' => 'Puede usar smilies', 'cat' => 'titania'),

	'acl_u_titania_post_hard_delete'	=> array('lang' => 'Puede <strong>eliminar</strong> mensajes y temas (mensajes y temas que el usuario puede eliminar de otro modo).', 'cat' => 'titania'),

	// Moderation
	'acl_u_titania_mod_author_mod'			=> array('lang' => 'Puede moderar los perfíles de los autores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_contrib_mod'			=> array('lang' => 'Puede moderar las contribuciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_faq_mod'				=> array('lang' => 'Puede moderar las entradas de FAQ ', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_rate_reset'			=> array('lang' => 'Puede resetear evaluaciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_post_mod'			=> array('lang' => 'Puede moderar temas', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_style_queue_discussion'			=> array('lang' => 'Puede ver la discusión de la cola de estilos', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_queue'						=> array('lang' => 'Puede ver la cola de estilos', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_validate'					=> array('lang' => 'Puede validar estilos', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_moderate'					=> array('lang' => 'Puede moderar estilos', 'cat' => 'titania_moderate'),
    'acl_u_titania_mod_style_clr'                       => array('lang' => 'Can edit ColorizeIt defaults', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_modification_queue_discussion'	=> array('lang' => 'Puede ver la discusión de la cola de modificaciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_queue'				=> array('lang' => 'Puede ver la cola de modificaciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_validate'			=> array('lang' => 'Puede validar modificaciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_moderate'			=> array('lang' => 'Puede moderar modificaciones', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_translation_queue_discussion'		=> array('lang' => 'Puede ver la discusión de la cola de traducciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_queue'					=> array('lang' => 'Puede ver la cola de traducciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_validate'				=> array('lang' => 'Puede validar traducciones', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_moderate'				=> array('lang' => 'Puede moderar traducciones', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_converter_queue_discussion'		=> array('lang' => 'Puede ver la discusión de la cola de conversores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_queue'					=> array('lang' => 'Puede ver la cola de conversores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_validate'				=> array('lang' => 'Puede validar conversores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_moderate'				=> array('lang' => 'Puede moderar conversores', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_bbcode_queue_discussion'			=> array('lang' => 'Puede ver la discusión de la cola del BBcode personalizado', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bbcode_queue'					=> array('lang' => 'Puede ver la cola del BBcode personalizado', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bbcode_validate'					=> array('lang' => 'Puede validar BBcodes personalizados', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bbcode_moderate'					=> array('lang' => 'Puede moderar BBcodes personalizados', 'cat' => 'titania_moderate'),
	
	'acl_u_titania_mod_bridge_queue_discussion'			=> array('lang' => 'Puede ver la discusión de la cola de bridge', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_queue'					=> array('lang' => 'Puede ver la cola de puente', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_validate'					=> array('lang' => 'Puede validar puentes', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_moderate'					=> array('lang' => 'Puede moderar puentes', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_official_tool_moderate'			=> array('lang' => 'Puede enviar/moderar herramientas oficiales', 'cat' => 'titania_moderate'),

	'acl_u_titania_admin'			=> array('lang' => 'Puede <strong>administrar</strong> Titania', 'cat' => 'titania_moderate'),
));

?>