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
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);
include(TITANIA_ROOT . 'includes/titania_modules.' . PHP_EXT);

$id		= request_var('id', '');
$mode	= request_var('mode', '');

// Auto assign some ID's to eliminate the need for id param on most URLs
if (!$id)
{
	switch ($mode)
	{
		case 'details':
			$id = 'details';
		break;

		case 'faq':
			$id = 'faq';
		break;

		case 'reviews':
			$id = 'reviews';
		break;

		case 'support':
			$id = 'support';
		break;

		default:
			$id = 'main';
		break;
	}
}

$module = new titania_modules();

// Instantiate module system and generate list of available modules
$module->list_modules('mods');

// Select the active module
$module->set_active($id, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(append_sid(TITANIA_ROOT . 'mods/index.' . PHP_EXT));

// Output page
titania::page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name(),
));

titania::page_footer();
