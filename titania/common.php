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

define('TITANIA_VERSION', '0.1.6');


// Include titania class
require TITANIA_ROOT . 'includes/core/titania.' . PHP_EXT;
require TITANIA_ROOT . 'includes/core/phpbb.' . PHP_EXT;

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

// Start session management etc.
phpbb::initialise();
titania::initialise();

// If the database is not installed or outdated redirect to the installer
if (!defined('IN_TITANIA_INSTALL') && (!isset(phpbb::$config['titania_version']) || version_compare(phpbb::$config['titania_version'], TITANIA_VERSION, '<')))
{
	redirect(titania_sid('install'));
}