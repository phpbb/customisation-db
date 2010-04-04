<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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

phpbb::_include('hooks/index', false, 'phpbb_hook');

/**
* Titania Hook Class
*
* Same as phpBB's hook class, but not requiring the hooks to be registered at class construction time
*/
class titania_hook extends phpbb_hook
{
	/**
	* Initialize hook class.
	*/
	public function titania_hook()
	{
		if (function_exists('phpbb_hook_register'))
		{
			phpbb_hook_register($this);
		}
	}

	/**
	* Register function/method to be called within hook
	* This function is normally called by the modification/application to attach/register the functions.
	*
	* @param mixed $definition Declaring function (with __FUNCTION__) or class with array(__CLASS__, __FUNCTION__)
	* @param mixed $hook The replacement function/method to be called. Passing function name or array with object/class definition
	* @param string $mode Specify the priority/chain mode. 'normal' -> hook gets appended to the chain. 'standalone' -> only the specified hook gets called - later hooks are not able to overwrite this (E_NOTICE is triggered then). 'first' -> hook is called as the first one within the chain. 'last' -> hook is called as the last one within the chain.
	*/
	public function register($definition, $hook, $mode = 'normal')
	{
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		switch ($mode)
		{
			case 'standalone':
				if (!isset($this->hooks[$class][$function]['standalone']))
				{
					$this->hooks[$class][$function] = array('standalone' => $hook);
				}
				else
				{
					trigger_error('Hook not able to be called standalone, previous hook already standalone.', E_NOTICE);
				}
			break;

			case 'first':
			case 'last':
				$this->hooks[$class][$function][$mode][] = $hook;
			break;

			case 'normal':
			default:
				$this->hooks[$class][$function]['normal'][] = $hook;
			break;
		}
	}
}