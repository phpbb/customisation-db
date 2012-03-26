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

if (!class_exists('titania_object'))
{
	require TITANIA_ROOT . 'includes/core/object.' . PHP_EXT;
}

/**
* Class providing basic database operations.
*
* @package Titania
*/
abstract class titania_database_object extends titania_object
{
	/**
	* SQL table our fields will reside in
	*
	* @var	string
	*/
	protected $sql_table;

	/**
	* Unique field in $this->sql_table which we can use to identify
	* records.. it's usually the primary key of the table
	*
	* @var	string
	*/
	protected $sql_id_field;

	/**
	* Array holding data we got from the db.
	*
	* @var	array[string]mixed
	*/
	protected $sql_data = array();

	/**
	* Triggers an insert or an update depending on presence of $this->sql_id_field
	*
	* @return	bool		true on success, else false
	*/
	public function submit()
	{
		$identifier = $this->sql_id_field;

		if (!$this->$identifier)
		{
			return $this->insert();
		}
		else
		{
			return $this->update();
		}
	}

	/**
	* Updates $this->sql_table with object data identified by $this->sql_id_field
	*
	* @return	bool		true on success, else false
	*/
	public function update()
	{
		$sql_array = array();
		foreach ($this->object_config as $name => $config_array)
		{
			// No need to update the sql identifier
			if ($name == $this->sql_id_field)
			{
				continue;
			}

			$property_value = $this->validate_property($this->$name, $config_array);

			// Property value has not changed
			// Comparison with == is fine
			if (isset($this->sql_data[$name]) && $this->sql_data[$name] == $property_value)
			{
				continue;
			}

			$sql_array[$name] = $property_value;
		}

		if (empty($sql_array))
		{
			return false;
		}

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_array) . '
			WHERE ' . $this->sql_id_field . ' = ' . $this->{$this->sql_id_field};
		phpbb::$db->sql_query($sql);

		// Merge sql data back
		$this->sql_data = array_merge($this->sql_data, $sql_array);

		if (phpbb::$db->sql_affectedrows())
		{
			return true;
		}

		return false;
	}

	/**
	* Inserts object data into $this->sql_table.
	* Sets the identifier property to the correct id.
	*
	* @return	bool		true on success, else false
	*/
	public function insert()
	{
		$sql_array = array();
		foreach ($this->object_config as $name => $null)
		{
			$sql_array[$name] = $this->validate_property($this->$name, $this->object_config[$name]);
		}

		$sql = 'INSERT INTO ' . $this->sql_table . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_array);
		phpbb::$db->sql_query($sql);

		if ($id = phpbb::$db->sql_nextid())
		{
			$this->{$this->sql_id_field} = $id;

			return true;
		}

		return false;
	}

	/**
	* Gets object data from the database and sets the properties.
	*
	* @return	bool		true when object found, else false
	*/
	public function load($sql_id = false)
	{
		if ($sql_id !== false)
		{
			$this->{$this->sql_id_field} = (int) $sql_id;
		}

		if (!$this->{$this->sql_id_field})
		{
			return false;
		}

		$sql = 'SELECT ' . implode(', ', array_keys($this->object_config)) . '
			FROM ' . $this->sql_table . '
			WHERE ' . $this->sql_id_field . ' = ' . (int) $this->{$this->sql_id_field};
		$result = phpbb::$db->sql_query($sql);
		$this->sql_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (empty($this->sql_data))
		{
			return false;
		}

		foreach ($this->sql_data as $key => $value)
		{
			$this->$key = $value;
		}

		return true;
	}

	/**
	* Deletes an object identified by $this->sql_id_field
	*
	* @return	int		rows deleted
	*/
	public function delete()
	{
		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE ' . $this->sql_id_field . ' = ' . $this->{$this->sql_id_field};
		phpbb::$db->sql_query($sql);

		// Unset the sql indentifier field
		unset($this->{$this->sql_id_field});

		return phpbb::$db->sql_affectedrows();
	}

	/**
	* Set the SQL data
	* This should only be used when you are loading just this item from the database (do not modify anything before sending it to this)!
	*
	* @param mixed $sql_data
	*/
	public function set_sql_data($sql_data)
	{
		$this->sql_data = $sql_data;
	}

	/**
	* Function data has to pass before entering the database.
	*
	* @param	mixed				$value		Value to validate
	* @param	array[string]mixed	$config		Configuration array
	*
	* @return	mixed
	*/
	protected function validate_property($value, $config)
	{
		if (is_string($value))
		{
			$value = $this->validate_string($value, $config);
		}

		return $value;
	}

	/**
	* Private function strings have to pass before entering the database.
	* Ensures string length et cetera.
	*
	* @param	string	$value		The string we want to validate
	* @param	array	$config		The configuration array we're validating against
	*
	* @return	string				The validated string
	*/
	private function validate_string($value, $config)
	{
		if (empty($value))
		{
			return '';
		}

		// Check if multibyte characters are disallowed
		if (isset($config['multibyte']) && $config['multibyte'] === false)
		{
			// No multibyte, only allow ASCII (0-127)
			$value = preg_replace('/[\x80-\xFF]/', '', $value);
		}
		else
		{
			// Make sure multibyte characters are wellformed
			if (!preg_match('/^./u', $value))
			{
				return '';
			}
		}

		// Truncate to the maximum length
		if (isset($config['max']) && $config['max'])
		{
			phpbb::_include('functions_content', 'truncate_string');

			truncate_string($value, $config['max']);
		}

		return $value;
	}
}
