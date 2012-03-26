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
	'CONVERTER'								=> 'Konvertor',
	'CONVERTERS'							=> 'Konvertor',
	'CONVERTER_VALIDATION'					=> '[Pengesahan-Konvertor phpBB] %1$s %2$s',
	'CONVERTER_VALIDATION_MESSAGE_APPROVE'	=> 'Terima kasih telah mengajukan konvertor anda ke Kostumisasi Database phpBB.com. Setalah pemeriksaan dengan teliti, konvertor yang anda ajukan telah disetujui dan dirilis di Kostumisasi Database kami.

Harapan kami semoga anda bisa memberikan bantuan yang paling mendasar atas konvertor ini dan tetap membarui dengan rilis di masa yang akan datang dari phpBB. Kami menghargai semua usaha dan kontribusi anda kepada komunitas. Pengarang seperti anda bisa membuat phpBB.com menjadi sebuah tempat untuk semua orang.

[b]Catatan dari Tim tentang konvertor anda:[/b]
[quote]%s[/quote]

Hormat kami,
Tim phpBB',
	'CONVERTER_VALIDATION_MESSAGE_DENY'		=> 'Hallo,

Sebagaimana apa yang anda ketahui mengenai semua konvertor yang diajukan di Database Kostumisasi phpBB harus disahkan dan disetujui oleh anggota dari Team phpBB.

Selama pengesahan konvertor anda, Tim phpBB sangat menyesal memberitahukan kepada anda bahwa konvertor anda kami tolak.

Untuk menyelesaikan permasalahan atas konvertor anda, silahkan ikuti instruksi berikut ini:
[list=1][*]Buat perubahan yang diperlukan untuk memperbaiki setiap permasalah (dilampirkan di bawah ini) yang bisa menjadikan konvertor anda ditolak.
[*]Unggah ulang konvertor anda ke Database Kostumisasi kami.[/list]
Mohon dipastikan bahwa anda sudah mencoba konvertor pada versi terbaru phpBB (lihat halaman [url=http://www.phpbb.com/downloads/]Unduh[/url]) sebelum anda mengajukan ulang konvertor.

Jika anda merasa penolakan ini tidak beralasan, silahkan menghubungi Development Leader.

Berikut adalah keterangan kenapa konvertor anda ditolak:
[quote]%s[/quote]

Terima kasih,
Tim phpBB',
));
