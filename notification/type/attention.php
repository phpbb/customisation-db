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
* A new attention item (report or content needing approval) was created.
*/
class attention extends base
{
	/** @var \phpbb\titania\contribution\type\collection */
	protected $type_collection;

	/**
	* {@inheritdoc}
	*/
	static public $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_TITANIA_ATTENTION',
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
		return 'phpbb.titania.notification.type.attention';
	}

	/**
	* {@inheritdoc}
	*
	* Mirrors the attention page's own access check.
	*/
	public function is_available()
	{
		return $this->auth->acl_gets('u_titania_mod_contrib_mod', 'u_titania_mod_post_mod')
			|| count($this->type_collection->find_authed('moderate')) > 0;
	}
}
