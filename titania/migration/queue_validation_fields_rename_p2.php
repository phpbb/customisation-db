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

namespace phpbb\titania\migration;

class queue_validation_fields_rename_p2 extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migration\queue_validation_fields_rename');
	}

	public function effectively_installed()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return !$this->db_tools->sql_column_exists($table_prefix . 'queue', 'queue_validation_notes_options');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'drop_columns'	=> array(
				$table_prefix . 'queue'	=> array(
					'queue_validation_notes',
					'queue_validation_notes_bitfield',
					'queue_validation_notes_uid',
					'queue_validation_notes_options',
				),
			),
		);
	}
}
