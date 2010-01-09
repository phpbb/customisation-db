<?php
/**
* acp_permissions_phpbb (phpBB Permission Set) [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group
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

$lang['permission_type']['titania_'] = 'Titania Permissions';

$lang['permission_cat']['moderate'] = 'Moderate';
$lang['permission_cat']['contrib'] = 'Contributions';
$lang['permission_cat']['queue'] = 'Queue';

$lang = array_merge($lang, array(
	'ACL_TYPE_TITANIA_'					=> 'Titania Permissions',
	'ACL_TYPE_GLOBAL_TITANIA_'			=> 'Titania Permissions',

	'acl_a_titaniaauth'					=> array('lang' => 'Can alter Titania permission class', 'cat' => 'permissions'),

	// Author related
	'acl_titania_author_mod'			=> array('lang' => 'Can moderate author profiles', 'cat' => 'moderate'),

	// Contribution related
	'acl_titania_contrib_submit'		=> array('lang' => 'Can submit contributions', 'cat' => 'contrib'),
	'acl_titania_contrib_mod'			=> array('lang' => 'Can moderate (all) contributions', 'cat' => 'moderate'),
	'acl_titania_faq_create'			=> array('lang' => 'Can create FAQ entries (for own Contributions)', 'cat' => 'contrib'),
	'acl_titania_faq_edit'				=> array('lang' => 'Can edit FAQ entries (for own Contributions)', 'cat' => 'contrib'),
	'acl_titania_faq_delete'			=> array('lang' => 'Can delete FAQ entries (for own Contributions)', 'cat' => 'contrib'),
	'acl_titania_faq_mod'				=> array('lang' => 'Can moderate FAQ entries', 'cat' => 'moderate'),

	// Rating
	'acl_titania_rate'					=> array('lang' => 'Can rate items', 'cat' => 'actions'),
	'acl_titania_rate_reset'			=> array('lang' => 'Can reset ratings', 'cat' => 'moderate'),

	// Posts/Topics
	'acl_titania_topic'					=> array('lang' => 'Can create new topics', 'cat' => 'post'),
	'acl_titania_post'					=> array('lang' => 'Can create new posts', 'cat' => 'post'),
	'acl_titania_post_edit_own'			=> array('lang' => 'Can edit own posts', 'cat' => 'post'),
	'acl_titania_post_delete_own'		=> array('lang' => 'Can delete own posts', 'cat' => 'post'),
	'acl_titania_post_mod_own'			=> array('lang' => 'Can moderate own topics', 'cat' => 'post'),
	'acl_titania_post_mod'				=> array('lang' => 'Can moderate topics', 'cat' => 'moderate'),
	'acl_titania_post_attach'			=> array('lang' => 'Can attach files to posts', 'cat' => 'post'),
	'acl_titania_bbcode'				=> array('lang' => 'Can post BBCode', 'cat' => 'post'),
	'acl_titania_smilies'				=> array('lang' => 'Can post smilies', 'cat' => 'post'),

	// Modifications
	'acl_titania_mod_queue'				=> array('lang' => 'Can see Modifications Queue', 'cat' => 'queue'),
	'acl_titania_mod_validate'			=> array('lang' => 'Can validate Modifications', 'cat' => 'queue'),
	'acl_titania_mod_moderate'			=> array('lang' => 'Can moderate Modifications', 'cat' => 'moderate'),

	// Styles
	'acl_titania_style_queue'			=> array('lang' => 'Can see Styles Queue', 'cat' => 'queue'),
	'acl_titania_style_validate'		=> array('lang' => 'Can validate Styles', 'cat' => 'queue'),
	'acl_titania_style_moderate'		=> array('lang' => 'Can moderate Styles', 'cat' => 'moderate'),
));

?>