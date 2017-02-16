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

namespace phpbb\titania\controller\contribution;

use phpbb\titania\access;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\count;
use phpbb\titania\ext;

class base
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var type_collection */
	protected $types;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var \titania_contribution */
	protected $contrib;

	/** @var bool */
	protected $is_author;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\config\config $config
	* @param \phpbb\db\driver\driver_interface $db
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\controller\helper $helper
	* @param type_collection $types
	* @param \phpbb\request\request $request
	* @param \phpbb\titania\cache\service $cache
	* @param \phpbb\titania\config\config $ext_config
	* @param \phpbb\titania\display $display
	* @param \phpbb\titania\access $access
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request $request, \phpbb\titania\cache\service $cache, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, access $access)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->types = $types;
		$this->request = $request;
		$this->cache = $cache;
		$this->ext_config = $ext_config;
		$this->display = $display;
		$this->access = $access;

		// Add common lang
		$this->user->add_lang_ext('phpbb/titania', 'contributions');
	}

	/**
	* Load contribution.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	* @throws \Exception				Throws exception if contrib is not found.
	* @return null
	*/
	protected function load_contrib($contrib_type, $contrib)
	{
		$type = ($contrib_type) ? $this->types->type_from_url($contrib_type) : false;
		$this->contrib = new \titania_contribution;

		if (!$this->contrib->load($contrib, $type) || !$this->contrib->is_visible())
		{
			throw new \Exception($this->user->lang['CONTRIB_NOT_FOUND']);
		}

		$this->is_author = $this->contrib->is_active_coauthor || $this->contrib->is_author;
		$this->set_access_level();
	}

	/**
	* Assign navigation tabs.
	*
	* @param string $page	Current active page.
	* @return null
	*/
	protected function generate_navigation($page)
	{
		// Count the number of FAQ items to display
		$flags = count::get_flags($this->access->get_level());
		$faq_count = count::from_db($this->contrib->contrib_faq_count, $flags);
		$is_disabled = in_array($this->contrib->contrib_status, array(ext::TITANIA_CONTRIB_CLEANED, ext::TITANIA_CONTRIB_DISABLED));

		/**
		* Menu Array
		*
		* 'filename' => array(
		*	'title'		=> 'nav menu title',
		* 	'url'		=> $page_url,
		*	'auth'		=> ($can_see_page) ? true : false, // Not required, always true if missing
		* ),
		*/
		$nav_ary = array(
			'details' => array(
				'title'		=> 'CONTRIB_DETAILS',
				'url'		=> $this->contrib->get_url(),
			),
			'faq' => array(
				'title'		=> 'CONTRIB_FAQ',
				'url'		=> $this->contrib->get_url('faq'),
				'auth'		=> !$this->access->is_public() || $faq_count,
				'count'		=> $faq_count,
			),
			'support' => array(
				'title'		=> 'CONTRIB_SUPPORT',
				'url'		=> $this->contrib->get_url('support'),
				'auth'		=> $this->ext_config->support_in_titania || $this->access->get_level() < access::PUBLIC_LEVEL,
			),
			'demo'	=> array(
				'title'		=> 'CONTRIB_DEMO',
				'url'		=> '',
				'auth'		=> !empty($this->contrib->contrib_demo),
			),
			'manage' => array(
				'title'		=> 'CONTRIB_MANAGE',
				'url'		=> $this->contrib->get_url('manage'),
				'auth'		=> (($this->is_author && $this->auth->acl_get('u_titania_post_edit_own')) && !$is_disabled) || $this->auth->acl_get('u_titania_mod_contrib_mod') || $this->contrib->type->acl_get('moderate'),
			),
		);

		if ($this->contrib->contrib_demo)
		{
			$demo_menu = array();
			$allowed_branches = $this->contrib->type->get_allowed_branches(true);
			krsort($allowed_branches);
			$is_external = $this->contrib->contrib_status != ext::TITANIA_CONTRIB_APPROVED || !$this->contrib->options['demo'];

			foreach ($allowed_branches as $branch => $name)
			{
				$demo_url = $this->contrib->get_demo_url(
					$branch,
					!$is_external
				);

				if ($demo_url)
				{
					$demo_menu[] = array(
						'url'		=> $demo_url,
						'title'		=> $name,
						'external'	=> $is_external,
					);
				}
			}
			if (sizeof($demo_menu) == 1)
			{
				$nav_ary['demo']['url'] = $demo_menu[0]['url'];
				$nav_ary['demo']['external'] = $demo_menu[0]['external'];
			}
			else if (!empty($demo_menu))
			{
				$nav_ary['demo']['sub_menu'] = $demo_menu;
			}
			else
			{
				unset($nav_ary['demo']);
			}
		}

		$this->display->generate_nav($nav_ary, $page, 'details');
	}

	/**
	* Assign breadcrumbs to template.
	*
	* @return null
	*/
	protected function generate_breadcrumbs()
	{
		// Search for a category with the same name as the contrib type.  This is a bit ugly, but there really isn't any better option
		$categories = $this->cache->get_categories();
		$category = new \titania_category;

		foreach ($categories as $category_id => $category_row)
		{
			$category->__set_array($category_row);
			$name = $category->get_name();

			if ($name == $this->contrib->type->lang || $name == $this->contrib->type->langs)
			{
				// Generate the main breadcrumbs
				$this->display->generate_breadcrumbs(array(
					$name => $category->get_url(),
				));
			}
		}
	}

	/**
	* Set user's access level.
	*
	* @return null
	*/
	protected function set_access_level()
	{
		if ($this->access->is_public() && $this->user->data['is_registered'] && !$this->user->data['is_bot'])
		{
			if ($this->is_author)
			{
				$this->access->set_level(access::AUTHOR_LEVEL);
			}
		}
	}
}
