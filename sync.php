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

namespace phpbb\titania;

use phpbb\titania\access;
use phpbb\titania\count;

/**
 * Sync handler for Titania
 *
 * Hopefully we never need to use this, but we probably will at some point, so put all sync stuff in here for easy access (and not to take up extra space in other files when they will rarely, if ever, be needed)
 */
class sync
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\titania\attachment\attachment */
	protected $attachment;

	/** @var \phpbb\titania\search\manager */
	protected $search_manager;

	/** @var string */
	protected $attachments_table;

	/** @var string */
	protected $authors_table;

	/** @var string */
	protected $categories_table;

	/** @var string */
	protected $contrib_coauthors_table;

	/** @var string */
	protected $contrib_faq_table;

	/** @var string */
	protected $contrib_in_categories_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $queue_table;

	/** @var string */
	protected $revisions_table;

	/** @var string */
	protected $topics_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param cache\service $cache
	 * @param attachment\attachment $attachment
	 * @param search\manager $search_manager
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, cache\service $cache, \phpbb\titania\attachment\attachment $attachment, search\manager $search_manager)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->attachment = $attachment;
		$this->search_manager = $search_manager;
		$this->attachments_table = TITANIA_ATTACHMENTS_TABLE;
		$this->authors_table = TITANIA_AUTHORS_TABLE;
		$this->categories_table = TITANIA_CATEGORIES_TABLE;
		$this->contrib_coauthors_table = TITANIA_CONTRIB_COAUTHORS_TABLE;
		$this->contrib_faq_table = TITANIA_CONTRIB_FAQ_TABLE;
		$this->contrib_in_categories_table = TITANIA_CONTRIB_IN_CATEGORIES_TABLE;
		$this->contribs_table = TITANIA_CONTRIBS_TABLE;
		$this->posts_table = TITANIA_POSTS_TABLE;
		$this->queue_table = TITANIA_QUEUE_TABLE;
		$this->revisions_table = TITANIA_REVISIONS_TABLE;
		$this->topics_table = TITANIA_TOPICS_TABLE;
	}
	/**
	* Sync attachments
	*/
	public function attachments($mode, $attachment_id = false)
	{
		switch ($mode)
		{
			case 'hash' :
				$sql = 'SELECT *
					FROM ' . $this->attachments_table .
					(($attachment_id !== false) ? 'WHERE attachment_id = ' . (int) $attachment_id : '');
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->attachment->__set_array($row);
					$file = $this->attachment->get_filepath();

					if (file_exists($file))
					{
						$md5 = md5_file($file);

						if ($md5 != $row['hash'])
						{
							$sql = 'UPDATE ' . $this->attachments_table . '
								SET hash = "' . $this->db->sql_escape($md5) . '"
								WHERE attachment_id = ' . $row['attachment_id'];
							$this->db->sql_query($sql);
						}
					}
				}
				$this->db->sql_freeresult($result);
			break;
		}
	}

	/**
	* Sync authors
	*
	* @param string $mode		The mode (count)
	* @param int|bool $user_id	(Optional) User id to limit to. Defaults to false.
	*/
	public function authors($mode, $user_id = false)
	{
		switch ($mode)
		{
			case 'count' :
				// Reset the count for all authors first
				$sql_ary = array(
					'author_contribs' => 0,
				);
				foreach (\titania_types::$types as $type_id => $class)
				{
					if (!isset($class->author_count))
					{
						continue;
					}

					$sql_ary[$class->author_count] = 0;
				}
				$sql = 'UPDATE ' . $this->authors_table . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary);
				$this->db->sql_query($sql);

				$sql = 'SELECT DISTINCT(contrib_user_id) AS user_id
					FROM ' . $this->contribs_table .
					(($user_id) ? ' WHERE contrib_user_id = ' . (int) $user_id : '');
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql_ary = $this->_get_author_count($row['user_id']);

					// sql_affectedrows does not work if the count is 0 across the board
					$sql = 'SELECT author_id
						FROM ' . $this->authors_table . '
						WHERE user_id = ' . (int) $row['user_id'];
					$this->db->sql_query($sql);
					$author_id = $this->db->sql_fetchfield('author_id');
					$this->db->sql_freeresult();

					if ($author_id)
					{
						// Increment/Decrement the contrib counter for the new owner
						$sql = 'UPDATE ' . $this->authors_table . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE user_id = ' . (int) $row['user_id'];
						$this->db->sql_query($sql);
					}
					else
					{
						$author = new \titania_author($row['user_id']);
						$author->__set_array($sql_ary);
						$author->submit();
					}
				}
				$this->db->sql_freeresult($result);
			break;
		}
	}

	/**
	* Sync categories
	*
	* @param string $mode		The mode (count)
	* @param int|bool $cat_id	(Optional) Category id to limit to. Defaults to false.
	*/
	public function categories($mode, $cat_id = false)
	{
		switch ($mode)
		{
			case 'count' :
				$sql = 'SELECT category_id, category_contribs
					FROM ' . $this->categories_table .
					(($cat_id) ? ' WHERE category_id = ' . (int) $cat_id : '');
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$contrib_count = $this->_get_contrib_count($row['category_id']);
					if ($row['category_contribs'] != $contrib_count)
					{
						$sql = 'UPDATE ' . $this->categories_table . '
							SET category_contribs = ' . (int) $contrib_count . '
							WHERE category_id = ' . (int) $row['category_id'];
						$this->db->sql_query($sql);
					}
				}
				$this->db->sql_freeresult($result);
			break;
		}
	}

	/**
	* Sync contribs
	*
	* @param string $mode			The mode (validated)
	* @param int|bool $contrib_id	Contrib id to limit to
	* @param int|bool $start		For indexing only
	* @param int|bool $limit		For indexing only
	*/
	public function contribs($mode, $contrib_id = false, $start = false, $limit = false)
	{
		switch ($mode)
		{
			case 'validated' :
				$sql = 'SELECT contrib_id, contrib_status
					FROM ' . $this->contribs_table .
					(($contrib_id) ? ' AND contrib_id = ' . (int) $contrib_id : '');
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql = 'SELECT COUNT(revision_id) AS cnt
						FROM ' . $this->revisions_table . '
						WHERE contrib_id = ' . $row['contrib_id'] . '
							AND revision_status = ' . TITANIA_REVISION_APPROVED;
					$result1 = $this->db->sql_query($sql);
					$cnt = $this->db->sql_fetchfield('cnt', $result1);
					$this->db->sql_freeresult($result1);

					if (($cnt > 0 && $row['contrib_status'] == TITANIA_CONTRIB_NEW) || ($cnt == 0 && $row['contrib_status'] == TITANIA_CONTRIB_APPROVED))
					{
						$sql = 'UPDATE ' . $this->contribs_table . ' SET contrib_status = ' . (($cnt > 0) ? TITANIA_CONTRIB_APPROVED : TITANIA_CONTRIB_NEW) . '
							WHERE contrib_id = ' . $row['contrib_id'];
						$this->db->sql_query($sql);
					}
				}
				$this->db->sql_freeresult($result);
			break;

			case 'faq_count' :
				$contribs = array();
				$sql = 'SELECT faq_access, contrib_id
					FROM ' . $this->contrib_faq_table;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$flags = count::update_flags($row['faq_access']);
					$contribs[$row['contrib_id']] = count::increment(((isset($contribs[$row['contrib_id']])) ? $contribs[$row['contrib_id']] : ''), $flags);
				}
				$this->db->sql_freeresult($result);

				foreach ($contribs as $contrib_id => $count)
				{
					$sql = 'UPDATE ' . $this->contribs_table . '
						SET contrib_faq_count = \'' . $this->db->sql_escape($count) . '\'
						WHERE contrib_id = ' . $contrib_id;
					$this->db->sql_query($sql);
				}
			break;

			case 'index' :
				$data = $contribs = array();

				$sql = 'SELECT * FROM ' . $this->contribs_table . '
						ORDER BY contrib_id ASC';
				if ($start === false || $limit === false)
				{
					$result = $this->db->sql_query($sql);
				}
				else
				{
					$result = $this->db->sql_query_limit($sql, (int) $limit, (int) $start);
				}
				while ($row = $this->db->sql_fetchrow($result))
				{
					$data[$row['contrib_id']] = array(
						'object_type'	=> TITANIA_CONTRIB,
						'object_id'		=> $row['contrib_id'],

						'title'			=> $row['contrib_name'],
						'text'			=> $row['contrib_desc'],
						'text_uid'		=> $row['contrib_desc_uid'],
						'text_bitfield'	=> $row['contrib_desc_bitfield'],
						'text_options'	=> $row['contrib_desc_options'],
						'author'		=> $row['contrib_user_id'],
						'date'			=> $row['contrib_last_update'],
						'url'			=> serialize(array(
							'contrib_type'	=> \titania_types::$types[$row['contrib_type']]->url,
							'contrib'		=> $row['contrib_name_clean'],
						)),
						'approved'		=> (in_array($row['contrib_status'], array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))) ? true : false,
						'categories'	=> explode(',', $row['contrib_categories']),
					);

					$contribs[] = $row['contrib_id'];
				}
				$this->db->sql_freeresult($result);

				$sql = 'SELECT DISTINCT contrib_id, phpbb_version_branch, phpbb_version_revision
					FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
					WHERE ' . $this->db->sql_in_set('contrib_id', $contribs) . '
						AND revision_validated = 1';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$phpbb_versions[$row['contrib_id']][] = $row; 
				}
				$this->db->sql_freeresult($result);

				foreach ($data as $contrib_id => $contrib_data)
				{
					if (isset($phpbb_versions[$contrib_id]))
					{
						$data[$contrib_id]['phpbb_versions'] = versions::order_phpbb_version_list_from_db(
							$this->cache,
							$phpbb_versions[$contrib_id]
						);
					}
				}

				$this->search_manager->mass_index($data);
			break;
		}
	}

	/**
	 * Sync topics
	 *
	 * @param string $mode			The mode (post_count - topics_posts field)
	 * @param int|bool $topic_id	(Optional) Topic id to limit to. Defaults to false.
	 */
	public function topics($mode, $topic_id = false)
	{
		switch ($mode)
		{
			// Sync the topics_posts field
			case 'post_count' :
				$sql = 'SELECT topic_id, topic_posts
					FROM ' . $this->topics_table .
					(($topic_id) ? ' WHERE topic_id = ' . (int) $topic_id : '') . '
					ORDER BY topic_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$post_count = $this->_get_post_count($row['topic_id']);
					if ($row['topic_posts'] != $post_count)
					{
						$sql = 'UPDATE ' . $this->topics_table . '
							SET topic_posts = "' . $post_count . '"
							WHERE topic_id = ' . (int) $row['topic_id'];
						$this->db->sql_query($sql);
					}
				}
				$this->db->sql_freeresult($result);
			break;

			case 'queue_discussion_category' :
				$sql = 'SELECT t.topic_id, c.contrib_type
					FROM ' . $this->topics_table . ' t, ' . $this->contribs_table . ' c
					WHERE c.contrib_id = t.parent_id
						AND t.topic_type = ' . TITANIA_QUEUE_DISCUSSION .
						(($topic_id) ? ' AND topic_id = ' . (int) $topic_id : '') . '
					ORDER BY topic_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . $this->topics_table . '
						SET topic_category = ' . $row['contrib_type'] . '
						WHERE topic_id = ' . $row['topic_id'];
					$this->db->sql_query($sql);
				}
				$this->db->sql_freeresult($result);
			break;
		}
	}

	/**
	 * Sync posts.
	 *
	 * @param string $mode
	 * @param int|bool $start	For indexing
	 * @param int|bool $limit	For indexing
	 */
	public function posts($mode, $start = false, $limit = false)
	{
		switch ($mode)
		{
			case 'index' :
				$data = array();
				$post = new \titania_post;

				$sql = 'SELECT p.*, t.topic_id, t.topic_type, t.topic_subject_clean, t.parent_id, q.queue_type, c.contrib_type
					FROM ' . $this->posts_table . ' p, ' . $this->topics_table . ' t 
					LEFT JOIN ' . TITANIA_QUEUE_TABLE . ' q
						ON (t.parent_id = q.queue_id AND t.topic_type = ' . TITANIA_QUEUE . ')
					LEFT JOIN ' . $this->contribs_table . ' c
						ON (t.parent_id = c.contrib_id AND t.topic_type <> ' . TITANIA_QUEUE . ')
					WHERE t.topic_id = p.topic_id
					ORDER BY p.post_id ASC';
				if ($start === false || $limit === false)
				{
					$result = $this->db->sql_query($sql);
				}
				else
				{
					$result = $this->db->sql_query_limit($sql, (int) $limit, (int) $start);
				}

				while ($row = $this->db->sql_fetchrow($result))
				{
					$post->__set_array($row);
					$post->topic->__set_array($row);

					$data[] = array(
						'object_type'	=> $post->post_type,
						'object_id'		=> $post->post_id,

						'parent_id'		=> $post->topic->parent_id,

						'title'			=> $post->post_subject,
						'text'			=> $post->post_text,
						'text_uid'		=> $post->post_text_uid,
						'text_bitfield'	=> $post->post_text_bitfield,
						'text_options'	=> $post->post_text_options,
						'author'		=> $post->post_user_id,
						'date'			=> $post->post_time,
						'url'			=> serialize($post->get_url_params()),
						'approved'		=> $post->post_approved,
						'access_level'	=> $post->post_access,
						'parent_contrib_type'	=> (int) ($post->post_type == TITANIA_QUEUE) ? $row['queue_type'] : $row['contrib_type'],
					);
				}
				$this->db->sql_freeresult($result);

				$this->search_manager->mass_index($data);
			break;
		}
	}

	/**
	 * Sync FAQ
	 *
	 * @param string $mode
	 */
	public function faqs($mode)
	{
		switch ($mode)
		{
			case 'index' :
				$this->search_manager->truncate(TITANIA_FAQ);

				$data = array();

				$sql = 'SELECT f.*, c.contrib_name_clean, c.contrib_type
					FROM ' . $this->contrib_faq_table . ' f, ' . $this->contribs_table . ' c
					WHERE c.contrib_id = f.contrib_id';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$data[] = array(
						'object_type'	=> TITANIA_FAQ,
						'object_id'		=> $row['faq_id'],

						'title'			=> $row['faq_subject'],
						'text'			=> $row['faq_text'],
						'text_uid'		=> $row['faq_text_uid'],
						'text_bitfield'	=> $row['faq_text_bitfield'],
						'text_options'	=> $row['faq_text_options'],
						'author'		=> 0,
						'date'			=> 0,
						'url'			=> serialize(array(
							'contrib_type'	=> \titania_types::$types[$row['contrib_type']]->url,
							'contrib'		=> $row['contrib_name_clean'],
							'id'			=> $row['faq_id'],
						)),
						'access_level'	=> $row['faq_access'],
					);
				}
				$this->db->sql_freeresult($result);

				$this->search_manager->mass_index($data);
			break;
		}
	}

	/**
	 * Sync queue
	 *
	 * @param string $mode
	 * @param int|bool $queue_id	(Optional) Queue id to limit to. Defaults to false.
	 */
	public function queue($mode, $queue_id = false)
	{
		switch ($mode)
		{
			case 'update_first_queue_post' :
				$queue = new \titania_queue;
				$contrib = new \titania_contribution;

				$sql = 'SELECT *
					FROM ' . $this->queue_table . ' q, ' . $this->contribs_table . ' c
					WHERE c.contrib_id = q.contrib_id' .
						(($queue_id) ? ' AND queue_id = ' . (int) $queue_id : '');
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$contrib->__set_array($row);
					$contrib->set_type($row['contrib_type']);
					$queue->__set_array($row);

					$queue->update_first_queue_post(false, $contrib);
				}
				$this->db->sql_freeresult($result);

				unset($queue);
			break;

			case 'revision_queue_id' :
				$sql = 'SELECT queue_id, revision_id
					FROM ' . $this->queue_table;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . $this->revisions_table . '
						SET revision_queue_id = ' . (int) $row['queue_id'] . '
						WHERE revision_id = ' . (int) $row['revision_id'];
					$this->db->sql_query($sql);
				}
				$this->db->sql_freeresult($result);
			break;
		}
	}

	/**
	 * Get contrib count for a category.
	 *
	 * @param $category_id
	 * @return int
	 */
	public function _get_contrib_count($category_id)
	{
		// Bundle up the children in a nice array
		$child_list = array($category_id);
		$sql = 'SELECT left_id, right_id
			FROM ' . $this->categories_table . '
			WHERE category_id = ' . (int) $category_id . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		$cat_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (!$cat_row)
		{
			return 0;
		}

		$sql = 'SELECT category_id
			FROM ' . $this->categories_table . '
			WHERE left_id > ' . $cat_row['left_id'] . '
				AND right_id < ' . $cat_row['right_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$child_list[] = $row['category_id'];
		}
		$this->db->sql_freeresult($result);


		$sql_ary = array(
			'SELECT'	=> 'COUNT(DISTINCT c.contrib_id) AS cnt',

			'FROM'		=> array(
				$this->contrib_in_categories_table => 'cic',
				$this->contribs_table => 'c',
			),

			'WHERE'		=> 'cic.contrib_id = c.contrib_id
				AND ' . $this->db->sql_in_set('cic.category_id', array_map('intval', $child_list)) . '
				AND c.contrib_visible = 1
				AND ' . $this->db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)),
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$this->db->sql_query($sql);
		$cnt = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult();

		return $cnt;
	}

	/**
	 * Get post counts for a topic.
	 *
	 * @param int $topic_id
	 * @return string	Returns counts in db storable form
	 */
	public function _get_post_count($topic_id)
	{
		$sql = 'SELECT COUNT(post_id) AS cnt
			FROM ' . $this->posts_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . access::TEAM_LEVEL . '
				AND post_deleted = 0
				AND post_approved = 1';
		$result = $this->db->sql_query($sql);
		$teams = $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt
			FROM ' . $this->posts_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . access::AUTHOR_LEVEL . '
				AND post_deleted = 0
				AND post_approved = 1';
		$result = $this->db->sql_query($sql);
		$authors = $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt
			FROM ' . $this->posts_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . access::PUBLIC_LEVEL . '
				AND post_deleted = 0
				AND post_approved = 1';
		$result = $this->db->sql_query($sql);
		$public = $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt
			FROM ' . $this->posts_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_deleted <> 0';
		$result = $this->db->sql_query($sql);
		$deleted = $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt
			FROM ' . $this->posts_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_deleted = 0
				AND post_approved = 0';
		$result = $this->db->sql_query($sql);
		$unapproved = $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		return count::to_db(array(
			'teams'			=> $teams,
			'authors'		=> $authors,
			'public'		=> $public,
			'deleted'		=> $deleted,
			'unapproved'	=> $unapproved,
		));
	}

	/**
	 * Get the contrib count for a user.
	 *
	 * @param int $user_id
	 * @return array
	 */
	public function _get_author_count($user_id)
	{
		$sql_ary = array(
			'author_contribs' => 0,
		);
		foreach (\titania_types::$types as $type_id => $class)
		{
			if (!isset($class->author_count))
			{
				continue;
			}

			$sql_ary[$class->author_count] = 0;
		}

		// Count the contribution totals for each user
		foreach (\titania_types::$types as $type_id => $class)
		{
			// Main authors
			$sql = 'SELECT COUNT(contrib_id) AS cnt
				FROM ' . $this->contribs_table . '
				WHERE contrib_type = ' . (int) $type_id . '
					AND contrib_user_id = ' . (int) $user_id . '
					AND ' . $this->db->sql_in_set('contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED));
			$this->db->sql_query($sql);
			$cnt = $this->db->sql_fetchfield('cnt');

			$sql_ary['author_contribs'] += $cnt;
			if (isset($class->author_count))
			{
				$sql_ary[$class->author_count] += $cnt;
			}

			// Co-authors
			$sql = 'SELECT COUNT(c.contrib_id) AS cnt
				FROM ' . $this->contrib_coauthors_table . ' cc, ' . $this->contribs_table . ' c
				WHERE c.contrib_type = ' . (int) $type_id . '
					AND cc.user_id = ' . (int) $user_id . '
					AND c.contrib_id = cc.contrib_id
					AND ' . $this->db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED));
			$this->db->sql_query($sql);
			$cnt = $this->db->sql_fetchfield('cnt');

			$sql_ary['author_contribs'] += $cnt;
			if (isset($class->author_count))
			{
				$sql_ary[$class->author_count] += $cnt;
			}
		}

		return $sql_ary;
	}
}
