<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
 * Class that handles some contrib packaging stuff
 *
 * @package Titania
 */
class titania_contrib_tools
{
	/**
	* Directories we unzip the files to for testing/performing actions
	*
	* @var mixed
	*/
	private $unzip_dir;

	/**
	* The original file
	*
	* @var mixed
	*/
	private $original_zip;

	/**
	* Directory name we want to use when rezipping
	*
	* @var mixed
	*/
	private $new_dir_name;

	/**
	* Error array (if any)
	*
	* @var mixed
	*/
	public $error = array();

	/**
	* @param string $zip Full path to the zip package
	* @param string $new_dir_name name of the directory you want to use in the zip package to
	*/
	public function __construct($zip, $new_dir_name = '')
	{
		$this->original_zip = $zip;
		$this->new_dir_name = utf8_basename($new_dir_name);
		$this->unzip_dir = titania::$config->contrib_temp_path . $this->new_dir_name . '/';

		// Clear out old stuff if there is anything here...
		$this->rmdir_recursive($this->unzip_dir);

		phpbb::_include('functions_compress', false, 'compress_zip');

		// Unzip to our temp directory
		$zip = new compress_zip('r', $this->original_zip);
		$zip->extract($this->unzip_dir);
		$zip->close();
	}

	/**
	* Clean crap out of the directories that should not be in mod packages
	*
	* Ignore the variables, don't send anything, this is a recursive function
	*/
	public function clean_package($sub_dir = '', $cnt = 0)
	{
		// Ok, have to draw the line somewhere; 50 sub directories is insane
		if ($cnt > 50)
		{
			$this->remove_temp_files();

			trigger_error('SUBDIRECTORY_LIMIT');
		}

		// Replace any leading slash
		$sub_dir = (isset($sub_dir[0]) && $sub_dir[0] == '/') ? substr($sub_dir, 1) : $sub_dir;

		// Array of the things we want to remove
		$dirs_to_remove = array('.git', '.svn', 'CVS', '.settings');
		$files_to_remove = array('desktop.ini', 'Thumbs.db', '.DS_Store', '.project', '.buildpath', '.gitmodules', '.gitignore');

		if (!is_dir($this->unzip_dir . $sub_dir))
		{
				return true;
		}

        foreach (scandir($this->unzip_dir . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (in_array($item, $dirs_to_remove))
			{
				$this->rmdir_recursive($this->unzip_dir . $sub_dir . '/' . $item);
			}
			else if (in_array($item, $files_to_remove))
			{
				@unlink($this->unzip_dir . $sub_dir . '/' . $item);
			}
			else if (is_dir($this->unzip_dir . $sub_dir . '/' . $item))
			{
				if ($this->clean_package($sub_dir . '/' . $item, ($cnt + 1)) === false)
				{
					return false;
				}
			}
        }

        return true;
	}

	/**
	* Find the root directory of the mod package and place it appropriately so we have package.zip/mod_name_1_0_0/(install files)
	* After finding the root, moves it to be in the correct path we need
	*/
	public function restore_root()
	{
		// Find the root directory
		$package_root = $this->find_root();

		if ($package_root === false)
		{
			$this->remove_temp_files();

			$this->error[] = phpbb::$user->lang['COULD_NOT_FIND_ROOT'];
			return false;
		}

		// Move it to the correct location
		if ($package_root != $this->unzip_dir)
		{
			// Find the main subdirectory off the unzip dir
			$sub_dir = str_replace($this->unzip_dir, '', $package_root);
			$sub_dir = (isset($sub_dir[0]) && $sub_dir[0] == '/') ? substr($sub_dir, 1) : $sub_dir; // Remove leading slash if any
			if (strpos($sub_dir, '/') !== false)
			{
				$sub_dir = substr($sub_dir, (strpos($sub_dir, '/') - 1));
			}

			if (!is_dir($this->unzip_dir))
			{
				return false;
			}

			// First remove everything but the subdirectory that the package root is in
			foreach (scandir($this->unzip_dir) as $item)
			{
	            if ($item == '.' || $item == '..' || $item == $sub_dir)
				{
					continue;
				}

				if (is_dir($this->unzip_dir . $sub_dir . '/' . $item))
				{
					$this->rmdir_recursive($this->unzip_dir . $sub_dir . '/' . $item);
				}
				else
				{
					@unlink($this->unzip_dir . $sub_dir . '/' . $item);
				}
			}

			// Now move the package root to our unzip directory
			$this->mvdir_recursive($package_root, $this->unzip_dir);

			// Now remove the old directory
			$this->rmdir_recursive($this->unzip_dir . $sub_dir);
		}

		return true;
	}

	/**
	* Find the root directory of the mod package
	*
	* Ignore the variables, don't send anything, this is a recursive function
	*/
	public function find_root($sub_dir = '', $cnt = 0)
	{
		// Ok, have to draw the line somewhere; 50 sub directories is insane
		if ($cnt > 50)
		{
			$this->remove_temp_files();

			trigger_error('SUBDIRECTORY_LIMIT');
		}

		// Replace any leading slash
		$sub_dir = (isset($sub_dir[0]) && $sub_dir[0] == '/') ? substr($sub_dir, 1) : $sub_dir;
		if (!is_dir($this->unzip_dir . $sub_dir))
		{
			return false;
		}

        foreach (scandir($this->unzip_dir . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (strpos($item, 'install') !== false && substr($item, -4) == '.xml')
			{
				// Found an install xml file
				return $this->unzip_dir . $sub_dir;
			}
        }

        // We failed the scan of this directory, let's try some children
        foreach (scandir($this->unzip_dir . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($this->unzip_dir . $sub_dir . '/' . $item))
			{
				$root_dir = $this->find_root($sub_dir . '/' . $item, ($cnt + 1));
				if ($root_dir !== false)
				{
					return $root_dir;
				}
			}
        }

        return false;
    }

    /**
    * Replace the original zip with the package we generated
    */
    public function replace_zip()
    {
    	@unlink($this->original_zip);

		$zip = new compress_zip('w', $this->original_zip);

		$this->_replace_zip($zip);

		$zip->close();
    }

    /**
    * Helper to add the files in the new zip package
    */
    private function _replace_zip($zip, $sub_dir = '')
    {
		// Replace any leading slash
		$sub_dir = (isset($sub_dir[0]) && $sub_dir[0] == '/') ? substr($sub_dir, 1) : $sub_dir;

		foreach (scandir($this->unzip_dir . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($this->unzip_dir . $sub_dir . '/' . $item))
			{
				$this->_replace_zip($zip, $sub_dir . '/' . $item);
			}
			else
			{
				$zip->add_custom_file($this->unzip_dir . $sub_dir . '/' . $item, $this->new_dir_name . '/' . $sub_dir . '/' . $item);
			}
		}
    }


	/**
	* Remove the temporary files we created
	*/
	public function remove_temp_files()
	{
		return $this->rmdir_recursive($this->unzip_dir);
	}

	/**
	* Move a directory and children
	*
	* @param mixed $source
	* @param mixed $destination
	*/
	function mvdir_recursive($source, $destination)
	{
		if (!is_dir($destination) && is_dir($source))
		{
			$this->mkdir_recursive($destination);
		}

		if (!is_dir($source))
		{
			return false;
		}

		foreach (scandir($source) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($source . '/' . $item))
			{
				$this->mvdir_recursive($source . '/' . $item, $destination . '/' . $item);
			}
			else if (is_file($source . '/' . $item))
			{
				@copy($source . '/' . $item, $destination . '/' . $item);
				phpbb_chmod($destination . '/' . $item, CHMOD_READ | CHMOD_WRITE);
			}
		}
	}

	/**
	* Make a directory recursively (from functions_compress)
	*
	* @param string $target_filename The target directory we wish to have
	*/
	public function mkdir_recursive($target_filename)
	{
		if (!is_dir($target_filename))
		{
			$str = '';
			$folders = explode('/', $target_filename);

			// Create and folders and subfolders if they do not exist
			foreach ($folders as $folder)
			{
				$folder = trim($folder);
				if (!$folder)
				{
					continue;
				}

				$str = (!empty($str)) ? $str . '/' . $folder : $folder;
				if (!is_dir($str))
				{
					if (!@mkdir($str, 0777))
					{
						trigger_error("Could not create directory $folder");
					}
					phpbb_chmod($str, CHMOD_READ | CHMOD_WRITE);
				}
			}
		}
	}

	/**
	* Remove a directory (and any children) from Automod
	*
	* @param string $target_filename The target directory we wish to remove
	*/
	public function rmdir_recursive($target_filename)
	{
		// Prevent getting out of our temp directory
		if (strpos($target_filename, titania::$config->contrib_temp_path) !== 0 || strpos(str_replace(titania::$config->contrib_temp_path, '', $target_filename), '..') !== false)
		{
			return false;
		}

		if (!file_exists($target_filename))
		{
			return true;
		}

		if (!is_dir($target_filename) && is_file($target_filename))
		{
			phpbb_chmod($target_filename, CHMOD_ALL);
			return @unlink($target_filename);
		}

        foreach (scandir($target_filename) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}
            if (!$this->rmdir_recursive($target_filename . "/" . $item))
			{
				phpbb_chmod($target_filename . "/" . $item, CHMOD_ALL);
                if (!$this->rmdir_recursive($target_filename . "/" . $item))
				{
					return false;
				}
            }
        }

		return @rmdir($target_filename);
	}
}