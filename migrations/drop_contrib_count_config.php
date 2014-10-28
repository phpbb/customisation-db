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

class drop_contrib_count_config extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\titania\migrations\release_1_1_0');	
	}

	public function effectively_installed()
	{
		return !isset($this->config['titania_num_styles']);
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('titania_num_contribs')),
			array('config.remove', array('titania_num_bbcodes')),
			array('config.remove', array('titania_num_bridges')),
			array('config.remove', array('titania_num_converters')),
			array('config.remove', array('titania_num_mods')),
			array('config.remove', array('titania_num_official_tools')),
			array('config.remove', array('titania_num_translations')),
			array('config.remove', array('titania_num_styles')),
		);
	}
}
