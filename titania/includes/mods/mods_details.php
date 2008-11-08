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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* mods_details
* Class for Details module
* @package details
*/
class mods_details extends titania_object
{
	public $p_master;
	public $u_action;

	/**
	 * Constructor
	 */
	public function __construct($p_master)
	{
		global $user;

		$this->p_master = $p_master;

		$this->page = $user->page['script_path'] . $user->page['page_name'];
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $user, $template, $cache;

		// complete the hack to allow our modules to be loaded from the Titania/includes directory.
		$phpbb_root_path = PHPBB_ROOT_PATH;

		$user->add_lang(array('titania_mods'));

		$mod_id	= request_var('mod', 0);
		$submit	= isset($_POST['submit']) ? true : false;

		$form_key = 'mods_details';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'styles':
			break;

			case 'translations':
			break;

			case 'email':
				$this->tpl_name = 'mods/mod_email';
				$this->page_title = 'MOD_EMAIL';

				$this->mod_email($mod_id);
			break;

			case 'changes':
			break;

			case 'preview':
			break;

			case 'screenshots':
			break;

			case 'details':
			default:
				$this->tpl_name = 'mods/mod_details';
				$this->page_title = 'MODS_DETAILS';

				$this->mod_details($mod_id);
			break;
		}
	}

	public function mod_details($mod_id)
	{
		global $db, $template, $user;

		$sql_ary = array(
			'SELECT'	=> 'c.*, a.author_id, a.author_username, u.user_colour',
			'FROM'		=> array(CUSTOMISATION_CONTRIBS_TABLE => 'c'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.author_id = c.contrib_author_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = a.user_id',
				),
			),
			'WHERE'		=> 'c.contrib_id = ' . (int) $mod_id . '
							AND c.contrib_status = ' .  STATUS_APPROVED,
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchfield($result);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'MOD_ID'		=> $row['contrib_id'],
			'MOD_TITLE'		=> $row['contrib_name'],
			'MOD_DESC'		=> $row['contrib_description'],
			'RATING'		=> round($row['contrib_rating'], 2),
			'DOWNLOADS'		=> $row['contrib_downloads'],
			'ADDED'			=> $user->format_date($row['contrib_release_date']),
			'UPDATED'		=> $user->format_date($row['contrib_update_date']),
			'VERSION'		=> $row['contrib_version'],
			'AUTHOR'		=> sprintf($user->lang['AUTHOR_BY'], get_username_string('full', $row['author_id'], $row['author_username'], $row['user_colour'], false, $profile_url)),
		));
	}

	public function mod_email($mod_id)
	{
		global $config, $auth, $db, $phpbb_root_path, $template, $user;

		// $user->add_lang('memberlist');

		if (!$config['email_enable'])
		{
			trigger_error('EMAIL_DISABLED');
		}

		if ($user->data['is_registered'] || !$user->data['is_bot'] || !$auth->acl_get('u_sendemail'))
		{
			trigger_error('NO_EMAIL_MOD');
		}

		// Are we trying to abuse the facility?
		if (time() - $user->data['user_emailtime'] < $config['flood_interval'])
		{
			trigger_error('FLOOD_EMAIL_LIMIT');
		}

		$sql = 'SELECT c.contrib_id, c.contrib_name FROM ' . CUSTOMISATION_CONTRIBS_TABLE . ' c
				WHERE c.contrib_id = ' . (int) $mod_id . '
				AND c.contrib_status = ' .  STATUS_APPROVED;
		$result = $db->sql_query($sql);
		$mod = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$mod)
		{
			titania::error_box('ERROR', $user->lang['MOD_NOT_FOUND'], ERROR_ERROR);
			$this->main('', 'list');
			return;
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
					WHERE user_id = ' . $user->data['user_id'];
				$result = $db->sql_query($sql);

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
						'email_lang'		=> $user->data['user_lang'],
						'email'				=> $user->data['user_email'],
						'name'				=> $user->data['username'],
						'username'			=> $user->data['username'],
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
						'U_MOD'			=> generate_board_url(true) . $this->page . '?mode=details&mod=' . $mod_id, // @todo Not sure if this is the correct url
					));

					$messenger->send(NOTIFY_EMAIL);
				}

				titania::error_box('SUCCESS', $user->lang['EMAIL_SENT'], ERROR_SUCCESS);
				$this->main('', 'details'); // @todo What should the value of $id be?
				return;
			}
		}

		$template->assign_vars(array(
			'MOD_TITLE'		=> $mod['contrib_name'],

			'ERROR_MESSAGE'		=> (sizeof($error)) ? implode('<br />', $error) : '',

			'S_LANG_OPTIONS'	=> language_select($email_lang),
			'S_POST_ACTION'		=> $this->u_action . '&amp;mod=' . $mod_id,
		));
	}
}