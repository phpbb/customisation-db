<?php
/**
*
* @package Titania
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
	* Unread
	*
	* @var bool
	*/
	public $unread = true;

	/**
	 * Constructor class for titania topics
	 *
	 * @param int|string $type The type of topic ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion.  Constants for the type can be sent instead of a string
	 * @param int $topic_id The topic_id, 0 for making a new topic
	 */
	public function __construct($type = TITANIA_SUPPORT, $topic_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'topic_id'						=> array('default' => 0),
			'parent_id'						=> array('default' => 0), // contrib_id most of the time
			'topic_type'					=> array('default' => 0), // Post Type, Main TITANIA_ constants
			'topic_access'					=> array('default' => TITANIA_ACCESS_PUBLIC), // Access level, TITANIA_ACCESS_ constants
			'topic_category'				=> array('default' => 0), // Category for the topic. For the Tracker
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

		switch ($type)
		{
			case 'tracker' :
			case TITANIA_TRACKER :
				$this->topic_type = TITANIA_TRACKER;
			break;

			case 'queue' :
			case TITANIA_QUEUE :
				$this->topic_type = TITANIA_QUEUE;
			break;

			default :
				$this->topic_type = TITANIA_SUPPORT;
			break;
		}

		$this->topic_id = $topic_id;
	}

	public function submit()
	{
		// @todo search indexer on posts (reindex all in case the topic_access level has changed))

		$this->topic_subject_clean = titania_url::url_slug($this->topic_subject);

		return parent::submit();
	}

	/**
	* Delete the stuff for this topic
	*/
	public function delete()
	{
		$post = new titania_post;
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

		$is_mod = phpbb::$auth->acl_get('m_titania_post_mod');
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

		$url = titania_url::build_url($base, $append);

		return $url;
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
		$this->unread = titania_tracking::is_unread(TITANIA_TOPIC, $this->topic_id, $this->topic_last_post_time);

		$folder_img = $folder_alt = '';
		$this->topic_folder_img($folder_img, $folder_alt);

		$details = array(
		//@todo - go through vars in output and make sure we output all needed and used ones
			'TOPIC_ID'						=> $this->topic_id,
			'TOPIC_TYPE'					=> $this->topic_type,
			'TOPIC_ACCESS'					=> $this->topic_access,
			'TOPIC_STATUS'					=> $this->topic_status, // @todo build a function for outputting this
			'TOPIC_STICKY'					=> $this->topic_sticky,
			'TOPIC_LOCKED'					=> $this->topic_locked,
			'TOPIC_APPROVED'				=> $this->topic_approved,
			'TOPIC_REPORTED'				=> $this->topic_reported,
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

			'U_NEWEST_POST'					=> ($this->unread) ? titania_url::append_url($this->get_url(), array('view' => 'unread', '#' => 'unread')) : '',
			'U_VIEW_TOPIC'					=> $this->get_url(),
			'U_VIEW_LAST_POST'				=> titania_url::append_url($this->get_url(), array('p' => $this->topic_last_post_id, '#p' => $this->topic_last_post_id)),

			'S_UNREAD_TOPIC'				=> ($this->unread) ? true : false,

			'TOPIC_FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
			'TOPIC_FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'TOPIC_FOLDER_IMG_WIDTH'		=> phpbb::$user->img($folder_img, '', false, '', 'width'),
			'TOPIC_FOLDER_IMG_HEIGHT'		=> phpbb::$user->img($folder_img, '', false, '', 'height'),
		);

		return $details;
	}
}
