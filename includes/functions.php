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
