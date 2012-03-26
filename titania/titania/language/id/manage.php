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
	'ADMINISTRATION'			=> 'Administrasi',
	'ALTER_NOTES'				=> 'Ubah Catatan',
	'APPROVE'					=> 'Setujui',
	'APPROVE_QUEUE'				=> 'Setujui',
	'APPROVE_QUEUE_CONFIRM'		=> 'Apakah anda yakin ingin <strong>menyetujui</strong> item ini?',
	'ATTENTION'					=> 'Perhatian',

	'CATEGORY_NAME_CLEAN'		=> 'URL Kategori',
	'CHANGE_STATUS'				=> 'Ubah Status/Pindahkan',
	'CLOSED_ITEMS'				=> 'Item Ditutup',

	'DELETE_QUEUE'				=> 'Hapus Entri Antrian',
	'DELETE_QUEUE_CONFIRM'		=> 'Apakah anda yakin ingin menghapus entri antrian ini?  Semua post untuk antrian akan hilang dan revisi akan dibuat menjadi yang baru.',
	'DENY'						=> 'Tolak',
	'DENY_QUEUE'				=> 'Tolak',
	'DENY_QUEUE_CONFIRM'		=> 'pakah anda yakin ingin <strong>menolak</strong> item ini?',

	'EDIT_VALIDATION_NOTES'		=> 'Ubah Catatan Pengesahan',

	'MANAGE_CATEGORIES'			=> 'Atur Kategori',
	'MARK_IN_PROGRESS'			=> 'Tandai "Sedang Dalam Proses"',
	'MARK_NO_PROGRESS'			=> 'Hapus Tanda "Sedang Dalam Proses"',
	'MOVE_QUEUE'				=> 'Pindahkan Antrian',
	'MOVE_QUEUE_CONFIRM'		=> 'Pilih ke lokasi antrian baru dan konfirmasikan.',

	'NO_ATTENTION'				=> 'Tidak ada item yang membutuhkan perhatian.',
	'NO_ATTENTION_ITEM'			=> 'Item yang diperhatikan tidak ada.',
	'NO_ATTENTION_TYPE'			=> 'Tipe perhatian yang tidak tepat.',
	'NO_NOTES'					=> 'Tidak Ada Catatan',
	'NO_QUEUE_ITEM'				=> 'Item antrian tidak ada.',

	'OLD_VALIDATION_AUTOMOD'	=> 'Uji Automod dari pra pengepakan ulang',
	'OLD_VALIDATION_MPV'		=> 'Catatan MPV dari pra pengepakan ulang',
	'OPEN_ITEMS'				=> 'Buka Itema',

	'PUBLIC_NOTES'				=> 'Catatan rilis publik',

	'QUEUE_APPROVE'				=> 'Menunggu Persetujuan',
	'QUEUE_ATTENTION'			=> 'Perhatian',
	'QUEUE_DENY'				=> 'Penolakn Penungguan',
	'QUEUE_DISCUSSION_TOPIC'	=> 'Antrian Topik Diskusi',
	'QUEUE_NEW'					=> 'Baru',
	'QUEUE_REPACK'				=> 'Antrian Pengepakan Ulang',
	'QUEUE_REPACK_ALLOWED'		=> 'Pengepakan Ulang Diijinkan',
	'QUEUE_REPACK_NOT_ALLOWED'	=> 'Pengepakan Ulang <strong>Tidak</strong> Diijinkan',
	'QUEUE_REPLY_APPROVED'		=> 'Revisi %1$s [b]disetujui[/b] dengan alasan:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_DENIED'		=> 'Revisi %1$s [b]ditolak[/b] dengan alasan:<br /><br />[quote]%2$s[/quote]',
	'QUEUE_REPLY_IN_PROGRESS'	=> 'Tandai sedang dalam proses',
	'QUEUE_REPLY_MOVE'			=> 'Dipindahkan dari %1$s ke %2$s',
	'QUEUE_REPLY_NO_PROGRESS'	=> 'Buang tanda sedang dalam proses',
	'QUEUE_REVIEW'				=> 'Peninjauan antrian',
	'QUEUE_STATUS'				=> 'Status antrian',
	'QUEUE_TESTING'				=> 'Mencoba',
	'QUEUE_VALIDATING'			=> 'Mengesahkan',

	'REBUILD_FIRST_POST'		=> 'Bangun ulang post pertama',
	'REPACK'					=> 'Paketkan Ulang',
	'REPORTED'					=> 'Dilaporkan',
	'RETEST_AUTOMOD'			=> 'Coba ulang Automod',
	'RETEST_MPV'				=> 'Coba ulang MPV',
	'REVISION_REPACKED'			=> 'Versi ini sudah dipaketkan ulangThis revision has been repacked.',

	'SUBMIT_TIME'				=> 'Waktu Pengajuan',

	'UNAPPROVED'				=> 'Tidak Disetujui',
	'UNKNOWN'					=> 'Tidak Diketahui',

	'VALIDATION'				=> 'Pengesahan',
	'VALIDATION_AUTOMOD'		=> 'Percobaan Automod',
	'VALIDATION_MESSAGE'		=> 'Pesan/Alasan Pengesahan',
	'VALIDATION_MPV'			=> 'Catatan MPV',
	'VALIDATION_NOTES'			=> 'Catatan Pengesahan',
	'VALIDATION_QUEUE'			=> 'Antrian Pengesahan',
	'VALIDATION_SUBMISSION'		=> 'Pengajuan Pengesahan',
));
