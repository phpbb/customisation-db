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

namespace phpbb\titania;

class tracking
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * @var <array> Temporary storage of all the tracking data grabbed during this page
	 * $store[$type][$id] = mark time
	 */
	public $store = array();

	/**
	 * @var <bool> Have we grabbed the sent cookies or not?
	 */
	protected $grabbed_cookies = false;

	/**
	 * @var <string> Database table used to store it in
	 */
	protected $sql_table = TITANIA_TRACK_TABLE;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\config\config $config
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\config\config $config)
	{
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->config = $config;
	}

	/**
	 * Track an object.
	 *
	 * @param int $type			Object type
	 * @param int $id			Object id
	 * @param bool|int $time	Optional track time to use, if none is given
	 * 		the value from time() is used.
	 */
	public function track($type, $id, $time = false)
	{
		// Ignore
		$this->get_track_cookie();

		// Cookie storage method
		if (!$this->user->data['is_registered']) // @todo support the option to use cookies for all
		{
			$this->track_cookie($type, $id, $time);
			return;
		}

		if ($this->get_track($type, $id, true) >= (($time === false) ? time() : (int) $time))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->sql_table . '
			SET track_time = ' . (($time === false) ? time() : (int) $time) . '
			WHERE track_type = ' . (int) $type . '
				AND track_id = ' . (int) $id . '
				AND track_user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$sql_ary = array(
				'track_type'		=> (int) $type,
				'track_id'			=> (int) $id,
				'track_user_id'		=> (int) $this->user->data['user_id'],
				'track_time'		=> ($time === false) ? time() : (int) $time,
			);

			$this->db->sql_return_on_error(true);

			$this->db->sql_query('INSERT INTO ' . $this->sql_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

			$this->db->sql_return_on_error();
		}

		$this->store[$type][$id] = ($time === false) ? time() : (int) $time;
	}

	/**
	 * Check if an item is unread
	 *
	 * @param int $type			The type id of the item
	 * @param int $id			The id of the item
	 * @param int $last_update	The last time the item was updated
	 * @param bool $no_query	True if we do not want to query the database
	 * @return bool True if the item is unread, false if it is read
	 */
	public function is_unread($type, $id, $last_update, $no_query = true)
	{
		return $last_update > $this->get_track($type, $id, $no_query);
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
	public function find_last_read_mark($unread_fields, $object_type, $parent_id)
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

			$last_read_mark = max($last_read_mark, $this->get_track($field_ary['type'], $field_ary['id'], true));
		}

		return $last_read_mark;
	}

	/**
	 * Get tracking time for an object.
	 *
	 * @param int $type			Object type
	 * @param int $id			Object id
	 * @param bool $no_query	Whether to query the database if the data
	 * 		isn't already loaded. Defaults to false.
	 * @return int
	 */
	public function get_track($type, $id, $no_query = false)
	{
		// Ignore
		$this->get_track_cookie();

		if (isset($this->store[$type][$id]))
		{
			return $this->store[$type][$id];
		}

		if ($no_query || !$this->user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return 0;
		}

		$sql = 'SELECT track_time FROM ' . $this->sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id . '
			AND track_user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$this->store[$type][$id] = (int) $this->db->sql_fetchfield('track_time');

		$this->db->sql_freeresult();

		return $this->store[$type][$id];
	}

	/**
	* Get tracking on multiple types/items at the same time from the database
	*
	* @param mixed $type array of types or the type
	* @param mixed $ids array of ids or an id
	*/
	public function get_tracks($type, $ids)
	{
		// Ignore
		$this->get_track_cookie();

		if (!sizeof($ids) || !$this->user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return;
		}

		$sql = 'SELECT track_type, track_id, track_time
			FROM ' . $this->sql_table . '
			WHERE ' . ((!is_array($type)) ? 'track_type = ' . (int) $type : $this->db->sql_in_set('track_type', array_map('intval', $type))) . '
				AND ' . $this->db->sql_in_set('track_id', array_map('intval', $ids)) . '
				AND track_user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->store[$row['track_type']][$row['track_id']] = $row['track_time'];
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Get tracking select SQL array.
	 *
	 * @param array $sql_ary	Existing SQL array
	 * @param int $type			Object type
	 * @param $id_field			Object id
	 * @param string $prefix	Table alias
	 */
	public function get_track_sql(array &$sql_ary, $type, $id_field, $prefix = 'tt')
	{
		if (!$this->user->data['is_registered']) // @todo support the option to use cookies for all
		{
			return;
		}

		$type = (int) $type;
		$id_field = $this->db->sql_escape($id_field);
		$prefix = $this->db->sql_escape($prefix);

		$sql_ary['LEFT_JOIN'] = (!isset($sql_ary['LEFT_JOIN'])) ? array() : $sql_ary['LEFT_JOIN'];

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(TITANIA_TRACK_TABLE => $prefix),
			'ON'	=> "{$prefix}.track_type = $type
				AND {$prefix}.track_id = $id_field
				AND {$prefix}.track_user_id = " . (int) $this->user->data['user_id'],
		);

		$sql_ary['SELECT'] .= ", {$prefix}.track_time as track_time_{$type}";
		$sql_ary['SELECT'] .= ", {$id_field} as track_time_{$type}_id";
	}

	/**
	 * Store tracking data from database.
	 *
	 * @param array $row
	 */
	public function store_from_db(array $row)
	{
		foreach ($row as $name => $value)
		{
			if (strpos($name, 'track_time_') === 0 && strpos($name, '_id') === false)
			{
				$type = (int) substr($name, 11, 2);

				if (!isset($row['track_time_' . $type . '_id']))
				{
					continue;
				}

				$id = (int) $row['track_time_' . $type . '_id'];

				$this->store_track($type, $id, $value);
			}
		}
	}

	/**
	 * Put the data in $this->store, for when you've already grabbed the info yourself
	 *
	 * @param <int> $type The type id of the item
	 * @param <int> $id The id of the item
	 * @param <int> $track_time The time it was last marked
	 */
	public function store_track($type, $id, $track_time)
	{
		// Ignore
		$this->get_track_cookie();

		$this->store[$type][(int) $id] = (int) $track_time;
	}

	/**
	 * Delete current user's tracking data for a given object.
	 *
	 * @param int $type		Object type
	 * @param int $id		Object id
	 */
	public function clear_track($type, $id)
	{
		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id . '
			AND track_user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$this->store[$type][$id] = 0;
	}

	/**
	 * Delete an object's tracking data for all users.
	 *
	 * @param int $type		Object type
	 * @param int $id		Object id
	 */
	public function clear_item($type, $id)
	{
		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE track_type = ' . (int) $type . '
			AND track_id = ' . (int) $id;
		$this->db->sql_query($sql);

		$this->store[$type][$id] = 0;
	}

	/**
	 * Delete all tracking for the current user.
	 */
	public function clear_user()
	{
		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE track_user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$this->store = array();
	}

	/**
	 * Set tracking data in a cookie.
	 *
	 * @param int $type			Object type
	 * @param int $id			Object id
	 * @param bool|int $time	Optional tracking time to use,
	 * 		if none is given, the value from time() is used
	 */
	protected function track_cookie($type, $id, $time = false)
	{
		$this->store[$type][$id] = ($time === false) ? time() : (int) $time;

		$this->user->set_cookie('titania_track', serialize($this->store), (time() + 31536000));
	}

	/**
	 * Get tracking data from cookie.
	 */
	protected function get_track_cookie()
	{
		if ($this->grabbed_cookies == true || $this->user->data['is_registered'])
		{
			return;
		}

		$cookie = $this->request->variable($this->config['cookie_name'] . '_titania_track', '', false, true);
		if ($cookie)
		{
			$this->store = tracking_unserialize($cookie);
		}

		$this->grabbed_cookies = true;
	}
}
