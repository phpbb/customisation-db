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

namespace phpbb\titania\user;

class helper
{
	/**
	 * Get the user ids from a list of usernames
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param string $list		List of usernames
	 * @param string $separator Delimiter. Defaults to new line character
	 * @return array Returns array in form of
	 * 	array(
	 * 		'ids'		=> array(),
	 * 		'missing'	=> array(),
	 * 	)
	 */
	public static function get_user_ids_from_list(\phpbb\db\driver\driver_interface $db, $list, $separator = "\n")
	{
		$users = array(
			'ids'		=> array(),
			'missing'	=> array(),
		);

		if (!$list)
		{
			return $users;
		}
		$usernames = explode($separator, $list);

		foreach ($usernames as &$username)
		{
			$users['missing'][$username] = $username;
			$username = utf8_clean_string($username);
		}

		unset($username);

		$sql = 'SELECT username, username_clean, user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('username_clean', $usernames) . '
			AND user_type != ' . USER_IGNORE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			unset($users['missing'][$row['username']], $users['missing'][$row['username_clean']]);

			$users['ids'][$row['username']] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		return $users;
	}
}
