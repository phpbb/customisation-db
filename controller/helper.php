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

namespace phpbb\titania\controller;

use phpbb\auth\auth;
use phpbb\cache\driver\driver_interface as cache_interface;
use phpbb\config\config;
use phpbb\cron\manager;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\routing\helper as routing_helper;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\titania\config\config as ext_config;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class helper extends \phpbb\controller\helper
{
	/** @var ext_config  */
	protected $ext_config;

	/**
	 * Constructor
	 *
	 * @param auth $auth
	 * @param cache_interface $cache
	 * @param config $config Config object
	 * @param manager $cron_manager
	 * @param driver_interface $db
	 * @param dispatcher $dispatcher
	 * @param language $language
	 * @param request_interface $request phpBB request object
	 * @param routing_helper $routing_helper Helper to generate the routes
	 * @param symfony_request $symfony_request Symfony Request object
	 * @param template $template Template object
	 * @param user $user User object
	 * @param $root_path
	 * @param $admin_path
	 * @param $php_ext
	 * @param bool $sql_explain
	 * @param ext_config|null $ext_config
	 */
	public function __construct(auth $auth, cache_interface $cache, config $config, manager $cron_manager,
								driver_interface $db, dispatcher $dispatcher, language $language,
								request_interface $request, routing_helper $routing_helper,
								symfony_request $symfony_request, template $template, user $user, $root_path,
								$admin_path, $php_ext, $sql_explain = false,
								ext_config $ext_config = null)
	{
		parent::__construct($auth, $cache, $config, $cron_manager, $db, $dispatcher, $language, $request,  $routing_helper, $symfony_request, $template, $user, $root_path, $admin_path, $php_ext, $sql_explain);

		$this->ext_config = $ext_config;
	}

	/**
	 * Modify URL to point back to correct Titania location.
	 *
	 * If Titania is running from an app.php that is not under
	 * the board root, the URL needs to be adjusted since routes
	 * that are generated on the phpBB board will always point
	 * back to it.
	 *
	 * @param string $url
	 * @return string
	 */
	public function get_real_url($url)
	{
		if (!is_null($this->ext_config) && $this->ext_config->titania_script_path)
		{
			$scripts_path = array(
				'phpbb_script_path'   => $this->ext_config->phpbb_script_path,
				'titania_script_path' => $this->ext_config->titania_script_path,
			);

			// We start by removing the domain name from the URL
			if (strpos($url, generate_board_url(true)) === 0)
			{
				$url = substr($url, strlen(generate_board_url(true)));
			}

			// Then, we remove the script path
			foreach ($scripts_path as $script_path)
			{
				if (strpos($url, '/' . $script_path) === 0)
				{
					$url = substr($url, strlen('/' . rtrim($script_path, '/')));
					break;
				}
			}

			return generate_board_url(true) . '/' . rtrim($this->ext_config->titania_script_path, '/') . $url;
		}
		return $url;
	}

	/**
	* Checks whether user is logged in and outputs login box
	* for guests or returns error response for registered users.
	*
	* @return Response object
	*/
	public function needs_auth()
	{
		if (!$this->user->data['is_registered'])
		{
			login_box($this->get_current_url());
		}

		return $this->error($this->user->lang['NO_AUTH'], 403);
	}

	/**
	* @{inheritDoc}
	*/
	public function route($route, array $params = array(), $is_amp = true, $session_id = false, $reference_type = UrlGeneratorInterface::ABSOLUTE_URL)
	{
		$route = parent::route($route, $params, $is_amp, $session_id, $reference_type);

		return ($this->config->offsetGet('cookie_secure') && strpos($route, 'http://') === 0) ? 'https://' . substr($route, 7) : $route;
	}

	/**
	* {@inheritDoc}
	*/
	public function render($template_file, $page_title = '', $status_code = 200, $display_online_list = false, $item_id = 0, $item = 'forum', $send_headers = false)
	{
		return parent::render($template_file, $this->user->lang($page_title), $status_code, $display_online_list, $item_id, $item);
	}

	/**
	* {@inheritDoc}
	*/
	public function error($message, $code = 500)
	{
		return parent::error($this->user->lang($message), $code);
	}
}