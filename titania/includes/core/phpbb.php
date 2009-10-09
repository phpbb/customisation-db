<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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
 * phpBB class that will be used in place of globalising these variables.
 */
class phpbb
{
	public static $auth;
	public static $cache;
	public static $config;
	public static $db;
	public static $template;
	public static $user;

	/**
	 * Static Constructor.
	 */
	public static function initialise()
	{
		global $auth, $config, $db, $template, $user, $cache;

		self::$auth		= &$auth;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;
		self::$cache	= &$cache;
	}

	/**
	* Shortcut for phpbb's append_sid function (do not send the root path/phpext in the url part)
	*
	* @param mixed $url
	* @param mixed $params
	* @param mixed $is_amp
	* @param mixed $session_id
	* @return string
	*/
	public static function append_sid($url, $params = false, $is_amp = true, $session_id = false)
	{
		if (!strpos($url, '.' . PHP_EXT))
		{
			$url = titania::$absolute_board . $url . '.' . PHP_EXT;
		}

		return append_sid($url, $params, $is_amp, $session_id);
	}

	/**
	* Include a phpBB includes file
	*
	* @param string $file The name of the file
	* @param string|bool $function_check Bool false to ignore; string function name to check if the function exists (and not load the file if it does)
	* @param string|bool $class_check Bool false to ignore; string class name to check if the class exists (and not load the file if it does)
	*/
	public static function _include($file, $function_check = false, $class_check = false)
	{
		if ($function_check !== false)
		{
			if (function_exists($function_check))
			{
				return;
			}
		}

		if ($class_check !== false)
		{
			if (class_exists($class_check))
			{
				return;
			}
		}

		include(PHPBB_ROOT_PATH . 'includes/' . $file . '.' . PHP_EXT);
	}

	/**
	* Assign/Build custom bbcodes for display in screens supporting using of bbcodes
	* The custom bbcodes buttons will be placed within the template block 'custom_codes'
	*/
	public static function display_custom_bbcodes()
	{
		global $db, $template, $user;

		// Start counting from 22 for the bbcode ids (every bbcode takes two ids - opening/closing)
		$num_predefined_bbcodes = 22;

		$sql = 'SELECT bbcode_id, bbcode_tag, bbcode_helpline
			FROM ' . BBCODES_TABLE . '
			WHERE display_on_posting = 1
			ORDER BY bbcode_tag';
		$result = $db->sql_query($sql);

		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			// If the helpline is defined within the language file, we will use the localised version, else just use the database entry...
			if (isset($user->lang[strtoupper($row['bbcode_helpline'])]))
			{
				$row['bbcode_helpline'] = $user->lang[strtoupper($row['bbcode_helpline'])];
			}

			$template->assign_block_vars('custom_tags', array(
				'BBCODE_NAME'		=> "'[{$row['bbcode_tag']}]', '[/" . str_replace('=', '', $row['bbcode_tag']) . "]'",
				'BBCODE_ID'			=> $num_predefined_bbcodes + ($i * 2),
				'BBCODE_TAG'		=> $row['bbcode_tag'],
				'BBCODE_HELPLINE'	=> $row['bbcode_helpline'],
				'A_BBCODE_HELPLINE'	=> str_replace(array('&amp;', '&quot;', "'", '&lt;', '&gt;'), array('&', '"', "\'", '<', '>'), $row['bbcode_helpline']),
			));

			$i++;
		}
		$db->sql_freeresult($result);
	}

	/**
	* Fill smiley templates (or just the variables) with smilies, either in a window or inline
	*/
	public static function generate_smilies($mode, $forum_id)
	{
		global $auth, $db, $user, $config, $template;
		global $phpEx, $phpbb_root_path;

		$start = request_var('start', 0);

		if ($mode == 'window')
		{
			if ($forum_id)
			{
				$sql = 'SELECT forum_style
					FROM ' . FORUMS_TABLE . "
					WHERE forum_id = $forum_id";
				$result = $db->sql_query_limit($sql, 1);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$user->setup('posting', (int) $row['forum_style']);
			}
			else
			{
				$user->setup('posting');
			}

			page_header($user->lang['SMILIES']);

			$sql = 'SELECT COUNT(smiley_id) AS count
				FROM ' . SMILIES_TABLE . '
				GROUP BY smiley_url';
			$result = $db->sql_query($sql, 3600);

			$smiley_count = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				++$smiley_count;
			}
			$db->sql_freeresult($result);

			$template->set_filenames(array(
				'body' => 'posting_smilies.html')
			);

			$template->assign_var('PAGINATION',
				generate_pagination(append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies&amp;f=' . $forum_id),
					$smiley_count, $config['smilies_per_page'], $start, true)
			);
		}

		$display_link = false;
		if ($mode == 'inline')
		{
			$sql = 'SELECT smiley_id
				FROM ' . SMILIES_TABLE . '
				WHERE display_on_posting = 0';
			$result = $db->sql_query_limit($sql, 1, 0, 3600);

			if ($row = $db->sql_fetchrow($result))
			{
				$display_link = true;
			}
			$db->sql_freeresult($result);
		}

		if ($mode == 'window')
		{
			$sql = 'SELECT smiley_url, MIN(emotion) as emotion, MIN(code) AS code, smiley_width, smiley_height
				FROM ' . SMILIES_TABLE . '
				GROUP BY smiley_url, smiley_width, smiley_height
				ORDER BY smiley_order';
			$result = $db->sql_query_limit($sql, $config['smilies_per_page'], $start, 3600);
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . SMILIES_TABLE . '
				WHERE display_on_posting = 1
				ORDER BY smiley_order';
			$result = $db->sql_query($sql, 3600);
		}

		$smilies = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if (empty($smilies[$row['smiley_url']]))
			{
				$smilies[$row['smiley_url']] = $row;
			}
		}
		$db->sql_freeresult($result);

		if (sizeof($smilies))
		{
			foreach ($smilies as $row)
			{
				$template->assign_block_vars('smiley', array(
					'SMILEY_CODE'	=> $row['code'],
					'A_SMILEY_CODE'	=> addslashes($row['code']),
					'SMILEY_IMG'	=> $phpbb_root_path . $config['smilies_path'] . '/' . $row['smiley_url'],
					'SMILEY_WIDTH'	=> $row['smiley_width'],
					'SMILEY_HEIGHT'	=> $row['smiley_height'],
					'SMILEY_DESC'	=> $row['emotion'])
				);
			}
		}

		if ($mode == 'inline' && $display_link)
		{
			$template->assign_vars(array(
				'S_SHOW_SMILEY_LINK' 	=> true,
				'U_MORE_SMILIES' 		=> append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=smilies&amp;f=' . $forum_id))
			);
		}

		if ($mode == 'window')
		{
			page_footer();
		}
	}
}
