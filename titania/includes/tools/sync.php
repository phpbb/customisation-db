<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
@set_time_limit(1200);

/**
 * Sync handler for Titania
 *
 * Hopefully we never need to use this, but we probably will at some point, so put all sync stuff in here for easy access (and not to take up extra space in other files when they will rarely, if ever, be needed)
 */
class titania_sync
{
	/**
	* Sync attachments
	*/
	public function attachments($mode, $attachment_id = false)
	{
		switch ($mode)
		{
			case 'hash' :
				$sql = 'SELECT * FROM ' . TITANIA_ATTACHMENTS_TABLE .
					(($attachment_id !== false) ? ' WHERE attachment_id = ' . (int) $attachment_id : '');
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$file = titania::$config->upload_path . utf8_basename($row['attachment_directory']) . '/' . utf8_basename($row['physical_filename']);
					if (file_exists($file))
					{
						$md5 = md5_file($file);

						if ($md5 != $row['hash'])
						{
							$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
								SET hash = \'' . phpbb::$db->sql_escape($md5) . '\'
								WHERE attachment_id = ' . $row['attachment_id'];
							phpbb::$db->sql_query($sql);
						}
					}
				}
				phpbb::$db->sql_freeresult($result);
			break;
		}
	}

	/**
	* Sync authors
	*
	* @param string $mode The mode (count)
	* @param int $user_id User id to limit to
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
				foreach (titania_types::$types as $type_id => $class)
				{
					if (!isset($class->author_count))
					{
						continue;
					}

					$sql_ary[$class->author_count] = 0;
				}
				$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . '
					SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary);
				phpbb::$db->sql_query($sql);

				$sql = 'SELECT DISTINCT(contrib_user_id) AS user_id FROM ' . TITANIA_CONTRIBS_TABLE .
					(($user_id) ? ' WHERE contrib_user_id = ' . (int) $user_id : '');
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$sql_ary = $this->_get_author_count($row['user_id']);

					// sql_affectedrows does not work if the count is 0 across the board
					$sql = 'SELECT author_id FROM ' . TITANIA_AUTHORS_TABLE . '
						WHERE user_id = ' . (int) $row['user_id'];
					phpbb::$db->sql_query($sql);
					$author_id = phpbb::$db->sql_fetchfield('author_id');
					phpbb::$db->sql_freeresult();

					if ($author_id)
					{
						// Increment/Decrement the contrib counter for the new owner
						$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . '
							SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE user_id = ' . $row['user_id'];
						phpbb::$db->sql_query($sql);
					}
					else
					{
						$author = new titania_author($row['user_id']);
						$author->__set_array($sql_ary);
						$author->submit();
					}
				}
				phpbb::$db->sql_freeresult($result);
			break;
		}
	}

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
				phpbb::$db->sql_freeresult($result);
			break;
		}
	}

	/**
	* Sync contribs
	*
	* @param string $mode The mode (validated)
	* @param int $contrib_id Contrib id to limit to
	* @param int|bool $start & $limit start and limit for indexing (only indexing!)
	*/
	public function contribs($mode, $contrib_id = false, $start = false, $limit = false)
	{
		switch ($mode)
		{
			case 'validated' :
				$sql = 'SELECT contrib_id, contrib_status FROM ' . TITANIA_CONTRIBS_TABLE .
					(($contrib_id) ? ' AND contrib_id = ' . (int) $contrib_id : '');
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$sql = 'SELECT COUNT(revision_id) AS cnt FROM ' . TITANIA_REVISIONS_TABLE . '
						WHERE contrib_id = ' . $row['contrib_id'] . '
							AND revision_status = ' . TITANIA_REVISION_APPROVED;
					$result1 = phpbb::$db->sql_query($sql);
					$cnt = phpbb::$db->sql_fetchfield('cnt', $result1);
					phpbb::$db->sql_freeresult($result1);

					if (($cnt > 0 && $row['contrib_status'] == TITANIA_CONTRIB_NEW) || ($cnt == 0 && $row['contrib_status'] == TITANIA_CONTRIB_APPROVED))
					{
						$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . ' SET contrib_status = ' . (($cnt > 0) ? TITANIA_CONTRIB_APPROVED : TITANIA_CONTRIB_NEW) . '
							WHERE contrib_id = ' . $row['contrib_id'];
						phpbb::$db->sql_query($sql);
					}
				}
				phpbb::$db->sql_freeresult($result);
			break;

			case 'faq_count' :
				$contribs = array();
				$sql = 'SELECT faq_access, contrib_id FROM ' . TITANIA_CONTRIB_FAQ_TABLE;
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$flags = titania_count::update_flags($row['faq_access']);
					$contribs[$row['contrib_id']] = titania_count::increment(((isset($contribs[$row['contrib_id']])) ? $contribs[$row['contrib_id']] : ''), $flags);
				}
				phpbb::$db->sql_freeresult($result);

				foreach ($contribs as $contrib_id => $count)
				{
					$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
						SET contrib_faq_count = \'' . phpbb::$db->sql_escape($count) . '\'
						WHERE contrib_id = ' . $contrib_id;
					phpbb::$db->sql_query($sql);
				}
			break;

			case 'index' :
				$data = array();

				$sql = 'SELECT * FROM ' . TITANIA_CONTRIBS_TABLE . '
						ORDER BY contrib_id ASC';
				if ($start === false || $limit === false)
				{
					$result = phpbb::$db->sql_query($sql);
				}
				else
				{
					$result = phpbb::$db->sql_query_limit($sql, (int) $limit, (int) $start);
				}
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$data[] = array(
						'object_type'	=> TITANIA_CONTRIB,
						'object_id'		=> $row['contrib_id'],

						'title'			=> $row['contrib_name'],
						'text'			=> $row['contrib_desc'],
						'text_uid'		=> $row['contrib_desc_uid'],
						'text_bitfield'	=> $row['contrib_desc_bitfield'],
						'text_options'	=> $row['contrib_desc_options'],
						'author'		=> $row['contrib_user_id'],
						'date'			=> $row['contrib_last_update'],
						'url'			=> titania_types::$types[$row['contrib_type']]->url . '/' . $row['contrib_name_clean'],
						'approved'		=> (((!titania::$config->require_validation || !titania_types::$types[$row['contrib_type']]->require_validation) && $row['contrib_status'] == TITANIA_CONTRIB_NEW) || in_array($row['contrib_status'], array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))) ? true : false,
					);
				}
				phpbb::$db->sql_freeresult($result);

				titania_search::mass_index($data);
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
						$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . ' SET topic_posts = \'' . $post_count . '\' WHERE topic_id = ' . $row['topic_id'];
						phpbb::$db->sql_query($sql);
					}
				}
				phpbb::$db->sql_freeresult($result);
			break;

			case 'queue_discussion_category' :
				$sql = 'SELECT t.topic_id, c.contrib_type FROM ' . TITANIA_TOPICS_TABLE . ' t, ' . TITANIA_CONTRIBS_TABLE . ' c
					WHERE c.contrib_id = t.parent_id
						AND t.topic_type = ' . TITANIA_QUEUE_DISCUSSION .
						(($topic_id) ? ' AND topic_id = ' . (int) $topic_id : '') . '
					ORDER BY topic_id ASC';
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
						SET topic_category = ' . $row['contrib_type'] . '
						WHERE topic_id = ' . $row['topic_id'];
					phpbb::$db->sql_query($sql);
				}
				phpbb::$db->sql_freeresult($result);
			break;
		}
	}

	public function posts($mode, $start = false, $limit = false)
	{
		switch ($mode)
		{
			case 'index' :
				$data = array();
				$post = new titania_post;

				$sql = 'SELECT p.*, t.topic_id, t.topic_type, t.topic_subject_clean, t.parent_id
					FROM ' . TITANIA_POSTS_TABLE . ' p, ' . TITANIA_TOPICS_TABLE . ' t
					WHERE t.topic_id = p.topic_id
					ORDER BY p.post_id ASC';
				if ($start === false || $limit === false)
				{
					$result = phpbb::$db->sql_query($sql);
				}
				else
				{
					$result = phpbb::$db->sql_query_limit($sql, (int) $limit, (int) $start);
				}

				while ($row = phpbb::$db->sql_fetchrow($result))
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
						'url'			=> titania_url::unbuild_url($post->get_url()),
						'approved'		=> $post->post_approved,
						'access_level'	=> $post->post_access,
					);
				}
				phpbb::$db->sql_freeresult($result);

				titania_search::mass_index($data);
			break;
		}
	}

	public function faqs($mode)
	{
		switch ($mode)
		{
			case 'index' :
				titania_search::truncate(TITANIA_FAQ);

				$data = array();

				$sql = 'SELECT f.*, c.contrib_name_clean, c.contrib_type
					FROM ' . TITANIA_CONTRIB_FAQ_TABLE . ' f, ' . TITANIA_CONTRIBS_TABLE . ' c
					WHERE c.contrib_id = f.contrib_id';
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
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
						'url'			=> titania_types::$types[$row['contrib_type']]->url . '/' . $row['contrib_name_clean'] . '/faq/f_' . $row['faq_id'],
						'access_level'	=> $row['faq_access'],
					);
				}
				phpbb::$db->sql_freeresult($result);

				titania_search::mass_index($data);
			break;
		}
	}

	public function queue($mode, $queue_id = false)
	{
		switch ($mode)
		{
			case 'update_first_queue_post' :
				$queue = new titania_queue;
				$contrib = new titania_contribution;

				$sql = 'SELECT * FROM ' . TITANIA_QUEUE_TABLE . ' q, ' . TITANIA_CONTRIBS_TABLE . ' c
					WHERE c.contrib_id = q.contrib_id' .
						(($queue_id) ? ' AND queue_id = ' . (int) $queue_id : '');
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$contrib->__set_array($row);
					$queue->__set_array($row);

					$queue->update_first_queue_post(false, $contrib);
				}
				phpbb::$db->sql_freeresult($result);

				unset($queue);
			break;

			case 'revision_queue_id' :
				$sql = 'SELECT queue_id, revision_id FROM ' . TITANIA_QUEUE_TABLE;
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . ' SET revision_queue_id = ' . (int) $row['queue_id'] . '
						WHERE revision_id = ' . (int) $row['revision_id'];
					phpbb::$db->sql_query($sql);
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
				AND ' . phpbb::$db->sql_in_set('cic.category_id', array_map('intval', $child_list)) . '
				AND c.contrib_visible = 1',
		);

		$validation_free = titania_types::find_validation_free();
		if (sizeof($validation_free) && titania::$config->require_validation)
		{
			$sql_ary['WHERE'] .= ' AND (' . phpbb::$db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) . '
				OR ' . phpbb::$db->sql_in_set('c.contrib_type', $validation_free) . ')';
		}

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
				AND post_access = ' . TITANIA_ACCESS_TEAMS . '
				AND post_deleted = 0
				AND post_approved = 1';
		$result = phpbb::$db->sql_query($sql);
		$teams = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . TITANIA_ACCESS_AUTHORS . '
				AND post_deleted = 0
				AND post_approved = 1';
		$result = phpbb::$db->sql_query($sql);
		$authors = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_access = ' . TITANIA_ACCESS_PUBLIC . '
				AND post_deleted = 0
				AND post_approved = 1';
		$result = phpbb::$db->sql_query($sql);
		$public = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_deleted <> 0';
		$result = phpbb::$db->sql_query($sql);
		$deleted = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND post_deleted = 0
				AND post_approved = 0';
		$result = phpbb::$db->sql_query($sql);
		$unapproved = phpbb::$db->sql_fetchfield('cnt', $result);
		phpbb::$db->sql_freeresult($result);

		return titania_count::to_db(array(
			'teams'			=> $teams,
			'authors'		=> $authors,
			'public'		=> $public,
			'deleted'		=> $deleted,
			'unapproved'	=> $unapproved,
		));
	}

	public function _get_author_count($user_id)
	{
		$sql_ary = array(
			'author_contribs' => 0,
		);
		foreach (titania_types::$types as $type_id => $class)
		{
			if (!isset($class->author_count))
			{
				continue;
			}

			$sql_ary[$class->author_count] = 0;
		}

		// Count the contribution totals for each user
		foreach (titania_types::$types as $type_id => $class)
		{
			// Main authors
			$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE . '
				WHERE contrib_type = ' . (int) $type_id . '
					AND contrib_user_id = ' . (int) $user_id .
					((titania::$config->require_validation && $class->require_validation) ? ' AND ' . phpbb::$db->sql_in_set('contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) : '');
			phpbb::$db->sql_query($sql);
			$cnt = phpbb::$db->sql_fetchfield('cnt');

			$sql_ary['author_contribs'] += $cnt;
			if (isset($class->author_count))
			{
				$sql_ary[$class->author_count] += $cnt;
			}

			// Co-authors
			$sql = 'SELECT COUNT(c.contrib_id) AS cnt FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . ' cc, ' . TITANIA_CONTRIBS_TABLE . ' c
				WHERE c.contrib_type = ' . (int) $type_id . '
					AND cc.user_id = ' . (int) $user_id . '
					AND c.contrib_id = cc.contrib_id' .
					((titania::$config->require_validation && $class->require_validation) ? ' AND ' . phpbb::$db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) : '');
			phpbb::$db->sql_query($sql);
			$cnt = phpbb::$db->sql_fetchfield('cnt');

			$sql_ary['author_contribs'] += $cnt;
			if (isset($class->author_count))
			{
				$sql_ary[$class->author_count] += $cnt;
			}
		}

		return $sql_ary;
	}
}
