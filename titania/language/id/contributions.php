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
'CUSTOM_LICENSE' => 'Custom',
	'ANNOUNCEMENT_TOPIC'					=> 'Topik Pengumuman',
	'ANNOUNCEMENT_TOPIC_SUPPORT'			=> 'Topik Bantuan',
	'ANNOUNCEMENT_TOPIC_VIEW'				=> '%sLihat%s',
	'ATTENTION_CONTRIB_CATEGORIES_CHANGED'	=> '<strong>Kategori kontribusi diubah dari:</strong><br />%1$s<br /><br /><strong>menjadi:</strong><br />%2$s',
	'ATTENTION_CONTRIB_DESC_CHANGED'		=> '<strong>Deskripsi Kontribusi diubah dari:</strong><br />%1$s<br /><br /><strong>menjadi:</strong><br />%2$s',
	'AUTOMOD_RESULTS'						=> '<strong>Silahkan diperiksa hasil dari proses instal AutoMOD dan pastikan tidak ada yang perlu diperbaiki.<br /><br />Jika kesalahan terjadi dan anda yakin bahwa kesalahan yang terjadi adalah tidak tepat, maka tekan lanjutkan di bawah ini.</strong>',
	'AUTOMOD_TEST'							=> 'MOD akan dicoba dengan AutoMOD dan hasilnya akan ditunjukkan (ini mungkin berlangsung tidak lama dan bersabarlah).<br /><br />Tekan lanjutkan ketika anda siap.',

	'BAD_VERSION_SELECTED'					=> '%s tidak cocok untuk versi phpBB.',

	'CANNOT_ADD_SELF_COAUTHOR'				=> 'Anda adalah pengarang utamanya, dan anda tidak dapat menambahkan anda sendiri ke dalam daftar pengarang pembantu.',
	'CLEANED_CONTRIB'						=> 'Kontribusi dibersihkan',
	'CONTRIB'								=> 'Kontribusi',
	'CONTRIBUTIONS'							=> 'Kontribusi',
	'CONTRIB_ACTIVE_AUTHORS'				=> 'Pengarang Pembantu Aktif',
	'CONTRIB_ACTIVE_AUTHORS_EXPLAIN'		=> 'Pengarang Pembantu Aktif bisa mengatur banyak bagian dari kontribusi.',
	'CONTRIB_APPROVED'						=> 'Disetujui',
	'CONTRIB_AUTHOR'						=> 'Kontribusi Pengarang',
	'CONTRIB_AUTHORS_EXPLAIN'				=> 'Masukkan nama pengarang pembantu, satu nama pengarang pembantu perbaris.',
	'CONTRIB_CATEGORY'						=> 'Kategori Kontribusi',
	'CONTRIB_CHANGE_OWNER'					=> 'Ubah Pemilik',
	'CONTRIB_CHANGE_OWNER_EXPLAIN'			=> 'Masukkan nama pengguna di sini untuk membuat pengguna ini sebagai pemiliknya. Dengan melakukan pengubahan ini, maka anda akan menjadi pengarang yang tidak berkontribusi.',
	'CONTRIB_CHANGE_OWNER_NOT_FOUND'		=> 'Pengguna yang anda coba atur sebagai pemilik, %s, tidak ditemukan.',
	'CONTRIB_CLEANED'						=> 'Dibersihkan',
	'CONTRIB_CONFIRM_OWNER_CHANGE'			=> 'Apakah anda yakin ingin memberikan kepemilikan kepada %s? Ini akan menjadikan anda tidak bisa lagi mengatur proyek dan tidak bisa dikembalikan lagi.',
	'CONTRIB_CREATED'						=> 'Kontribusi telah berhasil dibuat',
	'CONTRIB_DESCRIPTION'					=> 'Deskripsi Kontribusi',
	'CONTRIB_DETAILS'						=> 'Keterangan Kontribusi',
	'CONTRIB_DISABLED'						=> 'Tersembunyi + Nonaktif',
	'CONTRIB_DOWNLOAD_DISABLED'				=> 'Unduh Nonaktif',
	'CONTRIB_EDITED'						=> 'Kontribusi telah berhasil diubah.',
	'CONTRIB_HIDDEN'						=> 'Tersembunyi',
	'CONTRIB_ISO_CODE'						=> 'Kode ISO',
	'CONTRIB_ISO_CODE_EXPLAIN'				=> 'Kode ISO sesuai dengan <a href="http://area51.phpbb.com/docs/coding-guidelines.html#translation">Panduan Pengkodean Terjemahan</a>.',
	'CONTRIB_LOCAL_NAME'					=> 'Nama lokal',
	'CONTRIB_LOCAL_NAME_EXPLAIN'			=> 'Nama lokal bahasa, contoh <em>Français</em>.',
	'CONTRIB_NAME'							=> 'Nama Kontribusi',
	'CONTRIB_NAME_EXISTS'					=> 'Nama unik telah dibuat.',
	'CONTRIB_NEW'							=> 'Baru',
	'CONTRIB_NONACTIVE_AUTHORS'				=> 'Pengarang Pembantu Tidak Aktif (Kontributor Terdahulu)',
	'CONTRIB_NONACTIVE_AUTHORS_EXPLAIN'		=> 'Pengarang Pembantu Tidak Aktif tidak bisa mengatur apapun untuk kontribusi dan hanya terdaftar sebagai pengarang terdahulu.',
	'CONTRIB_NOT_FOUND'						=> 'Kontribusi yang anda minta tidak ditemukan.',
	'CONTRIB_OWNER_UPDATED'					=> 'Kepemilikan sudah diubah.',
	'CONTRIB_PERMALINK'						=> 'Permalink Kontribusi',
	'CONTRIB_PERMALINK_EXPLAIN'				=> 'Versi bersih dari nama kontribusi, digunakan untuk membuat url untuk kontribusi.<br /><strong>Biarkan kosong agar secara otomatis dibuat berdasarkan nama kontribusinya.</strong>',
	'CONTRIB_RELEASE_DATE'					=> 'Tanggal rilis',
	'CONTRIB_STATUS'						=> 'Status kontribusi',
	'CONTRIB_STATUS_EXPLAIN'				=> 'Ubuh status kontribusi',
	'CONTRIB_TYPE'							=> 'Tipe Kontribusi',
	'CONTRIB_UPDATED'						=> 'Kontribusi telah berhasil dibarui.',
	'CONTRIB_UPDATE_DATE'					=> 'Terakhir dibarui',
	'COULD_NOT_FIND_ROOT'					=> 'Tidak bisa menemukan direktori utama.  Pastikan ada file xml dengan nama instal didalamnya pada paket zip tersebut.',
	'COULD_NOT_FIND_USERS'					=> 'Tidak bisa menemukan penggunga berikut ini: %s',
	'COULD_NOT_OPEN_MODX'					=> 'Tidak bisa membuka file ModX.',
	'CO_AUTHORS'							=> 'Pengarang Pembantu',

	'DELETE_CONTRIBUTION'					=> 'Hapus Kontribusi',
	'DELETE_CONTRIBUTION_EXPLAIN'			=> 'Singkirkan secara permanen kontribusi ini (gunakan isian status kontribusi jika anda ingin menyembunyikannya).',
	'DELETE_REVISION'						=> 'Hapus Revisi',
	'DELETE_REVISION_EXPLAIN'				=> 'Hapus secara permanen revisi ini (gunakan isian status kontribusi jika anda ingin menyembunyikannya).',
	'DEMO_URL'								=> 'URL Demo',
	'DEMO_URL_EXPLAIN'						=> 'Lokasi demonstrasi',
	'DOWNLOADS_PER_DAY'						=> '%.2f Unduh per Hari',
	'DOWNLOADS_TOTAL'						=> 'Total Unduh',
	'DOWNLOADS_VERSION'						=> 'Versi Unduh',
	'DOWNLOAD_CHECKSUM'						=> 'MD5 checksum',
	'DUPLICATE_AUTHORS'						=> 'Anda memiliki daftar pengarang berikut ini sebagai yang aktif dan tidak aktif (tidak bisa kedua-duanya): %s',

	'EDIT_REVISION'							=> 'Ubah Revisi',
	'EMPTY_CATEGORY'						=> 'Pilihlah satu kategori',
	'EMPTY_CONTRIB_DESC'					=> 'Masukkan deskripsi kontribusi',
	'EMPTY_CONTRIB_ISO_CODE'				=> 'Masukkan kode ISO',
	'EMPTY_CONTRIB_LOCAL_NAME'				=> 'Masukkan nama lokal',
	'EMPTY_CONTRIB_NAME'					=> 'Masukkan nama kontribusi',
	'EMPTY_CONTRIB_PERMALINK'				=> 'Masukkan proposal permalink anda untuk kontribusi',
	'EMPTY_CONTRIB_TYPE'					=> 'Pilih satu tipe kontribusi paling sedikit',
	'ERROR_CONTRIB_EMAIL_FRIEND'			=> 'Anda tidak diijinkan menganjurkan kontribusi ini ke orang lain.',

	'INVALID_LICENSE'						=> 'Lisensi Tidak Sah',
	'INVALID_PERMALINK'						=> 'Anda perlu memasukkan sebuah permalink yang sah, sebagai contoh: %s',

	'LICENSE'								=> 'Lisensi',
	'LICENSE_EXPLAIN'						=> 'Lisensi merilis ini dibawah',
	'LOGIN_EXPLAIN_CONTRIB'					=> 'Untuk membuat kontribusi yang baru anda harus mendaftar terlebih dulu',

	'MANAGE_CONTRIBUTION'					=> 'Atur Kontribusi',
	'MPV_RESULTS'							=> '<strong>Silahkan periksa di hasil MPV dan psstikan tidak ada yang perlu diperbaiki.<br /><br />Jika anda tidak ingin memperbaiki apapun ataupun anda tidak yakin, maka tekan lanjutkan di bawah ini.</strong>',
	'MPV_TEST'								=> 'MOD ini akan diperiksa melalui MPV dan hasilnya akan ditampilkan (kegiatan ini mungkin akan memakan waktu, jadi bersabarlah).<br /><br />Silahkan tekan lanjutkan jika anda sudah siap.',
	'MPV_TEST_FAILED'						=> 'Maaf, tes MPV secara otomatis telah gagal dan hasil tes MPV tersebut tidak tersedia.  Silahkan dilanjutkan.',
	'MPV_TEST_FAILED_QUEUE_MSG'				=> 'Tes MPV otomatis gagal.  [url=%s]Klik di sini untuk mencoba mengulangi MPV secara otomatis lagi[/url]',
	'MUST_SELECT_ONE_VERSION'				=> 'Paling tidak anda harus memilih satu versi phpBB.',

	'NEW_CONTRIBUTION'						=> 'Kontribusi Baru',
	'NEW_REVISION'							=> 'Revisi Baru',
	'NEW_REVISION_SUBMITTED'				=> 'Revisi Baru telah berhasil diajukan!',
	'NEW_TOPIC'								=> 'Topik Baru',
	'NOT_VALIDATED'							=> 'Tidak Disahkan',
	'NO_CATEGORY'							=> 'Kategori yang terpilih tidak ada',
	'NO_PHPBB_BRANCH'						=> 'Anda harus memilih cabang phpBB.',
	'NO_QUEUE_DISCUSSION_TOPIC'				=> 'Tidak ada topik Diskusi Antrian ditemukan.  Sudahkan anda mengajukan beberapa revisi untuk kontribusi ini sebelumnya (topik akan dibuat setelahnya)?',
	'NO_REVISIONS'							=> 'Tidak Ada Revisi',
	'NO_REVISION_ATTACHMENT'				=> 'Silahkan pilih sebuah file untuk diunggah',
	'NO_REVISION_VERSION'					=> 'Silahkan masukkan sebuah versi dari revisi',
	'NO_SCREENSHOT'							=> 'Tidak ada gambar',
	'NO_TRANSLATION'						=> 'Arsip tidak tampak seperti paket bahasa yang sah. Mohon pastikan semua file yang ditemukan di direktori terjemahan Bahasa Inggris',

	'PHPBB_BRANCH'							=> 'Cabang phpBB',
	'PHPBB_BRANCH_EXPLAIN'					=> 'Pilih cabang phpBB yang didukung oleh revisi ini.',
	'PHPBB_VERSION'							=> 'Versi phpBB',

	'QUEUE_ALLOW_REPACK'					=> 'Ijinkan Pemaketan Ulang',
	'QUEUE_ALLOW_REPACK_EXPLAIN'			=> 'Ijinkan kontribusi ini untuk dipaketkan ulang untuk kesalahan kecil?',
	'QUEUE_NOTES'							=> 'Catatan Pengesahan',
	'QUEUE_NOTES_EXPLAIN'					=> 'Pesan ke tim.',

	'REPORT_CONTRIBUTION'					=> 'Kontribusi Laporan',
	'REPORT_CONTRIBUTION_CONFIRM'			=> 'Gunakan isian ini untuk memilih kontribusi kepada moderator dan administrator. Pelaporan seharusnya digunakan jika kontribusi melanggar aturan.',
	'REVISION'								=> 'Revisi',
	'REVISIONS'								=> 'Revisi',
	'REVISION_APPROVED'						=> 'Disetujui',
	'REVISION_DENIED'						=> 'Ditolak',
	'REVISION_IN_QUEUE'						=> 'Anda sudah memiliki revisi pada natrian validasi.  Anda harus mengunggu sampai revisi sebelumnya di setujui ataupun ditolak untuk mengajukan yang baru.',
	'REVISION_NAME'							=> 'Nama Revisi',
	'REVISION_NAME_EXPLAIN'					=> 'Masukkan nama opsional untuk versi ini (contoh: Furry Edition)',
	'REVISION_NEW'							=> 'Baru',
	'REVISION_PENDING'						=> 'Tunda',
	'REVISION_PULLED_FOR_OTHER'				=> 'Tarik',
	'REVISION_PULLED_FOR_SECURITY'			=> 'Tarik - Keamanan',
	'REVISION_REPACKED'						=> 'Paketkan Ulang',
	'REVISION_RESUBMITTED'					=> 'Ajukan Ulang',
	'REVISION_STATUS'						=> 'Status Revisi',
	'REVISION_STATUS_EXPLAIN'				=> 'Ubah status revisi',
	'REVISION_SUBMITTED'					=> 'Revisi sudah berhasil diajukan.',
	'REVISION_VERSION'						=> 'Versi Revisi',
	'REVISION_VERSION_EXPLAIN'				=> 'Nomor versi dari paket ini',

	'SCREENSHOTS'							=> 'Screenshot',
	'SELECT_CONTRIB_TYPE'					=> '-- Pilih tipe kontribusi --',
	'SELECT_PHPBB_BRANCH'					=> 'Pilih cabang phpBB',
	'SUBDIRECTORY_LIMIT'					=> 'Paket tidak diijinkan lebih dari 50 kedalaman subdirektori apapun.',
	'SUBMIT_NEW_REVISION'					=> 'Ajukan dan tambahkan revisi baru',

	'TOO_MANY_TRANSLATOR_LINKS'				=> 'Anda sedang menggunakan %d tautan luar pada baris TRANSLATION/TRANSLATION_INFO. Mohon masukkan <strong>satu tautan</strong> saja. Memasukkan dua tautan diijinkan hanya kasus demi kasus - silahkan post dalam forum terjemahan mengenai alasan anda memasukkan beberapa tautan luar di sepanjang garis.',

	'VALIDATION_TIME'						=> 'Waktu pengesahan',
	'VIEW_DEMO'								=> 'Lihat Demo',
	'VIEW_INSTALL_FILE'						=> 'Lihat file instal',

	'WRONG_CATEGORY'						=> 'Anda hanya bisa meletakkan kontribusi ini pada kategori yang sama dengan tipe kontribusi.',
));
