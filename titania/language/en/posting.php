<?php
/**
*
* posting [English]
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_TITANIA'))
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
	'UNDELETE_FILE'		=> 'Cancel Delete',
	'FILE_DELETED'		=> 'This file will be deleted when you submit',
	'UNDELETE_POST'		=> 'Undelete Post',
	'UNDELETE_POST_CONFIRM'	=> 'Are you sure you want to undelete this post?',
	'REPORT_POST'		=> 'Report Post',
	'REPORT_POST_CONFIRM'	=> 'Are you sure you want to report this post?',
	'ACCESS'			=> 'Access Level',
	'ACCESS_AUTHORS'	=> 'Authors Access',
	'ACCESS_PUBLIC'		=> 'Public Access',
	'ACCESS_TEAMS'		=> 'Teams Access',
	'ATTACH'			=> 'Attach',

	'STICKIES'			=> 'Stickies',
	'STICKY_TOPIC'		=> 'Sticky Topic',

	'QUEUE_DISCUSSION_TOPIC_TITLE'		=> 'Validation Discussion - %s',
	'QUEUE_DISCUSSION_TOPIC_MESSAGE'	=> 'This topic is for validation discussion between the contribution authors and validators.

Anything posted in this topic will be read by those validating your contribution so please post here instead of using private messages to validators.

Validation staff may also post questions to the authors here so please reply with helpful information for them as it may be required to proceed with the validation procedure.

Note that by default this topic is private between authors and validators and cannot be seen by the public.',

	'CREATE_PUBLIC_TOPIC'				=> '[b]Modification name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Modification description[/b]: %4$s
[b]Modification version[/b]: %5$s
[b]Tested on phpBB version[/b]: See below

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s Bytes

[b]Modification overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]Support for this modification needs to be asked within this topic. The phpBB Team is not responsible or required to give anyone support for this modification. By installing this MOD, you acknowledge that the phpBB Support Team or phpBB MODifications Team may not be able to provide support.

This MOD has only been tested by the phpBB MOD Team with the phpBB version listed in the topic.  It may not work in any other versions of phpBB.[/b][/color]',

	'UPDATE_PUBLIC_TOPIC' => '[color=darkred][b]MOD Updated to version %s
See first post for Download Link[/b][/color]',
));
