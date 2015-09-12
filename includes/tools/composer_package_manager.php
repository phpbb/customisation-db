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

/**
 * Composer package manager helper for handling file system functions.
 *
 * @package Titania
 */
class titania_composer_package_helper
{
	/**
	 * @var string Path to the packages directory
	 */
	protected $packages_dir;

	/**
	 * @var array Array of open resource handles.
	 */
	protected $resources;

	public function __construct()
	{
		$this->packages_dir = \titania::$root_path . 'composer/';
		$this->resources = array();
	}

	/**
	 * Check if the packages directory is writable.
	 *
	 * @return Returns false if the composer directory is not writable.
	 */
	public function packages_dir_writable()
	{
		if (!is_writable($this->packages_dir))
		{
			return false;
		}
		return true;
	}

	/**
	 * Get resource handle for the file. Opens a new one if one doesn't exist or if the mode differs from the open one.
	 *
	 * @param string $file Name of JSON file to get handle for without file extension. For example: mod-packages
	 * @param string $mode Mode to open the file.
	 *
	 * @return Returns resource handle or false if an error occurred.
	 */
	protected function get_resource($file, $mode)
	{
		if (!empty($this->resources[$file]))
		{
			if ($mode != $this->resources[$file]['mode'])
			{
				$this->release_resource(false, $file);

				return $this->get_resource($file, $mode);
			}
			return $this->resources[$file]['resource'];
		}

		$filepath = $this->packages_dir . $file . '.json';

		if (!file_exists($filepath))
		{
			touch($filepath);
		}
		$resource = @fopen($filepath, $mode);

		if ($resource && @flock($resource, LOCK_EX))
		{
			$this->resources[$file] = array('resource' => $resource, 'mode' => $mode);
			return $resource;
		}

		return false;
	}

	/**
	 * Unlock and close open files in the resource handle pool.
	 *
	 * @param bool $all Release all open handles.
	 * @param string|array Name of specific JSON file(s) to release. As with get_resource(), it must not contain the file extension.
	 *
	 * @return void
	 */
	public function release_resource($all = true, $file = '')
	{
		$files = array();

		if ($all)
		{
			$files = array_keys($this->resources);
		}
		else
		{
			if (!empty($file) && is_string($file))
			{
				$files = array($file);
			}
			else if(is_array($file))
			{
				$files = $file;
			}
		}

		if (empty($files))
		{
			return;
		}

		foreach ($files as $file)
		{
			if (isset($this->resources[$file]))
			{
				@flock($this->resources[$file]['resource'], LOCK_UN);
				fclose($this->resources[$file]['resource']);
				unset($this->resources[$file]);
			}
		}
	}

	/**
	 * Get JSON data for the file specified.
	 *
	 * @param string $file Name of JSON file... must not contain file extension. For example: mod-packages
	 *
	 * @return Returns array of decoded data.
	 */
	public function get_json_data($file)
	{
		$resource = $this->get_resource($file, 'rb');

		if (!$resource)
		{
			return false;
		}

		$filesize = filesize($this->packages_dir . $file . '.json');

		if ($filesize === 0)
		{
			return array();
		}

		$json = fread($resource, $filesize);
		rewind($resource);

		if (!$json)
		{
			return array();
		}

		return json_decode($json, true);
	}

	/**
	 * Write JSON data to the file specified.
	 *
	 * @param string $file Name of JSON file... must not contain file extension. For example: mod-packages
	 * @param array $data Array of data to write.
	 *
	 * @return Retuns SHA1 hash for the JSON encoded data.
	 */
	public function put_json_data($file, $data)
	{
		$resource = $this->get_resource($file, 'wb');

		if (!$resource)
		{
			return false;
		}

		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		fwrite($resource, $data);
		$data_sha1 = sha1($data);

		return $data_sha1;
	}

	/**
	 * Remove all JSON files in the packages directory.
	 *
	 * @return void
	 */
	public function clear_packages_dir()
	{
        foreach (scandir($this->packages_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (!is_dir($this->packages_dir . $item) && substr($item, strrchr($item, '.') == '.json'))
			{
				@unlink($this->packages_dir . $item);
			}
		}
	}
}

/**
 * Composer package manager
 *
 * @package Titania
 */
class titania_composer_package_manager
{
	/**
	 * @var string Package name for the contribution
	 */
	public $package_name;

	/**
	 * @var string Contribution type name. Eg mod, style, translation
	 */
	public $type_name;

	/**
	 * @var string JSON file name for the contrib type. Eg packages-style, packages-mod
	 */
	public $packages_type_file;

	/**
	 * @var string JSON file name in which the contrib resides. Eg packages-style-1
	 */
	public $packages_file;

	/**
	 * @var array Decoded JSON data to be inserted into packages file.
	 */
	public $packages_data;

	/**
	 * @var object titania_composer_package_helper() object.
	 */
	public $helper;

	/**
	 * @var int Number of contributions per JSON file.
	 */
	public $contribs_per_file;

	/**
	 * @param int $contrib_id Contribution id
	 * @param string $contrib_name_clean Cleaned version of the contribution name
	 * @param int $contrib_type Contribution type
	 * @param object $helper titania_composer_package_helper() object.
	 * @param bool $write_map Write contrib id to type map.
	 *
	 * @return Returns false if the composer directory is not writable.
	 */
	public function __construct($contrib_id, $package_name, $contrib_type, $helper, $write_map = true)
	{
		$this->contrib_id = (int) $contrib_id;
		$this->package_name = $package_name;
		$this->helper = $helper;
		$this->contribs_per_file = 50;

		$this->type_name = titania_types::$types[$contrib_type]->name;
		$this->packages_type_file = 'packages-' . $this->type_name;
		$this->set_group_data($this->get_packages_group($write_map));
	}

	/**
	 * Set the package file and its data.
	 *
	 * @param int $group Group that the contribution belongs to.
	 *
	 * @return void
	 */
	public function set_group_data($group)
	{
		if (!$group)
		{
			return;
		}

		$this->packages_file = 'packages-' . $this->type_name . '-' . $group;
		$this->packages_data = $this->helper->get_json_data($this->packages_file);
	}

	/**
	 * Determine which group the contribution belongs to.
	 *
	 * The contrib type map has the contribs arranged by the order in which they were added.
	 * We then divide the map into groups of 50 (from contribs_per_file) to determine where the contrib belongs.
	 * If the contribs_per_file value is changed, the JSON files need to be regenerated.
	 *
	 * @param bool $write_map Write the contrib id to type map. We set this to false when rebuilding the packages to make avoid duplicates.
	 *
	 * @return Returns the group id
	 */
	public function get_packages_group($write_map)
	{
		$map = $this->helper->get_json_data($this->type_name . '-map');

		if ($map === false)
		{
			return false;
		}

		$reverse_map = array_flip($map);

		// If the contrib id isn't set, then this is the first revision approved.
		if (!isset($reverse_map[$this->contrib_id]))
		{
			// Add the contrib to the map.
			$map[] = $this->contrib_id;

			if ($write_map)
			{
				$this->helper->put_json_data($this->type_name . '-map', $map);
			}
			$reverse_map[$this->contrib_id] = sizeof($map) - 1;
		}
		// Determine the group id by dividing the index for the contrib by the number of contribs per JSON file and rounding up.
		$group = ceil(($reverse_map[$this->contrib_id] + 1) / $this->contribs_per_file);

		return $group;
	}

	/**
	 * Add a release to the packages data array.
	 *
	 * @param string $composer_json Package composer.json content
	 * @param int $attachment_id Attachment id for the revision download.
	 *
	 * @return void
	 */
	public function add_release($composer_json, $attachment_id, $validated)
	{
		$controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$path_helper = phpbb::$container->get('path_helper');

		$composer_json['dist'] = array(
			'url'	=> $path_helper->strip_url_params(
				$controller_helper->route('phpbb.titania.download', array('id' => $attachment_id)),
				'sid'),
			'type'	=> 'zip',
		);

		$this->packages_data['packages'][$this->package_name][$composer_json['version']] = $composer_json;
	}

	/**
	 * Remove a release from the packages data array.
	 *
	 * @param string $version Revision version.
	 *
	 * @return void
	 */
	public function remove_release($version)
	{
		if (isset($this->packages_data['packages'][$this->package_name][$version]))
		{
			unset($this->packages_data['packages'][$this->package_name][$version]);
		}

		// Remove the package if there aren't any releases left.
		if (!sizeof($this->packages_data['packages'][$this->package_name]))
		{
			$this->remove_package();
		}
	}

	/**
	 * Remove contrib from the packages data array.
	 *
	 * The contrib is left intact in the map to ensure that the grouping method still works afterward.
	 *
	 * @return void
	 */
	public function remove_package()
	{
		if (isset($this->packages_data['packages'][$this->package_name]))
		{
			unset($this->packages_data['packages'][$this->package_name]);
		}
	}

	/**
	 * Update the parent JSON files for the contrib type and the main packages.json
	 *
	 * @param string $packages_data_sha1 SHA1 hash of the JSON encoded data in the packages file.
	 *
	 * @return void
	 */
	protected function update_parents($packages_data_sha1)
	{
		if (!$packages_data_sha1)
		{
			return;
		}

		// Update the (contrib type)-packages.json file
		$sha1 = $this->update_include_sha1($this->packages_type_file, $this->packages_file, $packages_data_sha1);

		// Update main packages.json
		$this->update_include_sha1('packages', $this->packages_type_file, $sha1);
	}

	/**
	 * Update the SHA1 hash for an include
	 *
	 * @param string $file File to update.
	 * @param string $include Included file.
	 * @param string $sha1 SHA1 hash of the data in the included file.
	 *
	 * @return Returns the new SHA1 hash for the file that we've just updated.
	 */
	function update_include_sha1($file, $include, $sha1)
	{
		if (!$file || !$include || !$sha1)
		{
			return '';
		}

		$data = $this->helper->get_json_data($file);

		if ($data === false)
		{
			return '';
		}
		$data['includes'][$include . '.json']['sha1'] = $sha1;

		return $this->helper->put_json_data($file, $data);
	}

	/**
	 * Write the data in the packages data array to the packages JSON file and update the necessary SHA1 hashes.
	 *
	 * @return void
	 */
	public function submit()
	{
		$packages_sha1 = $this->helper->put_json_data($this->packages_file, $this->packages_data);

		// If we don't have a SHA1 hash, then something went wrong...
		if ($packages_sha1)
		{
			$this->update_parents($packages_sha1);
		}
		// Unlock and close any files that we opened.
		$this->helper->release_resource();
	}
}
