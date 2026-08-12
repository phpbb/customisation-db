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

namespace phpbb\titania\notification\type;

/**
* A new revision of a subscribed contribution was released.
*/
class contribution extends base
{
	/**
	* {@inheritdoc}
	*/
	static public $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_TITANIA_CONTRIBUTION',
		'group'	=> 'NOTIFICATION_GROUP_TITANIA',
	);

	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'phpbb.titania.notification.type.contribution';
	}
}
