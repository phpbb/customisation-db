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

use phpbb\titania\url\legacy\rerouter;
use Symfony\Component\HttpFoundation\RedirectResponse;

class legacy_rerouter
{
	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/**
	* Constructor
	*
	* @param \phpbb\titania\controller\helper $helper
	*/
	public function __construct(\phpbb\titania\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	/**
	* Display contributions from all contribution types.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function redirect($path)
	{
		$rerouter = new rerouter();
		$url_data = $rerouter->get_url_data($path);

		if (!empty($url_data))
		{
			try
			{
				$redirect_url = $this->helper->route(
					$url_data['route'],
					$url_data['params'],
					false
				);
				return new RedirectResponse($redirect_url, 301);
			}
			catch (\Exception $e)
			{
			}
		}
		return $this->helper->error('NO_PAGE_FOUND', 404);
	}
}
