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

class subscriptions_module extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');	
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'ucp',
				'UCP_MAIN',
					array(
						'module_basename'	=> '\phpbb\titania\ucp\subscriptions_module',
						'modes'	=> array('items', 'sections'),
					),
				)),
			);
		}
}
