<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
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
		$user_ids = array_diff($user_ids, array_keys(self::$users), array(0));

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
					return get_username_string(substr($field, 1), $user_id, self::$users[$user_id]['username'], self::$users[$user_id]['user_colour'], false, phpbb::append_sid('memberlist', 'mode=viewprofile'));
				break;

				case '_unbuilt_titania_profile' :
					return 'author/' . htmlspecialchars_decode(self::$users[$user_id]['username_clean']);
				break;

				case '_titania_profile' :
					return titania_url::build_url(self::get_user($user_id, '_unbuilt_titania_profile'));
				break;

				case '_titania' :
					return '<a href="' . self::get_user($user_id, '_titania_profile') . ((self::$users[$user_id]['user_colour']) ? '" style="color: #' . self::$users[$user_id]['user_colour'] . ';" class="username-coloured">' : '">') . get_username_string('no_profile', $user_id, self::$users[$user_id]['username'], self::$users[$user_id]['user_colour']) . '</a>';
				break;

				case '_u_pm' :
					return ($user_id != ANONYMOUS && phpbb::$config['allow_privmsg'] && phpbb::$auth->acl_get('u_sendpm') && (self::$users[$user_id]['user_allow_pm'] || phpbb::$auth->acl_gets('a_', 'm_') || phpbb::$auth->acl_getf_global('m_'))) ? phpbb::append_sid('ucp', "i=pm&amp;mode=compose&amp;u=$user_id") : '';
				break;

				case '_u_email' :
					return (!empty(self::$users[$user_id]['user_allow_viewemail']) || phpbb::$auth->acl_get('a_email')) ? ((phpbb::$config['board_email_form'] && phpbb::$config['email_enable']) ? phpbb::append_sid('memberlist', "mode=email&amp;u=$user_id") : ((phpbb::$config['board_hide_emails'] && !phpbb::$auth->acl_get('a_email')) ? '' : 'mailto:' . self::$users[$user_id]['user_email'])) : '';
				break;

				case '_icq' :
					return (!empty(self::$users[$user_id]['user_icq'])) ? 'http://www.icq.com/people/webmsg.php?to=' . self::$users[$user_id]['user_icq'] : '';
				break;

				case '_icq_status' :
					return (!empty(self::$users[$user_id]['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . self::$users[$user_id]['user_icq'] . '&amp;img=5" width="18" height="18" alt="" />' : '';
				break;

				case '_aim' :
					return (self::$users[$user_id]['user_aim'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=aim&amp;u=$user_id") : '';
				break;

				case '_msnm' :
					return (self::$users[$user_id]['user_msnm'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=msnm&amp;u=$user_id") : '';
				break;

				case '_yim' :
					return (self::$users[$user_id]['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . urlencode(self::$users[$user_id]['user_yim']) . '&amp;.src=pg' : '';
				break;

				case '_jabber' :
					return (self::$users[$user_id]['user_jabber'] && phpbb::$auth->acl_get('u_sendim')) ? phpbb::append_sid('memberlist', "mode=contact&amp;action=jabber&amp;u=$user_id") : '';
				break;

				case '_avatar' :
					// IT'S A HACK!
					global $phpbb_root_path;
					$phpbb_root_path = titania::$absolute_board;

					// Get avatar (need hacks for this)
					$avatar = (phpbb::$user->optionget('viewavatars')) ? get_user_avatar(self::$users[$user_id]['user_avatar'], self::$users[$user_id]['user_avatar_type'], self::$users[$user_id]['user_avatar_width'], self::$users[$user_id]['user_avatar_height']) : '';

					// Undo
					$phpbb_root_path = PHPBB_ROOT_PATH;

					return $avatar;
				break;

				case '_signature' :
					if (self::$users[$user_id]['user_sig'] && phpbb::$config['allow_sig'] && phpbb::$user->optionget('viewsigs'))
					{
						return titania_generate_text_for_display(self::$users[$user_id]['user_sig'], self::$users[$user_id]['user_sig_bbcode_uid'], self::$users[$user_id]['user_sig_bbcode_bitfield'], 7);
					}
					return '';
				break;
			}
		}

		if ($field)
		{
			return self::$users[$user_id][$field];
		}

		return self::$users[$user_id];
	}

	/**
	* Assign user details
	*
	* @param int $user_id
	* @param string $prefix Prefix to assign to the user details
	* @param bool $output_to_template True to output the data to the template
	*/
	public static function assign_details($user_id, $prefix = '', $output_to_template = false)
	{
		$row = self::get_user($user_id);
		$user_id = $row['user_id']; // Re-assign properly...in case it gives us the anonymous user

		phpbb::_include('functions_display', 'get_user_rank');
		phpbb::$user->add_lang('memberlist');

		// IT'S A HACK!
		global $phpbb_root_path;
		$phpbb_root_path = titania::$absolute_board;

		// Get user rank and avatar (need hacks for this)
		get_user_rank($row['user_rank'], $row['user_posts'], $row['rank_title'], $row['rank_image'], $row['rank_image_src']);

		// Undo
		$phpbb_root_path = PHPBB_ROOT_PATH;

		$output = array(
			$prefix . 'USER_FULL'			=> self::get_user($user_id, '_full'),
			$prefix . 'USER_COLOUR'			=> self::get_user($user_id, '_colour'),
			$prefix . 'USERNAME'			=> self::get_user($user_id, '_username'),

			$prefix . 'RANK_TITLE'			=> $row['rank_title'],
			$prefix . 'RANK_IMG'			=> $row['rank_image'],
			$prefix . 'RANK_IMG_SRC'		=> $row['rank_image_src'],
			$prefix . 'USER_JOINED'			=> phpbb::$user->format_date($row['user_regdate']),
			$prefix . 'USER_POSTS'			=> $row['user_posts'],
			$prefix . 'USER_FROM'			=> $row['user_from'],
			$prefix . 'USER_AVATAR'			=> self::get_user($user_id, '_avatar'),
			$prefix . 'USER_WARNINGS'		=> $row['user_warnings'],
	//		$prefix . 'USER_AGE'			=> $row['age'],
			$prefix . 'USER_SIG'			=> self::get_user($user_id, '_signature'),

			$prefix . 'ICQ_STATUS_IMG'		=> self::get_user($user_id, '_icq_status'),
			$prefix . 'ONLINE_IMG'			=> ($user_id != ANONYMOUS && isset(self::$status[$user_id])) ? ((self::$status[$user_id]) ? phpbb::$user->img('icon_user_online', 'ONLINE') : phpbb::$user->img('icon_user_offline', 'OFFLINE')) : '',
			$prefix . 'S_ONLINE'			=> ($user_id != ANONYMOUS && isset(self::$status[$user_id])) ? self::$status[$user_id] : false,
			$prefix . 'S_FRIEND'			=> (isset($row['friend'])) ? true : false,
			$prefix . 'S_FOE'				=> (isset($row['foe'])) ? true : false,

	// @todo: info link...need to build the mcp stuff first.
	//		$prefix . 'U_INFO'				=> ($auth->acl_get('m_info', $forum_id)) ? phpbb::append_sid('mcp', "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, phpbb::$user->session_id) : '',
			$prefix . 'U_USER_PROFILE'		=> self::get_user($user_id, '_profile'),
			$prefix . 'U_SEARCH'			=> (phpbb::$auth->acl_get('u_search')) ? phpbb::append_sid('search', "author_id=$user_id&amp;sr=posts") : '',
			$prefix . 'U_PM'				=> self::get_user($user_id, '_u_pm'),
			$prefix . 'U_EMAIL'				=> self::get_user($user_id, '_u_email'),
			$prefix . 'U_WWW'				=> $row['user_website'],
			$prefix . 'U_ICQ'				=> self::get_user($user_id, '_icq'),
			$prefix . 'U_AIM'				=> self::get_user($user_id, '_aim'),
			$prefix . 'U_MSN'				=> self::get_user($user_id, '_msnm'),
			$prefix . 'U_YIM'				=> self::get_user($user_id, '_yim'),
			$prefix . 'U_JABBER'			=> self::get_user($user_id, '_jabber'),
			$prefix . 'S_JABBER_ENABLED'	=> (phpbb::$config['jab_enable']) ? true : false,
		);

		if ($output_to_template)
		{
			phpbb::$template->assign_vars($output);
		}

		return $output;
	}
}
