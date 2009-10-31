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

/**
* Class to titania faq.
* @package Titania
*/
class titania_tag extends titania_database_object
{
	// Tag types constants
	const TYPE_CUSTOM_TAG		= 0;
	const TYPE_CATEGORY 		= 1;
	const TYPE_COMPONENT	 	= 2;
	const TYPE_COMPLEXITY		= 3;

	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= TITANIA_TAG_FIELDS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'tag_id';

	/**
	 * Constructor class for titania faq
	 *
	 * @param int $faq_id
	 */
	public function __construct($tag_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'tag_id'			=> array('default' => 0, 'readonly' => true),
			'tag_type_id'		=> array('default' => titania_tag::TYPE_CATEGORY),
			'tag_contrib_type'	=> array('default' => 0),
			'tag_field_name'	=> array('default' => '', 'max' => 255),
			'tag_clean_name' 	=> array('default' => '', 'readonly' => true),
			'tag_field_desc'	=> array('default' => ''),
			'tag_items'			=> array('default' => 0),
		));

		if ($tag_id !== false)
		{
			$this->tag_id = $tag_id;
			parent::load();
		}
	}

	/**
	 *
	 */
	public function create_tag()
	{

	}

	public function validate_tags()
	{

	}

	public function build_cat_selection($types, $selected)
	{
		foreach ($types as $type)
		{
			// Asign the type
			phpbb::$template->assign_block_vars('category_select', array(
				'S_SELECTED'		=> false,
				'S_DISABLED'		=> true,

				'VALUE'				=> $type['type_id'],
				'NAME'				=> (isset(phpbb::$user->lang[$type['type_name']])) ? phpbb::$user->lang[$type['type_name']] : $type['type_name'],
			));

			$categories = titania::$cache->get_categories();

			foreach ($categories as $category)
			{
				if ($category['tag_contrib_type'] == $type['type_id'] && $category)
				{
					phpbb::$template->assign_block_vars('category_select', array(
						'S_SELECTED'		=> (in_array($category['tag_id'], $selected)) ? true : false,

						'VALUE'				=> $category['tag_id'],
						'NAME'				=> (isset(phpbb::$user->lang[$category['tag_field_name']])) ? phpbb::$user->lang[$category['tag_field_name']] : $category['tag_field_name'],
					));
				}
			}
		}
	}

	/**
	 * Tags an item
	 *
	 * Throws UnknownTagCategory if the tag id does not exist.
	 * Throws InsertError if unable to insert data into database.
	 *
	 * @param int $item_id		The item to tag
	 * @param int $tag_id		The tag id to tag the item as
	 * @return bool 			true if successful
	 */
	public function tag_item($item_id, $tag_id)
	{
		$backup = self::set_contrib_tags_config($item_id, $tag_id);

		// Insert data
		$result = parent::insert();

		$this->object_config = $backup;
		unset($backup);

		$this->sql_table		= TITANIA_TAG_FIELDS_TABLE;
		$this->update_item_count($tag_id);

		return true;
	}

	private function update_item_count($tag_id, $action = 'add')
	{
		// Reset object and load the tag.
		$this->tag_id = $tag_id;
		parent::load();

		switch ($action)
		{
			case 'add' :
				// Add an item
				$this->tag_items++;
			break;
			case 'remove':
				// Remove an item
				$this->tag_items--;
			break;
		}

		parent::update();

		titania::$cache->destroy('_titania_categories');
	}

	/**
	 * Removes a tag from a item
	 *
	 * @param int $item_id		The item to tag
	 * @param int $tag_id		The tag id to tag the item as
	 */
	public function delete_taged_item($item_id, $tag_id)
	{
		$backup = $this->set_contrib_tags_config($item_id, $tag_id);

		// Create sql to delete a taged item. Sadly we can not use parent::delete because we need to match the contrib id
		// the tag id.
		$sql = 'DELETE FROM ' . $this->sql_table . ' WHERE tag_id = ' . $this->tag_id . ' AND contrib_id = ' .$this->item_id;
		phpbb::$db->sql_query($sql);

		return phpbb::$db->query_result;
	}

	/**
	* Assign the common items to the template
	*
	* @param bool $return True to return the array of stuff to display and output yourself, false to output to the template automatically
	*/
	public function assign_display($return = false)
	{
		$display = array(
			'CATEGORY_NAME'		=> (isset(phpbb::$user->lang[$this->tag_field_name])) ? phpbb::$user->lang[$this->tag_field_name] : $this->tag_field_name,
			'CATEGORY_CONTRIBS'	=> $this->tag_items,
//			'CATEGORY_TYPE'		=> $this->tag_items,

			'U_VIEW_CATEGORY'	=> titania::$url->build_url($this->get_url()),
		);

		if ($return)
		{
			return $display;
		}

		phpbb::$template->assign_vars($display);
	}

	/**
	* Build view URL for a category
	*/
	public function get_url()
	{
		$url = $this->tag_clean_name . '-' . $this->tag_id;

		return $url;
	}

	/**
	 * Sets the config data for the TITANIA_CONTRIB_TAGS_TABLE. The method calling this is responsible for
	 * restoring the backup data!
	 *
	 * @param int $item_id		The item to tag
	 * @param int $tag_id		The tag id to tag the item as
	 * @return array $backup	The old object data backup.
	 */
	private function set_contrib_tags_config($item_id = 0, $tag_id = 0)
	{
		if ($tag_id !== 0)
		{
			$this->validate_tag_id($tag_id);
		}

		// Create a backup of object config
		$backup = $this->object_config;

		// Define new object data and table name since we are only tagging an item.
		$this->object_config = array(
			'contrib_id'		=> array('default' => (int) $item_id),
			'tag_id'			=> array('default' => (int) $tag_id),
		);

		$this->sql_table = TITANIA_CONTRIB_TAGS_TABLE;

		return $backup;
	}

	private function validate_tag_id($tag_id)
	{
		$categories = titania::$cache->get_categories();

		if (isset($categories[$tag_id]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
