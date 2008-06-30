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
 * @todo remove || true
 */
if (!file_exists(TITANIA_ROOT . 'config.' . PHP_EXT)) // test the install...
{
	die('<p>The Titania config.' . PHP_EXT . ' file could not be found.</p>
	<p><a href="' . TITANIA_ROOT . 'install/index.' . PHP_EXT . '">Click here to install Titania</a></p>');
}

// Include titania configuration
require(TITANIA_ROOT . 'config.' . PHP_EXT);

// We need to prepend the titania root because $phpbb_root_path is relative to it.
define('PHPBB_ROOT_PATH', TITANIA_ROOT . $phpbb_root_path);

// We need those variables to let phpBB 3.0.x scripts work properly.
$phpbb_root_path = PHPBB_ROOT_PATH;
$phpEx = PHP_EXT;

// We set this so we can access the phpBB scripts.
define('IN_PHPBB', true);

// Include the general phpbb-related files.
// This will also check if phpBB is installed and if we have the settings we need (db etc.).
require(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Include titania constants
require(TITANIA_ROOT . 'includes/constants.' . PHP_EXT);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Set the custom template path for titania. Default: root/titania/template
$template->set_custom_template(TITANIA_ROOT . $template_path, 'titania');

$titania = new titania();

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
					trigger_error('Language file ' . basename($language_filename) . ' couldn\'t be opened.', E_USER_ERROR);
				}

				// we only merge the lang array if it is set and not empty
				if (isset($lang) && sizeof($lang))
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
}
