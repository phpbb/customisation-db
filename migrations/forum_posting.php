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

class forum_posting extends base
{
	public function effectively_installed()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return $this->db_tools->sql_column_exists($table_prefix . 'posts', 'post_queue_id');
	}

	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_1');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'add_columns'		=> array(
				$table_prefix . 'posts'			=> array(
					'post_queue_id'		=> array('UINT', 0),
				),
				$table_prefix . 'topics'			=> array(
					'topic_queue_id'	=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'drop_columns'		=> array(
				$table_prefix . 'posts'			=> array(
					'post_queue_id'
				),
				$table_prefix . 'topics'		=> array(
					'topic_queue_id'
				),
			),
		);
	}
}
