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

namespace phpbb\titania\controller\manage;

class administration extends base
{
	/**
	* List administration tools.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function list_tools()
	{
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$this->setup();

		$this->template->assign_vars(array(
			'S_ACTION'		=> $this->helper->route('phpbb.titania.administration'),
		));
		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		return $this->helper->render('manage/administration.html', 'ADMINISTRATION');
	}

	/**
	* Run requested tool.
	*
	* @param string $tool		Tool identifier.
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function run_tool($tool)
	{
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$this->setup();

		$this->load_tool($tool);
		$submit = $this->request->is_set_post('submit');
		$error = array();

		if ($submit)
		{
			// In run_tool do whatever is required.  If there is an error, put it into the array and the display options will be ran again
			$this->tool->run_tool($error);
		}
		return $this->display_tool($error);
	}

	/**
	* Load tool.
	*
	* @param string $tool	Tool identifier
	* @throws \Exception	Throws \Exception if tool is not active.
	* @return null
	*/
	protected function load_tool($tool)
	{
		// Load the tool
		$this->tool = $this->plugin->load_tool($tool);

		// Can we use this tool?
		if (method_exists($this->tool, 'tool_active'))
		{
			if (($msg = $this->tool->tool_active()) !== true)
			{
				if (!$msg)
				{
					$msg = 'TOOL_NOT_AVAILABLE';
				}
				throw new \Exception($this->user->lang($msg));
			}
		}
	}

	/**
	* Build tool page.
	*
	* @param array $error		Array containing any errors to output.
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function display_tool($error)
	{
        // Instead of building a page yourself you may return an array with the options you want to show. 
        // This is outputted similar to how the acp_board is.
       	// You may also send back a string if you just want a confirm box shown with that string used for the title
		$options = $this->tool->display_options();

		if (is_array($options) && isset($options['vars']))
		{
			return $this->build_tool_page($options['title'], $options['vars'], $error);
		}
		else if (is_string($options))
		{
			$this->confirm_action($options);
		}
		else
		{
			redirect($this->helper->route('phpbb.titania.administration'));
		}
	}

	/**
	* Automatically build tool page given tool's display options.
	*
	* @param string $title		Page title.
	* @param array $options		Page options.
	* @param array $error		Any errors to output.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function build_tool_page($title, $options, $error)
	{
		\titania::_include('functions_manage', 'use_lang');

		// Go through each error and see if the key exists in the $this->user->lang.  If it does, use that.
		if (!empty($error))
		{
			array_walk($error, 'use_lang');
		}

		$this->template->assign_vars(array(
			'L_TITLE'			=> $this->user->lang($title),
			'L_TITLE_EXPLAIN'	=> $this->user->lang($title . '_EXPLAIN'),

			'S_ERROR'			=> !empty($error),
			'ERROR_MSG'			=> (!empty($error)) ? implode('<br />', $error) : '',
		));

		foreach ($options as $name => $data)
		{
			if ($this->is_legend_option($name))
			{
				$this->assign_legend_option($data);
			}
			else if (is_array($vars))
			{
				$this->assign_complex_option($data);
			}
		}

		return $this->helper->render('manage/tool_options.html', $title);
	}

	/**
	* Check whether the option is a legend.
	*
	* @param string $name		Option name
	* @return bool
	*/
	protected function is_legend_option($name)
	{
		return strpos($name, 'legend') !== false;
	}

	/**
	* Assign legend option to template.
	*
	* @param string $title		Option title.
	* @return null
	*/
	protected function assign_legend_option($title)
	{
		$this->template->assign_block_vars('options', array(
			'S_LEGEND'		=> true,
			'LEGEND'		=> $this->user->lang($title),
		));
	}

	/**
	* Assign "complex option to template.
	*
	* @param string $name		Option name.
	* @param array $data		Option data.
	*
	* @return null
	*/
	protected function assign_complex_option($name, $data)
	{
		$l_explain = '';

		if ($data['explain'])
		{
			$l_explain = (isset($data['lang_explain'])) ? $data['lang_explain'] : $data['lang'] . '_EXPLAIN';
			$l_explain = $this->user->lang($l_explain);
		}

		$properties = explode(':', $data['type']);
		$content = build_cfg_template($properties, $name, $data);

		if (!empty($content))
		{
			$this->template->assign_block_vars('options', array(
				'KEY'			=> $name,
				'TITLE'			=> $this->user->lang($vars['lang']),
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content['tpl'],
			));
		}
	}

	/**
	* Handle action confirmation.
	*
	* @param string $title		Confirmation title.
	* @return null
	*/
	protected function confirm_action($title)
	{
		$submit = $this->request->is_set('submit', \phpbb\request\request_interface::GET);
		$hash = $this->request->variable('hash', '');

		if (confirm_box(true) || ($submit && check_link_hash($hash, 'manage')))
		{
			$this->tool->run_tool();
		}
		else
		{
			confirm_box(false, $title);
		}
	}

	/**
	* Check user's authorization.
	*
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth()
	{
		return $this->auth->acl_get('u_titania_admin');
	}

	/**
	* Run common initial tasks.
	*
	* @return null
	*/
	protected function setup()
	{
		$this->user->add_lang('acp/common');

		// Setup the plugin manager
		\titania::_include('manage_tools/manage_plugin', false, 'manage_plugin');
		$this->plugin = new \manage_plugin($this->helper);
	}
}
