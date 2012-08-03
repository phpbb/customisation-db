<?php
/**
*
* @package Titania

* @copyright (c) 2008 phpBB Customisation Database Team
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
	'AUTHOR_LANGUAGE_PACKS'						=> '%d paquete de traducciones',
	'AUTHOR_LANGUAGE_PACKS_ONE'					=> '1 paquete de traducción',
	'COULD_NOT_FIND_TRANSLATION_ROOT'			=> 'No pudimos localizar el directorio raiz de su paquete de idioma. Asegúrese que tiene un directorio que contenga <code>language/</code> y opcionalmente <code>styles/</code> en el nivel superior.',

	'MISSING_FILE'								=> 'El archivo <code>%s</code> no está presente en su paquete de idioma',
	'MISSING_KEYS'								=> 'No se hallaron las siguientes claves de idioma en <code>%1$s</code>:<br />%2$s',

	'PASSED_VALIDATION'							=> 'Su paquete de idioma ha pasado el proceso de validación que comprueba que no falten claves, archivos de licencia y el cual re empaqueta su traducción. Por favor, continúe.',

	'TRANSLATION'								=> 'Traducción',
	'TRANSLATION_VALIDATION'					=> '[phpBB Translation-Validation] %1$s %2$s',
	'TRANSLATION_VALIDATION_MESSAGE_APPROVE'	=> 'Gracias por enviar su Traducción a la Base de Datos de Personalización de phpBB.com. Después de inspeccionar cuidadosamente su traducción ha sido aprobada y publicada en nuestra Base de Descargas (Customisation Database).

Deseamos que pueda dar un nivel básico de soporte para esta traducción y que la mantenga actualizada con versiones futuras de phpBB. Apreciamos su trabajo y contribución a la comunidad. Los autores como usted hacen de phpBB.com un lugar mejor para todos.

[b]Notas del Equipo sobre su traducción:[/b]
[quote]%s[/quote]

Sinceramente,

El Gerente Internacional',
	'TRANSLATION_VALIDATION_MESSAGE_DENY'		=> 'Hola,

Como puede que sepa las traducciones enviadas a la Base de Datos de Personalización de phpBB deben ser validadas y aprobadas por miembros del Equipo de phpBB.

Con respecto a la validación de su traducción el Equipo de phpBB lamenta informarle de que hemos tenido que rechazarla.

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


El Gerente Internacional',
	'WRONG_FILE'								=> 'The file <code>%s</code> is not allowed.',
));