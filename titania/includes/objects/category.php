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

if (!class_exists('titania_message_object'))
{
	require TITANIA_ROOT . 'includes/core/object_message.' . PHP_EXT;
}

/**
 * Class to abstract categories.
 * @package Titania
 */
class titania_category extends titania_message_object
{
	/**
	 * Database table to be used for the contribution object
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_CATEGORIES_TABLE;

	/**
	 * Primary sql identifier for the contribution object
	 *
	 * @var string
	 */
	protected $sql_id_field = 'category_id';

	/**
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = TITANIA_CATEGORY;

	/**
	 * Constructor class for the contribution object
	 */
	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'category_id'					=> array('default' => 0),
			'parent_id'						=> array('default' => 0),
			'left_id'						=> array('default' => 0),
			'right_id'						=> array('default' => 0),

			'category_type'					=> array('default' => 0),
			'category_contribs'				=> array('default' => 0), // Number of items
			'category_visible'				=> array('default' => true),

			'category_name'					=> array('default' => '',	'message_field' => 'subject'),
			'category_name_clean'			=> array('default' => ''),

			'category_desc'					=> array('default' => '',	'message_field' => 'message'),
			'category_desc_bitfield'		=> array('default' => '',	'message_field' => 'message_bitfield'),
			'category_desc_uid'				=> array('default' => '',	'message_field' => 'message_uid'),
			'category_desc_options'			=> array('default' => 7,	'message_field' => 'message_options'),
		));
	}

	/**
	 * Submit data for storing into the database
	 *
	 * @return bool
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->message_parsed_for_storage)
		{
			$this->generate_text_for_storage();
		}

		$this->contrib_name_clean = utf8_clean_string($this->contrib_name);

		// Destroy category parents cache
		titania::$cache->destroy('_titania_category_parents');

		return parent::submit();
	}

	/**
	* Load the category
	*
	* @param int|string $category The category (category_name_clean, category_id)
	*
	* @return bool True if the category exists, false if not
	*/
	public function load($category)
	{
		$sql = 'SELECT * FROM ' . $this->sql_table . ' WHERE ';

		if (is_numeric($category))
		{
			$sql .= 'category_id = ' . (int) $category;
		}
		else
		{
			$sql .= 'category_name_clean = \'' . phpbb::$db->sql_escape(utf8_clean_string($category)) . '\'';
		}
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

		$this->description_parsed_for_storage = true;

		return true;
	}

	/**
	* Build view URL for a category
	*/
	public function get_url()
	{
		$url = '';

		$parent_list = titania::$cache->get_category_parents($this->category_id);

		// Pop the last two categories from the parents and attach them to the url
		$parent_array = array();
		if (!empty($parent_list))
		{
			$parent_array[] = array_pop($parent_list);
		}
		if (!empty($parent_list))
		{
			$parent_array[] = array_pop($parent_list);
		}

		foreach ($parent_array as $row)
		{
			$url .= $row['category_name_clean'] . '/';
		}

		$url .= $this->category_name_clean . '-' . $this->category_id;

		return $url;
	}

	/**
	* Assign the common items to the template
	*
	* @param bool $return True to return the array of stuff to display and output yourself, false to output to the template automatically
	*/
	public function assign_display($return = false)
	{
		$display = array(
			'CATEGORY_NAME'		=> (isset(phpbb::$user->lang[$this->category_name])) ? phpbb::$user->lang[$this->category_name] : $this->category_name,
			'CATEGORY_CONTRIBS'	=> $this->category_contribs,
			'CATEGORY_TYPE'		=> $this->category_type,

			'U_VIEW_CATEGORY'	=> titania_url::build_url($this->get_url()),
		);

		if ($return)
		{
			return $display;
		}

		phpbb::$template->assign_vars($display);
	}
}
