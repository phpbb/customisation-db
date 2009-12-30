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
						$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . ' SET topic_posts = \'' . $post_count . '\' WHERE topic_id = ' . $row['topic_id'];
						phpbb::$db->sql_query($sql);
					}
				}
				phpbb::$db->sql_freeresult($result);
			break;
		}
	}

	public function _get_post_count($topic_id)
	{
		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND (post_access = ' . TITANIA_ACCESS_TEAMS . ' OR post_deleted <> 0)'; // Account for our hacking
		$result = phpbb::$db->sql_query($sql);
		$teams = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . TITANIA_ACCESS_AUTHORS . '
				AND post_deleted = 0'; // Account for our hacking
		$result = phpbb::$db->sql_query($sql);
		$authors = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . TITANIA_ACCESS_PUBLIC . '
				AND post_deleted = 0'; // Account for our hacking
		$result = phpbb::$db->sql_query($sql);
		$public = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		return ($teams + $authors + $public) . ':' . ($authors + $public) . ':' . $public;
	}
}