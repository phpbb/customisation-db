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
 * Translated By: Kastaneda
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
	'ACTION'						=> 'Действие',
	'ADVANCED'						=> 'Дополнительно',
	'AUTH_CACHE_PURGE'				=> 'Очистка кэша',

	'CACHE_PURGE'					=> 'Очистка кэша конференции',
	'CONFIGURE'						=> 'Настройка',
	'CONFIG_ADD'					=> 'Добавление новой конфигурационной переменной: %s',
	'CONFIG_ALREADY_EXISTS'			=> 'Ошибка: конфигурационная переменная «%s» уже существует.',
	'CONFIG_NOT_EXIST'				=> 'Ошибка: конфигурационной переменной «%s» не существует.',
	'CONFIG_REMOVE'					=> 'Удаление конфигурационной переменной: %s',
	'CONFIG_UPDATE'					=> 'Обновление конфигурационной переменной: %s',

	'DISPLAY_RESULTS'				=> 'Показать полные результаты',
	'DISPLAY_RESULTS_EXPLAIN'		=> 'Выберите «Да» для отображения всех действий и результатов, выполняемых требуемыми действиями.',

	'ERROR_NOTICE'					=> 'Во время выполнения действия произошли ошибки. Загрузите <a href="%1$s">файл с перечисленными ошибками</a>, и запросите помощь у автора модификации.<br /><br />Если вы испытываете проблемы с загрузкой данного файла, то вы можете напрямую загрузить файл, используя FTP в следующей позиции: %2$s',
	'ERROR_NOTICE_NO_FILE'			=> 'Во время выполнения действия произошли ошибки. Выполните полный отчёт обо всех ошибках, и запросите помощь у автора модификации.',

	'FAIL'							=> 'Ошибка',
	'FILE_COULD_NOT_READ'			=> 'Ошибка: не удалось открыть файл «%s» для чтения.',
	'FOUNDERS_ONLY'					=> 'Для получения доступа к данной странице у вас должен быть статус основателя.',

	'GROUP_NOT_EXIST'				=> 'Группа не существует',

	'IGNORE'						=> 'Пропустить',
	'IMAGESET_CACHE_PURGE'			=> 'Обновление набора рисунков «%s»',
	'INSTALL'						=> 'Установить',
	'INSTALL_MOD'					=> 'Установка %s',
	'INSTALL_MOD_CONFIRM'			=> 'Вы действительно хотите установить модификацию «%s»?',

	'MODULE_ADD'					=> 'Добавление модуля %1$s: %2$s',
	'MODULE_ALREADY_EXIST'			=> 'Ошибка: модуль уже существует.',
	'MODULE_NOT_EXIST'				=> 'Ошибка: модуль не существует.',
	'MODULE_REMOVE'					=> 'Удаление модуля %1$s: %2$s',

	'NONE'							=> 'Нет',
	'NO_TABLE_DATA'					=> 'Ошибка: не определены данные таблицы',

	'PARENT_NOT_EXIST'				=> 'Ошибка: Родительская категория, указанная для этого модуля, не существует.',
	'PERMISSIONS_WARNING'			=> 'Добавлены новые параметры прав доступа. Не забудьте проверить настройки прав доступа и убедиться в их корректности.',
	'PERMISSION_ADD'				=> 'Добавление нового права доступа: %s',
	'PERMISSION_ALREADY_EXISTS'		=> 'Ошибка: право доступа «%s» уже существует.',
	'PERMISSION_NOT_EXIST'			=> 'Ошибка: права доступа «%s» не существует.',
	'PERMISSION_REMOVE'				=> 'Удаление права доступа: %s',
	'PERMISSION_SET_GROUP'			=> 'Настройка прав доступа для группы «%s».',
	'PERMISSION_SET_ROLE'			=> 'Настройка прав доступа для роли «%s».',
	'PERMISSION_UNSET_GROUP'		=> 'Сброс прав доступа для группы «%s».',
	'PERMISSION_UNSET_ROLE'			=> 'Сброс прав доступа для роли «%s».',

	'ROLE_NOT_EXIST'				=> 'Роль не существует',

	'SUCCESS'						=> 'Успешно',

	'TABLE_ADD'						=> 'Добавление новой таблицы в базу данных: %s',
	'TABLE_ALREADY_EXISTS'			=> 'Ошибка: таблица базы данных «%s» уже существует.',
	'TABLE_COLUMN_ADD'				=> 'Добавление нового столбца «%2$s» в таблицу «%1$s»',
	'TABLE_COLUMN_ALREADY_EXISTS'	=> 'Ошибка: столбец «%2$s» уже существует в таблице «%1$s».',
	'TABLE_COLUMN_NOT_EXIST'		=> 'Ошибка: столбец «%2$s» не существует в таблице «%1$s».',
	'TABLE_COLUMN_REMOVE'			=> 'Удаление столбца «%2$s» из таблицы «%1$s»',
	'TABLE_COLUMN_UPDATE'			=> 'Обновление столбца «%2$s» в таблице «%1$s»',
	'TABLE_KEY_ADD'					=> 'Добавление нового ключа «%2$s» в таблицу «%1$s»',
	'TABLE_KEY_ALREADY_EXIST'		=> 'Ошибка: индекс «%2$s» уже существует в таблице «%1$s».',
	'TABLE_KEY_NOT_EXIST'			=> 'Ошибка: индекс «%2$s» не существует в таблице «%1$s».',
	'TABLE_KEY_REMOVE'				=> 'Удаление ключа «%2$s» из таблицы «%1$s»',
	'TABLE_NOT_EXIST'				=> 'Ошибка: таблица базы данных «%s» не существует.',
	'TABLE_REMOVE'					=> 'Удаление таблицы базы данных: %s',
	'TABLE_ROW_INSERT_DATA'			=> 'Вставка столбцов в таблицу базы данных «%s».',
	'TABLE_ROW_REMOVE_DATA'			=> 'Удаление строки из таблицы базы данных «%s».',
	'TABLE_ROW_UPDATE_DATA'			=> 'Обновление строки в таблице базе данных «%s».',
	'TEMPLATE_CACHE_PURGE'			=> 'Обновление шаблона «%s»',
	'THEME_CACHE_PURGE'				=> 'Обновление темы «%s»',

	'UNINSTALL'						=> 'Деинсталлировать',
	'UNINSTALL_MOD'					=> 'Деинсталляция «%s» ',
	'UNINSTALL_MOD_CONFIRM'			=> 'Вы действительно хотите деинсталлировать модификацию «%s»? Все настройки и данные, сохранённые посредством данной модификации, будут удалены.',
	'UNKNOWN'						=> 'Неизвестно',
	'UPDATE_MOD'					=> 'Обновление %s',
	'UPDATE_MOD_CONFIRM'			=> 'Вы действительно хотите обновить модификацию «%s»?',
	'UPDATE_UMIL'					=> 'Данная версия UMIL устарела.<br /><br />Загрузите последнюю версию UMIL (Unified MOD Install Library) с сайта: <a href="%1$s">%1$s</a>',

	'VERSIONS'						=> 'Версия модификации: <strong>%1$s</strong><br />Установленная версия: <strong>%2$s</strong>',
	'VERSION_SELECT'				=> 'Выбор версии',
	'VERSION_SELECT_EXPLAIN'		=> 'Не изменяйте переключатель «Пропустить», если вы не знаете, что делаете, или если об этом не говорилось прямо.',
));

?>