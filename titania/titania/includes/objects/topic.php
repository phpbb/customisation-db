<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_database_object'))
{
	require TITANIA_ROOT . 'includes/core/object_database.' . PHP_EXT;
}

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

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	public function submit()
	{
		// @todo search indexer on posts (reindex all in case the topic_access level has changed))

		$this->topic_subject_clean = titania_url::url_slug($this->topic_subject);

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

		// Deleting all the posts results in the last post calling this topic to delete itself
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
		$flags = titania_count::get_flags($access_level, $is_mod, $is_mod);
		return titania_count::from_db($this->topic_posts, $flags);
	}

	/**
	 * Get the URL to this topic
	 *
	 * @param string|bool $action The topic action if any
	 */
	public function get_url($action = false)
	{
		$base = $append = false;
		titania_url::split_base_params($base, $append, $this->topic_url);

		$append = array_merge($append, array(
			$this->topic_subject_clean,
			't' => $this->topic_id,
		));

		if ($action)
		{
			$append['action'] = $action;
		}

		return titania_url::build_url($base, $append);
	}

	/**
	 * Get the parent URL for this topic
	 */
	public function get_parent_url()
	{
		$base = $append = false;
		titania_url::split_base_params($base, $append, $this->topic_url);

		return titania_url::build_url($base, $append);
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
		
		phpbb::_include('functions_display', 'topic_generate_pagination');

		// To find out if we have any posts that need approval
		$approved = titania_count::from_db($this->topic_posts, titania_count::get_flags(TITANIA_ACCESS_PUBLIC, false, false));
		$total = titania_count::from_db($this->topic_posts, titania_count::get_flags(TITANIA_ACCESS_PUBLIC, false, true));
		$sort = new titania_sort();

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
			'PAGINATION'					=> $sort->topic_generate_pagination(($this->get_postcount()-1), titania_url::append_url($this->get_url())),

			'U_NEWEST_POST'					=> ($this->unread) ? titania_url::append_url($this->get_url(), array('view' => 'unread', '#' => 'unread')) : '',
			'U_VIEW_TOPIC'					=> $this->get_url(),
			'U_VIEW_LAST_POST'				=> titania_url::append_url($this->get_url(), array('p' => $this->topic_last_post_id, '#p' => $this->topic_last_post_id)),

			'S_UNREAD_TOPIC'				=> ($this->unread) ? true : false,
			'S_ACCESS_TEAMS'				=> ($this->topic_access == TITANIA_ACCESS_TEAMS) ? true : false,
			'S_ACCESS_AUTHORS'				=> ($this->topic_access == TITANIA_ACCESS_AUTHORS) ? true : false,

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
	* Sync the first post data
	*
	* @param mixed $ignore_post_id false to ignore, int post_id to ignore the specified post_id (for when we are going to be deleting that post)
	*/
	public function sync_first_post($ignore_post_id = false)
	{
		$sql = 'SELECT p.post_id, p.post_time, p.post_subject, u.user_id, u.username, u.user_colour
			FROM ' . TITANIA_POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $this->topic_id . '
				AND p.post_access >= ' . $this->topic_access . '
				AND p.post_approved = 1
				AND p.post_deleted = 0
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
		$sql = 'SELECT p.post_id, p.post_time, p.post_subject, u.user_id, u.username, u.user_colour
			FROM ' . TITANIA_POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
			WHERE p.topic_id = ' . $this->topic_id . '
				AND p.post_access >= ' . $this->topic_access . '
				AND p.post_approved = 1
				AND p.post_deleted = 0
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
}
