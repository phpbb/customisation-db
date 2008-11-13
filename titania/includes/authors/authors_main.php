<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* authors_main
* Titania Authors and Maintainers
* @package authors
*/
class authors_main extends titania_object
{
	public $p_master;
	public $u_action;

	/**
	 * Constructor
	 */
	public function __construct($p_master)
	{
		global $user;

		$this->p_master = $p_master;

		$this->page = $user->page['script_path'] . $user->page['page_name'];
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $user;

		$user->add_lang(array('titania_contrib', 'titania_authors'));

		switch ($mode)
		{
			case 'profile':
				$this->tpl_name = 'authors/author_profile';
				$this->page_title = 'AUTHOR_PROFILE';

				$found = $this->author_profile();

				if (!$found)
				{
					titania::error_box('ERROR', $user->lang['AUTHOR_NOT_FOUND'], ERROR_ERROR);

					$this->main($id, 'list');
					return;
				}

			break;

			case 'list':
			default:
				$this->tpl_name = 'authors/author_list';
				$this->page_title = 'AUTHOR_LIST';

				$this->author_list();
			break;
		}
	}

	private function author_list()
	{
		global $db, $template, $config, $auth, $user;

		if (!class_exists('sort'))
		{
			include(TITANIA_ROOT . 'includes/class_sort.' . PHP_EXT);
		}

		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);
		}

		$sort = new sort();

		$sort->set_sort_keys(array(
			array('SORT_AUTHOR',		'a.author_username_clean', 'default' => true),
			array('SORT_AUTHOR_RATING',	'a.author_rating'),
			array('SORT_CONTRIBS',		'a.author_contribs'),
			array('SORT_MODS',			'a.author_mods'),
			array('SORT_STYLES',		'a.author_styles'),
		));

		$sort->sort_request(false);

		$pagination = new pagination();
		$pagination->result_lang = 'AUTHOR';
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		// select the list of contribs
		$sql_ary = array(
			'SELECT'	=> 'a.*, u.user_lastvisit, u.username, u.user_posts, u.user_colour',
			'FROM'		=> array(
				CUSTOMISATION_AUTHORS_TABLE => 'a',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'a.user_id = u.user_id'
				),
			),
			'WHERE'		=> 'a.author_visible <> ' . AUTHOR_HIDDEN,
			'ORDER_BY'	=> $sort->get_order_by(),
		);

		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		$authors = $author_id_key = array();

		while ($author = $db->sql_fetchrow($result))
		{
			$author_id_key[$author['user_id']] = $author;
			$author_id_key[$author['user_id']]['online'] = false;
			$authors[] = &$author_id_key[$author['user_id']];
		}
		$db->sql_freeresult($result);

		// Generate online information for user
		if ($config['load_onlinetrack'] && sizeof($authors))
		{
			$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
				FROM ' . SESSIONS_TABLE . '
				WHERE ' . $db->sql_in_set('session_user_id', array_keys($author_id_key)) . '
				GROUP BY session_user_id';
			$result = $db->sql_query($sql);

			$update_time = $config['load_online_time'] * 60;
			while ($row = $db->sql_fetchrow($result))
			{
				$author_id_key[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
			}
			$db->sql_freeresult($result);
		}

		foreach ($authors as $author)
		{
			$u_author_profile = append_sid(TITANIA_ROOT . 'authors/index.' . PHP_EXT, 'mode=profile');

			$template->assign_block_vars('authors', array(
				'USER_FULL'			=> ($author['user_id']) ? get_username_string('full', $author['user_id'], $author['username'], $author['user_colour']) : '',
				'AUTHOR_FULL'		=> get_username_string('full', $author['author_id'], $author['author_username'], $author['user_colour'], false, $u_author_profile),
				'CONTRIBS'			=> $author['author_contribs'],
				'MODS'				=> $author['author_mods'],
				'STYLES'			=> $author['author_styles'],
				'RATING'			=> $this->generate_rating($author['author_rating']),
				'WEBSITE'			=> $author['author_website'],
				'LAST_VISIT'		=> $user->format_date($author['user_lastvisit'], false, true),
				'POSTS'				=> $author['user_posts'],
				'ONLINE'			=> $author['online'],
				'U_PHPBB_PROFILE'	=> ($author['phpbb_user_id']) ? U_PHPBBCOM_VIEWPROFILE . '&amp;u=' . $author['phpbb_user_id'] : '',
			));
		}

		$pagination->sql_total_count($sql_ary, 'a.author_id');

		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(false),
			'sd'	=> $sort->get_sort_dir(false),
		));

		$pagination->build_pagination($this->u_action);

		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}

	private function author_profile()
	{
		global $db, $template;

		$author_id = request_var('u', 0);

		$sql_ary = array(
			'SELECT' => 'a.*, u.user_lastvisit, u.username, u.user_posts, u.user_colour',
			'FROM'		=> array(
				CUSTOMISATION_AUTHORS_TABLE => 'a',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'a.user_id = u.user_id'
				),
			),
			'WHERE'		=> 'a.author_id = ' . $author_id . '
				AND a.author_visible <> ' . AUTHOR_HIDDEN
		);

		$sql = $db->sql_build_query('SELECT', $sql_ary);

		$result = $db->sql_query($sql);

		if(!($author = $db->sql_fetchrow($result)))
		{
			return false;
		}

		if(!$author['author_visible'])
		{
			return false;
		}

		$template->assign_vars(array(
			'AUTHOR_NAME'		=> get_username_string('username', $author['user_id'], $author['username'], $author['user_colour']),
			'USER_FULL'			=> ($author['user_id']) ? get_username_string('full', $author['user_id'], $author['username'], $author['user_colour']) : '',
			'REAL_NAME'			=> htmlspecialchars($author['author_realname']),
			'WEBSITE'			=> $author['author_website'],
			'RATING'			=> $this->generate_rating($author['author_rating']),
			'RATING_COUNT'		=> $author['author_rating_count'],
			'CONTRIB_COUNT'		=> $this->generate_contrib_string('contrib', 'link', $author['author_contribs'], $author_id),
			'SNIPPET_COUNT'		=> $this->generate_contrib_string('snippet', 'link', $author['author_snippets'], $author_id),
			'MOD_COUNT'			=> $this->generate_contrib_string('mod', 'link', $author['author_mods'], $author_id),
			'STYLE_COUNT'		=> $this->generate_contrib_string('style', 'link', $author['author_styles'], $author_id),

			'U_PHPBB_PROFILE'	=> ($author['phpbb_user_id'] && titania::$config->phpbbcom_profile) ? U_PHPBBCOM_VIEWPROFILE . '&amp;u=' . $author['phpbb_user_id'] : '',
		));

		return true;
	}

	// Currently this just returns the $rating parameter, but we may want to use an image/image combo for ratings
	// This can be changed later if this is decided.
	private function generate_rating($rating)
	{
		return round($rating, 2);
	}

	// This can handle generating links to a contrib list, as well as just text
	private function generate_contrib_string($contrib_type, $string_type, $num, $author_id = 0)
	{
		global $user;

		$contrib_type = strtoupper($contrib_type);
		$lang_key = 'NUM_' . $contrib_type . (($num == 1)?'':'S');
		$contrib_string = sprintf($user->lang[$lang_key], $num);
		if($string_type == 'link')
		{
			if($author_id == 0)
			{
				trigger_error('Author ID not set when using link', E_USER_WARNING);
			}
			switch($contrib_type)
			{
				case 'MOD':
					$url = append_sid(TITANIA_ROOT . 'mods/index.php', 'mode=search&amp;u=' . $author_id);

				break;

				default:
					$url = '#';
			}
			$contrib_string = '<a href="' . $url . '">' . $contrib_string . '</a>';
		}

		return $contrib_string;
	}
}