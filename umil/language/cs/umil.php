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
 * Translated By: Vojtěch Vondra (ameeck) http://www.ameeck.net
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
	'ACTION'										=> 'Činnost',
	'ADVANCED'									=> 'Pokročilé',
	'AUTH_CACHE_PURGE'					=> 'Odstraňuji cache oprávnění',

	'CACHE_PURGE'								=> 'Odstraňuji cache fóra',
	'CONFIGURE'									=> 'Nastavit',
	'CONFIG_ADD'								=> 'Přidávám novou proměnnou nastavení: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'CHYBA: Proměnná nastavení %s již existuje.',
	'CONFIG_NOT_EXIST'					=> 'CHYBA: Proměnná nastavení %s neexistuje.',
	'CONFIG_REMOVE'							=> 'Odstraňuji proměnnou nastavení: %s',
	'CONFIG_UPDATE'							=> 'Aktualizuji proměnnou nastavení: %s',

	'DISPLAY_RESULTS'						=> 'Zobrazit celý výsledek',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Vyberte Ano pro zobrazí všech činností a výsledků získaných během spuštěného procesu.',

	'ERROR_NOTICE'					=> 'Během instalace se objevila jedna nebo více chyb. Prosím stáhněte si <a href="%1$s">tento soubor</a>, který obsahuje seznam chyb a požádejte autora MODu o pomoc.<br /><br />Pokud vám tento soubor nejde stáhnout, můžete jej získat přímo na FTP v umístění: %2$s',
	'ERROR_NOTICE_NO_FILE'	=> 'Objevila se jedna nebo více chyb během požadované akce. Zkopírujte záznam všech chyb a kontaktujte autora MODu.',

	'FAIL'									=> 'Selhalo',
	'FILE_COULD_NOT_READ'		=> 'Chyba: Nelze otevřít soubor %s pro čtení.',
	'FOUNDERS_ONLY'					=> 'Musíte být zakladatel fóra pro přístup k této stránce.',

	'GROUP_NOT_EXIST'				=> 'Skupina neexistuje',

	'IGNORE'										=> 'Ignorovat',
	'IMAGESET_CACHE_PURGE'			=> 'Obnovuji sadu obrázků %s',
	'INSTALL'										=> 'Instalovat',
	'INSTALL_MOD'								=> 'Instalovat %s',
	'INSTALL_MOD_CONFIRM'				=> 'Opravdu chcete nainstalovat %s?',

	'MODULE_ADD'								=> 'Přidávám %1$s modul: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'CHYBA: Modul již existuje.',
	'MODULE_NOT_EXIST'					=> 'CHYBA: Modul neexistuje.',
	'MODULE_REMOVE'							=> 'Odstraňuji %1$s modul: %2$s',

	'NONE'											=> 'Žádné',
	'NO_TABLE_DATA'							=> 'CHYBA: Nebyly zvoleny žádné data z tabulek',

	'PARENT_NOT_EXIST'					=> 'CHYBA: Rodičovská kategorie zvolená pro tento MOD nebyla nalezena.',
	'PERMISSIONS_WARNING'				=> 'Byla přidána nová oprávnění. Zkontrolujte nastavení svých oprávnění a ujistěte se, že jsou nastavené jak potřebujete.',
	'PERMISSION_ADD'						=> 'Přidávám nové oprávnění: %s',
	'PERMISSION_ALREADY_EXISTS'	=> 'CHYBA: Oprávnění %s již existuje.',
	'PERMISSION_NOT_EXIST'			=> 'CHYBA: Oprávnění %s neexistuje.',
	'PERMISSION_REMOVE'					=> 'Odstraňuji oprávnění: %s',
	'PERMISSION_SET_GROUP'			=> 'Nastavuji oprávnění pro skupinu %s.',
	'PERMISSION_SET_ROLE'				=> 'Nastavuji oprávnění pro roli %s.',
	'PERMISSION_UNSET_GROUP'		=> 'Odnastavuji oprávnění pro skupinu %s.',
	'PERMISSION_UNSET_ROLE'			=> 'Odnastavuji oprávnění pro roli %s.',

	'ROLE_NOT_EXIST'						=> 'Role neexistuje',

	'SUCCESS'										=> 'Úspěch',

	'TABLE_ADD'									=> 'Přidávám novou tabulku do databáze: %s',
	'TABLE_ALREADY_EXISTS'			=> 'CHYBA: Databázová tabulka %s již existuje.',
	'TABLE_COLUMN_ADD'					=> 'Přidávám nový sloupec %2$s do tabulky %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'CHYBA: Sloupec %2$s již existuje v tabulce %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'CHYBA: Sloupec %2$s neexistuje v tabulce %1$s.',
	'TABLE_COLUMN_REMOVE'				=> 'Odstraňuji sloupec %2$s z tabulky %1$s',
	'TABLE_COLUMN_UPDATE'				=> 'Aktualizuji sloupec %2$s v tabulce %1$s',
	'TABLE_KEY_ADD'							=> 'Přidávám klíč %2$s k tabulce %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'CHYBA: Index %2$s již existuje v tabulce %1$s.',
	'TABLE_KEY_NOT_EXIST'				=> 'CHYBA: Index %2$s neexistuje v tabulce %1$s.',
	'TABLE_KEY_REMOVE'					=> 'Odstraňuji index %2$s z tabulky %1$s',
	'TABLE_NOT_EXIST'						=> 'CHYBA: Tabulka %s ve zvolené databázi neexistuje.',
	'TABLE_REMOVE'							=> 'Odstraňuji tabulku databáze: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Vkládám data do tabulky %s',
	'TABLE_ROW_REMOVE_DATA'			=> 'Odstraňuji data z tabulky %s',
	'TABLE_ROW_UPDATE_DATA'			=> 'Aktualizuji data v tabulce %s',
	'TEMPLATE_CACHE_PURGE'			=> 'Obnovuji šablonu %s',
	'THEME_CACHE_PURGE'					=> 'Obnovuji vzhled/skin %s',

	'UNINSTALL'								=> 'Odinstalovat',
	'UNINSTALL_MOD'						=> 'Odinstalovat %s',
	'UNINSTALL_MOD_CONFIRM'		=> 'Opravdu chcete odinstalovat %s? Všechna nastavení a data uložena tímto MODem budou odstraněna!',
	'UNKNOWN'									=> 'Neznámé',
	'UPDATE_MOD'							=> 'Aktualizovat %s',
	'UPDATE_MOD_CONFIRM'			=> 'Opravdu chcete aktualizovat %s?',
	'UPDATE_UMIL'							=> 'Tato verze UMIL je zastaralá.<br /><br />Stáhněte si poslední verzi UMIL (Unified MOD Install Library) z: <a href="%1$s">%1$s</a>',

	'VERSIONS'								=> 'Verze MODu: <strong>%1$s</strong><br />Nainstalovaná verze: <strong>%2$s</strong>',
	'VERSION_SELECT'					=> 'Výběr verze',
	'VERSION_SELECT_EXPLAIN'	=> 'Neměňte z nastavení "Ignorovat", pokud nevíte co děláte nebo vám to nebylo řečeno.',
));

?>
