<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
	'STYLE'								=> 'Style',
	'STYLES'							=> 'Styles',
	'STYLE_CREATE_PUBLIC'				=> '[b]Style name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Style description[/b]: %4$s
[b]Style version[/b]: %5$s
[b]Tested on phpBB version[/b]: %11$s

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s Bytes

[b]Style overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]The phpBB Team is not responsible nor required to provide support for this Style. By installing this Style, you acknowledge that the phpBB Support Team or phpBB Styles Team may not be able to provide support.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Style support[/b]&lt;--[/url][/size]',
	'STYLE_DEMO_INSTALL'				=> 'Install on style demo board',
	'STYLE_QUEUE_TOPIC'					=> '[b]Style name[/b]: %1$s
[b]Author:[/b] [url=%2$s]%3$s[/url]
[b]Style description[/b]: %4$s
[b]Style version[/b]: %5$s

[b]Download file[/b]: [url=%6$s]%7$s[/url]
[b]File size:[/b] %8$s Bytes',
	'STYLE_REPLY_PUBLIC'				=> '[b][color=darkred]Style validated/released[/color][/b]',
	'STYLE_REPLY_PUBLIC_NOTES'			=> '

[b]Notes: %s[/b]',
	'STYLE_UPDATE_PUBLIC'				=> '[b][color=darkred]Style Updated to version %1$s
See first post for Download Link[/color][/b]',
	'STYLE_UPDATE_PUBLIC_NOTES'			=> '

[b]Notes:[/b] %1$s',
	'STYLE_UPLOAD_AGREEMENT'			=> '// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
// translators to re-order the output of data while ensuring it remains correct',
	'STYLE_VALIDATION'					=> '[phpBB Style-Validation] %1$s %2$s',
	'STYLE_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your style to the phpBB.com styles database. After inspection by the Styles Team your style has been approved and released into our styles database.

It is our hope that you will provide a basic level of support for this style and keep it updated as required for future releases of phpBB. We appreciate your work and contribution to the community.
[b]Notes from the Styles Team about your style:[/b]
[quote]%s[/quote]

Sincerely,',
	'STYLE_VALIDATION_MESSAGE_DENY'		=> 'Hello,

As you may know all styles submitted to the phpBB styles database must be validated and approved by members of the phpBB Team.

Upon validating your style the phpBB Styles Team regrets to inform you that we have had to deny your style. The reasons for this denial are outlined below:
[quote]%s[/quote]

If you wish to resubmit this style to the styles database please ensure that you fix the issues identified and that it meets the [url=http://www.phpbb.com/community/viewtopic.php?t=988545]Styles Submission Policy[/url].

If you feel this denial is not warranted please contact the Styles Team Leader.

Sincerely,
The Styles Team',
));
