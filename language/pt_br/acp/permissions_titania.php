<?php
/**
* acp_permissions_titania (Titania Permission Set) [Brazilian Portuguese]
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
* Tradução feita e revisada pela Equipe phpBB Brasil <http://www.phpbbrasil.com.br>!
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
		'actions'		=> 'Actions',
		'content'		=> 'Content',
		'forums'		=> 'Forums',
		'misc'			=> 'Misc',
		'permissions'	=> 'Permissions',
		'pm'			=> 'Private messages',
		'polls'			=> 'Polls',
		'post'			=> 'Post',
		'post_actions'	=> 'Post actions',
		'posting'		=> 'Posting',
		'profile'		=> 'Profile',
		'settings'		=> 'Settings',
		'topic_actions'	=> 'Topic actions',
		'user_group'	=> 'Users &amp; Groups',
	),
));*/

$lang['permission_cat']['titania'] = 'Titania';
$lang['permission_cat']['titania_moderate'] = 'Moderar Titania';

$lang = array_merge($lang, array(
	// Common
	'acl_u_titania_contrib_submit'		=> array('lang' => 'Pode enviar contribuições', 'cat' => 'titania'),
	'acl_u_titania_faq_create'			=> array('lang' => 'Pode criar entradas no FAQ (para contribuições próprias)', 'cat' => 'titania'),
	'acl_u_titania_faq_edit'			=> array('lang' => 'Pode editar entradas no FAQ (para contribuições próprias)', 'cat' => 'titania'),
	'acl_u_titania_faq_delete'			=> array('lang' => 'Pode excluir entradas no FAQ (para contribuições próprias)', 'cat' => 'titania'),
	'acl_u_titania_rate'				=> array('lang' => 'Pode avaliar itens', 'cat' => 'titania'),
	'acl_u_titania_topic'				=> array('lang' => 'Pode criar novos tópicos', 'cat' => 'titania'),
	'acl_u_titania_post'				=> array('lang' => 'Pode criar novas mensagens', 'cat' => 'titania'),
	'acl_u_titania_post_approved'		=> array('lang' => 'Pode postar <strong>sem</strong> aprovação', 'cat' => 'titania'),
	'acl_u_titania_post_edit_own'		=> array('lang' => 'Pode editar as próprias mensagens', 'cat' => 'titania'),
	'acl_u_titania_post_delete_own'		=> array('lang' => 'Pode excluir as próprias mensagens', 'cat' => 'titania'),
	'acl_u_titania_post_mod_own'		=> array('lang' => 'Pode moderar tópicos de contribuições próprias', 'cat' => 'titania'),
	'acl_u_titania_post_attach'			=> array('lang' => 'Pode anexar arquivos nas mensagens', 'cat' => 'titania'),
	'acl_u_titania_bbcode'				=> array('lang' => 'Pode postar BBCode', 'cat' => 'titania'),
	'acl_u_titania_smilies'				=> array('lang' => 'Pode postar smilies', 'cat' => 'titania'),

	'acl_u_titania_post_hard_delete'	=> array('lang' => 'Pode excluir <strong>definitivamente</strong> mensagens e tópicos (mensagens e tópicos que o usuário já possua permissão para excluir).', 'cat' => 'titania'),

	// Moderation
	'acl_u_titania_mod_author_mod'			=> array('lang' => 'Pode moderar perfis de autores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_contrib_mod'			=> array('lang' => 'Pode moderar (todas) as contribuições', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_faq_mod'				=> array('lang' => 'Pode moderar entradas no FAQ', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_rate_reset'			=> array('lang' => 'Pode resetar avaliações', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_post_mod'			=> array('lang' => 'Pode moderar tópicos', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_style_queue_discussion'			=> array('lang' => 'Pode ver a fila de discussões de estilos', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_queue'						=> array('lang' => 'Pode ver a fila de estilos', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_validate'					=> array('lang' => 'Pode validar estilos', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_moderate'					=> array('lang' => 'Pode moderar estilos', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_modification_queue_discussion'	=> array('lang' => 'Pode ver a fila de discussões de modificações', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_queue'				=> array('lang' => 'Pode ver a fila de modificações', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_validate'			=> array('lang' => 'Pode validar modificações', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_moderate'			=> array('lang' => 'Pode moderar modificações', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_translation_queue_discussion'		=> array('lang' => 'Pode ver a fila de discussões de traduções', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_queue'					=> array('lang' => 'Pode ver a fila de traduções', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_validate'				=> array('lang' => 'Pode validar traduções', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_moderate'				=> array('lang' => 'Pode moderar traduções', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_converter_queue_discussion'		=> array('lang' => 'Pode ver a fila de discussões de conversores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_queue'					=> array('lang' => 'Pode ver a fila de conversores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_validate'				=> array('lang' => 'Pode validar conversores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_moderate'				=> array('lang' => 'Pode moderar conversores', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_bridge_queue_discussion'			=> array('lang' => 'Pode ver a fila de discussões de integradores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_queue'					=> array('lang' => 'Pode ver a fila de integradores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_validate'					=> array('lang' => 'Pode validar integradores', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_moderate'					=> array('lang' => 'Pode moderar integradores', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_official_tool_moderate'			=> array('lang' => 'Pode enviar/moderar ferramentas oficiais', 'cat' => 'titania_moderate'),

	'acl_u_titania_admin'			=> array('lang' => 'Pode <strong>administrar</strong> a Titania', 'cat' => 'titania_moderate'),
));

?>