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

namespace phpbb\titania\controller;

class index
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var int */
	protected $id;

	/** @var \titania_category */
	protected $category;

	/** @var array */
	protected $categories;

	const ALL_CONTRIBS = 0;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\request\request_interace $request;
	* @param \phpbb\titania\display $display
	* @param \phpbb\path_helper $path_helper
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\display $display, \phpbb\titania\cache\service $cache, \phpbb\path_helper $path_helper)
	{
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->display = $display;
		$this->cache = $cache;
		$this->path_helper = $path_helper;

		\titania::_include('functions_display', 'titania_display_categories');
	}

	/**
	* Display the main index page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_index()
	{
		titania_display_categories(self::ALL_CONTRIBS);

		// Mark all contribs read
		if ($this->request->variable('mark', '') == 'contribs')
		{
			\titania_tracking::track(TITANIA_CONTRIB, self::ALL_CONTRIBS);
		}

		$this->template->assign_vars(array(
			'CATEGORY_ID'			=> self::ALL_CONTRIBS,

			'U_CREATE_CONTRIBUTION'	=> $this->get_create_contrib_url(),
			'U_MARK_FORUMS'			=> $this->path_helper->append_url_params($this->helper->get_current_url(), array('mark' => 'contribs')),
			'L_MARK_FORUMS_READ'	=> $this->user->lang['MARK_CONTRIBS_READ'],

			'S_DISPLAY_SEARCHBOX'	=> true,
			'S_SEARCHBOX_ACTION'	=> $this->helper->route('phpbb.titania.search.contributions.results'),
		));

		$this->display->assign_global_vars();
		$this->list_contributions('', self::ALL_CONTRIBS);

		return $this->helper->render('index_body.html', $this->user->lang['CUSTOMISATION_DATABASE']);
	}

	/**
	* Display a category, its children, and its contributions.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_category($category1, $category2, $category3)
	{
		$categories = array($category1, $category2, $category3);

		try
		{
			$this->load_category(array($category1, $category2, $category3));
		}
		catch (\Exception $e)
		{
			// If the category does not exist, check whether this is an old link
			// that may have been caught by the route matcher.
			$rerouter = new \phpbb\titania\controller\legacy_rerouter($this->helper);
			$url = '/' . implode('/', array_filter($categories, 'strlen'));
			return $rerouter->redirect($url);
		}
		titania_display_categories($this->id);

		$this->display->assign_global_vars();
		$this->generate_breadcrumbs();

		$type = $this->get_category_type();
		$u_queue_stats = '';

		if ($type && $type->use_queue)
		{
			$u_queue_stats = $this->helper->route('phpbb.titania.queue_stats', array('contrib_type' => $type->url));
		}

		$this->template->assign_vars(array(
			'CATEGORY_ID'			=> $this->id,

			'S_DISPLAY_SEARCHBOX'	=> true,
			'S_SEARCHBOX_ACTION'	=> $this->helper->route('phpbb.titania.search.contributions.results'),
			'U_QUEUE_STATS'			=> $u_queue_stats,
			'U_CREATE_CONTRIBUTION'	=> $this->get_create_contrib_url(),
		));

		$children = $this->get_children_ids();
		// Include the current category in the ones selected
		$children[] = $this->id;
		$this->list_contributions($children, $this->category->get_url());

		$title = $this->category->get_name() . ' - ' . $this->user->lang['CUSTOMISATION_DATABASE'];

		return $this->helper->render('index_body.html', $title);
	}

	/**
	* Get the id of the current category from the URL parts.
	*
	* @param array Array of category names to search in.
	*				ID is expected in string in the form of modifications-4
	* @return int|false Returns the category id or false if not found.
	*/
	protected function get_category_id($categories)
	{
		$categories = array_filter($categories, 'strlen');

		if (empty($categories))
		{
			return self::ALL_CONTRIBS;
		}

		$category = array_pop($categories);
		$category = explode('-', $category);
		$category_id = array_pop($category);

		if (isset($this->categories[$category_id]))
		{
			return (int) $category_id;
		}
		return false;
	}

	/**
	* Load the active category.
	*
	* @param array Array of category names to search in.
	*				ID is expected in string in the form of modifications-4
	* @throws \Exception Throws exception if no valid category is found.
	* @return null
	*/
	protected function load_category($categories)
	{
		$this->categories = $this->cache->get_categories();
		$this->id = $this->get_category_id($categories);

		if ($this->id === false)
		{
			throw new \Exception($this->user->lang['NO_PAGE_FOUND']);
		}

		$this->category = new \titania_category;
		$this->category->__set_array($this->categories[$this->id]);
	}

	/**
	* Assign breadcrumbs to the template.
	*
	* @return null
	*/
	protected function generate_breadcrumbs()
	{
		$category = new \titania_category;

		// Parents
		foreach (array_reverse($this->cache->get_category_parents($this->id)) as $row)
		{
			$category->__set_array($this->categories[$row['category_id']]);
			$this->display->generate_breadcrumbs(array(
				$category->get_name() => $category->get_url(),
			));
		}

		// Self
		$this->display->generate_breadcrumbs(array(
			$this->category->get_name() => $this->category->get_url(),
		));
	}

	/**
	* Get the id's of the current category's children.
	*
	* @return array
	*/
	protected function get_children_ids()
	{
		return array_keys($this->cache->get_category_children($this->id));
	}

	/**
	* Get the contribution type of the category.
	*
	* @return \titania_type or false if the type couldn't be determined
	*/
	protected function get_category_type()
	{
		$children = $this->get_children_ids();
		$type_id = $this->category->category_type;

		// If the category is the top most parent, we'll try to get the type from the first child
		if (!$type_id && !empty($children))
		{
			$type_id = $this->categories[$children[0]]['category_type'];
		}
		return ($type_id) ? \titania_types::$types[$type_id] : false;
	}

	/**
	* List the contributions for the category and its children
	*
	* @param array $children The id's of the categories from which to select.
	* @param string $sort_url The base url from which to sort.
	*
	* @return null
	*/
	protected function list_contributions($categories, $sort_url)
	{
		$mode = ($this->id) ? 'category' : 'all';
		$sort = \contribs_overlord::build_sort();
		$sort->set_defaults(25);

		$data = \contribs_overlord::display_contribs($mode, $categories, $sort);

		// Canonical URL
		$data['sort']->set_url($sort_url);
		$this->template->assign_var('U_CANONICAL', $data['sort']->build_canonical());
	}

	/**
	* Get the URL for the user's create contribution page.
	*
	* @return string Returns empty string if user cannot submit contributions.
	*/
	protected function get_create_contrib_url()
	{
		if (!$this->auth->acl_get('u_titania_contrib_submit'))
		{
			return '';
		}
		return $this->helper->route('phpbb.titania.author', array(
				'author' => $this->user->data['username_clean'],
				'page' => 'create',
		));
	}
}

