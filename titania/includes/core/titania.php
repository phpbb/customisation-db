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
	 * Instance of the titania_types class
	 *
	 * @var object titania_types
	 */
	public static $types;

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

		// Inherit from the boards prosilver (currently required for the Captcha)
		phpbb::$user->theme['template_inherits_id'] = 1; // Doesn't seem to matter what number I put in here...
		phpbb::$template->inherit_root = PHPBB_ROOT_PATH . 'styles/prosilver/template';

		// Access Level check for teams access
		self::$access_level = TITANIA_ACCESS_PUBLIC;
		if (in_array(phpbb::$user->data['group_id'], self::$config->team_groups))
		{
			self::$access_level = TITANIA_ACCESS_TEAMS;
		}

		// Add common titania language file
		self::add_lang('common');

		// Load the types
		self::load_types();

		// Load the users overlord
		self::load_overlord('users');
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
			echo '	The Titania configuration file could not be found or is inaccessible. Check your configuration.<br />';
			echo '	To install Titania you have to rename config.example.php to config.php and adjust it to your needs.';
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
	 * Autoload any objects, tools, or overlords.
	 * This autoload function does not handle core classes right now however it will once the naming of them is the same.
	 *
	 * @param $class_name
	 *
	 */
	public static function autoload($class_name)
	{
		$directories = array(
			'objects',
			'tools',
			'overlords'
		);

		foreach ($directories as $dir)
		{
			$class_name = preg_replace(array('#[^A-Za-z0-9]#', '#titania#', '#overlord#'), '', $class_name);

			if (file_exists(TITANIA_ROOT . 'includes/' . $dir . '/' . $class_name . '.' . PHP_EXT))
			{
				include(TITANIA_ROOT . 'includes/' . $dir . '/' . $class_name . '.' . PHP_EXT);
			}
		}

		// No error if file cant be found!
	}

	/**
	* Load a Titania Overlord Object
	*
	* @param mixed $overlord_name The name of the overlord
	*/
	public static function load_overlord($overlord_name)
	{
		return;
	}

	/**
	* Load a Titania Object
	*
	* @param mixed $object_name The name of the object
	*/
	public static function load_object($object_name)
	{
		return;
	}

	/**
	* Load a Titania Tool
	*
	* @param mixed $tool_name The name of the tool
	*/
	public static function load_tool($tool_name)
	{
		return;
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
	public static function page_header($page_title = '')
	{
		if (defined('HEADER_INC'))
		{
			return;
		}

		define('HEADER_INC', true);

		// gzip_compression
		if (phpbb::$config['gzip_compress'])
		{
			if (@extension_loaded('zlib') && !headers_sent())
			{
				ob_start('ob_gzhandler');
			}
		}

		// Check if page_title is a language string
		if (isset(phpbb::$user->lang[$page_title]))
		{
			$page_title = phpbb::$user->lang[$page_title];
		}

		// Generate logged in/logged out status
		if (phpbb::$user->data['user_id'] != ANONYMOUS)
		{
			$u_login_logout = phpbb::append_sid(self::$absolute_path . 'index.' . PHP_EXT, 'mode=logout', true, phpbb::$user->session_id);
			$l_login_logout = sprintf(phpbb::$user->lang['LOGOUT_USER'], phpbb::$user->data['username']);
		}
		else
		{
			$u_login_logout = phpbb::append_sid('ucp', 'mode=login&amp;redirect=' . self::$page);
			$l_login_logout = phpbb::$user->lang['LOGIN'];
		}

		// Send a proper content-language to the output
		$user_lang = phpbb::$user->lang['USER_LANG'];
		if (strpos($user_lang, '-x-') !== false)
		{
			$user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
		}

		phpbb::$template->assign_vars(array(
			'SITENAME'				=> phpbb::$config['sitename'],
			'SITE_DESCRIPTION'		=> phpbb::$config['site_desc'],
			'PAGE_TITLE'			=> $page_title,
			'SCRIPT_NAME'			=> str_replace('.' . PHP_EXT, '', phpbb::$user->page['page_name']),
			'CURRENT_TIME'			=> sprintf(phpbb::$user->lang['CURRENT_TIME'], phpbb::$user->format_date(time(), false, true)),

			// rewrite the login URL to redirect to the currently viewed page.
			'U_LOGIN_LOGOUT'		=> $u_login_logout,
			'L_LOGIN_LOGOUT'		=> $l_login_logout,
			'LOGIN_REDIRECT'		=> self::$page,
			'S_LOGIN_ACTION'		=> phpbb::append_sid('ucp', 'mode=login'),


			'SESSION_ID'				=> phpbb::$user->session_id,
			'PHPBB_ROOT_PATH'			=> self::$absolute_board,
			'TITANIA_ROOT_PATH'			=> self::$absolute_path,

			'U_BASE_URL'				=> self::$absolute_path,
			'U_SITE_ROOT'				=> generate_board_url(true),
			'U_MY_CONTRIBUTIONS'		=> (phpbb::$user->data['is_registered'] && !phpbb::$user->data['is_bot']) ? self::$url->build_url('author/' . phpbb::$user->data['username_clean']) : '',

			'T_TITANIA_TEMPLATE_PATH'	=> self::$template_path,
			'T_TITANIA_THEME_PATH'		=> self::$theme_path,
			'T_TITANIA_STYLESHEET'		=> self::$absolute_path . '/style.php?style=' . self::$config->style,

			'U_DELETE_COOKIES'		=> phpbb::append_sid('ucp', 'mode=delete_cookies'),
			'S_USER_LOGGED_IN'		=> (phpbb::$user->data['user_id'] != ANONYMOUS) ? true : false,
			'S_AUTOLOGIN_ENABLED'	=> (phpbb::$config['allow_autologin']) ? true : false,
			'S_BOARD_DISABLED'		=> (phpbb::$config['board_disable']) ? true : false,
			'S_REGISTERED_USER'		=> (!empty(phpbb::$user->data['is_registered'])) ? true : false,
			'S_IS_BOT'				=> (!empty(phpbb::$user->data['is_bot'])) ? true : false,
			'S_USER_LANG'			=> $user_lang,
			'S_USER_BROWSER'		=> (isset(phpbb::$user->data['session_browser'])) ? phpbb::$user->data['session_browser'] : phpbb::$user->lang['UNKNOWN_BROWSER'],
			'S_USERNAME'			=> phpbb::$user->data['username'],
			'S_CONTENT_DIRECTION'	=> phpbb::$user->lang['DIRECTION'],
			'S_CONTENT_FLOW_BEGIN'	=> (phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> (phpbb::$user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
			'S_CONTENT_ENCODING'	=> 'UTF-8',
			'S_REGISTER_ENABLED'	=> (phpbb::$config['require_activation'] != USER_ACTIVATION_DISABLE) ? true : false,

			'T_STYLESHEET_LINK'		=> (!phpbb::$user->theme['theme_storedb']) ? self::$absolute_board . '/styles/' . phpbb::$user->theme['theme_path'] . '/theme/stylesheet.css' : self::$absolute_board . 'style.' . PHP_EXT . '?sid=' . phpbb::$user->session_id . '&amp;id=' . phpbb::$user->theme['style_id'] . '&amp;lang=' . phpbb::$user->data['user_lang'],
			'T_STYLESHEET_NAME'		=> phpbb::$user->theme['theme_name'],

			'SITE_LOGO_IMG'			=> phpbb::$user->img('site_logo'),

			'A_COOKIE_SETTINGS'		=> addslashes('; path=' . phpbb::$config['cookie_path'] . ((!phpbb::$config['cookie_domain'] || phpbb::$config['cookie_domain'] == 'localhost' || phpbb::$config['cookie_domain'] == '127.0.0.1') ? '' : '; domain=' . phpbb::$config['cookie_domain']) . ((!phpbb::$config['cookie_secure']) ? '' : '; secure')),
		));

		// @todo allow outside modification of this function/call
		self::header_nav();

		// application/xhtml+xml not used because of IE
		header('Content-type: text/html; charset=UTF-8');

		header('Cache-Control: private, no-cache="set-cookie"');
		header('Expires: 0');
		header('Pragma: no-cache');

		return;
	}

	/**
	 * Header breadcrumb-type navigation
	 */
	public static function header_nav()
	{
		// @todo setup a cached array so that we can 'breadcrumb' anywhere within Titania that we go.
		$header_nav = array(
			array(
				'title'		=> 'TITANIA_INDEX',
				'url'		=> self::$absolute_path,
			),
		);

		self::generate_nav($header_nav, '', 'nav_header');
	}

	/**
	* Generate the navigation tabs/menu for display
	*
	* @param array $nav_ary The array of data to output
	* @param string $current_page The current page
	* @param string $block Optionally specify a custom template block loop name
	*/
	public static function generate_nav($nav_ary, $current_page, $block = 'nav_menu')
	{
		foreach ($nav_ary as $page => $data)
		{
			// If they do not have authorization, skip.
			if (isset($data['auth']) && !$data['auth'])
			{
				continue;
			}

			phpbb::$template->assign_block_vars($block, array(
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
			phpbb::$template->set_filenames(array(
				'body' => $template_body,
			));
		}

		// Output page creation time
		if (defined('DEBUG'))
		{
			global $starttime;
			$mtime = explode(' ', microtime());
			$totaltime = $mtime[0] + $mtime[1] - $starttime;

			if (!empty($_REQUEST['explain']) && phpbb::$auth->acl_get('a_') && defined('DEBUG_EXTRA') && method_exists(phpbb::$db, 'sql_report'))
			{
				// gotta do a rather nasty hack here, but it works and the page is killed after the display output, so no harm to anything else
				$GLOBALS['phpbb_root_path'] = self::$absolute_board;
				phpbb::$db->sql_report('display');
			}

			$debug_output = sprintf('Time : %.3fs | ' . phpbb::$db->sql_num_queries() . ' Queries | GZIP : ' . ((phpbb::$config['gzip_compress'] && @extension_loaded('zlib')) ? 'On' : 'Off') . ((phpbb::$user->load) ? ' | Load : ' . phpbb::$user->load : ''), $totaltime);

			if (phpbb::$auth->acl_get('a_') && defined('DEBUG_EXTRA'))
			{
				if (function_exists('memory_get_usage'))
				{
					if ($memory_usage = memory_get_usage())
					{
						global $base_memory_usage;
						$memory_usage -= $base_memory_usage;
						$memory_usage = get_formatted_filesize($memory_usage);

						$debug_output .= ' | Memory Usage: ' . $memory_usage;
					}
				}

				$debug_output .= ' | <a href="' . self::$url->append_url(self::$url->current_page, array_merge(self::$url->params, array('explain' => 1))) . '">Explain</a>';
			}
		}

		phpbb::$template->assign_vars(array(
			'DEBUG_OUTPUT'			=> (defined('DEBUG')) ? $debug_output : '',
			'TRANSLATION_INFO'		=> (!empty(phpbb::$user->lang['TRANSLATION_INFO'])) ? phpbb::$user->lang['TRANSLATION_INFO'] : '',

			'U_ACP'					=> (phpbb::$auth->acl_get('a_') && !empty(phpbb::$user->data['is_registered'])) ? phpbb::append_sid('adm/index', false, true, phpbb::$user->session_id) : '',
			'U_PURGE_CACHE'			=> (phpbb::$auth->acl_get('a_')) ? self::$url->append_url(self::$url->current_page, array_merge(self::$url->params, array('cache' => 'purge'))) : '',
		));

		// Call cron-type script
		if (!defined('IN_CRON') && $run_cron && !phpbb::$config['board_disable'])
		{
			$cron_type = '';

			if (self::$time - phpbb::$config['queue_interval'] > phpbb::$config['last_queue_run'] && !defined('IN_ADMIN') && file_exists(PHPBB_ROOT_PATH . 'cache/queue.' . PHP_EXT))
			{
				// Process email queue
				$cron_type = 'queue';
			}
			else if (method_exists(phpbb::$cache, 'tidy') && self::$time - phpbb::$config['cache_gc'] > phpbb::$config['cache_last_gc'])
			{
				// Tidy the cache
				$cron_type = 'tidy_cache';
			}
			else if (self::$time - phpbb::$config['warnings_gc'] > phpbb::$config['warnings_last_gc'])
			{
				$cron_type = 'tidy_warnings';
			}
			else if (self::$time - phpbb::$config['database_gc'] > phpbb::$config['database_last_gc'])
			{
				// Tidy the database
				$cron_type = 'tidy_database';
			}
			else if (self::$time - phpbb::$config['search_gc'] > phpbb::$config['search_last_gc'])
			{
				// Tidy the search
				$cron_type = 'tidy_search';
			}
			else if (self::$time - phpbb::$config['session_gc'] > phpbb::$config['session_last_gc'])
			{
				$cron_type = 'tidy_sessions';
			}

			if ($cron_type)
			{
				phpbb::$template->assign_var('RUN_CRON_TASK', '<img src="' . phpbb::append_sid('cron', 'cron_type=' . $cron_type) . '" width="1" height="1" alt="cron" />');
			}
		}

		phpbb::$template->display('body');

		garbage_collection();
		exit_handler();
	}

	/**
	 * Generate HTML of to the previous or a specified page.
	 *
	 * @param string $redirect -- redirect URL absolute or relative path.
	 * @param string $l_redirect optional -- LANG string e.g.: 'RETURN_TO_MODS'
	 *
	 * @return HTML link string
	 */
	public static function back_link($redirect, $l_redirect = '')
	{
		// set the redirect string (Return to previous page)
		$l_redirect = ($l_redirect) ? $l_redirect : 'RETURN_LAST_PAGE';

		return sprintf('<br /><br /><a href="%1$s">%2$s</a>', $redirect, phpbb::$user->lang[$l_redirect]);
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

		// Setup the error box to hide.
		phpbb::$template->assign_vars(array(
			'S_HIDE_ERROR_BOX'		=> true,
			'ERRORBOX_CLASS'		=> $block,
		));
	}

	/**
	* Build Confirm box
	* @param boolean $check True for checking if confirmed (without any additional parameters) and false for displaying the confirm box
	* @param string $title Title/Message used for confirm box.
	*		message text is _CONFIRM appended to title.
	*		If title cannot be found in user->lang a default one is displayed
	*		If title_CONFIRM cannot be found in user->lang the text given is used.
	* @param string $u_action Form action
	* @param string $post Hidden POST variables
	* @param string $html_body Template used for confirm box
	*/
	public static function confirm_box($check, $title = '', $u_action = '', $post = array(), $html_body = 'confirm_body.html')
	{
		$hidden = build_hidden_fields($post);

		if (isset($_POST['cancel']))
		{
			return false;
		}

		$confirm = false;
		if (isset($_POST['confirm']))
		{
			// language frontier
			if ($_POST['confirm'] === phpbb::$user->lang['YES'])
			{
				$confirm = true;
			}
		}

		if ($check && $confirm)
		{
			$user_id = request_var('confirm_uid', 0);
			$session_id = request_var('sess', '');
			$confirm_key = request_var('confirm_key', '');

			if ($user_id != phpbb::$user->data['user_id'] || $session_id != phpbb::$user->session_id || !$confirm_key || !phpbb::$user->data['user_last_confirm_key'] || $confirm_key != phpbb::$user->data['user_last_confirm_key'])
			{
				return false;
			}

			// Reset user_last_confirm_key
			$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = ''
				WHERE user_id = " . phpbb::$user->data['user_id'];
			phpbb::$db->sql_query($sql);

			return true;
		}
		else if ($check)
		{
			return false;
		}

		// generate activation key
		$confirm_key = gen_rand_string(10);

		$s_hidden_fields = build_hidden_fields(array(
			'confirm_uid'	=> phpbb::$user->data['user_id'],
			'confirm_key'	=> $confirm_key,
			'sess'			=> phpbb::$user->session_id,
			'sid'			=> phpbb::$user->session_id,
		));

		self::page_header((!isset(phpbb::$user->lang[$title])) ? phpbb::$user->lang['CONFIRM'] : phpbb::$user->lang[$title]);

		// If activation key already exist, we better do not re-use the key (something very strange is going on...)
		if (request_var('confirm_key', ''))
		{
			// This should not occur, therefore we cancel the operation to safe the user
			return false;
		}

		// re-add sid / transform & to &amp; for user->page (user->page is always using &)
		if ($u_action)
		{
			$u_action = titania::$url->append_url($u_action);
		}
		else
		{
			$u_action = reapply_sid(PHPBB_ROOT_PATH . str_replace('&', '&amp;', phpbb::$user->page['page']));
			$u_action .= ((strpos($u_action, '?') === false) ? '?' : '&amp;') . 'confirm_key=' . $confirm_key;
		}

		phpbb::$template->assign_vars(array(
			'MESSAGE_TITLE'		=> (!isset(phpbb::$user->lang[$title])) ? phpbb::$user->lang['CONFIRM'] : phpbb::$user->lang[$title],
			'MESSAGE_TEXT'		=> (!isset(phpbb::$user->lang[$title . '_CONFIRM'])) ? $title : phpbb::$user->lang[$title . '_CONFIRM'],

			'YES_VALUE'			=> phpbb::$user->lang['YES'],
			'S_CONFIRM_ACTION'	=> $u_action,
			'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields,
		));

		$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . phpbb::$db->sql_escape($confirm_key) . "'
			WHERE user_id = " . phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		self::page_footer(true, $html_body);
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

	/**
	 * Load types
	 */
	public static function load_types()
	{
		$dh = @opendir(TITANIA_ROOT . 'includes/types/');

		if (!$dh)
		{
			trigger_error('Could not open the types directory');
		}

		while (($fname = readdir($dh)) !== false)
		{
			if (strpos($fname, '.' . PHP_EXT) && substr($fname, 0, 1) != '_' && $fname != 'base.' . PHP_EXT)
			{
				include(TITANIA_ROOT . 'includes/types/' . $fname);

				$class_name = 'titania_type_' . substr($fname, 0, strpos($fname, '.' . PHP_EXT));

				$class = new $class_name;

				$class->auto_install();

				self::$types[$class->id] = $class;
			}
		}

		closedir($dh);

		ksort(self::$types);
	}
}
