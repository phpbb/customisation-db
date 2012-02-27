<?php
/**
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/
/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


$lang = array_merge($lang, array(
	'ADDITIONAL_CHANGES'					=> 'Pengubahan Yang Tersedia',
	'AM_MANUAL_INSTRUCTIONS'				=> 'AutoMOD sedang mengirimkan file yang dikomopresi ke komputer anda.  Karena konfigurasu AutoMOD, file tidak bisa ditulisi ke situs anda secara otomatis.  Anda perlu mengekstraksi file tersebut dan unggah ke server anda secara manual dengan menggunakan klien FTP ataupun metode yang sama.  Jika anda tidak menerima file ini secara otomatis, klik %shere%s.',
	'AM_MOD_ALREADY_INSTALLED'				=> 'AutoMOD mendeteksi bahwa MOD ini sudah ada diinstal dan tidak bisa diproses secara lanjut.',
	'APPLY_TEMPLATESET'						=> 'ke templat ini',
	'APPLY_THESE_CHANGES'					=> 'Pakai pengubahan ini',
	'AUTHOR_EMAIL'							=> 'Email Pengarang',
	'AUTHOR_INFORMATION'					=> 'Informasi Pengarang',
	'AUTHOR_NAME'							=> 'Nama Pengarang',
	'AUTHOR_NOTES'							=> 'Catatan Pengarang',
	'AUTHOR_URL'							=> 'URL Pengarang',
	'AUTOMOD'								=> 'AutoMOD',
	'AUTOMOD_CANNOT_INSTALL_OLD_VERSION'	=> 'Versi AutoMOD yang anda coba instal sudah ada.  Silahkan hapus direktori install/.',
	'AUTOMOD_INSTALLATION'					=> 'Intalasi AutoMOD',
	'AUTOMOD_INSTALLATION_EXPLAIN'			=> 'Selamat datang di Instalasi AutoMOD.  Anda membutuhkan keterangan FTP anda jika AutoMOD menemukan hal ini adalah cara terbaik untuk menulisi file.  Hasil percobaan persyaratan ada di bawah ini.',
	'AUTOMOD_UNKNOWN_VERSION'				=> 'AutoMOD tidak bisa dibarui karena tidak bisa menentukan versi yang sudah terinstal sekarang.  Versi yang terdaftar untuk instalasi anda adalah %s.',
	'AUTOMOD_VERSION'						=> 'Versi AutoMOD',

	'CAT_INSTALL_AUTOMOD'					=> 'AutoMOD',
	'CHANGES'								=> 'Perngubah',
	'CHANGE_DATE'							=> 'Tanggal Rilis',
	'CHANGE_VERSION'						=> 'Nomor Versi',
	'CHECK_AGAIN'							=> 'Periksa lagi',
	'COMMENT'								=> 'Komentar',
	'CREATE_TABLE'							=> 'Pengubahan Database',
	'CREATE_TABLE_EXPLAIN'					=> 'AutoMOD berhasil membuat pengubahan database, termasuk perijinan yang ditetapkan ke peran “Full Administrator”.',

	'DELETE'								=> 'Hapus',
	'DELETE_CONFIRM'						=> 'Apakah anda yakin ingin menghapus MOD ini?',
	'DELETE_ERROR'							=> 'Ada kesalahan pada penghapusan MOD yang dipilih.',
	'DELETE_SUCCESS'						=> 'MOD berhasil dihapus.',
	'DEPENDENCY_INSTRUCTIONS'				=> 'MOD yang anda coba install bergantung pada MOD yang lainnya.  AutoMOD tidak dapat mendeteksi jika MOD ini sudah diinstal.  Mohon Please periksa apakah anda sudah menginstal <strong><a href="%1$s">%2$s</a></strong> sebelum instalasi MOD anda.',
	'DESCRIPTION'							=> 'Deskripsi',
	'DETAILS'								=> 'Keterangan',
	'DIR_PERMS'								=> 'Perijinan Direktori',
	'DIR_PERMS_EXPLAIN'						=> 'Beberapa sistem membutuhkan direktori memiliki perijinan tertentu agar bisa berfungsi dengan baik.  Normalnya, pengaturan bawaan 0755 adalah yang benar.  Pengaturan ini tidak memberikan dampak pada sistem Windows.',
	'DIY_INSTRUCTIONS'						=> 'Instruksi Do It Yourself',

	'EDITED_ROOT_CREATE_FAIL'				=> 'AutoMOD tidak mampu membuat direktori dimana file yang diubah akan disimpan.',
	'ERROR'									=> 'Error',

	'FILESYSTEM_NOT_WRITABLE'				=> 'AutoMOD menemukan bahwa sistem file tidak bisa ditulisi, sehingga metode tulis langsung tidak bisa digunakan.',
	'FILE_EDITS'							=> 'File Edit',
	'FILE_EMPTY'							=> 'File Kosong',
	'FILE_MISSING'							=> 'Tidak bisa menempatkan file',
	'FILE_PERMS'							=> 'Perijinan File',
	'FILE_PERMS_EXPLAIN'					=> 'Beberapa sistem membutuhkan file memiliki perijinan tertentu agar bisa berfungsi dengan baik.  Secara normalnya 0644 adalah yang benar.  Pengaturan ini tidak memberikan dampak sistem Windows.',
	'FILE_TYPE'								=> 'Tipe File Kompresi',
	'FILE_TYPE_EXPLAIN'						=> 'Bagian ini hanya sah dengan metode penulisan “Compressed File Download”',
	'FIND'									=> 'Cari',
	'FIND_MISSING'							=> 'Pencarian yang ditentukan oleh MOD tidak bisa ditemukan',
	'FORCE_CONFIRM'							=> 'Fitur Instal Paksa berarti MOD tidak diinstal secara keseluruhan.  Anda perlu membuat beberapa perbaikan manual pada halaman anda untuk menyelesaikan instalasi.  Lanjutkan?',
	'FORCE_INSTALL'							=> 'Instal Paksa',
	'FORCE_UNINSTALL'						=> 'Uninstal Paksa',
	'FTP_INFORMATION'						=> 'Informasi FTP',
	'FTP_METHOD_ERROR'						=> 'Tidak ada metode FTP ditemukan, silahkan periksa pada konfigurasi AutoMOD apakah sudah diatur metode FTP-nya.',
	'FTP_METHOD_EXPLAIN'					=> 'Jika anda memiliki permasalahan dengan "FTP" bawaannya, anda bisa mencoba "Simple Socket" sebagai cara alternatif untuk koneksi ke server FTP.',
	'FTP_METHOD_FSOCK'						=> 'Simple Socket',
	'FTP_METHOD_FTP'						=> 'FTP',
	'FTP_NOT_USABLE'						=> 'Funsi FTP tidak bisa digunakan karena hosting anda menonaktifkannya.',

	'GO_PHP_INSTALLER'						=> 'MOD membutuhkan penginstal luar untuk menyelesaikan instalasi.  Klik disini untuk melanjutkan ke langkah tersebut.',

	'INHERIT_NO_CHANGE'						=> 'Tidak ada pengubahan yang bisa dibuat ke file ini karena templat %1$s bergantung pada %2$s.',
	'INLINE_EDIT_ERROR'						=> 'Error, pengubahan sebaris di file instlasi MODX tidak menemukan semua elemen yang diperlukan',
	'INLINE_FIND_MISSING'					=> 'Pencarian sebaris yang ditetapkan oleh MOD tidak bisa ditemukan.',
	'INSTALLATION_SUCCESSFUL'				=> 'AutoMOD berhasil diinstal.  Sekarang anda bisa mengatur MODifikasi phpBB melalui tab AutoMOD di Papan Pengaturan Administrasi.',
	'INSTALLED'								=> 'MOD diinstal',
	'INSTALLED_EXPLAIN'						=> 'MOD anda sudah diinstal! Di sini anda bisa melihat beberapa hasil dari instlasi. Mohon diperhatikan semua error dan carilah bantuan di <a href="http://www.phpbb.com">phpBB.com</a>',
	'INSTALLED_MODS'						=> 'MOD yang diinstal',
	'INSTALL_AUTOMOD'						=> 'Instlasi AutoMOD',
	'INSTALL_AUTOMOD_CONFIRM'				=> 'Apakah anda yakin ingin menginstal AutoMOD?',
	'INSTALL_ERROR'							=> 'Satu atau lebih tindakan instal telah gagal. Silahkan tinjau tindakannya di bawah ini, buatlah beberapa penyetelan dan coba lagi. Anda bisa melanjutkan dengan instlasi walaupun beberapa tindakan telah gagal. <strong>Hal ini tidak disarankan dan bisa menyebabkan papan anda tidak berfungsi dengan baik.</strong>',
	'INSTALL_FORCED'						=> 'Anda telah melakukan paksaan terhadapa instalasi MOD ini walaupun ada kesalahan instalasi MOD. Papan anda mungkin akan rusak. Mohon dicatat tindakan gagal di bawah ini dan lakukan perbaikan.',
	'INSTALL_MOD'							=> 'Instal MOD',
	'INSTALL_TIME'							=> 'Waktu instalasi',
	'INVALID_MOD_INSTRUCTION'				=> 'MOD ini memiliki instruksi yang cacat, ataupun kegagalan operasi pencarian sebaris.',
	'INVALID_MOD_NO_ACTION'					=> 'MOD ini kehilangan pencocokan aksi pencarian ‘%s’',
	'INVALID_MOD_NO_FIND'					=> 'MOD ini kehilangan pencocokan pencarian tindakan ‘%s’',

	'LANGUAGE_NAME'							=> 'Nama Bahasa',

	'MANUAL_COPY'							=> 'Penyalinan tidak dicobakan',
	'MODS_CONFIG_EXPLAIN'					=> 'Anda bisa memilih bagaimana AutoMOD menyetel file-file anda di sini.  Metode yang paling dasar adalah Compressed File Download.  Yang lainnya akan membutuhkan perijinan tambahan di server.',
	'MODS_COPY_FAILURE'						=> 'File %s tidak bisa disalin ke tempatnya.  Silahkan periksa perijinan anda atau gunakan sebuah alternatif metode penulisan.',
	'MODS_EXPLAIN'							=> 'Di sini anda bisa mengatur MOD yang tersedia di papan anda. AutoMODs mengijinkan anda untuk mengkostumasi papan dengan melakukan instalasi modifikasi secara otomatis yang dihasilkan oleh komunitas phpBB. Untuk informasi lebih lanjut mengenai MOD dan AutoMOD silahkan kunjungi <a href="http://www.phpbb.com/mods">situs phpBB</a>.  Untuk menambahkan MOD pada daftar ini, gunakan formulir pada bagian bawah halaman ini.  Sebagai alternatif, anda bisa melakukan unxip dan mengunggah file ke direktori /store/mods/ di server anda.',
	'MODS_FTP_CONNECT_FAILURE'				=> 'AutoMOD tidak bisa terhubung dengan server FTP anda.  Kesalahannya adalah %s',
	'MODS_FTP_FAILURE'						=> 'AutoMOD tidak bisa melakukan FTP pada file %s ditempatnya',
	'MODS_MKDIR_FAILED'						=> 'Direktori %s tidak bisa dibuat',
	'MODS_SETUP_INCOMPLETE'					=> 'Sebuah permasalahan ditemukan pada konfigurasi anda, dan AutoMOD tidak bisa beroperasi.  Ini hanya terjadi apabila pengaturan (contoh: FTP username) sudah diubah, dan bisa diperbaiki di halaman konfigutasi AutoMOD.',
	'MOD_CONFIG'							=> 'Konfigurasi AutoMOD',
	'MOD_CONFIG_UPDATED'					=> 'Konfigutasi AutoMOD sudah diperbarui.',
	'MOD_DETAILS'							=> 'Keterangan MOD',
	'MOD_DETAILS_EXPLAIN'					=> 'Di sini anda bisa melihat semua informasi yang diketahui mengenai MOD yang anda pilih.',
	'MOD_MANAGER'							=> 'AutoMOD',
	'MOD_NAME'								=> 'Nama MOD',
	'MOD_OPEN_FILE_FAIL'					=> 'AutoMOD tidak bisa membuka %s.',
	'MOD_UPLOAD'							=> 'Unggah MOD',
	'MOD_UPLOAD_EXPLAIN'					=> 'Di sini anda bisa mengunggah paket MOD dalam bentuk zip yang mengandung file-file MODX yang diperlukan untuk melakukan instalasi.  AutoMOD akan mencoba melakukan unzip pada file dan mempersiapkannya untuk instalasi.',
	'MOD_UPLOAD_INIT_FAIL'					=> 'Ada kesalahan pada saat memulai poses unggah MOD.',
	'MOD_UPLOAD_SUCCESS'					=> 'MOD diunggah dan dipersiapkan untuk instalasi.',

	'NAME'									=> 'Nama',
	'NEW_FILES'								=> 'File-File Baru',
	'NO_ATTEMPT'							=> 'Tidak Dicoba',
	'NO_INSTALLED_MODS'						=> 'Tidak ada instalasi MOD',
	'NO_MOD'								=> 'MOD yang dipilih tidak ditemukan.',
	'NO_UNINSTALLED_MODS'					=> 'Tidak ada MOD yang di uninstal',
	'NO_UPLOAD_FILE'						=> 'Tidak ada file yang ditentukan.',

	'ORIGINAL'								=> 'Asli',

	'PATH'									=> 'Path',
	'PREVIEW_CHANGES'						=> 'Pratinjau Perubahan',
	'PREVIEW_CHANGES_EXPLAIN'				=> 'Menampilkan perubahan untuk dilakukan sebelum pengeksekusian.',
	'PRE_INSTALL'							=> 'Mempersiapkan install',
	'PRE_INSTALL_EXPLAIN'					=> 'Di sini anda dapat meninjau semua modifikasi yang dibuat di papan anda sebelum dilaksanakan. <strong>PERINGATAN!</strong>, setelah disetujui, file dasar phpBB anda akan diubah dan pengubahan database mungkin saja terjadi. Akan tetapi, jika proses instlasi tidak berhasil, anggap anda bisa mengakses AutoMOD, maka anda akan diberikan pilihan untuk kembali ke titik ini.',
	'PRE_UNINSTALL'							=> 'Mempersiapkan untuk uninstal',
	'PRE_UNINSTALL_EXPLAIN'					=> 'Di sini anda bisa meninjau semua modifikasi yang akan diterapkan dipapan anda untuk melakukan uninstal MOD. <strong>PERINGATAN!</strong>, setelah disetujui file dasar phpBB anda akan diubah dan pengubahan database mungkin saja terjadi. Dan juga, proses ini menggunakan teknik pembalikan yang mungkin 100% tidak akurat. Akan tetapi, jika uninstal tidak berhasil, anggap anda bisa mengakses AutoMOD, anda akan diberikan pilihan untuk kembali ke titik ini.',

	'REMOVING_FILES'						=> 'File yang akan disingkirkan',
	'RETRY'									=> 'Coba Lagi',
	'RETURN_MODS'							=> 'Kembali ke AutoMOD',
	'REVERSE'								=> 'Balik',
	'ROOT_IS_READABLE'						=> 'Direktori induk phpBB bisa dibaca.',
	'ROOT_NOT_READABLE'						=> 'AutoMOD tidak bisa membuka file index.php phpBB untuk dibaca.  Ini mungkin berarti bahwa perijinan yang digunakan sangat terbatas di direktori induk phpBB anda, yang akan mencegah AutoMOD bekerja.  Silahkan atur perijinan dan cobalah memeriksanya lagi.',

	'SOURCE'								=> 'Sumber',
	'SQL_QUERIES'							=> 'Kueri SQL',
	'STATUS'								=> 'Status',
	'STORE_IS_WRITABLE'						=> 'Direktori store/ bisa ditulisi.',
	'STORE_NOT_WRITABLE'					=> 'Direktori store/ tidak bisa ditulisi.',
	'STORE_NOT_WRITABLE_INST'				=> 'Instlasi AutoMOD menemukan bahwa direktori store/ tidak bisa ditulisi.  Hal ini dibutuhakan agar AutoMOD bisa berfungsi dengan baik.  Silahakan atur perijinan anda dan lakukan percobaan lagi.',
	'STYLE_NAME'							=> 'Nama Gaya',
	'SUCCESS'								=> 'Berhasil',

	'TARGET'								=> 'Target',

	'UNINSTALL'								=> 'Uninstal',
	'UNINSTALLED'							=> 'MOD yang diuninstal',
	'UNINSTALLED_EXPLAIN'					=> 'MOD anda sudah diuninstal! Di sini anda bisa melihat beberapa hasil dari proses uninstal. Mohon dicatat semua kesalahan yang terjadi dan carilah bantuan di <a href="http://www.phpbb.com">phpBB.com</a>.',
	'UNINSTALLED_MODS'						=> 'MOD yang diuninstal',
	'UNINSTALL_AUTOMOD'						=> 'AutoMOD yang diuninstal',
	'UNINSTALL_AUTOMOD_CONFIRM'				=> 'Apakah anda yakin ingin menginstal AutoMOD?  Hal ini TIDAK akan menyingkirkan semua MOD yang sudah diinstal dengan AutoMOD.',
	'UNKNOWN_MOD_AUTHOR-NOTES'				=> 'Tidak ada cacatan pengarang yang ditentukan.',
	'UNKNOWN_MOD_COMMENT'					=> '',
	'UNKNOWN_MOD_DESCRIPTION'				=> '',
	'UNKNOWN_MOD_DIY-INSTRUCTIONS'			=> '',
	'UNKNOWN_MOD_INLINE-COMMENT'			=> '',
	'UNKNOWN_QUERY_REVERSE'					=> 'Kueri pembalikan tidak diketahui',
	'UNRECOGNISED_COMMAND'					=> 'Kesalahan, perintah tidak dikenali %s',
	'UPDATE_AUTOMOD'						=> 'Perbarui AutoMOD',
	'UPDATE_AUTOMOD_CONFIRM'				=> 'Silahkan konfirmasi jika anda ingin membarui AutoMOD.',
	'UPLOAD'								=> 'Unggah',

	'VERSION'								=> 'Versi',

	'WRITE_DIRECT_FAIL'						=> 'AutoMOD tidak dapat menyalin file %s ke tempatnya dengan menggunakan metode langsung.  Silahkan menggunakan metode penulisa yang lain dan cobalah lagi.',
	'WRITE_DIRECT_TOO_SHORT'				=> 'AutoMOD tidak bisa menyelesaikan penulisan file %s.  Ini bisa diselesaikan dengan menekan tombol Coba Lagi.  Jika hal ini tidak berfungsi, cobalah menggunakan metode penulisan yang lain.',
	'WRITE_MANUAL_FAIL'						=> 'AutoMOD tidak dapat menambahkan file %s ke sebuah kompresi arsip.  Silahkan mencoba metode penulisan yang lain.',
	'WRITE_METHOD'							=> 'Metode Penulisan',
	'WRITE_METHOD_DIRECT'					=> 'Langsung',
	'WRITE_METHOD_EXPLAIN'					=> 'Anda bisa menentukan metode penulisan yang inginkan.  Pilihan yang paling cocok untuk saat ini adalah “Compressed File Download”.',
	'WRITE_METHOD_FTP'						=> 'FTP',
	'WRITE_METHOD_MANUAL'					=> 'Compressed File Download',

	'after add'								=> 'Tambahkan Setelah',

	'before add'							=> 'Tambahkan Sebelum',

	'find'									=> 'Cari',

	'in-line-after-add'						=> 'Sebaris Setelah, Tambahkan',
	'in-line-before-add'					=> 'Sebaris Sebelum, Tambahkan',
	'in-line-edit'							=> 'Sebaris Cari',
	'in-line-operation'						=> 'Sebaris Tambahkan',
	'in-line-replace'						=> 'Sebaris Gantikan',
	'in-line-replace-with'					=> 'Sebaris Gantikan',

	'operation'								=> 'Tambahkan',

	'replace'								=> 'Gantikan Dengan',
	'replace with'							=> 'Gantikan Dengan',
));
