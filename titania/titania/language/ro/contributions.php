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

$lang = array_merge($lang, array(
'CUSTOM_LICENSE' => 'Custom',
	'ANNOUNCEMENT_TOPIC'					=> 'Subiect anunţ',
	'ANNOUNCEMENT_TOPIC_SUPPORT'			=> 'Subiect suport',
	'ANNOUNCEMENT_TOPIC_VIEW'				=> '%sVizualizare%s',
	'ATTENTION_CONTRIB_CATEGORIES_CHANGED'	=> '<strong>Categoriile contribuţiei schimbate din:</strong><br />%1$s<br /><br /><strong>^in:</strong><br />%2$s',
	'ATTENTION_CONTRIB_DESC_CHANGED'		=> '<strong>Descrierea contribuţiei schimbată din:</strong><br />%1$s<br /><br /><strong>^in:</strong><br />%2$s',
	'AUTOMOD_RESULTS'						=> '<strong>Verificaţi rezultatele instalării folosind AutoMod şi asiguraţi-vă că nu este nimic ce trebuie reparat.<br /><br />Dacă apare vreo eroare şi sunteţi sigur că aceea eroare este incorectă apăsaţi butonul Continuă de mai jos.</strong>',
	'AUTOMOD_TEST'							=> 'Această modificare va fi testată cu AutoMod şi rezultatele vor fi afişate (s-ar putea sa dureze cateva momente aşa că aveţi răbdare).<br /><br />Apăsaţi butonul Continu c^and sunteţi gata.',

	'BAD_VERSION_SELECTED'					=> '%s nu este o versiune phpBB validă.',

	'CANNOT_ADD_SELF_COAUTHOR'				=> 'Sunteţi autorul principal şi nu vă puteţi adăuga la lista co-autorilor.',
	'CLEANED_CONTRIB'						=> 'Contribuţie curăţată',
	'CONTRIB'								=> 'Contribuţie',
	'CONTRIBUTIONS'							=> 'Contribuţii',
	'CONTRIB_ACTIVE_AUTHORS'				=> 'Co-autori activi',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'		=> 'Co-autorii activi pot administra cele mai multe părţi ale contribuţiei.',
	'CONTRIB_APPROVED'						=> 'Aprobat,
	'CONTRIB_AUTHOR'						=> 'Autor contribuţie',
	'CONTRIB_AUTHORS_EXPLAIN'				=> 'Introduceţi un nume de co-autor pe fiecare linie ^in lista numelor co-autorilor .',
	'CONTRIB_CATEGORY'						=> 'Categorie contribuţie',
	'CONTRIB_CHANGE_OWNER'					=> 'Schimbă proprietar',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'			=> 'Introduceţi aici un nume de utilizator pentru a-l seta ca şi proprietar. Efectu^and acest lucru veţi fi setat ca şi autor ce nu contribuie.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'		=> 'Utilizatorul %s pe care ^incercaţi să setaţi ca şi proprietar nu a fost găsit.',
	'CONTRIB_CLEANED'						=> 'Curăţat',
	'CONTRIB_CONFIRM_OWNER_CHANGE'			=> 'Sunteţi sigur că doriţi să-l specificaţi pe %s ca proprietar? Acest lucru vă va limita posibilitatea de a administra proiectul şi acţiunea este ireversibilă.',
	'CONTRIB_CREATED'						=> 'Contribuţia a fost creată cu succes',
	'CONTRIB_DESCRIPTION'					=> 'Descriere contribuţie',
	'CONTRIB_DETAILS'						=> 'Detalii contribuţie',
	'CONTRIB_DISABLED'						=> 'Ascuns + Dezactivat',
	'CONTRIB_DOWNLOAD_DISABLED'				=> 'Descărcări dezactivate',
	'CONTRIB_EDITED'						=> 'Contribuţia a fost modificată cu succes.',
	'CONTRIB_HIDDEN'						=> 'Ascuns',
	'CONTRIB_ISO_CODE'						=> 'Cod ISO',
	'CONTRIB_ISO_CODE_EXPLAIN'				=> 'Codul ISO ^in conformitate cu <a href="http://area51.phpbb.com/docs/coding-guidelines.html#translation">instrucţiunile de codare a traducerilor</a>.',
	'CONTRIB_LOCAL_NAME'					=> 'Nume local',
	'CONTRIB_LOCAL_NAME_EXPLAIN'			=> 'Numele localizat al limbii, ex. <em>Français</em>.',
	'CONTRIB_NAME'							=> 'Nume contribuţie',
	'CONTRIB_NAME_EXISTS'					=> 'Numele unic a fost deja rezervat.',
	'CONTRIB_NEW'							=> 'Nou',
	'CONTRIB_NONACTIVE_AUTHORS'				=> 'Co-autori inactivi (foşti colaboratori)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'		=> 'Co-autorii inactivi nu pot modifica nimic ^in contribuţie şi sunt doar afişaţi ca foşti autori.',
	'CONTRIB_NOT_FOUND'						=> 'Contribuţia solicitată nu a putut fi găsită.',
	'CONTRIB_OWNER_UPDATED'					=> 'Proprietarul a fost schimbat.',
	'CONTRIB_PERMALINK'						=> 'Legătură permanentă contribuţie',
	'CONTRIB_PERMALINK_EXPLAIN'				=> 'Versiunea curăţată a numelui contribuţiei folosită pentru a construi legătura contribuţiei.<br /><strong>Lăsaţi necompletat pentru a avea una creată automat pe baza numelui contribuţiei.</strong>',
	'CONTRIB_RELEASE_DATE'					=> 'Dată lansare',
	'CONTRIB_STATUS'						=> 'Stare contribuţie',
	'CONTRIB_STATUS_EXPLAIN'				=> 'Schimbă starea contribuţiei',
	'CONTRIB_TYPE'							=> 'Tip contribuţie',
	'CONTRIB_UPDATED'						=> 'Contribuţia a fost actualizată cu succes.',
	'CONTRIB_UPDATE_DATE'					=> 'Ultima actualizare',
	'COULD_NOT_FIND_ROOT'					=> 'Nu am putut găsi directorul principal. Asiguraţi-vă că există un fişier XML ce are numele install in it somewhere in the zip package.',
	'COULD_NOT_FIND_USERS'					=> 'Nu am putut găsi următorii utilizatori: %s',
	'COULD_NOT_OPEN_MODX'					=> 'Nu am putut deschide fişierul ModX.',
	'CO_AUTHORS'							=> 'Co-Autori',

	'DELETE_CONTRIBUTION'					=> 'Şterge contribuţie',
	'DELETE_CONTRIBUTION_EXPLAIN'			=> 'Şterge permanen această contribuţie (folosiţi c^ampul de stare al contribuţiei dacă vreţi să o ascundeţi).',
	'DELETE_REVISION'						=> 'Şterge revizie',
	'DELETE_REVISION_EXPLAIN'				=> 'Şterge permanent această revizie (folosiţi c^ampul de stare al reviziei dacă vreţi să o ascundeţi).',
	'DEMO_URL'								=> 'Demo URL',
	'DEMO_URL_EXPLAIN'						=> 'Locaţie demonstraţie',
	'DOWNLOADS_PER_DAY'						=> '%.2f descărcări pe zi',
	'DOWNLOADS_TOTAL'						=> 'Total descărcări',
	'DOWNLOADS_VERSION'						=> 'Descărcări versiune',
	'DOWNLOAD_CHECKSUM'						=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'						=> 'Următorii autori sunt listaţi at^at ca şi activi c^at şi inactivi (nu pot fi ^in ambele categorii): %s',

	'EDIT_REVISION'							=> 'Modifică revizie',
	'EMPTY_CATEGORY'						=> 'Selectaţi cel puţin o categorie',
	'EMPTY_CONTRIB_DESC'					=> 'Introduceţi descrierea contribuţiei',
	'EMPTY_CONTRIB_ISO_CODE'				=> 'Introduceţi codul ISO',
	'EMPTY_CONTRIB_LOCAL_NAME'				=> 'Introduceţi numele local',
	'EMPTY_CONTRIB_NAME'					=> 'Introduceţi numele contribuţiei',
	'EMPTY_CONTRIB_PERMALINK'				=> 'Introduceţi propunerea dumneavoastră pentru legătura permanentă a contribuţiei',
	'EMPTY_CONTRIB_TYPE'					=> 'Selectaţi cel puţin un tip de contribuţie',
	'ERROR_CONTRIB_EMAIL_FRIEND'			=> 'Nu vă este permis să recomandaţi altcuiva această contribuţie.',

	'INVALID_LICENSE'						=> 'Licenţă invalidă',
	'INVALID_PERMALINK'						=> 'Trebuie să specificaţi o legătura permanentă validă, de exemplu: %s',

	'LICENSE'								=> 'Licenţă',
	'LICENSE_EXPLAIN'						=> 'Licenţa sub care acest proiect va fi lansat.',
	'LICENSE_FILE_MISSING'					=> 'Pachetul trebuie să conţină un fişier license.txt incluz^and termenii de licenţiere fie ^in directorul principal fie ^intr-unul din subdirectoarele directorului principal.',
	'LOGIN_EXPLAIN_CONTRIB'					=> 'Trebuie să fiţi ^inregistraţi pentru a creea o contribuţie nouă',

	'MANAGE_CONTRIBUTION'					=> 'Administrează contribuţie',
	'MPV_RESULTS'							=> '<strong>Verificaţi rezultatele MPV şi asiguraţi-vă că nu trebuie nimic reparat.<br /><br />Daca nu consideraţi că trebuie efectuate reparaţii sau nu sunteţi sigur, atunci doar apăsaţi butonul Continuă de mai jos.</strong>',
	'MPV_TEST'								=> 'Această modificare va fi testată cu MPV şi rezultatele vor fi afişate (poate dura c^ateva momente aşa că aveţi răbdare).<br /><br />Apăsaţi butonul Continuă c^and sunteţi gata.',
	'MPV_TEST_FAILED'						=> 'Ne pare rău dar testarea automată MPV a eşuat şi rezultatele testului MPB nu sunt disponibile. Vă rugăm să continuaţi.',
	'MPV_TEST_FAILED_QUEUE_MSG'				=> 'Testare automată MPV eşuată.  [url=%s]Apăsaţi aici pentru a ^incerca să executaţi din nou testul automat MPV[/url]',
	'MUST_SELECT_ONE_VERSION'				=> 'Trebuie să selectaţi cel puţin o versiune phpBB.',

	'NEW_CONTRIBUTION'						=> 'Contribuţie nouă',
	'NEW_REVISION'							=> 'Revizie nouă',
	'NEW_REVISION_SUBMITTED'				=> 'Revizia nouă a fost trimisă cu succes!',
	'NEW_TOPIC'								=> 'Subiect nou',
	'NOT_VALIDATED'							=> 'Nu este validată',
	'NO_CATEGORY'							=> 'Categoria selectată nu există',
	'NO_PHPBB_BRANCH'						=> 'Trebuie să selectaţi o ramură phpBB.',
	'NO_QUEUE_DISCUSSION_TOPIC'				=> 'Niciun subiect nu a fost găsit ^in discuţia pentru lista de aşteptare. Aţi trimis vreo revizie pentru această contribuţie (va fi creat c^and procedaţi astfel)?',
	'NO_REVISIONS'							=> 'Nicio revizie',
	'NO_REVISION_ATTACHMENT'				=> 'Selectaţi un fişier pentru ^incărcare',
	'NO_REVISION_VERSION'					=> 'Specificaţi o versiune pentru această revizie',
	'NO_SCREENSHOT'							=> 'Nicio captură',
	'NO_TRANSLATION'						=> 'Arhiva nu pare să aibă un pachet de limbă valid. Asiguraţi-vă că include toate fişierele găsite ^in directorul pentru limba engleză',

	'PHPBB_BRANCH'							=> 'Ramura phpBB',
	'PHPBB_BRANCH_EXPLAIN'					=> 'Selectaţi ramura phpBB suportată de această revizie.',
	'PHPBB_VERSION'							=> 'Versiune phpBB',

	'QUEUE_ALLOW_REPACK'					=> 'Permite re^impachetare',
	'QUEUE_ALLOW_REPACK_EXPLAIN'			=> 'Este permis ca această contribuţie să fie re^impachetată pentru erorile minore?',
	'QUEUE_NOTES'							=> 'Note validare',
	'QUEUE_NOTES_EXPLAIN'					=> 'Mesaj către echipă.',

	'REPORT_CONTRIBUTION'					=> 'Raportare contribuţie',
	'REPORT_CONTRIBUTION_CONFIRM'			=> 'Use this form to report the selected contribution to the moderators and administrators. Reporting should generally be used only if the contribution breaks forum rules.',
	'REVISION'								=> 'Revizie',
	'REVISIONS'								=> 'Revizii',
	'REVISION_APPROVED'						=> 'Aprobat',
	'REVISION_DENIED'						=> 'Respins',
	'REVISION_IN_QUEUE'						=> 'Aveţi deja o revizie ^in lista de aşteptare a validărilor. Trebuie să aşteptaţi p^ană c^and revizia anterioară este acceptată sau respinsă pentru a trimite o alta nouă.',
	'REVISION_NAME'							=> 'Revision Name',
	'REVISION_NAME_EXPLAIN'					=> 'Enter in an optional name for this version (ex: Furry Edition)',
	'REVISION_NEW'							=> 'Nou',
	'REVISION_PENDING'						=> '^In aşteptare',
	'REVISION_PULLED_FOR_OTHER'				=> 'Retras',
	'REVISION_PULLED_FOR_SECURITY'			=> 'Retras - Securitate',
	'REVISION_REPACKED'						=> 'Re^impachetat',
	'REVISION_RESUBMITTED'					=> 'Retrimis',
	'REVISION_STATUS'						=> 'Stare revizie',
	'REVISION_STATUS_EXPLAIN'				=> 'Modifică starea reviziei',
	'REVISION_SUBMITTED'					=> 'Revizia a fost trimisă cu succes.',
	'REVISION_VERSION'						=> 'Versiune revizie',
	'REVISION_VERSION_EXPLAIN'				=> 'Numărul versiunii acestui pachet',

	'SCREENSHOTS'							=> 'Capturi',
	'SELECT_CONTRIB_TYPE'					=> '-- Selectaţi tipul contribuţiei --',
	'SELECT_PHPBB_BRANCH'					=> 'Selectaţi ramura phpBB',
	'SUBDIRECTORY_LIMIT'					=> 'Pachetele nu pot avea la un moment dat mai mult de 50 subdirectoare ad^ancime.',
	'SUBMIT_NEW_REVISION'					=> 'Trimite şi adaugă o nouă revizie',

	'TOO_MANY_TRANSLATOR_LINKS'				=> '^In acest moment folosiţi %d legături externe ^in cadrul liniei TRANSLATION/TRANSLATION_INFO. Vă rugăm să includeţi doar <strong>o legătură</strong>. Două legături sunt permise de la caz la caz - vă rugăm să includeţi ^in notiţele forumului de traducere motivul care stă la baza folosirii mai multor legături externe.',

	'VALIDATION_TIME'						=> 'Timp validare',
	'VIEW_DEMO'								=> 'Vizualizare Demo',
	'VIEW_INSTALL_FILE'						=> 'Vizualizare fişier instalare',

	'WRONG_CATEGORY'						=> 'Puteţi pune această contribuţie doar ^in acelaşi tip de categorie ca şi tipul contribuţiei.',
));
