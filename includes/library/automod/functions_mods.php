<?php
/**
*
* @package automod
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

global $table_prefix;
define('MODS_TABLE', $table_prefix . 'mods');

define('WRITE_DIRECT', 1);
define('WRITE_FTP', 2);
define('WRITE_MANUAL', 3);

function test_ftp_connection($method, &$test_ftp_connection, &$test_connection)
{
	global $phpbb_root_path, $phpEx;

	$transfer = new $method(request_var('host', ''), request_var('username', ''), request_var('password', ''), request_var('root_path', ''), request_var('port', ''), request_var('timeout', ''));

	$test_connection = $transfer->open_session();

	// Make sure that the directory is correct by checking for the existence of common.php
	if ($test_connection === true)
	{
		// Check for common.php file
		if (!$transfer->file_exists($phpbb_root_path, 'common.' . $phpEx))
		{
			$test_connection = 'ERR_WRONG_PATH_TO_PHPBB';
		}
	}

	$transfer->close_session();

	// Make sure the login details are correct before continuing
	if ($test_connection !== true)
	{
		$test_ftp_connection = true;
	}

	return;
}

/**
* Helper function
* Runs basename() on $path, then trims the extension from it
* @param string $path - path to be basenamed
*/
function core_basename($path)
{
	$path = basename($path);
	$path = substr($path, 0, strrpos($path, '.'));

	$parts = explode('-', $path);
	return end($parts);
}

/**
* Helper function for matching languages
* This is a fairly dumb function because it ignores dialects.  But I have
* not seen any MODs that specify more than one dialect of the same language
* @param string $user_language - ISO language code of the current user
* @param string $xml_language - ISO language code of the MODX tag
* @return bool Whether or not this is a match
*/
function match_language($user_language, $xml_language)
{
	return strtolower(substr($user_language, 0, 2)) == strtolower(substr($xml_language, 0, 2));
}

/**
* Grab MOD titles
*
* @param $header - variable holding all relevant tag information
* @return array(language iso => MOD name)
*/
function get_title($header)
{
	if (is_string($header['TITLE']))
	{
		return($header['TITLE']);
	}

	$return = array();
	foreach ($header['TITLE'] as $row)
	{
		if (!is_array($row))
		{
			continue;
		}

		$return[$row['attrs']['LANG']] = trim($row['data']);
	}

	return($return);
}

/**
 * Get localized MOD title or English title
 *
 * @param $titles, string or a serialized array containing the MOD name(s)
 * @param $lang, string language ISO.
 * @return string with MOD name in users selected language or English.
 */
function localize_title($title, $lang = 'en')
{
	$name_ary = array();

	if (!is_array($title))
	{
		if (($name_ary = @unserialize($title)) === false)
		{
			// Before AutoMOD 1.0.1 the title was a string with only the MOD name.
			return($title);
		}
	}
	else
	{
		$name_ary = $title;
	}

	// If we get here the MOD is installed with AutoMOD 1.0.1+
	// And the stored title is a serialized array.
	if (!empty($name_ary[$lang]))
	{
		return($name_ary[$lang]);
	}
	else if (!empty($name_ary['en']))
	{
		// Default to English if the selected language is not found.
		return($name_ary['en']);
	}

	// Something went wrong.
	// We have a array that neither contain the selected language nor English.
	// Return the first array element.
	return(array_shift($name_ary));
}

/**
* Easy method to grab localisable tags from the XML array
* @param $header - variable holding all relevant tag information
* @param $tagname - tag name to fetch
* @param $index - Index number to pull.  Not required.
* @return $output - Localised contents of the tag in question
*/
function localise_tags($header, $tagname, $index = false)
{
	global $user;

	$output = '';

	if (isset($header[$tagname]) && is_array($header[$tagname]))
	{
		foreach ($header[$tagname] as $i => $void)
		{
			// Ugly.
			if ($index !== false && $index != $i)
			{
				continue;
			}

			if (!isset($header[$tagname][$i]['attrs']['LANG']))
			{
				// avoid notice...although, if we get here, MODX is invalid.
				continue;
			}

			if (match_language($user->data['user_lang'], $header[$tagname][$i]['attrs']['LANG']))
			{
				$output = isset($header[$tagname][$i]['data']) ? htmlspecialchars(trim($header[$tagname][$i]['data'])) : '';
			}
			else if (match_language('en', $header[$tagname][$i]['attrs']['LANG']))
			{
				$output = isset($header[$tagname][$i]['data']) ? htmlspecialchars(trim($header[$tagname][$i]['data'])) : '';
			}
		}

		// If there was no language match, put something out there
		// This is probably fairly common for non-English users of the MODs Manager
		if (!$output)
		{
			$output = isset($header[$tagname][0]['data']) ? htmlspecialchars(trim($header[$tagname][0]['data'])) : '';
		}
	}

	if (!$output)
	{
		// Should never happen.  If it does, either the MOD is not valid MODX
		// or the tag being localised is optional
		$output = isset($user->lang['UNKNOWN_MOD_' . $tagname]) ? $user->lang['UNKNOWN_MOD_' . $tagname] : 'UNKNOWN_MOD_' .$tagname;
	}

	return $output;
}

/**
* List files matching specified PCRE pattern.
*
* @access public
* @param string Relative or absolute path to the directory to be scanned.
* @param string Search pattern (perl compatible regular expression).
* @param integer Number of subdirectory levels to scan (set to 1 to scan only current).
* @param boolean List sub-directories only instead of files
* @param integer This one is used internally to control recursion level.
* @return array List of all files found matching the specified pattern.
*/
function find_files($directory, $pattern, $max_levels = 20, $subdirs_only = false, $_current_level = 1)
{
	if ($_current_level <= 1)
	{
		if (strpos($directory, '\\') !== false)
		{
			$directory = str_replace('\\', '/', $directory);
		}
		if (empty($directory))
		{
			$directory = './';
		}
		else if (substr($directory, -1) != '/')
		{
			$directory .= '/';
		}
	}

	$files = array();
	$subdir = array();
	if (is_dir($directory))
	{
		$handle = @opendir($directory);
		while (($file = @readdir($handle)) !== false)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}

			$fullname = $directory . $file;

			if (is_dir($fullname))
			{
				if ($_current_level < $max_levels)
				{
					if ($subdirs_only)
					{
						$subdir[] = $fullname . '/';
						$subdir = array_merge($subdir, find_files($fullname . '/', $pattern, $max_levels, true, $_current_level + 1));
					}
					else
					{
						$subdir = array_merge($subdir, find_files($fullname . '/', $pattern, $max_levels, false, $_current_level + 1));
					}
				}
			}
			else
			{
				if (!$subdirs_only && preg_match('/^' . $pattern . '$/i', $file))
				{
					$files[] = $fullname;
				}
			}
		}
		@closedir($handle);
		sort($files);
	}

	return array_merge($files, $subdir);
}

/**
* This function is common to all editor classes, so it is pulled out from them
* @param $filename - The filename to update
* @param $template_id - The template set to update
* @param $file_contents - The data to write
* @param $install_time - Essentially the current time
* @return bool true
*/
function update_database_template($filename, $template_id, $file_contents, $install_time)
{
	return;
/*
	global $db;

	// grab filename
	preg_match('#styles/[a-z0-9_]+/template/([a-z0-9_]+.html)#i', $filename, $match);

	if (empty($match[1]))
	{
		return false;
	}

	$sql = 'UPDATE ' . STYLES_TEMPLATE_DATA_TABLE . "
		SET template_data = '" . $db->sql_escape($file_contents) . "', template_mtime = " . (int) $install_time . '
		WHERE template_id = ' . (int) $template_id . "
		AND template_filename = '" . $db->sql_escape($match[1]) . "'";
	$db->sql_query($sql);

	// if something failed, sql_query will error out
	return true;
*/
}

function determine_write_method($preview = false)
{
	global $phpbb_root_path, $config;

	// to be truly correct, we should scan all files ...
	if (($config['write_method'] == WRITE_DIRECT && is_writable($phpbb_root_path)) || $preview)
	{
		$write_method = 'direct';
	}
	// FTP Method is now auto-detected
	else if ($config['write_method'] == WRITE_FTP)
	{
		$write_method = 'ftp';
	}
	// or zip or tarballs
	else if ($config['compress_method'])
	{
		$write_method = 'manual';
	}
	else
	{
		// We cannot go on without a write method set up.
		trigger_error('MODS_SETUP_INCOMPLETE', E_USER_ERROR);
	}

	return $write_method;
}

function handle_ftp_details($method, $test_ftp_connection, $test_connection)
{
	global $config, $template, $user;

	$s_hidden_fields = build_hidden_fields(array('method' => $method));

	if (!class_exists($method))
	{
		trigger_error('Method does not exist.', E_USER_ERROR);
	}

	$requested_data = call_user_func(array($method, 'data'));
	foreach ($requested_data as $data => $default)
	{
		$default = (!empty($config['ftp_' . $data])) ? $config['ftp_' . $data] : $default;

		$template->assign_block_vars('data', array(
			'DATA'		=> $data,
			'NAME'		=> $user->lang[strtoupper($method . '_' . $data)],
			'EXPLAIN'	=> $user->lang[strtoupper($method . '_' . $data) . '_EXPLAIN'],
			'DEFAULT'	=> (!empty($_REQUEST[$data])) ? request_var($data, '') : $default
		));
	}

	$template->assign_vars(array(
		'S_CONNECTION_SUCCESS'		=> ($test_ftp_connection && $test_connection === true) ? true : false,
		'S_CONNECTION_FAILED'		=> ($test_ftp_connection && $test_connection !== true) ? true : false,
		'ERROR_MSG'					=> ($test_ftp_connection && $test_connection !== true) ? $user->lang[$test_connection] : '',

		'S_FTP_UPLOAD'			=> true,
		'UPLOAD_METHOD'			=> $method,
		'S_HIDDEN_FIELDS_FTP'	=> $s_hidden_fields,
	));
}

/**
* Gets FTP information if we need it
*
* @param	$preview	bool	true if in pre_(install|uninstall), false otherwise
* @return				array	and array of connection info (currently only ftp)
*/
function get_connection_info($preview = false)
{
	global $config, $ftp_method, $test_ftp_connection, $test_connection;

	$conn_info_ary = array();

	// using $config instead of $editor because write_method is forced to direct
	// when in preview mode
	if ($config['write_method'] == WRITE_FTP)
	{
		if (!$preview && isset($_POST['password']))
		{
			$conn_info_ary['method'] = $config['ftp_method'];

			if (empty($config['ftp_method']))
			{
				trigger_error('FTP_METHOD_ERROR');
			}

			$requested_data = call_user_func(array($config['ftp_method'], 'data'));

			foreach ($requested_data as $data => $default)
			{
				if ($data == 'password')
				{
					$config['ftp_password'] = request_var('password', '');
				}
				$default = (!empty($config['ftp_' . $data])) ? $config['ftp_' . $data] : $default;

				$conn_info_ary[$data] = $default;
			}
		}

		handle_ftp_details($ftp_method, $test_ftp_connection, $test_connection);
	}

	return $conn_info_ary;
}

/**
 * Recursively delete a directory
 *
 * @param	string	$path (required)	Directory path to recursively delete
 * @author	jasmineaura
 */
function recursive_unlink($path)
{
	global $phpbb_root_path, $phpEx, $user;

	// Insurance - this should never really happen
	if ($path == $phpbb_root_path || is_file("$path/common.$phpEx"))
	{
		return false;
	}

	// Get all of the files in the source directory
	$files = find_files($path, '.*');
	// Get all of the sub-directories in the source directory
	$subdirs = find_files($path, '.*', 20, true);

	// Delete all the files
	foreach ($files as $file)
	{
		if (!unlink($file))
		{
			return sprintf($user->lang['MODS_RMFILE_FAILURE'], $file);
		}
	}

	// Delete all the sub-directories, in _reverse_ order (array_pop)
	for ($i=0, $cnt = count($subdirs); $i < $cnt; $i++)
	{
		$subdir = array_pop($subdirs);
		if (!rmdir($subdir))
		{
			return sprintf($user->lang['MODS_RMDIR_FAILURE'], $subdir);
		}
	}

	// Finally, delete the directory itself
	if (!rmdir($path))
	{
		return sprintf($user->lang['MODS_RMDIR_FAILURE'], $path);
	}

	return true;
}

/**
* PHP 5 Wrapper - simulate scandir, but only those features that we actually need
* NB: The third parameter of PHP5 native scandir is _not_ present in this wrapper
*/
if (!function_exists('scandir'))
{
	function scandir($directory, $sorting_order = false)
	{
		$files = array();

		$dp = opendir($directory);
		while (($filename = readdir($dp)) !== false)
		{
			$files[] = $filename;
		}

		if ($sorting_order)
		{
			rsort($files);
		}
		else
		{
			sort($files);
		}

		return $files;
	}
}

/**
* Return the number of files (optionally including sub-directories) in a directory, optionally recursively.
*
* @param	$dir		string (required)	- The directory you want to count files in
* @param	$subdirs	boolean (optional)	- Count subdirectories instead of files
* @param	$recurse	boolean (optional)	- Recursive count into sub-directories
* @param	$count		int (optional)		- Initial value of file (or subdirs) count
* @return				int					- Count of files (or count of subdirectories)
* @author	jasmineaura
*/
function directory_num_files($dir, $subdirs = false, $recurse = false, $count = 0)
{
	if (is_dir($dir))
	{
		if($handle = opendir($dir))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == '.' || $file == '..')
				{
					continue;
				}
				else if (is_dir($dir."/".$file))
				{
					if ($subdirs)
					{
						$count++;
					}
					else if ($recurse)
					{
						$count = directory_num_files($dir."/".$file, $subdirs, $recurse, $count);
					}
				}
				else if (!$subdirs)
				{
					$count++;
				}
			}

			closedir($handle);
		}
	}

	return $count;
}

/**
* Check if a directory is empty.
*
* @param $dir, string - directory to check
* @return bool, true if directory is empty.
*/
function check_empty_dir($dir)
{
	if (!is_dir($dir) || !is_readable($dir))
	{
		return(false);
	}

	if(!$handle = opendir($dir))
	{
		return(false);
	}

	while (($file = readdir($handle)) !== false)
	{
		if ($file == '.' || $file == '..')
		{
			continue;
		}

		// If we get here the directory is not empty
		closedir($handle);
		return(false);
	}

	// The directory is empty
	closedir($handle);
	return(true);
}

?>
