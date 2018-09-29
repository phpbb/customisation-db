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

		// Ignore markdown files too
		'language/en/AUTHORS.md',
		'language/en/README.md',
		'language/en/LICENSE.md',
		'language/en/CHANGELOG.md',
		'language/en/VERSION.md',
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
					$this->add_htm_files($uploaded_language, $reference_filepath);

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

			foreach ($files as $file)
			{
				$list_uploaded_files[] = $dir_clean . $file;
			}
		}
		// It's time to check if each file uploaded in the package is allowed
		foreach ($list_uploaded_files as $file)
		{
			if (!in_array($file, $list_reference_files) && !in_array($file, $this->ignore_files))
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
	 */
	private function add_htm_files($lang_root_path, $reference_path)
	{
		// Add index.htm files in all directories and subdirectories
		$finder = new Finder();
		$iterator = $finder->directories()->in($lang_root_path);

		foreach ($iterator as $file)
		{
			$res = fopen($file->getRealpath() . '/index.htm', 'w');
			fwrite($res, file_get_contents($reference_path . '/language/en/index.htm'));
			fclose($res);
		}
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
			if (is_file("$root_dir$dir$fname") && !in_array(strtoupper($fname), $this->ignore_files))
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
