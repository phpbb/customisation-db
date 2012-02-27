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
	'CREATE_FAQ'			=> 'FAQ Baru',

	'DELETE_FAQ'			=> 'Hapus FAQ',
	'DELETE_FAQ_CONFIRM'	=> 'Apakah anda yakin ingin menghapus FAQ ini?',

	'EDIT_FAQ'				=> 'Ubah FAQ',

	'FAQ_CREATED'			=> 'FAQ berhasil dibuat.',
	'FAQ_DELETED'			=> 'Entri FAQ sudah dihapus.',
	'FAQ_DETAILS'			=> 'Halaman Keterangan FAQ',
	'FAQ_EDITED'			=> 'FAQ berhasil diubah.',
	'FAQ_EXPANDED'			=> 'Pertanyaan Yang Sering Diajukan (Frequently Asked Questions)',
	'FAQ_LIST'				=> 'Daftar FAQ',
	'FAQ_NOT_FOUND'			=> 'FAQ yang ditentukan tidak dapat ditemukan.',

	'NO_FAQ'				=> 'Tidak ada entri FAQ.',

	'QUESTIONS'				=> 'Pertanyaan',
));
