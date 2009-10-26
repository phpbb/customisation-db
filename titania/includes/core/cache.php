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
	public function get_categories($tag_type = titania_tag::TYPE_CATEGORY)
	{
		//$categories = $this->get('_titania_categories_' . $tag_type);

		if (!$categories)
		{
			$categories = array();
			$sql = 'SELECT tag_id, tag_field_name, tag_clean_name, tag_field_desc, tag_items, tag_contrib_type
						FROM ' . TITANIA_TAG_FIELDS_TABLE . '
						WHERE tag_type_id = ' . (int) $tag_type;
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$categories[$row['tag_id']] = array(
					'tag_id'				=> $row['tag_id'],
					'tag_field_name'		=> $row['tag_field_name'],
					'tag_clean_name'		=> $row['tag_clean_name'],
					'tag_field_desc'		=> $row['tag_field_desc'],
					'tag_items'				=> $row['tag_items'],
					'tag_contrib_type'		=> $row['tag_contrib_type'],
				);
			}
			phpbb::$db->sql_freeresult($result);

			$this->put('_titania_categories_' . $tag_type, $categories);
		}

		return $categories;
	}

	public function get_types()
	{
		$types = $this->get('_titania_types');

		if (!$types)
		{
			$types = array();
			$sql = 'SELECT type_id, type_name, type_slug, author_count_field
						FROM ' . TITANIA_TYPES_TABLE;
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$types[$row['type_id']] = array(
					'type_id'				=> $row['type_id'],
					'type_name'				=> $row['type_name'],
					'author_count_field'	=> $row['author_count_field'],
					'type_slug'				=> $row['type_slug'],
				);
			}
			phpbb::$db->sql_freeresult($result);

			$this->put('_titania_types', $types);
		}

		return $types;
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

		// We shall group contributions by id in groups of 1000
		$contrib_block_name = '_titania_contribs_' . floor($contrib_id / 1000);

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

	/**
	* Get the author contribs for the specified user id
	*
	* @param int $user_id The user ID
	*
	* @return array Array of contrib_id's
	*/
	public function get_author_contribs($user_id)
	{
		$author_id = (int) $author_id;

		// We shall group authors by id in groups of 2500
		$author_block_name = '_titania_authors_' . floor($user_id / 2500);

		$author_block = $this->get($author_block_name);

		if ($author_block !== false)
		{
			if (isset($author_block[$user_id]))
			{
				return $author_block[$user_id];
			}
		}
		else
		{
			// Else the cache file did not exist and we need to start over
			$author_block = array();
		}

		$author_block[$user_id] = array();

		// Need to get the contribs for the selected author
		$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_user_id = ' . $user_id;
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$author_block[$user_id][] = $row['contrib_id'];
		}

		phpbb::$db->sql_freeresult($result);

		// Now get the lists where the user is a co-author
		$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$author_block[$user_id][] = $row['contrib_id'];
		}
		phpbb::$db->sql_freeresult($result);

		// Store the updated cache data
		$this->put($author_block_name, $author_block);

		return $author_block[$user_id];
	}

	/**
	* Obtain allowed extensions
	*
	* @return array allowed extensions array.
	*/
	public function obtain_attach_extensions()
	{
		if (($extensions = $this->get('_titania_extensions')) === false)
		{
			$extensions = array(
				'_allowed_contrib'		=> array(),
				'_allowed_screenshot'	=> array(),
			);

			// The rule is to only allow those extensions defined. ;)
			$sql = 'SELECT e.extension, g.*
				FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . " g
				WHERE e.group_id = g.group_id
					AND (g.group_name = 'Titania Contributions' OR g.group_name = 'Titania Screenshots')";
			$result = phpbb::$db->sql_query($sql);

			while ($row =  phpbb::$db->sql_fetchrow($result))
			{
				$extension = strtolower(trim($row['extension']));

				$extensions[$extension] = array(
					'display_cat'	=> (int) $row['cat_id'],
					'download_mode'	=> (int) $row['download_mode'],
					'upload_icon'	=> trim($row['upload_icon']),
					'max_filesize'	=> (int) $row['max_filesize'],
					'allow_group'	=> $row['allow_group'],
					'allow_in_pm'	=> $row['allow_in_pm'],
				);

				// Store allowed extensions forum wise
				if ($row['group_name'] == 'Titania Contributions')
				{
					$extensions['_allowed_contrib'][$extension] = 0;
				}
				else if ($row['group_name'] == 'Titania Screenshots')
				{
					$extensions['_allowed_screenshot'][$extension] = 0;
				}
			}
			 phpbb::$db->sql_freeresult($result);

			$this->put('_titania_extensions', $extensions);
		}

		return $extensions;
	}
}
