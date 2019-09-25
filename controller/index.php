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

use phpbb\exception\http_exception;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\ext;
use Symfony\Component\HttpFoundation\JsonResponse;

class index
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

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

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\tracking */
	protected $tracking;

	/** @var int */
	protected $id;

	/** @var \titania_category */
	protected $category;

	/** @var array */
	protected $categories;

	/** @var string */
	protected $branch = '';

	/** @var string */
	protected $status = '';

	/** @var array */
	protected $params = array();

	const ALL_CONTRIBS = 0;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param helper $helper
	 * @param type_collection $types
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\path_helper $path_helper
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\tracking $tracking
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request $request, \phpbb\titania\display $display, \phpbb\titania\cache\service $cache, \phpbb\path_helper $path_helper, \phpbb\titania\config\config $ext_config, \phpbb\titania\tracking $tracking)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->types = $types;
		$this->request = $request;
		$this->display = $display;
		$this->cache = $cache;
		$this->path_helper = $path_helper;
		$this->ext_config = $ext_config;
		$this->tracking = $tracking;
	}

	/**
	* Display the main index page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_index($branch)
	{
		$this->set_branch($branch);

		// Approval status
		$status = $this->request->variable('status', '');
		$this->set_status($status);

		$title = $this->user->lang('CUSTOMISATION_DATABASE');
		$sort = $this->list_contributions('', self::ALL_CONTRIBS);
		$this->params = $this->get_params($sort);
		$this->display->assign_global_vars();

		if ($this->request->is_ajax())
		{
			return $this->get_ajax_response($title, $sort, $status);
		}

		$this->display->display_categories(
			self::ALL_CONTRIBS,
			'categories',
			false,
			true,
			$this->params
		);

		// Mark all contribs read
		if ($this->request->variable('mark', '') == 'contribs')
		{
			$this->tracking->track(ext::TITANIA_CONTRIB, self::ALL_CONTRIBS);
		}

		$this->template->assign_vars(array(
			'CATEGORY_ID'			=> self::ALL_CONTRIBS,

			'U_CREATE_CONTRIBUTION'	=> $this->get_create_contrib_url(),
			'U_MARK_FORUMS'			=> $this->path_helper->append_url_params($this->helper->get_current_url(), array('mark' => 'contribs')),
			'L_MARK_FORUMS_READ'	=> $this->user->lang['MARK_CONTRIBS_READ'],
			'U_ALL_CONTRIBUTIONS'	=> $this->get_index_url($this->params),
			'U_CONTRIB_FEED'		=> $this->helper->route('phpbb.titania.index.feed'),

			'S_DISPLAY_SEARCHBOX'	=> true,
			'S_SEARCHBOX_ACTION'	=> $this->helper->route('phpbb.titania.search.contributions.results'),
		));

		$this->assign_sorting($sort);
		$this->assign_branches();
		$this->assign_status();

		return $this->helper->render('index_body.html', $title);
	}

	/**
	 * ATOM feed for all contribution revisions (new releases)
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function feed()
	{
		// Generic feed information
		$this->template->assign_vars(array(
			'SELF_LINK'				=> $this->helper->route('phpbb.titania.index.feed'),
			'FEED_LINK'				=> $this->helper->route('phpbb.titania.index'),
			'FEED_TITLE'			=> $this->user->lang('FEED_CDB_ALL', $this->config['sitename']),
			'FEED_SUBTITLE'			=> $this->config['site_desc'],
			'FEED_UPDATED'			=> date(\DateTime::ATOM),
			'FEED_LANG'				=> $this->user->lang('USER_LANG'),
			'FEED_AUTHOR'			=> $this->config['sitename'],
		));

		return \contribs_overlord::build_feed($this->template, $this->helper, $this->path_helper);
	}

	/**
	* Display a category, its children, and its contributions.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_category($category1, $category2, $category3, $category4)
	{
		// Approval status
		$status = $this->request->variable('status', '');
		$this->set_status($status);

		$categories = array($category1, $category2, $category3, $category4);

		try
		{
			$this->load_category($categories);
		}
		catch (\Exception $e)
		{
			// If the category does not exist, check whether this is an old link
			// that may have been caught by the route matcher.
			$rerouter = new \phpbb\titania\controller\legacy_rerouter($this->helper);
			$url = '/' . implode('/', array_filter($categories, 'strlen'));
			return $rerouter->redirect($url);
		}
		$this->set_branch($this->get_branch($categories));

		$children = $this->get_children_ids();
		// Include the current category in the ones selected
		$children[] = $this->id;
		$sort = $this->list_contributions($children, $this->category->get_url());
		$this->params = $this->get_params($sort);

		$title = $this->category->get_name() . ' - ' . $this->user->lang['CUSTOMISATION_DATABASE'];

		$this->display->assign_global_vars();
		$this->generate_breadcrumbs();

		if ($this->request->is_ajax())
		{
			return $this->get_ajax_response($title, $sort, $status);
		}

		$this->display->display_categories(
			$this->id,
			'categories',
			false,
			true,
			$this->params
		);

		$this->template->assign_vars(array(
			'CATEGORY_ID'			=> $this->id,

			'S_DISPLAY_SEARCHBOX'	=> true,
			'S_SEARCHBOX_ACTION'	=> $this->helper->route('phpbb.titania.search.contributions.results'),
			'U_QUEUE_STATS'			=> $this->get_queue_stats_url(),
			'U_CONTRIB_FEED'		=> $this->helper->route('phpbb.titania.index.feed'),
			'U_CREATE_CONTRIBUTION'	=> $this->get_create_contrib_url(),
			'U_ALL_CONTRIBUTIONS'	=> $this->get_index_url($this->params),
		));

		$this->assign_sorting($sort);
		$this->assign_branches();
		$this->assign_status();

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

		if (preg_match('/^\d\.\d$/', $category))
		{
			$category = array_pop($categories);
		}
		$category = explode('-', $category);
		$category_id = array_pop($category);

		if (isset($this->categories[$category_id]))
		{
			return (int) $category_id;
		}
		return false;
	}

	/**
	 * Get branch.
	 *
	 * @param $categories
	 * @return string
	 */
	protected function get_branch($categories)
	{
		$categories = array_filter($categories, 'strlen');

		if (empty($categories))
		{
			return '';
		}

		$category = array_pop($categories);

		if (preg_match('/^\d\.\d$/', $category))
		{
			return $category;
		}
		return '';
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
	* @return \phpbb\titania\contribution\type\type_interface|bool False if the type couldn't be determined
	*/
	protected function get_category_type()
	{
		$children = $this->get_children_ids();
		$type_id = ($this->category !== null) ? $this->category->category_type : false;

		// If the category is the top most parent, we'll try to get the type from the first child
		if (!$type_id && !empty($children))
		{
			$type_id = $this->categories[$children[0]]['category_type'];
		}
		return ($type_id) ? $this->types->get($type_id) : false;
	}

	/**
	* List the contributions for the category and its children
	*
	* @param array $categories The id's of the categories from which to select.
	* @param string $sort_url The base url from which to sort.
	*
	* @return \phpbb\titania\sort
	*/
	protected function list_contributions($categories, $sort_url)
	{
		$mode = ($this->id) ? 'category' : 'all';
		$sort = \contribs_overlord::build_sort();
		$sort->set_defaults(24);
		$branch = (int) str_replace('.', '', $this->branch);

		// Get the featured contributions
		\contribs_overlord::featured_contribs();

		$data = \contribs_overlord::display_contribs($mode, $categories, $branch, $sort, 'contribs', $this->status);

		// Canonical URL
		$data['sort']->set_url($sort_url);

		$this->template->assign_vars([
			'U_CANONICAL' => $data['sort']->build_canonical(),
			'U_IS_TITANIA_INDEX' => (!$sort_url) ? true : false,
		]);

		return $data['sort'];
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
			'author' => urlencode($this->user->data['username_clean']),
			'page' => 'create',
		));
	}

	/**
	 * Get queue stats URL.
	 *
	 * @return string
	 */
	protected function get_queue_stats_url()
	{
		if (!$this->id)
		{
			return '';
		}
		$type = $this->get_category_type();
		$u_queue_stats = '';

		if ($type && $type->use_queue)
		{
			$u_queue_stats = $this->helper->route('phpbb.titania.queue_stats', array(
				'contrib_type' => $type->url,
			));
		}
		return $u_queue_stats;
	}

	/**
	 * Get AJAX response.
	 *
	 * @param string $title
	 * @param \phpbb\titania\sort $sort
	 * @return JsonResponse
	 */
	protected function get_ajax_response($title, $sort)
	{
		$this->template->set_filenames(array(
			'body'			=> 'common/contribution_list.html',
			'breadcrumbs'	=> 'breadcrumbs.html',
			'pagination'    => 'common/pagination.html',
		));

		return new JsonResponse(array(
			'title'			=> $title,
			'content'		=> $this->template->assign_display('body'),
			'breadcrumbs'	=> $this->template->assign_display('breadcrumbs'),
			'categories'	=> $this->get_category_urls(),
			'branches'		=> $this->get_branches(),
			'sort'			=> $this->get_sorting($sort),
			'status'		=> $this->get_status(),
			'show_status'	=> $this->valid_type_permissions(),
			'pagination'    => $this->template->assign_display('pagination'),
			'u_queue_stats'	=> $this->get_queue_stats_url(),
			'l_queue_stats'	=> $this->user->lang('QUEUE_STATS'),
		));
	}

	/**
	 * Get sorting options.
	 *
	 * @param \phpbb\titania\sort $sort
	 * @return array
	 */
	protected function get_sorting($sort)
	{
		$keys = \contribs_overlord::$sort_by;
		$options = array();

		foreach ($keys as $key => $info)
		{
			$options["{$key}_d"] = $this->get_sort_data($sort, $key, 'd', $info[0] . '_DESC');
			$options["{$key}_a"] = $this->get_sort_data($sort, $key, 'a', $info[0] . '_ASC');
		}

		return $options;
	}

	/**
	 * Assign sorting options to the template.
	 *
	 * @param \phpbb\titania\sort $sort
	 */
	protected function assign_sorting($sort)
	{
		foreach ($this->get_sorting($sort) as $vars)
		{
			$this->template->assign_block_vars('sort', $vars);

			if ($vars['ACTIVE'])
			{
				$this->template->assign_var('ACTIVE_SORT_OPTION', $vars['NAME']);
			}
		}
	}

	/**
	 * Get template data for sort options.
	 *
	 * @param \phpbb\titania\sort $sort
	 * @param string $key
	 * @param string $dir
	 * @param string $name
	 * @return array
	 */
	protected function get_sort_data($sort, $key, $dir, $name)
	{
		$params = array_merge($this->get_params($sort), array(
			'sk' 	=> $key,
			'sd'	=> $dir,
		));

		$url = $this->get_item_url($params);
		$id = $key . '_' . $dir;

		return array(
			'NAME'		=> $this->user->lang($name),
			'URL'		=> ($this->request->is_ajax()) ? str_replace('&amp;', '&', $url) : $url,
			'ACTIVE'	=> $id == $sort->sort_key . '_' . $sort->sort_dir,
			'ID'		=> $id,
		);
	}

	/**
	 * Prepare status dropdown lists to show the various options
	 */
	protected function assign_status()
	{
		foreach ($this->get_status() as $status => $vars)
		{
			$this->template->assign_block_vars('sort_status', $vars);

			if ($vars['ACTIVE'])
			{
				$this->template->assign_var('ACTIVE_STATUS', $vars['NAME']);
			}
		}

		$this->template->assign_var('SHOW_STATUS', $this->valid_type_permissions());
	}

	/**
	 * Check whether the user has permission to filter by unapproved contributions
	 * @return bool
	 * @throws \Exception
	 */
	private function valid_type_permissions()
	{
		$types_managed = $this->types->find_authed('validate');

		// If current type id is null, it's the index page
		$current_category_type = $this->get_category_type();
		$current_type_id = ($current_category_type !== false) ? $current_category_type->get_id() : null;

		// If the user manages some types, and the current type is in that list (or it's the index) show the dropdown.
		$show = (sizeof($types_managed) && ($current_type_id === null || in_array($current_type_id, $types_managed)));

		return $show;
	}

	/**
	 * Get the list of statuses, including the one which is currently set to active
	 * @return array
	 * @throws \Exception
	 */
	protected function get_status()
	{
		$params = $this->params;
		unset($params['status']);

		$is_ajax = $this->request->is_ajax();
		$url = $this->get_item_url($params);

		$status_list = array();
		$status_list[] = array(
			'NAME'		=> $this->user->lang('STATUS_ALL'),
			'URL'		=> ($is_ajax) ? str_replace('&amp;', '&', $url) : $url,
			'ACTIVE'	=> empty($this->status),
			'ID'		=> 'all',
		);

		// Set up how the URL will look
		$status_types = array(
			$this->user->lang('STATUS_APPROVED') => 'approved',
			$this->user->lang('STATUS_UNAPPROVED') => 'unapproved',
		);

		foreach ($status_types as $status_type => $status_type_url)
		{
			$params['status'] = $status_type_url;
			$url = $this->get_item_url($params);

			// Set to active if it's the one currently selected
			$status_list[] = array(
				'NAME'		=> $status_type,
				'URL'		=> ($is_ajax) ? str_replace('&amp;', '&', $url) : $url,
				'ACTIVE'	=> $this->status == $status_type_url,
				'ID'		=> $status_type_url,
			);
		}

		return $status_list;
	}

	/**
	 * Store the selected status
	 * @param $status
	 */
	protected function set_status($status)
	{
		$this->status = $status;
	}

	/**
	 * Assign branch sort options to template.
	 */
	protected function assign_branches()
	{
		foreach ($this->get_branches() as $branch => $vars)
		{
			$this->template->assign_block_vars('sort_branches', $vars);
			if ($vars['ACTIVE'])
			{
				$this->template->assign_var('ACTIVE_BRANCH', $vars['NAME']);
			}
		}
	}

	/**
	 * Get branch sort options.
	 *
	 * @return array
	 */
	protected function get_branches()
	{
		$params = $this->params;
		unset($params['branch']);
		$is_ajax = $this->request->is_ajax();

		$branches = $this->ext_config->__get('phpbb_versions');
		$url = $this->get_item_url($params);

		$_branches = array(array(
			'NAME'		=> $this->user->lang('ALL_BRANCHES'),
			'URL'		=> ($is_ajax) ? str_replace('&amp;', '&', $url) : $url,
			'ACTIVE'	=> empty($this->branch),
			'ID'		=> 0,
		));

		foreach ($branches as $branch => $info)
		{
			$branch = (string) $branch;
			$branch = $branch[0] . '.' . $branch[1];
			$params['branch'] = $branch;
			$url = $this->get_item_url($params);

			$_branches[$branch] = array(
				'NAME'		=> $info['name'],
				'URL'		=> ($is_ajax) ? str_replace('&amp;', '&', $url) : $url,
				'ACTIVE'	=> $this->branch == $branch,
				'ID'		=> $branch,
			);
		}
		return $_branches;
	}

	/**
	 * Get category URL's.
	 *
	 * @return array
	 */
	protected function get_category_urls()
	{
		$category = new \titania_category;
		$url = $this->get_index_url($this->params);
		$urls = array(
			0	=> ($this->request->is_ajax()) ? str_replace('&amp;', '&', $url) : $url,
		);

		foreach ($this->cache->get_categories() as $data)
		{
			if (!$category->category_visible)
			{
				continue;
			}
			$category->__set_array($data);
			$url = $category->get_url($this->params);
			$urls[$category->category_id] = ($this->request->is_ajax()) ? str_replace('&amp;', '&', $url) : $url;
		}
		return $urls;
	}

	/**
	 * Get category/index URL.
	 *
	 * @param array $params
	 * @return string
	 */
	protected function get_item_url(array $params)
	{
		return ($this->id) ? $this->category->get_url($params) : $this->get_index_url($params);
	}

	/**
	 * Get index URL.
	 *
	 * @param array $params
	 * @return string
	 */
	protected function get_index_url(array $params = array())
	{
		$suffix = (isset($params['branch'])) ? '.branch' : '';

		return $this->helper->route('phpbb.titania.index' . $suffix, $params);
	}

	/**
	 * Get parameters for the current page.
	 *
	 * @param \phpbb\titania\sort $sort
	 * @return array
	 */
	protected function get_params($sort)
	{
		$params = array();

		if ($sort->default_sort_key != $sort->sort_key)
		{
			$params['sk'] = $sort->sort_key;
		}
		if ($sort->default_sort_dir != $sort->sort_dir)
		{
			$params['sd'] = $sort->sort_dir;
		}
		if ($this->branch)
		{
			$params['branch'] = $this->branch;
		}
		if ($this->status)
		{
			$params['status'] = $this->status;
		}

		return $params;
	}

	/**
	 * Set branch.
	 *
	 * @param string $branch
	 */
	protected function set_branch($branch)
	{
		$_branch = (int) str_replace('.', '', $branch);

		if ($branch !== '' && !array_key_exists($_branch, $this->ext_config->phpbb_versions))
		{
			throw new http_exception(404, 'NO_PAGE_FOUND');
		}
		$this->branch = $branch;
	}
}

