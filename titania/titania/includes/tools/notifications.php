<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

/**
 * Class hanldes sending notifications to users either via email, PM or both.
 * 
 * Documentation:
 * 
 * // First setup object.
 * $notify = new titania_notifications(titania_notifications::NOTIFY_PM);
 * 
 * // Add a user to the address list. You can also add an array of users. See method for more info. Also chain on the subject, messsage 
 * // and then send the message and that it! Method chaning is avaibable, however you dont have to do it.
 * $notify->add_address(2)->set_subject('Subject')->set_message('Testing Notification class')->send_message();
 * 
 * Notes: Once a message is sent, the address list, messsage subject and 
 * message are all reset unless reset_class is false when sending message.
 *
 */
class titania_notifications
{
	/**
	 * Message text.
	 *
	 * @var string
	 */
	public $message_text = '';

	/**
	 * Message subject
	 *
	 * @var unknown_type
	 */
	public $message_title = '';

	/**
	 * Who wants this message?
	 * 
	 * To modify this variable, you must use add_address.
	 * Ex:
	 *
	 *	$this->address_list = array(
	 *		2	=> array(
	 *			'email'					=> 'user@email.com',
	 *			'username'				=> 'Admin',
	 *			'notification_type'		=> notifications::NOTIFY_EMAIL
	 *		),
	 *	);
	 *
	 * @var array
	 */
	private $address_list = array();

	/**
	 * Who sent this message?
	 *
	 * @var array
	 */
	public $sender_data = array();

	/**
	 * How should be notify the user?
	 *
	 * @var unknown_type
	 */
	public $notification_type;

	/**
	 * If we are sending an email, we need a template!
	 * Ex: email_example.txt
	 *
	 * /@todo Be able to load email templates from any directory.
	 * 
	 * @var string
	 */
	private $email_tpl = '';

	/**
	 * Notifications methods. For now we only support email and PM. We make include Jabber and others later.
	 *
	 */
	const NOTIFY_EMAIL 	= 0;
	const NOTIFY_PM		= 1;
	const NOTIFY_BOTH 	= 2;

	/**
	 * Sets-up notification class. If notify tpye is NOTIFY_EMAIL or NOTIFY_BOTH, $email_tpl must be set.
	 *
	 * @param const $notify_type notifications::NOTIFY_EMAIL or notifications::NOTIFY_PM. This is the default notifications. If a user
	 * has a notification type set, that method will be used instead.
	 * @param string $email_tpl Email Template file
	 */
	public function __construct($notify_type, $email_tpl = '')
	{
		// Silly goose, we have to have an email template.
		if ($notify_type === NOTIFY_EMAIL || $notify_type === NOTIFY_BOTH && !$email_tpl)
		{
			trigger_error('An email template must be set when sending a user an email.', E_USER_ERROR);
		}

		// Set class variables.
		$this->notification_type = $notify_type;
		$this->email_tpl = $email_tpl;
	}

	/**
	 * Sends out message to current address list.
	 *
	 * @param unknown_type $override_notification_type
	 * 
	 * @return false on error, true on success
	 */
	public function send_messages($override_notification_type = true, $reset_class = true)
	{
		if (!$this->message_text || !$this->message_title)
		{
			if (DEBUG_EXTRA)
			{	
				trigger_error('Notification can not be sent with no message or title');
			}
			else 
			{
				return false;
			}
		}
		
		// We loop through out address list and send message.
		foreach ($this->address_list as $user_id => $row)
		{
			// Are we forcing a notification type or do we have an empty user defined notifcation type
			if ($override_notification_type || !$row['notify_type'])
			{
				// We send this user a notification based on our default type.
				if ($this->notification_type === self::NOTIFY_EMAIL || $this->notification_type === self::NOTIFY_BOTH)
				{
					$this->send_email();
				}

				if ($this->notification_type === self::NOTIFY_PM || $this->notification_type === self::NOTIFY_BOTH)
				{
					$this->send_pm($user_id);
				}
			}
		}

		// Do we want a fresh address list?
		if ($reset_class)
		{
			// Yes... You now know nobody...
			$this->reset_class();
		}
		
		return true;
	}

	/**
	 * Adds one or more address to the address list for the message. $user_id can be an array of multiple users.
	 * @link $this->address_list for format.
	 *
	 * @param array || int $user_id
	 * @param string $user_email
	 * @param string $username
	 * @param constant $notify_type
	 *
	 * @return this object
	 */
	public function add_address($user_id, $user_email = '', $username = '', $notify_type = false)
	{
		if (is_array($user_id))
		{
			$this->address_list = array_merge($this->address_list, $user_id);
		}
		else
		{
			$this->address_list[$user_id] = array(
				'email'				=> $user_email,
				'username'			=> $username,
				'notify_type'		=> $notify_type
			);
		}

		return $this;
	}

	/**
	 * Sets notification subject
	 *
	 * @param string $subject
	 * @return this object
	 */
	public function set_subject($subject)
	{
		global $user;

		$this->message_title = (isset($user->lang[$subject])) ? $user->lang[$subject] : $subject;

		return $this;
	}

	/**
	 * Sets notification message
	 *
	 * @param string $message
	 * @return this object
	 */
	public function set_message($message)
	{
		global $user;

		$this->message_text = (isset($user->lang[$message])) ? $user->lang[$message] : $message;

		return $this;
	}

	/**
	 * Gets all founders from DB and adds them to the address list.
	 *
	 * @return object notifications object
	 *
	 */
	private function add_founders()
	{
		global $db, $cache;

		if ($founder_ary = $cache->get('_founders') === false)
		{
			// Grab user data for all founders.
			$sql = 'SELECT user_id, username, user_email, user_lang, user_notify_type
					FROM ' . USERS_TABLE . '
					WHERE user_type = ' . USER_FOUNDER;
			$result = $db->sql_query($sql);

			$founder_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$founder_ary[$row['user_id']] = array(
					'email'					=> $row['user_email'],
					'username'				=> $row['username'],
					'notification_type'		=> $row['user_notify_type']
				);
			}
			$db->sql_freeresult($result);

			// Cache results and set address list to the founder list.
			$cache->put('_founders', $founder_ary);
			$this->address_list = $founder_ary;
		}
		else
		{
			foreach ($founder_ary as $user_id => $row)
			{
				$this->address_list[$user_id] = array(
					'email'					=> $row['user_email'],
					'username'				=> $row['username'],
					'notification_type'		=> $row['user_notify_type']
				);
			}
		}

		return $this;
	}

	/**
	 * Sends user(s) notification via PM
	 *
	 */
	private function send_pm($to_id)
	{
		global $phpbb_root_path, $phpEx, $user;

		if (!class_exists('parse_message'))
		{
			include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		}
		if (!function_exists('submit_pm'))
		{
			include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		}

		// Setup the PM message parser.
		$message_parser = new parse_message();
		$message_parser->message = $this->message_text;
		$message_parser->parse(true, true, true, true, true, true, true);

		// setup the PM data...
		$pm_data = array(
			'from_user_id'		=> 2, // @todo Find other user_id's we can use for this...
			'from_username'		=> 'Site Notifications', // @todo Find a different username we can use for this...
			'address_list'		=> array('u' => array($to_id => 'to')),
			'icon_id'			=> 0,
			'from_user_ip'		=> $user->ip, // @todo Maybe make this 0.0.0.0 or localhost IP
			'enable_bbcode'		=> true,
			'enable_smilies'	=> true,
			'enable_urls'		=> true,
			'enable_sig'		=> false,
			'message'			=> $message_parser->message,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
		);

		// Send the PM to the founders.
		submit_pm('post', $this->message_title, $pm_data, false);
	}

	/**
	 * Sends out an email.
	 *
	 * @todo Needs major work still...
	 */
	private function send_email()
	{
		global $user;

		trigger_error('Send Email method is still under development ;)'. E_USER_ERROR);
		
		// setup the e-mail for the founders
		if (!class_exists('messenger'))
		{
			include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		}

		$messenger = new messenger(false);

		// use the specified email language template according tho this users' language settings.
		$messenger->template($this->email_tpl, $user->data['lang']);

		// Set the "to" header.
		// @todo Set email 
//		$messenger->to(NOTIFICATION_EMAIL, 'Greg');

		// E-mail subject
		$messenger->subject(htmlspecialchars_decode($this->subject));

		// set some X-AntiAbuse headers, may not be needed but...
		$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
		$messenger->headers('X-AntiAbuse: User_id - ' . $user_id);
		$messenger->headers('X-AntiAbuse: Username - ' . $username);
		$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

		// Assign variables for the MVC to be used in the e-mail template
		$messenger->assign_vars(array(
			'TO_USERNAME'	=> 'Greg Head', //@todo
			'MESSAGE'		=> $message,
			'SUBJECT'		=> $subject,
			'AMOUNT'		=> $this->data['mc_gross'],
			'PAYER_EMAIL'	=> $this->data['payer_email'],
			'PAYER_USERNAME'=> ($this->sender_data) ? $this->sender_data['username'] : $this->data['first_name'],
			'VERIFY'		=> ($this->verified) ?  $user->lang['TRANSACTION_VERIFIED'] : sprintf($user->lang['TRANSACTION_NOT_VERIFIED'], $this->page . '?action=validate&amp;txn_id=' . $this->data['txn_id']),
		));

		// Now send the e-mail message
		$messenger->send(NOTIFY_EMAIL);
	}

	/**
	 * Resets our address list so we dont need a new instance of the notification class.
	 *
	 */
	private function reset_address_list()
	{
		$this->address_list = array();
	}
	
	private function reset_class()
	{
		$this->reset_address_list();
		$this->set_subject(false)->set_message(false);
	}
}
?>