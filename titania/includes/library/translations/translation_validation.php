<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

/**
 * Description of translation_validation
 *
 * @author Vojtěch Vondra
 * @package Titania
 */
class translation_validation extends titania_contrib_tools
{

	const NOT_REQUIRED = 0;
	const REQUIRED = 1;
	const REQUIRED_EMPTY = 2;
	const REQUIRED_DEFAULT = 3;

	/**
	* Array of files to ignore in language pack
	*
	* @var array
	*/
	protected $ignore_files = array('AUTHORS', 'README');

	public function __construct($original_zip, $new_dir_name)
	{
		parent::__construct($original_zip, $new_dir_name);
	}

	/**
	* Checks the file for the array contents
	* Make sure it has all the keys present in the newest version
	* 
	* @param string $reference_filepath The path to the files against I want to validate the uploaded package
	* @return array Returns an array of error messages encountered
	*/
	public function check_package($reference_filepath)
	{
		$error = $missing_keys = array();

		// Basically the individual parts of the translation, we check them separately, because they have colliding filenames
		$types = array(
			'language' => 'language/',
			'prosilver' => 'styles/prosilver/imageset/',
			'subsilver2' => 'styles/subsilver2/imageset/',
		);

		// Do the check for all types
		foreach ($types as $type => $path)
		{
			// Get all the files present in the uploaded package for the currently iterated type
			$uploaded_files = $this->lang_filelist($this->unzip_dir);
			$reference_files = $this->lang_filelist($reference_filepath);
			ksort($uploaded_files);
			ksort($reference_files);

			switch ($type)
			{
				case 'language':
					// The uploaded files array has keys prefixed with the upload path of the contribution
					// Have it stored in the variable so we can work with it
					$uploaded_files_prefix = explode('/', key($uploaded_files));
					$iso_code = $uploaded_files_prefix[2];
					$uploaded_files_prefix = $this->unzip_dir . $uploaded_files_prefix[0];

					// This goes directly to the root of the uploaded language pack, like /upload_path/language/cs/
					$uploaded_lang_root = $uploaded_files_prefix . '/language/' . $iso_code . '/';
				
					// Just perform a basic check if the common file is there
					if (!is_file($uploaded_lang_root . 'common.php'))
					{
						return array(phpbb::$user->lang('NO_TRANSLATION'));
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
										$error[] = sprintf(phpbb::$user->lang('MISSING_FILE'), str_replace('/en/', '/' . $iso_code . '/', $dir. $file)); // report a missing file
										$exists = false;
									}
								}
							
								// If the file is a php file and actually exists, no point in checking keys in a nonexistent one
								if ($ext == 'php' && $exists)
								{
									$missing_keys[$dir . $file] = $this->check_missing_keys($reference_filepath . '' .	$dir . $file, $uploaded_file_path);
								}

								if (!in_array(strtoupper($file), $this->ignore_files) && !in_array($ext, array('php', 'txt')) && is_file($uploaded_file_path))
								{
									// remove any files that aren't in the above stated extension list, this will delete index.htm files and LICENSE files
									unlink($uploaded_file_path);
								}			

								// In the last step we have removed the license and index files if there were any. We'll just put a new one instead
								$this->add_license_files($uploaded_lang_root . '/LICENSE', $reference_filepath);
								$this->add_htm_files($uploaded_lang_root, $reference_filepath);
							}
						}
					}
				
					if (sizeof($missing_keys))
					{
						foreach ($missing_keys as $file => $keys)
						{
							if (sizeof($keys))
							{
								$error[] = sprintf(phpbb::$user->lang['MISSING_KEYS'], $file, implode('<br />', $keys));
							}
						}
					}
		
					if (sizeof($error))
					{
						return $error;
					}
				
				break;
			
				case 'prosilver':
				case 'subsilver2':
					// just let them go through atm...
				break;
			}
		
		}
		
		$this->replace_zip(); // we have made changes to the package, so replace the original zip file
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
		fwrite($res, file_get_contents($reference_path . '/docs/COPYING'));
		fclose($res);
 	}
 	
 	private function add_htm_files($lang_root_path, $reference_path)
 	{
 		$htm_files = array('', 'acp/', 'mods/');
 		
 		if (!is_dir($lang_root_path . 'mods'))
 		{
 			mkdir($lang_root_path . 'mods');
 			phpbb_chmod($lang_root_path . 'mods', CHMOD_READ | CHMOD_WRITE);
 		}
 		
 		foreach ($htm_files as $htm_file)
 		{
 			$res = fopen($lang_root_path . $htm_file . 'index.htm', 'w');
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
?>
