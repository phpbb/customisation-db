<?php
/**
* acp_permissions_titania (Titania Permission Set) [Romanian]
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
*	$lang['permission_cat']['bugs'] = 'Erori';
*
*	// Adding new permission set
*	$lang['permission_type']['bug_'] = 'Permisiuni Erori';
*
*	// Adding the permissions
*	$lang = array_merge($lang, array(
*		'acl_bug_view'		=> array('lang' => 'Poate vedea rapoartele cu erori', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'Poate trimite erori', 'cat' => 'post'), // Using a phpBB category here
*	));
*
*	</code>
*/

// Define categories and permission types
/*$lang = array_merge($lang, array(
	'permission_cat'	=> array(
		'actions'		=> 'Acţiuni',
		'content'		=> 'Conţinut',
		'forums'		=> 'Forumuri',
		'misc'			=> 'Diverse',
		'permissions'	=> 'Permisiuni',
		'pm'			=> 'Mesaje private',
		'polls'			=> 'Chestionare',
		'post'			=> 'Mesaj',
		'post_actions'	=> 'Acţiuni mesaj',
		'posting'		=> 'Scriere',
		'profile'		=> 'Profil',
		'settings'		=> 'Setări',
		'topic_actions'	=> 'Acţiuni subiecte',
		'user_group'	=> 'Utilizatori &amp; Grupuri',
	),
));*/

$lang['permission_cat']['titania'] = 'Titania';
$lang['permission_cat']['titania_moderate'] = 'Moderare Titania';

$lang = array_merge($lang, array(
	// Common
	'acl_u_titania_contrib_submit'		=> array('lang' => 'Poate trimite contribuţii', 'cat' => 'titania'),
	'acl_u_titania_faq_create'			=> array('lang' => 'Poate create FAQ entries (for own Contributions)', 'cat' => 'titania'),
	'acl_u_titania_faq_edit'			=> array('lang' => 'Poate modifica înregistrări FAQ (pentru Contribuţiile proprii)', 'cat' => 'titania'),
	'acl_u_titania_faq_delete'			=> array('lang' => 'Poate şterge înregistrări FAQ (pentru Contribuţiile proprii)', 'cat' => 'titania'),
	'acl_u_titania_rate'				=> array('lang' => 'Poate evalua elemente', 'cat' => 'titania'),
	'acl_u_titania_topic'				=> array('lang' => 'Poate deschide subiecte noi', 'cat' => 'titania'),
	'acl_u_titania_post'				=> array('lang' => 'Poate scrie mesaje noi', 'cat' => 'titania'),
	'acl_u_titania_post_approved'		=> array('lang' => 'Poate scrie <strong>fără</strong> aprobare', 'cat' => 'titania'),
	'acl_u_titania_post_edit_own'		=> array('lang' => 'Poate modifica propriile mesaje', 'cat' => 'titania'),
	'acl_u_titania_post_delete_own'		=> array('lang' => 'Poate şterge propriile mesaje', 'cat' => 'titania'),
	'acl_u_titania_post_mod_own'		=> array('lang' => 'Poate moderate subiectele contribuţiilor proprii', 'cat' => 'titania'),
	'acl_u_titania_post_attach'			=> array('lang' => 'Poate ataşa fişiere la mesaje', 'cat' => 'titania'),
	'acl_u_titania_bbcode'				=> array('lang' => 'Poate trimite Cod BB', 'cat' => 'titania'),
	'acl_u_titania_smilies'				=> array('lang' => 'Poate trimite zâmbete', 'cat' => 'titania'),

	'acl_u_titania_post_hard_delete'	=> array('lang' => 'Poate şterge <strong>definitiv</strong> mesaje şi subiecte (mesaje şi subiecte ce pot fi şterse de către utilizator în alte condiţii).', 'cat' => 'titania'),

	// Moderation
	'acl_u_titania_mod_author_mod'			=> array('lang' => 'Poate modera profilurile autorilor', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_contrib_mod'			=> array('lang' => 'Poate modera (toate) contribuţiile', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_faq_mod'				=> array('lang' => 'Poate modera înregistrările FAQ', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_rate_reset'			=> array('lang' => 'Poate reseta evaluări', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_post_mod'			=> array('lang' => 'Poate modera subiecte', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_style_queue_discussion'			=> array('lang' => 'Poate vedea discuţiile legate de Lista cu Stiluri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_queue'						=> array('lang' => 'Poate vedea Lista cu Stiluri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_validate'					=> array('lang' => 'Poate valida Stiluri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_moderate'					=> array('lang' => 'Poate modera Stiluri', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_modification_queue_discussion'	=> array('lang' => 'Poate vedea discuţiile legate de Lista cu Modificări', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_queue'				=> array('lang' => 'Poate vedea Lista cu Modificări', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_validate'			=> array('lang' => 'Poate valida Modificări', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_moderate'			=> array('lang' => 'Poate modera Modificări', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_translation_queue_discussion'		=> array('lang' => 'Poate vedea discuţiile legate de Lista cu Traduceri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_queue'					=> array('lang' => 'Poate vedea Lista cu Traduceri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_validate'				=> array('lang' => 'Poate valida Traduceri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_moderate'				=> array('lang' => 'Poate modera Traduceri', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_converter_queue_discussion'		=> array('lang' => 'Poate vedea discuţiile legate de Lista cu Convertoare', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_queue'					=> array('lang' => 'Poate vedea Lista cu Convertoare', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_validate'				=> array('lang' => 'Poate valida Convertoare', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_moderate'				=> array('lang' => 'Poate modera Convertoare', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_bridge_queue_discussion'			=> array('lang' => 'Poate vedea discuţiile legate de Lista cu Bridge-uri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_queue'					=> array('lang' => 'Poate vedea Lista cu Bridge-uri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_validate'					=> array('lang' => 'Poate valida Bridge-uri', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_moderate'					=> array('lang' => 'Poate modera Bridge-uri', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_official_tool_moderate'			=> array('lang' => 'Poate trimite/modera Instrumentele Oficiale', 'cat' => 'titania_moderate'),

	'acl_u_titania_admin'			=> array('lang' => 'Poate <strong>administra</strong> Titania', 'cat' => 'titania_moderate'),
));

?>