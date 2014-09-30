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

class queue_validation_fields_rename extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function effectively_installed()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return $this->db_tools->sql_column_exists($table_prefix . 'queue', 'validation_notes_options');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'add_columns'	=> array(
				$table_prefix . 'queue'	=> array(
					'validation_notes'			=> array('MTEXT_UNI', ''),
					'validation_notes_bitfield'	=> array('VCHAR:255', ''),
					'validation_notes_uid'		=> array('VCHAR:8', ''),
					'validation_notes_options'	=> array('UINT:11', 7),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'move_field_data'))),
		);
	}

	public function move_field_data()
	{
		$table_prefix = $this->get_titania_table_prefix();

		$sql = 'UPDATE ' . $table_prefix . 'queue
			SET validation_notes = queue_validation_notes,
				validation_notes_bitfield = queue_validation_notes_bitfield,
				validation_notes_uid = queue_validation_notes_uid,
				validation_notes_options = queue_validation_notes_options';
		$this->db->sql_query($sql);
	}
}
