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

class titania_types
{
	/**
	* Store the types we've setup
	*
	* @var array(type_id => type_class)
	*/
	public static $types = array();

	/**
	* Load the types into the $types array
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

	/**
	* Get the type_id from the url string
	*
	* @param mixed $url
	*/
	public static function type_from_url($url)
	{
		foreach (self::$types as $type_id => $class)
		{
			if ($class->url == $url)
			{
				return $type_id;
			}
		}

		return false;
	}

	/**
	* Get the types this user is authorized to perform actions on
	*
	* @param mixed $type
	*/
	public static function find_authed($type = 'view')
	{
		$authed = array();

		foreach (self::$types as $type_id => $class)
		{
			if ($class->acl_get($type))
			{
				$authed[] = $type_id;
			}
		}

		return $authed;
	}

	public static function increment_count($type)
	{
		self::$types[$type]->increment_count();

		set_config('titania_num_mods', ++phpbb::$config['titania_num_contribs'], true);
	}

	public static function decrement_count($type)
	{
		self::$types[$type]->decrement_count();

		set_config('titania_num_mods', --phpbb::$config['titania_num_contribs'], true);
	}

	public static function get_count($type = false)
	{
		if ($type)
		{
			return self::$types[$type]->get_count();
		}

		return phpbb::$config['titania_num_mods'];
	}
}

/**
* Base class for types
*/
class titania_type_base
{
	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'contribution';

	/**
	 * The language key, initialize in constructor ($langs is for the plural forms of the language variables, used in category management)
	 *
	 * @var string Language key
	 */
	public $lang = '';
	public $langs = '';

	/**
	* Run MPV/Automod Test for this type?
	*/
	public $mpv_test = false;
	public $automod_test = false;
	public $clean_and_restore_root = false;
	
	/**
	 * The forum_database and forum_robot, initialize in constructor
	 *
	 * @var int
	 */
	public $forum_database = 0;
	public $forum_robot = 0;
}