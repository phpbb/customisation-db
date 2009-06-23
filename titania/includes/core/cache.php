<?php
/**
 *
 * @package titania
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

class titania_cache extends acm
{
	/**
	 * Constructor
	 */
	public function acm()
	{
		parent::acm();
		$this->cache_dir = phpbb_realpath($this->cache_dir) . '/';
	}

	/**
	 * Get categories by tag type
	 *
	 * @param int $tag_type
	 * @return array of category data (field_name and field_desc)
	 */
	public function get_categories($tag_type = TAG_TYPE_MOD_CATEGORY)
	{
		$categories = $this->get('_titania_categories_' . $tag_type);

		if (!$categories)
		{
			$categories = array();
			$sql = 'SELECT * FROM ' . TITANIA_TAG_FIELDS_TABLE . '
						WHERE tag_type_id = ' . (int) $tag_type;
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$categories[$row['tag_id']] = array(
					'id'			=> $row['tag_id'],
					'name'			=> $row['tag_field_name'],
					'clean_name'	=> $row['tag_clean_name'],
					'desc'			=> $row['tag_field_desc'],
				);
			}
			phpbb::$db->sql_freeresult($result);

			$this->put('_titania_categories_' . $tag_type, $categories);
		}

		return $categories;
	}

	/**
	* Get the list of parents for a category
	*
	* @param int $category_id The category id to get the parents for.
	*/
	public function get_category_parents($category_id)
	{
		$parent_list = $this->get('_titania_category_parents');

		if ($parent_list === false)
		{
			$parent_list = $list = array();

			$sql = 'SELECT category_id, parent_id, category_name_clean FROM ' . TITANIA_CATEGORIES_TABLE . '
				ORDER BY left_id ASC';
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				// need later
				$list[$row['category_id']] = $row;

				$parent_id = $row['parent_id'];

				// Go through and grab all of the parents
				while (isset($list[$parent_id]))
				{
					$parent_list[$row['category_id']][] = $list[$parent_id];

					$parent_id = $list[$parent_id]['parent_id'];
				}
			}

			phpbb::$db->sql_freeresult($result);

			$this->put('_titania_category_parents', $parent_list);
		}

		return (isset($parent_list[$category_id])) ? $parent_list[$category_id] : array();
	}
}
