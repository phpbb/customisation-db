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
* A queue item moved to a watched queue category.
*
* A separate type from queue only because both events concern the same queue
* item and notifications deduplicate per type and item id; it shares the queue
* type's UCP option and preferences.
*/
class queue_move extends queue
{
	/**
	* {@inheritdoc}
	*/
	static public $notification_option = array(
		'id'	=> 'phpbb.titania.notification.type.queue',
		'lang'	=> 'NOTIFICATION_TYPE_TITANIA_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_TITANIA',
	);

	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'phpbb.titania.notification.type.queue_move';
	}
}
