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

namespace phpbb\titania\contribution\translation;

use Symfony\Component\Finder\Finder;

/**
 * Translation prevalidator
 *
 * @author Vojtěch Vondra
 */
class prevalidator
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\contribution\prevalidator_helper */
	protected $helper;

	const NOT_REQUIRED = 0;
	const REQUIRED = 1;
	const REQUIRED_EMPTY = 2;
	const REQUIRED_DEFAULT = 3;

	/**
	 * Array of files to ignore in language pack
	 *
	 * @var array
	 */
	protected $ignore_files = array(
		'language/en/AUTHORS',
		'language/en/README',
		'language/en/LICENSE',
		'language/en/CHANGELOG',
		'language/en/VERSION',
	);

	/**
	 * File extensions list
	 * @var array
	 */
	protected $ignore_files_extensions = array(
		'',
		'.md',
		'.txt'
	);

	/**
	 * Computed ignore files
	 * @var array
	 */
	private $ignore_listing = array();

	/**
	 * For some reason we don't put index.htm in these directories
	 * @var array
	 */
	protected $exclude_htm_paths = array(
		'language/%s/email/short',
		'language/%s/help',
		'styles/prosilver/theme/%s'
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\contribution\prevalidator_helper $helper
	 */
	public function __construct(\phpbb\user $user, \phpbb\titania\contribution\prevalidator_helper $helper)
	{
		$this->user = $user;
		$this->helper = $helper;
	}

	/**
	 * Check to see if a file should be ignored
	 * @param $file
	 * @return bool
	 */
	private function is_in_ignored_list($file)
	{
		// Is the specified file in our listing?
		return in_array(strtolower($file), $this->ignore_listing);
	}

	/**
	 * Get helper.
	 *
	 * @return \phpbb\titania\contribution\prevalidator_helper
	 */
	public function get_helper()
	{
		return $this->helper;
	}

	/**
	 * Checks the file for the array contents
	 * Make sure it has all the keys present in the newest version
	 *
	 * @param \phpbb\titania\entity\package $package
	 * @param string $reference_filepath The path to the files against I want to validate the uploaded package
	 * @return array Returns an array of error messages encountered
	 */
	public function check_package($package, $reference_filepath)
	{
		$package->ensure_extracted();
		$error = $missing_keys = array();

		// Build up the list of files to ignore; do it here because we only want to run this computation once
		if (!count($this->ignore_listing))
		{
			foreach ($this->ignore_files as $ignore_file)
			{
				foreach ($this->ignore_files_extensions as $ignore_files_extension)
				{
					// Build up a list of our ignored files with the different extensions
					$this->ignore_listing[] = strtolower($ignore_file . $ignore_files_extension);
				}
			}
		}

		// Basically the individual parts of the translation, we check them separately, because they have colliding filenames
		$types = array(
			'ext' => 'ext/phpbb/viglink/language/en/',
			'language' => 'language/',
			'prosilver' => 'styles/prosilver/theme/en/',
		);

		$iso_code = '';

		// Do the check for all types
		foreach ($types as $type => $path)
		{
			// Get all the files present in the uploaded package for the currently iterated type
			$uploaded_files = $this->lang_filelist($package->get_temp_path());
			$reference_files = $this->lang_filelist($reference_filepath);
			ksort($uploaded_files);
			ksort($reference_files);

			switch ($type)
			{
				case 'language':
					// The uploaded files array has keys prefixed with the upload path of the contribution
					// Have it stored in the variable so we can work with it. Need to get the first key to ignore viglink
					$uploaded_files_keys = array_keys($uploaded_files);

					$uploaded_files_prefix = explode('/', $uploaded_files_keys[1]);
					$iso_code = $uploaded_files_prefix[2];
					$uploaded_files_prefix = $package->get_temp_path() . '/' . $uploaded_files_prefix[0];

					// This goes directly to the root of the uploaded language pack, like /upload_path/language/cs/
					$uploaded_language = $uploaded_files_prefix . '/language/';
					$uploaded_lang_root = $uploaded_language . $iso_code . '/';

					// Just perform a basic check if the common file is there
					if (!is_file($uploaded_lang_root . 'common.php'))
					{
						return array($this->user->lang('NO_TRANSLATION'));
					}

					// Loop through the reference files
					foreach ($reference_files as $dir => $files)
					{
						// Do not loop through files which are in non-language directories
						if (strpos($dir, $path) === 0)
						{
							// Loop through the files in the language/, language/adm etc. directories
							foreach ($files as $file)
							{
								$exists = true;
								$uploaded_file_path = str_replace('/en/', '/' . $iso_code . '/', $uploaded_files_prefix . '/' . $dir . $file);

								$ext = strtolower(substr($file, -3));
								// Require php and txt files
								if ($ext == 'php' || $ext == 'txt')
								{
									if (!is_file($uploaded_file_path))
									{
										$error[] = $this->user->lang('MISSING_FILE', str_replace('/en/', '/' . $iso_code . '/', $dir. $file)); // report a missing file
										$exists = false;
									}
								}

								// If the file is a php file and actually exists, no point in checking keys in a nonexistent one
								if ($ext == 'php' && $exists)
								{
									$missing_keys[$dir . $file] = $this->check_missing_keys($reference_filepath . '' .	$dir . $file, $uploaded_file_path);
								}
							}
						}
					}

					if (sizeof($missing_keys))
					{
						foreach ($missing_keys as $file => $keys)
						{
							if (sizeof($keys))
							{
								$error[] = $this->user->lang('MISSING_KEYS', $file, implode('<br />', $keys));
							}
						}
					}

					// In the last step we have removed the license and index files if there were any. We'll just put a new one instead
					$this->add_license_files($uploaded_lang_root . 'LICENSE', $reference_filepath);
					$this->add_htm_files($uploaded_language, $reference_filepath, $iso_code);

					break;

				case 'prosilver':
				case 'ext':
					// just let them go through atm...
					break;
			}

		}

		// We are going to check if all files included in the language pack are allowed
		// Before we need some stuff
		// We construct a list of all reference files with complete structure
		foreach ($reference_files as $dir => $files)
		{
			if (strpos($dir, $types['language']) === 0 || strpos($dir, $types['prosilver']) === 0 || strpos($dir, $types['ext']) === 0)
			{
				foreach ($files as $file)
				{
					$list_reference_files[] = $dir . $file;
				}
			}
		}

		$replaced_types_ext = str_replace('/en/', '/' . $iso_code . '/', $types['ext']);
		$replaced_types_prosilver = str_replace('/en/', '/' . $iso_code . '/', $types['prosilver']);

		// We construct a list of all uploaded file with complete structure
		foreach ($uploaded_files as $dir => $files)
		{
			// We need to clean our directory path according the type and replace iso_code package by en
			if (strpos($dir, $replaced_types_ext) != 0)
			{
				// We must have this first because otherwise the next language/{iso} condition would pick up the ext
				$dir_prefix = explode($replaced_types_ext, $dir);
			}
			else if (strpos($dir, $types['language']) != 0)
			{
				$dir_prefix = explode($types['language'], $dir);
			}
			else if (strpos($dir, $replaced_types_prosilver) != 0)
			{
				$dir_prefix = explode($replaced_types_prosilver, $dir);
			}
			$dir_clean = str_replace('/'.$iso_code.'/', '/en/', str_replace($dir_prefix[0], '', $dir));

			// Form the paths we don't worry about for index.htm files
			$exclude_htm_paths = $this->generate_exclude_htm_paths('en');

			foreach ($files as $file)
			{
				$add_file_to_list = true;

				if ($this->check_string_in_array($dir_clean, $exclude_htm_paths))
				{
					// There are some folders we don't need index.htm files in, it's neither here nor there.
					// Validation shouldn't fail because of it.
					if ($file == 'index.htm')
					{
						$add_file_to_list = false;
					}
				}

				if ($add_file_to_list)
				{
					// Add the file to the list which we will compare the package against
					$list_uploaded_files[] = $dir_clean . $file;
				}
			}
		}
		// It's time to check if each file uploaded in the package is allowed
		foreach ($list_uploaded_files as $file)
		{
			$in_reference_list = in_array($file, $list_reference_files);
			$in_ignore_list = $this->is_in_ignored_list($file);

			if (!$in_reference_list && !$in_ignore_list)
			{
				$error[] = $this->user->lang('WRONG_FILE', str_replace('/en/', '/'.$iso_code.'/', $file)); // report a wrong file
			}
		}

		if (!sizeof($error))
		{
			$package->repack(); // we have made changes to the package, so replace the original zip file
		}
		return $error;
	}

	/**
	 * Compares two phpBB 3.0.x language files and computes the missing keys in the uploaded file
	 * Does not include the uploaded file for security and uses string check on the file contents
	 *
	 * @param string $uploaded_file
	 * @param string $reference_file
	 * @return array returns an array with the missing keys
	 */
	private function check_missing_keys($reference_file, $uploaded_file)
	{
		// Check original file by including the language entries...
		$lang = $missing_keys = array();

		$contents = file_get_contents($uploaded_file);

		include($reference_file);
		//$lang_keys = $this->multiarray_keys($lang);

		// General cleanup
		$file_data = trim(str_replace(array("\r", "\t", ' '), '', $contents));
		$file_data = explode("\n", $file_data);

		$file_data = str_replace("\n", '', implode("\n", $file_data));

		// Now we have all array keys... let us have a look within $contents if all keys are there...
		// This can take a bit because we check every key... luckily with strpos...
		foreach (array_keys($lang) as $current_key)
		{
			if (is_string($current_key) && !is_array($lang[$current_key]) && strpos($file_data, "'{$current_key}'=>") === false)
			{
				if (empty($lang[$current_key]))
				{
					continue;
				}

				$missing_keys[] = $current_key;
			}
		}

		return $missing_keys;
	}

	/**
	 * Add license file
	 * @param $license_file
	 * @param $reference_path
	 */
	private function add_license_files($license_file, $reference_path)
	{
		$res = fopen($license_file, 'w');
		fwrite($res, file_get_contents($reference_path . 'docs/LICENSE.txt'));
		fclose($res);
	}

	/**
	 * Add index.htm files to any subdirectories of language/
	 * @param $lang_root_path
	 * @param $reference_path
	 * @param $iso_code
	 */
	private function add_htm_files($lang_root_path, $reference_path, $iso_code)
	{
		/* If we choose not to add the index.htm files for the excluded paths, we just need to add this:
		 *  $exclude_paths = $this->generate_exclude_htm_paths($iso_code);
		 * And then this inside the foreach loop:
		 *  if (!$this->check_string_in_array($real_path, $exclude_paths)) { ... }
		 */

		// Add index.htm files in all directories and subdirectories
		$finder = new Finder();
		$iterator = $finder->directories()->in($lang_root_path);

		foreach ($iterator as $file)
		{
			$real_path = $file->getRealpath();

			$res = fopen($real_path . '/index.htm', 'w');
			fwrite($res, file_get_contents($reference_path . '/language/en/index.htm'));
			fclose($res);
		}
	}

	/**
	 * Pop the iso code (language) into the path name dynamically
	 * @param $iso_code
	 * @return array
	 */
	private function generate_exclude_htm_paths($iso_code)
	{
		$exclude_paths = array();

		foreach ($this->exclude_htm_paths as $exclude_htm_path)
		{
			$exclude_paths[] = sprintf($exclude_htm_path, $iso_code);
		}

		return $exclude_paths;
	}

	/**
	 * Simple function to run strpos over an array of strings
	 * @param $string
	 * @param $array
	 * @return bool
	 */
	private function check_string_in_array($string, $array)
	{
		$match = false;

		foreach ($array as $item)
		{
			if (strpos($string, $item) !== false)
			{
				$match = true;
				break;
			}
		}

		return $match;
	}

	/**
	 * Basically flattens the files from all subdirectories of $root_dir into an array
	 *
	 * @param string $root_dir
	 * @param string $dir DO NOT USE, recursive!
	 * @return array
	 */
	private function lang_filelist($root_dir, $dir = '')
	{
		clearstatcache();
		$matches = array();

		// Add closing / if present
		$root_dir = ($root_dir && substr($root_dir, -1) != '/') ? $root_dir . '/' : $root_dir;

		// Remove initial / if present
		$dir = (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
		// Add closing / if present
		$dir = ($dir && substr($dir, -1) != '/') ? $dir . '/' : $dir;

		$dp = opendir($root_dir . $dir);
		while (($fname = readdir($dp)))
		{
			if (is_file("$root_dir$dir$fname") && !$this->is_in_ignored_list($fname))
			{
				$matches[$dir][] = $fname;
			}
			else if ($fname[0] != '.' && is_dir("$root_dir$dir$fname"))
			{
				$matches += $this->lang_filelist($root_dir, "$dir$fname");
			}
		}
		closedir($dp);

		return $matches;
	}

	/**
	 * array_keys for a multidimensional array
	 *
	 * @param array $array
	 * @return array
	 */
	private function multiarray_keys($array)
	{
		$keys = array();

		foreach($array as $k => $v)
		{
			$keys[] = $k;
			if (is_array($array[$k]))
			{
				$keys = array_merge($keys, $this->multiarray_keys($array[$k]));
			}
		}
		return $keys;
	}
}
