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
	'ADMINISTRATION'			=> 'Administrare',
	'ALLOW_AUTHOR_REPACK'		=> 'Permite autorului să re&impacheteze',
	'ALTER_NOTES'				=> 'Modifică notele de validare',
	'APPROVE'					=> 'Aprobă',
	'APPROVE_QUEUE'				=> 'Aprobă',
	'APPROVE_QUEUE_CONFIRM'		=> 'Sunteţi sigur că vreţi să <strong>aprobaţi</strong> acest element?',
	'ATTENTION'					=> 'Atenţie',
	'AUTHOR_REPACK_LINK'		=> 'Apăsaţi aici pentru a re&impacheta revizia',

	'CATEGORY_NAME_CLEAN'		=> 'URL categorie',
	'CHANGE_STATUS'				=> 'Modifică stare/Mută',
	'CLOSED_ITEMS'				=> 'Elemente &inchise',

	'DELETE_QUEUE'				=> 'Şterge lista elemente',
	'DELETE_QUEUE_CONFIRM'		=> 'Sunteţi sigur că vreţi să ştergeţi această listă cu elemente? Toate mesajele pentru listă vor fi pierdute şi revizia va fi extrasă dacă este nouă.',
	'DENY'						=> 'Respinge',
	'DENY_QUEUE'				=> 'Respinge',
	'DENY_QUEUE_CONFIRM'		=> 'Sunteţi sigur că vreţi să <strong>respingeţi</strong> acest element?',
	'DISCUSSION_REPLY_MESSAGE'	=> 'Scrie mesaj la discuţia listei de aşteptare',

	'EDIT_VALIDATION_NOTES'		=> 'Modifică notele de validare',

	'MANAGE_CATEGORIES'			=> 'Administrare Categorii',
	'MARK_IN_PROGRESS'			=> 'Marchează "&In Progres"',
	'MARK_NO_PROGRESS'			=> 'Demarchează "&In Progres"',
	'MOVE_QUEUE'				=> 'Mută Lista de aşteptare',
	'MOVE_QUEUE_CONFIRM'		=> 'Specifică noua locaţie pentru lista de aşteptare şi confirmă.',

	'NO_ATTENTION'				=> 'Niciun element nu necesită atenţie.',
	'NO_ATTENTION_ITEM'			=> 'Tip atenţionare inexistent.',
	'NO_ATTENTION_TYPE'			=> 'Tip atenţionare nepotrivit.',
	'NO_NOTES'					=> 'Nicio notă',
	'NO_QUEUE_ITEM'				=> 'Elementul listei de aşteptare nu există.',

	'OLD_VALIDATION_AUTOMOD'	=> 'Testare Automod din preîmpachetare',
	'OLD_VALIDATION_MPV'		=> 'Note MPV din preîmpachetare',
	'OPEN_ITEMS'				=> 'Elemente deschise',

	'PUBLIC_NOTES'				=> 'Note publice de lansare',

	'QUEUE_APPROVE'				=> 'Aşteaptă aprobare',
	'QUEUE_ATTENTION'			=> 'Atenţie',
	'QUEUE_DENY'				=> 'Aşteaptă respingere',
	'QUEUE_DISCUSSION_TOPIC'	=> 'Subiect discuţie Listă aşteptare',
	'QUEUE_NEW'					=> 'Nou',
	'QUEUE_REPACK'				=> 'Reîmpachetare',
	'QUEUE_REPACK_ALLOWED'		=> 'Reîmpachetare permisă',
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Reîmpachetare <strong>nepermisă</strong>',
	'QUEUE_REPLY_ALLOW_REPACK'	=> 'Alege să fie permis autorului reîmpachetarea',
	'QUEUE_REPLY_APPROVED'		=> 'Revizia %1$s [b]aprobată[/b] deoarece:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'Revizia %1$s [b]respinsă[/b] deoarece:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_IN_PROGRESS'	=> 'Marcat ca fiind în progres',
	'QUEUE_REPLY_MOVE'			=> 'Mutat de la %1$s la %2$s',
	'QUEUE_REPLY_NO_PROGRESS'	=> 'Elimina marcajul ca fiind în progres',
	'QUEUE_REVIEW'				=> 'Revizuire listă aşteptare',
	'QUEUE_STATUS'				=> 'Stare listă aşteptare',
	'QUEUE_TESTING'				=> 'Testare',
	'QUEUE_VALIDATING'			=> 'Validare',

	'REBUILD_FIRST_POST'		=> 'Reconstruieşte primul mesaj',
	'REPACK'					=> 'Reîmpachetare',
	'REPORTED'					=> 'Raportat',
	'RETEST_AUTOMOD'			=> 'Re-testare Automod',
	'RETEST_MPV'				=> 'Re-testare MPV',
	'REVISION_REPACKED'			=> 'Această revizie a fost reîmpachetată.',

	'SUBMIT_TIME'				=> 'Timp transmitere',

	'UNAPPROVED'				=> 'Neaprobat',
	'UNKNOWN'					=> 'Necunoscut',

	'VALIDATION'				=> 'Validare',
	'VALIDATION_AUTOMOD'		=> 'Test Automod',
	'VALIDATION_MESSAGE'		=> 'Mesaj/Motiv validare',
	'VALIDATION_MPV'			=> 'Note MPV',
	'VALIDATION_NOTES'			=> 'Note validare',
	'VALIDATION_QUEUE'			=> 'Listă aşteptare validare',
	'VALIDATION_SUBMISSION'		=> 'Transmitere validare',
));
