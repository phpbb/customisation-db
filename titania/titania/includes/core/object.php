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

/**
* Basic class providing basic magic methods.
*
* @package Titania
*/
abstract class titania_object
{
	/**
	* Object data
	*
	* @var	array[string]mixed		Associative array holding the properties of this class.
	*/
	protected $object_data = array();

	/**
	* Property configuration
	*
	* @var	array[string][string]mixed		Associative array with property name (key)
	* 										and associative configuration array (value).
	*
	*
	* Example 1:
	* 	Type will be determined from 'default'.
	* 	Length will be automatically truncated to the correct value.
	* 	Property can not be set via the setter method. It is readonly protected.
	* 	Multibyte characters will be removed.
	*
	* <code>
	* 	$object_config = array(
	* 		'property_name' => array(
	* 			'default' => 'teh_test_string',
	* 			'max' => 20,
	* 			'readonly' => true,
	* 			'multibyte' => false,
	* 		),
	* 	);
	* </code>
	*
	*	Note: 'max' and 'multibyte' only works when the object
	*		is an instance of titania_database_object.
	*
	*
	* Example 2:
	* 	Type will be determinded from 'type'.
	*
	* <code>
	* 	$object_config = array(
	* 		'property_name' => array(
	* 			'default' => false,
	* 			'type' => 'int',
	* 		),
	* 	);
	* </code>
	*/
	public $object_config = array();

	/**
	* Catches calles to non-existing methods.
	* Allows you to use generic getter and setter methods.
	*
	* @param	string	$name		Property name
	* @param	array	$arguments	Arguments array
	* @return	mixed
	*/
	public function __call($name, $arguments)
	{
		if (sizeof($arguments) != 1)
		{
			throw new UnknownMethodException($name);
		}

		$prefix		= substr($name, 0, 4);
		$property	= substr($name, 4);

		if ($prefix == 'get_')
		{
			return $this->__get($property);
		}
		else if ($prefix == 'set_')
		{
			// Check readonly attribute.
			if (isset($this->object_config[$property]['readonly']) && $this->object_config[$property]['readonly'])
			{
				throw new SetReadOnlyPropertyException($property);
			}
			else
			{
				return $this->__set($property, $arguments[0]);
			}
		}

		throw new UnknownMethodException($name);
	}

	/**
	* Get an object property. Catches calles to non-existing properties.
	* Allows you to read properties via <code>$this->property_name</code>.
	* Leave the & at the beginning to allow things like $this->property_name[] = 'new'; (or it will cause errors)
	*
	* @param	string	$name	Property name
	* @return	mixed			Property value
	*
	* Note: This method should only be used inside of classes.
	*/
	public function &__get($name)
	{
		if (isset($this->object_data[$name]))
		{
			// Return property value.
			return $this->object_data[$name];
		}
		else if (isset($this->object_config[$name]['default']))
		{
			// Return property default value.
			return $this->object_config[$name]['default'];
		}
		else
		{
			throw new UnknownPropertyException($name);
		}
	}

	/**
	* Mass get object properties
	*
	* @return	array[string]mixed
	*
	* Note: Method name is reserved for magic methods.
	*/
	public function __get_array()
	{
		$array = array();
		foreach ($this->object_config as $name => $null)
		{
			$array[$name] = $this->__get($name);
		}

		return $array;
	}

	/**
	* Set an object property. Catches calles to non-existing properties.
	* Allows you to write properties via <code>$this->property_name</code>.
	*
	* @param	string	$name	Property name
	* @param	mixed	$value	Property value
	* @return	void
	*
	* Note: This method should only be used inside of classes.
	*/
	public function __set($name, $value)
	{
		if (isset($this->object_config[$name]['type']))
		{
			$type = $this->object_config[$name]['type'];
		}
		else if (isset($this->object_config[$name]['default']) && !is_array($this->object_config[$name]['default']))
		{
			$type = gettype($this->object_config[$name]['default']);
		}

		$this->object_data[$name] = $value;

		if (isset($type))
		{
			settype($this->object_data[$name], $type);
		}
	}

	/**
	* Mass set object properties
	*
	* @param	array[string]mixed	$array	Array with properties
	* @return	void
	*
	* Note: Method name is reserved for magic methods.
	*/
	public function __set_array($array)
	{
		foreach ($array as $key => $value)
		{
			$this->__set($key, $value);
		}
	}

	/**
	* Fetches isset() and empty() calls. Checks if a property is set
	*
	* @param	string	$name	Property name
	* @return	boolean
	*/
	public function __isset($name)
	{
		if (isset($this->object_data[$name]))
		{
			return true;
		}

		return false;
	}

	/**
	* Fetches unset() calls. Unsets the property value
	*
	* @param	string	$name	Property name
	* @return	void
	*
	* Note: The default property might be returned, even if unset() has been called.
	*/
	public function __unset($name)
	{
		if (isset($this->object_data[$name]))
		{
			unset($this->object_data[$name]);
		}
		else
		{
			throw new UnknownPropertyException($name);
		}
	}

	/**
	* Checks if a property value is equal to it's default value
	*
	* @param	string		Property name
	* @return	bool
	*/
	protected function is_default($name)
	{
		if (!isset($this->object_data[$name]))
		{
			return true;
		}

		if (!isset($this->object_config[$name]['default']))
		{
			return false;
		}

		if ($this->object_data[$name] === $this->object_config[$name]['default'])
		{
			return true;
		}

		return false;
	}
}

/**
* Exception thrown when a property does not exist.
*
* @package Titania
*/
class UnknownPropertyException extends Exception
{
	function __construct($name, $code = 0)
	{
		parent::__construct('Unknown property: ' . $name, $code);
	}
}

/**
* Exception thrown when a method does not exist.
*
* @package Titania
*/
class UnknownMethodException extends Exception
{
	function __construct($name, $code = 0)
	{
		parent::__construct('Method ' . $name . ' does not exist', $code);
	}
}

/**
* Exception thrown when someone tries to set a read-only protected property.
*
* @package Titania
*/
class SetReadOnlyPropertyException extends Exception
{
	function __construct($name, $code = 0)
	{
		parent::__construct('Property ' . $name . ' is read-only protected.', $code);
	}
}
