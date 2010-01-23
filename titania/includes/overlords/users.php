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

class users_overlord
{
	/**
	* Users, CP fields, status array
	* Stores [id] => user_row
	*
	* @var array
	*/
	public static $users = array();
	public static $cp_fields = array();
	public static $status = array();

	public static function load($user_ids)
	{
		self::load_users($user_ids);
		self::load_cp_fields($user_ids);
		self::load_status($user_ids);
	}

	public static function load_users($user_ids)
	{
		// Always load the anonymous user, used in case the user requested doesn't exist for some reason
		$user_ids[] = ANONYMOUS;

		// Only get the rows for those we have not gotten already
		$user_ids = array_diff($user_ids, array_keys(self::$users));

		if (!sizeof($user_ids))
		{
			return;
		}

		$sql_ary = array(
			'SELECT' => 'u.*, z.friend, z.foe',
			'FROM'		=> array(
				USERS_TABLE => 'u',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(ZEBRA_TABLE => 'z'),
					'ON'	=> 'z.user_id = ' . phpbb::$user->data['user_id'] . ' AND z.zebra_id = u.user_id'
				)
			),
			'WHERE' => phpbb::$db->sql_in_set('u.user_id', array_map('intval', $user_ids)),
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$users[$row['user_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	public static function load_cp_fields($user_ids)
	{
		// Only get the rows for those we have not gotten already
		$user_ids = array_diff($user_ids, array_keys(self::$cp_fields));

		if (!sizeof($user_ids))
		{
			return;
		}

		// Load custom profile fields
		if (phpbb::$config['load_cpf_viewtopic'])
		{
			phpbb::_include('functions_profile_fields', false, 'custom_profile');

			$cp = new custom_profile();

			// Grab all profile fields from users in id cache for later use - similar to the poster cache
			$profile_fields_tmp = $cp->generate_profile_fields_template('grab', $user_ids);

			// filter out fields not to be displayed on viewtopic. Yes, it's a hack, but this shouldn't break any MODs.
			foreach ($profile_fields_tmp as $profile_user_id => $profile_fields)
			{
				self::$cp_fields[$profile_user_id] = array();
				foreach ($profile_fields as $used_ident => $profile_field)
				{
					if ($profile_field['data']['field_show_on_vt'])
					{
						self::$cp_fields[$profile_user_id][$used_ident] = $profile_field;
					}
				}
			}
			unset($profile_fields_tmp);
		}
	}

	public static function load_status($user_ids)
	{
		// Only get the rows for those we have not gotten already
		$user_ids = array_diff($user_ids, array_keys(self::$status));

		if (!sizeof($user_ids))
		{
			return;
		}

		// Generate online information for user
		if (phpbb::$config['load_onlinetrack'])
		{
			$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
				FROM ' . SESSIONS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('session_user_id', array_map('intval', $user_ids)) . '
				GROUP BY session_user_id';
			$result = phpbb::$db->sql_query($sql);

			$update_time = phpbb::$config['load_online_time'] * 60;
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				self::$status[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || phpbb::$auth->acl_get('u_viewonline'))) ? true : false;
			}
			phpbb::$db->sql_freeresult($result);
		}
	}

	/**
	 * Retrieve the data on a user
	 *
	 * @param <type> $user_id The user_id
	 * @param <type> $field The field you want (leave blank to return the full row)
	 * @param <type> $query True to query the DB for the user if not already loaded
	 */
	public static function get_user($user_id, $field = false, $query = false)
	{
		if ($query)
		{
			// Load the user if not already done
			self::load_users(array($user_id));
		}

		// If the user does not exist, use the anonymous uer
		if (!isset(self::$users[$user_id]))
		{
			$user_id = ANONYMOUS;
		}

		// Special things...
		if ($field[0] == '_')
		{
			switch ($field)
			{
				case '_profile' :
				case '_username' :
				case '_colour' :
				case '_full' :
				case '_no_profile' :
					return get_username_string(substr($field, 1), $user_id, self::$users[$user_id]['username'], self::$users[$user_id]['user_colour']);
				break;
			}
		}

		if ($field)
		{
			return self::$users[$user_id][$field];
		}

		return self::$users[$user_id];
	}

	public static function assign_details($user_id)
	{
		$row = self::get_user($user_id);
		$user_id = $row['user_id']; // Re-assign properly...in case it gives us the anonymous user

		phpbb::_include('functions_display', 'get_user_rank');

		// IT'S A HACK!
		global $phpbb_root_path;
		$phpbb_root_path = titania::$absolute_board;

		// Get user rank and avatar (need hacks for this)
		get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_image'], $row['rank_image_src']);
		$user_avatar = (phpbb::$user->optionget('viewavatars')) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : '';

		// Undo
		$phpbb_root_path = PHPBB_ROOT_PATH;

		// End signature parsing, only if needed
		if ($row['user_sig'] && phpbb::$config['allow_sig'] && phpbb::$user->optionget('viewsigs'))
		{
			$row['user_sig'] = generate_text_for_display($row['user_sig'], $row['user_sig_bbcode_uid'], $row['user_sig_bbcode_bitfield'], 7);
		}

		return array(
			'USER_FULL'				=> get_username_string('full', $user_id, $row['username'], $row['user_colour']),
			'USER_COLOUR'			=> get_username_string('colour', $user_id, $row['username'], $row['user_colour']),
			'USERNAME'				=> get_username_string('username', $user_id, $row['username'], $row['user_colour']),

			'RANK_TITLE'			=> $row['rank_title'],
			'RANK_IMG'				=> $row['rank_image'],
			'RANK_IMG_SRC'			=> $row['rank_image_src'],
			'USER_JOINED'			=> phpbb::$user->format_date($row['user_regdate']),
			'USER_POSTS'			=> $row['user_posts'],
			'USER_FROM'				=> $row['user_from'],
			'USER_AVATAR'			=> $user_avatar,
			'USER_WARNINGS'			=> $row['user_warnings'],
	//		'USER_AGE'				=> $row['age'],
			'USER_SIG'				=> $row['user_sig'],

			'ICQ_STATUS_IMG'		=> (!empty($row['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '',
			'ONLINE_IMG'			=> ($user_id != ANONYMOUS && isset(self::$status[$user_id])) ? ((self::$status[$user_id]) ? phpbb::$user->img('icon_user_online', 'ONLINE') : phpbb::$user->img('icon_user_offline', 'OFFLINE')) : '',
			'S_ONLINE'				=> ($user_id != ANONYMOUS && isset(self::$status[$user_id])) ? self::$status[$user_id] : false,
			'S_FRIEND'				=> (isset($row['friend'])) ? true : false,
			'S_FOE'					=> (isset($row['foe'])) ? true : false,

	// @todo: info link...need to build the mcp stuff first.
	//		'U_INFO'				=> ($auth->acl_get('m_info', $forum_id)) ? phpbb::append_sid('mcp', "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, phpbb::$user->session_id) : '',
			'U_USER_PROFILE'		=> get_username_string('profile', $user_id, $row['username'], $row['user_colour']),
			'U_SEARCH'				=> (phpbb::$auth->acl_get('u_search')) ? phpbb::append_sid('search', "author_id=$user_id&amp;sr=posts") : '',
			'U_PM'					=> ($user_id != ANONYMOUS && phpbb::$config['allow_privmsg'] && phpbb::$auth->acl_get('u_sendpm') && ($row['user_allow_pm'] || phpbb::$auth->acl_gets('a_', 'm_') || phpbb::$auth->acl_getf_global('m_'))) ? phpbb::append_sid('ucp', 'i=pm&amp;mode=compose') : '',
			'U_EMAIL'				=> (!empty($row['user_allow_viewemail']) || phpbb::$auth->acl_get('a_email')) ? ((phpbb::$config['board_email_form'] && phpbb::$config['email_enable']) ? phpbb::append_sid('memberlist', "mode=email&amp;u=$user_id") : ((phpbb::$config['board_hide_emails'] && !phpbb::$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email'])) : '',
			'U_WWW'					=> $row['user_website'],
			'U_ICQ'					=> (!empty($row['user_icq'])) ? 'http://www.icq.com/people/webmsg.php?to=' . $row['user_icq'] : '',
			'U_AIM'					=>($row['user_aim'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=aim&amp;u=$user_id") : '',
			'U_MSN'					=> ($row['user_msnm'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=msnm&amp;u=$user_id") : '',
			'U_YIM'					=> ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode($row['user_yim']) . '&amp;.src=pg' : '',
			'U_JABBER'				=> ($row['user_jabber'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=jabber&amp;u=$user_id") : '',
		);
	}
}
