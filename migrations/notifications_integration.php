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

namespace phpbb\titania\migrations;

use phpbb\titania\ext;

/**
* Seed notification preferences for existing subscribers.
*
* The legacy dispatcher emailed every watcher. The notification system's
* default delivery for users without preference rows is the board method only,
* so existing subscribers would silently stop receiving emails. Give every
* current watcher explicit board + email preferences for the notification
* types their subscriptions map to; new subscribers from here on get the
* standard core defaults.
*/
class notifications_integration extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'seed_user_notifications'))),
		);
	}

	/**
	* Seed user_notifications rows chunk by chunk.
	*
	* @param mixed $start Offset carried between calls by the migrator
	* @return mixed True when done, else the next offset
	*/
	public function seed_user_notifications($start)
	{
		$limit = 500;
		$start = (int) $start;

		$option_map = array(
			ext::TITANIA_CONTRIB			=> 'phpbb.titania.notification.type.contribution',
			ext::TITANIA_SUPPORT			=> 'phpbb.titania.notification.type.posted',
			ext::TITANIA_TOPIC				=> 'phpbb.titania.notification.type.posted',
			ext::TITANIA_QUEUE_DISCUSSION	=> 'phpbb.titania.notification.type.posted',
			ext::TITANIA_QUEUE				=> 'phpbb.titania.notification.type.queue',
			ext::TITANIA_QUEUE_TAG			=> 'phpbb.titania.notification.type.queue',
			ext::TITANIA_ATTENTION			=> 'phpbb.titania.notification.type.attention',
		);

		$watch_table = $this->get_titania_table_prefix() . 'watch';

		$sql = 'SELECT DISTINCT w.watch_user_id, w.watch_object_type
			FROM ' . $watch_table . ' w, ' . $this->table_prefix . 'users u
			WHERE w.watch_user_id = u.user_id
				AND w.watch_type = ' . \phpbb\titania\subscriptions::EMAIL . '
			ORDER BY w.watch_user_id, w.watch_object_type';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$row_count = 0;
		$wanted = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row_count++;

			if (isset($option_map[$row['watch_object_type']]))
			{
				$wanted[(int) $row['watch_user_id']][$option_map[$row['watch_object_type']]] = true;
			}
		}
		$this->db->sql_freeresult($result);

		if (!$row_count)
		{
			return true;
		}

		if (!empty($wanted))
		{
			// Users who already have a preference row for a type keep it untouched
			$sql = 'SELECT user_id, item_type, method
				FROM ' . $this->table_prefix . 'user_notifications
				WHERE item_id = 0
					AND ' . $this->db->sql_in_set('user_id', array_keys($wanted)) . '
					AND ' . $this->db->sql_in_set('item_type', array_unique(array_values($option_map)));
			$result = $this->db->sql_query($sql);

			$existing = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$existing[(int) $row['user_id']][$row['item_type']] = true;
			}
			$this->db->sql_freeresult($result);

			// The board method delivers by default without a row; an explicit
			// email row is all that is needed, like the rows user registration
			// creates for the post and topic types.
			$insert = array();
			foreach ($wanted as $user_id => $options)
			{
				foreach ($options as $option => $null)
				{
					if (isset($existing[$user_id][$option]))
					{
						continue;
					}

					$insert[] = array(
						'item_type'	=> $option,
						'item_id'	=> 0,
						'user_id'	=> $user_id,
						'method'	=> 'notification.method.email',
						'notify'	=> 1,
					);
				}
			}

			if (!empty($insert))
			{
				$this->db->sql_multi_insert($this->table_prefix . 'user_notifications', $insert);
			}
		}

		return ($row_count == $limit) ? $start + $limit : true;
	}
}
