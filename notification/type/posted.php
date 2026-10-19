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
* New topics and replies in subscribed topics and support areas.
*/
class posted extends base
{
	/**
	* {@inheritdoc}
	*/
	static public $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_TITANIA_POSTED',
		'group'	=> 'NOTIFICATION_GROUP_TITANIA',
	);

	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'phpbb.titania.notification.type.posted';
	}

	/**
	* {@inheritdoc}
	*
	* The poster's name is resolved at display time through the user loader,
	* like core's post notification does.
	*/
	public function get_title()
	{
		$params = $this->get_data('lang_params');
		$username = $this->user_loader->get_username((int) $this->get_data('actor_id'), 'no_profile');

		return $this->language->lang_array(
			$this->get_data('lang_key'),
			array_merge(array($username), (is_array($params)) ? $params : array())
		);
	}
}
