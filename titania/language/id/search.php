<?php
/**
*
* @package Titania
* @version $Id$
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
	'CONTRIBUTION_NAME_DESCRIPTION'	=> 'Nama/Deskripsi Kontribusi',
	'CONTRIB_FAQ'					=> 'FAQ Kontribusi',
	'CONTRIB_NAME_DESCRIPTION'		=> 'Nama dan Deskripsi Kontribusi',
	'CONTRIB_SUPPORT'				=> 'Diskusi/Bantuan Kontribusi',

	'SEARCH_KEYWORDS_EXPLAIN'		=> 'Letakkan sebuah daftar kata yang dipisahkan dengan tanda <strong>|</strong> dalam kurung jika hanya satu kata yang harus ditemukan. Gunakan * sebagai wildcard untuk pencarian perbagian saja yang cocok.',
	'SEARCH_MSG_ONLY'				=> 'Hanya Teks/Deskripsi',
	'SEARCH_SUBCATEGORIES'			=> 'Cari Subkategori',
	'SEARCH_TITLE_MSG'				=> 'Judul dan Teks/Deskripsi',
	'SEARCH_TITLE_ONLY'				=> 'Hanya Judul',
	'SEARCH_WITHIN_TYPES'			=> 'Car di dalam tipe',
));
