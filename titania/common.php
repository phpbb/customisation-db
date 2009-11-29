<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

// Version number (only used for the installer)
define('TITANIA_VERSION', '0.1.20');

define('PHPBB_MSG_HANDLER', 'titania_msg_handler');
define('PHPBB_USE_BOARD_URL_PATH', true);

// Include core classes
require(TITANIA_ROOT . 'includes/core/phpbb.' . PHP_EXT);
require(TITANIA_ROOT . 'includes/core/titania.' . PHP_EXT);

// Include our core functions
titania::_include('functions');

// Set up our auto-loader
spl_autoload_register(array('titania', 'autoload'));

// Read config.php file
titania::read_config_file(TITANIA_ROOT . 'config.' . PHP_EXT);

// Include the constants (after reading the Titania config file, but before loading the phpBB Common file)
titania::_include('constants');

// Include common phpBB files and functions.
if (!file_exists(PHPBB_ROOT_PATH . 'common.' . PHP_EXT))
{
	die('<p>No phpBB installation found. Check the Titania configuration file.</p>');
}
require(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Initialise phpBB
phpbb::initialise();

// If the database is not installed or outdated redirect to the installer
if (!defined('IN_TITANIA_INSTALL') && (!isset(phpbb::$config['titania_version']) || version_compare(phpbb::$config['titania_version'], TITANIA_VERSION, '<')))
{
	redirect(phpbb::append_sid(TITANIA_ROOT . 'install.' . PHP_EXT));
}

// Initialise Titania
titania::initialise();

// admin requested the cache to be purged, ensure they have permission and purge the cache.
if (isset($_GET['cache']) && $_GET['cache'] == 'purge' && phpbb::$auth->acl_get('a_'))
{
	if (titania::confirm_box(true))
	{
		titania::$cache->purge();

		titania::error_box('SUCCESS', phpbb::$user->lang['CACHE_PURGED']);
	}
	else
	{
		titania::confirm_box(false, phpbb::$user->lang['CONFIRM_PURGE_CACHE'], titania::$url->append_url(titania::$url->current_page, array_merge(titania::$url->params, array('cache' => 'purge'))));
	}
}