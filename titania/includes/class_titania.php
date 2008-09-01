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
		global $template;

		// Call the phpBB page_header() function, but we perform our own actions here as well.
		page_header($page_title, $display_online_list);

		$template->assign_vars(array(
			// rewrite the login URL to redirect to the currently viewed page.
			'U_LOGIN_LOGOUT'		=> $template->_rootref['U_LOGIN_LOGOUT'] . '&amp;redirect=' . $this->page,
			'T_TITANIA_THEME_PATH'	=> THEME_PATH,
			'T_TITANIA_STYLESHEET'	=> THEME_PATH . 'stylesheet.css',
		));
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
	 * Function to list contribs for the selected type.
	 *
	 * @todo Hard-coding many actions, will then need to seperate these into their own functions/classes to be dynamically generated and scaleable
	 *
	 * @param string $contrib_type
	 */
	public function contrib_list($contrib_type)
	{
		global $db, $template;

		// set an upper and lowercase contrib_type as well need each in multiple occurences.
		$l_contrib_type = strtolower($contrib_type);
		$u_contrib_type = strtoupper($contrib_type);

		if (!defined('CONTRIB_TYPE_' . $u_contrib_type))
		{
			trigger_error('NO_CONTRIB_TYPE');
		}

//		$submit = isset($_REQUEST['submit']) ? true : false;

		if (!class_exists('sort'))
		{
			include(TITANIA_ROOT . 'includes/class_sort.' . PHP_EXT);
		}

		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);
		}

		$sort = new sort();
		$sort->set_sort_keys(array(
			'a'	=> array('SORT_AUTHOR',			'a.author_username_clean', 'default' => true),
			'b'	=> array('SORT_TIME_ADDED',		'c.contrib_release_date'),
			'c'	=> array('SORT_TIME_UPDATED',	'c.contrib_update_date'),
			'd'	=> array('SORT_DOWNLOADS',		'c.contrib_downloads'),
			'e'	=> array('SORT_RATING',			'c.contrib_rating'),
			'f'	=> array('SORT_CONTRIB_NAME',	'c.contrib_name'),
		));

		$sort->sort_request(false);

		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		// select the list of contribs
		$sql_ary = array(
			'SELECT'	=> 'a.author_id, a.author_username, c.*',
			'FROM'		=> array(
				CUSTOMISATION_CONTRIBS_TABLE => 'c',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_AUTHORS_TABLE => 'a'),
					'ON'	=> 'c.contrib_author_id = a.author_id'
				),
			),
			'WHERE'		=> 'contrib_status = ' . STATUS_APPROVED . '
						AND contrib_type = ' . constant('CONTRIB_TYPE_' . $u_contrib_type),
			'ORDER_BY'	=> $sort->get_order_by(),
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars($l_contrib_type, array(
				$u_contrib_type . '_ID'		=> $row['contrib_id'],
			));
		}
		$db->sql_freeresult($result);

		$pagination->sql_total_count($sql_ary, 'c.contrib_id');

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
}

