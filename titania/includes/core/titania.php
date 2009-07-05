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
	public static $page;

	/**
	 * Titania configuration member
	 *
	 * @var object titania_config
	 */
	public static $config;

	/**
	 * Instance of titania_cache class
	 *
	 * @var titania_cache
	 */
	public static $cache;

	/**
	 * Request time (unix timestamp)
	 *
	 * @var int
	 */
	public static $time;

	/**
	* URL Class
	*/
	public static $url;

	/**
	* Current User's Access level
	*
	* @var int $access_level Check TITANIA_ACCESS_ constants
	*/
	public static $access_level = 2;

	/**
	 * Absolute Titania, Board, Style, Template, and Theme Path
	 *
	 * @var string
	 */
	public static $absolute_path;
	public static $absolute_board;
	public static $style_path;
	public static $template_path;
	public static $theme_path;

	/**
	* Hold our main contribution/author object for the currently loaded author/contribution
	*
	* @var object
	*/
	public static $contrib;
	public static $author;

	/**
	* Load URL class
	*/
	public static function load_url()
	{
		if (!class_exists('titania_url'))
		{
			include(TITANIA_ROOT . 'includes/core/url.' . PHP_EXT);
		}

		self::$url = new titania_url();

		self::$url->decode_url();
	}

	/*
	 * Initialise titania:
	 *	Session management, Cache, Language ...
	 *
	 * @return void
	 */
	public static function initialise()
	{
		global $starttime;

		// Start session management
		phpbb::$user->session_begin();
		phpbb::$auth->acl(phpbb::$user->data);
		phpbb::$user->setup();

		self::$page = htmlspecialchars(phpbb::$user->page['script_path'] . phpbb::$user->page['page_name']);
		self::$time = (int) $starttime;

		// Instantiate cache
		if (!class_exists('titania_cache'))
		{
			include TITANIA_ROOT . 'includes/core/cache.' . PHP_EXT;
		}
		self::$cache = new titania_cache();

		// Set the absolute path
		self::$absolute_path = generate_board_url(true) . '/' . self::$config->titania_script_path;
		self::$absolute_board = generate_board_url() . '/';

		// Set the root path for our URL class
		self::$url->root_url = self::$absolute_path;

		// Set template path and template name
		self::$style_path = self::$absolute_path . 'styles/' . self::$config->style . '/';
		self::$template_path = self::$style_path . 'template';
		self::$theme_path = self::$style_path . 'theme';

		phpbb::$template->set_custom_template(TITANIA_ROOT . 'styles/' . self::$config->style . '/' . 'template', 'titania_' . self::$config->style);
		phpbb::$user->theme['template_storedb'] = false;

		// Access Level check for teams access
		self::$access_level = TITANIA_ACCESS_PUBLIC;
		if (in_array(phpbb::$user->data['group_id'], self::$config->team_groups))
		{
			self::$access_level = TITANIA_ACCESS_TEAMS;
		}

		// Add common titania language file
		self::add_lang('common');
	}

	/**
	 * Reads a configuration file with an assoc. config array
	 *
	 * @param string $file	Path to configuration file
	 */
	public static function read_config_file($file)
	{
		if (!file_exists($file) || !is_readable($file))
		{
			echo '<p>';
			echo '	The titania configuration file could not be found or is inaccessible. Check your configuration.';
			echo '	<br />';
			echo '	To install titania you have to rename config.example.php to config.php and adjust it to your needs.';
			echo '</p>';

			exit;
		}

		require($file);

		if (!isset(self::$config))
		{
			if (!class_exists('titania_config'))
			{
				require TITANIA_ROOT . 'includes/core/config.' . PHP_EXT;
			}

			self::$config = new titania_config();
		}

		if (!is_array($config))
		{
			$config = array();
		}

		self::$config->read_array($config);
	}

	/**
	* Load a Titania Object
	*
	* @param mixed $object_name The name of the object
	*/
	public static function load_object($object_name)
	{
		if (is_array($object_name))
		{
			foreach ($object_name as $name)
			{
				self::load_object($name);
			}

			return;
		}

		$object_name = preg_replace('#[^A-Za-z0-9]#', '', $object_name);

		if (class_exists('titania_' . $object_name))
		{
			return;
		}

		if (!file_exists(TITANIA_ROOT . 'includes/objects/' . $object_name . '.' . PHP_EXT))
		{
			trigger_error('Missing Object: ' . $object_name);
		}

		include(TITANIA_ROOT . 'includes/objects/' . $object_name . '.' . PHP_EXT);
	}

	/**
	 * Add a phpBB language file
	 *
	 * @param mixed $lang_set
	 * @param bool $use_db
	 * @param bool $use_help
	 */
	public static function add_lang($lang_set, $use_db = false, $use_help = false)
	{
		$old_path = phpbb::$user->lang_path;

		phpbb::$user->set_custom_lang_path(self::$config->language_path);
		phpbb::$user->add_lang($lang_set, $use_db, $use_help);

		phpbb::$user->set_custom_lang_path($old_path);
	}

	/**
	 * Titania page_header
	 *
	 * @param string $page_title
	 * @param bool $display_online_list
	 */
	public static function page_header($page_title = '', $display_online_list = false)
	{
		// Check if page_title is a language string
		if (isset(phpbb::$user->lang[$page_title]))
		{
			$page_title = phpbb::$user->lang[$page_title];
		}

		// Call the phpBB page_header() function, but we perform our own actions here as well.
		page_header($page_title, $display_online_list);

		if (phpbb::$user->data['user_id'] == ANONYMOUS)
		{
			$u_login_logout = phpbb::$template->_rootref['U_LOGIN_LOGOUT'] . '&amp;redirect=' . self::$page;
		}
		else
		{
			$u_login_logout = append_sid(self::$absolute_path . 'index.' . PHP_EXT, 'mode=logout', true, phpbb::$user->session_id);
		}

		phpbb::$template->assign_vars(array(
			// rewrite the login URL to redirect to the currently viewed page.
			'U_LOGIN_LOGOUT'			=> $u_login_logout,
			'LOGIN_REDIRECT'			=> self::$page,

			'T_TITANIA_TEMPLATE_PATH'	=> self::$template_path,
			'T_TITANIA_THEME_PATH'		=> self::$theme_path,
			'T_TITANIA_STYLESHEET'		=> self::$absolute_path . '/style.php?style=' . self::$config->style,
		));
	}

	/**
	* Generate the navigation tabs/menu for display
	*
	* @param array $nav_ary The array of data to output
	* @param string $current_page The current page
	*/
	public static function generate_nav($nav_ary, $current_page)
	{
		foreach ($nav_ary as $page => $data)
		{
			// If they do not have authorization, skip.
			if (isset($data['auth']) && !$data['auth'])
			{
				continue;
			}

			phpbb::$template->assign_block_vars('nav_menu', array(
				'L_TITLE'		=> (isset(phpbb::$user->lang[$data['title']])) ? phpbb::$user->lang[$data['title']] : $data['title'],

				'U_TITLE'		=> $data['url'],

				'S_SELECTED'	=> ($page == $current_page) ? true : false,
			));
		}
	}

	/**
	 * Titania page_footer
	 *
	 * @param cron $run_cron
	 * @param bool|string $template_body For those lazy like me, send the template body name you want to load (or leave default to ignore and assign it yourself)
	 */
	public static function page_footer($run_cron = true, $template_body = false)
	{
		// Because I am lazy most of the time...
		if ($template_body !== false)
		{
			global $template;
			$template->set_filenames(array(
				'body' => $template_body,
			));
		}

		// admin requested the cache to be purged, ensure they have permission and purge the cache.
		if (isset($_GET['cache']) && $_GET['cache'] == 'purge' && phpbb::$auth->acl_get('a_'))
		{
			if (confirm_box(true))
			{
				titania::$cache->purge();

				self::error_box('SUCCESS', phpbb::$user->lang['CACHE_PURGED'] . self::back_link('', '', array('cache')));
			}
			else
			{
				$s_hidden_fields = build_hidden_fields(array(
					'cache'		=> 'purge',
				));

				confirm_box(false, phpbb::$user->lang['CONFIRM_PURGE_CACHE'], $s_hidden_fields);
			}
		}

		phpbb::$template->assign_vars(array(
			'U_PURGE_CACHE'		=> (phpbb::$auth->acl_get('a_')) ? append_sid(self::$page, array_merge($_GET, array('cache' => 'purge'))) : '',
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
	public static function back_link($redirect = '', $l_redirect = '', $exclude = array(), $return_url = false)
	{
		$params = $query = array();
		$exclude = array_combine($exclude, $exclude);

		if (!$redirect)
		{
			// we must process our own redirect
			// full site URL based on config.
			$site_url = phpbb::$config['server_protocol'] . phpbb::$config['server_name'] . '/';

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
				$redirect = self::$page;
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

		return (!$return_url) ? sprintf('<br /><br /><a href="%1$s">%2$s</a>', $redirect, phpbb::$user->lang[$l_redirect]) : $redirect;
	}

	/**
	 * Titania Logout method to redirect the user to the Titania root instead of the phpBB Root
	 *
	 * @param bool $return if we are within a method, we can use the error_box instead of a trigger_error on the redirect.
	 */
	public static function logout($return = false)
	{
		if (phpbb::$user->data['user_id'] != ANONYMOUS && isset($_GET['sid']) && !is_array($_GET['sid']) && $_GET['sid'] === phpbb::$user->session_id)
		{
			phpbb::$user->session_kill();
			phpbb::$user->session_begin();
			$message = phpbb::$user->lang['LOGOUT_REDIRECT'];
		}
		else
		{
			$message = (phpbb::$user->data['user_id'] == ANONYMOUS) ? phpbb::$user->lang['LOGOUT_REDIRECT'] : phpbb::$user->lang['LOGOUT_FAILED'];
		}

		if ($return)
		{
			return $message;
		}

		meta_refresh(3, self::$url->build_url());

		$message = $message . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . self::$url->build_url() . '">', '</a> ');
		trigger_error($message);
	}

	/**
	 * Wrapper for phpBB function trigger_error (with added http response code)
	 *
	 * @param string	$error_msg		error message or language string
	 * @param int		$error_type		error type e.g. E_USER_NOTICE
	 * @param int		$status_code	http response code
	 * @param string	$title			Optional message header title
	 *
	 * @return void
	 */
	public static function trigger_error($error_msg, $error_type = E_USER_NOTICE, $status_code = NULL, $title = '')
	{
		global $msg_title, $msg_long_text, $msg_template;

		$msg_template = &phpbb::$template;

		if ($status_code)
		{
			self::set_header_status($status_code);
		}

		if ($title)
		{
			$msg_title = isset(phpbb::$user->lang[$title]) ? phpbb::$user->lang[$title] : $title;
		}

		trigger_error($error_msg, $error_type);
	}

	/**
	 * Show the errorbox or successbox
	 *
	 * @param string $l_title message title - custom or user->lang defined
	 * @param mixed $l_message message string or array of strings
	 * @param int $error_type TITANIA_SUCCESS or TITANIA_ERROR constant
	 * @param int $status_code an HTTP status code
	 */
	public static function error_box($l_title, $l_message, $error_type = TITANIA_SUCCESS, $status_code = NULL)
	{
		if ($status_code)
		{
			self::set_header_status($status_code);
		}

		$block = ($error_type == TITANIA_ERROR) ? 'errorbox' : 'successbox';

		if ($l_title)
		{
			$title = (isset(phpbb::$user->lang[$l_title])) ? phpbb::$user->lang[$l_title] : $l_title;

			phpbb::$template->assign_var(strtoupper($block) . '_TITLE', $title);
		}

		if (!is_array($l_message))
		{
			$l_message = array($l_message);
		}

		foreach ($l_message as $message)
		{
			if (!$message)
			{
				continue;
			}

			phpbb::$template->assign_block_vars($block, array(
				'MESSAGE'	=> (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message,
			));
		}
	}

	/**
	 * Set proper page header status
	 *
	 * @param int $status_code
	 */
	public static function set_header_status($status_code = NULL)
	{
		// Send the appropriate HTTP status header
		static $status = array(
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			204 => 'No Content',
			205 => 'Reset Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found', // Moved Temporarily
			303 => 'See Other',
			304 => 'Not Modified',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			406 => 'Not Acceptable',
			409 => 'Conflict',
			410 => 'Gone',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
		);

		if ($status_code && isset($status[$status_code]))
		{
			$header = $status_code . ' ' . $status[$status_code];
			header('HTTP/1.1 ' . $header, false, $status_code);
			header('Status: ' . $header, false, $status_code);
		}
	}
}
