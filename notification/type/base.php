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
* Base class for Titania notification types.
*
* All Titania subscription events are data driven: the dispatcher passes the
* watch pairs to select the recipients from, the language key and parameters
* for the board notification, the url, and the email template with its
* variables. Subclasses only differ in their type name, their UCP option and
* their availability.
*/
abstract class base extends \phpbb\notification\type\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	/**
	* Set the user loader (used for actor avatars and names)
	*
	* @param \phpbb\user_loader $user_loader
	*/
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	* The item type the user's notification preferences are stored under.
	* Types sharing a UCP option (via $notification_option['id']) share it.
	*
	* @return string
	*/
	protected function get_option_type()
	{
		if (static::$notification_option !== false && isset(static::$notification_option['id']))
		{
			return static::$notification_option['id'];
		}
		return $this->get_type();
	}

	/**
	* {@inheritdoc}
	*/
	static public function get_item_id($type_data)
	{
		return (int) $type_data['item_id'];
	}

	/**
	* {@inheritdoc}
	*/
	static public function get_item_parent_id($type_data)
	{
		return (isset($type_data['item_parent_id'])) ? (int) $type_data['item_parent_id'] : 0;
	}

	/**
	* Find the users subscribed to the given watch pairs.
	*
	* @param array $type_data Expects 'watch' (array of array(object_type, object_id)
	*	pairs) and optionally 'exclude_user'.
	* @param array $options
	* @return array
	*/
	public function find_users_for_notification($type_data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'	=> array(),
		), $options);

		// The watch table constant only exists once Titania's common.php ran,
		// which is the case on every page that dispatches a notification.
		if (empty($type_data['watch']) || !defined('TITANIA_WATCH_TABLE'))
		{
			return array();
		}

		$sql_objects = array();
		foreach ($type_data['watch'] as $watch)
		{
			$sql_objects[] = '(watch_object_type = ' . (int) $watch[0] . '
				AND watch_object_id = ' . (int) $watch[1] . ')';
		}

		$sql = 'SELECT watch_user_id
			FROM ' . TITANIA_WATCH_TABLE . '
			WHERE (' . implode(' OR ', $sql_objects) . ')' .
			((!empty($type_data['exclude_user'])) ? ' AND watch_user_id <> ' . (int) $type_data['exclude_user'] : '');
		$result = $this->db->sql_query($sql);

		$users = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[(int) $row['watch_user_id']] = true;
		}
		$this->db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}
		$users = array_keys($users);
		sort($users);

		return $this->check_user_notification_options($users, array_merge($options, array(
			'item_type'	=> $this->get_option_type(),
		)));
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($type_data, $pre_create_data = array())
	{
		$this->set_data('lang_key', $type_data['lang_key']);
		$this->set_data('lang_params', (isset($type_data['lang_params'])) ? $type_data['lang_params'] : array());
		$this->set_data('reference', (isset($type_data['reference'])) ? $type_data['reference'] : '');
		$this->set_data('url', (isset($type_data['url'])) ? $type_data['url'] : '');
		$this->set_data('email_template', $type_data['email_template']);
		$this->set_data('email_vars', (isset($type_data['email_vars'])) ? $type_data['email_vars'] : array());
		$this->set_data('actor_id', (isset($type_data['actor_id'])) ? (int) $type_data['actor_id'] : 0);

		parent::create_insert_array($type_data, $pre_create_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_title()
	{
		$params = $this->get_data('lang_params');

		return $this->language->lang_array($this->get_data('lang_key'), (is_array($params)) ? $params : array());
	}

	/**
	* {@inheritdoc}
	*/
	public function get_reference()
	{
		$reference = $this->get_data('reference');

		return ($reference) ? $this->language->lang('NOTIFICATION_REFERENCE', $reference) : '';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_url()
	{
		return $this->get_data('url');
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template()
	{
		return '@phpbb_titania/' . $this->get_data('email_template');
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template_variables()
	{
		$vars = $this->get_data('email_vars');

		return (is_array($vars)) ? $vars : array();
	}

	/**
	* {@inheritdoc}
	*/
	public function users_to_query()
	{
		$actor_id = (int) $this->get_data('actor_id');

		return ($actor_id) ? array($actor_id) : array();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_avatar()
	{
		$actor_id = (int) $this->get_data('actor_id');

		return ($actor_id) ? $this->user_loader->get_avatar($actor_id, false, true) : '';
	}
}
