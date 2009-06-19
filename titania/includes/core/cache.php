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
	* Get the contrib authors for the specified contribution id
	*
	* @param int $contrib_id The contribution ID
	*
	* @return array|bool Array of author user_id's if the item exists, boolean false if the contrib item does not exist.
	*/
	public function get_contrib_authors($contrib_id)
	{
		$contrib_id = (int) $contrib_id;
		if (!$contrib_id)
		{
			return false;
		}

		// We shall group contributions by id in groups of 1000
		$contrib_block_name = '_titania_authors_' . floor($contrib_id / 1000);

		$contrib_block = $this->get($contrib_block_name);

		if ($contrib_block !== false)
		{
			if (isset($contrib_block[$contrib_id]))
			{
				return $contrib_block[$contrib_id];
			}
		}
		else
		{
			// Else the cache file did not exist and we need to start over
			$contrib_block = array();
		}

		$contrib_block[$contrib_id] = array();

		// Need to get the authors for the selected contrib
		$sql = 'SELECT contrib_user_id FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . $contrib_id;
		phpbb::$db->sql_query($sql);
		$user_id = phpbb::$db->sql_fetchfield('contrib_user_id');
		phpbb::$db->sql_freeresult();

		if (!$user_id) // Contrib does not exist
		{
			$contrib_block[$contrib_id] = false;

			// Store the updated cache data
			$this->put($contrib_block_name, $contrib_block);

			return false;
		}

		$contrib_block[$contrib_id][] = $user_id;

		// Now get the co-authors
		$sql = 'SELECT user_id FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE contrib_id = ' . $contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$contrib_block[$contrib_id][] = $row['user_id'];
		}
		phpbb::$db->sql_freeresult($result);

		// Store the updated cache data
		$this->put($contrib_block_name, $contrib_block);

		return $contrib_block[$contrib_id];
	}
}
