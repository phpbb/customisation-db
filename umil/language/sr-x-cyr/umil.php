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
 * Translated By: vojislavradoja $
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
	'ACTION'						=> 'Поступак',
	'ADVANCED'						=> 'Напредно',
	'AUTH_CACHE_PURGE'				=> 'Чишћење Auth Cache-а',

	'CACHE_PURGE'					=> 'Чишћење кеша вашег форума',
	'CONFIGURE'						=> 'Конфигуриши',
	'CONFIG_ADD'					=> 'Додавање нове config променљиве: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'ГРЕШКА: Config променљива %s већ постоји.',
	'CONFIG_NOT_EXIST'				=> 'ГРЕШКА: Config променљива %s не постоји.',
	'CONFIG_REMOVE'					=> 'Уклањање config променљиве: %s',
	'CONFIG_UPDATE'					=> 'Ажурирање config променљиве: %s',

	'DISPLAY_RESULTS'				=> 'Прикажи пуне резултате',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Изаберите Да за приказ свих поступака и резултата током траженог поступка.',

	'ERROR_NOTICE'					=> 'Једна или више грешака се појавило током траженог поступка.  Молимо преузмите <a href="%1$s">ову датотеку</a> са грешкама наведеним у њој и питајте аутора модула за помоћ.<br /><br />Уколико имате било каквих сметњи при преузимању те датотеке можете јој приступити непосредно са FTP прегледачем на следећем месту: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Једна или више грешака се појавило током траженог поступка.  Молимо направите пун извештај о грешкама и питајте аутора модула за помоћ.',

	'FAIL'							=> 'Неуспешно',
	'FILE_COULD_NOT_READ'			=> 'ГРЕШКА: Не могу да отворим датотеку %s за читање.',
	'FOUNDERS_ONLY'					=> 'Морате бити оснивач форума за приступ овој страници.',

	'GROUP_NOT_EXIST'				=> 'Група не постоји',

	'IGNORE'						=> 'Игнориши',
	'IMAGESET_CACHE_PURGE'			=> 'Освежавање %s imageset',
	'INSTALL'						=> 'Инсталирај',
	'INSTALL_MOD'					=> 'Инсталирај %s',
	'INSTALL_MOD_CONFIRM'			=> 'Јесте ли спремни да инсталирате %s?',

	'MODULE_ADD'					=> 'Додавање %1$s модула: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'ГРЕШКА: Модул већ постоји.',
	'MODULE_NOT_EXIST'				=> 'ГРЕШКА: Модул не постоји.',
	'MODULE_REMOVE'					=> 'Уклањање %1$s модула: %2$s',

	'NONE'							=> 'Ништа',
	'NO_TABLE_DATA'					=> 'ГРЕШКА: Ниједан податак табеле није назначен',

	'PARENT_NOT_EXIST'				=> 'ГРЕШКА: Матична категорија назначена за овај модул не постоји.',
	'PERMISSIONS_WARNING'			=> 'Нове поставке дозвола су додате.  Свакако проверите Ваше поставке дозвола и видите да ли су као што бисте волели да буду.',
	'PERMISSION_ADD'				=> 'Додавање нове понуде у дозволама: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'ГРЕШКА: Понуда дозволе %s већ постоји.',
	'PERMISSION_NOT_EXIST'			=> 'ГРЕШКА: Понуда дозволе %s не постоји.',
	'PERMISSION_REMOVE'				=> 'Уклањање понуде дозволе: %s',
	'PERMISSION_SET_GROUP'			=> 'Постављање дозвола за %s групу.',
	'PERMISSION_SET_ROLE'			=> 'Постављање дозвола за %s ролу.',
	'PERMISSION_UNSET_GROUP'		=> 'Скидање дозвола за %s групу.',
	'PERMISSION_UNSET_ROLE'			=> 'Скидање дозвола за %s ролу.',

	'ROLE_NOT_EXIST'				=> 'Рола не постоји',

	'SUCCESS'						=> 'Успешно',

	'TABLE_ADD'						=> 'Додавање нове табеле базе: %s',
	'TABLE_ALREADY_EXISTS'			=> 'ГРЕШКА: Табела базе %s већ постоји.',
	'TABLE_COLUMN_ADD'				=> 'Додавање новог ступца под именом %2$s у табелу %1$s',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'ГРЕШКА: Стубац %2$s већ постоји у табели %1$s.',
	'TABLE_COLUMN_NOT_EXIST'		=> 'ГРЕШКА: Стубац %2$s не постоји у табели %1$s.',
	'TABLE_COLUMN_REMOVE'			=> 'Уклањање ступца под именом %2$s из табеле %1$s',
	'TABLE_COLUMN_UPDATE'			=> 'Ажурирање ступца под именом %2$s у табели %1$s',
	'TABLE_KEY_ADD'					=> 'Додавање кључа под именом %2$s у табелу %1$s',
	'TABLE_KEY_ALREADY_EXIST'		=> 'ГРЕШКА: Садржај %2$s већ постоји у табели %1$s.',
	'TABLE_KEY_NOT_EXIST'			=> 'ГРЕШКА: Садржај %2$s не постоји у табели %1$s.',
	'TABLE_KEY_REMOVE'				=> 'Уклањање кључа под именом %2$s из табеле %1$s',
	'TABLE_NOT_EXIST'				=> 'ГРЕШКА: Табела базе %s не постоји.',
	'TABLE_REMOVE'					=> 'Уклањање табеле базе: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Убацивање података у %s табелу базе.',
	'TABLE_ROW_REMOVE_DATA'			=> 'Уклањање реда из %s табеле базе',
	'TABLE_ROW_UPDATE_DATA'			=> 'Ажурирање реда у %s табели базе.',
	'TEMPLATE_CACHE_PURGE'			=> 'Освежавање %s предлошка',
	'THEME_CACHE_PURGE'				=> 'Освежавање %s теме',

	'UNINSTALL'						=> 'Деинсталирај',
	'UNINSTALL_MOD'					=> 'Деинсталирај %s',
	'UNINSTALL_MOD_CONFIRM'			=> 'Јесте ли спремни да деинсталирате %s?  Све поставке и подаци сачувани од овог модула биће уклоњени!',
	'UNKNOWN'						=> 'Непознато',
	'UPDATE_MOD'					=> 'Ажурирај %s',
	'UPDATE_MOD_CONFIRM'			=> 'Јесте ли спремни да ажурирате %s?',
	'UPDATE_UMIL'					=> 'Ова верзија UMIL је застарела.<br /><br />Молимо преузмите најновији UMIL (Unified MOD Install Library) са: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'Верзија модула: <strong>%1$s</strong><br />Тренутно инсталирана: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Одабир верзије',
	'VERSION_SELECT_EXPLAIN'		=> 'Не мењајте са “Игнориши” осим ако не знате шта радите или Вам је речено тако.',
));

?>