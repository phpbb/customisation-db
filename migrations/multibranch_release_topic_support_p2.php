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

class multibranch_release_topic_support_p2 extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\multibranch_release_topic_support_p1');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_release_topic_id'))),
		);
	}

	public function update_release_topic_id($start)
	{
		$table_prefix = $this->get_titania_table_prefix();
		$limit = 250;
		$i = 0;

		if (!$start)
		{
			$sql = "UPDATE {$table_prefix}contribs
				SET contrib_release_topic_id = ''
					WHERE contrib_release_topic_id = 0";
			$this->db->sql_query($sql);
		}

		$sql = "SELECT DISTINCT c.contrib_id, c.contrib_release_topic_id, rp.phpbb_version_branch
			FROM {$table_prefix}contribs c,
				{$table_prefix}revisions_phpbb rp
			WHERE c.contrib_id = rp.contrib_id
				AND c.contrib_release_topic_id <> ''";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$data = json_encode(array(
				(int) $row['phpbb_version_branch'] => (int) $row['contrib_release_topic_id'],
			));

			$sql = "UPDATE {$table_prefix}contribs
				SET contrib_release_topic_id = '" . $this->db->sql_escape($data) . "'
				WHERE contrib_id = " . (int) $row['contrib_id'];
			$this->db->sql_query($sql);

			$i++;
		}
		$this->db->sql_freeresult($result);

		if ($i === $limit)
		{
			return $start + $i;
		}
	}
}
