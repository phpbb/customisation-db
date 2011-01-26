<?php
/**
*
* @package Titania
* @copyright (c) 2011 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'BBCODE'							=> 'BBcode',
	'LONG_BBCODE'						=> 'Custom BBcode',
	'BBCODES'							=> 'BBcodes',
	'LONG_BBCODES'						=> 'Custom BBcodes',
	'BBCODE_UPDATE_PUBLIC'					=> '[b][color=darkred]Custom BBcode Updated to version %1$s
See first post for Download Link[/color][/b]',
	'BBCODE_UPDATE_PUBLIC_NOTES'			=> '

[b]Notes:[/b] %1$s',
	'BBCODE_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">By submitting this revision you agree to abide by the <a href="http://www.phpbb.com/bbcode/policies/">Custom BBcode database policies</a> and that your MOD conforms to and follows the W3 Web Standards.

You also agree and accept that this Custom BBcode\'s license and the license of any included components are compatible with the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> and that you also allow the re-distributibution of your Custom BBcode through this website indefinitely. For a list of available licenses and licenses compatible with the GNU GPLv2 please reference the <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">list of FSF approved software licenses</a>.</span>',
	'BBCODE_REPLY_PUBLIC'				=> '[b][color=darkred]Custom BBcode validated/released[/color][/b]',
	'BBCODE_REPLY_PUBLIC_NOTES'			=> '

[b]Notes:[/b] %s',
	'BBCODE_CREATE_PUBLIC'				=> '[b]Custom BBcode name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Custom BBcode description[/b]: %4$s
[b]Custom BBcode version[/b]: %5$s
[b]Tested on phpBB version[/b]: %11$s

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s Bytes

[b]Custom BBcode overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]The phpBB Team is not responsible nor required to provide support for this Custom BBcode. By adding this Custom BBcode, you acknowledge that the phpBB Support Team or any other phpBB Team may not be able to provide support.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Modification support[/b]&lt;--[/url][/size]',
	'BBCODE_VALIDATION'					=> '[phpBB Custom BBcode - Validation] %1$s %2$s',
	'BBCODE_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your Custom BBcode to the phpBB.com Customisation Database. After careful inspection your Custom BBcode has been approved and released into our Customisation Database.

It is our hope that you will provide a basic level of support for this BBcode and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the Team about your Custom BBcode:[/b]
[quote]%s[/quote]

Sincerely,
phpBB Teams',
	'BBCODE_VALIDATION_MESSAGE_DENY'		=> 'Hello,

As you may know all Custom BBCodes submitted to the phpBB Customisation Database must be validated and approved by members of the phpBB Team.

Upon validating your Custom BBcode the phpBB Team regrets to inform you that we have had to deny it.

To correct the problem(s) with your BBcode, please following the below instructions:
[list=1][*]Make the necessary changes to correct any problems (listed below) that resulted in your Custom BBcode being denied.
[*]Make sure it abides by W3 Validation
[*]Re-upload your BBcode to our Customisation Database.[/list]
Please ensure you tested your Custom BBcode on the latest version of phpBB (see the [url=http://www.phpbb.com/downloads/]Downloads[/url] page) before you re-submit your BBcode.

If you feel this denial was not warranted please contact the phpBB Teams.

Here is a report on why your Custom BBcode was denied:
[quote]%s[/quote]

Thank you,
phpBB Teams',
));