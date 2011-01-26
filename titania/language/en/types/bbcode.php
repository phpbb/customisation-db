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
	'BBCODE_VALIDATION'					=> '[phpBB Custom BBcode-Validation] %1$s %2$s',
	'BBCODE_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your Custom BBcode to the phpBB.com Customisation Database. After careful inspection your Custom BBcode has been approved and released into our Customisation Database.

It is our hope that you will provide a basic level of support for this BBcode and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the Team about your convertor:[/b]
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