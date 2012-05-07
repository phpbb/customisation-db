<?php
/**
* acp_permissions_titania (Titania Permission Set) [Bahasa Indonesia]
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
*	$lang['permission_cat']['bugs'] = 'Bugs';
*
*	// Adding new permission set
*	$lang['permission_type']['bug_'] = 'Bug Permissions';
*
*	// Adding the permissions
*	$lang = array_merge($lang, array(
*		'acl_bug_view'		=> array('lang' => 'Bisa melihat laporan bug', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'Bisa mempost bug', 'cat' => 'post'), // Using a phpBB category here
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
$lang['permission_cat']['titania_moderate'] = 'Titania Moderate';

$lang = array_merge($lang, array(
	// Common
	'acl_u_titania_contrib_submit'		=> array('lang' => 'Bisa mengajukan kontribusi', 'cat' => 'titania'),
	'acl_u_titania_faq_create'			=> array('lang' => 'Bisa membuat entri FAQ (untuk kontribusinya sendiri)', 'cat' => 'titania'),
	'acl_u_titania_faq_edit'			=> array('lang' => 'Bisa mengubah entri FAQ (untuk kontribusinya sendiri)', 'cat' => 'titania'),
	'acl_u_titania_faq_delete'			=> array('lang' => 'Bisa menghapus entri FAQ (untuk kontribusinya sendiri)', 'cat' => 'titania'),
	'acl_u_titania_rate'				=> array('lang' => 'Bisa memberikan penilaian pada item-item', 'cat' => 'titania'),
	'acl_u_titania_topic'				=> array('lang' => 'Bisa membuat topik baru', 'cat' => 'titania'),
	'acl_u_titania_post'				=> array('lang' => 'Bisa membuat post baru', 'cat' => 'titania'),
	'acl_u_titania_post_approved'		=> array('lang' => 'Bisa membuat post <strong>tanpa</strong> persetujuan', 'cat' => 'titania'),
	'acl_u_titania_post_edit_own'		=> array('lang' => 'Bisa mengubah postnya sendiri', 'cat' => 'titania'),
	'acl_u_titania_post_delete_own'		=> array('lang' => 'Bisa menghapus postnya sendiri', 'cat' => 'titania'),
	'acl_u_titania_post_mod_own'		=> array('lang' => 'Bisa memoderasi topik kontribusinya sendiri', 'cat' => 'titania'),
	'acl_u_titania_post_attach'			=> array('lang' => 'Bisa melampirkan file-file di post', 'cat' => 'titania'),
	'acl_u_titania_bbcode'				=> array('lang' => 'Bisa mempost BBCode', 'cat' => 'titania'),
	'acl_u_titania_smilies'				=> array('lang' => 'Bisa mempost smiley', 'cat' => 'titania'),

	'acl_u_titania_post_hard_delete'	=> array('lang' => '<strong>Bisa</strong> menghapus post dan topik (post dan topik yang bisa dihapus pengguna).', 'cat' => 'titania'),

	// Moderation
	'acl_u_titania_mod_author_mod'			=> array('lang' => 'Bisa memoderasi profil pengarang', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_contrib_mod'			=> array('lang' => 'Bisa memoderasi (semua) kontribusi', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_faq_mod'				=> array('lang' => 'Bisa memoderasi entri FAQ', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_rate_reset'			=> array('lang' => 'Bisa mengatur ulang penilaian', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_post_mod'			=> array('lang' => 'Bisa memoderasi topik', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_style_queue_discussion'			=> array('lang' => 'Bisa melihat Diskusi Antrian Gaya', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_queue'						=> array('lang' => 'Bisa melihat Antrian Gaya', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_validate'					=> array('lang' => 'Bisa mensahkan Gaya', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_moderate'					=> array('lang' => 'Bisa memoderasi Gaya', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_modification_queue_discussion'	=> array('lang' => 'Bisa melihat Diskusi Antrian Modifikasi', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_queue'				=> array('lang' => 'Bisa melihat Antrian Modifikasi', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_validate'			=> array('lang' => 'Bisa mensahkan Modifikasi', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_moderate'			=> array('lang' => 'Bisa memoderasi Modifikasi', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_translation_queue_discussion'		=> array('lang' => 'Bisa melihat Diskusi Antrian Terjemahan', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_queue'					=> array('lang' => 'Bisa melihat Antrian Terjemahan', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_validate'				=> array('lang' => 'Bisa mensahkan Terjemahan', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_moderate'				=> array('lang' => 'Bisa memoderasi Terjemahan', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_converter_queue_discussion'		=> array('lang' => 'Bisa melihat Diskusi Antrian Konvertor', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_queue'					=> array('lang' => 'Bisa melihat Antrian Konvertor', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_validate'				=> array('lang' => 'Bisa mensahkan Konvertor', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_moderate'				=> array('lang' => 'Bisa memoderasi Konvertor', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_bridge_queue_discussion'			=> array('lang' => 'Bisa melihat Diskusi Antrian Bridge', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_queue'					=> array('lang' => 'Bisa melihat Antrian Bridge', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_validate'					=> array('lang' => 'Bisa mensahkan Bridge', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_moderate'					=> array('lang' => 'Bisa memoderasi Bridges', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_official_tool_moderate'			=> array('lang' => 'Bisa mengajukan/memoderasi Perkakas Resmi', 'cat' => 'titania_moderate'),

	'acl_u_titania_admin'			=> array('lang' => 'Bisa <strong>mengadministrasi</strong> Titania', 'cat' => 'titania_moderate'),
));

?>