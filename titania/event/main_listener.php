<?php

/**
*
* @package Titania
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\titania\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'		=> 'add_permissions',
		);
	}

	public function add_permissions($event)
	{
		$event['categories'] = array_merge($event['categories'], array(
			'titania'			=> 'ACL_CAT_TITANIA',
			'titania_moderate'	=> 'ACL_CAT_TITANIA_MODERATE',
		));

		$event['permissions'] = array_merge($event['permissions'], array(
			// Common
			'u_titania_contrib_submit'	=> array('lang' => 'ACL_U_TITANIA_CONTRIB_SUBMIT', 'cat' => 'titania'),
			'u_titania_faq_create'		=> array('lang' => 'ACL_U_TITANIA_FAQ_CREATE', 'cat' => 'titania'),
			'u_titania_faq_edit'		=> array('lang' => 'ACL_U_TITANIA_FAQ_EDIT', 'cat' => 'titania'),
			'u_titania_faq_delete'		=> array('lang' => 'ACL_U_TITANIA_FAQ_DELETE', 'cat' => 'titania'),
			'u_titania_rate'			=> array('lang' => 'ACL_U_TITANIA_RATE', 'cat' => 'titania'),
			'u_titania_topic'			=> array('lang' => 'ACL_U_TITANIA_TOPIC', 'cat' => 'titania'),
			'u_titania_post'			=> array('lang' => 'ACL_U_TITANIA_POST', 'cat' => 'titania'),
			'u_titania_post_approved'	=> array('lang' => 'ACL_U_TITANIA_POST_APPROVED', 'cat' => 'titania'),
			'u_titania_post_edit_own'	=> array('lang' => 'ACL_U_TITANIA_POST_EDIT_OWN', 'cat' => 'titania'),
			'u_titania_post_delete_own'	=> array('lang' => 'ACL_U_TITANIA_POST_DELETE_OWN', 'cat' => 'titania'),
			'u_titania_post_mod_own'	=> array('lang' => 'ACL_U_TITANIA_POST_MOD_OWN', 'cat' => 'titania'),
			'u_titania_post_attach'		=> array('lang' => 'ACL_U_TITANIA_POST_ATTACH', 'cat' => 'titania'),
			'u_titania_bbcode'			=> array('lang' => 'ACL_U_TITANIA_BBCODE', 'cat' => 'titania'),
			'u_titania_smilies'			=> array('lang' => 'ACL_U_TITANIA_SMILIES', 'cat' => 'titania'),

			'u_titania_post_hard_delete' => array('lang' => 'ACL_U_TITANIA_POST_HARD_DELETE', 'cat' => 'titania'),

			// Moderation
			'u_titania_mod_author_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_AUTHOR_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_contrib_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_CONTRIB_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_faq_mod'		=> array('lang' => 'ACL_U_TITANIA_MOD_FAQ_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_rate_reset'	=> array('lang' => 'ACL_U_TITANIA_MOD_RATE_RESET', 'cat' => 'titania_moderate'),
			'u_titania_mod_post_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_POST_MOD', 'cat' => 'titania_moderate'),

			'u_titania_mod_style_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_MODERATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_clr'				=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_CLR', 'cat' => 'titania_moderate'),

			'u_titania_mod_modification_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_MODERATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_language_pack'		=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_LANGUAGE_PACK', 'cat' => 'titania_moderate'),

			'u_titania_mod_translation_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_converter_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_bbcode_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_queue'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_MODERATE', 'cat' => 'titania_moderate'),
	
			'u_titania_mod_bridge_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_queue'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_official_tool_moderate' => array('lang' => 'ACL_U_TITANIA_MOD_OFFICIAL_TOOL_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_extension_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_admin' => array('lang' => 'ACL_U_TITANIA_ADMIN', 'cat' => 'titania_moderate'),
		));
	}
}

