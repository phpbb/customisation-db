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
		// Destroy category cache
		$this->destroy_cache();

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
	* Get category branch
	*/
	public function get_category_branch($type = 'all', $order = 'descending', $include_category = true)
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
			WHERE c1.category_id = " . $this->category_id . '
			ORDER BY c2.left_id ' . (($order == 'descending') ? 'ASC' : 'DESC');
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if (!$include_category && $row['category_id'] == $this->category_id)
			{
				continue;
			}

			$rows[] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Move category
	*/
	function move_category($to_id, $sync = true)
	{
		$to_data = $moved_ids = $errors = array();

		if ($to_id > 0)
		{
			// Retrieve $to_data
			$to_data = new titania_category;
			$to_data->load($to_id);
		}

		// Make sure we're not moving this category under one if its own children
		if ($to_id > 0 && $to_data->left_id > $this->left_id && $to_data->right_id < $this->right_id)
		{
			$errors[] = phpbb::$user->lang['CATEGORY_CHILD_AS_PARENT'];
		}
		else
		{
			$moved_categories = $this->get_category_branch('children');
			$diff = sizeof($moved_categories) * 2;

			$moved_ids = array();
			for ($i = 0; $i < sizeof($moved_categories); ++$i)
			{
				$moved_ids[] = (int) $moved_categories[$i]['category_id'];
			}

			// Resync parents
			$sql = 'UPDATE ' . $this->sql_table . "
				SET right_id = right_id - $diff
				WHERE left_id < " . $this->right_id . "
					AND right_id > " . $this->right_id;
			phpbb::$db->sql_query($sql);

			// Resync righthand side of tree
			$sql = 'UPDATE ' . $this->sql_table . "
				SET left_id = left_id - $diff, right_id = right_id - $diff
				WHERE left_id > " . $this->right_id;
			phpbb::$db->sql_query($sql);

			if ($to_id > 0)
			{
				// Retrieve $to_data again, it may have been changed...
				unset($to_data);
				$to_data = new titania_category;
				$to_data->load($to_id);

				// Resync new parents
				$sql = 'UPDATE ' . $this->sql_table . "
					SET right_id = right_id + $diff
					WHERE " . $to_data->right_id . ' BETWEEN left_id AND right_id
						AND ' . phpbb::$db->sql_in_set('category_id', $moved_ids, true);
				phpbb::$db->sql_query($sql);

				// Resync the righthand side of the tree
				$sql = 'UPDATE ' . $this->sql_table . "
					SET left_id = left_id + $diff, right_id = right_id + $diff
					WHERE left_id > " . $to_data->right_id . '
						AND ' . phpbb::$db->sql_in_set('category_id', $moved_ids, true);
				phpbb::$db->sql_query($sql);

				// Resync moved branch
				$to_data->right_id += $diff;

				if ($to_data->right_id > $this->right_id)
				{
					$diff = '+ ' . ($to_data->right_id - $this->right_id - 1);
				}
				else
				{
					$diff = '- ' . abs($to_data->right_id - $this->right_id - 1);
				}
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . $this->sql_table . '
					WHERE ' . phpbb::$db->sql_in_set('category_id', $moved_ids, true);
				$result = phpbb::$db->sql_query($sql);
				$row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);

				$diff = '+ ' . ($row['right_id'] - $this->left_id + 1);
			}

			$sql = 'UPDATE ' . $this->sql_table . "
				SET left_id = left_id $diff, right_id = right_id $diff
				WHERE " . phpbb::$db->sql_in_set('category_id', $moved_ids);
			phpbb::$db->sql_query($sql);

			if ($sync)
			{
				// Resync counters
				$sync = new titania_sync;
				$sync->categories('count');
			}
		}

		return $errors;
	}

	/**
	* Move category content from one to another category
	*/
	public function move_category_content($to_id = 0, $sync = true)
	{
		$sql = 'SELECT category_type
			FROM ' . $this->sql_table . '
			WHERE category_id = ' . (int) $to_id;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);

		$errors = array();
		if ($to_id == $this->category_id)
		{
			$errors[] = phpbb::$user->lang['DESTINATION_CAT_INVALID'];
		}
		else if (!$row['category_type'])
		{
			$errors[] = phpbb::$user->lang['DESTINATION_CAT_INVALID'];
		}

		phpbb::$db->sql_freeresult($result);

		if(!sizeof($errors))
		{
			// Select duplicate contribs and prevent them from being moved to the selected category
			$sql = 'SELECT contrib_id
				FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
				WHERE category_id = ' . (int) $to_id;
			$result = phpbb::$db->sql_query($sql);

			$contrib_ids = array();

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$contrib_ids[] = (int) $row['contrib_id'];
			}
			phpbb::$db->sql_freeresult($result);

			$sql = 'UPDATE ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
				SET category_id = ' . (int) $to_id . '
				WHERE category_id = ' . $this->category_id .
					((!empty($contrib_ids)) ? ' AND ' . phpbb::$db->sql_in_set('contrib_id', $contrib_ids, true) : '');
			phpbb::$db->sql_query($sql);

			// Now delete the contrib records from the previous parent category
			$sql = 'DELETE FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
				WHERE category_id = ' . $this->category_id;
			phpbb::$db->sql_query($sql);

			if ($sync)
			{
				// Resync counters
				$sync = new titania_sync;
				$sync->categories('count');
			}
		}

		return $errors;
	}

	/**
	* Remove complete category
	*/
	public function delete($sync = true)
	{
		// This should be the correct diff value each time
		$diff = 2;

		// Resync tree
		$sql = 'UPDATE ' . $this->sql_table . "
			SET right_id = right_id - $diff
			WHERE left_id < {$this->right_id} AND right_id > {$this->right_id}";
		phpbb::$db->sql_query($sql);

		$sql = 'UPDATE ' . $this->sql_table . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$this->right_id}";
		phpbb::$db->sql_query($sql);

		// Delete content
		$sql = 'DELETE FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE category_id = ' . $this->category_id;
		phpbb::$db->sql_query($sql);

		// Delete self
		parent::delete();

		// Resync counters
		if ($sync)
		{
			$sync = new titania_sync;
			$sync->categories('count');
		}

		// Destroy category cache
		$this->destroy_cache();
	}

	/**
	* Move category position by $steps up/down
	*/
	public function move_category_by($action = 'move_up', $steps = 1)
	{
		/**
		* Fetch all the siblings between the module's current spot
		* and where we want to move it to. If there are less than $steps
		* siblings between the current spot and the target then the
		* module will move as far as possible
		*/
		$sql = 'SELECT category_id, category_name, left_id, right_id
			FROM ' . $this->sql_table . '
			WHERE parent_id = ' . (int) $this->parent_id . "
				AND " . (($action == 'move_up') ? "right_id < {$this->right_id} ORDER BY right_id DESC" : "left_id > {$this->left_id} ORDER BY left_id ASC");
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
			$right_id = $this->right_id;

			$diff_up = $this->left_id - $target['left_id'];
			$diff_down = $this->right_id + 1 - $this->left_id;

			$move_up_left = $this->left_id;
			$move_up_right = $this->right_id;
		}
		else
		{
			$left_id = $this->left_id;
			$right_id = $target['right_id'];

			$diff_up = $this->right_id + 1 - $this->left_id;
			$diff_down = $target['right_id'] - $this->right_id;

			$move_up_left = $this->right_id + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->sql_table . "
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

		// Destroy category cache
		$this->destroy_cache();
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

		return titania_url::build_url($url . $this->category_name_clean . '-' . $this->category_id);
	}

	/**
	* Build view URL for a category in the Category Management panel
	*/
	public function get_manage_url()
	{
		return titania_url::build_url('manage/categories', array('c' => $this->category_id));
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
			'CATEGORY_DESC'				=> $this->generate_text_for_display(),

			'U_MOVE_UP'					=> titania_url::append_url($this->get_manage_url(), array('action' => 'move_up')),
			'U_MOVE_DOWN'				=> titania_url::append_url($this->get_manage_url(), array('action' => 'move_down')),
			'U_EDIT'					=> titania_url::append_url($this->get_manage_url(), array('action' => 'edit')),
			'U_DELETE'					=> titania_url::append_url($this->get_manage_url(), array('action' => 'delete')),
			'U_VIEW_CATEGORY'			=> $this->get_url(),
			'U_VIEW_MANAGE_CATEGORY'	=> $this->get_manage_url(),

			'HAS_CHILDREN'				=> ($this->right_id - $this->left_id > 1) ? true : false,
		);

		if ($return)
		{
			return $display;
		}

		phpbb::$template->assign_vars($display);
	}

	/**
	* Destroy the cached data
	*/
	public function destroy_cache()
	{
		titania::$cache->destroy('_titania_categories');
		titania::$cache->destroy('_titania_category_parents');
		titania::$cache->destroy('_titania_category_children');
	}
}
