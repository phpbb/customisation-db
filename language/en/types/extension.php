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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'AUTHOR_EXTENSIONS'					=> '%d Extensions',
	'AUTHOR_EXTENSIONS_ONE'				=> '1 Extension',
	'COULD_NOT_FIND_EXT_ROOT'			=> 'Could not find the main directory. Please ensure that there is a composer.json file in the zip package.',
	'EXTENSION'							=> 'Extension',
	'EXTENSIONS'						=> 'Extensions',
	'EXTENSION_CREATE_PUBLIC'			=> '[b]Extension name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Extension description[/b]: %4$s
[b]Extension version[/b]: %5$s
[b]Tested on phpBB version[/b]: %11$s

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s

[b]Extension overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]Except where otherwise noted, the phpBB Team is not responsible nor required to provide support for this extension. By installing this extension, you acknowledge that the phpBB Support Team or phpBB Extensions Team may not be able to provide support.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Extension support[/b]&lt;--[/url][/size]',
	'EXTENSION_QUEUE_TOPIC'				=> '[b]Extension name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Extension description[/b]: %4$s
[b]Extension version[/b]: %5$s

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s',
	'EXTENSION_REPLY_PUBLIC'			=> '[b][color=darkred]Extension validated/released[/color][/b]',
	'EXTENSION_REPLY_PUBLIC_NOTES'		=> '

[b]Notes:[/b] %s',
	'EXTENSION_UPDATE_PUBLIC'			=> '[b][color=darkred]Extension Updated to version %1$s
See first post for Download Link[/color][/b]',
	'EXTENSION_UPDATE_PUBLIC_NOTES'		=> '

[b]Notes:[/b] %1$s',
	'EXTENSION_UPLOAD_AGREEMENT'		=> '<span style="font-size: 1.5em;">By submitting this revision you agree to abide by the <a href="https://www.phpbb.com/extensions/rules-and-policies/">Extensions database policies</a> and that your extension conforms to and follows the <a href="https://area51.phpbb.com/docs/32x/coding-guidelines.html">phpBB 3.2 Coding Guidelines</a>.

You also agree and accept that this extension is to be released under the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> and that the license of any included components are compatible with the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> and that you also allow the re-distribution of your extension through this website indefinitely. For a list of available licenses and licenses compatible with the GNU GPLv2 please reference the <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">list of FSF approved software licenses</a>.</span>',
	'EXTENSION_VALIDATION'				=> '[phpBB Extension-Validation] %1$s %2$s',
	'EXTENSION_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your extension to the phpBB.com extensions database. After careful inspection by the Extensions Team, your extension has been [b][color=#5c8503]approved[/color][/b] and released into our extensions database.

It is our hope that you will provide a basic level of support for this extension and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the Extensions Team about your extension:[/b]
[quote]%s[/quote]

Sincerely,
phpBB Extensions Team',
	'EXTENSION_VALIDATION_MESSAGE_DENY'		=> 'Hello,

As you may know all extensions submitted to the phpBB extensions database must be validated and approved by members of the phpBB Team.

Upon validating your extension the phpBB Extensions Team regrets to inform you that we have had to [b][color=#A91F1F]deny[/color][/b] your extension.

To correct the problem(s) with your extension, please following the below instructions:
[list=1][*]Make the necessary changes to correct any problems (listed below) that resulted in your extension being denied.
[*]Re-upload your extension to our extensions database.[/list]
Please ensure you tested your extension on the latest version of phpBB (see the [url=https://www.phpbb.com/downloads/]Downloads[/url] page) before you re-submit your extension.

Here is a report on why your extension was denied:
[quote]%s[/quote]

Please refer to the following links before you re-upload your extension:
[list]
[*][url=https://www.phpbb.com/extensions/rules-and-policies/]Extensions Rules and Policies[/url]
[*][url=https://www.phpbb.com/extensions/writing/]Writing Extensions[/url]
[*][url=https://blog.phpbb.com/tag/security/]Security[/url]
[/list]

For further reading, you may want to review the following:
[list]
[*][url=https://wiki.phpbb.com/Category:Extensions]Extensions Development Wiki[/url]
[*][url=http://php.net/docs.php]PHP Documentation[/url]
[/list]

For help with writing phpBB extensions, the following resources exist:
[list][*][url=https://www.phpbb.com/community/viewforum.php?f=461]Extension Writers Discussion forum[/url]
[*]IRC Support - [url=irc://irc.freenode.net/phpBB-coding]#phpBB-coding[/url] is registered on the FreeNode IRC network ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]If you wish to discuss anything in this PM please use the “Validation Discussion“ sticky topic located in your extension’s Discussion/Support tab.[/b]

If you feel this denial was not warranted please contact the Extension Validation Leader.
If you have any queries and further discussion please use the Queue Discussion Topic.

Thank you,
phpBB Extensions Team',

	'INVALID_EXT_NAME'		=> 'Invalid value for <em>name</em> property in composer.json.
									Please refer to the <a href="https://www.phpbb.com/extensions/rules-and-policies/validation-policy/#packaging-extensions">Extension Validation Policy</a> for the valid format.',

	'TEST_ACCOUNT'			=> 'Test account',
	'TEST_ACCOUNT_EXPLAIN'	=> 'Do we need any information (Like an API key, username, password to login to a third party webservice) for testing your extension?
									Please provide all required information to test your extension. <strong>If we are unable to test your extension because of missing information, we will deny your extension.</strong>',
));
