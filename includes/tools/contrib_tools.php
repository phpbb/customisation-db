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

use \Phpbb\Epv\Output\Output;
use \Phpbb\Epv\Output\HtmlOutput;
use \Phpbb\Epv\Tests\TestRunner;

@set_time_limit(1200);

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
	public $unzip_dir;

	/**
	* The original file
	*
	* @var mixed
	*/
	public $original_zip;

	/**
	* Directory name we want to use when rezipping
	*
	* @var mixed
	*/
	public $new_dir_name;

	/**
	* Error array (if any)
	*
	* @var mixed
	*/
	public $error = array();

	/**
	* Contains the md5 hash of the zip package
	*
	* @var string
	*/
	public $md5_hash = '';

	/**
	* File size of the zip package
	*
	* @var int
	*/
	public $filesize = 0;

	/** @var string */
	protected $phpbb_root_path;

	/** @var  string */
	protected $ext_root_path;

	/**
	* Constructor.
	*/
	public function __construct()
	{
		$this->phpbb_root_path = \phpbb::$root_path;
		$this->ext_root_path = \titania::$root_path;
	}

	/**
	* Find the root directory of the mod package
	*
	* Ignore the variables other than $directory and $find, this is a recursive function
	*
	* @param string $directory The directory to search in (false to use $this->unzip_dir)
	* @param array $find An array of items to search for.  Root array is to search for any one of the files, child arrays hold the things we will check for in each
	* array(
	*	array( // Search for an install .xml file
	*		'install',
	* 		'.xml',
	* 	),
	* 	array( // Search for a directory with template in it
	* 		'template',
	* 		'is_directory',
	* 	),
	* 	array( // Search for a directory exactly named template (this also works for non-directories if you don't specify 'is_directory')
	* 		'template',
	* 		'is_directory',
	* 		'is_exactly',
	* 	),
	* 	'style.cfg', // Search for style.cfg file
	* )
	*/
	public function find_root($directory = false, $find = array(array('install', '.xml')), $sub_dir = '', $cnt = 0)
	{
		$directory = ($directory === false) ? $this->unzip_dir : $directory;
		$possible_match = false;

		// Ok, have to draw the line somewhere; 50 sub directories is insane
		if ($cnt > 50)
		{
			$this->remove_temp_files();

			trigger_error('SUBDIRECTORY_LIMIT');
		}

		if (!is_dir($directory . $sub_dir))
		{
			return false;
		}

        foreach (scandir($directory . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			// Search for the files
			if ($find == '*')
			{
				// * is used to search for a single main parent directory -- if there's more than one, we return false
				if ($possible_match !== false)
				{
					return false;
				}

				if (is_dir($directory . $sub_dir . '/' . $item))
				{
					$possible_match = $item;
				}
			}
			else if (!is_array($find))
			{
				if (strpos($item, $find) !== false)
				{
					return $sub_dir;
				}
			}
			else
			{
				foreach ($find as $file_search)
				{
					if (!is_array($file_search))
					{
						if (strpos($item, $file_search) !== false)
						{
							return $sub_dir;
						}
					}
					else
					{
						$match = 0;

						// Directory check
						if (in_array('is_directory', $file_search))
						{
							if (!is_dir($directory . $sub_dir . '/' . $item))
							{
								continue;
							}
						}

						// Search each subset to make sure they all exist
						foreach ($file_search as $check)
						{
							// Ignore the special attributes that can be sent
							if (in_array($check, array('is_directory', 'is_exactly')))
							{
								continue;
							}

							if (in_array('is_exactly', $file_search) && $item == $check)
							{
								$match++;
							}
							else if (!in_array('is_exactly', $file_search) && stripos($item, $check) !== false)
							{
								$match++;
							}
							else
							{
								break;
							}
						}

						// Do not include special parameters in the count for matches
						$specials = array('is_directory', 'is_exactly');
						foreach ($specials as $special)
						{
							if (in_array($special, $file_search))
							{
								$match++;
							}
						}

						if ($match == sizeof($file_search))
						{
							return $sub_dir;
						}
					}
				}
			}
        }

		// We managed to find a single parent directory
		if ($find == '*')
		{
			return $possible_match;
		}

        // We failed the scan of this directory, let's try some children
        foreach (scandir($directory . $sub_dir) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($directory . $sub_dir . $item))
			{
				if (($root_dir = $this->find_root($directory, $find, $sub_dir . $item . '/', ($cnt + 1))) !== false)
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

    	phpbb::_include('functions_compress', false, 'compress_zip');

		$zip = new compress_zip('w', $this->original_zip);

		$this->_replace_zip($zip);

		$zip->close();

		// Calculate the md5
		$this->md5_hash = md5_file($this->original_zip);

		// Get the new file size
		$this->filesize = @filesize($this->original_zip);
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
				$new_dir_name = (!preg_match('#'.$this->new_dir_name.'#', $sub_dir)) ? $this->new_dir_name . '/' : '';
				$zip->add_custom_file($this->unzip_dir . $sub_dir . $item, $new_dir_name . $sub_dir . $item);
			}
		}
    }


	/**
	* Remove the temporary files we created
	*/
	public function remove_temp_files()
	{
		if ($this->unzip_dir)
		{
			return $this->rmdir_recursive($this->unzip_dir);
		}

		return true;
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
	* Run extension prevalidator.
	*
	* @param string $directory	Directory on which to run EPV on
	* @return string
	*/
	public function epv($directory)
	{
		$int_output = new HtmlOutput(HtmlOutput::TYPE_BBCODE);
		$output = new Output($int_output, false);
		$runner = new TestRunner($output, $directory, false, true);
		$runner->runTests();

		// Write a empty line
		$output->writeLn('');

		$found_msg = ' ';
		$found_msg .= 'Fatal: ' . $output->getMessageCount(Output::FATAL);
		$found_msg .= ', Error: ' . $output->getMessageCount(Output::ERROR);
		$found_msg .= ', Warning: ' . $output->getMessageCount(Output::WARNING);
		$found_msg .= ', Notice: ' . $output->getMessageCount(Output::NOTICE);
		$found_msg .= ' ';

		if ($output->getMessageCount(Output::FATAL) > 0 || $output->getMessageCount(Output::ERROR) > 0 || $output->getMessageCount(Output::WARNING) > 0)
		{
			$output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
			$output->writeln('<fatal> Validation: FAILED' . str_repeat(' ', strlen($found_msg) - 19) . '</fatal>');
			$output->writeln('<fatal>' . $found_msg . '</fatal>');
			$output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
			$output->writeln('');
		}
		else
		{
			$output->writeln('<success>PASSED: ' . $found_msg . '</success>');
		}

		$output->writeln("<info>Test results for extension:</info>");

		foreach ($output->getMessages() as $msg)
		{
			$output->writeln((string)$msg);
		}

		if (sizeof($output->getMessages()) == 0)
		{
			$output->writeln("<success>No issues found </success>");
		}

		return $int_output->getBuffer();
	}

	/**
	* Prepare the files to test automod with
	*
	* @param string $version the full phpBB version number.  Ex: 2.0.23, 3.0.1, 3.0.7-pl1
	*/
	public function automod_phpbb_files($version)
	{
		$version = preg_replace('#[^a-zA-Z0-9\.\-]+#', '', $version);

		$phpbb_root = $this->ext_root_path . 'store/extracted/' . $version . '/';
		$phpbb_package = $this->ext_root_path . 'includes/phpbb_packages/phpBB-' . $version . '.zip';

		if (!file_exists($phpbb_root . 'common.php'))
		{
			if (!file_exists($phpbb_package))
			{
				$this->error[] = sprintf(phpbb::$user->lang['FILE_NOT_EXIST'], $phpbb_package);
				return false;
			}

			// Unzip to our temp directory
			$this->extract($phpbb_package, $phpbb_root);

			// Find the phpBB root
			$package_root = $this->find_root($phpbb_root, 'common.php');

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
				foreach (scandir($phpbb_root) as $item)
				{
		            if ($item == '.' || $item == '..' || ($item == $sub_dir && is_dir($phpbb_root . $item)))
					{
						continue;
					}

					if (is_dir($phpbb_root . $item))
					{
						$this->rmdir_recursive($phpbb_root . $item . '/');
					}
					else
					{
						@unlink($phpbb_root . $item);
					}
				}

				// Now move the package root to our unzip directory
				$this->mvdir_recursive($phpbb_root . $package_root, $phpbb_root);

				// Now remove the old directory
				$this->rmdir_recursive($phpbb_root . $sub_dir);
			}
		}

		return $phpbb_root;
	}

	/**
	* Automod test
	* TY AJD
	*
	* @param string $phpbb_path Path to phpBB files we run the test on
	* @param string $details Will hold the details of the mod
	* @param string $results Will hold the results for output
	* @param string $bbcode_results Will hold the results for storage
	* @return bool true on success, false on failure
	*/
	public function automod($phpbb_path, &$details, &$results, &$bbcode_results)
	{
		phpbb::_include('functions_transfer', false, 'transfer');
		phpbb::_include('functions_admin', 'recalc_nested_sets');
		titania::_include('library/automod/acp_mods', false, 'acp_mods');
		titania::_include('library/automod/editor', false, 'editor');
		titania::_include('library/automod/mod_parser', false, 'parser');
		titania::_include('library/automod/functions_mods', 'test_ftp_connection');

		titania::add_lang('automod');

		// Find the main modx file
		$modx_root = $this->find_root();

		if ($modx_root === false)
		{
			titania::add_lang('contributions');

			$this->error[] = phpbb::$user->lang['COULD_NOT_FIND_ROOT'];
			return false;
		}
		$modx_root = $this->unzip_dir . $modx_root;

		$modx_file = false;
		if (file_exists($modx_root . 'install.xml'))
		{
			$modx_file = $modx_root . 'install.xml';
		}
		else
		{
			// Find the first item with install in the name
			foreach (scandir($modx_root) as $item)
			{
		       if (strpos($item, 'install') !== false && strpos($item, '.xml'))
		       {
				   $modx_file = $modx_root . $item;
				   break;
		       }
			}
		}

		if (!$modx_file)
		{
			titania::add_lang('contributions');

			$this->error[] = phpbb::$user->lang['COULD_NOT_FIND_ROOT'];
			return false;
		}

		// HAX
		global $phpbb_root_path;
		$phpbb_root_path = $phpbb_path;

		// The real stuff
		$acp_mods = new acp_mods;
		$acp_mods->mods_dir = titania::$config->contrib_temp_path;
		$acp_mods->mod_root = $modx_root;
		$editor = new editor_direct;
		$details = $acp_mods->mod_details($modx_file, false);
		$actions = $acp_mods->mod_actions($modx_file);
		$installed = $acp_mods->process_edits($editor, $actions, $details, false, true, false);

		// Reverse HAX
		$phpbb_root_path = $this->phpbb_root_path;

		phpbb::$template->set_filenames(array(
			'automod'			=> 'contributions/automod.html',
			'automod_bbcode'	=> 'contributions/automod_bbcode.html',
		));

		phpbb::$template->assign_var('S_AUTOMOD_SUCCESS', $installed);

		$results = phpbb::$template->assign_display('automod');
		$bbcode_results = phpbb::$template->assign_display('automod_bbcode');

		return $installed;
	}

	public function extract($archive, $target, $check_minimum_directory = true)
	{
		if (!file_exists($archive))
		{
			trigger_error(sprintf(phpbb::$user->lang['FILE_NOT_EXIST'], basename($archive)));
		}

		// Some simple file protection to prevent getting out of the titania root
		if ($check_minimum_directory)
		{
			if (!$this->check_filesystem_path($archive))
			{
				return false;
			}
			if (!$this->check_filesystem_path($target))
			{
				return false;
			}
		}

		// Clear out old stuff if there is anything here...
		$this->rmdir_recursive($target);

		// Using the phpBB ezcomponents loader
		titania::_include('library/ezcomponents/loader', false, 'phpbb_ezcomponents_loader');
		phpbb_ezcomponents_loader::load_component('archive');

		// ezcomponents archive handler
		$ezcarchive = ezcArchive::open($archive, ezcArchive::ZIP);
		$ezcarchive->extract($target);
		$ezcarchive->close();
	}

	/**
	* Move a directory and children
	*
	* @param mixed $source
	* @param mixed $destination
	*/
	public function mvdir_recursive($source, $destination, $check_minimum_directory = true)
	{
		$source = (substr($source, -1) == '/') ? $source : $source . '/';
		$destination = (substr($destination, -1) == '/') ? $destination : $destination . '/';

		// Some simple file protection to prevent getting out of the titania root
		if ($check_minimum_directory)
		{
			if (!$this->check_filesystem_path($source))
			{
				return false;
			}
			if (!$this->check_filesystem_path($destination))
			{
				return false;
			}
		}

		if (strpos($destination, $source) !== false)
		{
			// Woh nelly, this will loop infinitely without some special care!

			$temp_destination = substr($source, 0, strrpos($source, '/', -2)) . '/temp/';
			$i = 1;
			while (file_exists($temp_destination . $i))
			{
				$i++;
			}
			$temp_destination .= $i;

			// Move to temp directory
			$this->mvdir_recursive($source, $temp_destination, $check_minimum_directory);

			// Remove source directory
			$this->rmdir_recursive($source, $check_minimum_directory);

			// Move from temp directory to the final directory
			$this->mvdir_recursive($temp_destination, $destination, $check_minimum_directory);

			// Remove temp directory
			$this->rmdir_recursive($temp_destination, $check_minimum_directory);

			return;
		}

		if (!is_dir($source))
		{
			return false;
		}

		if (!is_dir($destination))
		{
			$this->mkdir_recursive($destination, $check_minimum_directory);
		}

		foreach (scandir($source) as $item)
		{
            if ($item == '.' || $item == '..')
			{
				continue;
			}

			if (is_dir($source . $item))
			{
				$this->mvdir_recursive($source . $item . '/', $destination . $item . '/', $check_minimum_directory);
			}
			else if (is_file($source . $item))
			{
				@copy($source . $item, $destination . $item);
				phpbb_chmod($destination . $item, CHMOD_READ | CHMOD_WRITE);
			}
		}

		return true;
	}

	/**
	* Make a directory recursively (from functions_compress)
	*
	* @param string $target_filename The target directory we wish to have
	*/
	public function mkdir_recursive($target_filename, $check_minimum_directory = true)
	{
		$target_filename = (substr($target_filename, -1) == '/') ? $target_filename : $target_filename . '/';

		// Some simple file protection to prevent getting out of the titania root
		if ($check_minimum_directory)
		{
			if (!$this->check_filesystem_path($target_filename))
			{
				return false;
			}
		}

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

		return true;
	}

	/**
	* Remove a directory (and any children) from Automod
	*
	* @param string $target_filename The target directory we wish to remove
	*/
	public function rmdir_recursive($target_filename, $check_minimum_directory = true)
	{
		$target_filename = (substr($target_filename, -1) == '/') ? $target_filename : $target_filename . '/';

		// Some simple file protection to prevent getting out of the titania root
		if ($check_minimum_directory)
		{
			if (!$this->check_filesystem_path($target_filename))
			{
				return false;
			}
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
				$this->rmdir_recursive($target_filename . $item . '/', ($check_minimum_directory) ? true : false);
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
	public function get_remote_file($host, $directory, $filename, $port = 80, $timeout = 10)
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

	/**
	* Check a filesystem path to make sure it is within a minimum directory
	*
	* @param string $directory
	* @param mixed $minimum_directory if false, we check the store, upload path, and temp path
	*/
	public function check_filesystem_path($directory, $minimum_directory = false)
	{
		// If minimum directory is false, we check the store, upload path, and temp path
		if ($minimum_directory === false)
		{
			return ($this->check_filesystem_path($directory, $this->ext_root_path . 'store/') || $this->check_filesystem_path($directory, titania::$config->upload_path) || $this->check_filesystem_path($directory, titania::$config->contrib_temp_path) || $this->check_filesystem_path($directory, $this->ext_root_path . 'includes/')) ? true : false;
		}

		// Find the directory (ignore files and roll back through non-existant directories)
		$directory = substr($directory, 0, strrpos($directory, '/'));
		while (!file_exists($directory))
		{
			$directory = substr($directory, 0, strrpos($directory, '/', -1));
		}

		$minimum_directory = phpbb_realpath($minimum_directory);
		$directory = phpbb_realpath($directory);

		// If the path of the directory doesn't start the same as the minimum directory then it's not within the directory
		if (strpos($directory, $minimum_directory) !== 0)
		{
			return false;
		}

		return true;
	}
}
