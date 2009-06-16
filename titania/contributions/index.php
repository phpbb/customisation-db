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

$id			= request_var('id', '');
$mode		= request_var('mode', '');
$contrib_id	= request_var('c', '');

// Auto assign some ID's to eliminate the need for id param on most URLs
if (!$id)
{
	$modes = array('details', 'faq', 'support');
	$id = (in_array($mode, $modes)) ? $mode : 'details';
}

$module = new titania_modules();

// Instantiate module system and generate list of available modules
$module->list_modules('contribs');

// Select the active module
$module->set_active($id, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(append_sid(TITANIA_ROOT . 'contributions/index.' . PHP_EXT));

// Output page
titania::page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name(),
));

$template->assign_vars(array(
	'U_CONTRIB_DETAILS'		=> titania_sid('contributions/index', 'c=' . $contrib_id),
	'U_CONTRIB_SUPPORT'		=> titania_sid('contributions/index', 'mode=support&amp;c=' . $contrib_id),
	'U_CONTRIB_FAQ'			=> titania_sid('contributions/index', 'mode=faq&amp;c=' . $contrib_id),
));

titania::page_footer();
