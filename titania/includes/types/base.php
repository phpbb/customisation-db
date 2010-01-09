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
	 * The language key, initialize in constructor
	 *
	 * @var string Language key
	 */
	public $lang = '';
}