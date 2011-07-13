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
	'ACCESS_LIMIT_AUTHORS'		=> 'Limitare acces nivel autor',
	'ACCESS_LIMIT_TEAMS'		=> 'Limitare acces nivel echipă',
	'ADD_FIELD'					=> 'Adaugă c&amp',
	'AGREE'						=> 'De acord',
	'AGREEMENT'					=> '&Inţelegere',
	'ALL'						=> 'Toate',
	'ALL_CONTRIBUTIONS'			=> 'Toate contribuţiile',
	'ALL_SUPPORT'				=> 'Toate subiectele de suport',
	'AUTHOR_BY'					=> 'De %s',

	'BAD_RATING'				=> '&Incercare eşuată de evaluare.',
	'BY'						=> 'de',

	'CACHE_PURGED'				=> 'Cache-ul a fost curăţat cu succes',
	'CATEGORY'					=> 'Categorie',
	'CATEGORY_CHILD_AS_PARENT'	=> 'Categoria părinte aleasă nu poate fi selectată deoarece este un descendent direct al acestei categorii.',
	'CATEGORY_DELETED'			=> 'Categorie ştearsă',
	'CATEGORY_DESC'				=> 'Descriere categorie',
	'CATEGORY_DUPLICATE_PARENT'	=> 'Categoria nu poate să fie propriul parinte.',
	'CATEGORY_HAS_CHILDREN'		=> 'Această categorie nu poate fi ştearsă pentru că include categorii descendente.',
	'CATEGORY_INFORMATION'		=> 'Informaţii categorie',
	'CATEGORY_NAME'				=> 'Nume categorie',
	'CATEGORY_TYPE'				=> 'Tip categorie',
	'CATEGORY_TYPE_EXPLAIN'		=> 'Tipul contribuţiilor acestei categorii va fi păstrat. Lăsaţi nespecificat pentru a nu accepta contribuţii.',
	'CAT_ADDONS'				=> 'Extensii',
	'CAT_ANTI_SPAM'				=> 'Anti-Spam',
	'CAT_AVATARS'				=> 'Avatar-uri',
	'CAT_BOARD_STYLES'			=> 'Stiluri forum',
	'CAT_COMMUNICATION'			=> 'Comunicaţii',
	'CAT_COSMETIC'				=> 'Cosmetice',
	'CAT_ENTERTAINMENT'			=> 'Divertisment',
	'CAT_LANGUAGE_PACKS'		=> 'Pachete limbă',
	'CAT_MISC'					=> 'Diverse',
	'CAT_MODIFICATIONS'			=> 'Modificări',
	'CAT_PROFILE_UCP'			=> 'Panoul utilizator/profil',
	'CAT_RANKS'					=> 'Ranguri',
	'CAT_SECURITY'				=> 'Securitate',
	'CAT_SMILIES'				=> 'Z&ambete',
	'CAT_SNIPPETS'				=> 'Coduri refolosibile',
	'CAT_STYLES'				=> 'Stiluri',
	'CAT_TOOLS'					=> 'Unelte',
	'CLOSED_BY'					=> '&Inchis de ',
	'CLOSED_ITEMS'				=> 'Elemente &inchise',
	'CONFIRM_PURGE_CACHE'		=> 'Sunteţi sigur că doriţi să ştergeţi cache-ul?',
	'CONTINUE'					=> 'Continuă',
	'CONTRIBUTION'				=> 'Contribuţie',
	'CONTRIBUTIONS'				=> 'Contribuţii',
	'CONTRIB_FAQ'				=> 'FAQ',
	'CONTRIB_MANAGE'			=> 'Administrare contribuţie',
	'CONTRIB_SUPPORT'			=> 'Discuţie/Suport',
	'CREATE_CATEGORY'			=> 'Creare categorie',
	'CREATE_CONTRIBUTION'		=> 'Creare contribuţie',
	'CUSTOMISATION_DATABASE'	=> 'Baza de date pentru personalizare',

	'DATE_CLOSED'				=> 'Dată &inchidere',
	'DELETED_MESSAGE'			=> 'Ultima ştergere efectuată de %1$s &in data de %2$s - <a href="%3$s">Apăsaţi aici pentru a restaura acest mesaj</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Şterge toate contribuţiile',
	'DELETE_CATEGORY'			=> 'Şterge categorie',
	'DELETE_SUBCATS'			=> 'Şterge subcategorii',
	'DESCRIPTION'				=> 'Descriere',
	'DESTINATION_CAT_INVALID'	=> 'Categoria destinaţie nu poate accepta contribuţii.',
	'DETAILS'					=> 'Detalii',
	'DOWNLOAD'					=> 'Descărcare',
	'DOWNLOADS'					=> 'Descărcări',
	'DOWNLOAD_ACCESS_DENIED'	=> 'Nu vă este permis să descărcaţi fişierul solicitat.',
	'DOWNLOAD_NOT_FOUND'		=> 'Fişierul solicitat nu a putut fi găsit.',

	'EDIT'						=> 'Modificare',
	'EDITED_MESSAGE'			=> 'Ultima modificare efectuată de %1$s &in data de %2$s',
	'EDIT_CATEGORY'				=> 'Modificare categorie',
	'ERROR'						=> 'Eroare',

	'FILE_NOT_EXIST'			=> 'Fişierul nu există: %s',
	'FIND_CONTRIBUTION'			=> 'Caută contribuţie',

	'HARD_DELETE'				=> 'Ştergere definitivă',
	'HARD_DELETE_EXPLAIN'		=> 'Selectaţi pentru a şterge definitiv acest element.',
	'HARD_DELETE_TOPIC'			=> 'Ştergere definitivă subiect',

	'LANGUAGE_PACK'				=> 'Pachet limbă',
	'LIST'						=> 'Listă',

	'MAKE_CATEGORY_VISIBLE'		=> 'Setează categoria vizibilă',
	'MANAGE'					=> 'Administrare',
	'MARK_CONTRIBS_READ'		=> 'Marchează contribuţiile citite',
	'MOVE_CONTRIBS_TO'			=> 'Mută contribuţiile &in',
	'MOVE_DOWN'					=> 'Mută jos',
	'MOVE_SUBCATS_TO'			=> 'Mută subcategoriile &in',
	'MOVE_UP'					=> 'Mută sus',
	'MULTI_SELECT_EXPLAIN'		=> 'Tineţi CTRL şi apăsaţi pentru a selecta mai multe elemente.',
	'MY_CONTRIBUTIONS'			=> 'Contribuţiile mele',

	'NAME'						=> 'Nume',
	'NEW_REVISION'				=> 'Revizie nouă',
	'NOT_AGREE'					=> 'Nu sunt de acord',
	'NO_AUTH'					=> 'Nu sunteţi autorizat pentru a vedea această pagină.',
	'NO_CATEGORY'				=> 'Categoria solicitată nu există.',
	'NO_CATEGORY_NAME'			=> 'Introduceţi numele categoriei',
	'NO_CONTRIB'				=> 'Contribuţia solicitată nu există.',
	'NO_CONTRIBS'				=> 'Nu a fost găsită nicio contribuţie',
	'NO_DESC'					=> 'Trebuie să introduceţi descrierea.',
	'NO_DESTINATION_CATEGORY'	=> 'Nu a fost găsită nicio categorie destinaţie.',
	'NO_POST'					=> 'Mesajul solicitat nu există.',
	'NO_REVISION_NAME'			=> 'Niciun nume specificat pentru revizie',
	'NO_TOPIC'					=> 'Subiectul solicitat nu există.',

	'ORDER'						=> 'Ordonare',

	'PARENT_CATEGORY'			=> 'Categoria părinte',
	'PARENT_NOT_EXIST'			=> 'Parintele nu există.',
	'POST_IP'					=> 'Scrie IP',
	'PURGE_CACHE'				=> 'Curăţă Cache',

	'QUEUE'						=> 'Listă aşteptare',
	'QUEUE_DISCUSSION'			=> 'Discuţie listă aşteptare',
	'QUICK_ACTIONS'				=> 'Acţiuni rapide',

	'RATING'					=> 'Evaluare',
	'REMOVE_RATING'				=> 'Şterge evaluare',
	'REPORT'					=> 'Raportează',
	'RETURN_LAST_PAGE'			=> 'Revenire la pagina anterioară',
	'ROOT'						=> 'Rădăcină',

	'SEARCH_UNAVAILABLE'		=> 'Sistemul de căutare nu este pentru moment disponibil. Vă rugăm să &incercaţi din nou peste c&ateva minute.',
	'SELECT_CATEGORY'			=> '-- Selectare categorie --',
	'SELECT_CATEGORY_TYPE'		=> '-- Selectare tip categorie --',
	'SELECT_SORT_METHOD'		=> 'Sortare după',
	'SHOW_ALL_REVISIONS'		=> 'Arată toate reviziile',
	'SITE_INDEX'				=> 'Index Site',
	'SNIPPET'					=> 'Cod refolosibil',
	'SOFT_DELETE_TOPIC'			=> 'Şterge temporar subiect',
	'SORT_CONTRIB_NAME'			=> 'Nume contribuţie',
	'STICKIES'					=> 'Lipicioase',
	'SUBSCRIBE'					=> 'Abonare',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Abonare notificare',

	'TITANIA_DISABLED'			=> 'Baza de date pentru personalizare este momentan dezactivată. Vă rugăm să &incercaţi din nou peste c&ateva minute',
	'TITANIA_INDEX'				=> 'Baza de date pentru personalizare',
	'TOTAL_CONTRIBS'			=> '%d contribuţii',
	'TOTAL_CONTRIBS_ONE'		=> '1 contribuţie',
	'TOTAL_POSTS'				=> '%d mesaje',
	'TOTAL_POSTS_ONE'			=> '1 mesaj',
	'TOTAL_RESULTS'				=> '%d rezultate',
	'TOTAL_RESULTS_ONE'			=> '1 rezultat',
	'TOTAL_TOPICS'				=> '%d subiecte',
	'TOTAL_TOPICS_ONE'			=> '1 subiect',
	'TRANSLATION'				=> 'Traducere',
	'TRANSLATIONS'				=> 'Traduceri',
	'TYPE'						=> 'Tip',

	'UNDELETE_TOPIC'			=> 'Restaurare subiect',
	'UNKNOWN'					=> 'Necunoscut',
	'UNSUBSCRIBE'				=> 'Dezabonare',
	'UPDATE_TIME'				=> 'Actualizat',

	'VERSION'					=> 'Versiune',
	'VIEW'						=> 'Vizualizare',
));
