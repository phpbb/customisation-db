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
if (!defined('IN_PHPBB') || !defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_object'))
{
	require(TITANIA_ROOT . 'class_base_object.' . $phpEx);
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
	* @var		string
	*/
	protected $sql_table;

	/**
	* Unique field in $this->sql_table which we can use to identify
	* records.. it's usually the primary key of the table
	*
	* @var		string
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
	* @return	void
	*/
	public function submit()
	{
		$identifier = $this->sql_id_field;

		if (!$this->$identifier)
		{
			$this->insert();
		}
		else
		{
			$this->update();
		}
	}

	/**
	* Updates $this->sql_table with object data identified by $this->sql_id_field
	*
	* @return	void
	*/
	public function update()
	{
		$sql_array = array();
		foreach ($this->sql_data as $key => $value)
		{
			if ($key == $this->sql_id_field || $this->$key == $value)
			{
				continue;
			}

			$sql_array[$key] = $this->validate($this->$key, $this->object_config[$key]);
		}

		if (!sizeof($sql_array))
		{
			return;
		}

		global $db;

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . $db->sql_build_array('UPDATE', $sql_array) . '
			WHERE ' . $this->sql_id_field . ' = ' . $this->{$this->sql_id_field};
		$db->sql_query($sql);
	}

	/**
	* Inserts object data into $this->sql_table.
	* Sets the identifier property to the correct id.
	*
	* @return	void
	*/
	public function insert()
	{
		global $db;

		$sql_array = array();
		foreach ($this->object_config as $name => $null)
		{
			$sql_array[$name] = $this->validate($this->$name, $this->object_config[$name]);
		}

		$sql = 'INSERT INTO ' . $this->sql_table . ' ' . $db->sql_build_array('INSERT', $sql_array);
		$db->sql_query($sql);

		$this->{$this->sql_id_field} = $db->sql_nextid();
	}

	/**
	* Gets object data from the database and sets the properties.
	*
	* @return	void
	*/
	public function load()
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . $this->sql_table . '
			WHERE ' . $this->sql_id_field . ' = ' . $this->{$this->sql_id_field};
		$result = $db->sql_query($sql);
		$this->sql_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		foreach ($this->sql_data as $key => $value)
		{
			if ($key == $this->sql_id_field || !isset($this->object_config[$key]))
			{
				continue;
			}

			$this->$key = $value;
		}
	}

	/**
	* Deletes an object identified by $this->sql_id_field
	*
	* @return	void
	*/
	public function delete()
	{
		global $db;

		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE ' . $this->sql_id_field . ' = ' . $this->{$this->sql_id_field};
		$db->sql_query($sql);
	}

	/**
	* Function data has to pass before entering the database.
	*
	* @param string $value
	* @param string $config
	*
	* @return	mixed
	*/
	protected function validate($value, $config)
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
	* @return	string
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
			// No multibyte, allow only ASCII (0-127)
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
		if (isset($config['max']) && $config['max'] < strlen($value))
		{
			$value = substr($value, 0, $config['max']);
		}

		return $value;
	}
}
