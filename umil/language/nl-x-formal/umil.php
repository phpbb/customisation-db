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
 * Translation By: Raimon
 *
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
	'ACTION'						=> 'Actie',
	'ADVANCED'						=> 'Uitgebreid',
	'AUTH_CACHE_PURGE'				=> 'De auth-cache legen',

	'CACHE_PURGE'					=> 'De forum-cache legen',
	'CONFIGURE'						=> 'Configuren',
	'CONFIG_ADD'					=> 'Bezig met toevoegen van nieuwe config variable: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'FOUT: Config-variable %s bestaat al.',
	'CONFIG_NOT_EXIST'				=> 'FOUT: Config-variable %s bestaat niet.',
	'CONFIG_REMOVE'					=> 'Bezig met verwijderen van config-variable: %s',
	'CONFIG_UPDATE'					=> 'Bezig met bijwerken van config-variable: %s',

	'DISPLAY_RESULTS'				=> 'Volledige resultaten weergeven',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Selecteer ja om alle acties en resultaten tijdens de aangevraagde actie te laten weergeven.',

	'ERROR_NOTICE'					=> 'Eén of meer fouten zijn er opgetreden tijdens de aangevraagde actie. Download <a href="%1$s">dit bestand</a> en plaats alle fouten die zijn weergeven erin en vraag de MOD auteur voor verdere ondersteuning.<br /><br />Als uw enige problemen hebt met het downloaden van dat bestand, dan mag uw het direct benaderen met een FTP-browser op de volgende locatie: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Eén of meer fouten zijn er opgetreden tijdens de aangevraagde actie. Sla alle fouten goed op en vraag de MOD-auteur voor verdere ondersteuning.',

	'FAIL'							=> 'Mislukt',
	'FILE_COULD_NOT_READ'			=> 'FOUT: Kon het volgende bestand %s niet openen voor te lezen.',
	'FOUNDERS_ONLY'					=> 'U moet oprichterrechten hebben om deze pagina te kunnen betreden.',

	'GROUP_NOT_EXIST'				=> 'Groep bestaat niet',

	'IGNORE'						=> 'Negeren',
	'IMAGESET_CACHE_PURGE'			=> 'Bezig met vernieuwen van de %s afbeeldingenset',
	'INSTALL'						=> 'Installeren',
	'INSTALL_MOD'					=> 'Installeren %s',
	'INSTALL_MOD_CONFIRM'			=> 'Bent uw klaar om %s te installeren?',

	'MODULE_ADD'					=> 'Bezig met toevoegen %1$s module: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'FOUT: Module bestaat al.',
	'MODULE_NOT_EXIST'				=> 'FOUT: Module bestaat niet.',
	'MODULE_REMOVE'					=> 'Bezig met verwijderen %1$s module: %2$s',

	'NONE'							=> 'Geen',
	'NO_TABLE_DATA'					=> 'FOUT: Er is geen tabel-data opgegeven',

	'PARENT_NOT_EXIST'				=> 'FOUT: De hoofdcategorie die is opgegeven voor deze module bestaat niet.',
	'PERMISSIONS_WARNING'			=> 'Nieuwe permissieinstellingen zijn toegevoegd. Wees er zeker van om uw permissieinstellingen te controleren en te kijken of ze zo goed staan zoals uw ze wilt hebben.',
	'PERMISSION_ADD'				=> 'Bezig met toevoegen van nieuwe permissie optie: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'FOUT: Permissie optie %s bestaat al.',
	'PERMISSION_NOT_EXIST'			=> 'FOUT: Permissie optie %s bestaat niet.',
	'PERMISSION_REMOVE'				=> 'Bezig met verwijderen van permissie optie: %s',
	'PERMISSION_SET_GROUP'			=> 'Bezig met instellen van permissies voor de %s groep.',
	'PERMISSION_SET_ROLE'			=> 'Bezig met instellen van permissies voor de %s rol.',
	'PERMISSION_UNSET_GROUP'		=> 'Bezig met permissieinstellingen terug draaien van de %s groep.',
	'PERMISSION_UNSET_ROLE'			=> 'Bezig met permissieinstellingen terug draaien van de %s rol.',

	'ROLE_NOT_EXIST'				=> 'Rol bestaat niet.',

	'SUCCESS'						=> 'Voltooid',

	'TABLE_ADD'						=> 'Bezig met toevoegen van nieuwe database-tabel: %s',
	'TABLE_ALREADY_EXISTS'			=> 'FOUT: Database-tabel %s bestaat al.',
	'TABLE_COLUMN_ADD'				=> 'Bezig met toevoegen van nieuwe kolom %2$s naar tabel %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'FOUT: De kolom %2$s bestaat al op tabel %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'FOUT: De kolom %2$s bestaat niet op tabel %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Bezig met verwijderen van de kolom %2$s van tabel %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Bezig met bijwerken van kolom %2$s van tabel %1$s',
	'TABLE_KEY_ADD'					=> 'Bezig met toevoegen van sleutel %2$s naar tabel %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'FOUT: De index %2$s bestaat al op tabel %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'FOUT: De index %2$s bestaat niet op tabel %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Bezig met verwijderen van de sleutel %2$s van tabel %1$s',
	'TABLE_NOT_EXIST'				=> 'FOUT: Database-tabel %s bestaat niet.',
	'TABLE_REMOVE'					=> 'Bezig met verwijderen van de database-tabel: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Bezig met data toevoegen in de %s database-tabel.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Bezig met verwijderen van een row van de %s database-tabel',
	'TABLE_ROW_UPDATE_DATA'			=> 'Bezig met bijwerken van een row in de $s database-tabel.',
	'TEMPLATE_CACHE_PURGE'			=> 'Bezig met vernieuwen van de %s template',
	'THEME_CACHE_PURGE'				=> 'Bezig met vernieuwen van de %s thema',

	'UNINSTALL'						=> 'Deïnstalleren',
	'UNINSTALL_MOD'					=> 'Deïnstalleren %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Weet u zeker dat u %s wilt deïnstalleren? Alle instellingen en data die zijn opgeslagen door deze MOD zullen worden verwijderd.!',
	'UNKNOWN'						=> 'Onbekend',
	'UPDATE_MOD'					=> 'Bijwerken %s',
	'UPDATE_MOD_CONFIRM'			=> 'Weet u zeker dat u %s wilt bijwerken?',
	'UPDATE_UMIL'					=> 'Deze versie van UMIL is niet up-to-date.<br /><br />Download de laatste UMIL (Unified MOD Install Library) van: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'MOD-versie: <strong>%1$s</strong><br />Momenteel geïnstalleerd: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Versie selecteren',
	'VERSION_SELECT_EXPLAIN'		=> 'Verander niet “negeren” tenzei u weet wat u aan het doen bent, of als u verteld werd om dit te doen.',
));

?>