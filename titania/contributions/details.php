<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

titania::add_lang('authors');

// Load the Contrib item
load_contrib();

titania::$contrib->assign_details();

titania::page_header('CONTRIB_DETAILS');
titania::page_footer(true, 'contributions/contribution_details.html');

/**
 * Email a friend
 *
 * @param int $mod_id
 */
 /*function mod_email($mod_id)
{
	phpbb::$user->add_lang(array('memberlist', 'ucp'));

	if (!phpbb::$config['email_enable'])
	{
		titania::error_box('ERROR', phpbb::$user->lang['EMAIL_DISABLED'], TITANIA_ERROR, HEADER_SERVICE_UNAVAILABLE);
		$this->main('details', 'details');
		return;
	}

	if (!phpbb::$user->data['is_registered'] || phpbb::$user->data['is_bot'] || !phpbb::$auth->acl_get('u_sendemail'))
	{
		if (phpbb::$user->data['user_id'] == ANONYMOUS)
		{
			login_box(TITANIA_ROOT . $this->page . '&amp;mod=' . $mod_id, 'NO_EMAIL_MOD');
		}

		titania::error_box('ERROR', phpbb::$user->lang['NO_EMAIL_MOD'], TITANIA_ERROR, HEADER_FORBIDDEN);
		$this->main('details', 'details');
		return;
	}

	// Are we trying to abuse the facility?
	if (time() - phpbb::$user->data['user_emailtime'] < phpbb::$config['flood_interval'])
	{
		trigger_error('FLOOD_EMAIL_LIMIT');
	}

	$sql = 'SELECT c.contrib_id, c.contrib_name
		FROM ' . TITANIA_CONTRIBS_TABLE . ' c
		WHERE c.contrib_id = ' . (int) $mod_id . '
			AND c.contrib_status = ' .  TITANIA_STATUS_APPROVED;
	$result = phpbb::$db->sql_query($sql);
	$mod = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$mod)
	{
		titania::trigger_error('MOD_NOT_FOUND', E_USER_NOTICE, HEADER_NOT_FOUND);
	}

	$error = array();

	$name		= utf8_normalize_nfc(request_var('name', '', true));
	$email		= request_var('email', '');
	$email_lang = request_var('lang', $config['default_lang']);
	$message	= utf8_normalize_nfc(request_var('message', '', true));
	$cc			= (isset($_POST['cc_email'])) ? true : false;
	$submit		= (isset($_POST['submit'])) ? true : false;

	if ($submit)
	{
		if (!check_form_key('mods_details'))
		{
			$error[] = 'FORM_INVALID';
		}

		if (!$email || !preg_match('/^' . get_preg_expression('email') . '$/i', $email))
		{
			$error[] = $user->lang['EMPTY_ADDRESS_EMAIL'];
		}

		if (!$name)
		{
			$error[] = $user->lang['EMPTY_NAME_EMAIL'];
		}

		if (!sizeof($error))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_emailtime = ' . time() . '
				WHERE user_id = ' . phpbb::$user->data['user_id'];
			$result = phpbb::$db->sql_query($sql);

			include_once(PHPBB_ROOT_PATH . 'includes/functions_messenger.' . PHP_EXT);
			$messenger = new messenger(false);

			$mail_to_users = array();

			$mail_to_users[] = array(
				'email_lang'		=> $email_lang,
				'email'				=> $email,
				'name'				=> $name,
				'username'			=> '',
				'to_name'			=> $name,
				'mod_id'			=> $mod['contrib_id'],
				'mod_title'			=> $mod['contrib_name'],
			);

			// Ok, now the same email if CC specified, but without exposing the users email address
			if ($cc)
			{
				$mail_to_users[] = array(
					'email_lang'		=> phpbb::$user->data['user_lang'],
					'email'				=> phpbb::$user->data['user_email'],
					'name'				=> phpbb::$user->data['username'],
					'username'			=> phpbb::$user->data['username'],
					'to_name'			=> $name,
					'mod_id'			=> $mod['contrib_id'],
					'mod_title'			=> $mod['contrib_name'],
				);
			}

			foreach ($mail_to_users as $row)
			{
				$messenger->template('mod_recommend', $row['email_lang']);
				$messenger->replyto($user->data['user_email']);
				$messenger->to($row['email'], $row['name']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

				$messenger->assign_vars(array(
					'BOARD_CONTACT'	=> $config['board_contact'],
					'TO_USERNAME'	=> htmlspecialchars_decode($row['to_name']),
					'FROM_USERNAME'	=> htmlspecialchars_decode($user->data['username']),
					'MESSAGE'		=> htmlspecialchars_decode($message),

					'MOD_TITLE'		=> htmlspecialchars_decode($row['mod_title']),
					'U_MOD'			=> generate_board_url(true) . $this->page . '?mode=details&mod=' . $mod_id,
				));

				$messenger->send(NOTIFY_EMAIL);
			}

			titania::error_box('SUCCESS', 'EMAIL_SENT', TITANIA_SUCCESS);
			$this->main('details', 'details');
			return;
		}
	}

	$template->assign_vars(array(
		'MOD_TITLE'			=> $mod['contrib_name'],

		'ERROR_MESSAGE'		=> (sizeof($error)) ? implode('<br />', $error) : '',

		'S_LANG_OPTIONS'	=> language_select($email_lang),
		'S_POST_ACTION'		=> append_sid($this->page, 'id=details&amp;mode=email&amp;mod=' . $mod_id),
	));
}*/