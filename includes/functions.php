<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
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
* phpBB is nub like that
*
* @param string $revision
* @return string
*/
function get_real_revision_version($revision)
{
	return str_replace('rc', 'RC', strtolower($revision));
}

/**
* Generate text for display
* Returns the result of generate_text_for_display() from phpBB's functions_content.php file, but has hacks in place to let us use our own bbcode.html file
*
* @param mixed $text
* @param mixed $uid
* @param mixed $bitfield
* @param mixed $flags
* @return mixed
*/
function titania_generate_text_for_display($text, $uid, $bitfield, $flags)
{
	if (titania::$hook->call_hook(__FUNCTION__, $text, $uid, $bitfield, $flags))
	{
		if (titania::$hook->hook_return(__FUNCTION__))
		{
			return titania::$hook->hook_return_result(__FUNCTION__);
		}
	}
	phpbb::_include('bbcode', false, 'bbcode');
	return generate_text_for_display($text, $uid, $bitfield, $flags);
}

/**
* Decode a message from the database (properly)
*
* @param string $message
* @param mixed $bbcode_uid
*/
function titania_decode_message(&$message, $bbcode_uid = '')
{
	decode_message($message, $bbcode_uid);

	// We have to do all sorts of crap because decode_message doesn't properly decode a message for reinserting into the database

	// Replace &nbsp; with spaces - otherwise a number of issues happen...
	$message = str_replace('&nbsp;', ' ', $message);

	// Decode HTML entities, else bbcode reparsing will fail
	$message = html_entity_decode($message, ENT_QUOTES);

	// With magic_quotes_gpc on slashes are stripped too many times, so add them
	$message = (STRIP) ? addslashes($message) : $message;

	// Run set_var to re-encode the proper entities as if the user had submitted it themselves
	set_var($message, $message, 'string', true);
}

/**
* Exception handler
*
* @param mixed $exception
*/
function titania_exception_handler($exception)
{
	$message = $exception->getMessage();

	$message .= titania_backtrace($exception);

	trigger_error($message);
}

function titania_backtrace($exception = false)
{
	if (titania::$config->display_backtrace == 3 || (titania::$config->display_backtrace == 2 && titania::$access_level == TITANIA_ACCESS_TEAMS) || (titania::$config->display_backtrace == 1 && phpbb::$auth->acl_get('a_')))
	{
		if ($exception !== false)
		{
			return '<br /><br /><pre>' . var_export($exception->getTrace(), true) . '</pre>';
		}

		return '<br /><br /><pre>' . get_backtrace() . '</pre>';
	}
}

/**
* Used in titania::$cache->get_phpbb_versions()
*
* @param mixed $version1
* @param mixed $version2
* @return mixed
*/
function reverse_version_compare($version1, $version2)
{
	return version_compare($version2, $version1);
}

/**
* Compare the order of two attachments. Used to sort attachments in conjuction with uasort()
* @param array $attach1
* @param array $attach2
*/
function titania_attach_order_compare($attach1, $attach2)
{
	if ($attach1['attachment_order'] == $attach2['attachment_order'])
	{
		return 0;
	}
	else
	{
		return ($attach1['attachment_order'] > $attach2['attachment_order']) ? 1 : -1;
	}
}

/**
* Format the delta between two timestamps.
*
* @param int $start_time Lower time limit. If only $start_time is provided, then its value is used as the delta.
* @param int $end_time Upper time limit.
*
* @return string Returns a translated string containing the appropriate label (up to days) for the time delta. Eg. Less than a minute, 2 Minutes, 1 Hour, 10 Days
*/
function format_time_delta($start_time, $end_time = 0)
{
	if ($end_time)
	{
		$delta = abs($end_time - $start_time);
	}
	else
	{
		$delta = $start_time;
	}

	if ($delta < 60)
	{
		$delta = '';
		$delta_label = 'LESS_THAN_A_MINUTE';
	}
	else 
	{
		if ($delta < 3600)
		{
			$delta = floor($delta / 60);
			$delta_label = 'MINUTE';
		}
		else if ($delta < 86400)
		{
			$delta = floor($delta / 3600);
			$delta_label = 'HOUR';
		}
		else
		{
			$delta = floor($delta / 86400);
			$delta_label = 'DAY';
		}

		$delta_label .= ($delta != 1) ? 'S' : '';
	}

	return $delta . ' ' . phpbb::$user->lang[$delta_label] ;
}

function prerelease_submission_allowed($branch, $contrib_type)
{
	if (empty(titania::$config->prerelease_phpbb_version[$branch]) || empty(titania::$config->phpbb_versions[$branch]) || !titania_types::$types[$contrib_type]->prerelease_submission_allowed)
	{
		return false;
	}

	$branch_string = $branch[0] . '.' . $branch[1] . '.';
	$current_version = $branch_string . titania::$config->phpbb_versions[$branch]['latest_revision'];
	$next_version = $branch_string . titania::$config->prerelease_phpbb_version[$branch];

	return phpbb_version_compare($current_version, $next_version, '<');
}

/**
*
* Get Titania config object
*
* @param string $titania_root_path	Titania root path
* @param string $php_ext			PHP file extension
* @return \titania_config
*/
function titania_get_config($titania_root_path, $php_ext)
{
	$config_file = $titania_root_path . 'config.' . $php_ext;

	if (!file_exists($config_file) || !is_readable($config_file))
	{
		throw new \Exception('The Titania configuration file could not be found or is inaccessible. Check your configuration.');
	}

	// Config file may contain constants, so include constants file.
	if (!defined('TITANIA_CONTRIB'))
	{
		include($titania_root_path . 'includes/constants.' . $php_ext);
	}

	if (!class_exists('\titania_object'))
	{
		include($titania_root_path . 'includes/core/object.' . $php_ext);
	}

	if (!class_exists('\titania_config'))
	{
		include($titania_root_path . 'includes/core/config.' . $php_ext);
	}

	include($config_file);

	$config_object = new \titania_config($titania_root_path);

	if (!is_array($config))
	{
		$config = array();
	}
	$config_object->__set_array($config);

	return $config_object;
}
