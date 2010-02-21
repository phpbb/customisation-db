<?php
/**
*
* authors [English]
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
'MARK_NO_PROGRESS' => 'Mark no Progress',
'MARK_IN_PROGRESS' => 'Mark in Progress',
'SUBMIT_TIME' => 'Submission Time',
	'VALIDATION_QUEUE'	=> 'Validation Queue',
	'VALIDATION_NOTES'	=> 'Validation Notes',
	'VALIDATION_MPV' => 'MPV Notes',
	'VALIDATION'	=> 'Validation',
	'VALIDATION_SUBMISSION' => 'Validation Submission',
	'RETEST_MPV'	=> 'Re-test MPV',
	'RETEST_AUTOMOD'	=> 'Re-test Automod',
	'REPACK' => 'Repack',
	'QUEUE_NEW' => 'New',
	'QUEUE_ATTENTION' => 'Attention',
	'QUEUE_REPACK' => 'Repack',
	'QUEUE_VALIDATING' => 'Validating',
	'QUEUE_TESTING' => 'Testing',
	'QUEUE_APPROVE' => 'Awaiting Approval',
	'QUEUE_DENY' => 'Awaiting Denial',
	'MOVE' => 'Move',
	'APPROVE' => 'Approve',
	'DENY' => 'Deny',
	'ALTER_NOTES' => 'Alter Validation Notes',
	'MOVE_QUEUE' => 'Move Queue',
	'MOVE_QUEUE_CONFIRM' => 'Select the new queue location and confirm.',
	'EDIT_VALIDATION_NOTES' => 'Edit Validation Notes',
	'APPROVE_QUEUE' => 'Approve',
	'APPROVE_QUEUE_CONFIRM' => 'Are you sure you want to <strong>approve</strong> this item?  The notes below will be sent along with the PM to the author notifying them.',
	'DENY_QUEUE' => 'Deny',
	'DENY_QUEUE_CONFIRM' => 'Are you sure you want to <strong>deny</strong> this item?  The notes below will be sent along with the PM to the author notifying them.',
	'MOD_VALIDATION' => '[phpBB MOD-Validation] %1$s %2$s',
	'STYLE_VALIDATION' => '[phpBB Style-Validation] %1$s %2$s',
	'MOD_VALIDATION_MESSAGE_APPROVE' => 'Thank you for submitting your modication to the phpBB.com modifications database. After careful inspection by the MOD Team, your MOD has been approved and released into our modifications database.

It is our hope that you will provide a basic level of support for this modification and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the MOD Team about your modification:[/b]
[quote]%s[/quote]

Sincerely,
phpBB MOD Team',
	'MOD_VALIDATION_MESSAGE_DENY' => 'Hello,

As you may know, all modifications submitted to the phpBB modification database must be validated and approved by members of the phpBB Team.

Upon validating your modification, the phpBB MOD Team regrets to inform you that we have had to deny your modification.

To correct the problem(s) with your modification, please following the below instructions:
[list=1][*]Make the necessary changes to correct any problems (listed below) that resulted in your modification being denied.
[*]Increase your version number.
[*]Test your MOD, the XML file and the installation of it.
[*]Re-upload your MOD to our modifications database.[/list]
Please ensure you tested your modification on the latest version of phpBB (see the [url=http://www.phpbb.com/downloads/]Downloads[/url] page) before you re-submit your modification.

If you feel this denial was not warranted, you can contact the MOD Validation Leader.

Here is a report on why your modification was denied:
[quote]%s[/quote]

Please refer to the following links before you reupload your modification:
[list]
[*][url=http://www.phpbb.com/mods/modx/]phpBB MODX standard[/url]
[*][b]Securing MODs:[/b]
[url=http://blog.phpbb.com/2009/02/12/injection-vulnerabilities/]Injection Vulnerability Prevention[/url]
[url=http://blog.phpbb.com/2009/09/10/how-not-to-use-request_var/]How (not) to use request_var[/url]
[/list]
For further reading, you may want to review the following:
[list][*][url=http://www.phpbb.com/mods/faq/]MODifications FAQ[/url]
[*][url=http://www.phpbb.com/kb/3.0/modifications/]phpBB3 MODifications Category in Knowledge Base[/url][/list]

For help with writing phpBB MODs, the following resources exists:
[list][*][url=http://www.phpbb.com/community/viewforum.php?f=71]Forum for MOD Authors\' Help[/url]
[*]IRC Support - [url=irc://irc.freenode.net/phpBB-coding]#phpBB-coding[/url] is registered on the FreeNode IRC network ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]If you wish to discuss anything in this PM please send a message using the discusion tab in the MOD database, My Modifications, manage for this MOD.[/b] If you feel this denial was not warranted, you can contact the MOD Validation Leader.

Thank you,
phpBB MOD Team',


	'STYLE_VALIDATION_MESSAGE_APPROVE' => 'Thank you for submitting your style to the phpBB.com styles database. After inspection by the Styles Team, your style has been approved and released into our styles database.

It is our hope that you will provide a basic level of support for this style and keep it updated as required for future releases of phpBB. We appreciate your work and contribution to the community.
[b]Notes from the Styles Team about your style:[/b]
[quote]%s[/quote]

Sincerely,',
	'STYLE_VALIDATION_MESSAGE_DENY' => 'Hello,

As you may know, all styles submitted to the phpBB styles database must be validated and approved by members of the phpBB Team.

Upon validating your style, the phpBB Styles Team regrets to inform you that we have had to deny your style. The reasons for this denial are outlined below:
[quote]%s[/quote]

If you wish to resubmit this style to the styles database, please ensure that you fix the issues identified and that it meets the [url=http://www.phpbb.com/community/viewtopic.php?t=988545]Styles Submission Policy[/url].

If you feel this denial is not warranted, you can contact the Styles Team Leader.

Sincerely,
The Styles Team',
));
