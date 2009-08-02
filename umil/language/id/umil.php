<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * Translated By: Sastra Manurung
 *
 */

/**
 * @ignore
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
	'ACTION'						=> 'Tindakan',
	'ADVANCED'						=> 'Tingkat Lanjut',
	'AUTH_CACHE_PURGE'				=> 'Membersihkan Cache Auth',

	'CACHE_PURGE'					=> 'Membersihkan cache forum anda',
	'CONFIGURE'						=> 'Konfigurasikan',
	'CONFIG_ADD'					=> 'Menambahkan variabel konfigurasi yang baru: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'ERROR: Variabel konfigurasi %s sudah ada.',
	'CONFIG_NOT_EXIST'				=> 'ERROR: Variabel konfigurasi %s belum ada.',
	'CONFIG_REMOVE'					=> 'Menyingkirkan variabel konfigurasi: %s',
	'CONFIG_UPDATE'					=> 'Memperbarui variabel konfigurasi: %s',

	'DISPLAY_RESULTS'				=> 'Menampilkan Hasil Selangkapnya',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Pilih ya untuk menampilkan semua tindakan dan hasil selama tindakan yang diinginkan.',

	'ERROR_NOTICE'					=> 'Satu atau lebih error terjadi selama tindakan yang dipilih.  Silahkan download <a href="%1$s">file ini</a> dengan error yang diberikan dan mintalah bantuan dari pembuat mod.<br /><br />Jika anda memiliki masalah dalam mendownload file, anda bisa mengaksesnya secara langsung melalui browser FTP pada lokasi yang diberikan berikut ini: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Satu atau lebih error terjadi selama tindakan yang dipilih.  Silahkan buat salinan lengkap untuk semua error dan mintalah bantuan dari pembuat mod tersebut.',

	'FAIL'							=> 'Gagal',
	'FILE_COULD_NOT_READ'			=> 'ERROR: Tidak dapat membuka file %s untuk dibaca.',
	'FOUNDERS_ONLY'					=> 'Anda harus seorang pendiri halaman untuk mengakses halaman ini.',

	'GROUP_NOT_EXIST'				=> 'Grup tidak ada',

	'IGNORE'						=> 'Abaikan',
	'IMAGESET_CACHE_PURGE'			=> 'Melakukan refresh imageset %s',
	'INSTALL'						=> 'Instal',
	'INSTALL_MOD'					=> 'Instal %s',
	'INSTALL_MOD_CONFIRM'			=> 'Apakan anda siap untuk melakukan instalasi %s?',

	'MODULE_ADD'					=> 'Menambahkan %1$s modul: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'ERROR: Modul sudah ada.',
	'MODULE_NOT_EXIST'				=> 'ERROR: Modul belum ada.',
	'MODULE_REMOVE'					=> 'Menyingkirkan %1$s modul: %2$s',

	'NONE'							=> 'Tidak ada',
	'NO_TABLE_DATA'					=> 'ERROR: Tidak ada data tabel yang ditentukan',

	'PARENT_NOT_EXIST'				=> 'ERROR: Kategori induk yang ditentukan untuk modul ini tidak ada.',
	'PERMISSIONS_WARNING'			=> 'Pengaturan perijinan yang baru sudah ditambahkan.  Periksa pengaturan perijinan anda dan pastikan semuanya sudah sesuai dengan keiinginan anda.',
	'PERMISSION_ADD'				=> 'Menambahkan pilihan perijinan yang baru: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'ERROR: Pilihan perijinan %s sudah ada.',
	'PERMISSION_NOT_EXIST'			=> 'ERROR: Pilihan perijinan %s belum ada.',
	'PERMISSION_REMOVE'				=> 'Menyingkirkan pilihan perijinan: %s',
	'PERMISSION_SET_GROUP'			=> 'Menetapkan perijinan untuk grup %s.',
	'PERMISSION_SET_ROLE'			=> 'Menetapkan perijinan untuk peranan %s.',
	'PERMISSION_UNSET_GROUP'		=> 'Membatalkan perijinan untuk grup %s.',
	'PERMISSION_UNSET_ROLE'			=> 'Membatalkan perijinan untuk peranan %s.',

	'ROLE_NOT_EXIST'				=> 'Peranan tidak ada',

	'SUCCESS'						=> 'Berhasil',

	'TABLE_ADD'						=> 'Menambahkan sebuah tabel database yang baru: %s',
	'TABLE_ALREADY_EXISTS'			=> 'ERROR: Tabel database %s sudah ada.',
	'TABLE_COLUMN_ADD'				=> 'Menambahkan sebuah kolom baru bernama %2$s ke tabel %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'ERROR: Kolom %2$s sudah ada pada tabel %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'ERROR: Kolom %2$s belum ada pada tabel %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Menyingkirkan kolom yang bernama %2$s dari tabel %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Memperbarui kolom yang bernama %2$s dari tabel %1$s',
	'TABLE_KEY_ADD'					=> 'Menambahkan sebuah kunci bernama %2$s ke tabel %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'ERROR: Indeks %2$s sudah ada pada tabel %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'ERROR: Indeks %2$s belum ada pada tabel %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Menyingkirkan kunci yang bernama %2$s dari tabel %1$s',
	'TABLE_NOT_EXIST'				=> 'ERROR: Database table %s does not exist.',
	'TABLE_REMOVE'					=> 'Menyingkirkan tabel database: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Memasukkan data pada tebel database %s.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Menyingkirkan sebuah baris dari tabel database %s',
	'TABLE_ROW_UPDATE_DATA'			=> 'Memperbarui sebuah baris pada tabel database %s.',
	'TEMPLATE_CACHE_PURGE'			=> 'Melakukan refresh pada templat %s',
	'THEME_CACHE_PURGE'				=> 'Melakukan refresh pada thema %s',

	'UNINSTALL'						=> 'Uninstal',
	'UNINSTALL_MOD'					=> 'Uninstal %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Apakah anda siap untuk uninstall %s?  Semua pengaturan dan data yang tersimpan oleh mod ini akan dihapus!',
	'UNKNOWN'						=> 'Tidak diketahui',
	'UPDATE_MOD'					=> 'Memperbarui %s',
	'UPDATE_MOD_CONFIRM'			=> 'Apakah anda siap untuk memperbarui %s?',
	'UPDATE_UMIL'					=> 'Versi UMIL sudah lama.<br /><br />Silahkan download versi UMIL (Unified MOD Install Library) terbaru dari: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'Versi Mod: <strong>%1$s</strong><br />Yang diinstal sekarang ini: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Versi Pilih',
	'VERSION_SELECT_EXPLAIN'		=> 'Jangan merubah dari “Abaikan” kecuali anda mengerti dengan apa yang anda lakukan ataupun yang diperintahkan.',
));

?>