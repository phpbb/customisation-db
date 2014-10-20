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

class multibranch_release_topic_support_p1 extends base
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');
	}

	public function update_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'change_columns'	=> array(
				$table_prefix . 'contribs'	=> array(
					'contrib_release_topic_id'	=> array('VCHAR:255', ''),
				),
			),
		);
	}
}
