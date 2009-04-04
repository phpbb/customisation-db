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

// Include titania class
require TITANIA_ROOT . 'includes/class_titania.' . PHP_EXT;
require TITANIA_ROOT . 'includes/class_phpbb.' . PHP_EXT;

// Read config.php file
titania::read_config_file(TITANIA_ROOT . 'config.' . PHP_EXT);

// Include titania constants
require TITANIA_ROOT . 'includes/constants.' . PHP_EXT;

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
