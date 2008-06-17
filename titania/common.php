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

// We need to prepend the titania root because $phpbb_root_path is relative to it.
define('PHPBB_ROOT_PATH', TITANIA_ROOT . $phpbb_root_path);

// We need those variables to let phpBB 3.0.x scripts work properly.
$phpbb_root_path = PHPBB_ROOT_PATH;
$phpEx = PHP_EXT;

// We set this so we can access the phpBB scripts.
define('IN_PHPBB', true);

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