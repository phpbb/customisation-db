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

$lang = array_merge($lang, array(
	'ACCESS'							=> 'Nivel acces',
	'ACCESS_AUTHORS'					=> 'Acces autori',
	'ACCESS_PUBLIC'						=> 'Acces public ',
	'ACCESS_TEAMS'						=> 'Acces echipe',
	'ATTACH'							=> '&Incarcă fişier',

	'FILE_DELETED'						=> 'Acest fişier va fi şters c&and trimiteţi formularul',

	'HARD_DELETE_TOPIC_CONFIRM'			=> 'Sunteţi sigur că vreţi să ştergeţi <strong>definitiv</strong> acest subiect?<br /><br />Acest subiect va fi şters pentru totdeauna!',

	'QUEUE_DISCUSSION_TOPIC_MESSAGE'	=> 'Acest subiect este folosit pentru validarea discuţiei dintre autorii contribuţiei şi validatori.

Orice mesaj scris &in acest subiect va fi citit de cei care vă validează contribuţia aşa că scrieţi aici &in loc să folosiţi mesajele private pentru validatori.

Echipa de validare poate de asemenea adresa aici &intrebări autorilor aşa că furnizaţi informaţii pertinente pentru aceştia ce pot fi necesare pentru iniţierea procesului de validare.

Reţineţi că &in mod standard acest subiect este privat pentru autori şi validatori şi nu poate fi vazut de către publicul larg.',
	'QUEUE_DISCUSSION_TOPIC_TITLE'		=> 'Discuţie validare - %s',

	'REPORT_POST_CONFIRM'				=> 'Folosiţi acest formular să raportaţi mesajul selectat moderatorilor şi administratorilor forumului. Raportarea ar trebui să fie &in general folosită doar dacă mesajul &incalcă regulamentul forumului.',

	'SOFT_DELETE_TOPIC_CONFIRM'			=> 'Sunteţi sigur că doriţi să ştergeţi <strong>soft</strong> acest subiect?',
	'STICKIES'							=> 'Lipicioase',
	'STICKY_TOPIC'						=> 'Subiect lipicios',

	'UNDELETE_FILE'						=> 'Renunţare ştergere',
	'UNDELETE_POST'						=> 'Recuperare mesaj',
	'UNDELETE_POST_CONFIRM'				=> 'Sunteţi sigur că doriţi să recuperaţi acest mesaj?',
	'UNDELETE_TOPIC_CONFIRM'			=> 'Sunteţi sigur că doriţi să recuperaţi acest subiect?',
));
