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

use phpbb\db\migration\exception;

class base extends \phpbb\db\migration\migration
{
	/** @var \phpbb\titania\config\config */
	protected $titania_config;

	/** @var string */
	protected $titania_table_prefix;

	protected function get_titania_config()
	{
		if ($this->titania_config)
		{
			return;
		}

		global $phpbb_container;

		$this->titania_config = new \phpbb\titania\config\config(
			$phpbb_container->get('config'),
			$phpbb_container->getParameter('core.root_path') . 'ext/phpbb/titania/',
			$phpbb_container->getParameter('core.php_ext')
		);
	}

	protected function get_titania_table_prefix()
	{
		if (!is_string($this->titania_table_prefix))
		{
			$this->get_titania_config();
			$this->titania_table_prefix = $this->titania_config->table_prefix;
		}
		return $this->titania_table_prefix;
	}
}

