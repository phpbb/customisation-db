<?php
/**
*
* help_faq [Romanian]
*
* @package Titania language
* @version $Id: help_faq.php
* @author: RMcGirr83
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
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
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$help = array(
	array(
		0 => '--',
		1 => 'Ce este Titania (aka Baza de date a personalizării)'
	),
	array(
		0 => 'Ce este Titania?',
		1 => 'Titania (aka Baza de date a personalizării) este o bază de date unde utilizatorii pot descărca modificări şi stiluri pentru un forum phpBB. De asemenea, sunteţi asiguraţi că modificarea sau stilul pe care l-aţi descărcat a trecut testul de validare phpBB.'
	),
	array(
		0 => 'Validare? Ce-i aia?',
		1 => 'Fiecare modificare sau stil care este descărcat din baza de date a personalizării a trecut prin validare.  Validarea înseamnă că o modificare sau un stil a fost evaluat din punct de vedere al securităţii codului implicat cât şi al testării instalării şi funcţionării corecte cu o anumită versiune a forumului phpBB. Validarea dă un anumit nivel de confort asigurându-vă că nu aţi descărcat/instalat o modificare sau un stil care poate determina ca forumul dumneavoastră să fie spart.'
	),
	array(
		0 => '--',
		1 => 'Cum să folosesc Titania',
	),
	array(
		0 => 'Caută o contribuţie',
		1 => 'Există diverse metode de a găsi o contribuţie. În pagina principală a bazei de date a personalizării puteţi vedea categoriile care sunt momentan disponibile cât şi modificările/stilurile recente care au fost aprobate în cadrul bazei de date.'
	),
	array(
		0 => 'Caută o modificare',
		1 => 'Puteţi fie să mergeţi direct la tipul modificării dorite în funcţie de categoria contribuţiei (Unelte, Comunicaţii, Securitate, Divertisment, etc) sau folosind funcţia de căutare localizată în partea superioară a paginii. Daca folosiţi funcţia de căutare atunci puteţi include wildcard-uri şi căuta fie după numele contribuţiei (sau părţi din nume) cât şi după autorul contribuţiei. Odată ce aţi găsit peronalizarea de care eraţi interesat veţi fi trimis la pagina “Detalii contribuţie” unde puteţi găsi o legătură pentru a descărca versiunea curentă a personalizării alături de versiunile anterioare în cadrul secţiunii “Revizii”.'
	),
	array(
		0 => 'Caută un stil',
		1 => 'Similar cu căutarea unei modificări, Titania vă permite să localizaţi stiluri, pachete zâmbete, imagini pentru ranguri şi alte elemente. Funcţia de căutare vă va permite de asemenea să includeţi wildcard-uri şi să căutaţi după numele autorului. Odată ce aţi găsit peronalizarea de care eraţi interesat veţi fi trimis la pagina “Detalii contribuţie” unde puteţi găsi o legătură pentru a descărca versiunea curentă a personalizării alături de versiunile anterioare în cadrul secţiunii “Revizii”'
	),
	array(
		0 => '--',
		1 => 'Suport personalizare'
	),
	array(
		0 => 'Reguli',
		1 => 'Odată cu introducerea utilitarului Titania, regulile implicate sunt foarte simple. Ca  şi în trecut, persistă expresia “trebuie să ceri suport în cadrul subiectului modificării/stilului de unde aţi luat personalizarea”.  În timp ce echipa de suport phpBB.com face totul ca să vă asiste în folosirea forumului dumneavoastră, ei nu pot şi nici nu li se poate cere să furnizeze suport pentru orice personalizare/contribţie. Există speranţa că autorul contribuţie vă furnizează, ca şi utilizator final, suport la folosirea personalizării. Vă rugăm să reţineţi că toţi autorii sunt voluntari ce au folosit timpul propriu pentru îmbunătaţirea soluţiei phpBB. Se aplică expresia “Obţii mai multe muşte cu miere decât cu oţet”, reţineţi aşadar acest lucru când cereţi suport pentru o personalizare (ex, cereţi frumos ajutor).'
	),
	array(
		0 => 'Cum se obţine suport',
		1 => 'Fiecare personalizare include o metodă pentru a vă furniza suport. În cadrul fiecăreia există posibilitatea autorului pentru a publica un mesaj cu răspunsuri la întrebări frecvente FAQ în ceea ce priveşte personalizarea cât şi o secţiune pentru discuţie/suport pentru suport individual. Acest suport poate varia de la a vă asista la instalarea personalizării până la a vă furniza extensii suplimentare pentru a îmbunătaţii personalizarea. Pentru a accesa această secţiune accesaţi personalizarea şi va apărea o secţiune “Discuţie/Suport”. Odată ce aţi accesat această secţiune, puteţi adresa o întrebare sau trimite un comentariu autorului. Reţineţi că autorii nu sunt obligaţi să vă ofere suport şi nici personalizarea. Dacă întâlniţi un mesaj sau comentariu ce consideraţi că nu este în beneficiul comuniăţii, vă rugăm să folosiţi butonul “Raportează acest mesaj” şi un moderator va lua măsurile necesare.'
	),
	// This block will switch the FAQ-Questions to the second template column
	// Authors corner!!
	array(
		0 => '--',
		1 => '--'
	),
	array(
		0 => '--',
		1 => 'Crearea şi administrarea contribuţiilor'
	),
	array(
		0 => 'Crearea unei contribuţii',
		1 => 'La orice contribuţie, autorilor li se cere să urmeze anumite direcţii când îşi trimit contribuţia. <a href="http://area51.phpbb.com/docs/coding-guidelines.html">Ghidul de codare</a>, care la început părea o corvoadă, este în realitate prietenul dumneavoastră. Aceste reguli ar trebui respectate cât mai bine pentru a vă asista la publicarea contribuţiei dumneavoastră în comunitate. În cazul unei modificări, <a href="http://www.phpbb.com/mods/mpv/">Pre-validatorul phpBB MOD</a> (aka “MPV”) va rula pe revizia trimisă şi va verifica diverse aspecte: licenţă corectă, versiunea curentă phpBB şi versiunea <a href="http://www.phpbb.com/mods/modx/">MODX</a> curentă.'
	),
	array(
		0 => 'Cum se trimite o contribuţie',
		1 => 'Aţi creat o contribuţie. Haideţi să o publicăm!!<br /><br />Pentru a trimite o contribuţie, accesaţi Baza de date a personalizării şi în cadrul acelei pagini veţi găsi o legătură imagine “Contribuţie nouă”. Odată ce aţi accesat-o, veţi putea specifica numele contribuţiei, selecta tipul contribuţiei şi adăuga câteva cuvinte la descrierea contribuţiei (sunt permise zâmbetele şi codul BB), selecta categoria căreia i se potriveşte contribuţia, adăuga co-autori (dacă este cazul) şi de asemenea capturi. Reţineţi că numele contribuţiei va fi asociat cu numele dumneavoastră.'
	),
	array(
		0 => 'Administrare contribuţii',
		1 => 'Odată ce contribuţia dumneavoastră este încărcată cu succes în Titania, o veţi putea administra. După selectarea contribuţiei folosind "Contribuţiile mele" din partea superioară a paginii, puteţi adăuga informaţii suplimentare prin intermediul TAB-ului "Administrare contribuţie". Puteţi modifica descrierea contribuţiei, încărca capturi, schimba proprietarul contribuţiei (reţineţi că această operaţie nu este reversibilă aşa că asiguraţi-vă că vreţi să cedaţi dreptul de proprietate al contribuţiei proprii unui alt utilizator), schimba categoriile contribuţiei şi specifica o legătură demo pentru ca utilizatorii să vadă cum arată şi cum funcţionează contribuţia.'
	),
	array(
		0 => 'Cum se trimite o nouă revizie',
		1 => 'Puteţi trimite noi revizii prin intermediul paginii principale din secţiunea “Detalii contribuţie” a personalizării dumneavoastră. De îndată ce aţi accesat legătura “Revizie nouă”, veţi ajunge într-o pagină în care puteţi încărca revizia, asocia revizia cu o versiune şi specifica observaţii pentru echipa de validare (sunt permise zâmbetele şi codul BB). Puteţi de asemenea să împachetaţi modificarea pentru echipa de validare. Reîmpachetarea implică mici reparaţii la personalizare. Aceasta poate implica şi corecţii la fişierul MODX install sau chiar mici modificări de cod.  Reîmpachetarea <strong>nu</strong> va însemna că echipa de validare va rescrie secvenţe majore din codul furnizat, aceasta va fi “sarcina” dumneavoastră.<br /><br />Regulile ce se aplică la crearea unei personalizări se vor aplica şi la trimiterea reviziilor personalizării proprii. Astfel <a href="http://www.phpbb.com/mods/mpv/">Pre-validatorul phpBB MOD</a> (aka “MPV”) va rula pe revizia trimisă şi va verifica diverse aspecte: licenţă corectă, versiunea curentă phpBB şi versiunea <a href="http://www.phpbb.com/mods/modx/">MODX</a> curentă.    '
	),
	array(
		0 => '--',
		1 => 'Acordare suport'
	),
	array(
		0 => 'FAQ',
		1 => 'Fiecare personalizare dă autorului posibilitatea de a crea subiecte cu răspunsuri la întrebări frecvente. Aceste subiecte pe care le creaţi ar trebui scrise în aşa fel să fie înţelese de un utilizator şi să se poată aplica subiectului personalizării fie pe marginea instalării personalizării, accesării funcţionalităţilor personalizării, etc. Ar trebui reţinut că această secţiune este doar pentru dumneavoastră. Utilizatorii nu pot modifica sau răspunde înregistrărilor din lista cu răspunsuri la întrebările frecvente.'
	),
	array(
		0 => 'Forum suport',
		1 => 'Reţineţi că utilizatorii vor adresa întrebări şi vor comenta pe marginea contribuţiei dumneavoastră. Vă rugăm să acordaţi suport contribuţiei cât de mult este posibil. Noi realizăm că v-aţi folosit timpul dumneavoastră pentru crearea acestei contribuţii şi că în viaţa reală câteodată nu mai e distractiv. Vă cerem doar ca şi autor să furnizaţi suport cât de mult posibil. Dacă întâlniţi un mesaj sau comentariu ce consideraţi că nu este în beneficiul comuniăţii, vă rugăm să folosiţi butonul “Raportează acest mesaj” şi un moderator va lua măsurile necesare.'
	),
	array(
		0 => '--',
		1 => 'Validare'
	),
	array(
		0 => 'Personalizarea mea nu a trecut testul de prevalidare',
		1 => 'Reţineţi, fiecare personalizare trebuie să aibă licenţa corectă (momentan este GNU GPL versiunea 2), versiunea corectă a utilitarului phpBB şi versiunea corectă MODX. Dacă nu aţi specificat aceste elemente în personalizare atunci nu poate fi acceptată în baza de date. Anumite erori pot fi doar simple avertismente şi nu trebuie reparate, dacă nu sunteţi sigur de problemă, continuaţi procesul de transmitere şi validatorul se va ocupa mai departe.'
	),
	array(
		0 => 'Personalizarea mea a trecut testul de prevalidare, ce urmează acum?',
		1 => 'Odată ce o personalizare este acceptată în baza de date, dacă este o modificare atunci va depinde de echipa în cauză să o valideze. Puteţi primi un mesaj din care să aflaţi că personalizarea dumneavoastră a fost respinsă. Nu vă supăraţi. Ştim că anumite lucruri pot si analizate superficial sau chiar pot scăpa. Nu vă îngrijoraţi. Mesajul primit va conţine elementele pe care le-am identificat. Acestea pot recomanda modificări ale codului sau imagini şi pot chiar recomanda modificări pentru o interfaţă pentru utilizator “neprietenoasă”. În general, recomandările pentru o interfaţă pentru utilizator “neprietenoasă” sunt doar ... recomandări. Cea mai importantă parte a oricărei personalizări este securitatea şi nu cum se prezintă utilizatorului final.<br /><br />Dacă nu au fost găsite probleme în timpul validării contribuţiei dumneavoastră, veţi primi un mesaj privat care vă va anunţa că am acceptat contribuţia dumneavoastră în baza de date. Acum este timpul să vă relaxaţi şi să sărbătoriţi faptul că aţi adus o contribuţie la comunitatea open source.<br /><br />Nu contează rezultatul validării, apreciem timpul şi efortul depus pentru această contribuţie.'
	),
	array(
		0 => 'Cine îmi va valida contribuţia?',
		1 => 'Dacă este o modificare atunci va fi validată de către echipa MOD-ificărilor şi validatorii juniori de MOD-ificări sau ocazional de către un membru al echipei de Dezvoltare. Un stil va fi validat de către echipa Stilurilor şi validatorii juniori de Stiluri. Un convertor va fi validat de către un membru al echipei de Dezvoltare sau Suport. O interfaţă va fi validat de către un membru al echipei de Dezvoltare sau MOD-ificărilor. Traducerile sunt toate verificate de către seful de Traduceri si IST, ameeck. Utilitarele oficiale sunt testate şi create de echipele phpBB.com.'
	),
);

?>