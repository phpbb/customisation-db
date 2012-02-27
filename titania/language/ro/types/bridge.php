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
	'BRIDGE'							=> 'Interfaţă',
	'BRIDGES'							=> 'Interfaţe',
	'BRIDGE_VALIDATION'					=> '[Interfaţă phpBB - Validare] %1$s %2$s',
	'BRIDGE_VALIDATION_MESSAGE_APPROVE'	=> 'Vă mulţumim că aţi trimis intefaţa în baza de date a personalizării phpBB.com. După o verificare atentă, interfaţa dumneavoastră a fost aprobată şi va fi inclusă în baza noastră de date pentru personalizare.

Sperăm că veţi furniza un suport minim pentru această interfaţă şi o veţi actualiza odata cu viitoarele versiuni ale phpBB-ului. Vă apreciem munca şi contribuţia la comunitate. Autorii ca dumneavoastră fac phpBB.com un loc mai bun pentu toată lumea.

[b]Note de la echipă relativ la interfaţa dumneavoastră:[/b]
[quote]%s[/quote]

Cu sinceritate,
Echipa phpBB',
	'BRIDGE_VALIDATION_MESSAGE_DENY'	=> 'Salut,

După cum ştiţi toate interfeţele trimise în baza de date a personalizării phpBB trebuie validate şi aprobate de către membrii echipei phpBB.

În urma procesului de validare a interfeţei dumneavoastră, echipa phpBB regretă să vă informeze că a trebuit să o respingă.

Pentru a corecta problema cu interfaţa dumneavoastră, vă rugăm să urmaţi instrucţiunile de mai jos:
[list=1][*]Efectuaţi modificările necesare pentru a corecta orice probleme (afişate mai jos) ce au determinat ca interfaţa dumneavoastră să fie respinsă.
[*]Trimiteţi din nou interfaţa dumneavoastră în baza noastră de date pentru personalizare.[/list]
Asiguraţi-vă că aţi testat interfaţa cu ultima versiune phpBB (verificaţi pagina [url=http://www.phpbb.com/downloads/]Downloads[/url]) înainte de a retrimite interfaţa.

Dacă simţiţi că această respingere nu este justă vă rugăm să luaţi legătura cu liderul echipei de dezvoltare.

Aici puteţi găsi raportul pe baza căruia interfaţa dumneavoastră a fost respinsă:
[quote]%s[/quote]

Vă mulţumim,
Echipa phpBB',
));
