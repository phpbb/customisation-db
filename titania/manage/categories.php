<?php
/**
*
* @package Titania
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

titania::_include('functions_posting', 'generate_type_select');

$category_id 	= request_var('c', 0);
$submit 	= (isset($_POST['submit'])) ? true : false;
$action		= request_var('action', '');

switch ($action)
{
	case 'add' :
	case 'edit' :
		$category_object = new titania_category;

		if ($action == 'edit')
		{
			if (!$category_id)
			{
				trigger_error(phpbb::$user->lang['NO_CATEGORY']);
			}

			$category_object->load($category_id);
		}

		// Load the message object
		$message_object = new titania_message($category_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
		));
		$message_object->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
		));

		$category_object->post_data($message_object);

		$message_object->display();

		// Define some variables for use later with keeping language-based category names the same in the DB during submit
		$old_category_name = $category_object->category_name;
		$old_category_name_clean = $category_object->category_name_clean;
		$old_category_name_lang = (isset(phpbb::$user->lang[$old_category_name])) ? phpbb::$user->lang[$old_category_name] : $old_category_name;

		$error = array();

		if($submit)
		{
			// Goodbye to some of the old category data...
			unset($category_object->category_name);

			$category_object->category_id = ($action == 'edit') ? $category_id : '';
			$category_name = utf8_normalize_nfc(request_var('category_name', '', true));
			$category_object->category_name = ($category_name == $old_category_name_lang) ? $old_category_name : $category_name;
			$category_object->category_name_clean = utf8_normalize_nfc(request_var('category_name_clean', '', true));
			$category_object->parent_id = request_var('category_parent', 0);
			$category_object->category_visible = request_var('category_visible', 1);
			$category_object->category_type = request_var('category_type', 0);

			// Check for errors
			if (!$category_object->category_name || !$category_object->category_name_clean)
			{
				$error[] = phpbb::$user->lang['NO_CATEGORY_NAME'];
			}
			if ($action == 'edit' && $category_object->parent_id == $category_object->category_id)
			{
				$error[] = phpbb::$user->lang['CATEGORY_DUPLICATE_PARENT'];
			}

			// We have no errors
			if (!sizeof($error))
			{
				// Set left_id and right_id to proper values
				if (!$category_object->category_id)
				{
					// no category_id means we're creating a new category
					if ($category_object->parent_id)
					{
						$sql = 'SELECT left_id, right_id
							FROM ' . TITANIA_CATEGORIES_TABLE . '
							WHERE category_id = ' . $category_object->parent_id;
						$result = phpbb::$db->sql_query($sql);
						$row = phpbb::$db->sql_fetchrow($result);
						phpbb::$db->sql_freeresult($result);

						if (!$row)
						{
							trigger_error(phpbb::$user->lang['PARENT_NOT_EXIST']);
						}

						$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
							SET left_id = left_id + 2, right_id = right_id + 2
							WHERE left_id > ' . $row['right_id'];
						phpbb::$db->sql_query($sql);

						$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
							SET right_id = right_id + 2
							WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
						phpbb::$db->sql_query($sql);

						$category_object->left_id = $row['right_id'];
						$category_object->right_id = $row['right_id'] + 1;
					}
					else
					{
						$sql = 'SELECT MAX(right_id) AS right_id
							FROM ' . TITANIA_CATEGORIES_TABLE;
						$result = phpbb::$db->sql_query($sql);
						$row = phpbb::$db->sql_fetchrow($result);
						phpbb::$db->sql_freeresult($result);

						$category_object->left_id = $row['right_id'] + 1;
						$category_object->right_id = $row['right_id'] + 2;
					}
				}
				else
				{
					$category_check = new titania_category;
					$category_check->load($category_id);

					if ($category_check->parent_id != $category_object->parent_id)
					{
						if ($category_check->category_id != $category_object->parent_id)
						{
							$errors_extra = $category_object->move_category($category_object->parent_id);

							// Check for errors from moving the category
							if (sizeof($errors_extra))
							{
								$error = array_merge($error, $errors_extra);
							}
						}
						else
						{
							$category_object->parent_id = $category_check->parent_id;
						}
					}
				}

				// Only update category if no errors occurred from moving it
				if (!sizeof($error))
				{
					// Now we submit the category information...
					$category_object->submit();

					// Redirect back to the previous category
					redirect(titania_url::build_url('manage/categories', array('c' => $category_object->parent_id)));
				}
			}
		}

		// Generate data for category type dropdown box
		generate_type_select($category_object->category_type);

		phpbb::$template->assign_vars(array(
			'ERROR_MSG'						=> (sizeof($error)) ? implode('<br />', $error) : '',
			'CATEGORY' 						=> $category_id,
			'CATEGORY_NAME'					=> (isset(phpbb::$user->lang[$category_object->category_name])) ? phpbb::$user->lang[$category_object->category_name] : $category_object->category_name,
			'CATEGORY_NAME_CLEAN'			=> $category_object->category_name_clean,
			'CATEGORY_VISIBLE' 				=> $category_object->category_visible,
			'SECTION_NAME'					=> ($action == 'add') ? phpbb::$user->lang['CREATE_CATEGORY'] : phpbb::$user->lang['EDIT_CATEGORY'] . ' - ' . ((isset(phpbb::$user->lang[$old_category_name])) ? phpbb::$user->lang[$old_category_name] : $old_category_name),

			'U_ACTION'						=> ($action == 'add') ? titania_url::build_url('manage/categories', array('c' => $category_id, 'action' => 'add')) : titania_url::build_url('manage/categories', array('c' => $category_id, 'action' => 'edit')),
			'U_BACK'						=> ($action == 'add') ? titania_url::build_url('manage/categories', array('c' => $category_id)) : titania_url::build_url('manage/categories', array('c' => $category_object->parent_id)),

			'S_ADD_CATEGORY' 				=> ($action == 'add') ? true : false,
			'S_EDIT_CATEGORY' 				=> ($action == 'edit') ? true : false,
			'S_MOVE_CATEGORY_OPTIONS'		=> (isset($category_object->parent_id)) ? generate_category_select($category_object->parent_id, true) : generate_category_select($category_id, true),
		));
	break;
	case 'move_up' :
	case 'move_down' :
		$category_object = new titania_category;

		if (!$category_id)
		{
			trigger_error(phpbb::$user->lang['NO_CATEGORY']);
		}

		$category_object->load($category_id);

		$category_object->move_category_by($action, 1);

		phpbb::$template->assign_vars(array(
			'CATEGORY' 				=> $category_object->category_id,

			'S_MOVE_CATEGORY' 		=> true,
		));

		// Redirect back to previous category to avoid problems
		redirect(titania_url::build_url('manage/categories', array('c' => $category_object->parent_id)));
	break;
	case 'delete' :
		$category_object = new titania_category;

		if (!$category_id)
		{
			trigger_error(phpbb::$user->lang['NO_CATEGORY']);
		}

		$category_object->load($category_id);

		$parent_id = ($category_object->parent_id == $category_object->category_id) ? 0 : $category_object->parent_id;

		$error = array();

		if($submit)
		{
			$action_contribs	= request_var('action_contribs', '');
			$contribs_to_id		= request_var('contribs_to_id', 0);

			// Check for errors
			$sql = 'SELECT category_id
				FROM ' . TITANIA_CATEGORIES_TABLE . "
				WHERE parent_id = $category_object->category_id";
			$result = phpbb::$db->sql_query($sql);
			$children_row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			// Check if category contains subcategories. If yes, then return an error.
			if ($children_row)
			{
				$error[] = phpbb::$user->lang['CATEGORY_HAS_CHILDREN'];
			}
			else
			{
				if ($action_contribs == 'move' && !empty($contribs_to_id))
				{
					$error = array_merge($error, $category_object->move_category_content($contribs_to_id));
				}
			}

			if (!sizeof($error))
			{
				// Delete category
				$category_object->delete();

				// Redirect back to the previous category
				redirect(titania_url::build_url('manage/categories', array('c' => $parent_id)));
			}
		}

		phpbb::$template->assign_vars(array(
			'S_DELETE_CATEGORY'				=> true,
			'U_ACTION'						=> titania_url::build_url('manage/categories', array('c' => $category_id, 'action' => 'delete')),
			'U_BACK'						=> 'c_' . $category_object->parent_id,

			'CATEGORY' 						=> $category_id,
			'CATEGORY_NAME'					=> (isset(phpbb::$user->lang[$category_object->category_name])) ? phpbb::$user->lang[$category_object->category_name] : $category_object->category_name,
			'SECTION_NAME'					=> phpbb::$user->lang['DELETE_CATEGORY'] . ' - ' . ((isset(phpbb::$user->lang[$category_object->category_name])) ? phpbb::$user->lang[$category_object->category_name] : $category_object->category_name),
			'S_HAS_SUBCATS'					=> ($category_object->right_id - $category_object->left_id > 1) ? true : false,
			'S_MOVE_CATEGORY_OPTIONS'		=> generate_category_select($category_object->parent_id, true),
			'ERROR_MSG'						=> (sizeof($error)) ? implode('<br />', $error) : '')
		);

	break;
	default :
		titania::_include('functions_display', 'titania_display_categories');

		titania_display_categories($category_id, 'categories', true);

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
					((isset(phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']])) ? phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']] : $categories_ary[$row['category_id']]['category_name'])	=> titania_url::build_url('manage/categories', array('c' => $row['category_id'])),
				));
			}

			// Self
			$category_object->__set_array($categories_ary[$category_id]);
			titania::generate_breadcrumbs(array(
				((isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'])	=> titania_url::build_url('manage/categories', array('c' => $category_id)),
			));

			// Second set of breadcrumbs for category navigation

			// Parents
			foreach (array_reverse(titania::$cache->get_category_parents($category_id)) as $row)
			{
				$category_object->__set_array($categories_ary[$row['category_id']]);
				titania::generate_breadcrumbs(array(
					((isset(phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']])) ? phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']] : $categories_ary[$row['category_id']]['category_name'])	=> titania_url::build_url('manage/categories', array('c' => $row['category_id'])),
				), $block = 'nav_categories');
			}

			// Self
			$category_object->__set_array($categories_ary[$category_id]);
			titania::generate_breadcrumbs(array(
				((isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'])	=> titania_url::build_url('manage/categories', array('c' => $category_id)),
			), $block = 'nav_categories');

			unset($categories_ary, $category_object);
		}

		phpbb::$template->assign_vars(array(
			'ICON_MOVE_UP'			=> '<img src="' . titania::$images_path . 'icon_up.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . titania::$images_path . 'icon_up_disabled.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_DOWN'		=> '<img src="' . titania::$images_path . 'icon_down.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . titania::$images_path . 'icon_down_disabled.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
			'ICON_EDIT'			=> '<img src="' . titania::$images_path . 'icon_edit.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
			'ICON_EDIT_DISABLED'				=> '<img src="' . titania::$images_path . 'icon_edit_disabled.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
			'ICON_DELETE'			=> '<img src="' . titania::$images_path . 'icon_delete.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . titania::$images_path . 'icon_delete_disabled.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',
			'SECTION_NAME'			=> '<a href="' . titania_url::build_url('manage/categories') . '">' . phpbb::$user->lang['MANAGE_CATEGORIES'] . '</a>',

			'U_CREATE_CATEGORY'		=> titania_url::build_url('manage/categories', array('c' => $category_id, 'action' => 'add')),
			'U_MANAGE_CATEGORIES'		=> titania_url::build_url('manage/categories'),

			'S_MANAGE'			=> true,
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
