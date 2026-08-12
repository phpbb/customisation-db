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

class subscriptions
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var string */
	protected $watch_table;

	/**
	* The historical delivery type stored in watch_type. Every row carries
	* EMAIL; delivery preferences now live in the notification system, so the
	* column only distinguishes subscription rows, not how they are delivered.
	*/
	const EMAIL = 1;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\path_helper $path_helper
	 * @param \phpbb\notification\manager $notification_manager
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\path_helper $path_helper, \phpbb\notification\manager $notification_manager)
	{
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->path_helper = $path_helper;
		$this->notification_manager = $notification_manager;
		$this->watch_table = TITANIA_WATCH_TABLE;
	}

	/**
	* Shorten the amount of code required for some places
	*
	* @param mixed $object_type
	* @param mixed $object_id
	* @param mixed $url
	* @param string $lang_key Language key to use in link
	*/
	public function handle_subscriptions($object_type, $object_id, $url, $lang_key = 'SUBSCRIBE')
	{
		if (!$this->user->data['is_registered'])
		{
			// Cannot currently handle non-registered users
			return;
		}

		$action = $this->request->variable('subscribe', '');
		$action = (in_array($action, array('subscribe', 'unsubscribe'))) ? $action : false;
		$hash = $this->request->variable('hash', '');

		if ($action && check_link_hash($hash, $action))
		{
			$this->{$action}($object_type, $object_id);
		}

		$is_subscribed = $this->is_subscribed($object_type, $object_id);
		$action = 'subscribe';
		$toggle = 'unsubscribe';

		if ($is_subscribed)
		{
			$action = 'unsubscribe';
			$toggle = 'subscribe';
		}

		$hash = generate_link_hash($action);

		$params = array(
			'subscribe'	=> array(
				'subscribe'	=> $action,
				'hash'		=> $hash,
			),
			'toggle'	=> array(
				'subscribe'	=> $toggle,
				'hash'		=> $hash,
			),
		);

		$this->template->assign_vars(array(
			'IS_SUBSCRIBED'			=> $is_subscribed,

			'U_SUBSCRIBE'			=> $this->path_helper->append_url_params($url, $params['subscribe']),
			'L_SUBSCRIBE_TYPE'		=> $this->user->lang(($is_subscribed ? 'UN' . $lang_key : $lang_key)),

			'U_SUBSCRIBE_TOGGLE'	=> $this->path_helper->append_url_params($url, $params['toggle']),
			'L_SUBSCRIBE_TOGGLE'	=> $this->user->lang(($is_subscribed ? $lang_key : 'UN' . $lang_key)),
		));
	}

	/*
	 * Is Subscribed
	 */
	public function is_subscribed($object_type, $object_id, $user_id = false)
	{
		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

		$sql = 'SELECT watch_object_id
			FROM ' . $this->watch_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', array(
				'watch_object_type'		=> (int) $object_type,
				'watch_object_id'		=> (int) $object_id,
				'watch_user_id'			=> (int) $user_id,
			)
		);

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return !empty($row);
	}

	/*
	 * Subscribe
	 */
	public function subscribe($object_type, $object_id, $user_id = false, $subscription_type = self::EMAIL)
	{
		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

		if ($this->is_subscribed($object_type, $object_id, $user_id))
		{
			return false;
		}

		// Build an insert
		$sql = 'INSERT INTO ' . $this->watch_table . ' ' . $this->db->sql_build_array('INSERT', array(
			'watch_object_type'		=> (int) $object_type,
			'watch_type'			=> (int) $subscription_type,
			'watch_object_id'		=> (int) $object_id,
			'watch_user_id'			=> (int) $user_id,
			'watch_mark_time'		=> time(),
		));

		// Query and we're done
		$this->db->sql_query($sql);

		return true;
	}

	/*
	 * Unsubscribe
	 */
	public function unsubscribe($object_type, $object_id, $user_id = false)
	{
		$user_id = ($user_id === false) ? $this->user->data['user_id'] : $user_id;

		// Get our delete query
		$sql = 'DELETE FROM ' . $this->watch_table . "
				WHERE watch_object_id = " . (int) $object_id . '
					AND watch_user_id = ' .(int) $user_id . '
					AND watch_object_type = ' . (int) $object_type;

		// Query and we're done
		$this->db->sql_query($sql);

		return true;
	}

	/**
	 * Send subscription notifications through the phpBB notification system.
	 *
	 * Watchers of the given watch pairs receive the notification through the
	 * delivery methods they enabled in the UCP (board and/or email); the email
	 * method renders the same Titania email templates the legacy dispatcher
	 * used, with the same variables plus USERNAME added by the core.
	 *
	 * @param string $type Titania notification type suffix
	 *	(posted|contribution|queue|queue_move|attention)
	 * @param array $type_data Notification data:
	 *	'item_id'			int the notified item (post, revision, queue, attention id)
	 *	'item_parent_id'	int its parent (optional)
	 *	'watch'				array of array(watch_object_type, watch_object_id)
	 *						pairs selecting the recipients
	 *	'exclude_user'		int user to exclude, normally the acting user (optional)
	 *	'lang_key'			string language key for the board notification title
	 *	'lang_params'		array parameters for the language key (optional)
	 *	'url'				string url the notification links to
	 *	'email_template'	string Titania email template name
	 *	'email_vars'		array variables for the email template (optional)
	 *	'actor_id'			int user shown as the notification's actor (optional)
	 */
	public function send_notifications($type, array $type_data)
	{
		// A stored notification url must never carry a session id
		if (!empty($type_data['url']))
		{
			$type_data['url'] = $this->path_helper->strip_url_params($type_data['url'], 'sid');
		}

		$this->notification_manager->add_notifications('phpbb.titania.notification.type.' . $type, $type_data);
	}
}
