<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

use phpbb\titania\ext;
use phpbb\titania\sync;

/**
 * Class to abstract categories.
 * @package Titania
 */
class titania_category extends \phpbb\titania\entity\message_base
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
	protected $object_type = ext::TITANIA_CATEGORY;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var array */
	public $available_options;

	const FLAG_DEMO = 1;
	const FLAG_ALL_VERSIONS = 2;
	const FLAG_TEAM_ONLY = 4;

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

			'category_options'				=> array('default' => 0),
		));

		$this->db = phpbb::$container->get('dbal.conn');
		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');

		$this->available_options = array(
			'integrate_demo'		=> self::FLAG_DEMO,
			'support_all_versions'	=> self::FLAG_ALL_VERSIONS,
			'team_only'				=> self::FLAG_TEAM_ONLY,
		);
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
	public function load($category = false)
	{
		$sql = 'SELECT * FROM ' . $this->sql_table . ' WHERE ';

		if ($category === false)
		{
			$sql .= 'category_id = ' . (int) $this->category_id;
		}
		else if (is_numeric($category))
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
	* Get translated category name.
	*
	* @return string
	*/
	public function get_name()
	{
		return phpbb::$user->lang($this->category_name);
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
	*
	* @param int $to_id			New parent id
	* @param sync|null $sync	If given sync class, category counts are resynchronized
	* @return array
	*/
	function move_category($to_id, $sync)
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
				$sync->categories('count');
			}
		}

		return $errors;
	}

	/**
	* Move category content from one to another category
	*
	* @param int $to_id			New parent id
	* @param sync|null $sync	If given sync class, category counts are resynchronized
	* @return array
	*/
	public function move_category_content($to_id = 0, $sync = null)
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
			// Update contrib_categories
			$this->update_contrib_categories($this->category_id, $to_id);

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
				$sync->categories('count');
			}
		}

		return $errors;
	}

	public function update_contrib_categories($from_id, $to_id)
	{
		$all = true;
		if ($from_id && $to_id)
		{
			$sql = 'SELECT ci.contrib_id, c.contrib_categories
				FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . ' ci
				LEFT JOIN ' . TITANIA_CONTRIBS_TABLE . ' c
					ON (ci.contrib_id = c.contrib_id)
				WHERE ci.category_id = ' . (int) $from_id;
			$all = false;
		}
		else
		{
			$sql = 'SELECT contrib_id, category_id
				FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE;
		}
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$contribs[$row['contrib_id']][] = ($all) ? $row['category_id'] : $row['contrib_categories'];
		}
		phpbb::$db->sql_freeresult($result);

		foreach ($contribs as $id => $categories)
		{
			if ($all)
			{
				$categories = implode(',', $categories);
			}
			else
			{
				$categories = array_flip(explode(',', $categories[0]));
				unset($categories[$from_id]);
				$categories[$to_id] = true;
				$categories = implode(',', array_keys($categories));
			}

			phpbb::$db->sql_query('UPDATE ' . TITANIA_CONTRIBS_TABLE . ' SET contrib_categories = "' . phpbb::$db->sql_escape($categories) . '" WHERE contrib_id = ' . (int) $id);
			unset($contribs[$id]);
		}
	}

	/**
	* Remove complete category
	*
	* @param sync|null $sync	If given sync class, category counts are resynchronized
	* @return null
	*/
	public function delete($sync = null)
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
	public function get_url(array $params = array())
	{
		$i = 1;

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
			$params["category$i"] = $row['category_name_clean'];
			$i++;
		}
		$params["category$i"] = $this->category_name_clean . '-' . $this->category_id;

		if (isset($params['branch']))
		{
			$i++;
			$params["category$i"] = $params['branch'];
			unset($params['branch']);
		}

		return $this->controller_helper->route('phpbb.titania.category', $params);
	}

	/**
	* Build view URL for a category in the Category Management panel
	*
	* @param bool|string $action		Optional action. Defaults to false.
	* @param array $params				Additional parameters to add to URL.
	*
	* @return string
	*/
	public function get_manage_url($action = false, $params = array())
	{
		$controller = 'phpbb.titania.manage.categories';
		$params['id'] = $this->category_id;

		if ($action)
		{
			$controller .= '.action';
			$params['action'] = $action;
		}

		return $this->controller_helper->route($controller, $params);
	}

	/**
	* Assign the common items to the template
	*
	* @param bool $return True to return the array of stuff to display and output yourself, false to output to the template automatically
	*/
	public function assign_display($return = false)
	{
		$action_hash = array('hash' => generate_link_hash('category_action'));
		$depth = sizeof(titania::$cache->get_category_parents($this->category_id)) * 15;

		$display = array(
			'CATEGORY_NAME'				=> (isset(phpbb::$user->lang[$this->category_name])) ? phpbb::$user->lang[$this->category_name] : $this->category_name,
			'CATEGORY_CONTRIBS'			=> $this->category_contribs,
			'CATEGORY_TYPE'				=> $this->category_type,
			'CATEGORY_DESC'				=> $this->generate_text_for_display(),
			'CATEGORY_ID'				=> $this->category_id,
			'PARENT_ID'					=> $this->parent_id,
			'DEPTH'						=> $depth,

			'U_MOVE_UP'					=> $this->get_manage_url('move_up', $action_hash),
			'U_MOVE_DOWN'				=> $this->get_manage_url('move_down', $action_hash),
			'U_EDIT'					=> $this->get_manage_url('edit'),
			'U_DELETE'					=> $this->get_manage_url('delete'),
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

	/**
	* Set left and right id values for a newly created category.
	*
	* @return bool|string Returns an error string if something went wrong,
	*	otherwise returns false.
	*/
	public function set_left_right_ids()
	{
		if ($this->parent_id)
		{
			$sql = 'SELECT left_id, right_id
				FROM ' . TITANIA_CATEGORIES_TABLE . '
				WHERE category_id = ' . $this->parent_id;
			$result = phpbb::$db->sql_query($sql);
			$row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			if (!$row)
			{
				return phpbb::$user->lang['PARENT_NOT_EXIST'];
			}

			$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
				SET left_id = left_id + 2, right_id = right_id + 2
				WHERE left_id > ' . (int) $row['right_id'];
			phpbb::$db->sql_query($sql);

			$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
				SET right_id = right_id + 2
				WHERE ' . (int) $row['left_id'] . ' BETWEEN left_id AND right_id';
			phpbb::$db->sql_query($sql);

			$this->left_id = $row['right_id'];
			$this->right_id = $row['right_id'] + 1;
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . TITANIA_CATEGORIES_TABLE;
			$result = phpbb::$db->sql_query($sql);
			$row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			$this->left_id = $row['right_id'] + 1;
			$this->right_id = $row['right_id'] + 2;
		}

		return false;
	}

	/**
	* Validate category settings.
	*
	* @return array Returns array containing any errors found.
	*/
	public function validate()
	{
		$error = array();

		if (!$this->category_name)
		{
			$error[] = phpbb::$user->lang['NO_CATEGORY_NAME'];
		}

		if (!$this->category_name_clean || !preg_match('/^[a-zA-Z0-9\-\_]+$/', $this->category_name_clean))
		{
			$error[] = phpbb::$user->lang['NO_CATEGORY_URL'];
		}

		if ($this->category_id && $this->parent_id == $this->category_id)
		{
			$error[] = phpbb::$user->lang['CATEGORY_DUPLICATE_PARENT'];
		}
		return $error;
	}

	/**
	* Set category option.
	*
	* @param string $option		Option to set - from those defined in available_options property.
	* @return null
	*/
	public function set_option($option)
	{
		if (isset($this->available_options[$option]) && !$this->is_option_set($option))
		{
			$this->category_options += $this->available_options[$option];
		}
	}

	/**
	* Check whether the given option is set.
	*
	* @param string $option 	Option to check for - from those defined in available_options property.
	* @return bool
	*/
	public function is_option_set($option)
	{
		return isset($this->available_options[$option]) &&
			$this->category_options & $this->available_options[$option];
	}
}
