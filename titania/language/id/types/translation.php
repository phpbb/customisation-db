<?php
/**
*
* @package Titania
* @version $Id: converter.php 1556 2010-06-15 00:25:31Z exreaction $
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
	'COULD_NOT_FIND_TRANSLATION_ROOT'			=> 'Kami tidak bisa menemukan direktori induk dari paket bahasa anda. Pastikan anda memiliki direktori yang mengandung <code>language/</code> dan <code>styles/</code> pada tingkat utama.',

	'MISSING_FILE'								=> 'File <code>%s</code> hilang dalam paket bahasa anda',
	'MISSING_KEYS'								=> 'Anda kehilangan kunci-kunci bahasa sebagai berikut <code>%1$s</code>:<br />%2$s',

	'PASSED_VALIDATION'							=> 'Paket bahasa anda telah melewati uji pengesahan yang memeriksa bagian kunci yang hilang, lisensi dan pemaketan ulang terjemahan, silahkan lanjutkan.',

	'TRANSLATION'								=> 'Terjemahan',
	'TRANSLATION_VALIDATION'					=> '[Pengesahan-Terjemahan phpBB] %1$s %2$s',
	'TRANSLATION_VALIDATION_MESSAGE_APPROVE'	=> 'Terima kasih atas pengajuan Terjemahan anda ke Database Kostumisasi phpBB.com. Setelah melakukan pemeriksaan dengan teliti, terjemahan anda telah disetejui dan dirilis ke dalam Database Kostumisasi kami.

Harapan kami semoga anda bisa memberikan bantuan yang paling mendasar atas terjemahan ini dan tetap membarui dengan rilis di masa yang akan datang dari phpBB. Kami menghargai semua usaha dan kontribusi anda kepada komunitas. Pengarang seperti anda bisa membuat phpBB.com menjadi sebuah tempat untuk semua orang.

[b]Catatan dari Tim tentang terjemahan anda:[/b]
[quote]%s[/quote]

Sincerely,
phpBB Teams',
	'TRANSLATION_VALIDATION_MESSAGE_DENY'		=> 'Hallo,

Sebagaimana apa yang anda ketahui mengenai semua terjemahan yang diajukan di Database Kostumisasi phpBB harus disahkan dan disetujui oleh anggota dari Team phpBB.

Selama pengesahan terjemahan anda, Tim phpBB sangat menyesal memberitahukan kepada anda bahwa terjemahan anda kami tolak.

Untuk menyelesaikan permasalahan atas terjemahan anda, silahkan ikuti instruksi berikut ini:
[list=1][*]Buat perubahan yang diperlukan untuk memperbaiki setiap permasalah (dilampirkan di bawah ini) yang bisa menjadikan terjemahan anda ditolak.
[*]Unggah ulang terjemahan anda ke Database Kostumisasi kami.[/list]
Mohon dipastikan bahwa anda sudah mencoba terjemahan pada versi terbaru phpBB (lihat halaman [url=http://www.phpbb.com/downloads/]Unduh[/url]) sebelum anda mengajukan ulang terjemahan.

Jika anda merasa penolakan ini tidak beralasan, silahkan menghubungi Translation Manager.

Berikut adalah keterangan kenapa terjemahan anda ditolak:
[quote]%s[/quote]

Terima kasih,
Tim phpBB',
));
