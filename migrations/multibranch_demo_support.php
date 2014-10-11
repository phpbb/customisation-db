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

class multibranch_demo_support extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_demo_url'))),
		);
	}

	public function update_demo_url($start)
	{
		$prefix = $this->get_titania_table_prefix();
		$limit = 250;
		$i = 0;

		$sql = "SELECT DISTINCT c.contrib_id, c.contrib_demo, rp.phpbb_version_branch
			FROM {$prefix}contribs c
			LEFT JOIN {$prefix}revisions_phpbb rp
				ON (rp.contrib_id = c.contrib_id)
			WHERE c.contrib_demo <> ''";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Check that this hasn't already been processed... just in case.
			if (is_array(json_decode($row['contrib_demo'], true)))
			{
				continue;
			}
			$branch = (empty($row['phpbb_version_branch'])) ? 30 : (int) $row['phpbb_version_branch'];
			$demo = json_encode(array($branch => $row['contrib_demo']));

			$sql = 'UPDATE ' . $prefix . 'contribs
				SET contrib_demo = "' . $this->db->sql_escape($demo) . '"
				WHERE contrib_id = ' . (int) $row['contrib_id'];
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
