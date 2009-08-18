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
* Class to abstract titania posts
* @package Titania
*/
class titania_post extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table			= TITANIA_POSTS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field			= 'post_id';

	/**
	* Text ready for storage
	*
	* @var bool
	*/
	private $text_parsed_for_storage	= false;

	/**
	* Topic Object
	*
	* @var object
	*/
	public $topic					= NULL;

	/**
	 * Constructor class for titania posts
	 *
	 * @param int|string $type The type of post ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion.  Constants for the type can be sent instead of a string
	 * @param object|bool|int $topic The topic object, topic_id to load it ourselves for an existing topic, boolean false for making a new post (we will create the topic object)
	 * @param int $post_id The post_id, 0 for making a new post
	 */
	public function __construct($type, $topic = false, $post_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'post_id'				=> array('default' => 0),
			'topic_id'				=> array('default' => 0),
			'post_type'				=> array('default' => 0), // Post Type, TITANIA_POST_ constants
			'post_access'			=> array('default' => TITANIA_ACCESS_PUBLIC), // Access level, TITANIA_ACCESS_ constants

			'post_locked'			=> array('default' => false),
			'post_approved'			=> array('default' => true),
			'post_reported'			=> array('default' => false),
			'post_attachment'		=> array('default' => false),

			'post_user_id'			=> array('default' => (int) phpbb::$user->data['user_id']),
			'post_ip'				=> array('default' => phpbb::$user->ip),

			'post_time'				=> array('default' => (int) titania::$time),
			'post_edited'			=> array('default' => 0), // Post edited; 0 for not edited, timestamp if (when) last edited
			'post_deleted'			=> array('default' => 0), // Post deleted; 0 for not edited, timestamp if (when) last edited

			'post_edit_user'		=> array('default' => 0), // The last user to edit/delete the post
			'post_edit_reason'		=> array('default' => ''), // Reason for deleting/editing

			'post_subject'			=> array('default' => ''),
			'post_text'				=> array('default' => ''),
			'post_text_bitfield'	=> array('default' => ''),
			'post_text_uid'			=> array('default' => ''),
			'post_text_options'		=> array('default' => 7),
		));

		switch ($type)
		{
			case 'tracker' :
			case TITANIA_POST_TRACKER :
				$this->post_type = TITANIA_POST_TRACKER;
			break;

			case 'queue' :
			case TITANIA_POST_QUEUE :
				$this->post_type = TITANIA_POST_QUEUE;
			break;

			case 'review' :
			case TITANIA_POST_REVIEW :
				$this->post_type = TITANIA_POST_REVIEW;
			break;

			default :
				$this->post_type = TITANIA_POST_DEFAULT;
			break;
		}

		$this->post_id = $post_id;

		// copy/create the topic object
		if (is_object($topic))
		{
			$this->topic = $topic;
		}
		else if (is_int($topic))
		{
			titania::load_object('topic');
			$this->topic = new titania_topic($this->post_type, $topic);
			$this->topic->load();
		}
		else
		{
			titania::load_object('topic');
			$this->topic = new titania_topic($this->post_type);
		}
	}

	/**
	* Validate that all the data is correct
	*
	* @return array empty array on success, array with (string) errors ready for output on failure
	*/
	public function validate()
	{
		$error = array();

		if (utf8_clean_string($this->post_subject) === '')
		{
			$error[] = phpbb::$user->lang['EMPTY_SUBJECT'];
		}

		$message_length = utf8_strlen($this->post_text);
		if ($message_length < (int) phpbb::$config['min_post_chars'])
		{
			$error[] = sprintf(phpbb::$user->lang['TOO_FEW_CHARS_LIMIT'], $message_length, (int) phpbb::$config['min_post_chars']);
		}
		else if ($message_length > (int) phpbb::$config['max_post_chars'])
		{
			$error[] = sprintf($user->lang['TOO_MANY_CHARS_POST'], $message_length, (int) phpbb::$config['max_post_chars']);
		}

		return $error;
	}

	/**
	* Submit data in the post_data format (from includes/tools/message.php)
	*
	* @param mixed $post_data
	*/
	public function post_data($post_data)
	{
		$this->__set_array(array(
			'post_subject'		=> $post_data['subject'],
			'post_text'			=> $post_data['message'],
			'post_access'		=> $post_data['access'],
			'post_locked'		=> $post_data['lock_post'],
		));
		$this->topic->__set_array(array(
			'topic_sticky'		=> $post_data['sticky_topic'],
			'topic_locked'		=> $post_data['lock_topic'],
		));

		$this->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);
	}

	/**
	* Get the url for the post
	*/
	public function get_url()
	{
		$url = $this->topic->get_url();

		$url = titania::$url->append_url($url, array('p' => $this->post_id, '#p' => $this->post_id));
	}

	/**
	 * Parse text to store in database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 *
	 * @return void
	 */
	public function generate_text_for_storage($allow_bbcode = true, $allow_urls = true, $allow_smilies = true)
	{
		generate_text_for_storage($this->post_text, $this->post_text_uid, $this->post_text_bitfield, $this->post_text_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Parse text for display
	 *
	 * @return string text content from database for display
	 */
	public function generate_text_for_display()
	{
		return generate_text_for_display($this->post_text, $this->post_text_uid, $this->post_text_bitfield, $this->post_text_options);
	}

	/**
	 * Parse text for edit
	 *
	 * @return array of data for editing
	 */
	public function generate_text_for_edit()
	{
		return array_merge(generate_text_for_edit($this->post_text, $this->post_text_uid, $this->post_text_options), array(
			'options'	=> $this->post_text_options,
			'subject'	=> $this->post_subject,
		));
	}

	/**
	* Check if the current user has permission to do something
	*
	* @param string $option The auth option to check ('post', 'edit', 'soft_delete', 'hard_delete')
	*
	* @return bool True if they have permission False if not
	*/
	public function acl_get($option)
	{
		// First check anonymous/bots for things they can *never* do
		$no_anon = array('edit', 'soft_delete', 'undelete', 'hard_delete');
		$no_bot = array('post', 'edit', 'soft_delete', 'undelete', 'hard_delete');
		if ((!phpbb::$user->data['is_registered'] && in_array($option, $no_anon)) || (phpbb::$user->data['is_bot'] && in_array($option, $no_bot)))
		{
			return false;
		}

		$is_poster = ($this->post_user_id == phpbb::$user->data['user_id']) ? true : false; // Poster
		$is_author = titania::$access_level == TITANIA_ACCESS_AUTHORS; // Contribution author

		switch ($option)
		{
			case 'post' :
				if (phpbb::$auth->acl_get('titania_post') || // Can post
					($is_author && phpbb::$auth->acl_get('titania_post_mod_own')) || // Is contrib author and can moderate own
					phpbb::$auth->acl_get('titania_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'edit' :
				if (($is_poster && phpbb::$auth->acl_get('titania_post_edit_own')) || // Is poster and can edit own
					($is_author && phpbb::$auth->acl_get('titania_post_mod_own')) || // Is contrib author and can moderate own
					phpbb::$auth->acl_get('titania_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'soft_delete' :
				if (($is_poster && phpbb::$auth->acl_get('titania_post_delete_own')) || // Is poster and can delete own
					($is_author && phpbb::$auth->acl_get('titania_post_mod_own')) || // Is contrib author and can moderate own
					phpbb::$auth->acl_get('titania_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'undelete' :
			case 'hard_delete' :
				if (phpbb::$auth->acl_get('titania_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;
		}

		return false;
	}

	/**
	* Catch an attempt to use submit
	*/
	public function submit()
	{
		$error = $this->validate();

		if (sizeof($error))
		{
			return $error;
		}

		if (!$this->post_id)
		{
			return $this->post();
		}
		else
		{
			return $this->edit();
		}
	}

	/**
	* Catch an attempt to delete the post (must use the hard_delete function)
	*/
	public function delete()
	{
		$this->hard_delete();
	}

	/**
	* Post a post
	*/
	public function post()
	{
		if (!$this->acl_get('post'))
		{
			trigger_error('NO_AUTH');
		}

		$this->topic->__set_array(array(
			'topic_access'		=> $this->post_access,
			'topic_approved'	=> $this->post_approved,
			'topic_user_id'		=> $this->post_user_id,
			'topic_subject'		=> $this->post_subject,
		));

		// Update the postcount for the topic and submit the topic
		$this->topic->update_postcount($this->post_access, false, false);
		$this->topic->submit();

		$this->topic_id = $this->topic->topic_id;

		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage();
		}

		// If no topic_id it means we are creating a new topic, so we need to set the first_post_ data.
		if (!$this->topic->topic_first_post_id)
		{
			parent::submit();

			$this->topic->__set_array(array(
				'topic_first_post_id'			=> $this->post_id,
				'topic_first_post_user_id'		=> $this->post_user_id,
				'topic_first_post_username'		=> phpbb::$user->data['username'],
				'topic_first_post_user_colour'	=> phpbb::$user->data['user_colour'],
				'topic_first_post_time'			=> $this->post_time,
			));
		}
		else
		{
			parent::submit();
		}

		$this->topic->__set_array(array(
			'topic_last_post_id'			=> $this->post_id,
			'topic_last_post_user_id'		=> $this->post_user_id,
			'topic_last_post_username'		=> phpbb::$user->data['username'],
			'topic_last_post_user_colour'	=> phpbb::$user->data['user_colour'],
			'topic_last_post_time'			=> $this->post_time,
			'topic_last_post_subject'		=> $this->post_subject,
		));

		// Gotta update the topic again with the first/last post data
		$this->topic->submit();
	}

	/**
	* Edit a post
	*/
	public function edit()
	{
		if (!$this->post_id)
		{
			return false;
		}

		if (!$this->acl_get('edit'))
		{
			trigger_error('NO_AUTH');
		}

		$this->topic->__set_array(array(
			'topic_access'		=> $this->post_access,
			'topic_approved'	=> $this->post_approved,
			'topic_user_id'		=> $this->post_user_id,
			'topic_time'		=> $this->post_time,
			'topic_subject'		=> $this->post_subject,
		));

		// Update the postcount for the topic and submit the topic
		$this->topic->update_postcount($this->post_access, $this->sql_data['post_access'], false);
		$this->topic->submit();

		$this->topic_id = $this->topic->topic_id;

		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage();
		}

		return parent::submit();
	}

	/**
	* Soft delete a post
	*/
	public function soft_delete($reason = '')
	{
		if (!$this->acl_get('soft_delete'))
		{
			trigger_error('NO_AUTH');
		}

		$this->post_deleted = titania::$time;
		$this->post_edit_user = phpbb::$user->data['user_id'];
		$this->post_edit_reason = $reason;

		$this->topic->update_postcount($this->post_access, $this->sql_data['post_access'], false);

		// Set the visibility appropriately if no posts are visibile to the public/authors
		if ($this->topic->get_postcount(TITANIA_ACCESS_PUBLIC) == 0)
		{
			if ($this->topic->get_postcount(TITANIA_ACCESS_AUTHORS) == 0)
			{
				$this->topic->topic_access = TITANIA_ACCESS_TEAMS;
			}
			else
			{
				$this->topic->topic_access = TITANIA_ACCESS_AUTHORS;
			}
		}

		$this->topic->submit();

		parent::submit();
	}

	/**
	* Undelete a post
	*
	* @param int $access_level The new access level at which to display the undeleted post at (public, authors
	*/
	public function undelete($access_level = TITANIA_ACCESS_PUBLIC)
	{
		if (!$this->acl_get('undelete'))
		{
			trigger_error('NO_AUTH');
		}

		$this->post_access = $access_level;

		$this->topic->update_postcount($this->post_access, $this->sql_data['post_access'], false);

		// Set the visibility appropriately if no posts are visibile to the public/authors
		if ($this->topic->topic_access < $access_level)
		{
			$this->topic->topic_access = $access_level;
		}

		$this->topic->submit();

		parent::submit();
	}

	/**
	* Hard delete a post
	*/
	public function hard_delete()
	{
		if (!$this->acl_get('hard_delete'))
		{
			trigger_error('NO_AUTH');
		}

		$this->topic->update_postcount(false, $this->sql_data['post_access'], false);

		// Set the visibility appropriately if no posts are visibile to the public/authors
		if ($this->topic->get_postcount(TITANIA_ACCESS_PUBLIC) == 0)
		{
			$this->topic->topic_access = TITANIA_ACCESS_AUTHORS;
			if ($this->topic->get_postcount(TITANIA_ACCESS_AUTHORS) == 0)
			{
				$this->topic->topic_access = TITANIA_ACCESS_TEAMS;
				if ($this->topic->get_postcount(TITANIA_ACCESS_TEAMS) == 0)
				{
					// Hard delete the topic
					$this->topic->delete();
				}
			}
		}

		parent::delete();
	}
}
