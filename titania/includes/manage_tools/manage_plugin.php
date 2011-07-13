<?php
/**
*
* @package Support Toolkit - Plugin handler
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
*
*/

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

// Load functions_admin.php if required
if (!function_exists('filelist'))
{
	include(PHPBB_ROOT_PATH . 'includes/functions_admin.' . PHP_EXT);
}

class manage_plugin
{
	/**
	 * A list containing file and directory names that should be ignored
	 *
	 * @var array
	 * @access private
	 */
	var $ignore_tools = array('index.htm', 'manage_plugin.php');

	/**
	 * List containing all available tools and in which category they belong.
	 * On default it contains the "main" category
	 *
	 * @var array
	 * @access private
	 */
	var $plugin_list = array();

	/**
	 * Path to the tools directory
	 *
	 * @var String
	 * @access public
	 */
	var $tool_box_path = '';

	var $tool_id = '';

	/**
	 * Constructor
	 * Load the list with available plugins and assign them in the correct category
	 */
	function manage_plugin()
	{
		// Set the path
		$this->tool_box_path = TITANIA_ROOT . 'includes/manage_tools/';

		// Create a list with tools
		$filelist = filelist($this->tool_box_path, '', PHP_EXT);

		// Need to do some sanitization on the result of filelist
		foreach ($filelist as $tools)
		{
			// Don't want the extension
			foreach ($tools as $tool)
			{
				if (in_array($tool, $this->ignore_tools))
				{
					continue;
				}

				$this->plugin_list[] = (($pos = strpos($tool, '.' . PHP_EXT)) !== false) ? substr($tool, 0, $pos) : $tool;
			}
		}

		// Get the requested cat and tool
		$this->tool_id = request_var('t', '');

		// Check if they want to use a tool or not, make sure that the tool name is legal, and make sure the tool exists
		if (!$this->tool_id || preg_match('#([^a-zA-Z0-9_])#', $this->tool_id) || !file_exists($this->tool_box_path . $this->tool_id . '.' . PHP_EXT))
		{
			$this->tool_id = '';
		}

		// Make sure the form_key is set
		add_form_key($this->tool_id);

		// Assign the two menus to the template
		$this->gen_left_nav();
	}

	/**
	 * Load the requested tool
	 *
	 * @param String $tool_name The name of this tool
	 * @param Boolean $return Specify whether an object of this tool will be returned
	 * @return The object of the requested tool if $return is set to true else this method will return true
	 */
	function load_tool($tool_name, $return = true)
	{
		global $user;

		static $tools_loaded = array();

		if (isset($tools_loaded[$tool_name]))
		{
			return ($return) ? $tools_loaded[$tool_name] : true;
		}

		$tool_path = $this->tool_box_path . $tool_name . '.' . PHP_EXT;
		if (false === (@include $tool_path))
		{
			trigger_error(sprintf($user->lang['TOOL_INCLUTION_NOT_FOUND'], $tool_path), E_USER_ERROR);
		}

		if (!class_exists($tool_name))
		{
			trigger_error(sprintf($user->lang['INCORRECT_CLASS'], $tool_name, PHP_EXT), E_USER_ERROR);
		}

		// Construct the class
		$tools_loaded[$tool_name] = new $tool_name();

		// Add the language file
		titania::add_lang('manage_tools/' . $tool_name);

		// Return
		return ($return) ? $tools_loaded[$tool_name] : true;
	}

	/**
	 * Build the left "tool" navigation for every page
	 * This is based upon the active tool
	 */
	function gen_left_nav()
	{
		global $template, $user;

		// Grep the correct category
		$tool_list = $this->plugin_list;

		// Run through the tools and collect all info we need
		$tpl_data = array();
		foreach ($tool_list as $tool)
		{
			$class = $this->load_tool($tool);

			// Can this tool be used?
			if (method_exists($class, 'tool_active'))
			{
				if ($class->tool_active() !== true)
				{
					continue;
				}
			}

			// Get the info
			if (method_exists($class, 'info'))
			{
				$info = $class->info();
			}
			else
			{
				// For us lazy people
				$info = array(
					'NAME'			=> (isset($user->lang[strtoupper($tool)])) ? $user->lang[strtoupper($tool)] : strtoupper($tool),
				);
			}

			$tpl_data[$tool] = $info['NAME'];
		}

		// Sort the data based on the tool name. This way we'll keep the menu sorted correctly for translations
		asort($tpl_data);

		// Now go ahead and build the template
		foreach ($tpl_data as $tool => $name)
		{
			$_s_active = ($tool == $this->tool_id) ? true : false;

			// Assign to the template
			$template->assign_block_vars('tools', array(
				'L_TITLE'		=> $name,
				'TOOL'			=> $tool,
				'S_SELECTED'	=> $_s_active,
				'U_TITLE'		=> titania_url::build_url('manage/administration', array('t' => $tool)),
			));
		}
	}
}
?>