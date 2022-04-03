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

namespace phpbb\titania;

use phpbb\titania\contribution\type\collection as type_collection;

class display
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var type_collection */
	protected $types;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\config\config $config
	* @param cache\service $cache
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\controller\helper $controller_helper
	* @param \phpbb\path_helper $path_helper
	* @parma type_collection $types
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, cache\service $cache, \phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $controller_helper, \phpbb\path_helper $path_helper, type_collection $types)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->cache = $cache;
		$this->template = $template;
		$this->user = $user;
		$this->controller_helper = $controller_helper;
		$this->path_helper = $path_helper;
		$this->types = $types;
	}

	/**
	* Assign global template variables
	*
	* @return null
	*/
	public function assign_global_vars()
	{
		$this->generate_breadcrumbs(array(
			'CUSTOMISATION_DATABASE'	=> $this->controller_helper->route('phpbb.titania.index'),
		));

		$u_manage = false;

		$manageable_types = $this->types->find_authed();

		if (!empty($manageable_types) || $this->auth->acl_get('u_titania_mod_contrib_mod') || $this->auth->acl_get('u_titania_mod_post_mod'))
		{
			$u_manage = $this->controller_helper->route('phpbb.titania.manage');
		}

		$web_root_path = $this->path_helper->get_web_root_path();
		$style_path = $web_root_path . 'ext/phpbb/titania/styles/' . rawurlencode($this->user->style['style_path']) . '/';

		$this->template->assign_vars(array(
			'T_TITANIA_TEMPLATE_PATH'	=> $style_path . 'template',
			'T_TITANIA_THEME_PATH'		=> $style_path . 'theme',
			'T_TITANIA_IMAGES_PATH'		=> $web_root_path . 'images',
			'T_TITANIA_ASSETS_PATH'		=> $web_root_path . 'ext/phpbb/titania/assets',
			'TITANIA_ROOT_PATH'			=> $web_root_path,

			'U_MANAGE'					=> $u_manage,
			'U_ALL_SUPPORT'				=> $this->controller_helper->route('phpbb.titania.support'),
			'U_TITANIA_INDEX'			=> $this->controller_helper->route('phpbb.titania.index'),
			'U_TITANIA_FAQ'				=> $this->controller_helper->route('phpbb.titania.faq'),

			'S_IN_TITANIA'				=> true,
		));
	}

	/**
	* Generate the navigation tabs/menu for display
	*
	* @param array $nav_ary The array of data to output
	* @param string $current_page The current page
	* @param string $default page The default page to show
	* @param string $block Optionally specify a custom template block loop name
	*
	* @return null
	*/
	public function generate_nav($nav_ary, &$current_page, $default, $block = 'nav_menu')
	{
		$current_page = (isset($nav_ary[$current_page])) ? $current_page : $default;

		if (!isset($nav_ary[$current_page]) || (isset($nav_ary[$current_page]['auth']) && !$nav_ary[$current_page]['auth']))
		{
			// Default page is not accessable, try the first page in the list
			$pages = array_keys($nav_ary);
			$current_page = $pages[0];
		}

		$retry_current_page = false;
		foreach ($nav_ary as $page => $data)
		{
			if ($retry_current_page)
			{
				$current_page = $page;
			}

			// If they do not have authorization, skip.
			if (isset($data['auth']) && !$data['auth'])
			{
				if ($page == $current_page)
				{
					$retry_current_page = true;
				}

				continue;
			}

			if (!isset($data['display']) || $data['display'])
			{
				$count_lang = (isset($data['count'])) ? " ({$data['count']})" : '';
				$this->template->assign_block_vars($block, array(
					'L_TITLE'		=> $this->user->lang($data['title']) . $count_lang,
					'U_TITLE'		=> $data['url'],
					'S_SELECTED'	=> $page == $current_page || ((isset($data['match']) && in_array($current_page, $data['match']))),
					'S_EXTERNAL'	=> !empty($data['external']),
				));

				if (!empty($data['sub_menu']))
				{
					$current_subpage = false;
					$this->generate_nav($data['sub_menu'], $current_subpage, $current_subpage, $block . '.sub_menu');
				}
			}

			$retry_current_page = false;
		}
	}

	/**
	* Assign breadcrumbs to the template
	*
	* @param array $breadcrumbs The array of data to output
	* @param string $block Optionally specify a custom template block loop name
	*
	* @return null
	*/
	public function generate_breadcrumbs($breadcrumbs, $block = 'navlinks')
	{
		foreach ($breadcrumbs as $title => $url)
		{
			$this->template->assign_block_vars($block, array(
				'FORUM_NAME'		=> $this->user->lang($title),
				'U_VIEW_FORUM'		=> $url,
			));
		}
	}

	/**
	* Assign custom fields to template.
	*
	* @param array $fields		Fields.
	* @param array $values		Field values.
	* @param int $group_id		Field group id.
	* @param bool $is_edit		Whether the parent item is being edited.
	* @param string $block		Template block. Defaults to custom_fields.
	*
	* @return null
	*/
	public function generate_custom_fields($fields, $values, $group_id, $is_edit = false, $block = 'custom_fields')
	{
		foreach ($fields as $id => $field)
		{
			if ($is_edit && !$field['editable'])
			{
				continue;
			}

			$this->template->assign_block_vars($block, array(
				'ID'				=> $id,
				'NAME'				=> $this->user->lang($field['name']),
				'EXPLAIN'			=> $this->user->lang($field['explain']),
				'VALUE'				=> (!empty($values[$id])) ? $values[$id] : '',
				'FIELD_TYPE'		=> $field['type'],
				'GROUP_ID'			=> $group_id,
			));
		}
	}

	/**
	 * Determine appropriate folder status image.
	 *
	 * @param string $folder_img		Folder image name holder
	 * @param string $folder_alt		Folder language key holder
	 * @param int $post_count			(Optional) Post count for given topic
	 * @param bool $unread				(Optional) Whether the object is unread. Defaults to false.
	 * @param bool $posted				(Optional) Whether the user has posted in the given topic. Defaults to false.
	 * @param bool $sticky				(Optional) Whether the topic is a sticky. Defaults to false.
	 * @param bool $locked				(Optional) Whether the topic is locked. Defaults to false.
	 */
	public function topic_folder_img(&$folder_img, &$folder_alt, $post_count = 0, $unread = false, $posted = false, $sticky = false, $locked = false)
	{
		if ($sticky)
		{
			$folder = 'sticky_read';
			$folder_new = 'sticky_unread';
		}
		else
		{
			$folder = 'topic_read';
			$folder_new = 'topic_unread';

			// Hot topic threshold is for posts in a topic, which is replies + the first post. ;)
			if ($this->config['hot_threshold'] && ($post_count + 1) >= $this->config['hot_threshold'] && !$locked)
			{
				$folder .= '_hot';
				$folder_new .= '_hot';
			}
		}

		if ($locked)
		{
			$folder .= '_locked';
			$folder_new .= '_locked';
		}

		$folder_img = ($unread) ? $folder_new : $folder;
		$folder_alt = ($unread) ? 'NEW_POSTS' : 'NO_NEW_POSTS';

		// Posted image?
		if ($posted)
		{
			$folder_img .= '_mine';
		}
	}

	/**
	 * Display categories
	 *
	 * @param int $parent_id			The parent id (only show categories under this category)
	 * @param string $blockname 		The name of the template block to use (categories by default)
	 * @param bool $is_manage			Whether the categories are being displayed in management page. Defaults to false.
	 * @param bool $display_full_tree	Whether to display the full category tree.
	 */
	public function display_categories($parent_id = 0, $blockname = 'categories', $is_manage = false, $display_full_tree = false, array $params = array())
	{
		$categories = $this->cache->get_categories();
		$category = new \titania_category;
		$active_parents = array();

		if ($parent_id)
		{
			$active_parents = $this->cache->get_category_parents($parent_id);
			$active_parents = ($active_parents) ? array_keys($active_parents) : array();
			$active_parents[] = $parent_id;
		}

		foreach ($categories as $data)
		{
			$category->__set_array($data);

			$ignore =
				(!$is_manage && (!$category->category_visible || ($category->parent_id && !$categories[$category->parent_id]['category_visible']))) ||
				(!$display_full_tree && $parent_id != $category->parent_id)
			;

			if ($ignore)
			{
				continue;
			}
			$active = in_array($category->category_id, $active_parents) ||
				in_array($category->parent_id, $active_parents)
			;

			$this->template->assign_block_vars(
				$blockname,
				array_merge(
					$category->assign_display(true),
					array(
						'ACTIVE'			=> $active,
						'U_VIEW_CATEGORY'	=> $category->get_url($params),
					)
				)
			);
		}
	}

	/**
	 * Generate the category select (much is from the make_jumpbox function)
	 *
	 * @param array|bool $selected		Array of selected categories. Defaults to false.
	 * @param bool $is_manage			Whether in category management, in which case all are listed
	 * @param bool $disable_parents		Whether to disable categories that do not have a contribution type
	 * @param bool|int $category_type	Category type to limit list to
	 * @return void
	 */
	public function generate_category_select($selected = false, $is_manage = false, $disable_parents = true, $category_type = false)
	{
		if (!is_array($selected))
		{
			$selected = array($selected);
		}

		$right = $padding = 0;
		$padding_store = array('0' => 0);

		$categories = $this->cache->get_categories();
		$hidden_categories = array();
		$category = new \titania_category;

		foreach ($categories as $row)
		{
			$type = $this->types->get($row['category_type']);

			if ($type && (!$type->acl_get('submit') || ($category_type && $type->id != $category_type)))
			{
				continue;
			}
			$category->__set_array($row);

			if ($row['left_id'] < $right)
			{
				$padding++;
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : $padding;
			}

			$right = $row['right_id'];

			if (!$is_manage)
			{
				// Non-postable category with no children, don't display
				$not_postable = $row['category_type'] == 0 && ($row['left_id'] + 1 == $row['right_id']);
				$hidden = !$row['category_visible'] || in_array($row['parent_id'], $hidden_categories);
				$team_only_restriction = $category->is_option_set('team_only') && !$type->acl_get('moderate');

				if ($not_postable || $hidden || $team_only_restriction)
				{
					if ($hidden)
					{
						$hidden_categories[] = $row['category_id'];
					}
					continue;
				}
			}

			$this->template->assign_block_vars('category_select', array(
				'S_SELECTED'		=> in_array($row['category_id'], $selected),
				'S_DISABLED'		=> $row['category_type'] == 0 && $disable_parents,

				'VALUE'				=> $row['category_id'],
				'TYPE'				=> $row['category_type'],
				'NAME'				=> $category->get_name(),
			));

			for ($i = 0; $i < $padding; $i++)
			{
				$this->template->assign_block_vars('category_select.level', array());
			}
		}
	}

	/**
	 * Create a select with the contrib types
	 *
	 * @param int|bool $selected	Selected contrib type id. Defaults to false.
	 * @return void
	 */
	public function generate_type_select($selected = false)
	{
		$this->template->assign_block_vars('type_select', array(
			'S_IS_SELECTED'		=> $selected === false,

			'VALUE'				=> 0,
			'NAME'				=> (isset($this->user->lang['SELECT_CONTRIB_TYPE'])) ? $this->user->lang['SELECT_CONTRIB_TYPE'] : '--',
		));

		foreach ($this->types->get_all() as $key => $type)
		{
			if (!$type->acl_get('submit'))
			{
				continue;
			}

			$this->template->assign_block_vars('type_select', array(
				'S_IS_SELECTED'		=> $key == $selected,

				'VALUE'				=> $key,
				'NAME'				=> (isset($this->user->lang['SELECT_CONTRIB_TYPE'])) ? $type->lang['lang'] : $type->lang['langs'],
			));
		}
	}

	/**
	 * Create a select with the phpBB branches.
	 *
	 * @param array|bool $selected	Array of selected branches. Defaults to false.
	 * @param array|bool $branches	Array of branches to output. Defaults to false.
	 * @return void
	 */
	public function generate_phpbb_version_select($selected = false, $branches = false)
	{
		if (!$branches)
		{
			$branches = get_allowed_phpbb_branches();
		}

		foreach ($branches as $branch => $row)
		{
			$this->template->assign_block_vars('phpbb_branches', array(
				'S_IS_SELECTED'		=> (is_array($selected) && in_array($branch, $selected)) ? true : false,

				'VALUE'				=> $branch,
				'NAME'				=> $row['name'],
			));
		}
	}
}
