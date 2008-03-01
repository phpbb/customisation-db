<?php
/** 
*
* @package website
* @version $Id: index.php,v 1.4 2007/06/23 17:32:52 vic Exp $
* @copyright (c) 2006 phpBB Group
* @license Not for redistribution
*
*/

// Paths
$root_path = './../';

define('IN_PHPBB', true);
define('IN_ADMIN', true);
include($root_path . 'common.php');

include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/common');

// Basic parameter data
$module_id	= request_var('i', '');
$mode		= request_var('mode', '');

$module = new p_master();

// Setting a variable to let the style designer know where he is...
$template->assign_var('S_IN_WEBSITE_BACKEND', true);

// Only Admins can go beyond this point
if (!$user->data['is_registered'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$root_path}index.$phpEx"));
	}

	login_box('', 'Please login to access the website backend');
}

// 
if (!$auth->acl_get('a_'))
{
	trigger_error('You are not authorized to access this area.');
}

$phpbb_admin_path = './';

// Set custom template
$template->set_custom_template($phpbb_admin_path . 'style', 'wadmin');
$template->assign_var('T_TEMPLATE_PATH', $phpbb_admin_path . 'style');

// the acp template is never stored in the database
$user->theme['template_storedb'] = false;

// Force pagination seperation for admin style
$user->theme['pagination_sep'] = '';

// Instantiate module system and generate list of available modules
$module->list_modules('website');

// Select the active module
$module->set_active($module_id, $mode);

// Assign data to the template engine for the list of modules
// We do this before loading the active module for correct menu display in trigger_error
$module->assign_tpl_vars(append_sid("{$phpbb_admin_path}index.$phpEx"));

// Load and execute the relevant module
$module->load_active();

// Generate the page
adm_page_header($module->get_page_title());

$template->set_filenames(array(
	'body' => $module->get_tpl_name())
);

adm_page_footer();


function adm_page_header($title)
{
	global $user, $phpbb_root_path, $phpbb_admin_path, $root_path, $phpEx, $template;

	$template->assign_vars(array(
		'USERNAME'				=> $user->data['username'],

		'U_LOGOUT'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=logout'),
		'U_ADM_INDEX'			=> append_sid("{$phpbb_admin_path}index.$phpEx"),
		'U_FORUM_INDEX'			=> append_sid("{$phpbb_root_path}index.$phpEx"),
		'U_WEBSITE_INDEX'		=> append_sid("{$root_path}index.$phpEx"),

		'ICON_MOVE_UP'				=> '<img src="' . $phpbb_admin_path . 'images/icon_up.gif" alt="' . $user->lang['MOVE_UP'] . '" title="' . $user->lang['MOVE_UP'] . '" />',
		'ICON_MOVE_UP_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_up_disabled.gif" alt="' . $user->lang['MOVE_UP'] . '" title="' . $user->lang['MOVE_UP'] . '" />',
		'ICON_MOVE_DOWN'			=> '<img src="' . $phpbb_admin_path . 'images/icon_down.gif" alt="' . $user->lang['MOVE_DOWN'] . '" title="' . $user->lang['MOVE_DOWN'] . '" />',
		'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . $phpbb_admin_path . 'images/icon_down_disabled.gif" alt="' . $user->lang['MOVE_DOWN'] . '" title="' . $user->lang['MOVE_DOWN'] . '" />',		
		'ICON_EDIT'					=> '<img src="' . $phpbb_admin_path . 'images/icon_edit.gif" alt="' . $user->lang['EDIT'] . '" title="' . $user->lang['EDIT'] . '" />',
		'ICON_EDIT_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_edit_disabled.gif" alt="' . $user->lang['EDIT'] . '" title="' . $user->lang['EDIT'] . '" />',
		'ICON_DELETE'				=> '<img src="' . $phpbb_admin_path . 'images/icon_delete.gif" alt="' . $user->lang['DELETE'] . '" title="' . $user->lang['DELETE'] . '" />',
		'ICON_DELETE_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_delete_disabled.gif" alt="' . $user->lang['DELETE'] . '" title="' . $user->lang['DELETE'] . '" />',
		'ICON_SYNC'					=> '<img src="' . $phpbb_admin_path . 'images/icon_sync.gif" alt="' . $user->lang['RESYNC'] . '" title="' . $user->lang['RESYNC'] . '" />',
		'ICON_SYNC_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_sync_disabled.gif" alt="' . $user->lang['RESYNC'] . '" title="' . $user->lang['RESYNC'] . '" />',
	));

	page_header($title);
}

function adm_page_footer()
{
	page_footer();
}

/**
* Generate back link for acp pages
*/
function adm_back_link($u_action)
{
	global $user;
	return '<br /><br /><a href="' . $u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>';
}

?>