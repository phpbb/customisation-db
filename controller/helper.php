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

use phpbb\config\config;
use phpbb\controller\provider;
use phpbb\extension\manager;
use phpbb\filesystem;
use phpbb\request\request_interface;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\titania\config\config as ext_config;
use phpbb\user;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class helper extends \phpbb\controller\helper
{
	/** @var ext_config  */
	protected $ext_config;

	/**
	 * Constructor
	 *
	 * @param template $template
	 * @param user $user
	 * @param config $config
	 * @param provider $provider
	 * @param manager $manager
	 * @param symfony_request $symfony_request
	 * @param request_interface $request
	 * @param filesystem $filesystem
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 * @param ext_config|null $ext_config
	 */
	public function __construct(template $template, user $user, config $config, provider $provider, manager $manager, symfony_request $symfony_request, request_interface $request, filesystem $filesystem, $phpbb_root_path, $php_ext, ext_config $ext_config = null)
	{
		parent::__construct($template, $user, $config, $provider, $manager, $symfony_request, $request, $filesystem, $phpbb_root_path, $php_ext);

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
		return parent::route($route, $params, $is_amp, $session_id, $reference_type);
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
