<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\titania\controller\manage;

class categories extends base
{
	/** @var \titania_category */
	protected $category;

	const ROOT_CATEGORY = 0;

	public function list_categories($id)
	{
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$this->setup();
		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		if ($id)
		{
			$this->load_category($id);
			$this->generate_breadcrumbs();
		}

		\titania::_include('functions_display', 'titania_display_categories');
		titania_display_categories($this->id, 'categories', true);

		$this->template->assign_vars(array(
			'SECTION_NAME'			=> '<a href="' . $this->helper->route('phpbb.titania.manage.categories') . '">' . $this->user->lang['MANAGE_CATEGORIES'] . '</a>',

			'U_CREATE_CATEGORY'		=> $this->category->get_manage_url('add'),
			'U_MANAGE_CATEGORIES'	=> $this->helper->route('phpbb.titania.manage.categories'),

			'S_MANAGE'			=> true,
		));

		return $this->helper->render('manage/categories.html', 'MANAGE_CATEGORIES');
	}

	public function category_action($id, $action)
	{
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		if (!in_array($action, array('add', 'edit', 'delete', 'move_up', 'move_down')))
		{
			return $this->helper->error('INVALID_ACTION', 404);
		}

		$this->setup();

		if ($action != 'add')
		{
			$this->load_category($id);
		}

		$this->{$action}();
		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		if ($id)
		{
			$this->generate_breadcrumbs();
		}

		return $this->helper->render('manage/categories.html', 'MANAGE_CATEGORIES');
	}

	protected function add()
	{
		if ($this->common_post(new \titania_category))
		{
			return;
		}

		$this->template->assign_vars(array(
			'S_ADD_CATEGORY' 		=> true,
			'SECTION_NAME'			=> $this->user->lang['CREATE_CATEGORY'],
			'U_ACTION'				=> $this->category->get_manage_url('add'),
			'U_BACK'				=> $this->category->get_manage_url(),
		));
	}

	/**
	* Edit category.
	*
	* @return null
	*/
	protected function edit()
	{
		// Define some variables for use later with keeping language-based category names the same in the DB during submit
		$old_settings = array(
			'name'			=> $this->category->category_name,
			'name_clean'	=> $this->category->category_name_clean,
			'name_lang'		=> $this->category->get_name(),
			'parent_id'		=> $this->category->parent_id,
		);

		if ($this->common_post($this->category, $old_settings))
		{
			return;
		}

		$this->template->assign_vars(array(
			'S_EDIT_CATEGORY' 		=> true,
			'SECTION_NAME'			=> $this->user->lang['EDIT_CATEGORY'] . ' - ' . $old_settings['name_lang'],
			'U_ACTION'				=> $this->category->get_manage_url('edit'),
			'U_BACK'				=> $this->helper->route('phpbb.titania.manage.categories', array('id' => $this->category->parent_id)),
		));
	}

	/**
	* Common handler for add/edit action.
	*
	* @param \titania_category	Category object.
	* @param bool|array			Old settings
	* @return bool Returns true if category was submitted.
	*/
	protected function common_post($category, $old_settings = false)
	{
		$message = new \titania_message($category);
		$message->set_auth(array(
			'bbcode'		=> $this->auth->acl_get('u_titania_bbcode'),
			'smilies'		=> $this->auth->acl_get('u_titania_smilies'),
		));
		$message->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
		));

		$category->post_data($message);
		$error = array();

		if ($this->request->is_set_post('submit'))
		{
			$category = $this->set_submitted_settings($category);

			if (!empty($old_settings) && $category->category_name == $old_settings['name_lang'])
			{
				$category->category_name = $old_settings['name'];
			}
			$error = $category->validate();

			if (($form_error = $message->validate_form_key()) !== false)
			{
				$error[] = $form_error;
			}

			if (empty($error))
			{
				$error = $this->submit($category, $old_settings);

				if (empty($error))
				{
					return true;
				}
			}
		}

		\titania::_include('functions_posting', 'generate_type_select');

		// Generate data for category type dropdown box
		generate_type_select($category->category_type);
		$message->display();

		$this->template->assign_vars(array(
			'ERROR_MSG'						=> (!empty($error)) ? implode('<br />', $error) : '',
			'CATEGORY' 						=> $category->category_id,
			'CATEGORY_NAME'					=> $category->get_name(),
			'CATEGORY_NAME_CLEAN'			=> $category->category_name_clean,
			'CATEGORY_VISIBLE' 				=> $category->category_visible,

			'S_MOVE_CATEGORY_OPTIONS'		=> generate_category_select($category->parent_id, true),
			'S_INTEGRATE_DEMO'				=> ($category->category_options & TITANIA_CAT_FLAG_DEMO) ? 'checked="checked"' : '',
			'S_SUPPORT_ALL_VERSIONS'		=> ($category->category_options & TITANIA_CAT_FLAG_ALL_VERSIONS) ? 'checked="checked"' : '',
		));

		return false;
	}

	/**
	* Move category up.
	*
	* @return null
	*/
	protected function move_up()
	{
		return $this->move('up');
	}

	/**
	* Move category down.
	*
	* @return null
	*/
	protected function move_down()
	{
		return $this->move('down');
	}

	/**
	* Move category.
	*
	* @param string $direction	Direction: up|down
	* @return null
	*/
	protected function move($direction)
	{
		$hash = $this->request->variable('hash', '');

		if (!check_link_hash($hash, 'category_action'))
		{
			redirect($this->category->get_manage_url());
		}

		$this->category->move_category_by("move_$direction");

		// Redirect back to parent category to avoid problems
		redirect($this->helper->route('phpbb.titania.manage.categories', array('id' => $this->category->parent_id)));
	}

	/**
	* Delete category.
	*
	* @return null
	*/
	protected function delete()
	{
		$parent_id = ($this->category->parent_id == $this->category->category_id) ? self::ROOT_CATEGORY : $this->category->parent_id;

		$error = array();

		if ($this->request->is_set_post('submit'))
		{
			if (check_form_key('category_move'))
			{
				$action_contribs	= $this->request->variable('action_contribs', '');
				$contribs_to_id		= $this->request->variable('contribs_to_id', 0);

				// Check for errors
				$sql = 'SELECT category_id
					FROM ' . TITANIA_CATEGORIES_TABLE . "
					WHERE parent_id = {$this->category->category_id}";
				$result = $this->db->sql_query($sql);
				$children_row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				// Check if category contains subcategories. If yes, then return an error.
				if ($children_row)
				{
					$error[] = $this->user->lang['CATEGORY_HAS_CHILDREN'];
				}
				else
				{
					if ($action_contribs == 'move' && !empty($contribs_to_id))
					{
						$error = array_merge($error, $this->category->move_category_content($contribs_to_id));
					}
				}
			}
			else
			{
				$error[] = $this->user->lang['INVALID_FORM'];
			}

			if (empty($error))
			{
				// Delete category
				$this->category->delete();

				// Redirect back to the parent category
				redirect($this->helper->route('phpbb.titania.manage.categories', array('id' => $parent_id)));
			}
		}

		add_form_key('category_move');

		\titania::_include('functions_posting', 'generate_category_select');

		$this->template->assign_vars(array(
			'S_DELETE_CATEGORY'				=> true,
			'U_ACTION'						=> $this->category->get_manage_url('delete'),
			'U_BACK'						=> $this->helper->route('phpbb.titania.manage.categories', array('id' => $parent_id)),

			'CATEGORY' 						=> $this->category->category_id,
			'CATEGORY_NAME'					=> $this->category->get_name(),
			'SECTION_NAME'					=> $this->user->lang['DELETE_CATEGORY'] . ' - ' . $this->category->get_name(),
			'S_HAS_SUBCATS'					=> ($this->category->right_id - $this->category->left_id > 1),
			'S_MOVE_CATEGORY_OPTIONS'		=> generate_category_select($this->category->parent_id, true),
			'ERROR_MSG'						=> (!empty($error)) ? implode('<br />', $error) : '')
		);
	}

	/**
	* Common initializing tasks.
	*
	* @return null
	*/
	protected function setup()
	{
		$this->id = self::ROOT_CATEGORY;
		$this->category = new \titania_category;
		$this->user->add_lang('acp/common');
	}

	/**
	* Load category.
	*
	* @param int $id		Category id.
	* @throws \Exception	Throws exception if no category found.
	* @return null
	*/
	protected function load_category($id)
	{
		$this->id = (int) $id;

		if (!$this->id || !$this->category->load($this->id))
		{
			throw new \Exception($this->user->lang['NO_CATEGORY']);
		}
	}

	/**
	* Check user's authorization.
	*
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth()
	{
		return $this->auth->acl_get('u_titania_admin');
	}

	/**
	* Generate category breadcrumbs.
	*
	* @return null
	*/
	protected function generate_breadcrumbs()
	{
		$categories = $this->cache->get_categories();
		$category = new \titania_category;
		$crumbs = array();

		// Parents
		foreach (array_reverse($this->cache->get_category_parents($this->id)) as $data)
		{
			$category->__set_array($categories[$data['category_id']]);
			$crumbs[$category->get_name()] = $category->get_manage_url();
		}
		// Self
		$crumbs[$this->category->get_name()] = $this->category->get_manage_url();

		$this->display->generate_breadcrumbs($crumbs);
		$this->display->generate_breadcrumbs($crumbs, 'nav_categories');
	}

	/**
	* Set submitted settings on the category object.
	*
	* @param \titania_category $category	Category object.
	* @return \titania_category
	*/
	protected function set_submitted_settings($category)
	{
		$category->__set_array(array(
			'category_name'			=> $this->request->variable('category_name', '', true),
			'category_name_clean'	=> $this->request->variable('category_name_clean', '', true),
			'parent_id'				=> $this->request->variable('category_parent', 0),
			'category_visible'		=> $this->request->variable('category_visible', 1),
			'category_type'			=> $this->request->variable('category_type', 0),
			'category_options'		=> 0,
		));

		// Set category options
		$category->category_options += ($this->request->variable('integrate_demo', false)) ? TITANIA_CAT_FLAG_DEMO : 0; 
		$category->category_options += ($this->request->variable('support_all_versions', false)) ? TITANIA_CAT_FLAG_ALL_VERSIONS : 0;

		return $category;
	}

	/**
	* Submit add/edit settings.
	*
	* @param \titania_category		Category object.
	* @param bool|array				Old settings.
	*
	* @return array Returns any errors found. Otherwise redirects to parent category.
	*/
	protected function submit($category, $old_settings = false)
	{
		$error = array();

		// Set left_id and right_id to proper values
		if (!$category->category_id)
		{
			$error_msg = $category->set_left_right_ids();

			if ($error_msg)
			{
				$error[] = $error_msg;
			}
		}
		else
		{
			if ($old_settings['parent_id'] != $category->parent_id)
			{
				if ($category->category_id != $category->parent_id)
				{
					$errors_extra = $category->move_category($category->parent_id);

					// Check for errors from moving the category
					if (!empty($errors_extra))
					{
						$error = array_merge($error, $errors_extra);
					}
				}
			}
		} 

		// Only update category if no errors occurred from moving it
		if (empty($error))
		{
			// Now we submit the category information...
			$category->submit();

			// Redirect back to the parent category
			redirect($this->helper->route('phpbb.titania.manage.categories', array('id' => $category->parent_id)));
		}

		return $error;
	}
}
