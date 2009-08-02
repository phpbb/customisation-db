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
 * translated by phpbb.nl ( vertaalteam@phpbb.nl )
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
	'ADVANCED'						=> 'Geavanceerd',
	'AUTH_CACHE_PURGE'				=> 'De auth cache legen',

	'CACHE_PURGE'					=> 'De forum cache legen',
	'CONFIGURE'						=> 'Configureren',
	'CONFIG_ADD'					=> 'Bezig met toevoegen van nieuwe config variabel : %s',
	'CONFIG_ALREADY_EXISTS'			=> 'FOUT: Config variabel %s bestaat al.',
	'CONFIG_NOT_EXIST'				=> 'FOUT: Config variabel %s bestaat niet.',
	'CONFIG_REMOVE'					=> 'Verwijderen config variabel: %s',
	'CONFIG_UPDATE'					=> 'Bezig met bijwerken config variabel: %s',

	'DISPLAY_RESULTS'				=> 'Toon volledige resultaten',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Selecteer ja om alle acties en resultaten te tonen tijdens de aangevraagde actie.',

	'ERROR_NOTICE'					=> 'Eén of meer fouten zijn er opgetreden tijdens de aangevraagde actie. Download <a href="%1$s">dit bestand</a> met daarin alle fouten die er worden weergegeven en vraag de MOD auteur om hulp.<br /><br />Als je problemen hebt met het downloaden van dat bestand, dan kun je het direct met een FTP-client downloaden vanaf de volgende locatie: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Eén of meer fouten zijn er opgetreden tijdens de aangevraagde actie. Maak een volledig verslag van alle fouten en vraag de MOD auteur voor hulp.',

	'FAIL'							=> 'Mislukt',
	'FILE_COULD_NOT_READ'			=> 'FOUT: Kon het bestand %s niet openen om te lezen.',
	'FOUNDERS_ONLY'					=> 'Je moet oprichters rechten hebben om deze pagina te benaderen.',

	'GROUP_NOT_EXIST'				=> 'Groep bestaat niet',

	'IGNORE'						=> 'Negeren',
	'IMAGESET_CACHE_PURGE'			=> 'Het afbeeldingenset %s wordt vernieuwd',
	'INSTALL'						=> 'Installeren',
	'INSTALL_MOD'					=> 'Installeren %s',
	'INSTALL_MOD_CONFIRM'			=> 'Ben je klaar om %s te installeren?',

	'MODULE_ADD'					=> 'Bezig met toevoegen %1$s module: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'FOUT: Module bestaat al.',
	'MODULE_NOT_EXIST'				=> 'FOUT: Module bestaat niet.',
	'MODULE_REMOVE'					=> 'Bezig met verwijderen %1$s module: %2$s',

	'NONE'							=> 'Geen',
	'NO_TABLE_DATA'					=> 'FOUT: Er is geen tabel data opgegeven',

	'PARENT_NOT_EXIST'				=> 'FOUT: De hoofdcategorie die is opgegeven voor deze module bestaat niet.',
	'PERMISSIONS_WARNING'			=> 'Nieuwe permissie instellingen zijn toegevoegd. Zorg er voor dat je de permissie instellingen nakijkt en bekijk of ze zijn zoals jij ze wilt hebben.',
	'PERMISSION_ADD'				=> 'Bezig met toevoegen van nieuwe permissie optie: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'FOUT: Permissie optie %s bestaat al.',
	'PERMISSION_NOT_EXIST'			=> 'FOUT: Permissie optie %s bestaat niet.',
	'PERMISSION_REMOVE'				=> 'Bezig met verwijderen van permissie optie: %s',
	'PERMISSION_SET_GROUP'			=> 'Bezig met instellen van permissies voor de %s groep.',
	'PERMISSION_SET_ROLE'			=> 'Bezig met instellen van permissies voor de %s rol.',
	'PERMISSION_UNSET_GROUP'		=> 'Bezig met permissie instellingen terug draaien van de %s groep.',
	'PERMISSION_UNSET_ROLE'			=> 'Bezig met permissie instellingen terug draaien van de %s rol.',

	'ROLE_NOT_EXIST'				=> 'Rol bestaat niet',

	'SUCCESS'						=> 'Succes',

	'TABLE_ADD'						=> 'Bezig met toevoegen van nieuwe database tabel: %s',
	'TABLE_ALREADY_EXISTS'			=> 'FOUT: Database tabel %s bestaat al.',
	'TABLE_COLUMN_ADD'				=> 'Bezig met toevoegen van nieuwe kolom %2$s aan tabel %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'FOUT: De kolom %2$s bestaat al in tabel %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'FOUT: De kolom %2$s bestaat niet in tabel %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Bezig met verwijderen van de kolom genaamd %2$s van tabel %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Bezig met bijwerken van een kolom genaamd %2$s van tabel %1$s',
	'TABLE_KEY_ADD'					=> 'Bezig met toevoegen van sleutel genaamd %2$s aan tabel %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'FOUT: De index %2$s bestaat al in tabel %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'FOUT: De index %2$s bestaat niet in tabel %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Bezig met verwijderen van de sleutel %2$s van tabel %1$s',
	'TABLE_NOT_EXIST'				=> 'FOUT: Database tabel %s bestaat niet.',
	'TABLE_REMOVE'					=> 'Bezig met verwijderen van database tabel: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Bezig met data toevoegen in de %s database tabel.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Bezig met verwijderen van een rij van de %s database tabel',
	'TABLE_ROW_UPDATE_DATA'			=> 'Bezig met bijwerken van een rij in de %s database tabel.',
	'TEMPLATE_CACHE_PURGE'			=> 'Bezig met vernieuwen van de %s template',
	'THEME_CACHE_PURGE'				=> 'Bezig met vernieuwen van het %s thema',

	'UNINSTALL'						=> 'De-installeren',
	'UNINSTALL_MOD'					=> 'De-installeren %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Ben je klaar om %s te de-installeren? Alle instellingen en data opgeslagen door deze MOD zullen worden verwijderd!',
	'UNKNOWN'						=> 'Onbekend',
	'UPDATE_MOD'					=> 'Bijwerken %s',
	'UPDATE_MOD_CONFIRM'			=> 'Ben je klaar om %s te bijwerken?',
	'UPDATE_UMIL'					=> 'Deze versie van UMIL is niet up-to-date.<br /><br />Download alstublieft de laatste UMIL (Unified MOD Install Library) van: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'MOD versie: <strong>%1$s</strong><br />Momenteel geïnstalleerd: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Versie selecteren',
	'VERSION_SELECT_EXPLAIN'		=> 'Verander niet “negeren” tenzij je weet wat je aan het doen bent, of als het je verteld werd om dit te doen.',
));

?>