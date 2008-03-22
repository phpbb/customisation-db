<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}


class titania_hooks
{
	private $hooks;
	
    /**
	 * Add a hook thats called when a certian action is done
	 * This function should be called in include file, class DB_NAME_config
	 *
	 * @param $name string hook name
	 * @param $db_type int Database type
	 * @param $class string Classname to call $function in. Should probarly be DB_NAME or so
	 * @param $function string functionname to be called.
	 */
	public static function add_hook($name, $db_type, $class, $function)
	{
		if (!isset(self::$hooks[$name . '_' . $db_type]))
		{
			self::$hooks[$name . '_' . $db_type] = array();
		}
		self::$hooks[$name . '_' . $db_type][] = array($class, $function);
		
		return true;
	}
	/**
	 * Delete a hook from the hooks list.
	 *
	 * @param $name string hook name
	 * @param $db_type int Database type
	 * @param $class string Classname to call $function in. Should probarly be DB_NAME or so
	 * @param $function string functionname to be called.
	 * @return bool true when sucessfull deleted, false when failed.
	 */
	public static function delete_hook($name, $db_type, $class, $function)
	{
		if (!isset(self::$hooks[$name . '_' . $db_type]))
		{
			return false;
		}
		
		$found = false;
		
		foreach (self::$hooks[$name . '_' . $db_type] as $i => $data)
		{
			if ($data[0] == $class && $data[1] == $function)
			{
				unset(self::$hooks[$name . '_' . $db_type][$i]);
				$found = true;
			}
		}
		return $found;
	}
	/**
	 * Call a hook.
	 * This function is called from the core. $parameter will contain some data about the called hook, like a contrib_id.
	 * @param $name string hook name
	 * @param $db_type int Database type
	 * @param $parameter array Array with parameters for function, like contrib_id.
	 */
	public static function call_hook($name, $db_type, $parameter = array())
	{
		if (!isset(self::$hooks[$name . '_' . $db_type]))
		{
			return false;
		}
		
		foreach (self::$hooks[$name . '_' . $db_type] as $i => $data)
		{
			/**
			 * @TODO: need to test if this works in PHP 5.2.2 and lower.
			 * Not fully sure following php.net
			 */
			call_user_func(($data[0] . '::' . $data[1]), $parameter);
		}
		return true;
	}
}
?>