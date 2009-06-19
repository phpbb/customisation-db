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
include(TITANIA_ROOT . 'common.' . PHP_EXT);
include(TITANIA_ROOT . 'includes/core/modules.' . PHP_EXT);

$mode		= request_var('mode', 'details');
$user_id	= request_var('u', '');

$module = new titania_modules();

// Instantiate module system and generate list of available modules
$module->list_modules('authors');

// Select the active module
$module->set_active($mode, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(titania_sid('authors/index'));

// Output page
titania::page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name(),
));

$template->assign_vars(array(
	'U_AUTHOR_DETAILS'		=> titania_sid('authors/index', 'u=' . $user_id),
	'U_AUTHOR_CONTRIBS'		=> titania_sid('authors/index', 'mode=contributions&amp;u=' . $user_id),

	'S_MODE'				=> $mode,
));

titania::page_footer();
