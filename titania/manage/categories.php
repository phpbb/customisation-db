<?php
/**
 *
 * @package titania
 * @version $Id: categories.php 937 2010-03-30 01:21:50Z Tom $
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

if (!phpbb::$auth->acl_gets('u_titania_admin'))
{
	titania::needs_auth();
}

phpbb::$user->add_lang('acp/common');

$category_id = request_var('c', 0);
$submit = (isset($_POST['submit'])) ? true : false;
$action = request_var('action', '');

switch ($action)
{
	case 'add' :
	case 'edit' :
		$test = "Testing";
		phpbb::$template->assign_vars(array(
			'U_TEST'		=> $test,
			'S_CATEGORY' 		=> $category_id,
		));
	break;
	case 'move_up' :
	case 'move_down' :
		$test = "Testing";
		phpbb::$template->assign_vars(array(
			'U_TEST'		=> $test,
			'S_CATEGORY' 		=> $category_id,
		));
	break;
	case 'delete' :
		$test = "Testing";
		phpbb::$template->assign_vars(array(
			'U_TEST'		=> $test,
			'S_CATEGORY' 		=> $category_id,
		));
	break;
	default :
		$phpbb_admin_path = titania::$config->phpbb_script_path . 'adm/';

		titania::_include('functions_display', 'titania_display_categories');

		titania_display_categories($category_id);

		if ($category_id != 0)
		{
			// Breadcrumbs
			$category_object = new titania_category;
			$categories_ary = titania::$cache->get_categories();

			// Parents
			foreach (array_reverse(titania::$cache->get_category_parents($category_id)) as $row)
			{
				$category_object->__set_array($categories_ary[$row['category_id']]);
				titania::generate_breadcrumbs(array(
					((isset(phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']])) ? phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']] : $categories_ary[$row['category_id']]['category_name'])	=> titania_url::$root_url . $category_object->get_manage_url(),
				));
			}

			// Self
			$category_object->__set_array($categories_ary[$category_id]);
			titania::generate_breadcrumbs(array(
				((isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'])	=> titania_url::$root_url . $category_object->get_manage_url(),
			));
			unset($categories_ary, $category_object);
		}

		phpbb::$template->assign_vars(array(
			'ICON_MOVE_UP'				=> '<img src="' . $phpbb_admin_path . 'images/icon_up.gif" alt="' . $user->lang['MOVE_UP'] . '" title="' . $user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_up_disabled.gif" alt="' . $user->lang['MOVE_UP'] . '" title="' . $user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_DOWN'			=> '<img src="' . $phpbb_admin_path . 'images/icon_down.gif" alt="' . $user->lang['MOVE_DOWN'] . '" title="' . $user->lang['MOVE_DOWN'] . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . $phpbb_admin_path . 'images/icon_down_disabled.gif" alt="' . $user->lang['MOVE_DOWN'] . '" title="' . $user->lang['MOVE_DOWN'] . '" />',
			'ICON_EDIT'					=> '<img src="' . $phpbb_admin_path . 'images/icon_edit.gif" alt="' . $user->lang['EDIT'] . '" title="' . $user->lang['EDIT'] . '" />',
			'ICON_EDIT_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_edit_disabled.gif" alt="' . $user->lang['EDIT'] . '" title="' . $user->lang['EDIT'] . '" />',
			'ICON_DELETE'				=> '<img src="' . $phpbb_admin_path . 'images/icon_delete.gif" alt="' . $user->lang['DELETE'] . '" title="' . $user->lang['DELETE'] . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . $phpbb_admin_path . 'images/icon_delete_disabled.gif" alt="' . $user->lang['DELETE'] . '" title="' . $user->lang['DELETE'] . '" />',

			'S_MANAGE' 			=> true,
		));
	break;
}

function trigger_back($message)
{
	$message = (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message;

	$message .= '<br /><br /><a href="' . titania_url::build_url('manage/categories') . '">' . phpbb::$user->lang['BACK'] . '</a>';

	trigger_error($message);

}

titania::page_header('MANAGE_CATEGORIES');

titania::page_footer(true, 'manage/categories.html');
