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

class composer_cron_config extends base
{
	static public function depends_on()
	{
		return array(
			'\phpbb\titania\migrations\composer_integration',
		);
	}

	public function effectively_installed()
	{
		return isset($this->config['titania_next_repo_rebuild']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('titania_next_repo_rebuild', time(), true)),
		);
	}
}
