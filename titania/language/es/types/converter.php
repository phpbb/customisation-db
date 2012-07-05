<?php
/**
*
* @package Titania
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
    'AUTHOR_CONVERTORS'						=> '%d Conversores',
	'AUTHOR_CONVERTORS_ONE'					=> '1 Conversor',
	'CONVERTER'								=> 'Conversor',
	'CONVERTERS'							=> 'Conversores',
	'CONVERTER_VALIDATION'					=> '[Validación de Conversor] %1$s %2$s',
	'CONVERTER_VALIDATION_MESSAGE_APPROVE'	=> 'Gracias por enviarnos su conversor a la base de mods para phpBB. Después de la inspección cuidadosa de su conversor ha sido aprobado y publicado en nuestra base de mods.

Es nuestra esperanza que va a proporcionar un nivel básico de apoyo a este conversor y mantenerlo actualizado con las futuras versiones de phpBB. Apreciamos su trabajo y contribución a la comunidad. Autores como tu mismo para el sitio sea un lugar mejor para todos.

[b]Notas del equipo acerca de su conversor:[/b]

[quote]%s[/quote]

Atentamente,
Los Equipos de phpBB',
	'CONVERTER_VALIDATION_MESSAGE_DENY'		=> 'Hola,

Como ustedes saben todos los convertidores presentado a la base de mods debe ser validados y aprobados por los miembros del equipo de phpBB.

Tras validar su conversor del Equipo de phpBB lamenta informarle de que no ha sido aprobado

Para corregir el problema (s) con su conversor, por favor siga las siguientes instrucciones:
[list=1][*]Haga los cambios necesarios para corregir cualquier problema (que se enumeran a continuación) que resultó en su conversor de ser negado.
[*]Vuelva a subir su conversor a nuestra base de mods.[/list]
Por favor, asegúrese de probar el conversor en la última versión de phpBB (ver el [url=http://www.phpbb.com/downloads/]Descargar[/url] página) antes de volver a presentar su conversor.

Si cree que esta negativa no estaba justificada por favor ponte en contacto con el Líder de Desarrollo.

He aquí un informe sobre por qué se le negó su conversor:
[quote]%s[/quote]

Gracias,
Los Equipos de phpBB',
));
