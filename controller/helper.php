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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class helper extends \phpbb\controller\helper
{
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

		return (strpos($route, 'http://') === 0) ? 'https://' . substr($route, 7) : $route;
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
