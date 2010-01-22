<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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

// Hopefully this helps
@set_time_limit(0);

/**
 * Sync handler for Titania
 *
 * Hopefully we never need to use this, but we probably will at some point, so put all sync stuff in here for easy access (and not to take up extra space in other files when they will rarely, if ever, be needed)
 */
class titania_sync
{
	/**
	* Sync categories
	*
	* @param string $mode The mode (count)
	* @param int $cat_id Category id to limit to
	*/
	public function categories($mode, $cat_id = false)
	{
		switch ($mode)
		{
			case 'count' :
				$sql = 'SELECT category_id, category_contribs FROM ' . TITANIA_CATEGORIES_TABLE .
					(($cat_id) ? ' WHERE category_id = ' . (int) $cat_id : '');
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$contrib_count = $this->_get_contrib_count($row['category_id']);
					if ($row['category_contribs'] != $contrib_count)
					{
						$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . ' SET category_contribs = ' . (int) $contrib_count . ' WHERE category_id = ' . $row['category_id'];
						phpbb::$db->sql_query($sql);
					}
				}
			break;
		}
	}

	/**
	 * Sync topics
	 *
	 * @param <type> $mode The mode (post_count - topics_posts field)
	 * @param <type> $topic_id The topic id to limit to
	 */
	public function topics($mode, $topic_id = false)
	{
		switch ($mode)
		{
			// Sync the topics_posts field
			case 'post_count' :
				$sql = 'SELECT topic_id, topic_posts FROM ' . TITANIA_TOPICS_TABLE .
					(($topic_id) ? ' WHERE topic_id = ' . (int) $topic_id : '') . '
					ORDER BY topic_id ASC';
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$post_count = $this->_get_post_count($row['topic_id']);
					if ($row['topic_posts'] != $post_count)
					{
						$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . ' SET topic_posts = ' . (int) $post_count . ' WHERE topic_id = ' . $row['topic_id'];
						phpbb::$db->sql_query($sql);
					}
				}
				phpbb::$db->sql_freeresult($result);
			break;
		}
	}

	public function _get_contrib_count($category_id)
	{
		// Bundle up the children in a nice array
		$child_list = array($category_id);
		$sql = 'SELECT left_id, right_id FROM ' . TITANIA_CATEGORIES_TABLE . '
			WHERE category_id = ' . (int) $category_id . '
			ORDER BY left_id ASC';
		$result = phpbb::$db->sql_query($sql);
		$cat_row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
		if (!$cat_row)
		{
			return 0;
		}

		$sql = 'SELECT category_id FROM ' . TITANIA_CATEGORIES_TABLE . '
			WHERE left_id > ' . $cat_row['left_id'] . '
				AND right_id < ' . $cat_row['right_id'];
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$child_list[] = $row['category_id'];
		}
		phpbb::$db->sql_freeresult($result);


		$sql_ary = array(
			'SELECT'	=> 'COUNT(DISTINCT c.contrib_id) AS cnt',

			'FROM'		=> array(
				TITANIA_CONTRIB_IN_CATEGORIES_TABLE => 'cic',
				TITANIA_CONTRIBS_TABLE => 'c',
			),

			'WHERE'		=> 'cic.contrib_id = c.contrib_id
				AND ' . phpbb::$db->sql_in_set('cic.category_id', $child_list) . '
				AND c.contrib_visible = 1' .
				((titania::$config->require_validation) ? ' AND c.contrib_status <> ' . TITANIA_CONTRIB_NEW : ''),
		);
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		phpbb::$db->sql_query($sql);
		$cnt = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		return $cnt;
	}

	public function _get_post_count($topic_id)
	{
		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND (post_access = ' . TITANIA_ACCESS_TEAMS . ' OR post_deleted <> 0)'; // Account for our hacking (post_deleted)
		$result = phpbb::$db->sql_query($sql);
		$teams = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . TITANIA_ACCESS_AUTHORS . '
				AND post_deleted = 0'; // Account for our hacking (post_deleted)
		$result = phpbb::$db->sql_query($sql);
		$authors = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . TITANIA_ACCESS_PUBLIC . '
				AND post_deleted = 0'; // Account for our hacking (post_deleted)
		$result = phpbb::$db->sql_query($sql);
		$public = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		return ($teams + $authors + $public) . ':' . ($authors + $public) . ':' . $public;
	}
}