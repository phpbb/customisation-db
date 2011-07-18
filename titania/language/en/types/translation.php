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
	'AUTHOR_LANGUAGE_PACKS'						=> '%d Translation Packs',
	'AUTHOR_LANGUAGE_PACKS_ONE'					=> '1 Translation Pack',
	'COULD_NOT_FIND_TRANSLATION_ROOT'			=> 'We couldn\'t locate the root directory of your language pack. Make sure you have a directory containing <code>language/</code> and optionally <code>styles/</code> in the top level.',

	'MISSING_FILE'								=> 'The file <code>%s</code> is missing in your language pack',
	'MISSING_KEYS'								=> 'You are missing the following language keys in <code>%1$s</code>:<br />%2$s',

	'PASSED_VALIDATION'							=> 'Your language pack has passed the validation process which checks for missing keys, license files and which repackages your translation, please continue.',

	'TRANSLATION'								=> 'Translation',
	'TRANSLATION_VALIDATION'					=> '[phpBB Translation-Validation] %1$s %2$s',
	'TRANSLATION_VALIDATION_MESSAGE_APPROVE'	=> 'Thank you for submitting your Translation to the phpBB.com Customisation Database. After careful inspection your translation has been approved and released into our Customisation Database.

It is our hope that you will provide a basic level of support for this translation and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the Team about your translation:[/b]
[quote]%s[/quote]

Sincerely,
phpBB Teams',
	'TRANSLATION_VALIDATION_MESSAGE_DENY'		=> 'Hello,

As you may know all translations submitted to the phpBB Customisation Database must be validated and approved by members of the phpBB Team.

Upon validating your translation the phpBB Team regrets to inform you that we have had to deny it.

To correct the problem(s) with your translation, please following the below instructions:
[list=1][*]Make the necessary changes to correct any problems (listed below) that resulted in your translation being denied.
[*]Re-upload your translation to our Customisation Database.[/list]
Please ensure you tested your translation on the latest version of phpBB (see the [url=http://www.phpbb.com/downloads/]Downloads[/url] page) before you re-submit your translation.

If you feel this denial was not warranted please contact the Translations Manager.

Here is a report on why your translation was denied:
[quote]%s[/quote]

Thank you,
phpBB Teams',
));
