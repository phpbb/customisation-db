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

// Include titania configuration
require(TITANIA_ROOT . 'config.' . PHP_EXT);

// Global constants to be used throughout the core of titania
define('PHPBB_ROOT_PATH', $phpbb_root_path);
define('IN_PHPBB', true);

// We also need a variable for the extension to let it work with phpBB 3.0.x scripts.
$phpEx = PHP_EXT;

// Include the general phpbb-related files.
// This will also check if phpBB is installed and if we have the settings we need (db etc.).
require(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Include titania constants
require(TITANIA_ROOT . 'includes/constants.' . PHP_EXT);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Set the custom template path for titania. Default: root/titania/template
$template->set_custom_template(TITANIA_ROOT . $template_location, 'titania');