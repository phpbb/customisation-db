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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
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
