<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
 * @ignore
 */
define('IN_TITANIA', true);
define('IN_TITANIA_INSTALL', true);
define('UMIL_AUTO', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);
titania::add_lang('install');

// Hopefully this helps
@set_time_limit(0);

include(TITANIA_ROOT . 'includes/functions_install.' . PHP_EXT);

// Just to be on the safe side, add a php version check.
if (version_compare(PHP_VERSION, '5.2.0') < 0)
{
	die('You are running an unsupported PHP version. Please upgrade to PHP 5.2.0 or higher before trying to install Titania');
}

if (!file_exists(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// Make sure we are not using the same table prefix as phpBB (will cause conflicts).
if (titania::$config->table_prefix == $GLOBALS['table_prefix'])
{
	trigger_error('You can not use the same table prefix for Titania as you are using for phpBB.');
}

// Include the versions/data file
include(TITANIA_ROOT . 'includes/versions.' . PHP_EXT);

include(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT);