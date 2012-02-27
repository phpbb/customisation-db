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
	'STYLE'								=> 'Stil',
	'STYLES'							=> 'Stiluri',
	'STYLE_CREATE_PUBLIC'				=> '[b]Nume stil[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descriere stil[/b]: %4$s
[b]Versiune stil[/b]: %5$s
[b]Testat cu versiunea phpBB[/b]: %11$s

[b]Descărcare fişier[/b]: [url=%6$s]%7$s[/url]
[b]Dimensiune fişier:[/b] %8$s Bytes

[b]Pagina de prezentare a stilului:[/b] [url=%9$s]Vizualizare[/url]

[color=blue][b]Echipa phpBB nu este responsabilă sau nu i se va cere să furnizeze suport pentru acest stil. Prin instalarea acestui stil, acceptaţi că echipa de suport phpBB sau echipa de stiluri phpBB nu ar putea fi capabilă să vă furnizeze suport.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Suport stil[/b]&lt;--[/url][/size]',
	'STYLE_DEMO_INSTALL'				=> 'Instalare pe forumul de testare stiluri',
	'STYLE_QUEUE_TOPIC'					=> '[b]Nume stil[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descriere stil[/b]: %4$s
[b]Versiune stil[/b]: %5$s

[b]Descărcare fişier[/b]: [url=%6$s]%7$s[/url]
[b]Dimensiune fişier:[/b] %8$s Bytes',
	'STYLE_REPLY_PUBLIC'				=> '[b][color=darkred]Stil validat/lansat[/color][/b]',
	'STYLE_REPLY_PUBLIC_NOTES'			=> '

[b]Note: %s[/b]',
	'STYLE_UPDATE_PUBLIC'				=> '[b][color=darkred]Stil actualizat la versiunea %1$s
Verificaţi primul mesaj pentru legătura de descărcare[/color][/b]',
	'STYLE_UPDATE_PUBLIC_NOTES'			=> '

[b]Note:[/b] %1$s',
	'STYLE_UPLOAD_AGREEMENT'			=> '// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
// translators to re-order the output of data while ensuring it remains correct',
	'STYLE_VALIDATION'					=> '[Stil phpBB - Validare] %1$s %2$s',
	'STYLE_VALIDATION_MESSAGE_APPROVE'	=> 'Vă mulţumim că aţi trimis stilul în baza de date a personalizării phpBB.com. După o verificare atentă, stilul dumneavoastră a fost aprobat şi va fi inclus în baza noastră de date pentru personalizare.

Sperăm că veţi furniza un suport minim pentru acest stil şi-l veţi actualiza odata cu viitoarele versiuni ale phpBB-ului. Vă apreciem munca şi contribuţia la comunitate. Autorii ca dumneavoastră fac phpBB.com un loc mai bun pentu toată lumea.

[b]Note de la echipa de stiluri relativ la stilul dumneavoastră:[/b]
[quote]%s[/quote]

Cu sinceritate,',
	'STYLE_VALIDATION_MESSAGE_DENY'		=> 'Salut,

După cum ştiţi toate stilurile trimise în baza de date a personalizării phpBB trebuie validate şi aprobate de către membrii echipei phpBB.

În urma procesului de validare a stilului  dumneavoastră, echipa phpBB regretă să vă informeze că a trebuit să-l respingă. Motivele pentru această respingere sunt subliniate mai jos:
[quote]%s[/quote]

Dacă doriţi să retrimiteţi stilul în baza de date a personalizării, asiguraţi-vă că aţi reparat toate problemele identificate şi că respectă [url=http://www.phpbb.com/community/viewtopic.php?t=988545]condiţiile de trimitere a stilurilor[/url].

Dacă simţiţi că această respingere nu este justă vă rugăm să luaţi legătura cu liderul echipei de stiluri.

Cu sinceritate,
Echipa de stiluri',
));
