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

require_once(PHPBB_ROOT_PATH . 'includes/functions_module.' . PHP_EXT);

/**
 * @package modules
 */
class titania_modules extends p_master
{
	public $include_path;

	/**
	 * Constructor to set the custom module include path
	 */
	public function __construct()
	{
		global $phpbb_root_path;

		$this->include_path = $phpbb_root_path . 'includes/';
	}

	/**
	* Function to set custom module include path (able to use directory outside of phpBB)
	*
	* @param string $include_path include path to be used.
	* @access public
	*/
	function set_custom_include_path($include_path)
	{
		$this->include_path = $include_path;

		if (substr($this->include_path, -1) != '/')
		{
			$this->include_path .= '/';
		}
	}

	/**
	* Loads currently active module
	*
	* This method loads a given module, passing it the relevant id and mode.
	*/
	public function load_active($mode = false, $module_url = false, $execute_module = true)
	{
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $user;

		$module_path = $this->include_path . $this->p_class;
		$icat = request_var('icat', '');

		if ($this->active_module === false)
		{
			trigger_error('Module not accessible', E_USER_ERROR);
		}

		if (!class_exists("{$this->p_class}_$this->p_name"))
		{
			if (!file_exists("$module_path/{$this->p_class}_$this->p_name.$phpEx"))
			{
				trigger_error("Cannot find module $module_path/{$this->p_class}_$this->p_name.$phpEx", E_USER_ERROR);
			}

			include("$module_path/{$this->p_class}_$this->p_name.$phpEx");

			if (!class_exists("{$this->p_class}_$this->p_name"))
			{
				trigger_error("Module file $module_path/{$this->p_class}_$this->p_name.$phpEx does not contain correct class [{$this->p_class}_$this->p_name]", E_USER_ERROR);
			}

			if (!empty($mode))
			{
				$this->p_mode = $mode;
			}

			// Create a new instance of the desired module ... if it has a
			// constructor it will of course be executed
			$instance = "{$this->p_class}_$this->p_name";

			$this->module = new $instance($this);

			// We pre-define the action parameter we are using all over the place
			if (defined('IN_ADMIN'))
			{
				// Is first module automatically enabled a duplicate and the category not passed yet?
				if (!$icat && $this->module_ary[$this->active_module_row_id]['is_duplicate'])
				{
					$icat = $this->module_ary[$this->active_module_row_id]['parent'];
				}

				// Not being able to overwrite ;)
				$this->module->u_action = append_sid("{$phpbb_admin_path}index.$phpEx", "i={$this->p_name}") . (($icat) ? '&amp;icat=' . $icat : '') . "&amp;mode={$this->p_mode}";
			}
			else
			{
				// If user specified the module url we will use it...
				if ($module_url !== false)
				{
					$this->module->u_action = $module_url;
				}
				else
				{
					$this->module->u_action = $phpbb_root_path . (($user->page['page_dir']) ? $user->page['page_dir'] . '/' : '') . $user->page['page_name'];
				}

				$this->module->u_action = append_sid($this->module->u_action, "i={$this->p_name}") . (($icat) ? '&amp;icat=' . $icat : '') . "&amp;mode={$this->p_mode}";
			}

			// Add url_extra parameter to u_action url
			if (!empty($this->module_ary) && $this->active_module !== false && $this->module_ary[$this->active_module_row_id]['url_extra'])
			{
				$this->module->u_action .= $this->module_ary[$this->active_module_row_id]['url_extra'];
			}

			// Assign the module path for re-usage
			$this->module->module_path = $module_path . '/';

			// Execute the main method for the new instance, we send the module id and mode as parameters
			// Users are able to call the main method after this function to be able to assign additional parameters manually
			if ($execute_module)
			{
				$this->module->main($this->p_name, $this->p_mode);
			}

			return;
		}
	}
}

?>