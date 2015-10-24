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

namespace phpbb\titania\contribution\type;

use phpbb\di\service_collection;

class collection
{
	/** @var array */
	protected $types = array();

	/** @var array */
	protected $use_composer = array();

	/** @var array */
	protected $require_validation = array();

	/** @var array */
	protected $require_upload = array();

	/** @var array */
	protected $url_id_map = array();

	/** @var array */
	protected $name_id_map = array();

	/**
	 * Constructor
	 *
	 * @param service_collection $types
	 */
	public function __construct(service_collection $types)
	{
		foreach ($types as $type)
		{
			$this->types[$type::ID] = $type;
		}
		ksort($this->types);
		$this->classify();
	}

	/**
	 * Check whether the given type exists.
	 *
	 * @param int $id	Type id
	 * @return bool
	 */
	public function exists($id)
	{
		return isset($this->types[$id]);
	}

	/**
	 * Get type.
	 *
	 * @param int $id	Type id
	 * @return null|type_interface
	 */
	public function get($id)
	{
		$id = (int) $id;
		return ($this->exists($id)) ? $this->types[$id] : null;
	}

	/**
	 * Get all types.
	 *
	 * @return array
	 */
	public function get_all()
	{
		return $this->types;
	}

	/**
	 * Get type id's.
	 *
	 * @return array
	 */
	public function get_ids()
	{
		return array_keys($this->types);
	}

	/**
	 * Get type id by its name.
	 *
	 * @param string $name
	 * @return int|null
	 */
	public function get_id_by_name($name)
	{
		return (isset($this->name_id_map[$name])) ? $this->name_id_map[$name] : null;
	}

	/**
	 * Get the type_id from the url string
	 *
	 * @param string $url
	 * @return string|bool
	 */
	public function type_from_url($url)
	{
		return (isset($this->url_id_map[$url])) ? $this->url_id_map[$url] : false;
	}

	/**
	 * Get the types this user is authorized to perform actions on
	 *
	 * @param string $acl
	 * @return array
	 */
	public function find_authed($acl = 'view')
	{
		$authed = array();

		foreach ($this->types as $id => $type)
		{
			if ($type->acl_get($acl))
			{
				$authed[] = $id;
			}
		}

		return $authed;
	}

	/**
	 * Get the types that do not require validation
	 *
	 * @return array
	 */
	public function find_validation_free()
	{
		return array_diff(array_keys($this->types), $this->require_validation);
	}

	/**
	 * Get the types that require an upload
	 */
	public function require_upload()
	{
		return $this->require_upload;
	}

	/**
	 * Get the types that use Composer
	 *
	 * @param bool $negate
	 * @return array
	 */
	public function use_composer($negate = false)
	{
		return ($negate) ? array_diff(array_keys($this->types), $this->use_composer) : $this->use_composer;
	}

	/**
	 * Classify the types by their various properties.
	 *
	 * @return $this
	 */
	protected function classify()
	{
		$this->use_composer = $this->require_upload = $this->require_validation = $this->url_id_map = array();

		foreach ($this->types as $id => $type)
		{
			if ($type->create_composer_packages)
			{
				$this->use_composer[] = $id;
			}
			if ($type->require_upload)
			{
				$this->require_upload[] = $id;
			}
			if ($type->require_validation)
			{
				$this->require_validation[] = $id;
			}
			$this->url_id_map[$type::URL] = $id;
			$this->name_id_map[$type::NAME] = $id;
		}
		return $this;
	}
}
