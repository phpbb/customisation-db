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

namespace phpbb\titania\controller\manage;

class manage extends base
{
	/**
	* Redirect user to first management page that he has access to.
	*
	* @return Returns \Symfony\Component\HttpFoundation\Response if user does not have
	*	permission to access any management page. Otherwise redirects.
	*/
	public function base()
	{
		$pages = $this->get_navigation_options();

		foreach ($pages as $page)
		{
			if ($page['auth'])
			{
				redirect($page['url']);
			}
		}

		return $this->helper->needs_auth();
	}
}
