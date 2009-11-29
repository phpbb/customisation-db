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
	public static $store = array();

	public static function track($type, $id, $user_id = false, $time = false)
	{
		$sql_ary = array(
			'track_type'		=> (int) $type,
			'track_id'			=> (int) $id,
			'track_user_id'		=> ($user_id === false) ? phpbb::$user->data['user_id'] : (int) $user_id,
			'track_time'		=> ($time === false) ? titania::$time : (int) $time,
		);
		
		phpbb::$db->sql_query('INSERT INTO ' . TITANIA_TRACK_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));
	}

	public static function get_track($type, $id, $user_id = false)
	{
		$user_id = ($user_id === false) ? phpbb::$user->data['user_id'] : (int) $user_id;

		if (isset(self::$store[$type][$id][$user_id]))
		{
			return self::$store[$type][$id][$user_id];
		}

		$sql = 'SELECT track_time FROM ' . TITANIA_TRACK_TABLE . '
			WHERE track_type = ' . (int) $type . '
			AND track_track_id = ' . (int) $id . '
			AND track_user_id = ' . $user_id;
		phpbb::$db->sql_query($sql);

		self::$store[$type][$id][$user_id] = (int) phpbb::$db->sql_fetchfield('track_time');

		phpbb::$db->sql_freeresult();

		return self::$store[$type][$id][$user_id];
	}

	public static function get_tracks($type, $ids, $user_id = false)
	{
		$user_id = ($user_id === false) ? phpbb::$user->data['user_id'] : (int) $user_id;
		$tracks = array();

		$sql = 'SELECT track_id, track_time FROM ' . TITANIA_TRACK_TABLE . '
			WHERE track_type = ' . (int) $type . '
			AND ' . phpbb::$db->sql_in_set('track_id', $ids) . '
			AND track_user_id = ' . $user_id;
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$store[$type][$row['track_id']][$user_id] = $row['track_time'];
		}

		phpbb::$db->sql_freeresult($result);

		return $tracks;
	}

	public static function clear_track($type, $id, $user_id = false)
	{
		$sql = 'DELETE FROM ' . TITANIA_TRACK_TABLE . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id . '
			AND track_user_id = ' . (($user_id === false) ? phpbb::$user->data['user_id'] : (int) $user_id);
		phpbb::$db->sql_query($sql);
	}

	public static function clear_item($type, $id)
	{
		$sql = 'DELETE FROM ' . TITANIA_TRACK_TABLE . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id;
		phpbb::$db->sql_query($sql);
	}

	public static function clear_user($user_id = false)
	{
		$sql = 'DELETE FROM ' . TITANIA_TRACK_TABLE . '
			WHERE track_user_id = ' . (($user_id === false) ? phpbb::$user->data['user_id'] : (int) $user_id);
		phpbb::$db->sql_query($sql);
	}
}
