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
	* Submit data in the post_data format (from includes/tools/message.php)
	*
	* @param object $message The message object
	*/
	public function post_data($message)
	{
		$post_data = $message->request_data();

		parent::post_data($message);
	}

	/**
	 * Submit data for storing into the database
	 *
	 * @return bool
	 */
	public function submit()
	{
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
	* Get category details
	*/
	public function get_category_info($category_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->sql_table . "
			WHERE category_id = $category_id";
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Category #$category does not exist", E_USER_ERROR);
		}

		return $row;
	}

	/**
	* Get category branch
	*/
	public function get_category_branch($category_id, $type = 'all', $order = 'descending', $include_forum = true)
	{
		switch ($type)
		{
			case 'parents':
				$condition = 'c1.left_id BETWEEN c2.left_id AND c2.right_id';
			break;

			case 'children':
				$condition = 'c2.left_id BETWEEN c1.left_id AND c1.right_id';
			break;

			default:
				$condition = 'c2.left_id BETWEEN c1.left_id AND c1.right_id OR c1.left_id BETWEEN c2.left_id AND c2.right_id';
			break;
		}

		$rows = array();

		$sql = 'SELECT c2.*
			FROM ' . $this->sql_table . ' c1
			LEFT JOIN ' . $this->sql_table . " c2 ON ($condition)
			WHERE c1.category_id = $category_id
			ORDER BY c2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if (!$include_category && $row['category_id'] == $category_id)
			{
				continue;
			}

			$rows[] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Simple version of jumpbox, just lists available categories
	*/
	public function make_category_select($select_id = false, $ignore_id = false, $ignore_acl = true, $ignore_nonpost = true, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
	{

		// This query is identical to the jumpbox one
		$sql = 'SELECT category_id, category_name, parent_id, left_id, right_id
			FROM ' . $this->sql_table . '
			ORDER BY left_id ASC';
		$result = phpbb::$db->sql_query($sql, 600);

		$right = 0;
		$padding_store = array('0' => '');
		$padding = '';
		$category_list = ($return_array) ? array() : '';

		if(!$select_id)
		{
			$selected = (is_array($select_id)) ? ((in_array(0, $select_id)) ? ' selected="selected"' : '') : ((0 == $select_id) ? ' selected="selected"' : '');
		}
		$category_list .= '<option value="0"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . '- ' . phpbb::$user->lang['ROOT'] . ' -' . '</option>';
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$category_name = (isset(phpbb::$user->lang[$row['category_name']])) ? phpbb::$user->lang[$row['category_name']] : $row['category_name'];
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];
			$disabled = false;

			if ($return_array)
			{
				// Include some more information...
				$selected = (is_array($select_id)) ? ((in_array($row['category_id'], $select_id)) ? true : false) : (($row['category_id'] == $select_id) ? true : false);
				$category_list[$row['category_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
			}
			else
			{
				$selected = (is_array($select_id)) ? ((in_array($row['category_id'], $select_id)) ? ' selected="selected"' : '') : (($row['category_id'] == $select_id) ? ' selected="selected"' : '');
				$category_list .= '<option value="' . $row['category_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $category_name . '</option>';
			}
		}
		phpbb::$db->sql_freeresult($result);
		unset($padding_store);

		return $category_list;
	}

	/**
	* Move category content from one to another category
	*/
	public function move_category_content($from_id, $to_id, $sync = true)
	{
		$table_ary = array(TITANIA_CONTRIB_IN_CATEGORIES_TABLE);

		foreach ($table_ary as $table)
		{
			$sql = "UPDATE $table
				SET category_id = $to_id
				WHERE category_id = $from_id";
			phpbb::$db->sql_query($sql);
		}
		unset($table_ary);

		$table_ary = array(TITANIA_CATEGORIES_TABLE);

		foreach ($table_ary as $table)
		{
			$sql = "UPDATE $table
				SET parent_id = $to_id
				WHERE parent_id = $from_id";
			phpbb::$db->sql_query($sql);
		}

		if ($sync)
		{
			// Resync counters
			$sync = new titania_sync;
			$sync->categories(count, $to_id);
		}

		return array();
	}

	/**
	* Remove complete category
	*/
	public function delete_category($category_id, $action_contribs = 'delete', $action_subcats = 'delete', $contribs_to_id = 0, $subcats_to_id = 0)
	{
		$category_data = $this->get_category_info($category_id);

		$errors = array();
		$category_ids = array($category_id);

		if ($action_contribs == 'delete')
		{
			$errors = array_merge($errors, $this->delete_category_content($category_id));
		}
		else if ($action_contribs == 'move')
		{
			if (!$contribs_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_CATEGORY'];
			}
			else
			{
				$sql = 'SELECT category_name
					FROM ' . $this->sql_table . '
					WHERE category_id = ' . $contribs_to_id;
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $user->lang['NO_CATEGORY'];
				}
				else
				{
					$contribs_to_name = $row['category_name'];
					$errors = array_merge($errors, $this->move_category_content($category_id, $contribs_to_id));
				}
			}
		}

		if (sizeof($errors))
		{
			return $errors;
		}

		if ($action_subcats == 'delete')
		{
			$rows = $this->get_category_branch($category_id, 'children', 'descending', false);

			foreach ($rows as $row)
			{
				$category_ids[] = $row['category_id'];
				$errors = array_merge($errors, $this->delete_category_content($row['category_id']));
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			$diff = sizeof($category_ids) * 2;

			$sql = 'DELETE FROM ' . $this->sql_table . '
				WHERE ' . phpbb::$db->sql_in_set('category_id', $category_ids);
			phpbb::$db->sql_query($sql);
		}
		else if ($action_subcats == 'move')
		{
			if (!$subcats_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_CATEGORY'];
			}
			else
			{
				$sql = 'SELECT category_name
					FROM ' . $this->sql_table . '
					WHERE category_id = ' . $subcats_to_id;
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $user->lang['NO_CATEGORY'];
				}
				else
				{
					$subcats_to_name = $row['category_name'];

					$sql = 'SELECT category_id
						FROM ' . $this->sql_table . "
						WHERE parent_id = $category_id";
					$result = phpbb::$db->sql_query($sql);

					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$this->move_category($row['category_id'], $subcats_to_id);
					}
					phpbb::$db->sql_freeresult($result);

					// Grab new category data for correct tree updating later
					$category_data = $this->get_category_info($category_id);

					$sql = 'UPDATE ' . $this->sql_table . "
						SET parent_id = $subcats_to_id
						WHERE parent_id = $category_id";
					phpbb::$db->sql_query($sql);

					$diff = 2;
					$sql = 'DELETE FROM ' . $this->sql_table . "
						WHERE category_id = $category_id";
					phpbb::$db->sql_query($sql);
				}
			}

			if (sizeof($errors))
			{
				return $errors;
			}
		}
		else
		{
			$diff = 2;
			$sql = 'DELETE FROM ' . $this->sql_table . "
				WHERE category_id = $category_id";
			phpbb::$db->sql_query($sql);
		}

		// Resync tree
		$sql = 'UPDATE ' . $this->sql_table . "
			SET right_id = right_id - $diff
			WHERE left_id < {$category_data['right_id']} AND right_id > {$category_data['right_id']}";
		phpbb::$db->sql_query($sql);

		$sql = 'UPDATE ' . $this->sql_table . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$category_data['right_id']}";
		phpbb::$db->sql_query($sql);

		phpbb::$db->sql_freeresult($result);

		return $errors;
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
			FROM ' . $this->sql_table . "
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
			// The category is already on top or bottom
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
			$right_id = $category_row['right_id'];

			$diff_up = $category_row['left_id'] - $target['left_id'];
			$diff_down = $category_row['right_id'] + 1 - $category_row['left_id'];

			$move_up_left = $category_row['left_id'];
			$move_up_right = $category_row['right_id'];
		}
		else
		{
			$left_id = $category_row['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $category_row['right_id'] + 1 - $category_row['left_id'];
			$diff_down = $target['right_id'] - $category_row['right_id'];

			$move_up_left = $category_row['right_id'] + 1;
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
			END
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

			'HAS_CHILDREN'				=> $this->get_children($this->category_id),
		);

		if ($return)
		{
			return $display;
		}

		phpbb::$template->assign_vars($display);
	}
}
