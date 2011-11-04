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
	'AUTHOR_LANGUAGE_PACKS'						=> '%d Language Packs',
	'AUTHOR_LANGUAGE_PACKS_ONE'					=> '1 Language Pack',
	'COULD_NOT_FIND_TRANSLATION_ROOT'			=> 'We couldn’t locate the root directory of your language pack.  Make sure you have a directory containing <code>language/</code> and <code>styles/</code> in the top level.',

	'MISSING_FILE'								=> 'The file <code>%s</code> is missing in your language pack.',
	'MISSING_KEYS'								=> 'You are missing the following language key(s) in <code>%1$s</code>:<br />%2$s',

	'PASSED_VALIDATION'							=> 'Your language pack has been repacked and has passed the validation process which checks for missing keys, structure, additionnal files and license.  Please continue.',

	'TRANSLATION'								=> 'Language Pack',
	'TRANSLATION_VALIDATION'					=> '[phpBB Language Pack-Validation] %1$s %2$s',
	'TRANSLATION_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your language pack to the phpBB.com Customisation Database. After careful inspection, your language pack has been approved and released into our Customisation Database.

It is my hope that you will provide a basic level of support for this language pack and keep it updated with future releases of phpBB. I appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the International Manager about your language pack:[/b]
[quote]%s[/quote]

Best regards,

The International Manager',
	'TRANSLATION_VALIDATION_MESSAGE_DENY'		=> 'Hello,

As you may know, all translations submitted to the phpBB Customisation Database must be validated and approved by the International Manager.

Upon validating your language pack, I regrets to inform you that I have had to deny it.

To correct the problem(s) with your language pack, please following the below instructions:
[list=1][*]Make the necessary changes to correct any problems (listed below) that resulted in your language pack being denied.
[*]Please ensure your language pack is up-to-date with the latest version of phpBB (see the [url=http://www.phpbb.com/downloads/]Downloads[/url] page).
[*]Please ensure that you comply with our [url=http://www.phpbb.com/community/viewtopic.php?f=79&t=2117453]Important Read Me![/url] and our [url=http://www.phpbb.com/community/viewtopic.php?f=79&t=2125191]Language Packs Submission Policy[/url].
[*]Fix and re-upload your language pack to our Customisation Database.[/list]

Here is a report on why your language pack was denied:
[quote]%s[/quote]

If you feel this denial was not warranted please contact me.
If you have any queries and further discussion please use the Queue Discussion Topic.

Best regards,

The International Manager',
	'WRONG_FILE'								=> 'The file <code>%s</code> is not allowed.',
));
