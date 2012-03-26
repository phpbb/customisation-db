<?php
/**
*
* @package Support Tool Kit - Organize Language Files
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class organize_lang
{
	/**
	* Tool Info
	*
	* @return Returns an array with the info about this tool.
	*/
	function info()
	{
		global $user;

		return array(
			'NAME'			=> $user->lang['ORGANIZE_LANG'],
			'NAME_EXPLAIN'	=> $user->lang['ORGANIZE_LANG_EXPLAIN'],
		);
	}

	/**
	* Display Options
	*
	* Output the options available
	*/
	function display_options()
	{
		return array(
			'title'	=> 'ORGANIZE_LANG',
			'vars'	=> array(
				'legend1'			=> 'ORGANIZE_LANG',
				'file'				=> array('lang' => 'ORGANIZE_LANG_FILE', 'default' => 'en/', 'type' => 'text:40:255', 'explain' => true),
			)
		);
	}

	/**
	* Run Tool
	*
	* Does the actual stuff we want the tool to do after submission
	*/
	function run_tool(&$error)
	{
        if (!check_form_key('organize_lang'))
		{
			$error[] = 'FORM_INVALID';
			return;
		}

		$file = request_var('file', '');
		if (!$file || (!file_exists(TITANIA_ROOT . 'language/' . $file) && !file_exists(TITANIA_ROOT . 'language/' . $file . '.' . PHP_EXT)))
		{
			$error[] = 'NO_FILE';
			return;
		}

		organize_lang($file);

		trigger_back('ORGANIZE_LANG_SUCCESS');
	}
}

/**
* For finding the max string length for the organize_lang function
*/
function find_max_length($lang, &$max_length, $start = 0)
{
	$start_length = $start * 4;

	foreach($lang as $name => $value)
	{
		if (is_array($value))
		{
			find_max_length($value, $max_length, ($start + 1));
		}

		if ((utf8_strlen($name) + $start_length) > $max_length)
		{
			$max_length = (utf8_strlen($name) + $start_length);
		}
	}
}

/**
* For outputting the lines for the organize_lang function
*/
function lang_lines($lang, $max_length, &$output, $start = 0)
{
	$last_letter = '';
	$total_tabs = ceil(($max_length + 3) / 4) - $start;

	if ($start != 0)
	{
		//ksort($lang);
	}

	$last_name = '';
	foreach($lang as $name => $value)
	{
		if ($name == $last_name)
		{
			echo 'Lang Duplicate: ' . $name . '<br />';
		}
		$last_name = $name;

		// make sure to add slashes to single quotes!
		$name = addcslashes($name, "'");

		// add an extra end line if the next word starts with a different letter then the last
		if (substr($name, 0, 1) != $last_letter && $start == 0)
		{
			$output .= "\n";
			$last_letter = substr($name, 0, 1);
		}

		// add the beggining tabs
		for ($i=0; $i <= $start; $i++)
		{
			$output .= "\t";
		}

		// add the beginning of the lang section and add slashes to single quotes for the name
		$output .= "'" . $name . "'";

		// figure out the number of tabs we need to add to the middle, then add them
		$tabs = ($total_tabs - ceil((utf8_strlen($name) + 3) / 4));

		for($i=0; $i <= $tabs; $i++)
		{
			$output .= "\t";
		}

		if (is_array($value))
		{
			$output .= "=> array(\n";
			lang_lines($value, $max_length, $output, ($start + 1));

			for ($i=0; $i <= $start; $i++)
			{
				$output .= "\t";
			}
			$output .= "),\n\n";
		}
		else
		{
			// add =>, then slashes to single quotes and add to the output
			$output .= "=> '" . addcslashes($value, "'") . "',\n";
		}
	}
}

/**
* Organize the language file by the lang keys, then re-output the data to the file
*/
function organize_lang($file, $skip_errors = false)
{
	if (substr($file, -1) == '/')
	{
		$file = substr($file, 0, -1);
	}

	// If the user submitted a directory, do every language file in that directory
	if (is_dir(TITANIA_ROOT . 'language/' . $file))
	{
		if ($handle = opendir(TITANIA_ROOT . 'language/' . $file))
		{
		    while (false !== ($file1 = readdir($handle)))
			{
				if ($file1 == '.' || $file1 == '..' || $file1 == '.svn')
				{
					continue;
				}

				if (strpos($file1, '.' . PHP_EXT))
				{
					organize_lang($file . '/' . substr($file1, 0, strpos($file1, '.' . PHP_EXT)), true);
				}
				else if (is_dir(TITANIA_ROOT . 'language/' . $file . '/' . $file1))
				{
					organize_lang($file . '/' . $file1);
				}
		    }
		    closedir($handle);
		}

		// if we went to a subdirectory, return
		if ($file != request_var('file', '') && $file . '/' != request_var('file', ''))
		{
			return;
		}

		// Finished entire directory
		return;
	}

	// include the file
	@include(TITANIA_ROOT . 'language/' . $file . '.' . PHP_EXT);

	// make sure it is a valid language file
	if (!isset($lang) || !is_array($lang))
	{
		if ($skip_errors)
		{
			return;
		}

		trigger_back('Bad Language File. language/' . $file);
	}

	// setup the $output var
	$output = '';

	// lets get the header of the file...
	$handle = @fopen(TITANIA_ROOT . 'language/' . $file . '.' . PHP_EXT, "r");
	if ($handle)
	{
		$stopped = false;

		while (!feof($handle))
		{
			$line = fgets($handle, 4096);

			// if the line is $lang = array_merge($lang, array( break out of the while loop
			if ($line == '$lang = array_merge($lang, array(' . "\n")
			{
				$stopped = true;
				break;
			}

			$output .= $line;
		}
		fclose($handle);

		if (!$stopped)
		{
			if ($skip_errors)
			{
				echo 'Bad line endings in ' . TITANIA_ROOT . 'language/' . $file . '.' . PHP_EXT . '<br />';
				return;
			}

			trigger_back('Please make sure you are using UNIX line endings.');
		}
	}

	// sort the languages by keys
	ksort($lang);

	// get the maximum length of the name string so we can format the page nicely when we output it
	$max_length = 1;

	find_max_length($lang, $max_length);

	// now add $lang = array_merge($lang, array( to the output
	$output .= '$lang = array_merge($lang, array(';

	lang_lines($lang, $max_length, $output);

	// add the end
	$output .= '));
';

	// write the contents to the specified file
	file_put_contents(TITANIA_ROOT . 'language/' . $file . '.' . PHP_EXT, $output);
}
?>