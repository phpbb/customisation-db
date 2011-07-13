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
	'acl_u_titania_contrib_submit'		=> array('lang' => 'Can submit contributions', 'cat' => 'titania'),
	'acl_u_titania_faq_create'			=> array('lang' => 'Can create FAQ entries (for own Contributions)', 'cat' => 'titania'),
	'acl_u_titania_faq_edit'			=> array('lang' => 'Can edit FAQ entries (for own Contributions)', 'cat' => 'titania'),
	'acl_u_titania_faq_delete'			=> array('lang' => 'Can delete FAQ entries (for own Contributions)', 'cat' => 'titania'),
	'acl_u_titania_rate'				=> array('lang' => 'Can rate items', 'cat' => 'titania'),
	'acl_u_titania_topic'				=> array('lang' => 'Can create new topics', 'cat' => 'titania'),
	'acl_u_titania_post'				=> array('lang' => 'Can create new posts', 'cat' => 'titania'),
	'acl_u_titania_post_approved'		=> array('lang' => 'Can post <strong>without</strong> approval', 'cat' => 'titania'),
	'acl_u_titania_post_edit_own'		=> array('lang' => 'Can edit own posts', 'cat' => 'titania'),
	'acl_u_titania_post_delete_own'		=> array('lang' => 'Can delete own posts', 'cat' => 'titania'),
	'acl_u_titania_post_mod_own'		=> array('lang' => 'Can moderate own contribution topics', 'cat' => 'titania'),
	'acl_u_titania_post_attach'			=> array('lang' => 'Can attach files to posts', 'cat' => 'titania'),
	'acl_u_titania_bbcode'				=> array('lang' => 'Can post BBCode', 'cat' => 'titania'),
	'acl_u_titania_smilies'				=> array('lang' => 'Can post smilies', 'cat' => 'titania'),

	'acl_u_titania_post_hard_delete'	=> array('lang' => 'Can <strong>hard</strong> delete posts and topics (posts and topics that the user is able to otherwise delete).', 'cat' => 'titania'),

	// Moderation
	'acl_u_titania_mod_author_mod'			=> array('lang' => 'Can moderate author profiles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_contrib_mod'			=> array('lang' => 'Can moderate (all) contributions', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_faq_mod'				=> array('lang' => 'Can moderate FAQ entries', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_rate_reset'			=> array('lang' => 'Can reset ratings', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_post_mod'			=> array('lang' => 'Can moderate topics', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_style_queue_discussion'			=> array('lang' => 'Can see Styles Queue Discussion', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_queue'						=> array('lang' => 'Can see Styles Queue', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_validate'					=> array('lang' => 'Can validate Styles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_moderate'					=> array('lang' => 'Can moderate Styles', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_style_clr'                       => array('lang' => 'Can edit ColorizeIt defaults', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_modification_queue_discussion'	=> array('lang' => 'Can see Modifications Queue Discussion', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_queue'				=> array('lang' => 'Can see Modifications Queue', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_validate'			=> array('lang' => 'Can validate Modifications', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_modification_moderate'			=> array('lang' => 'Can moderate Modifications', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_translation_queue_discussion'		=> array('lang' => 'Can see Translation Queue Discussion', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_queue'					=> array('lang' => 'Can see Translation Queue', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_validate'				=> array('lang' => 'Can validate Translations', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_translation_moderate'				=> array('lang' => 'Can moderate Translations', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_converter_queue_discussion'		=> array('lang' => 'Can see Converter Queue Discussion', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_queue'					=> array('lang' => 'Can see Converter Queue', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_validate'				=> array('lang' => 'Can validate Converters', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_converter_moderate'				=> array('lang' => 'Can moderate Converters', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_bridge_queue_discussion'			=> array('lang' => 'Can see Bridge Queue Discussion', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_queue'					=> array('lang' => 'Can see Bridge Queue', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_validate'					=> array('lang' => 'Can validate Bridges', 'cat' => 'titania_moderate'),
	'acl_u_titania_mod_bridge_moderate'					=> array('lang' => 'Can moderate Bridges', 'cat' => 'titania_moderate'),

	'acl_u_titania_mod_official_tool_moderate'			=> array('lang' => 'Can submit/moderate Official Tools', 'cat' => 'titania_moderate'),

	'acl_u_titania_admin'			=> array('lang' => 'Can <strong>administrate</strong> Titania', 'cat' => 'titania_moderate'),
));

?>