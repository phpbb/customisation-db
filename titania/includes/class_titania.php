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
 * titania class and functions for use within titania pages and apps.
 */
class titania
{
	/**
	 * Language ISO code name (en, de, etc)
	 *
	 * @var string
	 */
	private $lang_name;

	/**
	 * Titania Lang path
	 *
	 * @var string
	 */
	private $lang_path;

	/**
	 * Current viewing page location
	 *
	 * @var string
	 */
	public $page;

	/**
	 * construct class
	 */
	public function __construct()
	{
		$this->setup();
	}

	/**
	 * Titania setup, auto-load titania_common langauge file in TITANIA_ROOT language directory.
	 */
	private function setup()
	{
		global $user, $config;

		$this->lang_name = (file_exists(TITANIA_ROOT . 'language/' . $user->data['user_lang'] . '/titania_common.' . PHP_EXT)) ? $user->data['user_lang'] : basename($config['default_lang']);
		$this->lang_path = TITANIA_ROOT . 'language/' . $this->lang_name . '/';
		$this->add_lang('titania_common');
	}

	/**
	 * Add lang file for titania system.
	 *
	 * @param string $lang_set
	 * @param bool $phpbb_lang_file language file located in the phpbb/language/ directory.
	 */
	public function add_lang($lang_set, $phpbb_lang_file = false)
	{
		global $user;

		if (is_array($lang_set))
		{
			foreach ($lang_set as $lang_file)
			{
				$this->add_lang($lang_file);
			}
		}
		else
		{
			$language_filename = $this->lang_path . $lang_set . '.' . PHP_EXT;

			// if the phpbb_lang_file is set, we do not look in the TITANIA language directory for this file.
			// ensure the file exists, if not, check the phpbb/language/ directory for the language file
			// the downside of doing this is if the language file is missing from both locations, it will tell the user
			// that the language file does not exist in the phpbb/language/ directory
			if (!$phpbb_lang_file && file_exists($language_filename))
			{
				if ((@include $language_filename) === false)
				{
					trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
				}

				// we only merge the lang array if it is set and not empty
				if (!empty($lang) && is_array($lang))
				{
					$user->lang = array_merge($user->lang, $lang);
				}
			}
			else
			{
				$user->add_lang($lang_set);
			}
		}
	}

	/**
	 * Titania page_header
	 *
	 * @param string $page_title
	 * @param bool $display_online_list
	 */
	public function page_header($page_title = '', $display_online_list = true)
	{
		// Call the phpBB page_header() function, but we perform our own actions here as well.
		page_header($page_title, $display_online_list);
	}

	/**
	 * Titania page_footer
	 *
	 * @param cron $run_cron
	 */
	public function page_footer($run_cron = true)
	{
		global $auth, $user, $template, $cache;

		/*
		** This is development/testing code.
		** @security: Cross-Site Request Forgery
		*/

		// admin requested the cache to be purged, ensure they have permission and purge the cache.
		if (isset($_GET['cache']) && $_GET['cache'] == 'purge' && $auth->acl_get('a_'))
		{
			$cache->purge();
			trigger_error($user->lang['CACHE_PURGED'] . $this->back_link('', '', array('cache')));
		}

		$template->assign_vars(array(
			'U_PURGE_CACHE'		=> ($auth->acl_get('a_')) ? append_sid($user->page['script_path'] . $user->page['page_name'], 'cache=purge') : '',
		));

		page_footer($run_cron);
	}

	/**
	 * Generate HTML of to the previous or a specified page.
	 *
	 * @param string $redirect optional -- redirect URL absolute or relative path.
	 * @param string $l_redirect optional -- LANG string e.g.: 'RETURN_TO_MODS'
	 * @param array $exclude variables to exclude from params, if necessary. e.g.: array('search', 'sort');
	 * @param bool $return_url Return only the URL path, returns generated HTML by if set to false (default)
	 *
	 * @return HTML link string
	 */
	public function back_link($redirect = '', $l_redirect = '', $exclude = array(), $return_url = false)
	{
		global $user, $config;

		// if the redirect param is filled, we return directly to that page
		if (!$redirect)
		{
			// full site URL based on config.
			$site_url = $config['server_protocol'] . $config['server_name'] . '/';

			// if HTTP_REFERER is set, and begins with the site URL, we allow it to be our redirect...
			if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && (strpos($_SERVER['HTTP_REFERER'], $site_url) === 0))
			{
				$redirect = $_SERVER['HTTP_REFERER'];
			}
			else
			{
				$params = array();

				// collect the list of $_GET params to be used in the redirect string.
				foreach ($_GET as $key => $value)
				{
					if (!in_array($key, $exclude))
					{
						$params[] = $key . '=' . $value;
					}
				}

				$redirect = $user->page['script_path'] . $user->page['page_name'] . '?' . implode('&amp;', $params);
			}
		}

		// set the redirect string (Return to previous page)
		$l_redirect = ($l_redirect) ? $l_redirect : 'RETURN_LAST_PAGE';

		return (!$return_url) ? sprintf('<br /><br /><a href="%1$s">%2$s</a>', $redirect, $user->lang[$l_redirect]) : $redirect;
	}

	/**
	 * Function to list contribs for the selected type.
	 *
	 * @todo Hard-coding many actions, will then need to seperate these into their own functions/classes to be dynamically generated and scaleable
	 *
	 * @param string $contrib_type
	 */
	public function contrib_list($contrib_type)
	{
		global $db, $template, $user;

		// set an upper and lowercase contrib_type as well need each in multiple occurences.
		$l_contrib_type = strtolower($contrib_type);
		$u_contrib_type = strtoupper($contrib_type);

		if (!defined('CONTRIB_TYPE_' . $u_contrib_type))
		{
			trigger_error('NO_CONTRIB_TYPE');
		}

		$submit = isset($_REQUEST['submit']) ? true : false;

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
			'a'	=> array('SORT_AUTHOR',			'a.author_username_clean', 'default' => true),
			'b'	=> array('SORT_TIME_ADDED',		'c.contrib_release_date'),
			'c'	=> array('SORT_TIME_UPDATED',	'c.contrib_update_date'),
			'd'	=> array('SORT_DOWNLOADS',		'c.contrib_downloads'),
			'e'	=> array('SORT_RATING',			'c.contrib_rating'),
			'f'	=> array('SORT_CONTRIB_NAME',	'c.contrib_name'),
		));

		$sort->sort_request(false);

		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		// select the list of contribs
		$sql_ary = array(
			'SELECT'	=> 'a.author_id, a.author_username, c.*',
			'FROM'		=> array(
				CUSTOMISATION_CONTRIBS_TABLE => 'c',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_AUTHORS_TABLE => 'a'),
					'ON'	=> 'c.contrib_author_id = a.author_id'
				),
			),
			'WHERE'		=> 'contrib_status = ' . STATUS_APPROVED . '
						AND contrib_type = ' . constant('CONTRIB_TYPE_' . $u_contrib_type),
			'ORDER_BY'	=> $sort->get_order_by(),
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars($l_contrib_type, array(
				$u_contrib_type . '_ID'		=> $row['contrib_id'],
			));
		}
		$db->sql_freeresult($result);

		$pagination->sql_total_count($sql_ary, 'c.contrib_id');

		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(),
			'sd'	=> $sort->get_sort_dir(),
			'mode'	=> $mode,
		));

		$pagination->build_pagination(self::page);

		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}
}