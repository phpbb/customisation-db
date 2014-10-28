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
	/** @var \titania_config */
	protected $titania_config;

	/** @var string */
	protected $titania_table_prefix;

	protected function get_titania_config()
	{
		if ($this->titania_config)
		{
			return;
		}

		$root_path = $this->phpbb_root_path . 'ext/phpbb/titania/';

		if (!function_exists('titania_get_config'))
		{
			include($root_path . 'includes/functions.' . $this->php_ext);
		}

		try
		{
			$this->titania_config = titania_get_config($root_path, $this->php_ext);
		}
		catch(\Exception $e)
		{
			throw new exception($e->getMessage());
		}
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

