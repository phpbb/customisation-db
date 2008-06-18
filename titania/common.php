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

/**
 * @todo remove || true
 */
if (!file_exists(TITANIA_ROOT . 'config.' . PHP_EXT)) // test the install...
{
	die('<p>The Titania config.' . PHP_EXT . ' file could not be found.</p><p><a href="' . TITANIA_ROOT . 'install/index.' . PHP_EXT . '">Click here to install Titania</a></p>');
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
$template->set_custom_template(TITANIA_ROOT . $template_path, 'titania');

$titania = new titania();

class titania
{
	public function add_lang($lang_file)
	{
		global $user;

		if (defined('TMP_LANG_DIR'))
		{
			$language_filename = TMP_LANG_DIR . $lang_file . '.' . PHP_EXT;

			if (file_exists($language_filename))
			{
				include($language_filename);

				$user->lang = array_merge($user->lang, $lang);
			}
			else
			{
				trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
			}
		}
		else
		{
			$user->add_lang($lang_file);
		}
	}
}
