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

class categories
{
	/**
	* Tool Info
	*
	* @return Returns an array with the info about this tool.
	*/
	function info()
	{
		global $user;

		return array(
			'NAME'			=> $user->lang['MANAGE_CATEGORIES'],
		);
	}

	/**
	* Display Options
	*
	* Output the options available
	*/
	function display_options()
	{
		$controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		redirect($controller_helper->route('phpbb.titania.manage.categories'));
	}
}
