<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

if (!defined('IN_TITANIA'))
{
	exit;
}

// These lines can be removed/commented out when we get it integrated.
define('SUBSCRIPTION_EMAIL', 1);
define('SUBSCRIPTION_WATCH', 2);

class titania_subscriptions
{
	/**
	* Shorten the amount of code required for some places
	*
	* @param mixed $object_type
	* @param mixed $object_id
	* @param mixed $url
	*/
	public static function handle_subscriptions($object_type, $object_id, $url)
	{
		if (!phpbb::$user->data['is_registered'])
		{
			// Cannot currently handle non-registered users
			return;
		}

		$subscribe = request_var('subscribe', '');
		if ($subscribe == 'subscribe' && check_link_hash(request_var('hash', ''), 'subscribe'))
		{
			titania_subscriptions::subscribe($object_type, $object_id);
		}
		else if ($subscribe == 'unsubscribe' && check_link_hash(request_var('hash', ''), 'unsubscribe'))
		{
			titania_subscriptions::unsubscribe($object_type, $object_id);
		}

		if (titania_subscriptions::is_subscribed($object_type, $object_id))
		{
			phpbb::$template->assign_vars(array(
				'IS_SUBSCRIBED'		=> true,

				'U_SUBSCRIBE'		=> titania_url::append_url($url, array('subscribe' => 'unsubscribe', 'hash' => generate_link_hash('unsubscribe'))),
			));
		}
		else
		{
			phpbb::$template->assign_vars(array(
				'U_SUBSCRIBE'		=> titania_url::append_url($url, array('subscribe' => 'subscribe', 'hash' => generate_link_hash('subscribe'))),
			));
		}
	}

	/*
	 * Is Subscribed
	 */
	public static function is_subscribed($object_type, $object_id, $user_id = false)
	{
		$user_id = ($user_id === false) ? phpbb::$user->data['user_id'] : $user_id;

		$sql = 'SELECT watch_object_id FROM ' . TITANIA_WATCH_TABLE . ' WHERE ' . phpbb::$db->sql_build_array('SELECT', array(
			'watch_object_type'		=> (int) $object_type,
			'watch_object_id'		=> (int) $object_id,
			'watch_user_id'			=> (int) $user_id,
		));

		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		return ($row) ? true : false;
	}

	/*
	 * Subscribe
	 */
	public static function subscribe($object_type, $object_id, $user_id = false, $subscription_type = SUBSCRIPTION_EMAIL)
	{
		$user_id = ($user_id === false) ? phpbb::$user->data['user_id'] : $user_id;

		if(self::is_subscribed($object_type, $object_id, $user_id))
		{
			return false;
		}

		// Build an insert
		$sql = 'INSERT INTO ' . TITANIA_WATCH_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', array(
			'watch_object_type'		=> (int) $object_type,
			'watch_type'			=> (int) $subscription_type,
			'watch_object_id'		=> (int) $object_id,
			'watch_user_id'			=> (int) $user_id,
			'watch_mark_time'		=> time(),
		));

		// Query and we're done
		phpbb::$db->sql_query($sql);

		return true;
	}

	/*
	 * Unsubscribe
	 */
	public static function unsubscribe($object_type, $object_id, $user_id = false)
	{
		$user_id = ($user_id === false) ? phpbb::$user->data['user_id'] : $user_id;

		// Get our delete query
		$sql = 'DELETE FROM ' . TITANIA_WATCH_TABLE . "
				WHERE watch_object_id = " . (int) $object_id . '
					AND watch_user_id = ' .(int) $user_id . '
					AND watch_object_type = ' . (int) $object_type;

		// Query and we're done
		phpbb::$db->sql_query($sql);

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
	public static function send_notifications($object_type, $object_id, $email_tpl, $vars, $exclude_user = false)
	{
		$sql = 'SELECT w.watch_user_id, w.watch_type, u.user_id, u.username, u.user_email, u.user_lang
				FROM ' . TITANIA_WATCH_TABLE . ' w, ' . USERS_TABLE . ' u
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

		$result = phpbb::$db->sql_query($sql);

		// Throw everything here
		$user_data = array();
		while($row = phpbb::$db->sql_fetchrow($result))
		{
			// Use user_id for the keys to not send duplicates.
			$user_data[$row['user_id']] = array(
				'username'		=> $row['username'],
				'user_email'	=> $row['user_email'],
				'user_lang'		=> $row['user_lang'],
				'watch_type'	=> $row['watch_type'],
			);
		}

		// No one subscribed? We're done.
		if(!sizeof($user_data))
		{
			return;
		}

		// Send to each user
		// Add a new case statment for each subscription type
		foreach($user_data as $data)
		{
			// You wanted the email template parsed? Well here you go.
			if (file_exists(TITANIA_ROOT . 'language/' . $data['user_lang'] . '/email/' . $email_tpl))
			{
				$template = file_get_contents(TITANIA_ROOT . 'language/' . $data['user_lang'] . '/email/' . $email_tpl);
			}
			else if (file_exists(TITANIA_ROOT . 'language/' . phpbb::$config['default_lang'] . '/email/' . $email_tpl))
			{
				$template = file_get_contents(TITANIA_ROOT . 'language/' . phpbb::$config['default_lang'] . '/email/' . $email_tpl);
			}
			else
			{
				$template = file_get_contents(TITANIA_ROOT . 'language/en/email/' . $email_tpl);
			}

			foreach($vars as $var => $replace)
			{
				if(strtoupper($var) == 'SUBJECT')
				{
					continue;
				}

				$template = str_replace('{' . strtoupper($var) . '}', $replace, $template);
			}

			// Steal the subject if it exists
			$subject = '';

			if (($subject_start = strpos($template, 'Subject:')) !== false)
			{
				$subject_start += strlen('Subject:');
				$subject_length = strpos($template, "\n", $subject_start) - $subject_start;
				$subject = trim(substr($template, $subject_start, $subject_length));
				$template = trim(substr($template, ($subject_start + $subject_length)));
			}

			$subject = (isset($vars['SUBJECT']) && !$subject) ? $vars['SUBJECT'] : $subject;
			$subject = ($subject) ? $subject : phpbb::$user->lang['SUBSCRIPTION_NOTIFICATION'];

			// Generic messages that will be sent to each module individually
			$message = str_replace('{USERNAME}', $data['username'], $template);

			/*
			* Switch between the types.
			* ------------------------------------------
			* When adding a type, the final message will
			* be stored in $message, and the subject is
			* stored in $vars['SUBJECT'].
			*/
			switch($data['watch_type'])
			{
				case SUBSCRIPTION_EMAIL:

					// Only make the object if we need it
					phpbb::_include('functions_messenger', false, 'messenger');
					$messenger = new messenger();

					$messenger->headers('X-AntiAbuse: Board servername - ' . phpbb::$config['server_name']);
					$messenger->headers('X-AntiAbuse: User_id - ' . phpbb::$user->data['user_id']);
					$messenger->headers('X-AntiAbuse: Username - ' . phpbb::$user->data['username']);

					// HAX
					$user_lang_path = phpbb::$user->lang_path;
					phpbb::$user->lang_path = TITANIA_ROOT . 'language/';

					$messenger->template('subscribe_generic');

					// Reverse HAX
					phpbb::$user->lang_path = $user_lang_path;

					$messenger->to($data['user_email'], $data['username']);

					$messenger->assign_vars(array_merge($vars, array(
						'SUBJECT'			=> $subject,
						'MESSAGE'			=> $message,
				//		'EMAIL_SIG'			=> '', // @TODO - Email Sig
					)));

					$messenger->send();
					$messenger->save_queue();
				break;
			}
		}

		return;
	}
}
