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
	'ACCESS'							=> 'Tingkat Akses',
	'ACCESS_AUTHORS'					=> 'Akses Pengarang',
	'ACCESS_PUBLIC'						=> 'Akses Umum',
	'ACCESS_TEAMS'						=> 'Akses Tim',
	'ATTACH'							=> 'Lampirkan',

	'FILE_DELETED'						=> 'File ini akan dihapus ketika anda mengajukannya',

	'HARD_DELETE_TOPIC_CONFIRM'			=> 'Apakan anda yakin ingin menghapus <strong>paksa</strong> topik ini?<br /><br />Topik ini akan hilang selamanya!',

	'QUEUE_DISCUSSION_TOPIC_MESSAGE'	=> 'Topik ini adalah untuk diskusi pengesahan antara pengarang kontribusi dan pengesah.

Semua yang dipost pada topik ini akan dibaca oleh yang mengesahkan kontribusi anda, jadi dimohon untuk mempost di sini daripada menggunakan pesan pribadi kepada pengesah.

Staf pengesah juga bisa memberikan post pada pengarang di sini, jadi dimohon untuk membalas dengan sesuatu yang cukup bermanfaat yang mungkin bisa diperlukan untuk memproses prosedur pengesahan.

Mohon diingat bahwa normalnya topik ini adalah bersifat pribadi antara pengarang dan pengesah dan tidak bisa dilihat oleh publik.',
	'QUEUE_DISCUSSION_TOPIC_TITLE'		=> 'Diskusi Pengesahan - %s',

	'REPORT_POST_CONFIRM'				=> 'Gunakan isian ini untuk melaporkan post yang dipilih ke moderator forum dan administrator papan. Pelaporan seharusnya digunakan apabila ada post yang telah melanggar aturan yang berlaku.',

	'SOFT_DELETE_TOPIC_CONFIRM'			=> 'Apakah anda yakin ingin menghapus <strong>perlahan</strong> topik ini?',
	'STICKIES'							=> 'Sticky',
	'STICKY_TOPIC'						=> 'Topik Sticky',

	'UNDELETE_FILE'						=> 'Batalkan Hapus',
	'UNDELETE_POST'						=> 'Post Tak Terhapus',
	'UNDELETE_POST_CONFIRM'				=> 'Apakah anda yakin ingin mengahapus post ini?',
	'UNDELETE_TOPIC_CONFIRM'			=> 'Apakah anda yakin ingin mengahapus topik ini?',
));
