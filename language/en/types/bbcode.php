<?php
/**
*
* @package Titania
* @copyright (c) 2012 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
	'AUTHOR_BBCODES'					=> '%d Custom BBCodes',
	'AUTHOR_BBCODES_ONE'				=> '1 Custom BBCode',
	'BBCODE'							=> 'Custom BBCode',
	'BBCODES'							=> 'Custom BBCodes',
	'BBCODE_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">By submitting this revision you agree and accept that this Custom BBcode will be released under the <a href="http://creativecommons.org/licenses/by/3.0/">CC BY 3.0</a> license with attribution rights waived for public performance of the work. Attribution will still be required for distribution.</span>',

	'BBCODE_VALIDATION'					=> '[phpBB Custom BBcode - Validation] %1$s %2$s',
	'BBCODE_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your Custom BBcode to the phpBB.com Customisation Database. After careful inspection your Custom BBcode has been [b][color=#5c8503]approved[/color][/b] and released into our Customisation Database.

It is our hope that you will provide a basic level of support for this BBcode and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the Team about your Custom BBcode:[/b]
[quote]%s[/quote]

Sincerely,
phpBB Teams',
	'BBCODE_VALIDATION_MESSAGE_DENY'		=> 'Hello,

As you may know all Custom BBCodes submitted to the phpBB Customisation Database must be validated and approved by members of the phpBB Team.

Upon validating your Custom BBcode the phpBB Team regrets to inform you that we have had to [b][color=#A91F1F]deny[/color][/b] it.

To correct the problem(s) with your BBcode, please following the below instructions:
[list=1][*]Make the necessary changes to correct any problems (listed below) that resulted in your Custom BBcode being denied.
[*]Make sure it abides by W3 Validation
[*]Re-upload your BBcode to our Customisation Database.[/list]
Please ensure you tested your Custom BBcode on the latest version of phpBB (see the [url=http://www.phpbb.com/downloads/]Downloads[/url] page) before you re-submit your BBcode.

If you feel this denial was not warranted please contact the phpBB Teams via the validation dicussion topic.

Here is a report on why your Custom BBcode was denied:
[quote]%s[/quote]

Thank you,
phpBB Teams',
	'NO_BBCODE_USAGE'					=> 'Please enter a BBCode usage',
	'NO_HTML_REPLACE'					=> 'Please enter an HTML replacement',
	'PUBLIC_ATTR_WAIVED'				=> 'Public performance attribution has been waived.',
	'REVISION_HTML_REPLACE'				=> 'HTML Replacement',	
	'REVISION_BBCODE_USE'				=> 'BBCode Usage',
	'REVISION_HELP_LINE'				=> 'Help Line',
	'REVISION_HTML_REPLACE_EXPLAIN'		=> 'Here you define the default HTML replacement.',
	'REVISION_BBCODE_USE_EXPLAIN'		=> 'Here you define how to use the BBCode.',
	'REVISION_HELP_LINE_EXPLAIN'		=> 'This field contains the bbcode help that shows when mouse over text of the BBCode',
));
