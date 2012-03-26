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
	'MODIFICATION'						=> 'Modifikasi',
	'MODIFICATIONS'						=> 'Modifikasi',
	'MOD_CREATE_PUBLIC'					=> '[b]Nama Modifikasi[/b]: %1$s
[b]Pengarang:[/b] [url=%2$s]%3$s[/url]
[b]Deskripsi Modifikasi[/b]: %4$s
[b]Versi Modifikasi[/b]: %5$s
[b]Dicoba pada versi phpBB[/b]: Lihat di bawah

[b]Unduh file[/b]: [url=%6$s]%7$s[/url]
[b]Ukuran File:[/b] %8$s Bita

[b]Halaman peninjauan modifikasi:[/b] [url=%9$s]Lihat[/url]

[color=blue][b]Tim phpBB tidak bertanggung jawab ataupun berkewajiban memberikan bantuan untuk modifikasi ini. Dengan melakukan instalasi modifikasi ini, anda menyatakan bahwa tim pembantu ataupun modifikasi phpBB kemungkinan tidak berkewajiban memberikan bantuan.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Bantuan modifikasi[/b]&lt;--[/url][/size]',
	'MOD_QUEUE_TOPIC'					=> '[b]Nama modifikasi[/b]: %1$s
[b]Pengarang:[/b] [url=%2$s]%3$s[/url]
[b]Deskripsi modifikasi[/b]: %4$s
[b]Versi modifikasi[/b]: %5$s

[b]Unduh file[/b]: [url=%6$s]%7$s[/url]
[b]Ukran file:[/b] %8$s Bita',
	'MOD_REPLY_PUBLIC'					=> '[b][color=darkred]Modifikasi disahkan/dirilis[/color][/b]',
	'MOD_REPLY_PUBLIC_NOTES'			=> '

[b]Catatan:[/b] %s',
	'MOD_UPDATE_PUBLIC'					=> '[b][color=darkred]MOD dibarui ke versi %1$s
See first post for Download Link[/color][/b]',
	'MOD_UPDATE_PUBLIC_NOTES'			=> '

[b]Notes:[/b] %1$s',
	'MOD_UPLOAD_AGREEMENT'				=> '<span style="font-size: 1.5em;">Dengan mengajukan revisi ini anda setuju dengan <a href="http://www.phpbb.com/mods/policies/">Kebijaksanaan Database MODifikasi</a> dan MOD anda sesuai dengan dan mengikuti <a href="http://code.phpbb.com/svn/phpbb/branches/phpBB-3_0_0/phpBB/docs/coding-guidelines.html">Panduan Pengkodean phpBB3</a>.

Anda juga setuju dan menerima bahwa lisensi MODifikasi dan lisensi dari semua komponen cocok dengan <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GPLv2</a> dan anda juga mengijinkan pendistribusian ulang terhadap MODifikasi anda melalui situs ini tanpa batas. Untuk daftar lisensi yang tersedia dan lisensi yang cocokk dengan GNU GPLv2, silahkan mereferensikan <a href="http://en.wikipedia.org/wiki/List_of_FSF_approved_software_licenses">daftar dari lisensi perangkat lunak yang disetujui FSF</a>.</span>',
	'MOD_VALIDATION'					=> '[Pengesahan-phpBB MOD] %1$s %2$s',
	'MOD_VALIDATION_MESSAGE_APPROVE'	=> 'Terima kasih telah mengajukan modifikasi anda ke Database Modifikasi phpBB.com. Setelah pemeriksaan dengan cermat oleh Tim MOD, modifikasi anda telah disetujui dan dirilis ke dalam Database Kostumisasi kami.

Harapan kami semoga anda bisa memberikan bantuan yang paling mendasar atas mnodifikasi ini dan tetap membarui dengan rilis di masa yang akan datang dari phpBB. Kami menghargai semua usaha dan kontribusi anda kepada komunitas. Pengarang seperti anda bisa membuat phpBB.com menjadi sebuah tempat untuk semua orang.

[b]Catatan dari Tim MOD mengenai modifikasi anda:[/b]
[quote]%s[/quote]

Hormat kami,
Tim MOD phpBB MOD',
	'MOD_VALIDATION_MESSAGE_DENY'		=> 'Hallo,

Sebagaimana apa yang anda ketahui mengenai semua modifikasi yang diajukan di Database Kostumisasi phpBB harus disahkan dan disetujui oleh anggota dari Team phpBB.

Selama pengesahan modifikasi anda, Tim phpBB sangat menyesal memberitahukan kepada anda bahwa modifikasi anda kami tolak.

Untuk memperbaiki permasalahan atas modifikasi anda, silahkan ikuti instruksi berikut ini:
[list=1][*]Buat pengubahan yang diperlukan untuk memperbaiki setiap masalah (dijelaskan di bawah) yang mengakibatkan modifikasi anda ditolak.
[*]Coba MOD anda, file XML dan instalasinya.
[*]Unggah ulang MOD anda ke database modifikasi kami.[/list]
Mohon dipastikan bahwa anda telah mencoba modifikasi anda pada versi phpBB terbaru (lihat halaman (see the [url=http://www.phpbb.com/downloads/]Unduh[/url] phpBB) sebelum anda mengajukan modifikasi anda.

Jika anda merasa penolakan ini tidak beralasan, silahkan menghubungi MOD Validation Leader.

Berikut adalah laporan kenapa modifikasi anda ditolak:
[quote]%s[/quote]

Tolong ikuti tautan berikut ini sebelum anda mengunggah ulang modifikasi:
[list]
[*][url=http://www.phpbb.com/mods/modx/]Standar MODX phpBB[/url]
[*][b]Mengamanakan MODs:[/b]
[url=http://blog.phpbb.com/2009/02/12/injection-vulnerabilities/]Pencegahan Kerentanan Injeksi[/url]
[url=http://blog.phpbb.com/2009/09/10/how-not-to-use-request_var/]Bagaimana untuk tidak menggunakan request_var[/url]
[/list]

Untuk informasi lebih lanjut, mungkin anda ingin melihat yang tertera berikut ini:
[list][*][url=http://www.phpbb.com/mods/faq/]FAQ MODifikasi[/url]
[*][url=http://www.phpbb.com/kb/3.0/modifications/]Kategori MODifikasi phpBB3 di Pengetahuan Dasar[/url][/list]

Untuk membantu penulisan MOD phpBB, silahkan lihat sumber-sumber berikut ini:
[list][*][url=http://www.phpbb.com/community/viewforum.php?f=71]Forum untuk Pengarang MOD\Bantuan[/url]
[*]IRC Support - [url=irc://irc.freenode.net/phpBB-coding]#koding-phpBB[/url] terdaftar pada FreeNode IRC network ([url=irc://irc.freenode.net/]irc.freenode.net[/url])[/list]

[b]Jika anda ingin mendiskusikan sesuatu dalam pesan pribadi ini, silahkan kirim sebuah pesan kepada kami melalui tab diskusi di database MOD, Modifikasi Saya, atur MOD ini.[/b] Jika anda merasa penolakan ini tidak beralasan silahkan menghubungi MOD Validation Leader.

Terima kasih,
Tim MOD phpBB',
));
