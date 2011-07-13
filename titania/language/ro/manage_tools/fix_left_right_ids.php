<?php
/**
*
* @package Support Toolkit - Fix Left/Right ID's
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
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
	'FIX_LEFT_RIGHT_IDS'			=> 'Reparare ID-uri stânga/dreapta',
	'FIX_LEFT_RIGHT_IDS_CONFIRM'	=> 'Sunteţi sigur că vreţi să reparaţi ID-urile stânga/dreapta?<br /><br /><strong>Salvaţi o copie de siguraţă a bazei de date înainte de a executa acest utilitar!</strong>',

	'LEFT_RIGHT_IDS_FIX_SUCCESS'	=> 'ID-urile stânga/dreapta au fost reparare cu succes.',
	'LEFT_RIGHT_IDS_NO_CHANGE'		=> 'Utilitarul a procesat toate ID-urile stânga/dreapta şi toate liniile sunt deja corecte aşa că nu a fost efectuată nicio schimbare.',
));
