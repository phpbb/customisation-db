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

class release_1_1_1 extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['titania_version'], '1.1.1', '>=');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'change_columns' => array(
				$table_prefix . 'contribs' => array(
					'contrib_demo'			=> array('VCHAR_UNI:8000', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('titania_version', '1.1.1')),
		);
	}
}

