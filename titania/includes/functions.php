<?php
/**
*
* @package Titania
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

/**
* Error and message handler, call with trigger_error if reqd
*/
function titania_msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $msg_title, $msg_long_text;

	// Do not display notices if we suppress them via @
	if (error_reporting() == 0)
	{
		return;
	}

	// Message handler is stripping text. In case we need it, we are possible to define long text...
	if (isset($msg_long_text) && $msg_long_text && !$msg_text)
	{
		$msg_text = $msg_long_text;
	}

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:

			// Check the error reporting level and return if the error level does not match
			// If DEBUG is defined the default level is E_ALL
			if (($errno & ((defined('DEBUG')) ? E_ALL : error_reporting())) == 0)
			{
				return;
			}

			if (strpos($errfile, 'cache') === false && strpos($errfile, 'template.') === false)
			{
				// flush the content, else we get a white page if output buffering is on
				if ((int) @ini_get('output_buffering') === 1 || strtolower(@ini_get('output_buffering')) === 'on')
				{
					@ob_flush();
				}

				// Another quick fix for those having gzip compression enabled, but do not flush if the coder wants to catch "something". ;)
				if (!empty(phpbb::$config['gzip_compress']))
				{
					if (@extension_loaded('zlib') && !headers_sent() && !ob_get_level())
					{
						@ob_flush();
					}
				}

				// remove complete path to installation, with the risk of changing backslashes meant to be there
				$errfile = str_replace(array(phpbb_realpath(PHPBB_ROOT_PATH), '\\'), array('', '/'), $errfile);
				$msg_text = str_replace(array(phpbb_realpath(PHPBB_ROOT_PATH), '\\'), array('', '/'), $msg_text);

				echo '<b>[phpBB Debug] PHP Notice</b>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";
				// echo '<br /><br />BACKTRACE<br />' . get_backtrace() . '<br />' . "\n";
			}

			return;

		break;

		case E_USER_ERROR:

			if (!empty(phpbb::$user) && !empty(phpbb::$user->lang))
			{
				$msg_text = (!empty(phpbb::$user->lang[$msg_text])) ? phpbb::$user->lang[$msg_text] : $msg_text;
				$msg_title = (!isset($msg_title)) ? phpbb::$user->lang['GENERAL_ERROR'] : ((!empty(phpbb::$user->lang[$msg_title])) ? phpbb::$user->lang[$msg_title] : $msg_title);

				$l_return_index = sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . titania::$absolute_path . '">', '</a>');
				$l_notify = '';

				if (!empty(phpbb::$config['board_contact']))
				{
					$l_notify = '<p>' . sprintf(phpbb::$user->lang['NOTIFY_ADMIN_EMAIL'], phpbb::$config['board_contact']) . '</p>';
				}
			}
			else
			{
				$msg_title = 'General Error';
				$l_return_index = '<a href="' . titania::$absolute_path . '">Return to index page</a>';
				$l_notify = '';

				if (!empty(phpbb::$config['board_contact']))
				{
					$l_notify = '<p>Please notify the board administrator or webmaster: <a href="mailto:' . phpbb::$config['board_contact'] . '">' . phpbb::$config['board_contact'] . '</a></p>';
				}
			}

			garbage_collection();

			// Try to not call the adm page data...

			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
			echo '<head>';
			echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
			echo '<title>' . $msg_title . '</title>';
			echo '<style type="text/css">' . "\n" . '/* <![CDATA[ */' . "\n";
			echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #E4EDF0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
			echo 'a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
			echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
			echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
			echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
			echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #CCCCCC; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
			echo "\n" . '/* ]]> */' . "\n";
			echo '</style>';
			echo '</head>';
			echo '<body id="errorpage">';
			echo '<div id="wrap">';
			echo '	<div id="page-header">';
			echo '		' . $l_return_index;
			echo '	</div>';
			echo '	<div id="acp">';
			echo '	<div class="panel">';
			echo '		<div id="content">';
			echo '			<h1>' . $msg_title . '</h1>';

			echo '			<div>' . $msg_text . '</div>';
			echo '			<div>' . get_backtrace() . '</div>';

			echo $l_notify;

			echo '		</div>';
			echo '	</div>';
			echo '	</div>';
			echo '	<div id="page-footer">';
			echo '		Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>';
			echo '	</div>';
			echo '</div>';
			echo '</body>';
			echo '</html>';

			exit_handler();

			// On a fatal error (and E_USER_ERROR *is* fatal) we never want other scripts to continue and force an exit here.
			exit;
		break;

		case E_USER_WARNING:
		case E_USER_NOTICE:

			define('IN_ERROR_HANDLER', true);

			if (empty(phpbb::$user->data))
			{
				phpbb::$user->session_begin();
			}

			// We re-init the auth array to get correct results on login/logout
			phpbb::$auth->acl(phpbb::$user->data);

			if (empty(phpbb::$user->lang))
			{
				phpbb::$user->setup();
			}

			$msg_text = (!empty(phpbb::$user->lang[$msg_text])) ? phpbb::$user->lang[$msg_text] : $msg_text;
			$msg_title = (!isset($msg_title)) ? phpbb::$user->lang['INFORMATION'] : ((!empty(phpbb::$user->lang[$msg_title])) ? phpbb::$user->lang[$msg_title] : $msg_title);

			if (!defined('HEADER_INC'))
			{
				if (defined('IN_ADMIN') && isset(phpbb::$user->data['session_admin']) && phpbb::$user->data['session_admin'])
				{
					adm_page_header($msg_title);
				}
				else
				{
					titania::page_header($msg_title);
				}
			}

			phpbb::$template->set_filenames(array(
				'body' => 'message_body.html')
			);

			phpbb::$template->assign_vars(array(
				'MESSAGE_TITLE'		=> $msg_title,
				'MESSAGE_TEXT'		=> $msg_text,
				'S_USER_WARNING'	=> ($errno == E_USER_WARNING) ? true : false,
				'S_USER_NOTICE'		=> ($errno == E_USER_NOTICE) ? true : false)
			);

			// We do not want the cron script to be called on error messages
			define('IN_CRON', true);

			if (defined('IN_ADMIN') && isset(phpbb::$user->data['session_admin']) && phpbb::$user->data['session_admin'])
			{
				adm_page_footer();
			}
			else
			{
				titania::page_footer();
			}

			exit_handler();
		break;
	}

	// If we notice an error not handled here we pass this back to PHP by returning false
	// This may not work for all php versions
	return false;
}

/**
* For display of custom parsed text on user-facing pages
* Expects $text to be the value directly from the database (stored value)
*/
function titania_generate_text_for_display($text, $uid, $bitfield, $flags)
{
	static $bbcode;

	if (!$text)
	{
		return '';
	}

	$text = censor_text($text);

	// Parse bbcode if bbcode uid stored and bbcode enabled
	if ($uid && ($flags & OPTION_FLAG_BBCODE))
	{
		if (!class_exists('bbcode'))
		{
			include(PHPBB_ROOT_PATH . 'includes/bbcode.' . PHP_EXT);
		}

		if (empty($bbcode))
		{
			$bbcode = new bbcode($bitfield);
		}
		else
		{
			$bbcode->bbcode($bitfield);
		}

		$bbcode->bbcode_second_pass($text, $uid);
	}

	$text = bbcode_nl2br($text);
	$text = titania_smiley_text($text, !($flags & OPTION_FLAG_SMILIES));

	return $text;
}

function titania_generate_text_for_storage(&$text, &$uid, &$bitfield, &$flags, $allow_bbcode = false, $allow_urls = false, $allow_smilies = false)
{
	return generate_text_for_storage($text, $uid, $bitfield, $flags, $allow_bbcode, $allow_urls, $allow_smilies);
}

function titania_generate_text_for_edit($text, $uid, $flags)
{
	return generate_text_for_edit($text, $uid, $flags);
}

/**
* Smiley processing
*/
function titania_smiley_text($text, $force_option = false)
{
	if ($force_option || !phpbb::$config['allow_smilies'] || !phpbb::$user->optionget('viewsmilies'))
	{
		return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $text);
	}
	else
	{
		return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/(.*?) \/><!\-\- s\1 \-\->#', '<img src="' . titania::$absolute_board . phpbb::$config['smilies_path'] . '/\2 />', $text);
	}
}