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

/**
 * titania class and functions for use within titania pages and apps.
 */
class titania
{
	/**
	 * Titania configuration member
	 *
	 * @var \phpbb\titania\config\config
	 */
	public static $config;

	/**
	 * Instance of titania_cache class
	 *
	 * @var titania_cache
	 */
	public static $cache;

	/** @var string */
	public static $root_path;

	/** @var string */
	public static $php_ext;

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
	 * Configure Titania.
	 *
	 * @param \phpbb\titania\config\config $config
	 * @param string $root_path
	 * @param string $php_ext
	 */
	public static function configure(\phpbb\titania\config\config $config, $root_path, $php_ext)
	{
		self::$config = $config;
		self::$root_path = $root_path;
		self::$php_ext = $php_ext;
	}

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
		self::$access_level = phpbb::$container->get('phpbb.titania.access')->get_level();

		// Add common titania language file
		phpbb::$user->add_lang_ext('phpbb/titania', 'common');
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
			if (file_exists(self::$root_path . 'includes/overlords/' . $file_name . '.' . self::$php_ext))
			{
				include(self::$root_path . 'includes/overlords/' . $file_name . '.' . self::$php_ext);
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
			if (file_exists(self::$root_path . 'includes/' . $dir . '/' . $file_name . '.' . self::$php_ext))
			{
				include(self::$root_path . 'includes/' . $dir . '/' . $file_name . '.' . self::$php_ext);
				return;
			}
		}

		// No error if file cant be found!
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

		include(self::$root_path . 'includes/' . $file . '.' . self::$php_ext);
	}
}
