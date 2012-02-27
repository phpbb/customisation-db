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
	'STYLE'								=> 'Gaya',
	'STYLES'							=> 'Gaya-Gaya',
	'STYLE_CREATE_PUBLIC'				=> '[b]Nama gaya[/b]: %1$s
[b]Pengarang:[/b] [url=%2$s]%3$s[/url]
[b]Deskripsi gaya[/b]: %4$s
[b]Versi gaya[/b]: %5$s
[b]Dicoba pada versi phpBB[/b]: Lihat di bawah

[b]Unduh file[/b]: [url=%6$s]%7$s[/url]
[b]Ukuran file:[/b] %8$s Bita

[b]Style overview page:[/b] [url=%9$s]View[/url]

[color=blue][b]Tim phpBB tidak bertanggung jawab ataupun berkewajiban untuk memberikan bantuan untuk gaya ini. Dengan menginstal gaya ini, anda setuju bahwa Tim Support phpBB tidak bertanggung jawab untuk memberikan bantuan atas gaya ini.[/b][/color]

[size=150][url=%10$s]--&gt;[b]Style support[/b]&lt;--[/url][/size]',
	'STYLE_DEMO_INSTALL'				=> 'Iinstal di papan demo gaya',
	'STYLE_QUEUE_TOPIC'					=> '[b]Nama gaya[/b]: %1$s
[b]Pengarang:[/b] [url=%2$s]%3$s[/url]
[b]Deskripsi gaya[/b]: %4$s
[b]Versi gaya[/b]: %5$s

[b]Unduh file[/b]: [url=%6$s]%7$s[/url]
[b]Ukuran file:[/b] %8$s Bita',
	'STYLE_REPLY_PUBLIC'				=> '[b][color=darkred]Gaya Disahkan/dirilis[/color][/b]',
	'STYLE_REPLY_PUBLIC_NOTES'			=> '

[b]Catatan: %s[/b]',
	'STYLE_UPDATE_PUBLIC'				=> '[b][color=darkred]Gaya Dibarui ke versi %1$s
Lihat post untuk tautan Unduh[/color][/b]',
	'STYLE_UPDATE_PUBLIC_NOTES'			=> '

[b]Notes:[/b] %1$s',
	'STYLE_UPLOAD_AGREEMENT'			=> '// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
// translators to re-order the output of data while ensuring it remains correct',
	'STYLE_VALIDATION'					=> '[Pengesahan-Gaya phpBB] %1$s %2$s',
	'STYLE_VALIDATION_MESSAGE_APPROVE'	=> 'Terima kasih telah mengajukan gaya pada database gaya phpBB.com. Setelah pemeriksaan dengan cermat maka Tim Sytle telah menyetujui dan merilis gaya anda pada database gaya kami.

Harapan kami semoga anda bisa memberikan bantuan yang paling mendasar atas gaya ini dan tetap membarui dengan rilis di masa yang akan datang dari phpBB. Kami menghargai semua usaha dan kontribusi anda kepada komunitas. Pengarang seperti anda bisa membuat phpBB.com menjadi sebuah tempat untuk semua orang.

[b]Catatan dari Tim tentang konvertor anda:[/b]
[quote]%s[/quote]

Hormat kami,',
	'STYLE_VALIDATION_MESSAGE_DENY'		=> 'Hallo,

Sebagaimana apa yang anda ketahui mengenai semua gaya yang diajukan di Database Kostumisasi phpBB harus disahkan dan disetujui oleh anggota dari Team phpBB..

Selama pengesahan konvertor anda, Tim phpBB sangat menyesal memberitahukan kepada anda bahwa konvertor anda kami tolak. Untuk menyelesaikan permasalahan atas konvertor anda, silahkan ikuti instruksi berikut ini::
[quote]%s[/quote]

Jika anda ingin mengajukan ulang gaya ini ke database gaya kami, mohon dipastikan bahwa anda telah memperbaiki masalah yang ditemukan dan cocok dengan [url=http://www.phpbb.com/community/viewtopic.php?t=988545]Kebijaksanaan Pengajuan Gaya[/url].

Jika anda merasa penolakan ini tidak beralasan, silahkan menghubungi Styles Team Leader.

Terima kasih,
Tim Style phpBB',
));
