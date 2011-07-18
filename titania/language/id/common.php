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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACCESS_LIMIT_AUTHORS'		=> 'Batas akses tingkat Pengarang',
	'ACCESS_LIMIT_TEAMS'		=> 'Batas akses tingkat Tim',
	'ADD_FIELD'					=> 'Tambahkan item',
	'AGREE'						=> 'Saya setuju',
	'AGREEMENT'					=> 'Persetujuan',
	'ALL'						=> 'Semua',
	'ALL_CONTRIBUTIONS'			=> 'Semua Kontribusi',
	'ALL_SUPPORT'				=> 'Semua topik bantuan',
	'AUTHOR_BY'					=> 'Oleh %s',

	'BAD_RATING'				=> 'Percobaan penilaian gagal.',
	'BY'						=> 'oleh',

	'CACHE_PURGED'				=> 'Tembolok telah berhasil dibersihkan',
	'CATEGORY'					=> 'Kategori',
	'CATEGORY_CHILD_AS_PARENT'	=> 'Kategori induk yang dipilih tidak dapat dapat digunakan karena ini adalah kategori anakan.',
	'CATEGORY_DELETED'			=> 'Kategori Dihapus',
	'CATEGORY_DESC'				=> 'Keterangan Kategori',
	'CATEGORY_DUPLICATE_PARENT'	=> 'Kategori tidak bisa dari induknya sendiri.',
	'CATEGORY_HAS_CHILDREN'		=> 'Kategori ini tidak bisa dihapus karena masih mengandung kategori anakan.',
	'CATEGORY_INFORMATION'		=> 'Informasi Kategori',
	'CATEGORY_NAME'				=> 'Nama Kategori',
	'CATEGORY_TYPE'				=> 'Tipe Kategori',
	'CATEGORY_TYPE_EXPLAIN'		=> 'Tipe kontribusi yang akan ditangani oleh kategori ini. Biarkan tidak atur untuk tidak menerima kontribusi.',
	'CAT_ADDONS'				=> 'Addon',
	'CAT_ANTI_SPAM'				=> 'Anti-Spam',
	'CAT_AVATARS'				=> 'Avatar',
	'CAT_BOARD_STYLES'			=> 'Gaya Papan',
	'CAT_COMMUNICATION'			=> 'Komunikasi',
	'CAT_COSMETIC'				=> 'Kosmetik',
	'CAT_ENTERTAINMENT'			=> 'Hiburan',
	'CAT_LANGUAGE_PACKS'		=> 'Paket Bahasa',
	'CAT_MISC'					=> 'Serbaneka',
	'CAT_MODIFICATIONS'			=> 'Modifikasi',
	'CAT_PROFILE_UCP'			=> 'Profil/Papan Pengaturan Pengguna',
	'CAT_RANKS'					=> 'Ranking',
	'CAT_SECURITY'				=> 'Keamanan',
	'CAT_SMILIES'				=> 'Smiley',
	'CAT_SNIPPETS'				=> 'Snippet',
	'CAT_STYLES'				=> 'Gaya',
	'CAT_TOOLS'					=> 'Alat',
	'CLOSED_BY'					=> 'Ditutup oleh',
	'CLOSED_ITEMS'				=> 'Item Ditutup',
	'CONFIRM_PURGE_CACHE'		=> 'Apakah anda yakin ingin membersihkan tembolok?',
	'CONTINUE'					=> 'Lanjutkan',
	'CONTRIBUTION'				=> 'Kontribusi',
	'CONTRIBUTIONS'				=> 'Kontribusi',
	'CONTRIB_FAQ'				=> 'FAQ',
	'CONTRIB_MANAGE'			=> 'Atur Kontribusi',
	'CONTRIB_SUPPORT'			=> 'Diskusi/Bantuan',
	'CREATE_CATEGORY'			=> 'Buat Kategori',
	'CREATE_CONTRIBUTION'		=> 'Buat Kontribusi',
	'CUSTOMISATION_DATABASE'	=> 'Database Kostumisasi',

	'DATE_CLOSED'				=> 'Tangal ditutup',
	'DELETED_MESSAGE'			=> 'Diubah terakhir oleh %1$s pada %2$s - <a href="%3$s">Klik disini untuk tidak menghapus pesan ini</a>',
	'DELETE_ALL_CONTRIBS'		=> 'Hapus semua Kontribusi',
	'DELETE_CATEGORY'			=> 'Hapus Kategori',
	'DELETE_SUBCATS'			=> 'Hapus Subkategori',
	'DESCRIPTION'				=> 'Deskripsi',
	'DESTINATION_CAT_INVALID'	=> 'Tujuan kategori tidak bisa menerima kontribusi.',
	'DETAILS'					=> 'Keterangan',
	'DOWNLOAD'					=> 'Unduh',
	'DOWNLOADS'					=> 'Unduh',
	'DOWNLOAD_ACCESS_DENIED'	=> 'Anda tidak diijinkan untuk mengunduh file yang diminta.',
	'DOWNLOAD_NOT_FOUND'		=> 'File yang diminta tidak bisa ditemukan.',

	'EDIT'						=> 'Ubah',
	'EDITED_MESSAGE'			=> 'Diubah terakhir oleh %1$s pada %2$s',
	'EDIT_CATEGORY'				=> 'Ubah Kategori',
	'ERROR'						=> 'Error',

	'FILE_NOT_EXIST'			=> 'File tidak ditemukan: %s',
	'FIND_CONTRIBUTION'			=> 'Cari Kontribusi',

	'HARD_DELETE'				=> 'Hapus Paksa',
	'HARD_DELETE_EXPLAIN'		=> 'Pilih untuk mengahapus item ini secara permanen.',
	'HARD_DELETE_TOPIC'			=> 'Hapus Paksa Topik',

	'LANGUAGE_PACK'				=> 'Paket Bahasa',
	'LIST'						=> 'Daftar',

	'MAKE_CATEGORY_VISIBLE'		=> 'Buat Kategori Terlihat',
	'MANAGE'					=> 'Atur',
	'MARK_CONTRIBS_READ'		=> 'Tandai kontribusi dibaca',
	'MOVE_CONTRIBS_TO'			=> 'Pindahkan kontribusi ke',
	'MOVE_DOWN'					=> 'Turunkan',
	'MOVE_SUBCATS_TO'			=> 'Pindahkan Subkategori ke',
	'MOVE_UP'					=> 'Naikkan',
	'MULTI_SELECT_EXPLAIN'		=> 'Tekan tahan CTRL dab klik untuk memilih beberapa item.',
	'MY_CONTRIBUTIONS'			=> 'Kontribusi Saya',

	'NAME'						=> 'Nama',
	'NEW_REVISION'				=> 'Revisi Terbaru',
	'NOT_AGREE'					=> 'Saya tidak setuju',
	'NO_AUTH'					=> 'Anda tidak diijinkan untuk melihat halama ini.',
	'NO_CATEGORY'				=> 'Kategori yang dipilih tidak ada.',
	'NO_CATEGORY_NAME'			=> 'Masukkan nama kategori',
	'NO_CONTRIB'				=> 'Kontribusi yang diminta tidak ditemukan.',
	'NO_CONTRIBS'				=> 'Tidak ada kontribusi yang ditemukan',
	'NO_DESC'					=> 'Anda harus memasukkan deskripsinya.',
	'NO_DESTINATION_CATEGORY'	=> 'Tidak ada tujuan direktori yang ditemukan.',
	'NO_POST'					=> 'Post yang diminta tidak ditemukan.',
	'NO_REVISION_NAME'			=> 'Tidak ada nama revisi yang diberikan',
	'NO_TOPIC'					=> 'Topik yang diminta tidak ditemukan.',

	'ORDER'						=> 'Order',

	'PARENT_CATEGORY'			=> 'Kategori Induk',
	'PARENT_NOT_EXIST'			=> 'Induk tidak ditemukan.',
	'POST_IP'					=> 'IP Post',
	'PURGE_CACHE'				=> 'Bersihkan Tembolok',

	'QUEUE'						=> 'Antrian',
	'QUEUE_DISCUSSION'			=> 'Diskusi Antrian',
	'QUICK_ACTIONS'				=> 'Tindakan Cepat',

	'RATING'					=> 'Penilaian',
	'REMOVE_RATING'				=> 'Singkirkan Penilaian',
	'REPORT'					=> 'Laporkan',
	'RETURN_LAST_PAGE'			=> 'Kembali ke halaman sebelumnya',
	'ROOT'						=> 'Root',

	'SEARCH_UNAVAILABLE'		=> 'Sistem pencarian tidak tersedia.  Silahkan dicoba beberapa saat lagi.',
	'SELECT_CATEGORY'			=> '-- Pilih kategori --',
	'SELECT_CATEGORY_TYPE'		=> '-- Pilih tipe kategori --',
	'SELECT_SORT_METHOD'		=> 'Urutkan Oleh',
	'SHOW_ALL_REVISIONS'		=> 'Tunjukkan semua revisi',
	'SITE_INDEX'				=> 'Indeks Situs',
	'SNIPPET'					=> 'Snippet',
	'SOFT_DELETE_TOPIC'			=> 'Hapus Topik Perlahan',
	'SORT_CONTRIB_NAME'			=> 'Nama Kontribusi',
	'STICKIES'					=> 'Sticky',
	'SUBSCRIBE'					=> 'Langganan',
	'SUBSCRIPTION_NOTIFICATION'	=> 'Notifikasi Berlangganan',

	'TITANIA_DISABLED'			=> 'Database Kostumisasi untuk sementara dinonaktifkan, silahkan dicoba bebearapa saat lagi.',
	'TITANIA_INDEX'				=> 'Database Kostumisasi',
	'TOTAL_CONTRIBS'			=> '%d Kontribusi',
	'TOTAL_CONTRIBS_ONE'		=> '1 Kontribusi',
	'TOTAL_POSTS'				=> '%d Post',
	'TOTAL_POSTS_ONE'			=> '1 Post',
	'TOTAL_RESULTS'				=> '%d Hasil',
	'TOTAL_RESULTS_ONE'			=> '1 Hasil',
	'TOTAL_TOPICS'				=> '%d Topik',
	'TOTAL_TOPICS_ONE'			=> '1 Topik',
	'TRANSLATION'				=> 'Terjemahan',
	'TRANSLATIONS'				=> 'Terjemahan',
	'TYPE'						=> 'Tipe',

	'UNDELETE_TOPIC'			=> 'Topik Tak Terhapus',
	'UNKNOWN'					=> 'Tidak Diketahui',
	'UNSUBSCRIBE'				=> 'Tidak Berlangganan',
	'UPDATE_TIME'				=> 'Diperbarui',

	'VERSION'					=> 'Versi',
	'VIEW'						=> 'Lihat',
));
