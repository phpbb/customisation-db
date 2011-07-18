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

class titania_email
{
	/**
	 * Ignore this for now!
	 * Moving it to the side for later...
	 */
	public function email_friend()
	{
		phpbb::$user->add_lang('memberlist');

		if (!phpbb::$config['email_enable'])
		{
			titania::error_box('ERROR', 'EMAIL_DISABLED', TITANIA_ERROR, HEADER_SERVICE_UNAVAILABLE);

			return false;
		}

		if (!phpbb::$user->data['is_registered'] || phpbb::$user->data['is_bot'] || !phpbb::$auth->acl_get('u_sendemail'))
		{
			if (phpbb::$user->data['user_id'] == ANONYMOUS)
			{
				login_box(titania::$page, phpbb::$user->lang['ERROR_CONTRIB_EMAIL_FRIEND']);
			}

			titania::error_box('ERROR', 'ERROR_CONTRIB_EMAIL_FRIEND', TITANIA_ERROR, HEADER_FORBIDDEN);

			return false;
		}

		// Are we trying to abuse the facility?
		if (titania::$time - phpbb::$user->data['user_emailtime'] < phpbb::$config['flood_interval'])
		{
			trigger_error('FLOOD_EMAIL_LIMIT', E_USER_NOTICE);
		}

		$name		= utf8_normalize_nfc(request_var('name', '', true));
		$email		= request_var('email', '');
		$email_lang	= request_var('lang', phpbb::$config['default_lang']);
		$message	= utf8_normalize_nfc(request_var('message', '', true));
		$cc			= (isset($_POST['cc_email'])) ? true : false;
		$submit		= (isset($_POST['submit'])) ? true : false;

		add_form_key('contrib_email');

		phpbb::$template->assign_vars(array(
			'S_LANG_OPTIONS'	=> language_select($email_lang),
			'S_POST_ACTION'		=> phpbb::append_sid(titania::$page, array('id' => 'email', 'contrib_id' => $this->contrib_id)),
		));

		$error = array();

		if ($submit)
		{
			if (!check_form_key('contrib_email'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!$email || !preg_match('/^' . get_preg_expression('email') . '$/i', $email))
			{
				$error[] = 'EMPTY_ADDRESS_EMAIL';
			}

			if (!$name)
			{
				$error[] = 'EMPTY_NAME_EMAIL';
			}

			if (!empty($error))
			{
				titania::error_box('ERROR', $error, TITANIA_ERROR);

				return false;
			}

			phpbb::_include('functions_messenger', false, 'messenger');

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_emailtime = ' . titania::$time . '
				WHERE user_id = ' . (int) phpbb::$user->data['user_id'];
			$result = phpbb::$db->sql_query($sql);

			$mail_to_users = array();
			$mail_to_users[] = array(
				'email_lang'	=> $email_lang,
				'email'			=> $email,
				'name'			=> $name,
			);

			// Ok, now the same email if CC specified, but without exposing the users email address
			if ($cc)
			{
				$mail_to_users[] = array(
					'email_lang'	=> phpbb::$user->data['user_lang'],
					'email'			=> phpbb::$user->data['user_email'],
					'name'			=> phpbb::$user->data['username'],
				);
			}

			$lang_path = phpbb::$user->lang_path;
			phpbb::$user->set_custom_lang_path(titania::$config->language_path);

			$messenger = new messenger(false);

			foreach ($mail_to_users as $row)
			{
				$messenger->template('contrib_recommend', $row['email_lang']);
				$messenger->replyto(phpbb::$user->data['user_email']);
				$messenger->to($row['email'], $row['name']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . phpbb::$config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . (int) phpbb::$user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . phpbb::$user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . phpbb::$user->ip);

				$messenger->assign_vars(array(
					'BOARD_CONTACT'	=> phpbb::$config['board_contact'],
					'TO_USERNAME'	=> htmlspecialchars_decode($name),
					'FROM_USERNAME'	=> htmlspecialchars_decode(phpbb::$user->data['username']),
					'MESSAGE'		=> htmlspecialchars_decode($message),

					'CONTRIB_NAME'	=> htmlspecialchars_decode($this->contrib_name),
					'U_CONTRIB'		=> phpbb::append_sid(titania::$page, array('contrib_id' => $this->contrib_id, 'id' => 'details'), true, ''),
				));

				$messenger->send(NOTIFY_EMAIL);
			}

			phpbb::$user->set_custom_lang_path($lang_path);

			titania::error_box('SUCCESS', 'EMAIL_SENT', TITANIA_SUCCESS);

			return true;
		}

		return false;
	}
}
