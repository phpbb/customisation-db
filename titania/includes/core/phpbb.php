<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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
 * phpBB class that will be used in place of globalising these variables.
 */
class phpbb
{
	public static $auth;
	public static $cache;
	public static $config;
	public static $db;
	public static $template;
	public static $user;

	/**
	 * Static Constructor.
	 */
	public static function initialise()
	{
		global $auth, $config, $db, $template, $user, $cache;

		self::$auth		= &$auth;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;
		self::$cache	= &$cache;

		// Start session management
		self::$user->session_begin();
		self::$auth->acl(self::$user->data);
		self::$user->setup();
	}

	/**
	* Shortcut for phpbb's append_sid function (do not send the root path/phpext in the url part)
	*
	* @param mixed $url
	* @param mixed $params
	* @param mixed $is_amp
	* @param mixed $session_id
	* @return string
	*/
	public static function append_sid($url, $params = false, $is_amp = true, $session_id = false)
	{
		if (!strpos($url, '.' . PHP_EXT))
		{
			$url = titania::$absolute_board . $url . '.' . PHP_EXT;
		}

		return append_sid($url, $params, $is_amp, $session_id);
	}

	/**
	* Include a phpBB includes file
	*
	* @param string $file The name of the file
	* @param string|bool $function_check Bool false to ignore; string function name to check if the function exists (and not load the file if it does)
	* @param string|bool $class_check Bool false to ignore; string class name to check if the class exists (and not load the file if it does)
	*/
	public static function _include($file, $function_check = false, $class_check = false)
	{
		if ($function_check !== false)
		{
			if (function_exists($function_check))
			{
				return;
			}
		}

		if ($class_check !== false)
		{
			if (class_exists($class_check))
			{
				return;
			}
		}

		include(PHPBB_ROOT_PATH . 'includes/' . $file . '.' . PHP_EXT);
	}

	/**
	 * Page header function for phpBB stuff
	 *
	 * @param <string> $page_title
	 */
	public static function page_header($page_title = '')
	{
		// gzip_compression
		if (self::$config['gzip_compress'])
		{
			if (@extension_loaded('zlib') && !headers_sent())
			{
				ob_start('ob_gzhandler');
			}
		}

		// Send a proper content-language to the output
		$user_lang = self::$user->lang['USER_LANG'];
		if (strpos($user_lang, '-x-') !== false)
		{
			$user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
		}

		// Check if page_title is a language string
		if (isset(self::$user->lang[$page_title]))
		{
			$page_title = self::$user->lang[$page_title];
		}

		// Generate logged in/logged out status
		$l_login_redirect = titania_url::build_url(titania_url::$current_page, titania_url::$params);
		if (self::$user->data['user_id'] != ANONYMOUS)
		{
			$u_login_logout = self::append_sid('ucp', 'mode=logout', true, self::$user->session_id);
			$l_login_logout = sprintf(self::$user->lang['LOGOUT_USER'], self::$user->data['username']);
		}
		else
		{
			$u_login_logout = self::append_sid('ucp', 'mode=login&amp;redirect=' . $l_login_redirect);
			$l_login_logout = self::$user->lang['LOGIN'];
		}

		self::$template->assign_vars(array(
			'SITENAME'				=> self::$config['sitename'],
			'SITE_DESCRIPTION'		=> self::$config['site_desc'],
			'PAGE_TITLE'			=> $page_title,
			'SCRIPT_NAME'			=> str_replace('.' . PHP_EXT, '', self::$user->page['page_name']),
			'CURRENT_TIME'			=> sprintf(self::$user->lang['CURRENT_TIME'], self::$user->format_date(time(), false, true)),
			'SITE_LOGO_IMG'			=> self::$user->img('site_logo'),

			'U_REGISTER'			=> self::append_sid('ucp', 'mode=register'),
			'S_LOGIN_ACTION'		=> self::append_sid('ucp', 'mode=login'),
			'U_LOGIN_LOGOUT'		=> $u_login_logout,
			'L_LOGIN_LOGOUT'		=> $l_login_logout,
			'LOGIN_REDIRECT'		=> $l_login_redirect,

			'SESSION_ID'			=> self::$user->session_id,

			'U_DELETE_COOKIES'		=> self::append_sid('ucp', 'mode=delete_cookies'),
			'S_USER_LOGGED_IN'		=> (self::$user->data['user_id'] != ANONYMOUS) ? true : false,
			'S_AUTOLOGIN_ENABLED'	=> (self::$config['allow_autologin']) ? true : false,
			'S_BOARD_DISABLED'		=> (self::$config['board_disable']) ? true : false,
			'S_REGISTERED_USER'		=> (!empty(self::$user->data['is_registered'])) ? true : false,
			'S_IS_BOT'				=> (!empty(self::$user->data['is_bot'])) ? true : false,
			'S_USER_LANG'			=> $user_lang,
			'S_USER_BROWSER'		=> (isset(self::$user->data['session_browser'])) ? self::$user->data['session_browser'] : self::$user->lang['UNKNOWN_BROWSER'],
			'S_USERNAME'			=> self::$user->data['username'],
			'S_CONTENT_DIRECTION'	=> self::$user->lang['DIRECTION'],
			'S_CONTENT_FLOW_BEGIN'	=> (self::$user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'	=> (self::$user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
			'S_CONTENT_ENCODING'	=> 'UTF-8',
			'S_REGISTER_ENABLED'	=> (self::$config['require_activation'] != USER_ACTIVATION_DISABLE) ? true : false,
		));

		// application/xhtml+xml not used because of IE
		header('Content-type: text/html; charset=UTF-8');

		header('Cache-Control: private, no-cache="set-cookie"');
		header('Expires: 0');
		header('Pragma: no-cache');
	}

	/**
	 * Page footer function handling the phpBB tasks
	 */
	public static function page_footer($run_cron = true)
	{
		// Call cron-type script
		$cron_type = '';
		$time = time();
		if (!defined('IN_CRON') && $run_cron && !self::$config['board_disable'])
		{
			if ($time - self::$config['queue_interval'] > self::$config['last_queue_run'] && !defined('IN_ADMIN') && file_exists(PHPBB_ROOT_PATH . 'cache/queue.' . PHP_EXT))
			{
				// Process email queue
				$cron_type = 'queue';
			}
			else if (method_exists(self::$cache, 'tidy') && $time - self::$config['cache_gc'] > self::$config['cache_last_gc'])
			{
				// Tidy the cache
				$cron_type = 'tidy_cache';
			}
			else if ($time - self::$config['warnings_gc'] > self::$config['warnings_last_gc'])
			{
				$cron_type = 'tidy_warnings';
			}
			else if ($time - self::$config['database_gc'] > self::$config['database_last_gc'])
			{
				// Tidy the database
				$cron_type = 'tidy_database';
			}
			else if ($time - self::$config['search_gc'] > self::$config['search_last_gc'])
			{
				// Tidy the search
				$cron_type = 'tidy_search';
			}
			else if ($time - self::$config['session_gc'] > self::$config['session_last_gc'])
			{
				$cron_type = 'tidy_sessions';
			}
		}

		self::$template->assign_vars(array(
			'RUN_CRON_TASK'			=> ($cron_type) ? '<img src="' . self::append_sid('cron', 'cron_type=' . $cron_type) . '" width="1" height="1" alt="cron" />' : '',

			'TRANSLATION_INFO'		=> (!empty(self::$user->lang['TRANSLATION_INFO'])) ? self::$user->lang['TRANSLATION_INFO'] : '',

			'U_ACP'					=> (self::$auth->acl_get('a_') && !empty(self::$user->data['is_registered'])) ? self::append_sid('adm/index', false, true, self::$user->session_id) : '',
		));

		self::$template->display('body');

		garbage_collection();
		exit_handler();
	}
}
