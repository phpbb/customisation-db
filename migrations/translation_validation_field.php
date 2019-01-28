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

/**
 * Class translation_validation_field
 * Made to add the tv_results field to the queue table.
 * @package phpbb\titania\migrations
 */
class translation_validation_field extends base
{
	public function effectively_installed()
	{
		$table_prefix = $this->get_titania_table_prefix();
		return $this->db_tools->sql_column_exists($table_prefix . 'queue', 'tv_results');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\titania\migrations\release_1_1_0',
			'\phpbb\titania\migrations\release_1_1_1',
		);
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'add_columns' => array(
				$table_prefix . 'queue' => array(
					'tv_results'		=> array('MTEXT', ''),
				),
				$table_prefix . 'queue' => array(
					'tv_results'		=> array('MTEXT', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'drop_columns' => array(
				$table_prefix . 'queue' => array(
					'tv_results'
				),
				$table_prefix . 'queue'	=> array(
					'tv_results'
				),
			),
		);
	}
}
