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

class config_settings extends base
{
	static public function depends_on()
	{
		return array(
			'\phpbb\titania\migrations\release_1_1_0',
			'\phpbb\titania\migrations\release_1_1_1',
		);
	}

	public function effectively_installed()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return $this->db_tools->sql_table_exists($table_prefix . 'config_settings');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'add_tables' => array(
				$table_prefix . 'config_settings' => array(
					'COLUMNS'	=> array(
						'config_name'	=> array('VCHAR', ''),
						'config_value'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'config_name',
				),
			),
		);
	}

	public function revert_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'drop_tables'	=> array(
				$table_prefix . 'config_settings',
			),
		);
	}
}

