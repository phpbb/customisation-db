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

class titania_db
{
	private static $db_data = array();
	public static function init()
	{
		global $base_path, $root_path;

		if (!isset($base_path))
		{
			$base_path = '';
		}
		
		$request_uri = substr($_SERVER['REQUEST_URI'], strlen($base_path));
		$db_name = (strpos($request_uri, '/') !== false) ? substr($request_uri, 0, strpos($request_uri, '/')) : $request_uri;
		
		$db_name = htmlspecialchars($db_name);
		
		$sql = 'SELECT * FROM  ' . CONTRIB_DB_TABLE . ' WHERE db_name = \'' . $db->sql_escape($db_name) . '\'';
		$result = $db->sql_query($sql);
		
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			return false;
		}
		self::$db_data = $row;

		if (self::$db_data['include_file'] && file_exists($root_path . 'db/data/' . elf::$db_data['include_file']))
		{
			include($root_path . 'db/data/' . self::$db_data['include_file']);
			
			if (!class_exists($db_name . '_config'))
			{
				trigger_error(sprintf('Cant find class %s_config', $db_name));
			}
			$classname = $db_name . '_config';
			$classname::load_hooks();
		}

		return true;
	}
}
?>