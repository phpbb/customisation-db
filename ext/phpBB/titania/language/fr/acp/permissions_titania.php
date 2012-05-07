<?php
/**
* acp_permissions_titania (Titania Permission Set) [French]
*
* @package language
* @copyright (c) 2008 phpBB Customisation Database Team, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
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
*		'acl_bug_view'		=> array('lang' => 'can view bug reports', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'can post bugs', 'cat' => 'post'), // Using a phpBB category here
*	));
*
*	</code>
*/

// Define categories and permission types
/*$lang = array_merge($lang, array(
	'permission_cat'	=> array(
-		'actions'		=> 'Actions',
		'content'		=> 'Contenu',
		'forums'		=> 'Forums',
		'misc'			=> 'Divers',
		'permissions'	=> 'Permissions',
		'pm'			=> 'Messages privés',
		'polls'			=> 'Sondages',
		'post'			=> 'Message',
		'post_actions'	=> 'Actions du message',
		'posting'		=> 'Publication',
		'profile'		=> 'Profil',
		'settings'		=> 'Réglages',
		'topic_actions'	=> 'Actions du sujet',
		'user_group'	=> 'Utilisateurs &amp; groupes',
	),
));*/

$lang['permission_cat']['titania'] = 'Titania';
$lang['permission_cat']['titania_moderate'] = 'Modérer Titania';

$lang = array_merge($lang, array(
	// Common
	'acl_u_titania_contrib_submit'		=> array('lang' => 'Peut soumettre des contributions', 'cat' => 'titania'),
	'acl_u_titania_faq_create'			=> array('lang' => 'Peut créer des éléments de la FAQ (pour ses contributions)', 'cat' => 'titania'),
	'acl_u_titania_faq_edit'			=> array('lang' => 'Peut éditer les éléments de la FAQ (pour ses contributions)', 'cat' => 'titania'),
	'acl_u_titania_faq_delete'			=> array('lang' => 'Peut supprimer les éléments de la FAQ (pour ses contributions)', 'cat' => 'titania'),
	'acl_u_titania_rate'				=> array('lang' => 'Peut noter les éléments', 'cat' => 'titania'),
	'acl_u_titania_topic'				=> array('lang' => 'Peut créer de nouveaux sujets', 'cat' => 'titania'),
	'acl_u_titania_post'				=> array('lang' => 'Peut créer de nouveaux messages', 'cat' => 'titania'),
	'acl_u_titania_post_approved'		=> array('lang' => 'Peut publier <strong>sans</strong> approbation', 'cat' => 'titania'),
	'acl_u_titania_post_edit_own'		=> array('lang' => 'Peut éditer ses messages', 'cat' => 'titania'),
	'acl_u_titania_post_delete_own'		=> array('lang' => 'Peut supprimer ses messages', 'cat' => 'titania'),
	'acl_u_titania_post_mod_own'		=> array('lang' => 'Peut modérer les sujets de ses contributions', 'cat' => 'titania'),
	'acl_u_titania_post_attach'			=> array('lang' => 'Peut insérer des fichiers aux messages', 'cat' => 'titania'),
	'acl_u_titania_bbcode'				=> array('lang' => 'Peut publier du BBCode', 'cat' => 'titania'),
	'acl_u_titania_smilies'				=> array('lang' => 'Peut publier des émoticônes', 'cat' => 'titania'),

	'acl_u_titania_post_hard_delete'	=> array('lang' => 'Peut <strong>définitivement</strong> supprimer des messages et des sujets (ceux que l’utilisateur peut supprimer).', 'cat' => 'titania'),

	// Moderation
	'acl_u_titania_mod_author_mod'			=> array('lang' => 'Peut modérer le profil des auteurs', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_contrib_mod'			=> array('lang' => 'Peut modérer (toutes) les contributions', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_faq_mod'				=> array('lang' => 'Peut modérer les éléments de la FAQ', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_rate_reset'			=> array('lang' => 'Peut réinitialiser les notes', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_post_mod'			=> array('lang' => 'Peut modérer les sujets', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_style_queue_discussion'			=> array('lang' => 'Peut consulter la discussion de la file d’attente des styles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_queue'						=> array('lang' => 'Peut consulter la file d’attente des styles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_validate'					=> array('lang' => 'Peut valider les styles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_moderate'					=> array('lang' => 'Peut modérer les styles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_clr'                       => array('lang' => 'Peut éditer les réglages par défaut de ColorizeIt', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_modification_queue_discussion'	=> array('lang' => 'Peut consulter la discussion de la file d’attente des modifications', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_queue'				=> array('lang' => 'Peut consulter la file d’attente des modifications', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_validate'			=> array('lang' => 'Peut valider les modifications', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_moderate'			=> array('lang' => 'Peut modérer les modifications', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_translation_queue_discussion'		=> array('lang' => 'Peut consulter la discussion de la file d’attente des traductions', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_queue'					=> array('lang' => 'Peut consulter la file d’attente des traductions', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_validate'				=> array('lang' => 'Peut valider les traductions', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_moderate'				=> array('lang' => 'Peut modérer les traductions', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_converter_queue_discussion'		=> array('lang' => 'Peut consulter la discussion de la file d’attente des convertisseurs', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_queue'					=> array('lang' => 'Peut consulter la file d’attente des convertisseurs', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_validate'				=> array('lang' => 'Peut valider les convertisseurs', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_moderate'				=> array('lang' => 'Peut modérer les convertisseurs', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_bridge_queue_discussion'			=> array('lang' => 'Peut consulter la discussion de la file d’attente des bridges', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_queue'					=> array('lang' => 'Peut consulter la file d’attente des bridges', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_validate'					=> array('lang' => 'Peut valider les bridges', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_moderate'					=> array('lang' => 'Peut modérer les bridges', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_official_tool_moderate'			=> array('lang' => 'Peut soumettre et modérer des outils officiels', 'cat' => 'titania_moderate'),

	'acl_u_titania_admin'			=> array('lang' => 'Peut <strong>administrer</strong> Titania', 'cat' => 'titania_moderate'),
));

?>