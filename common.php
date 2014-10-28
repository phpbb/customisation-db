<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
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
