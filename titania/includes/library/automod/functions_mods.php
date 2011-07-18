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
* @param integer This one is used internally to control recursion level.
* @return array List of all files found matching the specified pattern.
*/
function find_files($directory, $pattern, $max_levels = 20, $_current_level = 1)
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
					$subdir = array_merge($subdir, find_files($fullname . '/', $pattern, $max_levels, $_current_level + 1));
				}
			}
			else
			{
				if (preg_match('/^' . $pattern . '$/i', $file))
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
}

function determine_write_method($pre_install = false)
{
	global $phpbb_root_path, $config;

	// to be truly correct, we should scan all files ...
	if ((is_writable($phpbb_root_path) && $config['write_method'] == WRITE_DIRECT) || $pre_install)
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
 * Recursively delete a directory
 *
 * @param string $file File name
 * @author A_Jelly_Doughnut
 */
function recursive_unlink($file)
{
	if (!($dh = opendir($file)))
	{
		return false;
	}

	while (($subfile = readdir($dh)) !== false)
	{
		if ($subfile[0] == '.')
		{
		    continue;
		}

		if (!unlink($file. '/' . $subfile))
		{
			recursive_unlink($file . '/' . $subfile);
		}
	}

	closedir($dh);

	rmdir($file);

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

?>