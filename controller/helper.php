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

use phpbb\titania\config\config as ext_config;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class helper extends \phpbb\controller\helper
{
	/** @var ext_config  */
	protected $ext_config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template $template Template object
	 * @param \phpbb\user $user User object
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\symfony_request $symfony_request Symfony Request object
	 * @param \phpbb\request\request_interface $request phpBB request object
	 * @param \phpbb\routing\helper $routing_helper Helper to generate the routes
	 * @param ext_config|null $ext_config
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\symfony_request $symfony_request, \phpbb\request\request_interface $request, \phpbb\routing\helper $routing_helper, ext_config $ext_config = null)
	{
		parent::__construct($template, $user, $config, $symfony_request, $request, $routing_helper);

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
			return generate_board_url(true) .'/'. rtrim($this->ext_config->titania_script_path, '/') .
			substr($url, strlen(generate_board_url()));
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
