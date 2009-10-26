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
* Generate the _options flag from the given settings
*
* @param bool $bbcode
* @param bool $smilies
* @param bool $url
* @return int options flag
*/
function get_posting_options($bbcode, $smilies, $url)
{
	return (($bbcode) ? OPTION_FLAG_BBCODE : 0) + (($smilies) ? OPTION_FLAG_SMILIES : 0) + (($url) ? OPTION_FLAG_LINKS : 0);
}

/**
* Reverses the posting options
*
* @param int $options The given posting options
* @param bool $bbcode
* @param bool $smilies
* @param bool $url
*/
function reverse_posting_options($options, &$bbcode, &$smilies, &$url)
{
	$bbcode = ($options & OPTION_FLAG_BBCODE) ? true : false;
	$smilies = ($options & OPTION_FLAG_SMILIES) ? true : false;
	$url = ($options & OPTION_FLAG_LINKS) ? true : false;
}

/*
 * Create select with Titania's accesses
 *
 * @param integer $default
 * @return string
 */
function titania_access_select($default = false)
{
	if (titania::$access_level == TITANIA_ACCESS_PUBLIC)
	{
		return '';
	}

	$access_types = array(
		TITANIA_ACCESS_TEAMS 	=> 'ACCESS_TEAMS',
		TITANIA_ACCESS_AUTHORS 	=> 'ACCESS_AUTHORS',
		TITANIA_ACCESS_PUBLIC 	=> 'ACCESS_PUBLIC',
	);

	if ($default === false)
	{
		$default = TITANIA_ACCESS_PUBLIC;
	}

	$s_options = '';

	foreach ($access_types as $type => $lang_key)
	{
		if (titania::$access_level > $type)
		{
			continue;
		}

		$selected = ($default == $type) ? ' selected="selected"' : '';

		$s_options .= '<option value="' . $type . '"' . $selected . '>' . phpbb::$user->lang[$lang_key] . '</option>';
	}

	return $s_options;
}

/**
* Get the author user_ids from the list of usernames
*
* @param string $list the list of usernames (after executed it will be an array of the user_ids)
* @param array $missing array of usernames that could not be found (will be populated if any)
* @param string $separator the delimiter
*/
function get_author_ids_from_list(&$list, &$missing, $separator = "\n")
{
	if (!$list)
	{
		$list = $missing = array();
		return true;
	}

	$usernames = explode($separator, $list);
	$list = array();

	foreach ($usernames as &$username)
	{
		$missing[$username] = $username;
		$username = utf8_clean_string($username);
	}

	$sql = 'SELECT username, user_id FROM ' . USERS_TABLE . '
		WHERE ' . phpbb::$db->sql_in_set('username_clean', $usernames) . '
		AND user_type != ' . USER_IGNORE;
	$result = phpbb::$db->sql_query($sql);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		unset($missing[$row['username']]);

		$list[$row['username']] = $row['user_id'];
	}

	if (sizeof($missing))
	{
		return false;
	}

	return true;
}