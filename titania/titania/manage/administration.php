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

if (!phpbb::$auth->acl_gets('u_titania_admin'))
{
	titania::needs_auth();
}

phpbb::$user->add_lang('acp/common');

// Setup the plugin manager
titania::_include('manage_tools/manage_plugin', false, 'manage_plugin');
$plugin = new manage_plugin();

$submit = (isset($_POST['submit'])) ? true : false;

phpbb::$template->assign_vars(array(
	'S_ACTION'		=> titania_url::build_url('manage/administration', (($plugin->tool_id) ? array('t' => $plugin->tool_id) : array())),
));

// Does the user want to run a tool?
if ($plugin->tool_id)
{
	// Load the tool
	$tool = $plugin->load_tool($plugin->tool_id);

	// Can we use this tool?
	if (method_exists($tool, 'tool_active'))
	{
		if (($msg = $tool->tool_active()) !== true)
		{
			if ($msg === false)
			{
				$msg = phpbb::$user->lang['TOOL_NOT_AVAILABLE'];
			}
			else
			{
				$msg = isset(phpbb::$user->lang[$msg]) ? phpbb::$user->lang[$msg] : $msg;
			}

			trigger_error($msg);
		}
	}

	$error = array();
	if ($submit)
	{
		// In run_tool do whatever is required.  If there is an error, put it into the array and the display options will be ran again
		$tool->run_tool($error);
	}

	if (!$submit || !empty($error))
	{
        /*
        * Instead of building a page yourself you may return an array with the options you want to show.  This is outputted similar to how the acp_board is.
        * You may also send back a string if you just want a confirm box shown with that string used for the title
        */
		$options = $tool->display_options();

		if (is_array($options) && isset($options['vars']))
		{
			titania::page_header($options['title']);

			titania::_include('functions_manage', 'use_lang');

			// Go through each error and see if the key exists in the phpbb::$user->lang.  If it does, use that.
			if (!empty($error))
			{
				array_walk($error, 'use_lang');
			}

			phpbb::$template->assign_vars(array(
				'L_TITLE'			=> phpbb::$user->lang[$options['title']],
				'L_TITLE_EXPLAIN'	=> (isset(phpbb::$user->lang[$options['title'] . '_EXPLAIN'])) ? phpbb::$user->lang[$options['title'] . '_EXPLAIN'] : '',

				'S_ERROR'			=> (!empty($error)) ? true : false,
				'ERROR_MSG'			=> (!empty($error)) ? implode('<br />', $error) : '',
			));

			foreach ($options['vars'] as $name => $vars)
			{
				if (!is_array($vars) && strpos($name, 'legend') === false)
				{
					continue;
				}

				if (strpos($name, 'legend') !== false)
				{
					phpbb::$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> (isset(phpbb::$user->lang[$vars])) ? phpbb::$user->lang[$vars] : $vars)
					);

					continue;
				}

				$type = explode(':', $vars['type']);

				$l_explain = '';
				if ($vars['explain'] && isset($vars['lang_explain']))
				{
					$l_explain = (isset(phpbb::$user->lang[$vars['lang_explain']])) ? phpbb::$user->lang[$vars['lang_explain']] : $vars['lang_explain'];
				}
				else if ($vars['explain'])
				{
					$l_explain = (isset(phpbb::$user->lang[$vars['lang'] . '_EXPLAIN'])) ? phpbb::$user->lang[$vars['lang'] . '_EXPLAIN'] : '';
				}

				$content = build_cfg_template($type, $name, $vars);

				if (empty($content))
				{
					continue;
				}

				phpbb::$template->assign_block_vars('options', array(
					'KEY'			=> $name,
					'TITLE'			=> (isset(phpbb::$user->lang[$vars['lang']])) ? phpbb::$user->lang[$vars['lang']] : $vars['lang'],
					'S_EXPLAIN'		=> $vars['explain'],
					'TITLE_EXPLAIN'	=> $l_explain,
					'CONTENT'		=> $content['tpl'],

					// Find user link
					'S_FIND_USER'	=> (isset($content['find_user'])) ? true : false,
					'U_FIND_USER'	=> (isset($content['find_user'])) ? phpbb::append_sid('memberlist', array('mode' => 'searchuser', 'form' => 'select_user', 'field' => 'username', 'select_single' => 'true', 'form' => 'stk', 'field' => $content['find_user_field'])) : '',
				));
			}

			titania::page_footer(true, 'manage/tool_options.html');
		}
		else if (is_string($options))
		{
			if (titania::confirm_box(true) || (isset($_GET['submit']) && check_link_hash(request_var('hash', ''), 'manage')))
			{
				$tool->run_tool();
			}
			else
			{
				titania::confirm_box(false, $options, titania_url::build_url('manage/administration', array('t' => $plugin->tool_id)));
			}
		}
		else
		{
			// The page should have been setup by the tool.  We will exit to prevent the redirect from below.
			exit;
		}
	}

	// Should never get here...
	redirect(titania_url::build_url('manage/administration'));
}
else
{
	titania::page_header('ADMINISTRATION');
	titania::page_footer(true, 'manage/administration.html');
}

function trigger_back($message)
{
	$message = (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message;

	$message .= '<br /><br /><a href="' . titania_url::build_url('manage/administration') . '">' . phpbb::$user->lang['BACK'] . '</a>';

	trigger_error($message);
}