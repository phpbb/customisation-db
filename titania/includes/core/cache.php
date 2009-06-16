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
}
