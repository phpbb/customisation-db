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

namespace phpbb\titania\search;

class manager
{
	/** @var \phpbb\di\service_collection */
	protected $drivers;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\search\driver\driver_interface */
	protected $active_driver;

	/**
	 * Constructor
	 *
	 * @param \phpbb\di\service_collection $drivers
	 * @param \phpbb\titania\config\config $ext_config
	 */
	public function __construct(\phpbb\di\service_collection $drivers, \phpbb\titania\config\config $ext_config)
	{
		$this->drivers = $drivers;
		$this->ext_config = $ext_config;
	}

	/**
	 * Check whether the search system is enabled.
	 *
	 * @return bool
	 */
	public function search_enabled()
	{
		return $this->ext_config->search_enabled && $this->active_driver !== null;
	}

	/**
	 * Set active search driver.
	 *
	 * @param bool $ignore_failures	Whether to ignore initialisation failures.
	 */
	public function set_active_driver($ignore_failures = false)
	{
		if ($this->active_driver)
		{
			return;
		}
		$driver = 'phpbb.titania.search.driver.' . $this->ext_config->search_backend;

		if (isset($this->drivers[$driver]))
		{
			$this->active_driver = $this->drivers[$driver];

			if ($ignore_failures)
			{
				try
				{
					$this->active_driver->initialise();
				}
				catch (\Exception $e)
				{
					$this->active_driver = null;
				}
			}
			else
			{
				$this->active_driver->initialise();
			}
		}
	}

	/**
	 * Get active driver.
	 *
	 * @return driver\driver_interface|null
	 */
	public function get_active_driver()
	{
		return $this->active_driver;
	}

	/**
	 * Index an object.
	 *
	 * @param int $object_type
	 * @param int $object_id
	 * @param $data
	 */
	public function index($object_type, $object_id, $data)
	{
		$this->set_active_driver(true);

		if ($this->search_enabled())
		{
			$this->get_active_driver()->index($object_type, $object_id, $data);
		}
	}

	/**
	 * Mass index objects.
	 *
	 * @param array $data
	 */
	public function mass_index($data)
	{
		$this->set_active_driver(true);

		if ($this->search_enabled())
		{
			$this->get_active_driver()->mass_index($data);
		}
	}

	/**
	 * Truncate the search index.
	 *
	 * @param int|bool $object_type Optional object type to limit to
	 */
	public function truncate($object_type = false)
	{
		$this->set_active_driver(true);

		if ($this->search_enabled())
		{
			$this->get_active_driver()->truncate($object_type);
		}
	}

	/**
	 * Delete an object from the index
	 *
	 * @param int $object_type
	 * @param int $object_id
	 */
	public function delete($object_type, $object_id)
	{
		$this->set_active_driver(true);

		if ($this->search_enabled())
		{
			$this->get_active_driver()->delete($object_type, $object_id);
		}
	}
}
