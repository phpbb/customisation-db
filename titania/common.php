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

// Include titania specific configuration
// Point to the installer if config file does not exist.
if (!file_exists(TITANIA_ROOT . 'config.' . PHP_EXT))
{
	die('<p>The Titania config.' . PHP_EXT . ' file could not be found.</p>
	<p><a href="' . TITANIA_ROOT . 'install/index.' . PHP_EXT . '">Click here to install Titania</a></p>');
}
require(TITANIA_ROOT . 'config.' . PHP_EXT);

// We need to prepend the titania root because $phpbb_root_path is relative to it.
define('PHPBB_ROOT_PATH', TITANIA_ROOT . $phpbb_root_path);

// We need those variables to let phpBB 3.0.x scripts work properly.
$phpbb_root_path = PHPBB_ROOT_PATH;
$phpEx = PHP_EXT;

// We set this so we can access the phpBB scripts.
define('IN_PHPBB', true);

// Include the general phpbb-related files.
// This will also check if phpBB is installed and if we have the settings we need (db etc.).
if (!file_exists(PHPBB_ROOT_PATH . 'common.' . PHP_EXT))
{
	die('<p>No phpBB installation found. Check the Titania configuration file.</p>');
}
require(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Include titania constants
require(TITANIA_ROOT . 'includes/constants.' . PHP_EXT);

// Include titania main class
require(TITANIA_ROOT . 'includes/class_titania.' . PHP_EXT);

// Include policy file (and interface)
require(TITANIA_ROOT . 'includes/interface_policy.' . PHP_EXT);
require(TITANIA_ROOT . 'policy.' . PHP_EXT);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Set the custom template path for titania. Default: root/titania/template
$template->set_custom_template(TITANIA_ROOT . $template_path, 'titania');

$titania = new titania();