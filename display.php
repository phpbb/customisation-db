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

class display
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\controller\helper $controller_helper
	* @param \phpbb\path_helper $path_helper
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $controller_helper, \phpbb\path_helper $path_helper)
	{
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->controller_helper = $controller_helper;
		$this->path_helper = $path_helper;
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

		$u_my_contribs = $u_manage = false;

		if ($this->user->data['is_registered'] && !$this->user->data['is_bot'])
		{
			$u_my_contribs = $this->controller_helper->route('phpbb.titania.author', array(
				'author'	=> $this->user->data['username_clean'],
				'page'		=> 'contributions',
			));
		}

		if (!empty(\titania_types::find_authed()) || $this->auth->acl_get('u_titania_mod_contrib_mod') || $this->auth->acl_get('u_titania_mod_post_mod'))
		{
			$u_manage = $this->controller_helper->route('phpbb.titania.manage');
		}

		$web_root_path = $this->path_helper->get_web_root_path();
		$style_path = $web_root_path . 'ext/phpbb/titania/styles/' . rawurlencode($this->user->style['style_path']) . '/';

		$this->template->assign_vars(array(
			'T_TITANIA_TEMPLATE_PATH'	=> $style_path . 'template',
			'T_TITANIA_THEME_PATH'		=> $style_path . 'theme',
			'T_TITANIA_IMAGES_PATH'		=> $web_root_path . 'images',
			'T_TITANIA_STYLESHEET'		=> $style_path . 'theme/stylesheet.css',
			'T_TITANIA_ASSETS_PATH'		=> $web_root_path . 'ext/phpbb/titania/assets',
			'TITANIA_ROOT_PATH'			=> $web_root_path,

			'U_MANAGE'					=> $u_manage,
			'U_MY_CONTRIBUTIONS'		=> $u_my_contribs,
			'U_ALL_CONTRIBUTIONS'		=> $this->controller_helper->route('phpbb.titania.all_contribs'),
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
}
