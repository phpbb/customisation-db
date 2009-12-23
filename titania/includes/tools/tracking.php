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

		if (self::get_track($type, $id) >= (($time === false) ? titania::$time : (int) $time))
		{
			return;
		}

		$sql = 'UPDATE ' . self::$sql_table . '
			SET track_time = ' . (($time === false) ? titania::$time : (int) $time) . '
			WHERE track_type = ' . (int) $type . '
				AND track_id = ' . (int) $id . '
				AND track_user_id = ' . (int) phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		if (!phpbb::$db->sql_affectedrows() && self::get_track($type, $id) == 0)
		{
			$sql_ary = array(
				'track_type'		=> (int) $type,
				'track_id'			=> (int) $id,
				'track_user_id'		=> (int) phpbb::$user->data['user_id'],
				'track_time'		=> ($time === false) ? titania::$time : (int) $time,
			);

			phpbb::$db->sql_query('INSERT INTO ' . self::$sql_table . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));
		}

		self::$store[$type][$id] = ($time === false) ? titania::$time : (int) $time;
	}

	/**
	 * Check if an item is unread
	 *
	 * @param <int> $type The type id of the item
	 * @param <int> $id The id of the item
	 * @param <int> $last_update The last time the item was updated
	 * @param <bool> $no_query True if we
	 * @return <bool> True if the item is unread, false if it is read
	 */
	public static function is_unread($type, $id, $last_update, $no_query = true)
	{
		return ($last_update > self::get_track($type, $id, $no_query)) ? true : false;
	}

	public static function get_track($type, $id, $no_query = false)
	{
		// Ignore
		self::get_track_cookie();

		if (isset(self::$store[$type][$id]))
		{
			return self::$store[$type][$id];
		}

		if (!phpbb::$user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return 0;
		}

		$sql = 'SELECT track_time FROM ' . self::$sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id . '
			AND track_user_id = ' . phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		self::$store[$type][$id] = (int) phpbb::$db->sql_fetchfield('track_time');

		phpbb::$db->sql_freeresult();

		return self::$store[$type][$id];
	}

	public static function get_tracks($type, $ids)
	{
		// Ignore
		self::get_track_cookie();

		if (!sizeof($ids) || !phpbb::$user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return;
		}

		$sql = 'SELECT track_id, track_time FROM ' . self::$sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND ' . phpbb::$db->sql_in_set('track_id', $ids) . '
			AND track_user_id = ' . phpbb::$user->data['user_id'];
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$store[$type][$row['track_id']] = $row['track_time'];
		}
		phpbb::$db->sql_freeresult($result);
	}

	public static function get_track_sql(&$sql_ary, $type, $id_field, $prefix = 'tt')
	{
		$sql_ary['LEFT_JOIN'] = (!isset($sql_ary['LEFT_JOIN'])) ? array() : $sql_ary['LEFT_JOIN'];

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(TITANIA_TRACK_TABLE => $prefix),
			'ON'	=> "{$prefix}.track_type = $type AND {$prefix}.track_id = $id_field",
		);

		$sql_ary['SELECT'] .= ", {$prefix}.track_time";
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
			AND track_user_id = ' . phpbb::$user->data['user_id'];
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
			WHERE track_user_id = ' . phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		self::$store = array();
	}

	private static function track_cookie($type, $id, $time = false)
	{
		phpbb::$user->set_cookie('titania_track', serialize(self::$store), (titania::$time + 31536000));
	}

	private static function get_track_cookie()
	{
		if (self::$grabbed_cookies == true)
		{
			return;
		}

		$cookie = request_var(phpbb::$config['cookie_name'] . '_' . 'titania_track', '', false, true);
		if ($cookie)
		{
			self::$store = unserialize($cookie);
		}

		self::$grabbed_cookies = true;
	}
}
