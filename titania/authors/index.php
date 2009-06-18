<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', '../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
require TITANIA_ROOT . 'includes/core/modules.' . PHP_EXT;

$id		= request_var('id', 'main');
$mode	= request_var('mode', '');

$module = new titania_modules();

// Instantiate module system and generate list of available modules
$module->list_modules('authors');

// Select the active module
$module->set_active($id, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(titania_sid('authors/index'));

// Output page
titania::page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name(),
));

titania::page_footer();
