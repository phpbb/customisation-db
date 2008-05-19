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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Basic class providing basic magic methods.
* @package Titania
*/
class titania_classbase
{
	// Holds the properties of this class.
	protected $properties = array();

	// Returns a specific property $name
	public function __get($name)
	{
		if (isset($this->properties[$name]))
		{
			return $this->properties[$name];
		}
		else
		{
			throw new exception ('Unknown property: ' . $name);
		}
	}

	// Sets the property $name to $value
	public function __set($name, $value)
	{
		if (isset($this->properties[$name]) && !is_array($this->properties[$name]))
		{
			$type = gettype($this->properties[$name]);
		}

		$this->properties[$name] = $value;

		if (isset($type))
		{
			settype($this->properties[$name], $type);
		}
	}

	// Checks if a property is set
	public function __isset($name)
	{
		if (isset($this->properties[$name]))
		{
			return true;
		}
		
		return false;
	}
}