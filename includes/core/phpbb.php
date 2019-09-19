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

use phpbb\auth\auth;
use phpbb\cache\service as cache;
use phpbb\config\config;
use phpbb\db\driver\driver as db;
use phpbb\event\dispatcher;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

	/** @var request phpBB request class */
	public static $request;

	/** @var ContainerBuilder phpBB container */
	public static $container;

	/* @var dispatcher */
	public static $dispatcher;

	/** @var string */
	public static $root_path;

	/** @var string */
	public static $php_ext;

	/**
	 * Static Constructor.
	 */
	public static function initialise()
	{
		global $auth, $config, $db, $template, $user, $cache, $request;
		global $phpbb_container, $phpbb_dispatcher, $phpbb_root_path;

		self::$auth		= &$auth;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;
		self::$cache	= &$cache;
		self::$request	= &$request;
		self::$container = &$phpbb_container;
		self::$dispatcher = &$phpbb_dispatcher;
		self::$root_path = $phpbb_root_path;
		self::$php_ext = self::$container->getParameter('core.php_ext');
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
		if (!strpos($url, '.' . self::$php_ext))
		{
			$url = self::$root_path . $url . '.' . self::$php_ext;
		}

		return append_sid($url, $params, $is_amp, $session_id);
	}

	/**
	* Include a phpBB includes file
	*
	* @param string $file The name of the file
	* @param string|bool $function_check Bool false to ignore; string function name to check if the function exists (and not load the file if it does)
	* @param string|bool $class_check Bool false to ignore; string class name to check if the class exists (and not load the file if it does)
	* @param bool $class_auto_load Whether or not to attempt to auto-load the class given in $class_check
	*/
	public static function _include($file, $function_check = false, $class_check = false, $class_auto_load = true)
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
			if (class_exists($class_check, $class_auto_load))
			{
				return;
			}
		}

		include(self::$root_path . 'includes/' . $file . '.' . self::$php_ext);
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
