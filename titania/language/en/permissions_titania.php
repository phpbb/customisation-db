<?php
/**
* acp_permissions_titania (Titania Permission Set) [English]
*
* @package language
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

$lang = array_merge($lang, array(
	'ACL_CAT_TITANIA'			=> 'Titania',
	'ACL_CAT_TITANIA_MODERATE'	=> 'Titania Moderate',
));


$lang = array_merge($lang, array(
	// Common
	'ACL_U_TITANIA_CONTRIB_SUBMIT'		=> 'Can submit contributions',
	'ACL_U_TITANIA_FAQ_CREATE'			=> 'Can create FAQ entries (for own Contributions)',
	'ACL_U_TITANIA_FAQ_EDIT'			=> 'Can edit FAQ entries (for own Contributions)',
	'ACL_U_TITANIA_FAQ_DELETE'			=> 'Can delete FAQ entries (for own Contributions)',
	'ACL_U_TITANIA_RATE'				=> 'Can rate items',
	'ACL_U_TITANIA_TOPIC'				=> 'Can create new topics',
	'ACL_U_TITANIA_POST'				=> 'Can create new posts',
	'ACL_U_TITANIA_POST_APPROVED'		=> 'Can post <strong>without</strong> approval',
	'ACL_U_TITANIA_POST_EDIT_OWN'		=> 'Can edit own posts',
	'ACL_U_TITANIA_POST_DELETE_OWN'		=> 'Can delete own posts',
	'ACL_U_TITANIA_POST_MOD_OWN'		=> 'Can moderate own contribution topics',
	'ACL_U_TITANIA_POST_ATTACH'			=> 'Can attach files to posts',
	'ACL_U_TITANIA_BBCODE'				=> 'Can post BBCode',
	'ACL_U_TITANIA_SMILIES'				=> 'Can post smilies',

	'ACL_U_TITANIA_POST_HARD_DELETE'	=> 'Can <strong>hard</strong> delete posts and topics (posts and topics that the user is able to otherwise delete).',

	// Moderation
	'ACL_U_TITANIA_MOD_AUTHOR_MOD'			=> 'Can moderate author profiles',
	'ACL_U_TITANIA_MOD_CONTRIB_MOD'			=> 'Can moderate (all) contributions',
	'ACL_U_TITANIA_MOD_FAQ_MOD'				=> 'Can moderate FAQ entries',
	'ACL_U_TITANIA_MOD_RATE_RESET'			=> 'Can reset ratings',
	'ACL_U_TITANIA_MOD_POST_MOD'			=> 'Can moderate topics',

	'ACL_U_TITANIA_MOD_STYLE_QUEUE_DISCUSSION'			=> 'Can see Styles Queue Discussion',
	'ACL_U_TITANIA_MOD_STYLE_QUEUE'						=> 'Can see Styles Queue',
	'ACL_U_TITANIA_MOD_STYLE_VALIDATE'					=> 'Can validate Styles',
	'ACL_U_TITANIA_MOD_STYLE_MODERATE'					=> 'Can moderate Styles',
	'ACL_U_TITANIA_MOD_STYLE_CLR'                       => 'Can edit ColorizeIt defaults',

	'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE_DISCUSSION'	=> 'Can see Modifications Queue Discussion',
	'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE'				=> 'Can see Modifications Queue',
	'ACL_U_TITANIA_MOD_MODIFICATION_VALIDATE'			=> 'Can validate Modifications',
	'ACL_U_TITANIA_MOD_MODIFICATION_MODERATE'			=> 'Can moderate Modifications',

	'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE_DISCUSSION'		=> 'Can see Translation Queue Discussion',
	'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE'					=> 'Can see Translation Queue',
	'ACL_U_TITANIA_MOD_TRANSLATION_VALIDATE'				=> 'Can validate Translations',
	'ACL_U_TITANIA_MOD_TRANSLATION_MODERATE'				=> 'Can moderate Translations',

	'ACL_U_TITANIA_MOD_CONVERTER_QUEUE_DISCUSSION'		=> 'Can see Converter Queue Discussion',
	'ACL_U_TITANIA_MOD_CONVERTER_QUEUE'					=> 'Can see Converter Queue',
	'ACL_U_TITANIA_MOD_CONVERTER_VALIDATE'				=> 'Can validate Converters',
	'ACL_U_TITANIA_MOD_CONVERTER_MODERATE'				=> 'Can moderate Converters',

	'ACL_U_TITANIA_MOD_BBCODE_QUEUE_DISCUSSION'			=> 'Can See Custom BBcode Queue Discussion',
	'ACL_U_TITANIA_MOD_BBCODE_QUEUE'					=> 'Can See Custom BBcode Queue',
	'ACL_U_TITANIA_MOD_BBCODE_VALIDATE'					=> 'Can Validate Custom BBcodes',
	'ACL_U_TITANIA_MOD_BBCODE_MODERATE'					=> 'Can Moderate Custom BBcodes',
	
	'ACL_U_TITANIA_MOD_BRIDGE_QUEUE_DISCUSSION'			=> 'Can see Bridge Queue Discussion',
	'ACL_U_TITANIA_MOD_BRIDGE_QUEUE'					=> 'Can see Bridge Queue',
	'ACL_U_TITANIA_MOD_BRIDGE_VALIDATE'					=> 'Can validate Bridges',
	'ACL_U_TITANIA_MOD_BRIDGE_MODERATE'					=> 'Can moderate Bridges',

	'ACL_U_TITANIA_MOD_OFFICIAL_TOOL_MODERATE'			=> 'Can submit/moderate Official Tools',

	'ACL_U_TITANIA_ADMIN'			=> 'Can <strong>administrate</strong> Titania',
));
