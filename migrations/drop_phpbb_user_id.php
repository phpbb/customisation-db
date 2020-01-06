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

class drop_phpbb_user_id extends base
{
	public static function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function effectively_installed()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return !$this->db_tools->sql_column_exists($table_prefix . 'authors', 'phpbb_user_id');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'drop_columns' => array(
				$table_prefix . 'authors' => array('phpbb_user_id'),
			),
		);
	}

	public function revert_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'add_columns' => array(
				$table_prefix . 'authors' => array(
					'phpbb_user_id' => array('UINT', 0),
				),
			),
		);
	}
}
