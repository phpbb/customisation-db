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
	'CONVERTER'								=> 'Convertor',
	'CONVERTERS'							=> 'Convertoare',
	'CONVERTER_VALIDATION'					=> '[Convertor phpBB - Validare] %1$s %2$s',
	'CONVERTER_VALIDATION_MESSAGE_APPROVE'	=> 'Vă mulţumim că aţi trimis convertorul în baza de date a personalizării phpBB.com. După o verificare atentă, convertorul dumneavoastră a fost aprobat şi va fi inclus în baza noastră de date pentru personalizare.

Sperăm că veţi furniza un suport minim pentru acest convertor şi-l veţi actualiza odata cu viitoarele versiuni ale phpBB-ului. Vă apreciem munca şi contribuţia la comunitate. Autorii ca dumneavoastră fac phpBB.com un loc mai bun pentu toată lumea.

[b]Note de la echipă relativ la convertorul dumneavoastră:[/b]
[quote]%s[/quote]

Cu sinceritate,
Echipa phpBB',
	'CONVERTER_VALIDATION_MESSAGE_DENY'		=> 'Salut,

După cum ştiţi toate convertoarele trimise în baza de date a personalizării phpBB trebuie validate şi aprobate de către membrii echipei phpBB.

În urma procesului de validare al convertorului dumneavoastră, echipa phpBB regretă să vă informeze că a trebuit să-l respingă.

Pentru a corecta problema cu convertorul dumneavoastră, vă rugăm să urmaţi instrucţiunile de mai jos:
[list=1][*]Efectuaţi modificările necesare pentru a corecta orice probleme (afişate mai jos) ce au determinat respingerea convertorului dumneavoastră.
[*]Trimiteţi din nou convertorul dumneavoastră în baza noastră de date pentru personalizare.[/list]
Asiguraţi-vă că aţi testat convertorul cu ultima versiune phpBB (verificaţi pagina [url=http://www.phpbb.com/downloads/]Downloads[/url]) înainte de a retrimite convertorul.

Dacă simţiţi că această respingere nu este justă vă rugăm să luaţi legătura cu liderul echipei de dezvoltare.

Aici puteţi găsi raportul pe baza căruia convertorul dumneavoastră a fost respins:
[quote]%s[/quote]

Cu sinceritate,
Echipa phpBB',
));
