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
	'CATEGORY_EXPLAIN'				=> 'Batas yang didukung oleh versi terbaru hanyalah pada kategori yang terpilih.',

	'NEW_PHPBB_VERSION'				=> 'Versi phpBB baru',
	'NEW_PHPBB_VERSION_EXPLAIN'		=> 'Versi phpBB baru untuk daftar mendukung pada revisi untuk.',
	'NO_REVISIONS_UPDATED'			=> 'Tidak ada revisi yang dibarui dari pembatasan yang diberikan.',
	'NO_VERSION_SELECTED'			=> 'Anda harus memberikan versi phpBB yang tepat.  Contoh: 3.0.7 atau 3.0.7-PL1.',

	'PHPBB_VERSION_ADD'				=> 'Tambahkan versi phpBB yang mendukung revisi',

	'REVISIONS_UPDATED'				=> '%s revisi sudah dibarui.',

	'VERSION_RESTRICTION'			=> 'Batasan Revisi',
	'VERSION_RESTRICTION_EXPLAIN'	=> 'Batas yang didukung hanya pada versi yang terpilih.',
));
