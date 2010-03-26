<?php
/**
*
* @package Titania
* @version $Id: subscriptions.php SyntaxError90 $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	 */
	public static function send_notifications($object_type, $object_id, $email_tpl, $vars)
	{
		$sql = 'SELECT w.watch_user_id, w.watch_type, u.user_id, u.username, u.user_email
				FROM ' . TITANIA_WATCH_TABLE . ' w, ' . USERS_TABLE . ' u,
				WHERE w.watch_user_id = u.user_id
				AND w.watch_object_type = ' . (int) $object_type . '
				AND w.watch_object_id = ' . (int) $object_id;

		$result = phpbb::$db->sql_query($sql);

		// Throw everything here
		$user_data = array();
		while($row = phpbb::$db->sql_fetchrow($result))
		{
			$user_data[] = array(
				'username'		=> $row['username'],
				'user_email'	=> $row['user_email'],
				'watch_type'	=> $row['watch_type'],
			);
		}

		// No one subscribed? We're done.
		if(!sizeof($user_data))
		{
			return;
		}

		// You wanted the email template parsed? Well here you go.
		$template = file_get_contents(TITANIA_ROOT . 'language/en/email/' . $emial_tpl);
		foreach($vars as $var => $replace)
		{
			if(strtoupper($var) == 'SUBJECT')
			{
				continue;
			}

			str_replace('{' . strtoupper($var) . '}', $replace, $template);
		}

		// Send to each user
		// Add a new case statment for each subscription type
		foreach($user_data as $data)
		{
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
					if(!isset($messenger))
					{
						$messenger = new messenger();
					}

					$messenger->template('subscribe_generic', 'en');
					$messenger->from('nobody@phpbb.com', 'Titania Mailer'); // @TODO - Make this not hardcoded.
					$messenger->to($data['user_email'], $data['username']);

					$messenger->assign_vars(array_merge($vars, array(
						'SUBJECT'			=> $vars['SUBJECT'],
						'MESSAGE'			=> $message,
				//		'EMAIL_SIG'			=> '', // @TODO - Email Sig
					)));

					$messenger->send();

				break;
			}
		}

		return;
	}
}

?>