<?php
/**
*
* @package Titania
* @copyright (c) 2012 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
* Traducción hecha y revisada por nextgen <http://www.melvingarcia.com>
* Traductores anteriores angelismo y sof-teo
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
	'AUTHOR_BBCODES'					=> '%d BBCodes Personalizados',
	'AUTHOR_BBCODES_ONE'				=> '1 BBCode Personalizado',
	'BBCODE'							=> 'BBCode Personalizado',
	'BBCODES'							=> 'BBCodes Personalizados',
	'BBCODE_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">Al presentar esta revisión se compromete a cumplir y aceptar esta licencia de los BBcode\'s Personalizados, y la licencia de los componentes incluidos son compatibles con la <a href ="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> y que también permiten la re-distributibución de su BBcode Personalizado a través de este sitio web de forma indefinida.</span>',

	'BBCODE_VALIDATION'					=> '[phpBB BBcode Personalizado - Validación] %1$s %2$s',
	'BBCODE_VALIDATION_MESSAGE_APPROVE'	=> 'Gracias por enviar su BBCode Personalizado a la Base de descargas de phpBB.com. Después de una cuidadosa inspección de su BBCode Personalizado ha sido aprobado y enviado en nuestra Base de descargas.

It is our hope that you will provide a basic level of support for this BBcode and keep it updated with future releases of phpBB. We appreciate your work and contribution to the community. Authors like yourself make phpBB.com a better place for everyone.

[b]Notes from the Team about your Custom BBcode:[/b]
[quote]%s[/quote]

Sincerely,
phpBB Teams',
	'BBCODE_VALIDATION_MESSAGE_DENY'		=> 'Hola,

As you may know all Custom BBCodes submitted to the phpBB Customisation Database must be validated and approved by members of the phpBB Team.

Upon validating your Custom BBcode the phpBB Team regrets to inform you that we have had to deny it.

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
	'NO_BBCODE_USAGE'					=> 'Por favor escriba el uso del BBCode',
	'NO_HTML_REPLACE'					=> 'Por favor escriba el reemplazo HTML',
	'REVISION_HTML_REPLACE'				=> 'Reemplazo HTML',	
	'REVISION_BBCODE_USE'				=> 'Uso del BBCode',
	'REVISION_HELP_LINE'				=> 'Línea de ayuda',
	'REVISION_HTML_REPLACE_EXPLAIN'		=> 'Aquí se define por defecto el reemplazo HTML.',
	'REVISION_BBCODE_USE_EXPLAIN'		=> 'Aquí se define el uso del BBCode.',
	'REVISION_HELP_LINE_EXPLAIN'		=> 'Este campo contiene la ayuda del BBCode que muestra al pasar el mouse sobre el texto del BBCode',
));
