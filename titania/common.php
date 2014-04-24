<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
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
if (!defined('NOT_IN_COMMUNITY'))
{
	define('NOT_IN_COMMUNITY', true);
}

if (!defined('PHPBB_USE_BOARD_URL_PATH'))
{
	define('PHPBB_USE_BOARD_URL_PATH', true);
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

// Initialise phpBB
phpbb::initialise();

// Initialise Titania
titania::initialise();
