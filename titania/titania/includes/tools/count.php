<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
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
* Titania class to build and get the values for count fields stored in the DB
*/
class titania_count
{
	private static $fields = array(
		'teams'			=> 0,
		'authors'		=> 0,
		'public'		=> 0,
		'deleted'		=> 0,
		'unapproved'	=> 0,
	);

	/**
	* Get the flags for the current viewing user to get the count
	*
	* @param int $access_level
	* @param bool $deleted Can or cannot view deleted items
	* @param bool $unapproved Can or cannot view unapproved
	*/
	public static function get_flags($access_level, $deleted = false, $unapproved = false)
	{
		$flags = array();

		switch ($access_level)
		{
			case TITANIA_ACCESS_TEAMS :
				$flags[] = 'teams';
			case TITANIA_ACCESS_AUTHORS :
				$flags[] = 'authors';
			default :
				$flags[] = 'public';
		}

		if ($deleted)
		{
			$flags[] = 'deleted';
		}

		if ($unapproved)
		{
			$flags[] = 'unapproved';
		}

		return $flags;
	}

	/**
	* Get the flags for the update call (increment, decrement)
	*
	* @param int $access_level
	* @param bool $deleted Can or cannot view deleted items
	* @param bool $unapproved Can or cannot view unapproved
	*/
	public static function update_flags($access_level, $deleted = false, $unapproved = false)
	{
		$flags = array();

		if ($deleted)
		{
			$flags[] = 'deleted';
		}
		else if ($unapproved)
		{
			$flags[] = 'unapproved';
		}
		else
		{
			switch ($access_level)
			{
				case TITANIA_ACCESS_TEAMS :
					$flags[] = 'teams';
				break;
				case TITANIA_ACCESS_AUTHORS :
					$flags[] = 'authors';
				break;
				default :
					$flags[] = 'public';
				break;
			}
		}

		return $flags;
	}

	/**
	* Get the count from the db field
	*
	* @param string $from_db The field from the database
	* @param array|bool $flags The flags to check for (get_flags function) or false for the whole array
	*/
	public static function from_db($from_db, $flags)
	{
		self::reset_fields();

		$count = 0;
		$from_db = explode(':', $from_db);

		for ($i = 0; $i < sizeof($from_db) - 1; $i += 2)
		{
			$field_name = $from_db[$i];
			$field_value = $from_db[($i + 1)];

			self::$fields[$field_name] = $field_value;

			if (is_array($flags) && in_array($field_name, $flags))
			{
				$count += $field_value;
			}
		}

		return ($flags === false) ? self::$fields : $count;
	}

	/**
	* Increment one to the raw db field
	*
	* @param array $flags Should have the flag from update_flags()
	* @return string to_db()
	*/
	public static function increment($from_db, $flags)
	{
		if (sizeof($flags) != 1)
		{
			throw new exception('Only increment one field at a time (you are using the field incorrectly if you increment more than one field per item)');
		}

		// Get the count array from the data
		$cnt_ary = self::from_db($from_db, false);

		// Increment the appropriate fields
		foreach ($flags as $flag)
		{
			$cnt_ary[$flag] = (!isset($cnt_ary[$flag])) ? 1 : ((int) $cnt_ary[$flag] + 1);
		}

		// Return to_data() version
		return self::to_db($cnt_ary);
	}

	/**
	* Decrement one to the raw db field
	*
	* @param array $flags Should have the flag from update_flags()
	* @return string to_db()
	*/
	public static function decrement($from_db, $flags)
	{
		if (sizeof($flags) != 1)
		{
			throw new exception('Only decrement one field at a time (you are using the field incorrectly if you decrement more than one field per item)');
		}

		// Get the count array from the data
		$cnt_ary = self::from_db($from_db, false);

		// Decrement the appropriate fields
		foreach ($flags as $flag)
		{
			$cnt_ary[$flag] = (!isset($cnt_ary[$flag])) ? -1 : ((int) $cnt_ary[$flag] - 1);
		}

		// Return to_data() version
		return self::to_db($cnt_ary);
	}

	/**
	* Prepare the count to go to the db field
	*/
	public static function to_db($data)
	{
		self::reset_fields();

		self::$fields = array_merge(self::$fields, $data);

		$to_db = array();

		foreach (self::$fields as $field_name => $field_value)
		{
			$to_db[] = $field_name . ':' . $field_value;
		}

		return implode(':', $to_db);
	}

	public static function reset_fields()
	{
		// Reset the fields
		foreach (self::$fields as $field => $value)
		{
			self::$fields[$field] = 0;
		}
	}
}