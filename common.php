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
if (!defined('IN_PHPBB'))
{
	exit;
}

// Include the non-dynamic constants
require(TITANIA_ROOT . 'includes/constants.' . PHP_EXT);

// Include core classes
require(TITANIA_ROOT . 'includes/core/phpbb.' . PHP_EXT);
require(TITANIA_ROOT . 'includes/core/titania.' . PHP_EXT);
require(TITANIA_ROOT . 'includes/core/object.' . PHP_EXT);
require(TITANIA_ROOT . 'includes/core/object_database.' . PHP_EXT);
require(TITANIA_ROOT . 'includes/core/object_message.' . PHP_EXT);

// Include our core functions
titania::_include('functions');

set_exception_handler('titania_exception_handler');

// Set up our auto-loader
spl_autoload_register(array('titania', 'autoload'));

// Read config.php file
titania::read_config_file();

// Include the dynamic constants (after reading the Titania config file, but before loading the phpBB common file)
titania::_include('dynamic_constants');

// Initialise phpBB
phpbb::initialise();

// Initialise Titania
titania::initialise();
