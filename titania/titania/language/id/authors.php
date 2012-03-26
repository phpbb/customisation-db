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
	'AUTHOR_CONTRIBS'			=> 'Kontribusi',
	'AUTHOR_DATA_UPDATED'		=> 'Informasi pengarang sudah dibarui.',
	'AUTHOR_DESC'				=> 'Deskripsi Pengarang',
	'AUTHOR_DETAILS'			=> 'Keterangan Pengarang',
	'AUTHOR_MODS'				=> '%d Modifikasi',
	'AUTHOR_MODS_ONE'			=> '1 Modifikasi',
	'AUTHOR_NOT_FOUND'			=> 'Pengarang tidak ditemukan',
	'AUTHOR_PROFILE'			=> 'Profil Pengarang',
	'AUTHOR_RATING'				=> 'Rating Pengarang',
	'AUTHOR_REAL_NAME'			=> 'Nama Asli',
	'AUTHOR_SNIPPETS'			=> '%d Potongan',
	'AUTHOR_SNIPPETS_ONE'		=> '1 Potongan',
	'AUTHOR_STATISTICS'			=> 'Statistik Pengarang',
	'AUTHOR_STYLES'				=> '%d Gaya',
	'AUTHOR_STYLES_ONE'			=> '1 Gaya',
	'AUTHOR_SUPPORT'			=> 'Bantuan',

	'ENHANCED_EDITOR'			=> 'Enhanced Editor',
	'ENHANCED_EDITOR_EXPLAIN'	=> 'Hidupkan/matikan enhanced editor (menangkap tab dan memperluas daerah teks secara otomatis).',

	'MANAGE_AUTHOR'				=> 'Atur Pengarang',

	'NO_AVATAR'					=> 'Tidak ada avatar',

	'PHPBB_PROFILE'				=> 'Profil phpBB.com',

	'USER_INFORMATION'			=> 'informasi pengguna',

	'VIEW_USER_PROFILE'			=> 'Lihat profil pengguna',
));
