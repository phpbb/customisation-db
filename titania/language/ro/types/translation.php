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
	'COULD_NOT_FIND_TRANSLATION_ROOT'			=> 'Nu am putut localiza directorul rădăcină al pachetului de traducere furnizat. Asiguraţi-vă că aveţi un director de conţine <code>language/</code> şi opţional <code>styles/</code> în nivelul cel mai de sus.',

	'MISSING_FILE'								=> 'Fişierul <code>%s</code> lipseşte din pachetul de traducere furnizat',
	'MISSING_KEYS'								=> 'Vă lipsesc următoarele etichete de limbă <code>%1$s</code>:<br />%2$s',

	'PASSED_VALIDATION'							=> 'Pachetul de traducere furnizat a trecut de procesul de validare ce verifică eventuala lipsă a etichetelor de limbă, fişierelor de licenţă şi continuaţi pentru a reîmpacheta traducerea.',

	'TRANSLATION'								=> 'Traducere',
	'TRANSLATION_VALIDATION'					=> '[Traducere phpBB - Validare] %1$s %2$s',
	'TRANSLATION_VALIDATION_MESSAGE_APPROVE'	=> 'Vă mulţumim că aţi trimis traducerea în baza de date a personalizării phpBB.com. După o verificare atentă, traducerea dumneavoastră a fost aprobată şi va fi inclusă în baza noastră de date pentru personalizare.

Sperăm că veţi furniza un suport minim pentru această traducere şi o veţi actualiza odata cu viitoarele versiuni ale phpBB-ului. Vă apreciem munca şi contribuţia la comunitate. Autorii ca dumneavoastră fac phpBB.com un loc mai bun pentu toată lumea.

[b]Note de la echipă relativ la traducerea dumneavoastră:[/b]
[quote]%s[/quote]

Cu sinceritate,
Echipa phpBB',
	'TRANSLATION_VALIDATION_MESSAGE_DENY'		=> 'Salut,

După cum ştiţi toate traducerile trimise în baza de date a personalizării phpBB trebuie validate şi aprobate de către membrii echipei phpBB.

În urma procesului de validare a traducerii dumneavoastră, echipa phpBB regretă să vă informeze că a trebuit să o respingă.

Pentru a corecta problema cu traducerea dumneavoastră, vă rugăm să urmaţi instrucţiunile de mai jos:
[list=1][*]Efectuaţi modificările necesare pentru a corecta orice probleme (afişate mai jos) ce au determinat respingerea traducerii dumneavoastră.
[*]Trimiteţi din nou traducerea dumneavoastră în baza noastră de date pentru personalizare.[/list]
Asiguraţi-vă că aţi testat traducerea cu ultima versiune phpBB (verificaţi pagina [url=http://www.phpbb.com/downloads/]Downloads[/url]) înainte de a retrimite traducerea.

Dacă simţiţi că această respingere nu este justă vă rugăm să luaţi legătura cu liderul echipei de traduceri.

Aici puteţi găsi raportul pe baza căruia traducerea dumneavoastră a fost respinsă:
[quote]%s[/quote]

Cu sinceritate,
Echipa phpBB',
));
