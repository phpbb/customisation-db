<?php
/**
*
* help_faq [English]
*
* @package Titania language
* @version $Id: help_faq.php
* @author: RMcGirr83
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
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
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$help = array(
	array(
		0 => '--',
		1 => 'Apa itu Titania (dikenal sebagai Database Kostumisasi)'
	),
	array(
		0 => 'Apa itu Titania?',
		1 => 'Titania (dikenal sebagai Database Kostumisasi) adalah sebuah database dimana pengguna bisa mengunduh modifikasi dan gaya untuk forum phpBB.  Anda juga bisa memastikan bahwa modifikasi ataupun gaya yang anda unduh telah melewati persayaratan uji pengesahan phpBB.'
	),
	array(
		0 => 'Pengesahan? Apa itu?',
		1 => 'Semua dan setiap modifikasi ataupun gaya yang anda unduh pada Titania telah menjalani proses pengesahan.  Pengesahan berarti bahwa modifikasi ataupun gaya telah menjalani beberapa penelitian cermat terhadap kode yang diikutsertakan bersamaan dengan uji cobanya untuk meyakinkan bahwa modifikasi ataupun gaya bisa diinstal dan bekerja dengan beberapa versi forum phpBB.  Pengesahan yang diberikan kepada anda dengan tingkat kenyamanan dalam mengetahui bahwa anda tidak mengunduh/menginstal sebuah modifikasi ataupun gaya yang bisa menyebabkan forum anda dihack.'
	),
	array(
		0 => '--',
		1 => 'Bagaimana mengggunakan Titania',
	),
	array(
		0 => 'Mencari Kontribusi',
		1 => 'Ada banyak kontribusi yang bisa dicari.  Pada halaman utama Database Kostumisasi anda bisa melihat kategori yang sudah tersedia beserta modifikasi ataupun gaya yang terakhir disetujui di dalam database.'
	),
	array(
		0 => 'Mencari Modifikasi',
		1 => 'Anda juga bisa secara langsung menuju tipe modifikasi pada kategori dimana kontribusi tersebut ditempatkan yang dikelompokkan atas (Alat, Komunikasi, Keamanan, Hiburan, dsb) ataupun dengan menggunkan fitur pencarian pada bagian atas halaman.  Jika anda menggunakan fitur pencarian, anda bisa menggunakan wildcard bersamaan dengan nama kontribusinya (ataupun sebagian nama saja) dan juga nama pengarang kontribusi.  Setelah anda menemukan kostumisasi yang anda inginkan maka anda akan diarahkan pada halaman “Keterangan Kontribusi” dimanan anda dapat mencari unduhan dari berbagai versi kostumisasi pada bagian “Revisi”.'
	),
	array(
		0 => 'Mencari Gaya',
		1 => 'Sama halnya dengan pencarian modifikasi, Titania juga mengijinkan anda untuk mencari gaya, paket smiley, gambar ranking dan item-item lainnya.  Fitur pencarian juga bis anda gunakan dengan wildcard ataupun mencari nama pengarangnya.  Setelah anda menemukan item yang anda inginkan maka anda akan diarahkan pada halaman “Keterangan Kontribusi” dimanan anda dapat mencari unduhan dari berbagai versi kostumisasi pada bagian “Revisi”.'
	),
	array(
		0 => '--',
		1 => 'Bantuan Kostumisasi'
	),
	array(
		0 => 'Peraturan',
		1 => 'Dengan pengenalan terhadap Titania, peraturan yang dilibatkan untuk penggunaannya sangatlah sederhana.  Sebagaimana telah tercantum terdahulu, yang menyebutkan “Anda harus mencari bantuan di dalam topik modifikasi/gaya di tempat anda menemukan kostumisasinya” per item.  Sewaktu tim pembantu dari phpBB.com melakukan yang terbaik untuk membantu anda menjalankan dan tidak bisa menggunakan forum anda, dan tidak diharapkan untuk memberikan bantuan untuk banyak kostumisasi/kontribusi.  Adalah harapan phpBB bahwa pengarang dari kontribusi yang diberikan kepada anda, pengguna, dengan bantuan dalam penggunaan kostumisasi mereka.  Mohon dicatat bahwa semua pengarang adalah relawan yang menyempatkan waktu mereka untuk meningkatkan perangkat lunatk phpBB.  Ada pernyataan “Anda akan mendapatkan banyak lalat dengan menggunakan madu daripada menggunakan cuka” juga akan digunakan, jadi mohon diingat pada saat meminta bantuan untuk sebuah kostumisasi (seperti, sopanlah pada saat anda meminta).'
	),
	array(
		0 => 'Bagaimana mendapatkan bantuan',
		1 => 'Setiap kostumisasi menyajikan sebuah metode bantuan untuk anda.  Setiapnya adalah kemampuan dari pengarang untuk membuat post FAQ mengenai kostumisasi sebagaimana sebuah tempat diskusi/bantuan untuk tipe bantuan satu lawan satu.  Bantuan ini bisa dalam bentuka apa saja mulai dari bantuan mengenai instalasi dan bahkan memberikan addon tambahan bagi anda untuk meningkatkan kostumisasi.  Untuk mengakses tempat ini, klik bagian tab kostumisasi yang akan bertulisakan  “Diskusi/Bantuan”.  Setelah anda mengakses area ini, anda bisa membuat post pertanyaan ataupun komentar kepada pengarang.  Mohon diingat bahwa pengarang tidak memiliki kewajiban untuk memberikan bantuan sebagaimana mereka tidak memiliki kewajiban memberikan kostumisasi tersebut kepada anda.  Jika anda menemukan sebuah post ataupun komentar yang anda rasa tidak memiliki ketertarikan pada komunitas , silahkan menggunakan tombol “Laporkan post ini” dan moderator akan mengambil tindakan yang diperlukan.'
	),
	// This block will switch the FAQ-Questions to the second template column
	// Authors corner!!
	array(
		0 => '--',
		1 => '--'
	),
	array(
		0 => '--',
		1 => 'Membuat dan Mengatur Kontribusi'
	),
	array(
		0 => 'Membuat Kontribusi',
		1 => 'Seperti beberapa kontribusi, pengarang diminta untuk mengikuti beberapa panduan pada saat mengajukan kontribusinya.  Bagian <a href="http://area51.phpbb.com/docs/coding-guidelines.html">Panduan Pengkodean</a>, walaupun tampak agak menyeramkan, tapi itu adalah teman sesungguhnya buat anda.  Panduan tersebut harus diikuti sedekat mungkin yang mungkin akan membantu untuk mendapatkan kontribusi anda yang akan dipublikasikan ke komunitas.  Dalam hal MOD, bagian <a href="http://www.phpbb.com/mods/mpv/">phpBB MOD pre-validator</a> (dikenal dengan nama “MPV”) akan dijalankan terhadap revisi yang diajukan dan akan memeriksa banyak hal seperti kebenaran lisensi, versi phpBB yang digunakan dan versi <a href="http://www.phpbb.com/mods/modx/">MODX</a>.'
	),
	array(
		0 => 'Mengajukan sebuah Kontribusi',
		1 => 'So you’ve made a contribution.  Let’s get that puppy published!!<br /><br />To submit a contribution, go to the Customisation Database and within that page you will find an image link that states “New Contribution”.  Once clicked on you will be able to enter in the contribution name, select the contribution type, add some wording to describe the contribution (smilies and bbcode is allowed), select the category(ies) that the contribution fits into, add co-authors (if any) and screenshots as well.  Please keep in mind that as you are submitting the contribution, it is your name the contribution will be aligned with.'
	),
	array(
		0 => 'Managing Contributions',
		1 => 'Once your contribution is uploaded successfully into Titania, you are able to manage it.  After selecting your contribution by clicking on "My Contributions" at the top of the page, you may add additional information to it via the "Manage Contribution" tab.  You are able to amend the description of the contribution, upload screen shots, change ownership of the contribution (please note this is irreversible so ensure you really want to give another user ownership of your contribution), change the categories the contribution fits under as well as input a demo url so users can see firsthand what the contribution looks like and how it works.'
	),
	array(
		0 => 'Submitting a new Revision',
		1 => 'You can upload new revisions on the main page, the “Contribution Details” section, of your customisation.  Once you click on the “New Revision” link, you are presented a page where in you upload the revision, assign it a version and input notes to the validation team (bbcode and smilies are allowed).  You can also choose to have the validation team “repack” the modification.  Repacking involves making minor fixes to the customisation.  This may involve corrections to the MODX install file or even minor code changes.  Repacking is <strong>not</strong> having the validation team re-write major snippets of the code you supplied, that would be your “job”.<br /><br />The rules, as they apply concerning creation of a customisation, still apply when submitting revisions to your customisation.  That is, the <a href="http://www.phpbb.com/mods/mpv/">phpBB MOD pre-validator</a> (aka “MPV”) will be run against the revision of the customisation and will check for things such as correct licensing, current phpBB version and current <a href="http://www.phpbb.com/mods/modx/">MODX</a> version.    '
	),
	array(
		0 => '--',
		1 => 'Giving Support'
	),
	array(
		0 => 'FAQ',
		1 => 'Each customisation provides to the author the ability to submit FAQ type of topics.  These topics that you create should be written in a way that a user can understand and apply the topic to the customisation, whether the topic be concerning how to get the customisation installed, accessing features of the customisation, etc.  It should be noted that this area is just for you.  Users can not edit or reply to FAQ entries.'
	),
	array(
		0 => 'Support Forum',
		1 => 'Please keep in mind that users will ask questions or make comments concerning your contribution.  We ask that you support your contribution as much as you can.  We realize that you spent your free time in creating your contribution and that real life can, sometimes, get in the way of fun.  We just ask that you as the author(s) provide as much support as is possible.  If you run across a post or comment that you feel is not in the best interests of the community, please feel free to use the “Report this post” button and a moderator will take the appropriate action necessary.'
	),
	array(
		0 => '--',
		1 => 'Validation'
	),
	array(
		0 => 'My Customisation didn’t pass the pre validator check',
		1 => 'Remember, every customisation MUST have the correct licensing (currenty GNU GPL version 2), the correct version of phpBB software and the correct MODX version.  If your customisation does not have these rudimentary items then it cannot be accepted into the database.  Some errors are simply warnings and may not need fixing, if you are unsure of the problem feel free to continue with the submission and a validator will handle it.'
	),
	array(
		0 => 'My Customisation passed pre validation, now what?',
		1 => 'Once a customisation is accepted into the database, if it is a modfication is then up to the relevant team who will validate your contribution.  You may find that you get a message stating your customisation was denied.  Please don’t fret.  We know things get overlooked or just simply missed.  Not to worry.  The message you receive will contain items that were found.  These items may suggest changes to the code or images and may even suggest changes on “user friendliness”.  Generally speaking, “user friendliness” suggestions are just that...suggestions.  The most important part of any customisation is security, not in what it looks like to the end user.<br /><br />If no items were found during validation of your contribution you will get a PM stating that your contribution has been accepted into the database.  It is now time to relax a bit and revel in the knowledge that you have made a contribution to the open source community.<br /><br />No matter the outcome of the validation, we appreciate the time and effort you have exerted in sharing your contribution.'
	),
	array(
		0 => 'Who will be validating my contribution?',
		1 => 'If it is a Modification it will be validated by the MOD Team and Junior MOD Validators or occassionally a Development Team Member. For a style it will be validated by the Styles Team and Junior Style Validators. For a convertor it will be validated by a Support or Development Team Member. For a bridge it will be validated by a MOD or Development Team Member. Translations are all checked by the Translations & IST Manager, ameeck. Offical Tools are tested and created by the phpBB.com Teams.'
	),
);

?>