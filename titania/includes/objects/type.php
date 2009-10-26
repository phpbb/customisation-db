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
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_database_object'))
{
	require TITANIA_ROOT . 'includes/core/object_database.' . PHP_EXT;
}

class titania_type extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table			= TITANIA_TYPES_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field			= 'type_id';

	/**
	 * Holds the types in an array with the type id being the key
	 *
	 * @var unknown_type
	 */
	public $types = array();

	/**
	 * Holds the types in an array with the key index being the type name.
	 *
	 * @var array
	 */
	public $types_type_name = array();

	public function __construct()
	{
		// Set the types that we have installed
		$this->set_types();

		// Install any new types
		$this->install_types();
	}

	public function build_type_selection($selected = false)
	{
		phpbb::$template->assign_block_vars('type_select', array(
			'S_IS_SELECTED'		=> ($selected === false) ? true : false,

			'VALUE'				=> 0,
			'NAME'				=> phpbb::$user->lang['SELECT_CONTRIB_TYPE'],
		));

		foreach ($this->types as $key => $type)
		{
			phpbb::$template->assign_block_vars('type_select', array(
				'S_IS_SELECTED'		=> ($selected === false) ? true : false,

				'VALUE'				=> $key,
				'NAME'				=> phpbb::$user->lang[strtoupper($type['type_name'])],
			));
		}
	}

	/**
	 * Custom getter
	 *
	 * Gets a type id based on a type name.
	 *
	 * Example: $this->type_modification
	 *
	 * @param string $name
	 */
	public function __get($name)
	{
		if (strpos($name, 'type_') !== false)
		{
			$name = str_replace('type_', '', $name);

			if (isset($this->types_type_name[$name]))
			{
				// Return property value.
				return $this->types_type_name[$name]['type_id'];
			}
			else
			{
				throw new UnknownPropertyException($name);
			}
		}
		else
		{
			parent::__get($name);
		}
	}

	/**
	 * Install contrib types
	 */
	private function install_types()
	{
		$dh = @opendir(TITANIA_ROOT . 'includes/types/');

		if (!$dh)
		{
			// Ah bummmer...
			trigger_error('Could not open the types directory', E_USER_ERROR);
		}

		$sql_ary = array();
		// Read some files
		while (($fname = readdir($dh)) !== false)
		{
			if (strpos($fname, '.' . PHP_EXT) && substr($fname, 0, 1) != '_' && $fname != 'base.' . PHP_EXT)
			{
				$class_name = 'titania_type_' . substr($fname, 0, strpos($fname, '.' . PHP_EXT));

				if (!class_exists($class_name))
				{
					include(TITANIA_ROOT . 'includes/types/' . $fname);
				}

				$type_data = call_user_func($class_name . '::get_type_data');

				if (isset($this->types_type_name[$type_data['type_slug']]))
				{
					continue;
				}
				$sql_ary[] = $type_data;
			}
		}

		// Bye directory!
		closedir($dh);

		// Do we have some sql data?
		if (sizeof($sql_ary))
		{
			phpbb::$db->sql_multi_insert(TITANIA_TYPES_TABLE, $sql_ary);

			// Purge that cache
			titania::$cache->destroy('_titania_types');

			// New types installed so set them in our object again
			$this->set_types();
		}
	}

	private function set_types()
	{
		$this->types = titania::$cache->get_types();

	 	// Now create array with names being the index
	 	foreach ($this->types as $type)
	 	{
	 		$this->types_type_name[$type['type_slug']] = $type;
	 	}
	}
}