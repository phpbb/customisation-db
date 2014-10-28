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
 * titania class and functions for use within titania pages and apps.
 */
class titania
{
	/**
	 * Titania configuration member
	 *
	 * @var titania_config
	 */
	public static $config;

	/**
	 * Instance of titania_cache class
	 *
	 * @var titania_cache
	 */
	public static $cache;

	/**
	* Hooks instance
	*
	* @var phpbb_hook
	*/
	public static $hook;

	/**
	 * Request time (unix timestamp)
	 *
	 * @var int
	 */
	public static $time;

	/**
	* Current User's Access level
	*
	* @var int $access_level Check TITANIA_ACCESS_ constants
	*/
	public static $access_level = 2;

	/**
	* Hold our main contribution object for the currently loaded contribution
	*
	* @var titania_contribution
	*/
	public static $contrib;

	/**
	 * Initialise titania:
	 *	Session management, Cache, Language ...
	 *
	 * @return void
	 */
	public static function initialise()
	{
		global $starttime;

		self::$time = (int) $starttime;
		self::$cache = phpbb::$container->get('phpbb.titania.cache');

		// Setup the Access Level
		self::$access_level = TITANIA_ACCESS_PUBLIC;

		// The user might be in a group with team access even if it's not his default group.
		$group_ids = implode(', ', self::$config->team_groups);

		$sql = 'SELECT group_id, user_id, user_pending FROM ' . USER_GROUP_TABLE . '
				WHERE user_id = ' . phpbb::$user->data['user_id'] . '
				AND user_pending = 0
				AND group_id IN (' . $group_ids . ')';
		$result = phpbb::$db->sql_query_limit($sql, 1);

		if ($group_id = phpbb::$db->sql_fetchfield('group_id'))
		{
			self::$access_level = TITANIA_ACCESS_TEAMS;
		}
		phpbb::$db->sql_freeresult($result);

		// Add common titania language file
		self::add_lang('common');

		// Load the contrib types
		self::_include('types/base');
		titania_types::load_types();

		// Load hooks
		self::load_hooks();
	}

	/**
	 * Reads a configuration file with an assoc. config array
	 */
	public static function read_config_file()
	{
		try
		{
			self::$config = titania_get_config(TITANIA_ROOT, PHP_EXT);
		}
		catch(\Exception $e)
		{
			trigger_error($e->getMessage());
		}
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
		// Remove titania/overlord from the class name
		$file_name = str_replace(array('titania_', '_overlord'), '', $class_name);

		// Overlords always have _overlord in and the file name can conflict with objects
		if (strpos($class_name, '_overlord') !== false)
		{
			if (file_exists(TITANIA_ROOT . 'includes/overlords/' . $file_name . '.' . PHP_EXT))
			{
				include(TITANIA_ROOT . 'includes/overlords/' . $file_name . '.' . PHP_EXT);
				return;
			}
		}

		$directories = array(
			'objects',
			'tools',
			'core',
		);

		foreach ($directories as $dir)
		{
			if (file_exists(TITANIA_ROOT . 'includes/' . $dir . '/' . $file_name . '.' . PHP_EXT))
			{
				include(TITANIA_ROOT . 'includes/' . $dir . '/' . $file_name . '.' . PHP_EXT);
				return;
			}
		}

		// No error if file cant be found!
	}

	/**
	 * Add a Titania language file
	 *
	 * @param mixed $lang_set
	 * @param bool $use_db
	 * @param bool $use_help
	 */
	public static function add_lang($lang_set, $use_db = false, $use_help = false)
	{
		phpbb::$user->add_lang_ext('phpbb/titania', $lang_set, $use_db, $use_help);
	}

	/**
	* Load the hooks
	* Using something very similar to the phpBB hook system
	*/
	public static function load_hooks()
	{
		if (self::$hook)
		{
			return;
		}

		// Add own hook handler
		self::$hook = new titania_hook();

		// Now search for hooks...
		$dh = @opendir(TITANIA_ROOT . 'includes/hooks/');
		if ($dh)
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strpos($file, 'hook_') === 0 && substr($file, -(strlen(PHP_EXT) + 1)) === '.' . PHP_EXT)
				{
					include(TITANIA_ROOT . 'includes/hooks/' . $file);
				}
			}
			closedir($dh);
		}
	}

	/**
	* Include a Titania includes file
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

		include(TITANIA_ROOT . 'includes/' . $file . '.' . PHP_EXT);
	}

	/**
	* Creates a log file with information that can help with identifying a problem
	*
	* @param int $log_type The log type to record. To be expanded for different types as needed
	* @param string $text The description to add with the log entry
	*/
	public static function log($log_type, $message = false)
	{
		switch ($log_type)
		{
			case TITANIA_ERROR:
				//Append the current server date/time, user information, and URL
				$text = date('d-m-Y @ H:i:s') . ' USER: ' . phpbb::$user->data['username'] . ' - ' . phpbb::$user->data['user_id'] . "\r\n";
				$text .= titania_url::$current_page_url;

				// Append the sent message if any
				$text .= (($message !== false) ? "\r\n" . $message : '');

				$server_keys = phpbb::$request->variable_names('_SERVER');
				$request_keys = phpbb::$request->variable_names('_REQUEST');

				//Let's gather the $_SERVER array contents
				if ($server_keys)
				{
					$text .= "\r\n-------------------------------------------------\r\n_SERVER: ";
					foreach ($server_keys as $key => $var_name)
					{
						$text .= sprintf('%1s = %2s; ', $key, phpbb::$request->server($var_name));
					}
					$text = rtrim($text, '; ');
				}

				//Let's gather the $_REQUEST array contents
				if ($request_keys)
				{
					$text .= "\r\n-------------------------------------------------\r\n_REQUEST: ";
					foreach ($request_keys as $key => $var_name)
					{
						$text .= sprintf('%1s = %2s; ', $key, phpbb::$request->variable($var_name, '', true));
					}
					$text = rtrim($text, '; ');
				}

				//Use PHP's error_log function to write to file
				error_log($text . "\r\n=================================================\r\n", 3, TITANIA_ROOT . "store/titania_log.log");
			break;

			case TITANIA_DEBUG :
				//Append the current server date/time, user information, and URL
				$text = date('d-m-Y @ H:i:s') . ' USER: ' . phpbb::$user->data['username'] . ' - ' . phpbb::$user->data['user_id'] . "\r\n";
				$text .= titania_url::$current_page_url;

				// Append the sent message if any
				$text .= (($message !== false) ? "\r\n" . $message : '');

				//Use PHP's error_log function to write to file
				error_log($text . "\r\n=================================================\r\n", 3, TITANIA_ROOT . "store/titania_debug.log");
			break;

			default:
				// phpBB Log System
				$args = func_get_args();
				call_user_func_array('add_log', $args);
			break;

		}
	}
}
