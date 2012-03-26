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
	'MODIFICATION'						=> 'Modificare',
	'MODIFICATIONS'						=> 'Modificări',
	'MOD_CREATE_PUBLIC'					=> '[b]Nume modificare[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descriere modificare[/b]: %4$s
[b]Versiune modificare[/b]: %5$s
[b]Testat cu versiunea phpBB[/b]: %11$s

[b]Descărcare fişier[/b]: [url=%6$s]%7$s[/url]
[b]Dimensiune fişier:[/b] %8$s Bytes

[b]Pagina de prezentare a modificării:[/b] [url=%9$s]Vizualizare[/url]

[color=blue][b]Echipa phpBB nu este responsabilă sau nu i se va cere să furnizeze suport pentru această modificare. Prin instalarea acestei modificări, acceptaţi că echipa de suport phpBB sau echipa de stiluri phpBB nu ar putea fi capabilă să vă furnizeze suport.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Suport modificare[/b]&lt;--[/url][/size]',
	'MOD_QUEUE_TOPIC'					=> '[b]Nume modificare[/b]: %1$s
[b]Autor:[/b] [url=%2$s]%3$s[/url]
[b]Descriere modificare[/b]: %4$s
[b]Versiune modificare[/b]: %5$s

[b]Descărcare fişier[/b]: [url=%6$s]%7$s[/url]
[b]Dimensiune fişier:[/b] %8$s Bytes',
	'MOD_REPLY_PUBLIC'					=> '[b][color=darkred]Modificare validată/lansată[/color][/b]',
	'MOD_REPLY_PUBLIC_NOTES'			=> '

[b]Note:[/b] %s',
	'MOD_UPDATE_PUBLIC'					=> '[b][color=darkred]Modificare actualizat la versiunea %1$s
Verificaţi primul mesaj pentru legătura de descărcare[/color][/b]',
	'MOD_UPDATE_PUBLIC_NOTES'			=> '

[b]Note:[/b] %1$s',
	'MOD_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">Trimiţ^and această revizie sunteţi de acord să respectaţi <a href="http://www.phpbb.com/mods/policies/">regulile bazei de date a modificărilor</a> şi că modificarea dumneavoastră respectă <a href="http://code.phpbb.com/svn/phpbb/branches/phpBB-3_0_0/phpBB/docs/coding-guidelines.html">regulile de codare phpBB3</a>.

De asemenea sunteţi de acord că licenţa acestei modificări şi licenţa oricărei componente incluse sunt compatibile cu <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> şi să permiteţi nelimitat redistribuirea modificării dumneavoastră prin acest site. Consultaţi <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">lista licenţelor de software aprobate FSF</a> pentru o listă a licenţelor disponibile şi compatibile cu GNU GPLv2.</span>',
	'MOD_VALIDATION'					=> '[Modificare phpBB - Validare] %1$s %2$s',
	'MOD_VALIDATION_MESSAGE_APPROVE'	=> 'Vă mulţumim că aţi trimis modificarea în baza de date a personalizării phpBB.com. După o verificare atentă, modificarea dumneavoastră a fost aprobată şi va fi inclusă în baza noastră de date pentru personalizare.

Sperăm că veţi furniza un suport minim pentru această modificare şi o veţi actualiza odata cu viitoarele versiuni ale phpBB-ului. Vă apreciem munca şi contribuţia la comunitate. Autorii ca dumneavoastră fac phpBB.com un loc mai bun pentu toată lumea.

[b]Note de la echipa de modificări relativ la modificarea dumneavoastră:[/b]
[quote]%s[/quote]

Cu sinceritate,
Echipa de modificări phpBB',
	'MOD_VALIDATION_MESSAGE_DENY'		=> 'Salut,

După cum ştiţi toate modificările trimise în baza de date a personalizării phpBB trebuie validate şi aprobate de către membrii echipei phpBB.

În urma procesului de validare a modificării dumneavoastră, echipa phpBB regretă să vă informeze că a trebuit să o respingă.

Pentru a corecta problema cu modificarea dumneavoastră, vă rugăm să urmaţi instrucţiunile de mai jos:
[list=1][*]Efectuaţi modificările necesare pentru a corecta orice probleme (afişate mai jos) ce au determinat ca modificarea dumneavoastră să fie respinsă.
[*]Testaţi-vă modificarea, fişierul XML şi instalarea acesteia.
[*]Trimiteţi din nou modificarea dumneavoastră în baza noastră de date pentru personalizare.[/list]
Asiguraţi-vă că aţi testat interfaţa cu ultima versiune phpBB (verificaţi pagina [url=http://www.phpbb.com/downloads/]Downloads[/url] page) înainte de a retrimite modificarea.

Aici puteţi găsi raportul pe baza căruia modificarea dumneavoastră a fost respinsă:
[quote]%s[/quote]

Vă rugăm să consultaţi următoarele legături ^inainte de a retrimite modificarea:
[list]
[*][url=http://www.phpbb.com/mods/modx/]Standardul phpBB MODX[/url]
[*][b]Securizare modificări:[/b]
[url=http://blog.phpbb.com/2009/02/12/injection-vulnerabilities/]Prevenire vulnerabilitate injecţie[/url]
[url=http://blog.phpbb.com/2009/09/10/how-not-to-use-request_var/]Cum să (nu) folosim request_var[/url]
[/list]

Pentru mai multe informaţii puteţi consulta:
[list][*][url=http://www.phpbb.com/mods/faq/]FAQ modificări[/url]
[*][url=http://www.phpbb.com/kb/3.0/modifications/]Categoria modificărilor phpBB3 ^in Catalogul cu articole[/url][/list]

Pentru ajutor ^in scrierea modificărilor phpBB există următoarele resurse:
[list][*][url=http://www.phpbb.com/community/viewforum.php?f=71]Forum pentru autorii de modificări\Ajutor[/url]
[*]Suport IRC - [url=irc://irc.freenode.net/phpBB-coding]#phpBB-coding[/url] este ^inregistrat pe reţeaua IRC FreeNode ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]Dacă doriţi să discutaţi despre orice informaţie inclusă ^in acest mesaj privat, vă rugăm să scrieţi un mesaj ^in secţiunea discuţiei din baza de date a modificărilor. Folosiţi legătura Modificările mele pentru a administra această modificare.[/b]

Dacă simţiţi că această respingere nu este justă vă rugăm să luaţi legătura cu liderul echipei de validări a modificărilor.
Pentru orice ^intrebări sau discuţii ulterioare folosiţi Subiectul din discuţia listei de aşteptare.

Cu sinceritate,
Echipa de modificări phpBB',
));
