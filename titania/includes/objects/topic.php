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
	protected $sql_table			= TITANIA_TOPICS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field			= 'topic_id';

	/**
	* True if the currently visiting user has posted in this topic
	*
	* @var bool
	*/
	public $topic_posted			= false;

	/**
	* Unread post id
	*
	* @var int|bool
	*/
	public $unread = false;

	/**
	 * Constructor class for titania topics
	 *
	 * @param int|string $type The type of topic ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion.  Constants for the type can be sent instead of a string
	 * @param int $topic_id The topic_id, 0 for making a new topic
	 */
	public function __construct($type = 0, $topic_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'topic_id'				=> array('default' => 0),
			'contrib_id'			=> array('default' => 0),
			'topic_type'			=> array('default' => 0), // Post Type, TITANIA_POST_ constants
			'topic_access'			=> array('default' => TITANIA_ACCESS_PUBLIC), // Access level, TITANIA_ACCESS_ constants
			'topic_category'		=> array('default' => 0), // Category for the topic. For the Tracker

			'topic_status'			=> array('default' => 0), // Topic Status, use tags from the DB
			'topic_assigned'		=> array('default' => ''), // Topic assigned status; u- for user, g- for group (followed by the id).  For the tracker
			'topic_sticky'			=> array('default' => false),
			'topic_locked'			=> array('default' => false),
			'topic_approved'		=> array('default' => true),
			'topic_reported'		=> array('default' => false), // True if any posts in the topic are reported
			'topic_deleted'			=> array('default' => false), // True if the topic is soft deleted

			'topic_time'			=> array('default' => (int) titania::$time),

			'topic_posts'			=> array('default' => ''), // Post count; separated by : between access levels ('10:9:8' = 10 team; 9 Mod Author; 8 Public)
			'topic_views'			=> array('default' => 0), // View count

			'topic_subject'			=> array('default' => ''),
			'topic_subject_clean'	=> array('default' => ''),

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
			case TITANIA_POST_TRACKER :
				$this->topic_type = TITANIA_POST_TRACKER;
			break;

			case 'queue' :
			case TITANIA_POST_QUEUE :
				$this->topic_type = TITANIA_POST_QUEUE;
			break;

			case 'review' :
			case TITANIA_POST_REVIEW :
				$this->topic_type = TITANIA_POST_REVIEW;
			break;

			default :
				$this->topic_type = TITANIA_POST_DEFAULT;
			break;
		}

		$this->topic_id = $topic_id;
	}

	public function submit()
	{
		$this->topic_subject_clean = titania::$url->url_slug($this->topic_subject);

		return parent::submit();
	}

	/**
	* Load tracking info for the current user
	*/
	public function load_tracking()
	{

	}

	/**
	* Update postcount (for adding/updating/deleting a post
	*
	* @param int|bool $new_access_level The new access level (false for hard deleting a post)
	* @param int|bool $old_access_level The old access level (false for adding a new post)
	* @param bool $auto_submit True to automatically update the topic in the database (only applies if $this->topic_id)
	*/
	public function update_postcount($new_access_level, $old_access_level = false, $auto_submit = true)
	{
		// Get the current postcount (may be empty string, so merge with 0, 0, 0)
		if (!$this->topic_posts)
		{
			$postcount = array(0, 0, 0);
		}
		else
		{
			$postcount = array_pad(explode(':', $this->topic_posts), 3, 0);
		}

		// If we are updating a post we need to clear the postcount from the old post
		if ($old_access_level !== false)
		{
			// If the two are the same just skip everything
			if ($old_access_level == $new_access_level)
			{
				return;
			}

			switch ($old_access_level)
			{
				case TITANIA_ACCESS_PUBLIC :
					$postcount[2]--;
				case TITANIA_ACCESS_AUTHORS :
					$postcount[1]--;
				case TITANIA_ACCESS_TEAMS :
					$postcount[0]--;
			}
		}

		// If we are deleting a post new access level should be false
		if ($new_access_level !== false)
		{
			switch ($new_access_level)
			{
				case TITANIA_ACCESS_PUBLIC :
					$postcount[2]++;
				case TITANIA_ACCESS_AUTHORS :
					$postcount[1]++;
				case TITANIA_ACCESS_TEAMS :
					$postcount[0]++;
			}
		}

		$this->topic_posts = implode(':', $postcount);

		// Autosubmit if wanted
		if ($auto_submit && $this->topic_id)
		{
			parent::submit();
		}
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

		$postcount = explode(':', $this->topic_posts);

		if (!isset($postcount[$access_level]))
		{
			return 0;
		}

		return $postcount[$access_level];
	}

	/**
	* Get the URL to this topic
	*/
	public function get_url()
	{
		switch ($this->topic_type)
		{
			case TITANIA_POST_TRACKER :
				$page = 'tracker';
			break;

			case TITANIA_POST_QUEUE :
				$page = 'queue';
			break;

			case TITANIA_POST_REVIEW :
				$page = 'review';
			break;

			default :
				$page = 'support';
			break;
		}

		if (!empty(titania::$contrib))
		{
			// We are *probably* visiting a contrib page
			$url = titania::$contrib->get_url($page);
		}
		else if (!empty(titania::$author))
		{
			// We are *probably* viewing the author's page
			if (isset($this->contrib_type) && isset($this->contrib_name_clean))
			{
				// Yay, generate good urls
				$url = titania::$url->build_url(titania::$types[$this->contrib_type]->url . '/' . $this->contrib_name_clean . '/' . $page);
			}
			else
			{
				$url = titania::$author->get_url($page);
			}
		}

		$url = titania::$url->append_url($url, array($this->topic_subject_clean, 't' => $this->topic_id));

		return $url;
	}

	/**
	* Generate topic status
	*/
	public function topic_folder_img(&$folder_img, &$folder_alt)
	{
		$folder = $folder_new = '';

		if ($this->topic_sticky)
		{
			$folder = 'sticky_read';
			$folder_new = 'sticky_unread';
		}
		else
		{
			$folder = 'topic_read';
			$folder_new = 'topic_unread';

			// Hot topic threshold is for posts in a topic, which is replies + the first post. ;)
			if (phpbb::$config['hot_threshold'] && ($this->get_postcount() + 1) >= phpbb::$config['hot_threshold'])
			{
				$folder .= '_hot';
				$folder_new .= '_hot';
			}
		}

		if ($this->topic_locked)
		{
			$folder .= '_locked';
			$folder_new .= '_locked';
		}

		$folder_img = ($this->unread) ? $folder_new : $folder;
		$folder_alt = ($this->unread) ? 'NEW_POSTS' : 'NO_NEW_POSTS';

		// Posted image?
		if ($this->topic_posted)
		{
			$folder_img .= '_mine';
		}
	}

	/**
	* Assign details
	*
	* A little different from those in other classes, this one only returns the info ready for output
	*/
	public function assign_details($data = false)
	{
		if ($data !== false)
		{
			$this->__set_array($data);
		}

		$folder_img = $folder_alt = '';
		$this->topic_folder_img($folder_img, $folder_alt);

		$details = array(
			'TOPIC_ID'						=> $this->topic_id,
			'TOPIC_TYPE'					=> $this->topic_type,
			'TOPIC_ACCESS'					=> $this->topic_access,
			'TOPIC_STATUS'					=> $this->topic_status, // @todo build a function for outputting this
			'TOPIC_STICKY'					=> $this->topic_sticky,
			'TOPIC_LOCKED'					=> $this->topic_locked,
			'TOPIC_APPROVED'				=> $this->topic_approved,
			'TOPIC_REPORTED'				=> $this->topic_reported,
			'TOPIC_DELETED'					=> $this->topic_deleted, // @todo output this to be something useful
			'TOPIC_ASSIGNED'				=> $this->topic_assigned, // @todo output this to be something useful
			'TOPIC_POSTS'					=> $this->get_postcount(),
			'TOPIC_VIEWS'					=> $this->topic_views,
			'TOPIC_SUBJECT'					=> censor_text($this->topic_subject),

			'TOPIC_FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
			'TOPIC_FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'TOPIC_FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'TOPIC_FOLDER_IMG_WIDTH'		=> phpbb::$user->img($folder_img, '', false, '', 'width'),
			'TOPIC_FOLDER_IMG_HEIGHT'		=> phpbb::$user->img($folder_img, '', false, '', 'height'),

			'TOPIC_FIRST_POST_ID'			=> $this->topic_first_post_id,
			'TOPIC_FIRST_POST_USER_ID'		=> $this->topic_first_post_user_id,
			'TOPIC_FIRST_POST_USER_COLOUR'	=> $this->topic_first_post_user_colour,
			'TOPIC_FIRST_POST_USER_FULL'	=> get_username_string('full', $this->topic_first_post_user_id, $this->topic_first_post_username, $this->topic_first_post_user_colour),
			'TOPIC_FIRST_POST_TIME'			=> phpbb::$user->format_date($this->topic_first_post_time),

			'TOPIC_LAST_POST_ID'			=> $this->topic_last_post_id,
			'TOPIC_LAST_POST_USER_ID'		=> $this->topic_last_post_user_id,
			'TOPIC_LAST_POST_USER_COLOUR'	=> $this->topic_last_post_user_colour,
			'TOPIC_LAST_POST_USER_FULL'		=> get_username_string('full', $this->topic_last_post_user_id, $this->topic_last_post_username, $this->topic_last_post_user_colour),
			'TOPIC_LAST_POST_TIME'			=> phpbb::$user->format_date($this->topic_last_post_time),
			'TOPIC_LAST_POST_SUBJECT'		=> censor_text($this->topic_last_post_subject),

			'U_NEWEST_POST'					=> ($this->unread) ? '' : '',
			'U_VIEW_TOPIC'					=> $this->get_url(),
			'U_VIEW_LAST_POST'				=> titania::$url->append_url($this->get_url(), array('p' => $this->topic_last_post_id, '#' => $this->topic_last_post_id)),

			'S_UNREAD_TOPIC'				=> ($this->unread) ? true : false,
		);

		return $details;
	}
}
