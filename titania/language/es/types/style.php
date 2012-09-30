<?php
/**
*
* @package Titania

* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'INTEGRATE_DEMO'					=> 'Integrar el demo de estilos',
	'NO_DEMO'							=> 'La contribución no tiene una demo para mostrar.',
	'NO_STYLES'							=> 'No hay estilos para mostrar.',
	'SELECT_STYLE'						=> 'Seleccionar estilo',
	'STYLE'								=> 'Estilo',
	'STYLES'							=> 'Estilos',
	'STYLE_CREATE_PUBLIC'				=> '[b]Nombre del estilo[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descripción del estilo[/b]: %4$s
[b]Versión del estilo[/b]: %5$s
[b]Probado en phpbb[/b]: %11$s

[b]Descarga[/b]: [url=%6$s]%7$s[/url]
[b]Tamaño del archivo:[/b] %8$s Bytes

[b]Página del información del estilo:[/b] [url=%9$s]Ver[/url]

[color=blue][b]El equipo de phpBB no es responsable ni tiene obligación de prestar apoyo a este estilo. Mediante la instalación de este estilo, usted reconoce que el equipo de soporte phpBB y el Equipo de estilos puede no dar soporte.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Soporte del estilo[/b]&lt;--[/url][/size]',
	'STYLE_DEMO_INSTALL'				=> 'Instalar en un foro demo',
	'STYLE_QUEUE_TOPIC'					=> '[b]Nombre del estilo[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descripción del estilo[/b]: %4$s
[b]Versión del estilo[/b]: %5$s

[b]Descarga[/b]: [url=%6$s]%7$s[/url]
[b]Tamaño del archivo:[/b] %8$s Bytes',
	'STYLE_REPLY_PUBLIC'				=> '[b][color=darkred]Estilo validado/aprobado[/color][/b]',
	'STYLE_REPLY_PUBLIC_NOTES'			=> '

[b]Notas: %s[/b]',
	'STYLE_UPDATE_PUBLIC'				=> '[b][color=darkred]Estilo actualizado a la versión %1$s
Ver enlace de descarga en el primer mensaje[/color][/b]',
	'STYLE_UPDATE_PUBLIC_NOTES'			=> '

[b]Notas:[/b] %1$s',
	'STYLE_UPLOAD_AGREEMENT'			=> '// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
// translators to re-order the output of data while ensuring it remains correct',
	'STYLE_VALIDATION'					=> '[phpBB validación de estilo] %1$s %2$s',
	'STYLE_VALIDATION_MESSAGE_APPROVE'	=> 'Gracias por enviar su estilo a la base de estilos. Después de la revisión por parte del equipo de estilos, ha sido aprobado y publicado en nuestra base  de estilos.

Es nuestra esperanza que va a proporcionar un nivel básico de apoyo a este estilo y mantenerlo actualizado como sea necesario para las futuras versiones de phpBB. Apreciamos su trabajo y contribución a la comunidad.
[b]Notas del equipo de Estilos sobre su estilo:[/b]
[quote]%s[/quote]

Atentamente,',
	'STYLE_VALIDATION_MESSAGE_DENY'		=> 'Hola,

Como ustedes saben todos los estilos presentados a la base de estilos de phpbbb deben ser validados y aprobads por los miembros del equipo de phpBB.

Tras validar su estilo El Equipo de Estilos lamenta informarle de que hemos tenido que rechazar su estilo. Las razones de este rechazo se resumen a continuación:
[quote]%s[/quote]



Si desea volver a presentar este estilo a la base de estilos por favor asegúrese de solucionar los problemas identificados y que cumple con las [url=http://www.phpbb.com/community/viewtopic.php?t=988545]Política de presentación de estilos[/url].

Si cree que esta negativa no está justificada, póngase en contacto con el Líder del Equipo de Estilos

Atentamente,
El Equipo de Estilos',
));
