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

namespace phpbb\titania;

class access
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var int */
	protected $level = self::PUBLIC_LEVEL;

	/**
	 * Level constants.
	 */
	const TEAM_LEVEL = 0;
	const AUTHOR_LEVEL = 1;
	const PUBLIC_LEVEL = 2;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param config\config $ext_config
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\titania\config\config $ext_config)
	{
		$this->db = $db;
		$this->user = $user;
		$this->ext_config = $ext_config;

		$this->calculate_level();
	}

	/**
	 * Check whether the access level matches expected value.
	 *
	 * @param int $expected		Expected access level
	 * @param int|null $real	Optional access level to check. If none is given
	 * 	the user's current access level is used.
	 * @return bool
	 */
	public function is($expected, $real = null)
	{
		$real = ($real === null) ? $this->level : $real;
		return $expected == $real;
	}

	/**
	 * Check whether the access level is at team's level.
	 *
	 * @param null $level	Optional access level to check. If none is given
	 * 	the user's current access level is used.
	 * @return bool
	 */
	public function is_team($level = null)
	{
		return $this->is(self::TEAM_LEVEL, $level);
	}

	/**
	 * Check whether the access level is at author's level.
	 *
	 * @param null $level	Optional access level to check. If none is given
	 * 	the user's current access level is used.
	 * @return bool
	 */
	public function is_author($level = null)
	{
		return $this->is(self::AUTHOR_LEVEL, $level);
	}

	/**
	 * Check whether the access level is at public's level.
	 *
	 * @param null $level	Optional access level to check. If none is given
	 * 	the user's current access level is used.
	 * @return bool
	 */
	public function is_public($level = null)
	{
		return $this->is(self::PUBLIC_LEVEL, $level);
	}

	/**
	 * Get current access level.
	 *
	 * @return int
	 */
	public function get_level()
	{
		return $this->level;
	}

	/**
	 * Set access level.
	 *
	 * @param int $level
	 */
	public function set_level($level)
	{
		// Is it a valid level?
		if ($this->is_team($level) || $this->is_author($level) || $this->is_public($level))
		{
			$this->level = (int) $level;
		}
	}

	/**
	 * Calculate the user's current access level.
	 */
	protected function calculate_level()
	{
		// The user might be in a group with team access even if it's not his default group.
		$group_ids = $this->ext_config->__get('team_groups');

		if (!$group_ids)
		{
			return;
		}
		$sql = 'SELECT group_id, user_id, user_pending
			FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . '
				AND user_pending = 0
				AND ' . $this->db->sql_in_set('group_id', array_map('intval', $group_ids));
		$result = $this->db->sql_query_limit($sql, 1);

		if ($group_id = $this->db->sql_fetchfield('group_id'))
		{
			$this->set_level(self::TEAM_LEVEL);
		}
		$this->db->sql_freeresult($result);
	}
}
