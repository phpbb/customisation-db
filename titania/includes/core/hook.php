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

	public function register_ary($prefix, $definitions, $mode = 'normal')
	{
		foreach ($definitions as $definition)
		{
			$this->register($definition, $prefix . ((is_array($definition)) ? implode('_', $definition) : $definition), $mode);
		}
	}

	/**
	* Calling all functions/methods attached to a specified hook by reference (only the first argument allows for a reference)
	*
	* @param mixed $definition Declaring function (with __FUNCTION__) or class with array(__CLASS__, __FUNCTION__)
	* @param mixed $args The arguments to be used as reference
	*/
	function call_hook_ref($definition, &$reference)
	{
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		if (!empty($this->hooks[$class][$function]))
		{
			// Developer tries to call a hooked function within the hooked function...
			if ($this->current_hook !== NULL && $this->current_hook['class'] === $class && $this->current_hook['function'] === $function)
			{
				return false;
			}

			// Call the hook with the arguments attached and store result
			$arguments = func_get_args();
			$this->current_hook = array('class' => $class, 'function' => $function);
			$arguments[0] = &$this;
			$arguments[1] = &$reference;

			// Call the hook chain...
			if (isset($this->hooks[$class][$function]['standalone']))
			{
				$hook = $this->hooks[$class][$function]['standalone'];
				if (function_exists($hook))
				{
					$function_call = '$this->hook_result[$class][$function] = ' . $hook . '(';
					for ($i = 0; $i < sizeof($arguments); $i++)
					{
						$function_call .= '$arguments[' . $i . '], ';
					}
					$function_call = substr($function_call, 0, -2) . ');';

					eval($function_call);
				}
			}
			else
			{
				foreach (array('first', 'normal', 'last') as $mode)
				{
					if (!isset($this->hooks[$class][$function][$mode]))
					{
						continue;
					}

					foreach ($this->hooks[$class][$function][$mode] as $hook)
					{
						if (function_exists($hook))
						{
							$function_call = '$this->hook_result[$class][$function] = ' . $hook . '(';
							for ($i = 0; $i < sizeof($arguments); $i++)
							{
								$function_call .= '$arguments[' . $i . '], ';
							}
							$function_call = substr($function_call, 0, -2) . ');';

							eval($function_call);
						}
					}
				}
			}

			$this->current_hook = NULL;
			return true;
		}

		$this->current_hook = NULL;
		return false;
	}
}