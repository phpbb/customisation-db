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
	'CREATE_FAQ'			=> 'FAQ nou',

	'DELETE_FAQ'			=> 'Şterge FAQ',
	'DELETE_FAQ_CONFIRM'	=> 'Sunteţi sigur că vreţi să ştergeţi pagina FAQ?',

	'EDIT_FAQ'				=> 'Modifică FAQ',

	'FAQ_CREATED'			=> 'Pagina FAQ a fost creată cu succes.',
	'FAQ_DELETED'			=> 'Pagina FAQ a fost ştearsă.',
	'FAQ_DETAILS'			=> 'Detalii pagină FAQ',
	'FAQ_EDITED'			=> 'Pagina FAQ a fost modificată cu succes.',
	'FAQ_EXPANDED'			=> 'Răspunsuri la &intrebări frecvente',
	'FAQ_LIST'				=> 'Lista FAQ',
	'FAQ_NOT_FOUND'			=> 'Pagina FAQ specificată nu a putut fi găsită.',

	'NO_FAQ'				=> 'Nu există &inregistrări FAQ.',

	'QUESTIONS'				=> '&Intrebări',
));
