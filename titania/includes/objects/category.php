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

		return true;
	}

	/**
	* Check if a category has child categories
	*
	* @param int $category_id The category id (category_id)
	*
	* @return bool True if the category has child categories, false if not
	*/
	public function get_children($category_id)
	{
		$sql = 'SELECT * FROM ' . $this->sql_table . ' WHERE parent_id = ' . (int) $category_id;

		$result = phpbb::$db->sql_query($sql);
		$this->sql_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (empty($this->sql_data))
		{
			return false;
		}

		return true;
	}

	/**
	* Move category position by $steps up/down
	*/
	public function move_category_by($category_row, $action = 'move_up', $steps = 1)
	{
		/**
		* Fetch all the siblings between the module's current spot
		* and where we want to move it to. If there are less than $steps
		* siblings between the current spot and the target then the
		* module will move as far as possible
		*/
		$sql = 'SELECT category_id, category_name, left_id, right_id
			FROM ' . TITANIA_CATEGORIES_TABLE . "
			WHERE parent_id = {$category_row['parent_id']}
				AND " . (($action == 'move_up') ? "right_id < {$category_row['right_id']} ORDER BY right_id DESC" : "left_id > {$category_row['left_id']} ORDER BY left_id ASC");
		$result = phpbb::$db->sql_query_limit($sql, $steps);

		$target = array();
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$target = $row;
		}
		phpbb::$db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The forum is already on top or bottom
			return false;
		}

		/**
		* $left_id and $right_id define the scope of the nodes that are affected by the move.
		* $diff_up and $diff_down are the values to substract or add to each node's left_id
		* and right_id in order to move them up or down.
		* $move_up_left and $move_up_right define the scope of the nodes that are moving
		* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		*/
		if ($action == 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $forum_row['right_id'];

			$diff_up = $forum_row['left_id'] - $target['left_id'];
			$diff_down = $forum_row['right_id'] + 1 - $forum_row['left_id'];

			$move_up_left = $forum_row['left_id'];
			$move_up_right = $forum_row['right_id'];
		}
		else
		{
			$left_id = $forum_row['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $forum_row['right_id'] + 1 - $forum_row['left_id'];
			$diff_down = $target['right_id'] - $forum_row['right_id'];

			$move_up_left = $forum_row['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			category_parents = ''
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		phpbb::$db->sql_query($sql);

		return $target['category_name'];
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
	* Build view URL for a category in the Category Management panel
	*/
	public function get_manage_url()
	{
		$url = 'manage/categories/c_' . $this->category_id;

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
			'CATEGORY_NAME'				=> (isset(phpbb::$user->lang[$this->category_name])) ? phpbb::$user->lang[$this->category_name] : $this->category_name,
			'CATEGORY_CONTRIBS'			=> $this->category_contribs,
			'CATEGORY_TYPE'				=> $this->category_type,

			'U_MOVE_UP'					=> titania_url::$root_url . $this->get_manage_url() . '-action_move_up',
			'U_MOVE_DOWN'				=> titania_url::$root_url . $this->get_manage_url() . '-action_move_down',
			'U_EDIT'					=> titania_url::$root_url . $this->get_manage_url() . '-action_edit',
			'U_DELETE'					=> titania_url::$root_url . $this->get_manage_url() . '-action_delete',
			'U_VIEW_CATEGORY'			=> titania_url::$root_url . $this->get_url(),
			'U_VIEW_MANAGE_CATEGORY'	=> titania_url::$root_url . $this->get_manage_url(),

			'HAS_CHILDREN'		=> $this->get_children($this->category_id),
		);

		if ($return)
		{
			return $display;
		}

		phpbb::$template->assign_vars($display);
	}
}
