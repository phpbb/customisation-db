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

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string */
	protected $users_table;

	/** @var string */
	protected $watch_table;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	const EMAIL = 1;
	const WATCH = 2;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\path_helper $path_helper
	 * @param string $users_table
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\path_helper $path_helper, $users_table, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->path_helper = $path_helper;
		$this->users_table = $users_table;
		$this->watch_table = TITANIA_WATCH_TABLE;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
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

		if ($is_subscribed)
		{
			$action = 'unsubscribe';
			$lang_key = 'UN' . $lang_key;
		}

		$params = array(
			'subscribe'	=> $action,
			'hash'		=> generate_link_hash($action),
		);

		$this->template->assign_vars(array(
			'IS_SUBSCRIBED'			=> $is_subscribed,

			'U_SUBSCRIBE'			=> $this->path_helper->append_url_params($url, $params),
			'L_SUBSCRIBE_TYPE'		=> $this->user->lang($lang_key),
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
	 * Send Notifications
	 *
	 * Using this function:
	 * Call this function when you know the Object type, object id, and the email
	 * template name.
	 * Sample usage:
	 *
	 * <code>
	 *
	 * $object_type = SOME_OBJECT_CONSTANT_TYPE;
	 * $obhect)id = 242974;
	 *
	 * titania_subscriptions::send_notifications($object_type, $object_id, 'mod_subscribe', array(
	 * 		'OBJECT_NAME'	=> 'Some MOD',
	 * ));
	 *
	 * </code>
	 *
	 * The vars parameter will be used in the messanger assign vars, which will act
	 * as the common vars when sending out the notifications. Data such as the MOD's
	 * or Style's name should go here, what action was taken, etc. The usernaeme and
	 * emails of the recepiants will be personalised by the function. Ensure the
	 * email template has the {USERNAME} var present.
	 *
	 * @param $exclude_user User_id of the one who posted the item to exclude them from the sending
	 *
	 */
	public function send_notifications($object_type, $object_id, $email_tpl, $vars, $exclude_user = false)
	{
		$sql = 'SELECT w.watch_user_id, w.watch_type, u.user_id, u.username, u.user_email, u.user_lang
			FROM ' . $this->watch_table . ' w, ' . $this->users_table . ' u
			WHERE w.watch_user_id = u.user_id ';

		if (is_array($object_type) || is_array($object_id))
		{
			// Both needs to be arrays if one is and they need to have the same number of elements.
			if (!is_array($object_type) || !is_array($object_id) || sizeof($object_type) != sizeof($object_id))
			{
				return;
			}

			$sql_objects = '';
			foreach ($object_type as $key => $value)
			{
				$sql_objects .= (($sql_objects == '') ? '' : ' OR ') . '(w.watch_object_type = ' . (int) $value . '
							AND w.watch_object_id = ' . (int) $object_id[$key] . ')';
			}
			$sql .= 'AND (' . $sql_objects . ')';

			unset($sql_objects);
		}
		else
		{
			$sql .= 'AND w.watch_object_type = ' . (int) $object_type . '
						AND w.watch_object_id = ' . (int) $object_id;
		}
		$sql .= ($exclude_user) ? ' AND w.watch_user_id <> ' . (int) $exclude_user : '';

		$result = $this->db->sql_query($sql);

		// Throw everything here
		$user_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Use user_id for the keys to not send duplicates.
			$user_data[$row['user_id']] = array(
				'username'		=> $row['username'],
				'user_email'	=> $row['user_email'],
				'user_lang'		=> $row['user_lang'],
				'watch_type'	=> $row['watch_type'],
			);
		}
		$this->db->sql_freeresult($result);

		// No one subscribed? We're done.
		if (empty($user_data))
		{
			return;
		}
		$messenger = null;

		// Send to each user
		// Add a new case statment for each subscription type
		foreach ($user_data as $data)
		{
			/*
			* Switch between the types.
			* ------------------------------------------
			* When adding a type, the final message will
			* be stored in $message, and the subject is
			* stored in $vars['SUBJECT'].
			*/
			switch($data['watch_type'])
			{
				case self::EMAIL:

					if ($messenger === null)
					{
						// Only make the object if we need it
						if (!class_exists('\messenger'))
						{
							require($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
						}
						$messenger = new \messenger;
					}

					$messenger->anti_abuse_headers($this->config, $this->user);
					$messenger->template('@phpbb_titania/' . $email_tpl, $data['user_lang']);
					$messenger->to($data['user_email'], $data['username']);
					$messenger->assign_vars(array_merge($vars, array(
						'USERNAME'			=> $data['username'],
					)));

					$messenger->send();
					$messenger->save_queue();
				break;
			}
		}

		return;
	}
}
