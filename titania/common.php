<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

// This gets checked in page_header() in functions.php to see whether we're in community
if (!defined('NOT_IN_COMMUNITY') && !defined('LOAD_CUSTOM_STYLE'))
{
	define('NOT_IN_COMMUNITY', true);
}

// Version number (only used for the installer)
define('TITANIA_VERSION', '0.3.13');

define('PHPBB_USE_BOARD_URL_PATH', true);
if (!defined('IN_TITANIA_INSTALL'))
{
	define('PHPBB_MSG_HANDLER', 'titania_msg_handler');
}

// Include the non-dynamic constants
require(TITANIA_ROOT . 'includes/constants.' . PHP_EXT);

// Include core classes
require(TITANIA_ROOT . 'includes/core/phpbb.' . PHP_EXT);
require(TITANIA_ROOT . 'includes/core/titania.' . PHP_EXT);

// Include our core functions
titania::_include('functions');

set_exception_handler('titania_exception_handler');

// Set up our auto-loader
spl_autoload_register(array('titania', 'autoload'));

// Read config.php file
// 2 separate locations possible: within Titania directory and just outside of it.
if (file_exists(TITANIA_ROOT . 'config.' . PHP_EXT))
{
	titania::read_config_file(TITANIA_ROOT . 'config.' . PHP_EXT);
}
else
{
	titania::read_config_file(TITANIA_ROOT . '../config.' . PHP_EXT);
}

// Include the dynamic constants (after reading the Titania config file, but before loading the phpBB common file)
titania::_include('dynamic_constants');

// Decode the request
titania_url::decode_request();

// Include common phpBB files and functions.
if (!file_exists(PHPBB_ROOT_PATH . 'common.' . PHP_EXT))
{
	die('<p>No phpBB installation found. Check the Titania configuration file.</p>');
}
if (!defined('PHPBB_INCLUDED'))
{
	require(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);
}

// Initialise phpBB
phpbb::initialise();

// If the database is not installed or outdated redirect to the installer
if (!defined('IN_TITANIA_INSTALL') && (!isset(phpbb::$config['titania_version']) || version_compare(phpbb::$config['titania_version'], TITANIA_VERSION, '<')))
{
	if (phpbb::$user->data['user_type'] != USER_FOUNDER)
	{
		phpbb::$user->set_custom_lang_path(TITANIA_ROOT . 'language/');
		phpbb::$user->add_lang('common');

		msg_handler(E_USER_ERROR, phpbb::$user->lang['TITANIA_DISABLED'], '', '');
	}

	redirect(phpbb::append_sid(TITANIA_ROOT . 'install.' . PHP_EXT));
}

// Initialise Titania
titania::initialise();

// Allow login attempts from any page (mini login box)
if (isset($_POST['login']))
{
	phpbb::login_box();
}

// admin requested the cache to be purged, ensure they have permission and purge the cache.
if (isset($_GET['cache']) && $_GET['cache'] == 'purge' && phpbb::$auth->acl_get('a_'))
{
	titania::$cache->purge();

	titania::error_box(phpbb::$user->lang['SUCCESSBOX_TITLE'], phpbb::$user->lang['CACHE_PURGED']);
}

// admin requested a sync
if (isset($_GET['sync']) && phpbb::$auth->acl_get('a_'))
{
	$sync = new titania_sync;
	$method = explode('_', request_var('sync', ''), 2);

	if (method_exists($sync, $method[0]))
	{
		if (isset($method[1]))
		{
			$id = request_var('id', 0);
			$sync->$method[0]($method[1], $id);
		}
		else
		{
			$sync->$method[0]();
		}

		titania::error_box(phpbb::$user->lang['SUCCESSBOX_TITLE'], phpbb::$user->lang['SYNC_SUCCESS']);
	}
}
