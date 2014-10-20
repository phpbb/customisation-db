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

class update_url_field_values extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_post_url'))),
			array('custom', array(array($this, 'update_topic_url'))),
			
		);
	}

	public function update_post_url($start)
	{
		return $this->update_url('posts', 'post', $start);
	}

	public function update_topic_url($start)
	{
		return $this->update_url('topics', 'topic', $start);
	}

	protected function update_url($table, $field, $start)
	{
		$table = $this->get_titania_table_prefix() . $table;
		$limit = 250;
		$i = 0;

		$sql = "SELECT {$field}_id, {$field}_url, {$field}_type
			FROM $table";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$url = $row[$field . '_url'];
			$pieces = explode('/', $url);
			$params = array();

			switch ($row[$field . '_type'])
			{
				case TITANIA_SUPPORT:
				case TITANIA_QUEUE_DISCUSSION:
					$params = array(
						'contrib_type'	=> $pieces[0],
						'contrib'		=> $pieces[1],
					);
				break;

				case TITANIA_QUEUE:
					$params = array(
						'id'	=> (int) substr($url, strrpos($url, '_') + 1),
					);
				break;
			}

			$params = serialize($params);

			$sql = "UPDATE $table
				SET {$field}_url = '" . $this->db->sql_escape($params) . "'
				WHERE {$field}_id = " . (int) $row[$field . '_id'];
			$this->db->sql_query($sql);

			$i++;
		}

		if ($i === $limit)
		{
			return $start + $i;
		}
	}
}
