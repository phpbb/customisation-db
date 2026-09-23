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
 * Class bbcode_help_text
 * Extends the BBCode help line to a text column, following phpBB 3.3.5's
 * extension of bbcode_helpline (PHPBB3-16804).
 * @package phpbb\titania\migrations
 */
class bbcode_help_text extends base
{
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
			'change_columns' => array(
				$table_prefix . 'revisions' => array(
					'revision_bbc_help_line'	=> array('TEXT_UNI', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		$table_prefix = $this->get_titania_table_prefix();

		return array(
			'change_columns' => array(
				$table_prefix . 'revisions' => array(
					'revision_bbc_help_line'	=> array('VCHAR:255', ''),
				),
			),
		);
	}
}
