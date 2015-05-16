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

use phpbb\titania\count;
use phpbb\titania\url\url;

/**
* Class to abstract titania topic
* @package Titania
*/
class titania_topic extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_TOPICS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'topic_id';

	/**
	* True if the currently visiting user has posted in this topic
	*
	* @var bool
	*/
	public $topic_posted = false;

	/**
	* Unread, additional unread fields to check array(unread_type => unread_id)
	*/
	public $unread = true;
	public $additional_unread_fields = array();

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/**
	 * Constructor class for titania topics
	 *
	 * @param int $topic_id The topic_id, 0 for making a new topic
	 */
	public function __construct($topic_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'topic_id'						=> array('default' => 0),
			'parent_id'						=> array('default' => 0), // contrib_id most of the time
			'topic_type'					=> array('default' => 0), // Post Type, Main TITANIA_ constants
			'topic_access'					=> array('default' => TITANIA_ACCESS_PUBLIC), // Access level, TITANIA_ACCESS_ constants
			'topic_category'				=> array('default' => 0), // Category for the topic. For the Tracker and stores the contrib_type for queue_discussion topics
			'topic_url'						=> array('default' => ''), // URL for the topic (simple unbuilt URL)

			'topic_status'					=> array('default' => 0), // Topic Status, use tags from the DB
			'topic_assigned'				=> array('default' => ''), // Topic assigned status; u- for user, g- for group (followed by the id).  For the tracker
			'topic_sticky'					=> array('default' => false),
			'topic_locked'					=> array('default' => false),
			'topic_approved'				=> array('default' => true),
			'topic_reported'				=> array('default' => false), // True if any posts in the topic are reported

			'topic_time'					=> array('default' => (int) titania::$time),

			'topic_posts'					=> array('default' => ''), // Post count; separated by : between access levels ('10:9:8' = 10 team; 9 Mod Author; 8 Public)
			'topic_views'					=> array('default' => 0), // View count

			'topic_subject'					=> array('default' => ''),
			'topic_subject_clean'			=> array('default' => ''),

			'topic_first_post_id'			=> array('default' => 0),
			'topic_first_post_user_id'		=> array('default' => 0),
			'topic_first_post_username'		=> array('default' => ''),
			'topic_first_post_user_colour'	=> array('default' => ''),
			'topic_first_post_time'			=> array('default' => (int) titania::$time),

			'topic_last_post_id'			=> array('default' => 0),
			'topic_last_post_user_id'		=> array('default' => 0),
			'topic_last_post_username'		=> array('default' => ''),
			'topic_last_post_user_colour'	=> array('default' => ''),
			'topic_last_post_time'			=> array('default' => (int) titania::$time),
			'topic_last_post_subject'		=> array('default' => ''),
		));

		$this->topic_id = $topic_id;
		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	public function submit()
	{
		// @todo search indexer on posts (reindex all in case the topic_access level has changed))

		$this->topic_subject_clean = url::generate_slug($this->topic_subject);

		parent::submit();

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Undelete this topic
	*/
	public function undelete()
	{
		$post = new titania_post;
		$post->topic = $this;

		$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $this->topic_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$post->__set_array($row);
			$post->set_sql_data($row);
			$post->undelete();
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Soft delete this topic
	*/
	public function soft_delete()
	{
		$post = new titania_post;
		$post->topic = $this;

		$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $this->topic_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$post->__set_array($row);
			$post->set_sql_data($row);
			$post->soft_delete();
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Hard delete the stuff for this topic
	*/
	public function delete()
	{
		$post = new titania_post;
		$post->topic = $this;

		$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $this->topic_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$post->__set_array($row);
			$post->delete();
		}
		phpbb::$db->sql_freeresult($result);

		// Remove any subscriptions to this topic
		$sql = 'DELETE FROM ' . TITANIA_WATCH_TABLE . '
			WHERE watch_object_type = ' . TITANIA_TOPIC . '
				AND watch_object_id = ' . $this->topic_id;
		phpbb::$db->sql_query($sql);

		// Remove any tracking for this topic
		titania_tracking::clear_item(TITANIA_TOPIC, $this->topic_id);

		// Delete the now empty topic
		$sql = 'DELETE FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE topic_id = ' . $this->topic_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	* Get the postcount for displaying
	*
	* @param int|bool $access_level Bool False to get the post count for the current user, access level id for finding from a specific level
	*
	* @return int The post count for the current user's access level
	*/
	public function get_postcount($access_level = false)
	{
		if ($access_level === false)
		{
			$access_level = titania::$access_level;
		}

		$is_mod = phpbb::$auth->acl_get('u_titania_mod_post_mod');
		$flags = count::get_flags($access_level, $is_mod, $is_mod);
		return count::from_db($this->topic_posts, $flags);
	}

	/**
	 * Get the URL to this topic
	 *
	 * @param string|bool $action	The topic action if any
	 * @param array $params			Additional parameters to add to the URL.	
	 */
	public function get_url($action = false, $params = array())
	{
		$params = array_merge(unserialize($this->topic_url), $params);

		switch ($this->topic_type)
		{
			case TITANIA_SUPPORT:
			case TITANIA_QUEUE_DISCUSSION:
				$controller = 'phpbb.titania.contrib.support.topic';
				$params['topic_id'] = $this->topic_id;
			break;

			case TITANIA_QUEUE:
				$controller = 'phpbb.titania.queue.item';
				$params['id'] = $this->parent_id;
			break;
		}

		if ($action)
		{
			$controller .= '.action';
			$params['action'] = $action;
		}

		return $this->controller_helper->route($controller, $params);
	}

	/**
	 * Get the parent URL for this topic
	 */
	public function get_parent_url()
	{
		$params = unserialize($this->topic_url);

		switch ($this->topic_type)
		{
			case TITANIA_SUPPORT:
			case TITANIA_QUEUE_DISCUSSION:
				$controller = 'phpbb.titania.contrib.support';
			break;

			case TITANIA_QUEUE:
				$controller = 'phpbb.titania.queue.item';
			break;
		}
		$base = $append = false;

		return $this->controller_helper->route($controller, $params);
	}

	/**
	* Generate topic status
	*/
	public function topic_folder_img(&$folder_img, &$folder_alt)
	{
		titania::_include('functions_display', 'titania_topic_folder_img');

		titania_topic_folder_img($folder_img, $folder_alt, $this->get_postcount(), $this->unread, $this->topic_posted, $this->topic_sticky, $this->topic_locked);
	}

	/**
	* Update topic posted mark
	*
	* Based on code from phpBB's functions.php and functions_posting.php
	*/
	public function update_posted_status($mode = 'add', $user_id = false)
	{
		$user_id = (int) ($user_id) ? $user_id : phpbb::$user->data['user_id'];
		$this->topic_id = (int) $this->topic_id;

		if ($mode == 'add')
		{
			phpbb::$db->sql_return_on_error(true);

			$sql_ary = array(
				'user_id'		=> $user_id,
				'topic_id'		=> $this->topic_id,
				'topic_posted'	=> 1
			);

			phpbb::$db->sql_query('INSERT INTO ' . TITANIA_TOPICS_POSTED_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

			phpbb::$db->sql_return_on_error(false);			
		}
		else if ($mode == 'remove')
		{
			$sql = 'SELECT post_id
				FROM ' . TITANIA_POSTS_TABLE . '
				WHERE post_user_id = ' . $user_id . ' AND topic_id = ' . $this->topic_id . '
					AND post_approved = 1 AND post_deleted = 0';
			phpbb::$db->sql_query_limit($sql, 1);
			$post_id = phpbb::$db->sql_fetchfield('post_id');

			if (!$post_id)
			{
				$sql = 'DELETE FROM ' . TITANIA_TOPICS_POSTED_TABLE . ' WHERE user_id = ' . $user_id . ' AND topic_id = ' . $this->topic_id;
				phpbb::$db->sql_query($sql);
			}
		}
	}

	/**
	* Assign details
	*
	* A little different from those in other classes, this one only returns the info ready for output
	*/
	public function assign_details()
	{
		// Tracking check
		$last_read_mark = titania_tracking::get_track(TITANIA_TOPIC, $this->topic_id, true);
		$last_read_mark = max($last_read_mark, titania_tracking::find_last_read_mark($this->additional_unread_fields, $this->topic_type, $this->parent_id));
		$this->unread = ($this->topic_last_post_time > $last_read_mark) ? true : false;

		$folder_img = $folder_alt = '';
		$this->topic_folder_img($folder_img, $folder_alt);

		// To find out if we have any posts that need approval
		$approved = count::from_db($this->topic_posts, count::get_flags(TITANIA_ACCESS_PUBLIC, false, false));
		$total = count::from_db($this->topic_posts, count::get_flags(TITANIA_ACCESS_PUBLIC, false, true));
		$u_new_post = '';

		if ($this->unread)
		{
			$u_new_post = $this->get_url(false, array(
				'view'	=> 'unread',
				'#' 	=> 'unread',
			));
		}

		$details = array(
			'TOPIC_ID'						=> $this->topic_id,
			'TOPIC_TYPE'					=> $this->topic_type,
			'TOPIC_ACCESS'					=> $this->topic_access,
			'TOPIC_STATUS'					=> $this->topic_status, // @todo build a function for outputting this
			'TOPIC_STICKY'					=> $this->topic_sticky,
			'TOPIC_LOCKED'					=> $this->topic_locked,
			'POSTS_APPROVED'				=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') && $total > $approved) ? false : true,
			'TOPIC_APPROVED'				=> (phpbb::$auth->acl_get('u_titania_mod_post_mod')) ? $this->topic_approved : true,
			'TOPIC_REPORTED'				=> (phpbb::$auth->acl_get('u_titania_mod_post_mod')) ? $this->topic_reported : false,
			'TOPIC_ASSIGNED'				=> $this->topic_assigned, // @todo output this to be something useful
			'TOPIC_REPLIES'					=> ($this->get_postcount() - 1), // Number of replies (posts minus the OP)
			'TOPIC_VIEWS'					=> $this->topic_views,
			'TOPIC_SUBJECT'					=> censor_text($this->topic_subject),

			'TOPIC_FIRST_POST_ID'			=> $this->topic_first_post_id,
			'TOPIC_FIRST_POST_USER_ID'		=> $this->topic_first_post_user_id,
			'TOPIC_FIRST_POST_USER_COLOUR'	=> $this->topic_first_post_user_colour,
			'TOPIC_FIRST_POST_USER_FULL'	=> get_username_string('full', $this->topic_first_post_user_id, $this->topic_first_post_username, $this->topic_first_post_user_colour, false, phpbb::append_sid('memberlist', 'mode=viewprofile')),
			'TOPIC_FIRST_POST_TIME'			=> phpbb::$user->format_date($this->topic_first_post_time),

			'TOPIC_LAST_POST_ID'			=> $this->topic_last_post_id,
			'TOPIC_LAST_POST_USER_ID'		=> $this->topic_last_post_user_id,
			'TOPIC_LAST_POST_USER_COLOUR'	=> $this->topic_last_post_user_colour,
			'TOPIC_LAST_POST_USER_FULL'		=> get_username_string('full', $this->topic_last_post_user_id, $this->topic_last_post_username, $this->topic_last_post_user_colour, false, phpbb::append_sid('memberlist', 'mode=viewprofile')),
			'TOPIC_LAST_POST_TIME'			=> phpbb::$user->format_date($this->topic_last_post_time),
			'TOPIC_LAST_POST_SUBJECT'		=> censor_text($this->topic_last_post_subject),

			'U_NEWEST_POST'					=> $u_new_post,
			'U_VIEW_TOPIC'					=> $this->get_url(),
			'U_VIEW_LAST_POST'				=> $this->get_url(false,
				array(
					'p'	=> $this->topic_last_post_id,
					'#'	=> 'p' . $this->topic_last_post_id,
				)
			),

			'S_UNREAD_TOPIC'				=> ($this->unread) ? true : false,
			'S_ACCESS_TEAMS'				=> ($this->topic_access == TITANIA_ACCESS_TEAMS) ? true : false,
			'S_ACCESS_AUTHORS'				=> ($this->topic_access == TITANIA_ACCESS_AUTHORS) ? true : false,

			'FOLDER_STYLE'					=> $folder_img,
			'FOLDER_IMG'					=> phpbb::$user->img($folder_img, $folder_alt),
			'FOLDER_IMG_SRC'				=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
			'FOLDER_IMG_ALT'				=> phpbb::$user->lang[$folder_alt],
			'FOLDER_IMG_WIDTH'				=> phpbb::$user->img($folder_img, '', false, '', 'width'),
			'FOLDER_IMG_HEIGHT'				=> phpbb::$user->img($folder_img, '', false, '', 'height'),
		);

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $details, $this);

		return $details;
	}

	/**
	* Check if unapproved posts and deleted posts should be included in sync.
	*/
	public function sync_hidden_post_inclusion()
	{
		$include = array('unapproved' => false, 'deleted' => false);
		$counts = count::from_db($this->topic_posts, false);
		$visible_posts = $counts['teams'] + $counts['authors'] + $counts['public'];

		if (!$visible_posts)
		{
			if (!$counts['deleted'])
			{
				$include['unapproved'] = true;
			}
			else
			{
				$include['deleted'] = true;
			}
		}
		return $include;
	}

	/**
	* Sync the first post data
	*
	* @param mixed $ignore_post_id false to ignore, int post_id to ignore the specified post_id (for when we are going to be deleting that post)
	*/
	public function sync_first_post($ignore_post_id = false)
	{
		$include = $this->sync_hidden_post_inclusion();

		$sql = 'SELECT p.post_id, p.post_time, p.post_subject, u.user_id, u.username, u.user_colour
			FROM ' . TITANIA_POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $this->topic_id . '
				AND p.post_access >= ' . $this->topic_access .
				((!$include['unapproved']) ? ' AND p.post_approved = 1' : '') .
				((!$include['deleted']) ? ' AND p.post_deleted = 0' : '') . '
				AND u.user_id = p.post_user_id ' .
				(($ignore_post_id !== false) ? ' AND p.post_id <> ' . (int) $ignore_post_id : '') . '
			ORDER BY post_time ASC';
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$update_row = phpbb::$db->sql_fetchrow($result);

		if ($update_row)
		{
			$this->__set_array(array(
				'topic_first_post_id'			=> $update_row['post_id'],
				'topic_first_post_user_id'		=> $update_row['user_id'],
				'topic_first_post_username'		=> $update_row['username'],
				'topic_first_post_user_colour'	=> $update_row['user_colour'],
				'topic_first_post_time'			=> $update_row['post_time'],
				'topic_first_post_subject'		=> $update_row['post_subject'],
			));
		}
	}

	/**
	* Sync the last post data
	*
	* @param mixed $ignore_post_id false to ignore, int post_id to ignore the specified post_id (for when we are going to be deleting that post)
	*/
	public function sync_last_post($ignore_post_id = false)
	{
		$include = $this->sync_hidden_post_inclusion();

		$sql = 'SELECT p.post_id, p.post_time, p.post_subject, u.user_id, u.username, u.user_colour
			FROM ' . TITANIA_POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $this->topic_id . '
				AND p.post_access >= ' . $this->topic_access .
				((!$include['unapproved']) ? ' AND p.post_approved = 1' : '') .
				((!$include['deleted']) ? ' AND p.post_deleted = 0' : '') . '
				AND u.user_id = p.post_user_id ' .
				(($ignore_post_id !== false) ? ' AND p.post_id <> ' . (int) $ignore_post_id : '') . '
			ORDER BY post_time DESC';
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$update_row = phpbb::$db->sql_fetchrow($result);

		if ($update_row)
		{
			$this->__set_array(array(
				'topic_last_post_id'			=> $update_row['post_id'],
				'topic_last_post_user_id'		=> $update_row['user_id'],
				'topic_last_post_username'		=> $update_row['username'],
				'topic_last_post_user_colour'	=> $update_row['user_colour'],
				'topic_last_post_time'			=> $update_row['post_time'],
				'topic_last_post_subject'		=> $update_row['post_subject'],
			));
		}
	}

	/**
	* Sync topic approved and access states.
	*
	* @param bool $delete_empty_topic Delete the topic if it does not contain any posts.
	* @param array $first_post_data Array containing info about first post in the form of array(post_approved => (bool), post_deleted => (bool)). If not provided, db is queried.
	*/
	public function sync_topic_state($delete_empty_topic = false, $first_post_data = false)
	{
		if (!$this->topic_id)
		{
			return;
		}

		$counts = count::from_db($this->topic_posts, false);
		$visible_posts = $counts['teams'] + $counts['authors'] + $counts['public'];
		$total_posts = array_sum($counts);

		if (!$total_posts && $delete_empty_topic)
		{
			$this->delete();
		}

		if (!$first_post_data)
		{
			$sql = 'SELECT post_deleted, post_approved
				FROM ' . TITANIA_POSTS_TABLE . '
				WHERE topic_id = ' . (int) $this->topic_id . '
				ORDER BY post_time ASC';
			$result = phpbb::$db->sql_query_limit($sql, 1);
			$first_post_data = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			if (!$first_post_data)
			{
				$this->topic_access = TITANIA_ACCESS_TEAMS;
				$this->topic_approved = 1;

				return;
			}
		}

		// Mark the topic as unapproved if there are no visible posts and the first one is unapproved.
		$this->topic_approved = (!$visible_posts && !$first_post_data['post_approved']) ? 0 : 1;

		// Adjust the topic access
		if ($visible_posts && !in_array($this->topic_type, array(TITANIA_QUEUE_DISCUSSION, TITANIA_QUEUE)))
		{
			$this->topic_access = TITANIA_ACCESS_PUBLIC;

			if (!$counts['public'])
			{
				$this->topic_access = TITANIA_ACCESS_AUTHORS;

				if (!$counts['authors'])
				{
					$this->topic_access = TITANIA_ACCESS_TEAMS;
				}
			}
		}
		else
		{
			// If no posts are visible and first post is deleted, then only the teams have access.
			$this->topic_access = (!$visible_posts && $first_post_data['post_deleted']) ? TITANIA_ACCESS_TEAMS : $this->topic_access;		
		}
	}

	/**
	* Acquire posts from another topic
	*
	* @param object $donor Object for topic where we'll be obtaining the posts from
	* @param array $post_ids Array of post id's that we want to acquire.
	* @param array $range Range of post times to transfer - array(min => (int), max => (int))
	*/
	public function acquire_posts($donor, $post_ids, $range = false)
	{
		if (!$this->topic_id || empty($post_ids))
		{
			return;
		}

		$sql_where = 'topic_id = ' . (int) $donor->topic_id . ' AND ';

		if (!empty($range))
		{
			$sql_where .= 'post_time >= ' . (int) $range['min'] . ' AND post_time <= ' . (int) $range['max'];
		}
		else
		{
			$sql_where .= phpbb::$db->sql_in_set('post_id', $post_ids);
		}

		$sql = 'SELECT *
			FROM ' . TITANIA_POSTS_TABLE . ' 
			WHERE ' . $sql_where;
		$result = phpbb::$db->sql_query($sql);
		$posts = phpbb::$db->sql_fetchrowset($result);
		phpbb::$db->sql_freeresult($result);

		if (empty($posts))
		{
			trigger_error('NO_POSTS');
		}

		// Update posts before resynchronizing topic
		$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
			SET topic_id = ' . (int) $this->topic_id . ',
				post_url = "' . phpbb::$db->sql_escape($this->topic_url) . '",
				post_type = ' . (int) $this->topic_type . '
			WHERE ' . $sql_where;
		phpbb::$db->sql_query($sql);

		$new_post = new titania_post();
		$old_post = new titania_post();
		$new_post->topic = $this;
		$old_post->topic = $donor;
		$posters = array();

		// Reindex posts and update topic post counts
		foreach ($posts as $post_data)
		{
			if (!$post_data['post_deleted'] && $post_data['post_approved'])
			{
				$posters[] = $post_data['post_user_id'];
			}

			// Reindex the post
			$new_post->__set_array($post_data);
			$new_post->sql_data = $post_data;
			$new_post->topic_id = $this->topic_id;
			$new_post->post_url = $this->topic_url;
			$new_post->post_type = $this->topic_type;
			$new_post->index();

			// Set the post_id to 0 so it's counted as a new reply
			$new_post->post_id = 0;
			$new_post->update_topic_postcount();

			// Decrease count for donor topic
			$old_post->__set_array($post_data);
			$old_post->sql_data = $post_data;
			$old_post->update_topic_postcount(true);
		}
		$posters = array_unique($posters);

		// Update posted status
		foreach ($posters as $user_id)
		{
			$this->update_posted_status('add', $user_id);
			$donor->update_posted_status('remove', $user_id);
		}

		$donor->topic_posts = $old_post->topic->topic_posts;
		$this->topic_posts = $new_post->topic->topic_posts;
		unset($old_post, $new_post, $posts);

		// Resync topic
		$this->sync_topic_state(true);
		$this->sync_first_post();
		$this->sync_last_post();
		$this->submit();

		// Resync donor topic
		$donor->sync_topic_state(true);
		$donor->sync_first_post();
		$donor->sync_last_post();
		$donor->submit();
	}
}
