<?php
/**
*
* @package Titania
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
 * phpBB class that will be used in place of globalising these variables.
 */
class phpbb
{
	/** @var auth phpBB Auth class */
	public static $auth;

	/** @var cache phpBB Cache class */
	public static $cache;
	
	/** @var config phpBB Config class */
	public static $config;
	
	/** @var db phpBB DBAL class */
	public static $db;
	
	/** @var template phpBB Template class */
	public static $template;
	
	/** @var user phpBB User class */
	public static $user;
	
	/** @var array $user->theme */
	public static $theme_data;

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
		if (!defined('PHPBB_INCLUDED'))
		{
			self::$user->session_begin();
			self::$auth->acl(self::$user->data);
			self::$user->setup();
		}

		self::$theme_data = self::$user->theme;
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
	* Reset the template/theme data to the phpBB information
	*/
	public static function reset_template()
	{
		self::$user->theme = self::$theme_data;
		self::$template->set_template();
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
		if (self::$user->data['user_id'] != ANONYMOUS)
		{
			$u_login_logout = titania_url::build_url('logout');
			$l_login_logout = sprintf(self::$user->lang['LOGOUT_USER'], self::$user->data['username']);
		}
		else
		{
			$u_login_logout = titania_url::build_url('login');
			$l_login_logout = self::$user->lang['LOGIN'];
		}

		$l_privmsgs_text = $l_privmsgs_text_unread = '';
		$s_privmsg_new = false;

		// Obtain number of new private messages if user is logged in
		if (!empty(self::$user->data['is_registered']))
		{
			if (self::$user->data['user_new_privmsg'])
			{
				$l_message_new = (self::$user->data['user_new_privmsg'] == 1) ? self::$user->lang['NEW_PM'] : self::$user->lang['NEW_PMS'];
				$l_privmsgs_text = sprintf($l_message_new, self::$user->data['user_new_privmsg']);

				if (!self::$user->data['user_last_privmsg'] || self::$user->data['user_last_privmsg'] > self::$user->data['session_last_visit'])
				{
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_last_privmsg = ' . self::$user->data['session_last_visit'] . '
						WHERE user_id = ' . self::$user->data['user_id'];
					self::$db->sql_query($sql);

					$s_privmsg_new = true;
				}
				else
				{
					$s_privmsg_new = false;
				}
			}
			else
			{
				$l_privmsgs_text = self::$user->lang['NO_NEW_PM'];
				$s_privmsg_new = false;
			}

			$l_privmsgs_text_unread = '';

			if (self::$user->data['user_unread_privmsg'] && self::$user->data['user_unread_privmsg'] != self::$user->data['user_new_privmsg'])
			{
				$l_message_unread = (self::$user->data['user_unread_privmsg'] == 1) ? self::$user->lang['UNREAD_PM'] : self::$user->lang['UNREAD_PMS'];
				$l_privmsgs_text_unread = sprintf($l_message_unread, self::$user->data['user_unread_privmsg']);
			}
		}

		self::$template->assign_vars(array(
			'SITENAME'				=> self::$config['sitename'],
			'SITE_DESCRIPTION'		=> self::$config['site_desc'],
			'PAGE_TITLE'			=> $page_title,
			'SCRIPT_NAME'			=> str_replace('.' . PHP_EXT, '', self::$user->page['page_name']),
			'CURRENT_TIME'			=> sprintf(self::$user->lang['CURRENT_TIME'], self::$user->format_date(time(), false, true)),
			'LAST_VISIT_DATE'		=> sprintf(self::$user->lang['YOU_LAST_VISIT'], ((self::$user->data['user_id'] != ANONYMOUS) ? self::$user->format_date(self::$user->data['session_last_visit']) : '')),
			'SITE_LOGO_IMG'			=> self::$user->img('site_logo'),
			'PRIVATE_MESSAGE_INFO'			=> $l_privmsgs_text,
			'PRIVATE_MESSAGE_INFO_UNREAD'	=> $l_privmsgs_text_unread,

			'U_REGISTER'			=> self::append_sid('ucp', 'mode=register'),
			'S_LOGIN_ACTION'		=> titania_url::$current_page_url,
			'U_LOGIN_LOGOUT'		=> $u_login_logout,
			'L_LOGIN_LOGOUT'		=> $l_login_logout,
			'LOGIN_REDIRECT'		=> titania_url::$current_page_url,

			'SESSION_ID'			=> self::$user->session_id,

			'U_PRIVATEMSGS'			=> self::append_sid('ucp', 'i=pm&amp;folder=inbox'),
			'UA_POPUP_PM'			=> addslashes(self::append_sid('ucp', 'i=pm&amp;mode=popup')),
			'U_PROFILE'				=> self::append_sid('ucp'),
			'U_RESTORE_PERMISSIONS'	=> (self::$user->data['user_perm_from'] && self::$auth->acl_get('a_switchperm')) ? self::append_sid('ucp', 'mode=restore_perm') : '',
			'U_DELETE_COOKIES'		=> self::append_sid('ucp', 'mode=delete_cookies'),

			'S_DISPLAY_PM'			=> (self::$config['allow_privmsg'] && !empty(self::$user->data['is_registered']) && (self::$auth->acl_get('u_readpm') || self::$auth->acl_get('u_sendpm'))) ? true : false,
			'S_USER_PM_POPUP'		=> self::$user->optionget('popuppm'),
			'S_NEW_PM'				=> ($s_privmsg_new) ? 1 : 0,
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
		self::$template->assign_vars(array(
			'RUN_CRON_TASK'			=> (!defined('IN_CRON') && $run_cron && !self::$config['board_disable']) ? '<img src="' . titania_url::build_url('cron') . '" width="1" height="1" alt="cron" />' : '',

			'TRANSLATION_INFO'		=> (!empty(self::$user->lang['TRANSLATION_INFO'])) ? self::$user->lang['TRANSLATION_INFO'] : '',

			'U_ACP'					=> (self::$auth->acl_get('a_') && !empty(self::$user->data['is_registered'])) ? self::append_sid('adm/index', false, true, self::$user->session_id) : '',
		));

		self::$template->display('body');

		garbage_collection();
		exit_handler();
	}


	/**
	* Generate login box or verify password
	*/
	function login_box($redirect = '', $l_explain = '', $l_success = '', $admin = false, $s_display = true)
	{
		self::_include('captcha/captcha_factory', 'phpbb_captcha_factory');
		self::$user->add_lang('ucp');

		$err = '';

		// Make sure user->setup() has been called
		if (empty(self::$user->lang))
		{
			self::$user->setup();
		}

		// Print out error if user tries to authenticate as an administrator without having the privileges...
		if ($admin && !self::$auth->acl_get('a_'))
		{
			// Not authd
			// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
			if (self::$user->data['is_registered'])
			{
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			}
			trigger_error('NO_AUTH_ADMIN');
		}

		if (isset($_POST['login']))
		{
			// Get credential
			if ($admin)
			{
				$credential = request_var('credential', '');

				if (strspn($credential, 'abcdef0123456789') !== strlen($credential) || strlen($credential) != 32)
				{
					if (self::$user->data['is_registered'])
					{
						add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
					}
					trigger_error('NO_AUTH_ADMIN');
				}

				$password	= request_var('password_' . $credential, '', true);
			}
			else
			{
				$password	= request_var('password', '', true);
			}

			$username	= request_var('username', '', true);
			$autologin	= (!empty($_POST['autologin'])) ? true : false;
			$viewonline = (!empty($_POST['viewonline'])) ? 0 : 1;
			$admin 		= ($admin) ? 1 : 0;
			$viewonline = ($admin) ? self::$user->data['session_viewonline'] : $viewonline;

			// Check if the supplied username is equal to the one stored within the database if re-authenticating
			if ($admin && utf8_clean_string(self::$username) != utf8_clean_string(self::$user->data['username']))
			{
				// We log the attempt to use a different username...
				add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				trigger_error('NO_AUTH_ADMIN_USER_DIFFER');
			}

			// If authentication is successful we redirect user to previous page
			$result = self::$auth->login($username, $password, $autologin, $viewonline, $admin);

			// If admin authentication and login, we will log if it was a success or not...
			// We also break the operation on the first non-success login - it could be argued that the user already knows
			if ($admin)
			{
				if ($result['status'] == LOGIN_SUCCESS)
				{
					add_log('admin', 'LOG_ADMIN_AUTH_SUCCESS');
				}
				else
				{
					// Only log the failed attempt if a real user tried to.
					// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
					if (self::$user->data['is_registered'])
					{
						add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
					}
				}
			}

			// The result parameter is always an array, holding the relevant information...
			if ($result['status'] == LOGIN_SUCCESS)
			{
				$redirect = request_var('redirect', '');

				if ($redirect)
				{
					$redirect = titania_url::unbuild_url($redirect);

					$base = $append = false;
					titania_url::split_base_params($base, $append, $redirect);

					redirect(titania_url::build_url($base, $append));
				}
				else
				{
					redirect(titania_url::build_url(titania_url::$current_page, titania_url::$params));
				}
			}

			// Something failed, determine what...
			if ($result['status'] == LOGIN_BREAK)
			{
				trigger_error($result['error_msg']);
			}

			// Special cases... determine
			switch ($result['status'])
			{
				case LOGIN_ERROR_ATTEMPTS:

					$captcha = phpbb_captcha_factory::get_instance(self::$config['captcha_plugin']);
					$captcha->init(CONFIRM_LOGIN);
					// $captcha->reset();

					// Parse the captcha template
					self::reset_template();
					self::$template->set_filenames(array(
						'captcha'	=> $captcha->get_template(),
					));

					// Correct confirm image link
					self::$template->assign_var('CONFIRM_IMAGE_LINK', self::append_sid('ucp', 'mode=confirm&amp;confirm_id=' . $captcha->confirm_id . '&amp;type=' . $captcha->type));

					self::$template->assign_display('captcha', 'CAPTCHA', false);

					titania::set_custom_template();

					$err = self::$user->lang[$result['error_msg']];
				break;

				case LOGIN_ERROR_PASSWORD_CONVERT:
					$err = sprintf(
						self::$user->lang[$result['error_msg']],
						(self::$config['email_enable']) ? '<a href="' . self::append_sid('ucp', 'mode=sendpassword') . '">' : '',
						(self::$config['email_enable']) ? '</a>' : '',
						(self::$config['board_contact']) ? '<a href="mailto:' . htmlspecialchars(self::$config['board_contact']) . '">' : '',
						(self::$config['board_contact']) ? '</a>' : ''
					);
				break;

				// Username, password, etc...
				default:
					$err = self::$user->lang[$result['error_msg']];

					// Assign admin contact to some error messages
					if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
					{
						$err = (!self::$config['board_contact']) ? sprintf(self::$user->lang[$result['error_msg']], '', '') : sprintf(self::$user->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars(self::$config['board_contact']) . '">', '</a>');
					}

				break;
			}
		}

		// Assign credential for username/password pair
		$credential = ($admin) ? md5(unique_id()) : false;

		$s_hidden_fields = array(
			'sid'		=> self::$user->session_id,
		);

		if ($redirect)
		{
			$s_hidden_fields['redirect'] = $redirect;
		}

		if ($admin)
		{
			$s_hidden_fields['credential'] = $credential;
		}

		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		titania::page_header('LOGIN');

		self::$template->assign_vars(array(
			'LOGIN_ERROR'		=> $err,
			'LOGIN_EXPLAIN'		=> $l_explain,

			'U_SEND_PASSWORD' 		=> (self::$config['email_enable']) ? self::append_sid('ucp', 'mode=sendpassword') : '',
			'U_RESEND_ACTIVATION'	=> (self::$config['require_activation'] == USER_ACTIVATION_SELF && self::$config['email_enable']) ? self::append_sid('ucp', 'mode=resend_act') : '',
			'U_TERMS_USE'			=> self::append_sid('ucp', 'mode=terms'),
			'U_PRIVACY'				=> self::append_sid('ucp', 'mode=privacy'),

			'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
			'S_HIDDEN_FIELDS' 		=> $s_hidden_fields,

			'S_ADMIN_AUTH'			=> $admin,
			'USERNAME'				=> ($admin) ? self::$user->data['username'] : '',

			'USERNAME_CREDENTIAL'	=> 'username',
			'PASSWORD_CREDENTIAL'	=> ($admin) ? 'password_' . $credential : 'password',
		));

		titania::page_footer(true, 'login_body.html');
	}

	/**
	* Update a user's postcount
	*
	* @param int $user_id The user_id
	* @param string $direction (+, -)
	* @param int $amount The amount to add or subtract
	*/
	public static function update_user_postcount($user_id, $direction = '+', $amount = 1)
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts ' . (($direction == '+') ? '+' : '-') . ' ' . (int) $amount .
				(($direction == '+') ? ', user_lastpost_time = ' . time() : '') . '
			WHERE user_id = ' . (int) $user_id;
		self::$db->sql_query($sql);
	}
}
