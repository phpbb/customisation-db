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
 * Class to track/get read/unread status
 *
 * @package Titania
 */
class titania_tracking
{
	/**
	 * @var <array> Temporary storage of all the tracking data grabbed during this page
	 * $store[$type][$id] = mark time
	 */
	public static $store = array();

	/**
	 *
	 * @var <bool> Have we grabbed the sent cookies or not?
	 */
	private static $grabbed_cookies = false;

	/**
	 * @var <string> Database table used to store it in
	 */
	private static $sql_table = TITANIA_TRACK_TABLE;

	public static function track($type, $id, $time = false)
	{
		// Ignore
		self::get_track_cookie();

		// Cookie storage method
		if (!phpbb::$user->data['is_registered']) // @todo support the option to use cookies for all
		{
			self::track_cookie($type, $id, $time);
			return;
		}

		if (self::get_track($type, $id, true) >= (($time === false) ? titania::$time : (int) $time))
		{
			return;
		}

		$sql = 'UPDATE ' . self::$sql_table . '
			SET track_time = ' . (($time === false) ? titania::$time : (int) $time) . '
			WHERE track_type = ' . (int) $type . '
				AND track_id = ' . (int) $id . '
				AND track_user_id = ' . (int) phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		if (!phpbb::$db->sql_affectedrows())
		{
			$sql_ary = array(
				'track_type'		=> (int) $type,
				'track_id'			=> (int) $id,
				'track_user_id'		=> (int) phpbb::$user->data['user_id'],
				'track_time'		=> ($time === false) ? titania::$time : (int) $time,
			);

			$temp = phpbb::$db->return_on_error;
			phpbb::$db->return_on_error = true;

			phpbb::$db->sql_query('INSERT INTO ' . self::$sql_table . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

			phpbb::$db->return_on_error = $temp;
		}

		self::$store[$type][$id] = ($time === false) ? titania::$time : (int) $time;
	}

	/**
	 * Check if an item is unread
	 *
	 * @param <int> $type The type id of the item
	 * @param <int> $id The id of the item
	 * @param <int> $last_update The last time the item was updated
	 * @param <bool> $no_query True if we do not want to query the database
	 * @return <bool> True if the item is unread, false if it is read
	 */
	public static function is_unread($type, $id, $last_update, $no_query = true)
	{
		return ($last_update > self::get_track($type, $id, $no_query)) ? true : false;
	}

	/**
	* Figure out the last read mark for an object
	*
	* @param mixed $unread_fields
	* 	array(
	* 		'type' => 0, (the object type to use when getting the tracking data)
	* 		'id' => 0, (the object id to use when getting the tracking data)
	* 		'parent_match' => false, (if isset and true, the 'id' field we search for is the $parent_id sent)
	* 		'type_match' => false, (if isset and true we will only count when the $object_type that is sent matches the type we are requesting from the tracking data)
	*	)
	* @param mixed $object_type
	* @param mixed $parent_id
	*
	* @return int last mark time
	*/
	public static function find_last_read_mark($unread_fields, $object_type, $parent_id)
	{
		$last_read_mark = 0;

		foreach ($unread_fields as $field_ary)
		{
			if (isset($field_ary['type_match']) && $field_ary['type'] != $object_type)
			{
				continue;
			}

			if (isset($field_ary['parent_match']))
			{
				$field_ary['id'] = $parent_id;
			}

			$last_read_mark = max($last_read_mark, titania_tracking::get_track($field_ary['type'], $field_ary['id'], true));
		}

		return $last_read_mark;
	}

	public static function get_track($type, $id, $no_query = false)
	{
		// Ignore
		self::get_track_cookie();

		if (isset(self::$store[$type][$id]))
		{
			return self::$store[$type][$id];
		}

		if ($no_query || !phpbb::$user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return 0;
		}

		$sql = 'SELECT track_time FROM ' . self::$sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id . '
			AND track_user_id = ' . (int) phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		self::$store[$type][$id] = (int) phpbb::$db->sql_fetchfield('track_time');

		phpbb::$db->sql_freeresult();

		return self::$store[$type][$id];
	}

	/**
	* Get tracking on multiple types/items at the same time from the database
	*
	* @param mixed $type array of types or the type
	* @param mixed $ids array of ids or an id
	*/
	public static function get_tracks($type, $ids)
	{
		// Ignore
		self::get_track_cookie();

		if (!sizeof($ids) || !phpbb::$user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return;
		}

		$sql = 'SELECT track_type, track_id, track_time FROM ' . self::$sql_table . '
			WHERE ' . ((!is_array($type)) ? 'track_type = ' . (int) $type : phpbb::$db->sql_in_set('track_type', array_map('intval', $type))) . '
			AND ' . phpbb::$db->sql_in_set('track_id', array_map('intval', $ids)) . '
			AND track_user_id = ' . (int) phpbb::$user->data['user_id'];
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$store[$row['track_type']][$row['track_id']] = $row['track_time'];
		}
		phpbb::$db->sql_freeresult($result);
	}

	public static function get_track_sql(&$sql_ary, $type, $id_field, $prefix = 'tt')
	{
		if (!phpbb::$user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return;
		}

		$type = (int) $type;
		$id_field = phpbb::$db->sql_escape($id_field);
		$prefix = phpbb::$db->sql_escape($prefix);

		$sql_ary['LEFT_JOIN'] = (!isset($sql_ary['LEFT_JOIN'])) ? array() : $sql_ary['LEFT_JOIN'];

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(TITANIA_TRACK_TABLE => $prefix),
			'ON'	=> "{$prefix}.track_type = $type
				AND {$prefix}.track_id = $id_field
				AND {$prefix}.track_user_id = " . (int) phpbb::$user->data['user_id'],
		);

		$sql_ary['SELECT'] .= ", {$prefix}.track_time as track_time_{$type}";
		$sql_ary['SELECT'] .= ", {$id_field} as track_time_{$type}_id";
	}

	public static function store_from_db($row)
	{
		foreach ($row as $name => $value)
		{
			if (strpos($name, 'track_time_') === 0 && strpos($name, '_id') === false)
			{
				$type = (int) substr($name, 11, 1);

				if (!isset($row['track_time_' . $type . '_id']))
				{
					continue;
				}

				$id = (int) $row['track_time_' . $type . '_id'];

				self::store_track($type, $id, $value);
			}
		}
	}

	/**
	 * Put the data in self::$store, for when you've already grabbed the info yourself
	 *
	 * @param <int> $type The type id of the item
	 * @param <int> $id The id of the item
	 * @param <int> $track_time The time it was last marked
	 */
	public static function store_track($type, $id, $track_time)
	{
		// Ignore
		self::get_track_cookie();

		self::$store[$type][(int) $id] = (int) $track_time;
	}

	public static function clear_track($type, $id)
	{
		$sql = 'DELETE FROM ' . self::$sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id . '
			AND track_user_id = ' . (int) phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		self::$store[$type][$id] = 0;
	}

	public static function clear_item($type, $id)
	{
		$sql = 'DELETE FROM ' . self::$sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id;
		phpbb::$db->sql_query($sql);

		self::$store[$type][$id] = 0;
	}

	public static function clear_user()
	{
		$sql = 'DELETE FROM ' . self::$sql_table . '
			WHERE track_user_id = ' . (int) phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		self::$store = array();
	}

	private static function track_cookie($type, $id, $time = false)
	{
		self::$store[$type][$id] = ($time === false) ? titania::$time : (int) $time;

		phpbb::$user->set_cookie('titania_track', serialize(self::$store), (titania::$time + 31536000));
	}

	private static function get_track_cookie()
	{
		if (self::$grabbed_cookies == true || phpbb::$user->data['is_registered'])
		{
			return;
		}

		$cookie = request_var(phpbb::$config['cookie_name'] . '_titania_track', '', false, true);
		if ($cookie)
		{
			self::$store = unserialize($cookie);
		}

		self::$grabbed_cookies = true;
	}
}
