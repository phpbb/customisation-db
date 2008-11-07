<?php
/**
*
* @package Titania
* @version $Id$
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

/**
 * titania class and functions for use within titania pages and apps.
 */
class titania
{
	/**
	 * Current viewing page location
	 *
	 * @var string
	 */
	public $page;

	/**
	 * construct class
	 */
	public function __construct()
	{
		global $user, $auth, $template;

		// Start session management
		$user->session_begin();
		$auth->acl($user->data);
		$user->setup();

		$this->page = $user->page['script_path'] . $user->page['page_name'];

		// Set the custom template path for titania. Default: root/titania/template
		$template->set_custom_template(TITANIA_ROOT . TEMPLATE_PATH, 'titania');
		$user->set_custom_lang_path(TITANIA_ROOT . 'language');

		$user->add_lang('titania_common');
	}

	/**
	 * Titania page_header
	 *
	 * @param string $page_title
	 * @param bool $display_online_list
	 */
	public function page_header($page_title = '', $display_online_list = false)
	{
		global $template, $user;

		// Call the phpBB page_header() function, but we perform our own actions here as well.
		page_header($page_title, $display_online_list);
		
		if ($user->data['user_id'] == ANONYMOUS)
		{
			$u_login_logout = $template->_rootref['U_LOGIN_LOGOUT'] . '&amp;redirect=' . $this->page;
		}
		else
		{
			$u_login_logout = append_sid(TITANIA_ROOT . 'index.' . PHP_EXT, 'mode=logout', true, $user->session_id); 
		}

		$template->assign_vars(array(
			// rewrite the login URL to redirect to the currently viewed page.
			'U_LOGIN_LOGOUT'		=> $u_login_logout,
			'LOGIN_REDIRECT'		=> $user->page['page'],
			'S_LOGIN_ACTION'		=> append_sid(PHPBB_ROOT_PATH . 'ucp.' . PHP_EXT, 'mode=login'),
			'T_TITANIA_THEME_PATH'	=> THEME_PATH,
			'T_TITANIA_STYLESHEET'	=> THEME_PATH . 'stylesheet.css',
		));
	}

	/**
	 * Titania Logout method to redirect the user to the Titania root instead of the phpBB Root
	 *
	 * @param bool $return if we are within a method, we can use the error_box instead of a trigger_error on the redirect.
	 */
	public function logout($return = false)
	{
		global $user;

		if ($user->data['user_id'] != ANONYMOUS && isset($_GET['sid']) && !is_array($_GET['sid']) && $_GET['sid'] === $user->session_id)
		{
			$user->session_kill();
			$user->session_begin();
			$message = $user->lang['LOGOUT_REDIRECT'];
		}
		else
		{
			$message = ($user->data['user_id'] == ANONYMOUS) ? $user->lang['LOGOUT_REDIRECT'] : $user->lang['LOGOUT_FAILED'];
		}
		
		if ($return)
		{
			return $message;
		}

		meta_refresh(3, append_sid(TITANIA_PATH . 'index.' . PHP_EXT));

		$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid(TITANIA_PATH . 'index.' . PHP_EXT) . '">', '</a> ');
		trigger_error($message);
	}

	/**
	 * Titania page_footer
	 *
	 * @param cron $run_cron
	 */
	public function page_footer($run_cron = true)
	{
		global $auth, $user, $template, $cache;

		// admin requested the cache to be purged, ensure they have permission and purge the cache.
		if (isset($_GET['cache']) && $_GET['cache'] == 'purge' && $auth->acl_get('a_'))
		{
			if (confirm_box(true))
			{
				$cache->purge();
				titania::error_box('SUCCESS', $user->lang['CACHE_PURGED'] . $this->back_link('', '', array('cache')));
			}
			else
			{
				$s_hidden_fields = build_hidden_fields(array(
					'cache'		=> 'purge',
				));

				confirm_box(false, 'CONFIRM_PURGE_CACHE', $s_hidden_fields);
			}
		}

		$template->assign_vars(array(
			'U_PURGE_CACHE'		=> ($auth->acl_get('a_')) ? append_sid($user->page['script_path'] . $user->page['page_name'], 'cache=purge') : '',
		));

		page_footer($run_cron);
	}

	/**
	 * Generate HTML of to the previous or a specified page.
	 *
	 * @param string $redirect optional -- redirect URL absolute or relative path.
	 * @param string $l_redirect optional -- LANG string e.g.: 'RETURN_TO_MODS'
	 * @param array $exclude variables to exclude from params, if necessary. e.g.: array('search', 'sort');
	 * @param bool $return_url Return only the URL path, returns generated HTML by if set to false (default)
	 *
	 * @return HTML link string
	 */
	public function back_link($redirect = '', $l_redirect = '', $exclude = array(), $return_url = false)
	{
		global $user, $config;

		$params = $query = array();
		$exclude = array_combine($exclude, $exclude);

		if (!$redirect)
		{
			// we must process our own redirect
			// full site URL based on config.
			$site_url = $config['server_protocol'] . $config['server_name'] . '/';

			// if HTTP_REFERER is set, and begins with the site URL, we allow it to be our redirect...
			if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && (strpos($_SERVER['HTTP_REFERER'], $site_url) === 0))
			{
				$url_scheme = parse_url($_SERVER['HTTP_REFERER']);

				$redirect = $url_scheme['path'];
				$query_ary = (isset($url_scheme['query'])) ? explode('&', $url_scheme['query']) : $query;

				foreach ($query_ary as $param)
				{
					list($key, $value) = explode('=', $param);
					$query[$key] = $value;
				}
			}
			else
			{
				$redirect = $user->page['script_path'] . $user->page['page_name'];
			}
		}
		else
		{
			$url_scheme = parse_url($redirect);
			$redirect = $url_scheme['path'];
			$query_ary = (isset($url_scheme['query'])) ? explode('&', $url_scheme['query']) : $query;

			foreach ($query_ary as $param)
			{
				list($key, $value) = explode('=', $param);
				$query[$key] = $value;
			}
		}

		if ($exclude || !$redirect)
		{
			// collect the list of $_GET params to be used in the redirect string if query string not filled.
			$query = ($query) ? $query : $_GET;

			foreach ($query as $key => $value)
			{
				if (!isset($exclude[$key]))
				{
					$params[] = $key . '=' . $value;
				}
			}

			$redirect .= ($params) ? '?' . implode('&amp;', $params) : '';
		}

		// set the redirect string (Return to previous page)
		$l_redirect = ($l_redirect) ? $l_redirect : 'RETURN_LAST_PAGE';

		return (!$return_url) ? sprintf('<br /><br /><a href="%1$s">%2$s</a>', $redirect, $user->lang[$l_redirect]) : $redirect;
	}

	/**
	 * Show the errorbox or successbox
	 *
	 * @param string $l_title message title - custom or user->lang defined
	 * @param string $l_message message string
	 * @param int $error_type ERROR_SUCCESS or ERROR_ERROR constant
	 */
	public static function error_box($l_title, $l_message, $error_type = ERROR_SUCCESS)
	{
		global $template, $user;

		$template->assign_block_vars('errorbox', array(
			'TITLE'		=> (isset($user->lang[$l_title])) ? $user->lang[$l_title] : $l_title,
			'MESSAGE'	=> $l_message,
			'S_ERROR'	=> ($error_type == ERROR_ERROR) ? true : false,
			'S_SUCCESS'	=> ($error_type == ERROR_SUCCESS) ? true : false,
		));
	}

	/**
	 * Function to list authors
	 *
	 */
	public function author_list()
	{
		global $db, $template, $config, $auth, $user;

		if (!class_exists('sort'))
		{
			include(TITANIA_ROOT . 'includes/class_sort.' . PHP_EXT);
		}

		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);
		}

		/**
		 * @todo too much hard-coding here
		 */
		$sort = new sort();

		$sort->set_sort_keys(array(
			array('SORT_AUTHOR',		'a.author_username_clean', 'default' => true),
			array('SORT_AUTHOR_RATING',	'a.author_rating'),
			array('SORT_CONTRIBS',		'a.author_contribs'),
			array('SORT_MODS',			'a.author_mods'),
			array('SORT_STYLES',		'a.author_styles'),
		));

		$sort->sort_request(false);

		$pagination = new pagination();
		$pagination->set_result_lang('AUTHOR');
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		// select the list of contribs
		$sql_ary = array(
			'SELECT'	=> 'a.*, u.user_lastvisit, u.user_posts',
			'FROM'		=> array(
				CUSTOMISATION_AUTHORS_TABLE => 'a',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'a.user_id = u.user_id'
				),
			),
			'WHERE'		=> 'a.author_visible <> ' . AUTHOR_HIDDEN,
			'ORDER_BY'	=> $sort->get_order_by(),
		);

		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		$authors = array();
		$author_id_key = array();

		while ($author = $db->sql_fetchrow($result))
		{
			$author_id_key[$author['user_id']] = $author;
			$author_id_key[$author['user_id']]['online'] = false;
			$authors[] = &$author_id_key[$author['user_id']];
		}

		$db->sql_freeresult($result);

		// Generate online information for user
		if ($config['load_onlinetrack'] && sizeof($authors))
		{
			$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
				FROM ' . SESSIONS_TABLE . '
				WHERE ' . $db->sql_in_set('session_user_id', array_keys($author_id_key)) . '
				GROUP BY session_user_id';
			$result = $db->sql_query($sql);

			$update_time = $config['load_online_time'] * 60;
			while ($row = $db->sql_fetchrow($result))
			{
				$author_id_key[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
			}
			$db->sql_freeresult($result);
		}
		foreach($authors as $author)
		{
			$template->assign_block_vars('authors', array(
				'USERNAME'			=>	$author['author_username'],
				'CONTRIBS'			=>	$author['author_contribs'],
				'MODS'				=>	$author['author_mods'],
				'STYLES'			=>	$author['author_styles'],
				'RATING'			=>	round($author['author_rating'], 2),
				'WEBSITE'			=>	$author['author_website'],
				'LAST_VISIT'		=>	$user->format_date($author['user_lastvisit'], false, true),
				'POSTS'				=>	$author['user_posts'],
				'ONLINE'			=>	$author['online'],
				'U_PHPBB_PROFILE'	=>	($author['phpbb_user_id'] > 0) ? 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=' . $author['phpbb_user_id'] : '',
			));
		}

		$pagination->sql_total_count($sql_ary, 'a.author_id');

		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(),
			'sd'	=> $sort->get_sort_dir(),
		));

		$pagination->build_pagination($this->page);


		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}
}

