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

namespace phpbb\titania;

class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		if (!defined('TITANIA_SUPPORT'))
		{
			$php_ext = $this->container->getParameter('core.php_ext');
			include($this->extension_path . 'includes/constants.' . $php_ext);
		}

		return true;
	}
}
