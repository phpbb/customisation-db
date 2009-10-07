<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 *
 * Ελληνική μετάφραση από την ομάδα του phpbbgr.com
 * http://phpbbgr.com
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
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
	'ACTION'						=> 'Ενέργεια',
	'ADVANCED'						=> 'Προηγμένες',
	'AUTH_CACHE_PURGE'				=> 'Εκκαθάριση της λανθάνουσας μνήμης Αρχής πιστοποίησης',

	'CACHE_PURGE'					=> 'Εκκαθάριση λανθάνουσας μνήμης της Δ.Συζήτησης',
	'CONFIGURE'						=> 'Ρυθμίσεις',
	'CONFIG_ADD'					=> 'Προσθήκη νέας μεταβλητής ρυθμίσεων: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'ΣΦΑΛΜΑ: η μεταβλητή ρυθμίσεων %s υπάρχει ήδη.',
	'CONFIG_NOT_EXIST'				=> 'ΣΦΑΛΜΑ: η μεταβλητή ρυθμίσεων %s δεν υπάρχει.',
	'CONFIG_REMOVE'					=> 'Αφαίρεση μεταβλητής ρυθμίσεων: %s',
	'CONFIG_UPDATE'					=> 'Ενημέρωση μεταβλητής ρυθμίσεων: %s',

	'DISPLAY_RESULTS'				=> 'Εμφάνιση πλήρους αποτελέσματος',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Επιλέξτε ναι για εμφάνιση όλων των ενεργειών και των αποτελεσμάτων κατα τη διάρκεια της ζητούμενης ενέργειας.',

	'ERROR_NOTICE'					=> 'Ένα ή περισσότερα σφάλματα προέκυψαν κατα την ζητούμενη ενέργεια. Παρακαλώ μεταφορτώστε <a href="%1$s">αυτό το αρχείο</a> με τα σφάλματα που φαίνονται μέσα του και ζητήστε τη βοήθεια του συγγραφέα του mod.<br /><br />Άν έχετε οποιοδήποτε πρόβλημα στη μεταφόρτωση του αρχείου αυτού μπορείτε να το προσπελάσετε απευθείας με ένα περιηγητή FTP στην ακόλουθη τοποθεσία: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Ένα ή περισσότερα σφάλματα προέκυψαν κατα τη ζητούμενη ενέργεια. Παρακαλώ κάντε μία πλήρη εγγραφή των σφαλμάτων και ζητήστε τη βοήθεια του συγγραφέα του mod.',

	'FAIL'							=> 'Αποτυχία',
	'FILE_COULD_NOT_READ'			=> 'ΣΦΑΛΜΑ: Δεν ήταν δυνατό να ανοιχτεί το αρχείο %s για ανάγνωση.',
	'FOUNDERS_ONLY'					=> 'Πρέπει να είστε Ιδρυτής για να έχετε πρόσβαση στη σελίδα.',

	'GROUP_NOT_EXIST'				=> 'Η ομάδα δεν υπάρχει',

	'IGNORE'						=> 'Αγνόηση',
	'IMAGESET_CACHE_PURGE'			=> 'Ανανέωση συνόλου εικόνων %s',
	'INSTALL'						=> 'Εγκατάσταση',
	'INSTALL_MOD'					=> 'Εγκατάσταση %s',
	'INSTALL_MOD_CONFIRM'			=> 'Είστε έτοιμοι να εγκαταστήσετε το %s?',

	'MODULE_ADD'					=> 'Προσθήκη %1$s μονάδας: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'ΣΦΑΛΜΑ: Η μονάδα υπάρχει ήδη.',
	'MODULE_NOT_EXIST'				=> 'ΣΦΑΛΜΑ: Η μονάδα δεν υπάρχει.',
	'MODULE_REMOVE'					=> 'Αφαίρεση %1$s μονάδας: %2$s',

	'NONE'							=> 'Κανένα',
	'NO_TABLE_DATA'					=> 'ΣΦΑΛΜΑ: Δεν καθορίστηκαν δεδομένα πινάκων',

	'PARENT_NOT_EXIST'				=> 'ΣΦΑΛΜΑ: Η κατηγορία γονέας που καθορίστηκε για αυτή την μονάδα δεν υπάρχει.',
	'PERMISSIONS_WARNING'			=> 'Οι νέες ρυθμίσεις δικαιωμάτων έχουην προστεθεί. Παρακαλώ ελέγξτε τις ρυθμίσεις δικαιωμάτων και σιγουρευτείτε οτι είναι έτσι οπως πρέπει.',
	'PERMISSION_ADD'				=> 'Προσθήκη νέας επιλογής δικαιωμάτων: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'ΣΦΑΛΜΑ: Η επιλογή δικαιωμάτων %s υπάρχει ήδη.',
	'PERMISSION_NOT_EXIST'			=> 'ΣΦΑΛΜΑ: Η επιλογή δικαιωμάτων %s δεν υπάρχει.',
	'PERMISSION_REMOVE'				=> 'Αφαίρεση επιλογής δικαιωμάτων: %s',
	'PERMISSION_SET_GROUP'			=> 'Καθορισμός δικαιωμάτων για την %s ομάδα.',
	'PERMISSION_SET_ROLE'			=> 'Καθορισμός δικαιωμάτων για τον %s ρόλο.',
	'PERMISSION_UNSET_GROUP'		=> 'Αφαίρεση δικαιωμάτων για την %s ομάδα.',
	'PERMISSION_UNSET_ROLE'			=> 'Αφαίρεση δικαιωμάτων για τον %s ρόλο.',

	'ROLE_NOT_EXIST'				=> 'Ο ρόλος δεν υπάρχει',

	'SUCCESS'						=> 'Επιτυχία',

	'TABLE_ADD'						=> 'Προσθήκη νέου πίνακα στη Β. Δεδομένων: %s',
	'TABLE_ALREADY_EXISTS'			=> 'ΣΦΑΛΜΑ: Ο πίνακας %s υπάρχει ήδη στη βάση.',
	'TABLE_COLUMN_ADD'				=> 'Προσθήκη νέας στήλης με όνομα %2$s στον πίνακα %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'ΣΦΑΛΜΑ: Η στήλη %2$s υπάρχει ήδη στον πίνακα %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'ΣΦΑΛΜΑ: Η στήλη %2$s δεν υπάρχει στον πίνακα %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Αφαίρεση της στήλης με όνομα %2$s από τον πίνακα %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Ενημέρωση στήλης με όνομα %2$s στον πίνακα %1$s',
	'TABLE_KEY_ADD'					=> 'Προσθήκη κλειδιού με όνομα %2$s στον πίνακα %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'ΣΦΑΛΜΑ: Το ευρετήριο %2$s υπάρχει ήδη στον πίνακα %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'ΣΦΑΛΜΑ: Το ευρετήριο %2$s δεν υπάρχει στον πίνακα %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Αφαίρεση ενός κλειδιού με όνομα %2$s από τον πίνακα %1$s',
	'TABLE_NOT_EXIST'				=> 'ΣΦΑΛΜΑ: Ο πίνακας %s δεν υπάρχει στη βάση.',
	'TABLE_REMOVE'					=> 'Αφαίρεση πίνακα Β.Δεδομένων: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Εισαγωγή δεδομένων στον %s πίνακα.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Αφαίρεση σειράς %s από τον πίνακα',
	'TABLE_ROW_UPDATE_DATA'			=> 'Ενημέρωση σειράς στον %s πίνακα.',
	'TEMPLATE_CACHE_PURGE'			=> 'Ανανέωση του πρωτύπου %s',
	'THEME_CACHE_PURGE'				=> 'Ανανέωση του θέματος %s',

	'UNINSTALL'						=> 'Απεγκατάσταση',
	'UNINSTALL_MOD'					=> 'Απεγκατάσταση %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Είστε έτοιμος να απεγκαταστήσετε %s?  Όλες οι ρυθμίσεις και τα δεδομένα από αυτό το mod θα χαθούν!',
	'UNKNOWN'						=> 'Άγνωστο',
	'UPDATE_MOD'					=> 'Ενημέρωση %s',
	'UPDATE_MOD_CONFIRM'			=> 'Είστε έτοιμος να ενημερώσετε %s?',
	'UPDATE_UMIL'					=> 'Αυτή η έκδοση του UMIL είναι παλιά.<br /><br />Παρακαλώ μεταφορτώστε την νεότερη UMIL (Ενοποιημένη βιβλιοθήκη εγκατάστασης MOD) από: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'Έκδοση Mod: <strong>%1$s</strong><br />Εγκατεστημένη: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Επιλογή έκδοσης',
	'VERSION_SELECT_EXPLAIN'		=> 'Μην αλλάζετε από “Αγνόηση” εκτός αν ξέρετε τι κάνετε ή σας έχουν πεί.',
));

?>