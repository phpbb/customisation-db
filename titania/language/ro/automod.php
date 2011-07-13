<?php
/**
*
* @package language
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ADDITIONAL_CHANGES'					=> 'Schimbări disponibile',
	'AM_MANUAL_INSTRUCTIONS'				=> 'AutoMOD trimite un fişier compresat la calculatorul dumneavoastră. Din cauza configurării AutoMOD, fişierele nu pot fi scrise automat pe site-ul dumneavoastră. Va trebui să extrageţi fişierul şi să urcaţi manual fişierele pe server-ul dumneavoastră folosind un client FTP sau o metodă similară. Apăsaţi %saici%s dacă nu aţi primit automat acest fişier.',
	'AM_MOD_ALREADY_INSTALLED'				=> 'AutoMOD a detectat că această modificare este instalată deja şi nu poate continua.',
	'APPLY_TEMPLATESET'						=> 'la acest şablon',
	'APPLY_THESE_CHANGES'					=> 'Aplica aceste schimbări',
	'AUTHOR_EMAIL'							=> 'Email autor',
	'AUTHOR_INFORMATION'					=> 'Informaţii autor',
	'AUTHOR_NAME'							=> 'Nume autor',
	'AUTHOR_NOTES'							=> 'Note autor',
	'AUTHOR_URL'							=> 'URL autor',
	'AUTOMOD'								=> 'AutoMOD',
	'AUTOMOD_CANNOT_INSTALL_OLD_VERSION'	=> 'Versiunea AutoMOD pe care &incercaţi să o instalaţi este instalată deja. Vă rugăm să ştergeţi acest director install/ .',
	'AUTOMOD_INSTALLATION'					=> 'Instalare AutoMOD',
	'AUTOMOD_INSTALLATION_EXPLAIN'			=> 'Bine aţi venit la Instalarea AutoMOD. Veţi avea nevoie de detaliile FTP dacă AutoMOD detectează că aceasta este cea mai bună metodă pentru a scrie fişierele. Mai jos puteţi găsi rezultatele testului de cerinţe.',
	'AUTOMOD_UNKNOWN_VERSION'				=> 'AutoMOD nu a putut să actualizeze pentru că nu a putut determina versiunea curent instalată. Versiunea afişată pentru instalarea dumneavoastră este %s.',
	'AUTOMOD_VERSION'						=> 'Versiune AutoMOD',

	'CAT_INSTALL_AUTOMOD'					=> 'AutoMOD',
	'CHANGES'								=> 'Schimbări',
	'CHANGE_DATE'							=> 'Dată lansare',
	'CHANGE_VERSION'						=> 'Număr versiune',
	'CHECK_AGAIN'							=> 'Verifică din nou',
	'COMMENT'								=> 'Comentariu',
	'CREATE_TABLE'							=> 'Alterări baza de date',
	'CREATE_TABLE_EXPLAIN'					=> 'AutoMOD a efectuat cu succes alterările la baza de date incluz&and o permisiune care a fost asociată rolului “Administrator Total”.',

	'DELETE'								=> 'Şterge',
	'DELETE_CONFIRM'						=> 'Sunteţi sigur că vreţi să ştergeţi această modificare?',
	'DELETE_ERROR'							=> 'A apărut o eroare la ştergerea modificării selectate.',
	'DELETE_SUCCESS'						=> 'Modificarea a fost ştearsă cu succes.',
	'DEPENDENCY_INSTRUCTIONS'				=> 'Modificarea pe care &incercaţi să o instalaţi este dependentă de o altă modificare. AutoMOD nu poate detecta dacă această modificare a fost instalată. Vă rugăm să verificaţi dacă aţi instalat <strong><a href="%1$s">%2$s</a></strong> &inainte de instalarea modificării dumneavoastră.',
	'DESCRIPTION'							=> 'Descriere',
	'DETAILS'								=> 'Detalii',
	'DIR_PERMS'								=> 'Permisiuni director',
	'DIR_PERMS_EXPLAIN'						=> 'Anumite sisteme necesită ca directoarele să aibă anumite permisiuni ca să funcţioneze corect.  Normal setarea 0755 este corectă. Această setare nu are niciun impact pe sistemele Windows.',
	'DIY_INSTRUCTIONS'						=> 'Instrucţiuni că să le efectuaţi manual',

	'EDITED_ROOT_CREATE_FAIL'				=> 'AutoMOD nu a putut crea directorul unde fişierele modificate vor fi păstrate.',
	'ERROR'									=> 'Eroare',

	'FILESYSTEM_NOT_WRITABLE'				=> 'AutoMOD a sesizat că sistemul de fişiere nu poate fi scris aşa că metoda de scriere directă nu poate fi folosită.',
	'FILE_EDITS'							=> 'Modificări fişier',
	'FILE_EMPTY'							=> 'Fişier gol',
	'FILE_MISSING'							=> 'Nu pot localiza fişierul',
	'FILE_PERMS'							=> 'Permisiuni fişier',
	'FILE_PERMS_EXPLAIN'					=> 'Anumite sisteme necesită ca fişierele să aibă anumite permisiuni ca să funcţioneze corect. Normal setarea 0644 este corectă. Această setare nu are niciun impact pe sistemele Windows.',
	'FILE_TYPE'								=> 'Tip fişier comprimat',
	'FILE_TYPE_EXPLAIN'						=> 'Acesta este valid doar cu metoda de scriere “Descărcare fişier comprimat”',
	'FIND'									=> 'Caută',
	'FIND_MISSING'							=> 'Fişierul specificat de modificare nu a putut fi găsit',
	'FORCE_CONFIRM'							=> 'Funţionalitatea Forţare instalare a determinat faptul că modificarea nu este instalată completă. Va trebui să faceţi manual anumite schimbări la forumul propriu pentru a finaliza instalarea. Continuaţi?',
	'FORCE_INSTALL'							=> 'Forţare instalare',
	'FORCE_UNINSTALL'						=> 'Forţare dezinstalare',
	'FTP_INFORMATION'						=> 'Informaţii FTP',
	'FTP_METHOD_ERROR'						=> 'Nu a fost găsită nicio metoda FTP, vă rugăm verificaţi dacă setările de configurare ale AutoMOD-ului includ o metoda FTP corectă.',
	'FTP_METHOD_EXPLAIN'					=> 'Dacă aveţi probleme cu "FTP-ul" standard puteţi încerca "Socket simplu" ca o metodă alternativă pentru a vă conecta la serverul FTP.',
	'FTP_METHOD_FSOCK'						=> 'Socket simplu',
	'FTP_METHOD_FTP'						=> 'FTP',
	'FTP_NOT_USABLE'						=> 'Funcţionalitatea FTP nu poate fi folosită pentru că a fost dezactivată de gazda dumneavoastră.',

	'GO_PHP_INSTALLER'						=> 'Modificarea necesită un utilitar de instalare extern pentru a finaliza instalarea. Apăsaţi aici pentru a continua cu acel pas.',

	'INHERIT_NO_CHANGE'						=> 'Nicio modificare nu poate fi aplicată la acest fişier pentru ca şablonul %1$s depinde de %2$s.',
	'INLINE_EDIT_ERROR'						=> 'Eroare, o modificare în linie din fişierul MODX install nu are toate elementele necesare',
	'INLINE_FIND_MISSING'					=> 'Căutarea În-linie specificată de către modificare nu a putut fi găsită.',
	'INSTALLATION_SUCCESSFUL'				=> 'AutoMOD instalat cu succes.  Acum puteţi administra modificările propriu folosind TAB-ul AutoMOD din Panoul administratorului.',
	'INSTALLED'								=> 'Modificare instalată',
	'INSTALLED_EXPLAIN'						=> 'Modificarea proprie a fost instalată! Aici puteţi vedea mai multe informaţii de la instalare. Vă rugăm să obsvervaţi eventuale erori şi să cereţi suport la <a href="http://www.phpbb.com">phpBB.com</a>',
	'INSTALLED_MODS'						=> 'Modificări instalate',
	'INSTALL_AUTOMOD'						=> 'Instalare AutoMOD',
	'INSTALL_AUTOMOD_CONFIRM'				=> 'Sunteţi sigur că vreţi să instalaţi AutoMOD?',
	'INSTALL_ERROR'							=> 'Una sau mai multe acţiuni de instalare au eşuat. Vă rugăm verificaţi acţiunile de mai jos, efectuaţi schimbările cerute şi încercaţi din nou. Puteţi continua cu instalarea chiar dacă anumite acţiuni au eşuat. <strong>Acest lucru nu este recomandat si poate determina forumul propriu să nu funcţioneze corect.</strong>',
	'INSTALL_FORCED'						=> 'Aţi forţat instalarea acestei modificări chiar dacă au apărut erori în timpul instalării acesteia. E posibil ca să aveţi probleme cu forumul propriu. Vă rugăm să obsvervaţi acţiunile care au eşuat şi să le corectaţi.',
	'INSTALL_MOD'							=> 'Instalează această modificare',
	'INSTALL_TIME'							=> 'Timp instalare',
	'INVALID_MOD_INSTRUCTION'				=> 'Această modificare are o instrucţiune invalidă sau o operaţie de căutare în linie a eşuat.',
	'INVALID_MOD_NO_ACTION'					=> 'O acţiune îi lipseşte modificării care corespunde criteriului ‘%s’',
	'INVALID_MOD_NO_FIND'					=> 'O potrivire îi lipseşte modificării care corespunde criteriului ‘%s’',

	'LANGUAGE_NAME'							=> 'Nume limbă',

	'MANUAL_COPY'							=> 'Copierea neîncercată',
	'MODS_CONFIG_EXPLAIN'					=> 'Aici puteţi alege cum AutoMOD vă modifică fişierele. Cea mai simplă metodă este Descărcare fişier comprimat. Alte metode necesită permisiuni suplimentare pe server.',
	'MODS_COPY_FAILURE'						=> 'Fişierul %s nu a putut fi copiat. Vă rugăm să vă verificaţi permisiunile sau să folosiţi o metodă alternativă de scriere.',
	'MODS_EXPLAIN'							=> 'Aici puteţi administra modificările disponibile pe forumul propriu. AutoMOD vă permite să vă personalizaţi forumul prin instalarea automată a modificările scrise de comunitatea phpBB. Pentru mai multe informaţii despre modificări vă rugăm să vizitaţi <a href="http://www.phpbb.com/mods">site-ul phpBB</a>. Pentru a adăuga o modificarea la această listă, folosiţi formularul din josul paginii. Alternativ, îl puteţi extrage şi urca fişierele în directorul /store/mods/ aflat pe serverul propriu.',
	'MODS_FTP_CONNECT_FAILURE'				=> 'AutoMOD nu a putut să se conecteze la serverul FTP propriu.  Eroarea este %s',
	'MODS_FTP_FAILURE'						=> 'AutoMOD nu a putut transmite prin FTP găsi fişierul %s',
	'MODS_MKDIR_FAILED'						=> 'Directorul %s nu a putut fi creat',
	'MODS_SETUP_INCOMPLETE'					=> 'A fost detectată o problemă cu configuraţia proprie şi AutoMOD nu a putut opera. Acest lucru se poate întâmpla doar când setările (ex. nume utilizator FTP) s-au schimbat şi pot fi corectate în pagina configurării AutoMOD.',
	'MOD_CONFIG'							=> 'Configurare AutoMOD',
	'MOD_CONFIG_UPDATED'					=> 'Configurarea AutoMOD-ului s-a actualizat.',
	'MOD_DETAILS'							=> 'Detalii modificare',
	'MOD_DETAILS_EXPLAIN'					=> 'Aici puteţi vedea toate informaţiile cunoscute despre modificarea selectată.',
	'MOD_MANAGER'							=> 'AutoMOD',
	'MOD_NAME'								=> 'Nume modificare',
	'MOD_OPEN_FILE_FAIL'					=> 'AutoMOD nu a putut deschide %s.',
	'MOD_UPLOAD'							=> 'Încarcă modificare',
	'MOD_UPLOAD_EXPLAIN'					=> 'Aici puteţi încărca o arhivă a modificării ce conţine fişierele MODX necesare pentru efectuarea instalării. AutoMOD vă încerca apoi să extragă fişierul şi să-l pregătească pentru instalare.',
	'MOD_UPLOAD_INIT_FAIL'					=> 'A apărut o eroare în timpul initializării procesului de încărcare a modificării.',
	'MOD_UPLOAD_SUCCESS'					=> 'Modificarea a fost încărcată şi pregătită pentru instalare.',

	'NAME'									=> 'Nume',
	'NEW_FILES'								=> 'Fişiere noi',
	'NO_ATTEMPT'							=> 'Fără &incercare',
	'NO_INSTALLED_MODS'						=> 'Nicio modificare instalată nu a fost detectată',
	'NO_MOD'								=> 'Modificarea selectată nu poate fi găsită.',
	'NO_UNINSTALLED_MODS'					=> 'Nicio modificare dezinstalată nu a fost detectată',
	'NO_UPLOAD_FILE'						=> 'Niciun fişier specificat.',

	'ORIGINAL'								=> 'Original',

	'PATH'									=> 'Cale',
	'PREVIEW_CHANGES'						=> 'Previzualizare modificări',
	'PREVIEW_CHANGES_EXPLAIN'				=> 'Afişează modificările ce trebuie efectuate &inainte de a le executa.',
	'PRE_INSTALL'							=> 'Pregătire pentru instalare',
	'PRE_INSTALL_EXPLAIN'					=> 'Aici puteţi previzualiza toate schimbările ce trebuie efectuate la forumul propriu &inainte de a fi aplicate. <strong>ATENŢIE!</strong>, odată acceptate, fişierele forumului propriu phpBB vor fi modificate şi pot fi efectuate schimbări la baza de date. Totuşi, dacă instalarea eşuează, presupun&and că folosiţi AutoMOD, veţi avea opţiunea să restauraţi p&ană la acest punct.',
	'PRE_UNINSTALL'							=> 'Pregătire pentru dezinstalare',
	'PRE_UNINSTALL_EXPLAIN'					=> 'Aici puteţi previzualiza toate schimbările ce trebuie efectuate la forumul propriu pentru a dezinstala modificarea. <strong>ATENŢIE!</strong>, odată acceptate, fişierele forumului propriu phpBB vor fi modificate şi pot fi efectuate schimbări la baza de date. De asemenea, acest proces foloseşte tehnici inverse ce nu sunt precise 100%. Totuşi, dacă instalarea eşuează, presupun&and că folosiţi AutoMOD, veţi avea opţiunea să restauraţi p&ană la acest punct.',

	'REMOVING_FILES'						=> 'Fişiere de eliminat',
	'RETRY'									=> '&Incearcă din nou',
	'RETURN_MODS'							=> 'Revenire la AutoMOD',
	'REVERSE'								=> 'Invers',
	'ROOT_IS_READABLE'						=> 'Directorul rădăcină phpBB poate fi citit.',
	'ROOT_NOT_READABLE'						=> 'AutoMOD nu a putut deschide pentru citire fişierul index.php al phpBB-ului. Acest lucru probabil &inseamnă că permisiunile sunt prea restrictive pe directorul radăcina al phpBB-ului propriu ce pot &impiedica AutoMOD-ul să funcţioneze. Vă rugăm să vă modificaţi permisiunile şi să &incercaţi din nou verificarea.',

	'SOURCE'								=> 'Sursă',
	'SQL_QUERIES'							=> 'Interogări SQL',
	'STATUS'								=> 'Stare',
	'STORE_IS_WRITABLE'						=> 'Directorul store/ poate fi scris.',
	'STORE_NOT_WRITABLE'					=> 'Directorul store/ nu poate fi scris.',
	'STORE_NOT_WRITABLE_INST'				=> 'Instalarea AutoMOD a detectat faptul că directorul store/ nu poate fi scris. Aceasta este o condiţie ca AutoMOD să funcţioneze corect. Vă rugăm să vă modificaţi permisiunile şi să &incercaţi din nou.',
	'STYLE_NAME'							=> 'Nume stil',
	'SUCCESS'								=> 'Succes',

	'TARGET'								=> 'Destinaţie',

	'UNINSTALL'								=> 'Dezinstalare',
	'UNINSTALLED'							=> 'Modificare dezinstalat',
	'UNINSTALLED_EXPLAIN'					=> 'Modificarea dumneavoastră a fost dezinstalată! Aici puteţi vedea c&ateva din rezultatele dezinstalării. Reţineţi orice erori şi cereţi suport la <a href="http://www.phpbb.com">phpBB.com</a>.',
	'UNINSTALLED_MODS'						=> 'Modificări dezinstalate',
	'UNINSTALL_AUTOMOD'						=> 'Dezinstalare AutoMOD',
	'UNINSTALL_AUTOMOD_CONFIRM'				=> 'Sunteţi sigur că doriţi să dezinstalaţi AutoMOD? Această procedură NU va elimina orice modificare ce a fost instalată cu AutoMOD.',
	'UNKNOWN_MOD_AUTHOR-NOTES'				=> 'Nicio nota de autor specificată.',
	'UNKNOWN_MOD_COMMENT'					=> '',
	'UNKNOWN_MOD_DESCRIPTION'				=> '',
	'UNKNOWN_MOD_DIY-INSTRUCTIONS'			=> '',
	'UNKNOWN_MOD_INLINE-COMMENT'			=> '',
	'UNKNOWN_QUERY_REVERSE'					=> 'Interogare necunoscută',
	'UNRECOGNISED_COMMAND'					=> 'Eroare, comandă necunoscută %s',
	'UPDATE_AUTOMOD'						=> 'Actualizare AutoMOD',
	'UPDATE_AUTOMOD_CONFIRM'				=> 'Vă rugăm să confirmaţi să vreţi să actualizaţi AutoMOD.',
	'UPLOAD'								=> '&Incarcă',

	'VERSION'								=> 'Versiune',

	'WRITE_DIRECT_FAIL'						=> 'AutoMOD nu a putut copia fişierul %s folosing metoda directă. Vă rugăm să folosiţi altă metodă de scriere şi să &incercaţi din nou.',
	'WRITE_DIRECT_TOO_SHORT'				=> 'AutoMOD nu a putut termina scrierea fişierului %s.  Aceasta poate fi de multe ori rezolvată folosing butonul &Incearcă din nou. Dacă nu funcţionează , &incercaţi altă metodă de scriere.',
	'WRITE_MANUAL_FAIL'						=> 'AutoMOD nu a putut adăuga fişierul %s lao arhivă comprimată. Vă rugăm să folosiţi altă metodă de scriere.',
	'WRITE_METHOD'							=> 'Metodă scriere',
	'WRITE_METHOD_DIRECT'					=> 'Direct',
	'WRITE_METHOD_EXPLAIN'					=> 'Puteţi specifica o metodă preferată pentru a scrie fişierele. Cea mai compatibilă opţiune este “Descărcare fişier compresat”.',
	'WRITE_METHOD_FTP'						=> 'FTP',
	'WRITE_METHOD_MANUAL'					=> 'Descărcare fişier compresat',

	'after add'								=> 'Adaugă după',

	'before add'							=> 'Adaugă &inainte',

	'find'									=> 'Caută',

	'in-line-after-add'						=> '&In linie după, adaugă',
	'in-line-before-add'					=> '&In linie &inainte, adaugă',
	'in-line-edit'							=> '&In linie caută',
	'in-line-operation'						=> '&In linie incrementează',
	'in-line-replace'						=> '&In linie &inlocuieşte',
	'in-line-replace-with'					=> '&In linie &inlocuieşte cu',

	'operation'								=> 'Incrementează',

	'replace'								=> '&Inlocuieşte cu',
	'replace with'							=> '&Inlocuieşte cu',
));
