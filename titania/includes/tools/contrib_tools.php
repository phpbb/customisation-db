<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

@set_time_limit(1200);

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
	* @param string $zip Full path to the zip package
	* @param string $new_dir_name name of the directory you want to use in the zip package (leave blank if the initial steps have been run already)
	*/
	public function __construct($original_zip, $new_dir_name = '')
	{
		$this->original_zip = $original_zip;

		// Calculate the md5
		$this->md5_hash = md5_file($this->original_zip);

		if ($new_dir_name)
		{
			$this->new_dir_name = utf8_basename($new_dir_name);
			$this->unzip_dir = titania::$config->contrib_temp_path . $this->new_dir_name . '/';

			// Unzippage
			$this->extract($this->original_zip, $this->unzip_dir);
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
	* Place the root directory appropriately so we have package.zip/mod_name_1_0_0/(install files)
	*
	* @param string $package_root the root path ($this->find_root())
	*/
	public function restore_root($package_root)
	{
		if ($package_root === false)
		{
			$this->remove_temp_files();

			titania::add_lang('contributions');
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
			if (!is_array($find))
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
							else if (!in_array('is_exactly', $file_search) && strpos($item, $check) !== false)
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
	* Prepare the files to test automod with
	*
	* @param string $version the full phpBB version number.  Ex: 2.0.23, 3.0.1, 3.0.7-pl1
	*/
	public function automod_phpbb_files($version)
	{
		$version = preg_replace('#[^a-zA-Z0-9\.\-]+#', '', $version);

		$phpbb_root = TITANIA_ROOT . 'store/extracted/' . $version . '/';
		$phpbb_package = TITANIA_ROOT . 'includes/phpbb_packages/phpBB-' . $version . '.zip';

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
		$editor = new editor_direct;
		$details = $acp_mods->mod_details($modx_file, false);
		$actions = $acp_mods->mod_actions($modx_file);
		$installed = $acp_mods->process_edits($editor, $actions, $details, false, true, false);

		// Reverse HAX
		$phpbb_root_path = PHPBB_ROOT_PATH;

		phpbb::$template->set_filenames(array(
			'automod'			=> 'contributions/automod.html',
			'automod_bbcode'	=> 'contributions/automod_bbcode.html',
		));

		phpbb::$template->assign_var('S_AUTOMOD_SUCCESS', $installed);

		$results = phpbb::$template->assign_display('automod');
		$bbcode_results = phpbb::$template->assign_display('automod_bbcode');

		return $installed;
	}

	/**
	* Copy the modx install file to the storage path (and edit it to use our xsl file)
	*
	* @param string $to Place to copy the modx file to
	*/
	public function copy_modx_install($to)
	{
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

		// Open the file
		if (!($file_contents = file_get_contents($modx_file)))
		{
			titania::add_lang('contributions');

			$this->error[] = phpbb::$user->lang['COULD_NOT_OPEN_MODX'];
			return false;
		}

		// Find the ModX version
		$version = array();
		preg_match('#<mod (.*)xmlns="(.*)"(.*)>#', $file_contents, $version);
		preg_match('#([0-9]\.(.*))\.xsd#', $version[2], $version);
		$modx_version = preg_replace('#[^a-zA-Z0-9\.]#', '', $version[1]);

		// Replace the stylesheet path
		$file_contents = preg_replace('#<\?xml\-stylesheet (.*)\?>#', '<?xml-stylesheet type="text/xsl" href="./modx/' . $modx_version . '.xsl" ?>', $file_contents);

		// Replace the link-group
		$file_contents = preg_replace('#<link\-group>(.*)</link\-group>#', '', $file_contents);

		// Output time
		file_put_contents($to, $file_contents);
		phpbb_chmod($to, CHMOD_READ | CHMOD_WRITE);

		return true;
	}

	/**
	* Install a style on the demo board.
	*
	* @param string $phpbb_root_path
	* @param mixed contrib object
	*/
	public function install_demo_style($phpbb_root_path, $contrib)
	{
		phpbb::$user->add_lang('acp/styles');

		if ($phpbb_root_path[strlen($phpbb_root_path) - 1] != '/')
		{
			$phpbb_root_path .= '/';
		}

		if (!is_dir($phpbb_root_path) || !file_exists($phpbb_root_path . 'config.' . PHP_EXT))
		{
			$this->error[] = 'PATH_INVALID';

			return false;
		}

		include ($phpbb_root_path . 'config.' . PHP_EXT);

		$sql_db = (!empty($dbms)) ? 'dbal_' . basename($dbms) : 'dbal';

		// Is this DBAL loaded?
		phpbb::_include('db/' . $dbms, false, $sql_db);

		// Instantiate DBAL class
		$db = new $sql_db();

		// Connect to demo board DB
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport);

		// We do not need this any longer, unset for safety purposes
		unset($dbpasswd);

		if (empty($this->unzip_dir))
		{
			// Extract zip.
			$this->unzip_dir = titania::$config->contrib_temp_path . basename($this->original_zip, 'zip') . '/';

			// Clear out old stuff if there is anything here...
			$this->rmdir_recursive($this->unzip_dir);

			// Unzip to our temp directory
			$this->extract($this->original_zip, $this->unzip_dir);
		}

		$package_root = $this->find_root(false, 'style.cfg');
		$stylecfg = parse_cfg_file($this->unzip_dir . $package_root . '/style.cfg');
		$style_root = $phpbb_root_path . 'styles/' . basename(strtolower(str_replace(' ', '_', $stylecfg['name']))) . '_' . $contrib->contrib_id . '/';

		$this->mvdir_recursive($this->unzip_dir . $package_root, $style_root, false);
		$this->rmdir_recursive($this->unzip_dir);

		$variables = array('db', 'phpbb_root_path');

		// Let's get lazy.
		foreach ($variables as $variable)
		{
			${'_' . $variable} = $GLOBALS[$variable];
			$GLOBALS[$variable] = ${$variable};
		}

		// Get the acp_styles class.
		phpbb::_include('acp/acp_styles', false, 'acp_styles');
	    if (!defined('TEMPLATE_BITFIELD'))
	    {
		    // Hardcoded template bitfield to add for new templates
		    $bitfield = new bitfield();
		    $bitfield->set(0);
		    $bitfield->set(1);
		    $bitfield->set(2);
		    $bitfield->set(3);
		    $bitfield->set(4);
		    $bitfield->set(8);
		    $bitfield->set(9);
		    $bitfield->set(11);
		    $bitfield->set(12);
		    define('TEMPLATE_BITFIELD', $bitfield->get_base64());
		    unset($bitfield);
	    }
		$styles = new acp_styles();

		// Fill the configuration variables
		$styles->style_cfg = $styles->template_cfg = $styles->theme_cfg = $styles->imageset_cfg = '
#
# phpBB {MODE} configuration file
#
# @package phpBB3
# @copyright (c) 2005 phpBB Group
# @license http://opensource.org/licenses/gpl-license.php GNU Public License
#
#
# At the left is the name, please do not change this
# At the right the value is entered
# For on/off options the valid values are on, off, 1, 0, true and false
#
# Values get trimmed, if you want to add a space in front or at the end of
# the value, then enclose the value with single or double quotes.
# Single and double quotes do not need to be escaped.
#
#

# General Information about this {MODE}
name = {NAME}
copyright = {COPYRIGHT}
version = {VERSION}
';

		$styles->theme_cfg .= '
# Some configuration options

#
# You have to turn this option on if you want to use the
# path template variables ({T_IMAGESET_PATH} for example) within
# your css file.
# This is mostly the case if you want to use language specific
# images within your css file.
#
parse_css_file = {PARSE_CSS_FILE}
';

		$styles->template_cfg .= '
# Some configuration options

#
# You can use this function to inherit templates from another template.
# The template of the given name has to be installed.
# Templates cannot inherit from inheriting templates.
#';

		$styles->imageset_keys = array(
			'logos' => array(
				'site_logo',
			),
			'buttons'	=> array(
				'icon_back_top', 'icon_contact_aim', 'icon_contact_email', 'icon_contact_icq', 'icon_contact_jabber', 'icon_contact_msnm', 'icon_contact_pm', 'icon_contact_yahoo', 'icon_contact_www', 'icon_post_delete', 'icon_post_edit', 'icon_post_info', 'icon_post_quote', 'icon_post_report', 'icon_user_online', 'icon_user_offline', 'icon_user_profile', 'icon_user_search', 'icon_user_warn', 'button_pm_forward', 'button_pm_new', 'button_pm_reply', 'button_topic_locked', 'button_topic_new', 'button_topic_reply',
			),
			'icons'		=> array(
				'icon_post_target', 'icon_post_target_unread', 'icon_topic_attach', 'icon_topic_latest', 'icon_topic_newest', 'icon_topic_reported', 'icon_topic_unapproved', 'icon_friend', 'icon_foe',
			),
			'forums'	=> array(
				'forum_link', 'forum_read', 'forum_read_locked', 'forum_read_subforum', 'forum_unread', 'forum_unread_locked', 'forum_unread_subforum', 'subforum_read', 'subforum_unread'
			),
			'folders'	=> array(
				'topic_moved', 'topic_read', 'topic_read_mine', 'topic_read_hot', 'topic_read_hot_mine', 'topic_read_locked', 'topic_read_locked_mine', 'topic_unread', 'topic_unread_mine', 'topic_unread_hot', 'topic_unread_hot_mine', 'topic_unread_locked', 'topic_unread_locked_mine', 'sticky_read', 'sticky_read_mine', 'sticky_read_locked', 'sticky_read_locked_mine', 'sticky_unread', 'sticky_unread_mine', 'sticky_unread_locked', 'sticky_unread_locked_mine', 'announce_read', 'announce_read_mine', 'announce_read_locked', 'announce_read_locked_mine', 'announce_unread', 'announce_unread_mine', 'announce_unread_locked', 'announce_unread_locked_mine', 'global_read', 'global_read_mine', 'global_read_locked', 'global_read_locked_mine', 'global_unread', 'global_unread_mine', 'global_unread_locked', 'global_unread_locked_mine', 'pm_read', 'pm_unread',
			),
			'polls'		=> array(
				'poll_left', 'poll_center', 'poll_right',
			),
			'ui'		=> array(
				'upload_bar',
			),
			'user'		=> array(
				'user_icon1', 'user_icon2', 'user_icon3', 'user_icon4', 'user_icon5', 'user_icon6', 'user_icon7', 'user_icon8', 'user_icon9', 'user_icon10',
			),
		);

		// Define references.
		$error = array();
		$style_id = 0;
		$style_row = array(
			'install_name'			=> $stylecfg['name'],
			'install_copyright'		=> $stylecfg['copyright'],
			'template_id'			=> 0,
			'template_name'			=> $stylecfg['name'],
			'template_copyright'	=> $stylecfg['copyright'],
			'theme_id'				=> 0,
			'theme_name'			=> $stylecfg['name'],
			'theme_copyright'		=> $stylecfg['copyright'],
			'imageset_id'			=> 0,
			'imageset_name'			=> $stylecfg['name'],
			'imageset_copyright'	=> $stylecfg['copyright'],
			'store_db'				=> 0,
			'style_active'			=> 1,
			'style_default'			=> 0,
		);

		// Install the style.
								// (&$error, $action, $root_path, &$id, $name, $path, $copyright, $active, $default, &$style_row, $template_root_path = false, $template_path = false, $theme_root_path = false, $theme_path = false, $imageset_root_path = false, $imageset_path = false)
		if (!$styles->install_style($error, 'install', $style_root, $style_id, $stylecfg['name'], basename(strtolower(str_replace(' ', '_', $stylecfg['name']))) . '_' . $contrib->contrib_id, $stylecfg['copyright'], true, false, $style_row))
		{
			if ($error != array(phpbb::$user->lang['STYLE_ERR_NAME_EXIST']))
			{
				$this->error = array_merge($this->error, $error);
			}
			else
			{
				$sql = 'SELECT style_id
					FROM ' . STYLES_TABLE . "
					WHERE style_name = '" . $db->sql_escape(basename($stylecfg['name'])) . "'";
				$db->sql_query($sql);
				$style_id = $db->sql_fetchfield('style_id');
				$db->sql_freeresult();
			}
		}

		// Have UMIL refresh the template, theme, imageset
		phpbb::_include('../umil/umil', false, 'umil');
		$umil = new umil(true, $db);
		$umil->cache_purge('template', $style_id);
		$umil->cache_purge('theme', $style_id);
		$umil->cache_purge('imageset', $style_id);

		foreach ($variables as $variable)
		{
			$GLOBALS[$variable] = ${'_' . $variable};
		}

		return $style_id;
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
			return ($this->check_filesystem_path($directory, TITANIA_ROOT . 'store/') || $this->check_filesystem_path($directory, titania::$config->upload_path) || $this->check_filesystem_path($directory, titania::$config->contrib_temp_path) || $this->check_filesystem_path($directory, TITANIA_ROOT . 'includes/')) ? true : false;
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
