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
* A new item entered a watched validation queue.
*/
class queue extends base
{
	/** @var \phpbb\titania\contribution\type\collection */
	protected $type_collection;

	/**
	* {@inheritdoc}
	*/
	static public $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_TITANIA_QUEUE',
		'group'	=> 'NOTIFICATION_GROUP_TITANIA',
	);

	/**
	* Set the contribution type collection.
	*
	* @param \phpbb\titania\contribution\type\collection $type_collection
	*/
	public function set_type_collection(\phpbb\titania\contribution\type\collection $type_collection)
	{
		$this->type_collection = $type_collection;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'phpbb.titania.notification.type.queue';
	}

	/**
	* {@inheritdoc}
	*
	* Only users who can view at least one validation queue see the option.
	*/
	public function is_available()
	{
		return count($this->type_collection->find_authed('view')) > 0;
	}
}
