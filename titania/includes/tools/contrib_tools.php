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
	* @param string $new_dir_name name of the directory you want to use in the zip package (leave blank if the initial steps have been run already)
	*/
	public function __construct($original_zip, $new_dir_name = '')
	{
		$this->original_zip = $original_zip;

		if ($new_dir_name)
		{
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
	}

	/**
	* Clean crap out of the directories that should not be in packages
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

		// Array of the things we want to remove
		$dirs_to_remove = array('.git', '.svn', 'CVS', '__MACOSX');
		$files_to_remove = array('desktop.ini', 'Thumbs.db', '.DS_Store', '.gitmodules', '.gitignore');

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

			if (in_array($item, $dirs_to_remove) && is_dir($this->unzip_dir . $sub_dir . $item))
			{
				$this->rmdir_recursive($this->unzip_dir . $sub_dir . $item . '/');
			}
			else if (in_array($item, $files_to_remove) && is_file($this->unzip_dir . $sub_dir . $item))
			{
				@unlink($this->unzip_dir . $sub_dir . $item);
			}
			else if (is_dir($this->unzip_dir . $sub_dir . $item))
			{
				$this->clean_package($sub_dir. $item . '/', ($cnt + 1));
			}
        }

        return true;
	}

	/**
	* Find the root directory of the mod package and place it appropriately so we have package.zip/mod_name_1_0_0/(install files)
	* After finding the root, moves it to be in the correct path we need
	*
	* @todo This only handles mod packages (containing an install xml file)
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
		if ($package_root != '')
		{
			// Find the main subdirectory off the unzip dir
			$sub_dir = $package_root;
			if (strpos($sub_dir, '/') !== false)
			{
				$sub_dir = substr($sub_dir, 0, strpos($sub_dir, '/'));
			}

			// First remove everything but the subdirectory that the package root is in
			foreach (scandir($this->unzip_dir) as $item)
			{
	            if ($item == '.' || $item == '..' || ($item == $sub_dir && is_dir($this->unzip_dir . $item)))
				{
					continue;
				}

				if (is_dir($this->unzip_dir . $item))
				{
					$this->rmdir_recursive($this->unzip_dir . $item . '/');
				}
				else
				{
					@unlink($this->unzip_dir . $item);
				}
			}

			// Now move the package root to our unzip directory
			$this->mvdir_recursive($this->unzip_dir . $package_root, $this->unzip_dir);

			// Now remove the old directory
			$this->rmdir_recursive($this->unzip_dir . $sub_dir . '/');
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
				return $sub_dir;
			}
        }

        // We failed the scan of this directory, let's try some children
        foreach (scandir($this->unzip_dir . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($this->unzip_dir . $sub_dir . $item))
			{
				if (($root_dir = $this->find_root($sub_dir . $item . '/', ($cnt + 1))) !== false)
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
    private function _replace_zip(&$zip, $sub_dir = '')
    {
    	if (!is_dir($this->unzip_dir . $sub_dir))
    	{
			return;
		}

		foreach (scandir($this->unzip_dir . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($this->unzip_dir . $sub_dir . $item))
			{
				$this->_replace_zip($zip, $sub_dir . $item . '/');
			}
			else
			{
				$zip->add_custom_file($this->unzip_dir . $sub_dir . $item, $this->new_dir_name . '/' . $sub_dir . $item);
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
	* Send the test to an MPV server and return the results
	*
	* @return False on error (check $this->error) results on success
	*/
	public function mpv($download_location)
	{
		$server_list = titania::$config->mpv_server_list;

		$server = $server_list[array_rand($server_list)];

		$mpv_result = $this->get_remote_file($server['host'], $server['directory'], $server['file'] . '?titania-' . $download_location);

		if ($mpv_result === false)
		{
			$this->error[] = phpbb::$user->lang['MPV_TEST_FAILED'];
			return false;
		}
		else
		{
			$mpv_result = str_replace('<br />', "\n", $mpv_result);
			set_var($mpv_result, $mpv_result, 'string', true);
			$mpv_result = utf8_normalize_nfc($mpv_result);

			return $mpv_result;
		}
	}

	/**
	* Move a directory and children
	*
	* @param mixed $source
	* @param mixed $destination
	*/
	function mvdir_recursive($source, $destination)
	{
		if (!is_dir($source))
		{
			return false;
		}

		if (!is_dir($destination))
		{
			$this->mkdir_recursive($destination);
		}

		foreach (scandir($source) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($source . $item))
			{
				$this->mvdir_recursive($source . $item . '/', $destination . $item . '/');
			}
			else if (is_file($source . $item))
			{
				@copy($source . $item, $destination . $item);
				phpbb_chmod($destination . $item, CHMOD_READ | CHMOD_WRITE);
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
					@mkdir($str, 0777);
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

		if (!is_dir($target_filename))
		{
			return;
		}

        foreach (scandir($target_filename) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($target_filename . $item))
			{
				$this->rmdir_recursive($target_filename . $item . '/');
			}
			else
			{
				@unlink($target_filename . $item);
			}
        }

		return @rmdir($target_filename);
	}

	/**
	* Retrieve contents from remotely stored file (mostly copied from functions_admin.php)
	* Modified to ignore errors
	*/
	function get_remote_file($host, $directory, $filename, $port = 80, $timeout = 10)
	{
		$errstr = '';
		if ($fsock = @fsockopen($host, $port, $errno, $errstr, $timeout))
		{
			@fputs($fsock, "GET $directory/$filename HTTP/1.1\r\n");
			@fputs($fsock, "HOST: $host\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");

			$file_info = '';
			$get_info = false;

			while (!@feof($fsock))
			{
				if ($get_info)
				{
					$file_info .= @fread($fsock, 1024);
				}
				else
				{
					$line = @fgets($fsock, 1024);
					if ($line == "\r\n")
					{
						$get_info = true;
					}
					else if (stripos($line, '404 not found') !== false)
					{
						$this->error[] = '404';
						return false;
					}
				}
			}
			@fclose($fsock);
		}
		else
		{
			$this->error[] = $errstr;
			return false;
		}

		return $file_info;
	}
}
