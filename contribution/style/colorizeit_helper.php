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

namespace phpbb\titania\contribution\style;

class colorizeit_helper
{
	/** @var array */
	protected $components;

	/**
	* Construct.
	*/
	public function __construct()
	{
		$this->components = array(
			'imageset',
			'template',
			'theme',
		);
	}

	/**
	* Generate ColorizeIt options for a revision.
	*
	* @param string $zip_file	Full path to revision zip file
	* @param string $temp_dir	Temporary directory
	* @throws \Exception		Throws exception if zip file does not exist
	* @return array
	*/
	public function generate_options($zip_file, $temp_dir)
	{
		if (!@file_exists($zip_file))
		{
		    throw new \Exception('ERROR_NO_ATTACHMENT');
		}

		$package = new \phpbb\titania\entity\package;
		$package
			->set_source($zip_file)
			->set_temp_path($temp_dir, true)
			->extract()
		;
		$data = $this->get_data($package->get_temp_path());
		$package->cleanup();

		return $data;
	}

	/**
	* Set ColorizeIt options for a revision.
	*
	* @param array $options
	* @param int $revision_id
	* @param \phpbb\db\driver\driver_interface $db
	*
	* @return null
	*/
	public function submit_options($options, $revision_id, $db)
	{
		$options = serialize($options);
		$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
		    SET revision_clr_options = "' . $db->sql_escape($options) . '"
		    WHERE revision_id = ' . (int) $revision_id;
		$db->sql_query($sql);
	}

	/**
	* Get data to be provided to ColorizeIt from style.
	*
	* @param string $directory		Full style directory path
	* @return array
	*/
	protected function get_data($directory)
	{
		$cfg_files = $this->find_cfg_files($directory);

		$no_result = array(
			'parser'	=> 'default',
			'options'	=> false,
		);

		// No cfg files were found. Not a phpBB style
		if (empty($cfg_files))
		{
			return $no_result;
		}

		$info = $this->get_style_info($cfg_files);
		extract($info);

		if (empty($properties))
		{
			return $no_result;
		}

		$has_style = !empty($properties['style']);
		$lang_imagesets = $this->get_valid_lang_imagesets($lang_imagesets, $imageset);

		if ($has_style)
		{
			$properties = $this->remove_clashing_components($properties);
		}

		$result = $this->get_properties_result($properties, array(
			'parser'	=> 'phpbb3',
			'options'	=> true,
		));

		if (!empty($lang_imagesets))
		{
			$result['phpbb3_lang'] = implode(',', $lang_imagesets);
		}

		if ($has_style && sizeof($properties['style']) == 1)
		{
		    $result['style_name'] = end($properties['style']);
		}

		return $result;
	}

	/**
	* Find all .cfg files in a given directory.
	*
	* @param string $directory		Full path to style directory
	* @return array
	*/
	protected function find_cfg_files($directory)
	{
		$finder = new \phpbb\finder(new \phpbb\filesystem);
		return $finder
			->suffix('.cfg')
			->find_from_paths(array('/' => $directory))
		;
	}

	/**
	* Get style info from .cfg files.
	*
	* @param array $cfg_files
	* @return array Returns array in form of
	*	array(
	*		'properties' => array,
	*		'lang_imagesets' => array,
	*		'imageset' array|bool,
	*	)
	*
	*/
	protected function get_style_info($cfg_files)
	{
		$properties = array();
		$expected = array('style.cfg', 'theme.cfg', 'template.cfg', 'imageset.cfg');
		$imageset = false;
		$lang_imagesets = array();

		foreach ($cfg_files as $file)
		{
			extract($file);
	  
			if (!in_array($filename, $expected))
			{
				continue;
			}

			$property = str_replace('.cfg', '', $filename);
			$property_dir = $this->get_style_dir($path, false);
			$is_component = in_array($property, $this->components);
			$style_dir = $this->get_style_dir($path, $is_component);

			if ($is_component && $property_dir != $property)
			{
				if ($property == 'imageset')
				{
					$lang_imagesets[] = $file;
				}
				continue;
			}
			$name = $this->get_name_from_cfg($path . $filename);

			if ($name !== false)
			{
				$properties['dir'][$style_dir] = $style_dir;
				$properties[$property][$name] = $name;

				if ($property == 'imageset')
				{
					$imageset = $file;
				}
			}
		}

		return array(
			'properties'		=> $properties,
			'imageset'			=> $imageset,
			'lang_imagesets'	=> $lang_imagesets,
		);
	}

	/**
	* Get style|component name from configuration file.
	*
	* @param string $file		Full path to .cfg file
	* @return bool|string Returns style|component name, or false
	*	on failure
	*/
	protected function get_name_from_cfg($file)
	{
		$result = parse_cfg_file($file);
		return (empty($result['name'])) ? false : $result['name'];
	}

	/**
	* Get style directory name from path.
	*
	* @param string $directory	Full style directory path.
	* @param bool $is_component	Whether the full path leads to a component.
	*
	* @param string
	*/
	protected function get_style_dir($directory, $is_component = false)
	{
		$dirs = explode('/', rtrim($directory, '/'));
		$count = sizeof($dirs);

		if (!$count)
		{
			return '';
		}
		$dir_num = $count - (($is_component) ? 2 : 1);

		return $dirs[$dir_num];
	}

	/**
	* Define result for given style properties.
	*
	* @param array $properties
	* @param array $result
	* @return array
	*/
	protected function get_properties_result($properties, $result)
	{
		foreach ($properties as $property => $instances)
		{
			$instances = array_values($instances);

			foreach ($instances as $index => $name)
			{
				$id = ($index > 0) ? $index : '';
				$result["phpbb3_{$property}{$id}"] = $name;
			}
		}
		return $result;
	}

	/**
	* Validate language imagesets to ensure that they're under the main imageset directory.
	*
	* @param array $lang_imagesets
	* @param array $imageset
	*
	* @return array Returns valid language directories - for example en.
	*/
	protected function get_valid_lang_imagesets($lang_imagesets, $imageset)
	{
		if (empty($lang_imagesets) || empty($imageset))
		{
			return array();
		}

		$valid = array();
		$imageset_path = $this->get_imageset_path($imageset['named_path']);

		foreach ($lang_imagesets as $index => $lang)
		{
			$lang_path = $this->get_imageset_path($lang['named_path']);
			$subpath = substr($lang_path, strlen($imageset_path . '/'));

			if (strpos($lang_path, $imageset_path) === 0 && preg_match('#^[a-zA-Z0-9\-_]+$#', $subpath))
			{
				$valid[] = $subpath;
			}
		}

		return $valid;
	}

	/**
	* Get imageset from named imageset.cfg path.
	*
	* @param string $named_path
	* @return string
	*/
	protected function get_imageset_path($named_path)
	{
		return substr($named_path, 0, - strlen('/imageset.cfg'));
	}

	/**
	* Remove component instances whose name matches the style name.
	*
	* @param array $properties		Style properties.
	*
	* @return array Returns $properties with the clashing component instances removed.
	*/
	protected function remove_clashing_components($properties)
	{
		$components = array_intersect_key($properties, array_flip($this->components));

		foreach ($components as $component => $instances)
		{
			foreach ($instances as $name)
			{
				if (isset($properties['style'][$name]))
				{
					unset($properties[$component][$name]);
				}
			}

			if (empty($properties[$component]))
			{
				unset($properties[$component]);
			}
		}
		return $properties;
	}
}
