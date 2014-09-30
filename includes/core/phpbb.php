<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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

	/** @var array $user->style */
	public static $style_data;

	/** @var request phpBB request class */
	public static $request;

	/** @var object phpBB container */
	public static $container;

	/* @var \phpbb\event\dispatcher */
	public static $dispatcher;

	/**
	 * Static Constructor.
	 */
	public static function initialise()
	{
		global $auth, $config, $db, $template, $user, $cache, $request, $phpbb_container, $phpbb_dispatcher;

		self::$auth		= &$auth;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;
		self::$cache	= &$cache;
		self::$request	= &$request;
		self::$container = &$phpbb_container;
		self::$dispatcher = &$phpbb_dispatcher;

		self::$style_data = self::$user->style;
		self::$container->set('phpbb.titania.config', titania::$config);
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
		self::$user->style = self::$style_data;
		self::$template->set_style();
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
