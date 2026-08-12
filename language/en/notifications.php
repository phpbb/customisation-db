<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
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

$lang = array_merge($lang, array(
	'NOTIFICATION_GROUP_TITANIA'				=> 'Customisation Database',

	'NOTIFICATION_TYPE_TITANIA_POSTED'			=> 'Someone posts in a subscribed topic or support area',
	'NOTIFICATION_TYPE_TITANIA_CONTRIBUTION'	=> 'A new version of a subscribed contribution is released',
	'NOTIFICATION_TYPE_TITANIA_QUEUE'			=> 'A watched validation queue changes',
	'NOTIFICATION_TYPE_TITANIA_ATTENTION'		=> 'Content is reported or awaits approval',

	'NOTIFICATION_TITANIA_REPLY'				=> '<strong>Reply</strong> from %1$s to the topic:',
	'NOTIFICATION_TITANIA_REPLY_CONTRIB'		=> '<strong>Reply</strong> from %1$s in the support area of %2$s to the topic:',
	'NOTIFICATION_TITANIA_TOPIC'				=> '<strong>New topic</strong> from %1$s:',
	'NOTIFICATION_TITANIA_TOPIC_CONTRIB'		=> '<strong>New topic</strong> from %1$s in the support area of %2$s:',
	'NOTIFICATION_TITANIA_CONTRIB_UPDATED'		=> '<strong>New version</strong> %1$s released for the contribution:',
	'NOTIFICATION_TITANIA_QUEUE_NEW'			=> '<strong>New queue item</strong>:',
	'NOTIFICATION_TITANIA_QUEUE_MOVE'			=> '<strong>Queue item moved</strong> to %1$s:',
	'NOTIFICATION_TITANIA_ATTENTION'			=> '<strong>Needs attention</strong>:',
	'NOTIFICATION_TITANIA_ATTENTION_REPORT'		=> '<strong>Reported</strong>:',
	'NOTIFICATION_TITANIA_ATTENTION_UNAPPROVED'	=> '<strong>Awaiting approval</strong>:',
));
