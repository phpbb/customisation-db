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
	'MODIFICATION'						=> 'Modificación',
	'MODIFICATIONS'						=> 'Modificaciones',
	'MOD_CREATE_PUBLIC'					=> '[b]Nombre del MOD[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descripción del MOD[/b]: %4$s
[b]Versión del MOD[/b]: %5$s
[b]Probado en phpBB[/b]: %11$s

[b]Descarga[/b]: [url=%6$s]%7$s[/url]
[b]Tamaño del archivo:[/b] %8$s Bytes

[b]Página del MOD:[/b] [url=%9$s]Ver[/url]

[color=blue][b]El Equipo de phpBB no es responsable ni esta obligado a prestar apoyo a este mod. Al instalar este MOD, usted reconoce que el equipo de Soporte phpBB  o equipo de MOD puede o no dar soporte.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Soporte[/b]&lt;--[/url][/size]',
	'MOD_QUEUE_TOPIC'					=> '[b]Nombre[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descripción del MOD[/b]: %4$s
[b]Versión del MOD[/b]: %5$s

[b]Descarga[/b]: [url=%6$s]%7$s[/url]
[b]Tamaño del archivo:[/b] %8$s Bytes',
	'MOD_REPLY_PUBLIC'					=> '[b][color=darkred]MOD validado/aprobado[/color][/b]',
	'MOD_REPLY_PUBLIC_NOTES'			=> '

[b]Notas:[/b] %s',
	'MOD_UPDATE_PUBLIC'					=> '[b][color=darkred]MOD actualizado%1$s
Ver enlace a la descarga en el primer mensaje[/color][/b]',
	'MOD_UPDATE_PUBLIC_NOTES'			=> '

[b]Notas:[/b] %1$s',
	'MOD_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;"> Al presentar esta revisión está de acuerdo en cumplir con las <a href="http://www.phpbb.com/mods/policies/">Politicas de la base de descargas</a>  y que el MOD cumple y sigue las <a href="http://code.phpbb.com/svn/phpbb/branches/phpBB-3_0_0/phpBB/docs/coding-guidelines.html">instrucciones de codificación de phpBB3</a>.

También están de acuerdo y aceptar que esta modificación \'s de licencia y la licencia de cualquier componentes incluidos son compatibles con el <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> y que también permite la redistribución de sus MODs a través de este sitio web de forma indefinida. Para obtener una lista de las licencias disponibles y las licencias compatibles con la GNU GPLv2 por favor, hacer referencia al <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">lista de la FSF aprobó licencias de software</a>.</span>',
	'MOD_VALIDATION'					=> '[Validación de MOD] %1$s %2$s',
	'MOD_VALIDATION_MESSAGE_APPROVE'	=> 'Gracias por enviar su mod a la base de phpBB. Después de una cuidadosa inspección por el equipo de mods su mod ha sido aprobado y publicado en nuestra base de modificaciones.

Es nuestra esperanza que va a proporcionar un nivel básico de apoyo a este mod y lo mantenga actualizado con las futuras versiones de phpBB. Apreciamos su trabajo y contribución a la comunidad de autores como tu para que  el sitio sea un lugar mejor para todos.

[b]Notas del Equipo de MOD sobre su mod:[/b]
[quote]%s[/quote]

Atentamente,
El Equipo de MODificaciones',
	'MOD_VALIDATION_MESSAGE_DENY'		=> 'Hola,

Como ustedes saben todas las mods presentadas a la  base de mods deben ser validados y aprobados por los miembros del equipo de phpBB.

Tras validar la modificación el equipo de MOD PHPBB lamenta informarle de que hemos tenido que rechazar su modificación.

Para corregir el problema (s) con la modificación, por favor siga las siguientes instrucciones:
[list=1][*]Haga los cambios necesarios para corregir cualquier problema (que se enumeran a continuación) que dio lugar a la modificación que se negó.
[*]Pon a prueba tu MOD, el archivo XML y la instalación de la misma.
[*]Volver a subir el MOD a nuestra base de datos de las modificaciones.[/list]
Por favor, asegúrese de probar su modificación en la última versión de phpBB (ver el [url = http://www.phpbb.com/downloads/] Descargar [/ url] página) antes de volver a presentar su modificación.

Aquí está un informe sobre por qué su modificación se negó:
[quote]%s[/quote]

Por favor, consulte los siguientes enlaces antes de reenviar su modificación:
[list]
[*][url=http://www.phpbb.com/mods/modx/]phpBB MODX standard[/url]
[*][b]Securing MODs:[/b]
[url=http://blog.phpbb.com/2009/02/12/injection-vulnerabilities/]Prevención de inyección de vulnerabilidades[/url
[url=http://blog.phpbb.com/2009/09/10/how-not-to-use-request_var/]Cómo (no) para utilizar request_var[/url]
[/list]

Para seguir leyendo, es posible que desee revisar lo siguiente:
[list][*][url=http://www.phpbb.com/mods/faq/]MODifications FAQ[/url]
[*][url=http://www.phpbb.com/kb/3.0/modifications/]phpBB3 Modificaciones en la categoría de Knowledge Base [/url][/list]

Para obtener ayuda con la escritura de MODs de phpBB, están estos recursos
[list][*][url=http://www.phpbb.com/community/viewforum.php?f=71]Foro de ayuda para Autores MOD[/url]
[*]IRC Support - [url=irc://irc.freenode.net/phpBB-coding]#phpBB-coding[/url]es registrado en la red IRC FreeNode ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]Si desea hablar de cualquier cosa por favor enviar un MP  utilizando la ficha discusión en la base de mods, Mi Modificaciones: gestión de este MOD.[/b] Si cree que esta negativa no estaba justificada por favor ponte en contacto con el Líder del Equipo de MODificaciones

Si cree que esta negativa no estaba justificada por favor ponte en contacto con el Líder del Equipo de MODificaciones.
If you have any queries and further discussion please use the Queue Discussion Topic.

Gracias,
El Equipo de MODificaciones',
));
