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

define('TITANIA_VERSION', '0.1.16');

define('PHPBB_MSG_HANDLER', 'titania_msg_handler');
define('PHPBB_USE_BOARD_URL_PATH', true);

// Include titania class
require TITANIA_ROOT . 'includes/core/titania.' . PHP_EXT;
require TITANIA_ROOT . 'includes/core/phpbb.' . PHP_EXT;

// Load the URL class
titania::load_url();

// Read config.php file
titania::read_config_file(TITANIA_ROOT . 'config.' . PHP_EXT);

// Include titania constants and functions
require TITANIA_ROOT . 'includes/constants.' . PHP_EXT;
require TITANIA_ROOT . 'includes/functions.' . PHP_EXT;
require TITANIA_ROOT . 'includes/functions_display.' . PHP_EXT;

// We need this for compatibility reasons
$phpEx = PHP_EXT;
$phpbb_root_path = PHPBB_ROOT_PATH;

// Include common phpBB files and functions.
if (!file_exists(PHPBB_ROOT_PATH . 'common.' . PHP_EXT))
{
	die('<p>No phpBB installation found. Check the Titania configuration file.</p>');
}
// This will also check if phpBB is installed and if we have the settings we need (db etc.).
require PHPBB_ROOT_PATH . 'common.' . PHP_EXT;

// We must use our own user class...
//require TITANIA_ROOT . 'includes/core/user.' . PHP_EXT;
//$user = new titania_user();

// Start session management etc.
phpbb::initialise();
titania::initialise();

// If the database is not installed or outdated redirect to the installer
if (!defined('IN_TITANIA_INSTALL') && (!isset(phpbb::$config['titania_version']) || version_compare(phpbb::$config['titania_version'], TITANIA_VERSION, '<')))
{
	redirect(append_sid(TITANIA_ROOT . 'install.' . PHP_EXT));
}

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