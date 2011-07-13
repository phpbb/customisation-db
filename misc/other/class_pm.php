<?php
/**
*
* @package Titania
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

if (!function_exists('send_pm'))
{
	include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
}

/**
* Messenger
* @package phpBB3
*/
class class_pm
{
	var $vars, $msg, $subject;
	var $addresses = array();

	var $tpl_obj = NULL;
	var $tpl_msg = array();
	var $pm_data = array();

	/**
	* Constructor
	*/
	function class_pm()
	{
		global $user;

		$this->subject = $this->msg = '';
		$this->pm_data = array(
			'msg_id'				=> 0,
			'from_user_id'			=> ANONYMOUS,
			'from_user_ip'			=> '127.0.0.1',
			'from_username'			=> 'Guest',
			'reply_from_root_level'	=> 0,
			'reply_from_msg_id'		=> 0,
			'icon_id'				=> 0,
			'enable_sig'			=> 0,
			'enable_bbcode'			=> 1,
			'enable_smilies'		=> 1,
			'enable_urls'			=> 1,
			'bbcode_bitfield'		=> '',
			'bbcode_uid'			=> '',
			'message'				=> '',
			'attachment_data'		=> array(),
			'filename_data'			=> array(),
			'address_list'			=> '',
		);
	}

	/**
	* Resets all the data (address, template file, etc etc) to default
	*/
	function reset()
	{
		$this->addresses = array();
		$this->vars = $this->msg = '';
	}

	/**
	* Sets an email address to send to
	*
	* @param string	$type		'u' for users, 'g' for groups
	* @param string	$receive	'to' or 'bcc'
	*/
	function to($type, $id, $receive = 'to')
	{
		$this->addresses[$type][$id] = $receive;
	}

	/**
	* Set the from address
	*/
	function from($id = ANONYMOUS, $name = '', $ip = '127.0.0.1')
	{
		if (!sizeof($this->pm_data))
		{
			$this->class_pm();
		}

		if ($name == '')
		{
			global $user;
			$name = $user->lang['GUEST'];
		}

		$this->pm_data['from_user_id'] = $id;
		$this->pm_data['from_user_ip'] = '127.0.0.1';
		$this->pm_data['from_username'] = $ip;
	}

	/**
	* set up subject for mail
	*/
	function subject($subject = '')
	{
		$this->subject = trim($subject);
	}

	/**
	* Set email template to use
	*/
	function template($template_file, $template_lang = '', $template_path = '')
	{
		global $config, $phpbb_root_path, $user;

		if (!trim($template_file))
		{
			trigger_error('No template file for private messaging set.', E_USER_ERROR);
		}

		if (!trim($template_lang))
		{
			// fall back to board default language if the user's language is
			// missing $template_file.  If this does not exist either,
			// $tpl->set_custom_template will do a trigger_error
			$template_lang = basename($config['default_lang']);
		}

		// tpl_msg now holds a template object we can use to parse the template file
		if (!isset($this->tpl_msg[$template_lang . $template_file]))
		{
			$this->tpl_msg[$template_lang . $template_file] = new template();
			$tpl = &$this->tpl_msg[$template_lang . $template_file];

			$fallback_template_path = false;

			if (!$template_path)
			{
				$template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
				$template_path .= $template_lang . '/pm';

				// we can only specify default language fallback when the path is not a custom one for which we
				// do not know the default language alternative
				if ($template_lang !== basename($config['default_lang']))
				{
					$fallback_template_path = (!empty($user->lang_path)) ? $user->lang_path : $phpbb_root_path . 'language/';
					$fallback_template_path .= basename($config['default_lang']) . '/pm';
				}
 			}

			$tpl->set_custom_template($template_path, $template_lang . '_pm', $fallback_template_path);

			$tpl->set_filenames(array(
				'body'		=> $template_file . '.txt',
			));
		}

		$this->tpl_obj = &$this->tpl_msg[$template_lang . $template_file];
		$this->vars = &$this->tpl_obj->_rootref;
		$this->tpl_msg = '';

		return true;
	}

	/**
	* Send the mail out to the recipients set previously in var $this->addresses
	*/
	function send($parse_msg, $allow_bbcode = true, $allow_magic_url = true, $allow_smilies = true, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $allow_url_bbcode = true, $update_this_message = true, $mode = 'space', $inc_privmsgs = true)
	{
		if (!sizeof($this->pm_data))
		{
			$this->class_pm();
		}

		global $config, $user;

		// We add some standard variables we always use, no need to specify them always
		if (!isset($this->vars['U_BOARD']))
		{
			$this->assign_vars(array(
				'U_BOARD'	=> generate_board_url(),
			));
		}

		if (!isset($this->vars['EMAIL_SIG']))
		{
			$this->assign_vars(array(
				'EMAIL_SIG'	=> str_replace('<br />', "\n", "-- \n" . htmlspecialchars_decode($config['board_email_sig'])),
			));
		}

		if (!isset($this->vars['SITENAME']))
		{
			$this->assign_vars(array(
				'SITENAME'	=> htmlspecialchars_decode($config['sitename']),
			));
		}

		// Parse message through template
		$this->msg = trim($this->tpl_obj->assign_display('body'));

		// Because we use \n for newlines in the body message we need to fix line encoding errors for those admins who uploaded email template files in the wrong encoding
		$this->msg = str_replace("\r\n", "\n", $this->msg);

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : $user->lang['NO_SUBJECT']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : $user->lang['NO_SUBJECT']);
		}

		if ($drop_header)
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		if ($parse_msg)
		{
			if (!class_exists('parse_message'))
			{
				global $phpbb_root_path, $phpEx;
				include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
			}
			$message_parser = new parse_message();
			$message_parser->message = $this->msg;
			$message_parser->parse($allow_bbcode, $allow_magic_url, $allow_smilies, $allow_img_bbcode, $allow_flash_bbcode, $allow_quote_bbcode, $allow_url_bbcode, $update_this_message, $mode);

			$this->pm_data['bbcode_bitfield'] = $message_parser->bbcode_bitfield;
			$this->pm_data['bbcode_uid'] = $message_parser->bbcode_uid;
			$this->pm_data['message'] = $message_parser->message;
			$this->pm_data['attachment_data'] = $message_parser->attachment_data;
			$this->pm_data['filename_data'] = $message_parser->filename_data;
		}
		else
		{
			$this->pm_data['message'] = $this->msg;
		}
		$this->pm_data['address_list'] = $this->addresses;

		submit_pm('post', $this->subject, $this->pm_data);

		return true;
	}

	/**
	* assign variables to email template
	*/
	function assign_vars($vars)
	{
		if (!is_object($this->tpl_obj))
		{
			return;
		}

		$this->tpl_obj->assign_vars($vars);
	}

	function assign_block_vars($blockname, $vars)
	{
		if (!is_object($this->tpl_obj))
		{
			return;
		}

		$this->tpl_obj->assign_block_vars($blockname, $vars);
	}
}

?>