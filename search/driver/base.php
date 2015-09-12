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

namespace phpbb\titania\search\driver;

abstract class base implements driver_interface
{
	/** @var string */
	protected $name;

	/** @var bool */
	protected $search_all_supported = true;

	/**
	 * Set driver name
	 *
	 * @param string $name
	 */
	public function set_name($name)
	{
		$this->name = $name;
	}

	/**
	 * Get driver name
	 *
	 * @return string
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * @{inheritDoc}
	 */
	public function search_all_supported()
	{
		return $this->search_all_supported;
	}

	/**
	 * @{inheritDoc}
	 */
	public function set_type($type)
	{
		$this->where_equals('type', $type);
		return $this;
	}
}
