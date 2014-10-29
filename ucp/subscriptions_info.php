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

namespace phpbb\titania\ucp;

class subscriptions_info
{
	public function module()
	{
		return array(
			'filename'	=> '\phpbb\titania\ucp\subscriptions_module',
			'title'		=> 'UCP_TITANIA',
			'modes'		=> array(
				'items'	=> array(
					'title'	=> 'SUBSCRIPTION_ITEMS_MANAGE',
					'auth'	=> '',
					'cat'	=> array('UCP_MAIN')
				),
				'sections'	=> array(
					'title'	=> 'SUBSCRIPTION_SECTIONS_MANAGE',
					'auth'	=> '',
					'cat'	=> array('UCP_MAIN')
				),
			),
		);
	}
}
