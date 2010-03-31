<?php
/**
*
* @package Titania
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
		phpbb::$template->assign_vars(array(
			'CATEGORY' 				=> $category_id,
			'SECTION_NAME'			=> ($action == 'add') ? phpbb::$user->lang['CREATE_CATEGORY'] : phpbb::$user->lang['EDIT_CATEGORY'] . ' - ' . $category_name,

			'S_EDIT_CATEGORY' 		=> ($action == 'edit') ? true : false,
			'S_ADD_CATEGORY' 		=> ($action == 'add') ? true : false,

		));
	break;
	case 'move_up' :
	case 'move_down' :
		$category_object = new titania_category;

		if (!$category_id)
		{
			trigger_error($user->lang['NO_CATEGORY'], E_USER_WARNING);
		}

		$sql = 'SELECT *
			FROM ' . TITANIA_CATEGORIES_TABLE . "
			WHERE category_id = $category_id";
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error($user->lang['NO_CATEGORY'], E_USER_WARNING);
		}

		$move_category_name = $category_object->move_category_by($row, $action, 1);

		if ($move_category_name !== false)
		{
			// add_log('admin', 'LOG_FORUM_' . strtoupper($action), $row['category_name'], $move_category_name);
			$cache->destroy('sql', TITANIA_CATEGORIES_TABLE);
		}

		phpbb::$template->assign_vars(array(
			'CATEGORY' 				=> $category_id,

			'S_MOVE_CATEGORY' 		=> true,
		));

		// Redirect back to previous category to avoid problems
		redirect(titania_url::build_url('manage/categories', array('c' => $row['parent_id'])));
	break;
	case 'delete' :
		$category_object = new titania_category;

		if (!$category_id)
		{
			trigger_error($user->lang['NO_CATEGORY'], E_USER_WARNING);
		}

		$category_data = $category_object->get_category_info($category_id);

		$subcats_id = array();
		$subcats = $category_object->get_category_branch($category_id, 'children');

		foreach ($subcats as $row)
		{
			$subcats_id[] = $row['category_id'];
		}

		$categories_list = $category_object->make_category_select($category_data['parent_id'], $subcats_id);

		$sql = 'SELECT category_id
			FROM ' . TITANIA_CATEGORIES_TABLE . "
			WHERE category_id <> $category_id";
		$result = phpbb::$db->sql_query_limit($sql, 1);

		if (phpbb::$db->sql_fetchrow($result))
		{
			phpbb::$template->assign_vars(array(
				'S_MOVE_CATEGORY_OPTIONS'		=> $category_object->make_category_select($category_data['parent_id'], $subcats_id, false, true)) // , false, true, false???
			);
		}
		phpbb::$db->sql_freeresult($result);

		$parent_id = ($category_object->parent_id == $category_id) ? 0 : $category_object->parent_id;

		if($submit)
		{
			$action_subcats	= request_var('action_subcats', '');
			$subcats_to_id	= request_var('subcats_to_id', 0);
			$action_contribs		= request_var('action_contribs', '');
			$contribs_to_id		= request_var('contribs_to_id', 0);

			$errors = $category_object->delete_category($category_id, $action_contribs, $action_subcats, $contribs_to_id, $subcats_to_id);

			if (sizeof($errors))
			{
				break;
			}

			$cache->destroy('sql', TITANIA_CATEGORIES_TABLE);

			trigger_error($user->lang['CATEGORY_DELETED']);
		}

		phpbb::$template->assign_vars(array(
			'S_DELETE_CATEGORY'		=> true,
			'U_ACTION'				=> titania_url::build_url('manage/categories', array('c' => $category_id, 'action' => 'delete')),
			'U_BACK'				=> 'c_' . $category_object->parent_id,

			'CATEGORY' 		=> $category_id,
			'CATEGORY_NAME'		=> (isset(phpbb::$user->lang[$category_data['category_name']])) ? phpbb::$user->lang[$category_data['category_name']] : $category_data['category_name'],
			'SECTION_NAME'		=> phpbb::$user->lang['DELETE_CATEGORY'],
			'S_HAS_SUBCATS'		=> ($category_data['right_id'] - $category_data['left_id'] > 1) ? true : false,
			'S_CATEGORIES_LIST'			=> $categories_list,
			'S_ERROR'				=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'				=> (sizeof($errors)) ? implode('<br />', $errors) : '')
		);

	break;
	default :
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
					((isset(phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']])) ? phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']] : $categories_ary[$row['category_id']]['category_name'])	=> titania_url::build_url('manage/categories', array('c' => $category_id)),
				));
			}

			// Self
			$category_object->__set_array($categories_ary[$category_id]);
			titania::generate_breadcrumbs(array(
				((isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'])	=> titania_url::build_url('manage/categories', array('c' => $category_id)),
			));
			unset($categories_ary, $category_object);
		}

		phpbb::$template->assign_vars(array(
			'ICON_MOVE_UP'				=> '<img src="' . titania::$absolute_board . 'adm/images/icon_up.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_up_disabled.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_DOWN'			=> '<img src="' . titania::$absolute_board . 'adm/images/icon_down.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . titania::$absolute_board . 'adm/images/icon_down_disabled.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
			'ICON_EDIT'					=> '<img src="' . titania::$absolute_board . 'adm/images/icon_edit.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
			'ICON_EDIT_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_edit_disabled.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
			'ICON_DELETE'				=> '<img src="' . titania::$absolute_board . 'adm/images/icon_delete.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_delete_disabled.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',

			'U_CREATE_CATEGORY'			=> ($category_id) ? titania_url::build_url('manage/categories', array('c' => $category_id, 'action' => 'add')) : titania_url::build_url('manage/categories', array('action' => 'add')),

			'S_MANAGE' 					=> true,
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
