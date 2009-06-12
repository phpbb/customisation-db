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
		$this->p_master = $p_master;
		$this->page = titania::$page;
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		titania::add_lang(array('contrib', 'authors'));

		$user_id = request_var('u', 0);

		if ($user_id && !$mode)
		{
			$found = $this->author_profile();

			if ($found)
			{
				$this->tpl_name = 'authors/author_profile';
				$this->page_title = 'AUTHOR_PROFILE';
				return;
			}
		}

		switch ($mode)
		{
			case 'profile':
				$this->tpl_name = 'authors/author_profile';
				$this->page_title = 'AUTHOR_PROFILE';

				$found = $this->author_profile();

				if (!$found)
				{
					titania::error_box('ERROR', phpbb::$user->lang['AUTHOR_NOT_FOUND'], ERROR_ERROR);

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
		if (!class_exists('sort'))
		{
			include(TITANIA_ROOT . 'includes/tools/sort.' . PHP_EXT);
		}

		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/tools/pagination.' . PHP_EXT);
		}

		$sort = new sort();
		$pagination = new pagination();

		$sort->set_sort_keys(array(
			array('SORT_AUTHOR',		'a.author_username_clean', 'default' => true),
			array('SORT_AUTHOR_RATING',	'a.author_rating'),
			array('SORT_CONTRIBS',		'a.author_contribs'),
			array('SORT_MODS',			'a.author_mods'),
			array('SORT_STYLES',		'a.author_styles'),
		));

		$sort->sort_request(false);

		$pagination->result_lang = 'AUTHOR';
		$start = $pagination->get_start();
		$limit = $pagination->get_limit();

		// select the list of contribs
		$sql_ary = array(
			'SELECT'	=> 'a.*, u.user_lastvisit, u.username, u.user_posts, u.user_colour',
			'FROM'		=> array(
				TITANIA_AUTHORS_TABLE => 'a',
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

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);

		$authors = $user_id_key = array();

		while ($author = phpbb::$db->sql_fetchrow($result))
		{
			$user_id_key[$author['user_id']] = $author;
			$user_id_key[$author['user_id']]['online'] = false;
			$authors[] = &$user_id_key[$author['user_id']];
		}
		phpbb::$db->sql_freeresult($result);

		// Generate online information for user
		if (phpbb::$config['load_onlinetrack'] && sizeof($authors))
		{
			$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
				FROM ' . SESSIONS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('session_user_id', array_keys($user_id_key)) . '
				GROUP BY session_user_id';
			$result = phpbb::$db->sql_query($sql);

			$update_time = $config['load_online_time'] * 60;
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$user_id_key[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || phpbb::$auth->acl_get('u_viewonline'))) ? true : false;
			}
			phpbb::$db->sql_freeresult($result);
		}

		$phpbbcom_profile_enabled = titania::$config->phpbbcom_profile;
		$phpbbcom_viewprofile_url = titania::$config->phpbbcom_viewprofile_url;

		foreach ($authors as $author)
		{
			$u_author_profile = append_sid(TITANIA_ROOT . 'authors/index.' . PHP_EXT, 'mode=profile');

			phpbb::$template->assign_block_vars('authors', array(
				'USER_FULL'			=> ($author['user_id']) ? get_username_string('full', $author['user_id'], $author['username'], $author['user_colour']) : '',
				'AUTHOR_FULL'		=> get_username_string('full', $author['user_id'], $author['author_username'], $author['user_colour'], false, $u_author_profile),
				'CONTRIBS'			=> $author['author_contribs'],
				'MODS'				=> $author['author_mods'],
				'STYLES'			=> $author['author_styles'],
				'RATING'			=> $this->generate_rating($author['author_rating']),
				'WEBSITE'			=> $author['author_website'],
				'LAST_VISIT'		=> phpbb::$user->format_date($author['user_lastvisit'], false, true),
				'POSTS'				=> $author['user_posts'],
				'ONLINE'			=> $author['online'],
				'U_PHPBB_PROFILE'	=> (!empty($author['phpbb_user_id']) && $phpbbcom_profile_enabled) ? sprintf($phpbbcom_viewprofile_url, $author['phpbb_user_id']) : '',
			));
		}

		$pagination->sql_total_count($sql_ary, 'a.user_id');

		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(false),
			'sd'	=> $sort->get_sort_dir(false),
		));

		$pagination->build_pagination($this->u_action);

		phpbb::$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}

	private function author_profile()
	{
		$user_id = request_var('u', 0);

		titania::load_object('author');

		$author = new titania_author($user_id);

		if ($author->load() === false)
		{
			return false;
		}

		$author_data = $author->get_profile_data();

		titania::load_object('rating');

		$author_rating = new titania_rating('author', $author);

		if(isset($author_data['author_visible']) && !$author_data['author_visible'])
		{
			return false;
		}

		phpbb::$template->assign_vars(array(
			'AUTHOR_NAME'		=> get_username_string('username', $author_data['user_id'], $author_data['username'], $author_data['user_colour']),
			'USER_FULL'			=> ($author_data['user_id']) ? get_username_string('full', $author_data['user_id'], $author_data['username'], $author_data['user_colour']) : '',
			'REAL_NAME'			=> htmlspecialchars($author_data['author_realname']),
			'WEBSITE'			=> $author_data['author_website'],
			'RATING'			=> $author_rating->get_rating_string(),
			'RATING_COUNT'		=> $author_data['author_rating_count'],
			'CONTRIB_COUNT'		=> $this->generate_contrib_string('contrib', 'link', $author_data['author_contribs'], $user_id),
			'SNIPPET_COUNT'		=> $this->generate_contrib_string('snippet', 'link', $author_data['author_snippets'], $user_id),
			'MOD_COUNT'			=> $this->generate_contrib_string('mod', 'link', $author_data['author_mods'], $user_id),
			'STYLE_COUNT'		=> $this->generate_contrib_string('style', 'link', $author_data['author_styles'], $user_id),

			'U_PHPBB_PROFILE'	=> $author->get_phpbb_com_profile_url(),
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
	private function generate_contrib_string($contrib_type, $string_type, $num, $user_id = 0)
	{
		$contrib_type = strtoupper($contrib_type);
		$lang_key = 'NUM_' . $contrib_type . (($num == 1)?'':'S');
		$contrib_string = sprintf(phpbb::$user->lang[$lang_key], $num);

		if($string_type == 'link')
		{
			if($user_id == 0)
			{
				trigger_error('Author ID not set when using link', E_USER_WARNING);
			}

			switch($contrib_type)
			{
				case 'MOD':
					$url = append_sid(TITANIA_ROOT . 'mods/index.php', 'mode=search&amp;u=' . $user_id);
				break;

				default:
					$url = '#';
			}

			$contrib_string = '<a href="' . $url . '">' . $contrib_string . '</a>';
		}

		return $contrib_string;
	}
}