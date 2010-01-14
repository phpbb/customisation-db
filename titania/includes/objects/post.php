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
	protected $sql_table = TITANIA_POSTS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'post_id';

	/**
	* Text ready for storage
	*
	* @var bool
	*/
	private $text_parsed_for_storage = false;

	/**
	* Topic Object
	*
	* @var object
	*/
	public $topic = NULL;

	/**
	* Unread post id
	*
	* @var int|bool
	*/
	public $unread = false;

	/**
	 * Constructor class for titania posts
	 *
	 * @param int|string $type The type of post ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion.  Constants for the type can be sent instead of a string
	 * @param object|bool|int $topic The topic object, topic_id to load it ourselves for an existing topic, boolean false for making a new post (we will create the topic object)
	 * @param int $post_id The post_id, 0 for making a new post
	 */
	public function __construct($type = TITANIA_SUPPORT, $topic = false, $post_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'post_id'				=> array('default' => 0),
			'topic_id'				=> array('default' => 0),
			'post_type'				=> array('default' => 0), // Post Type, Main TITANIA_ constants
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

			'post_edit_user'		=> array('default' => 0), // The last user to edit the post
			'post_edit_reason'		=> array('default' => ''), // Reason for deleting/editing
			'post_delete_user'		=> array('default' => 0), // The last user to delete the post

			'post_subject'			=> array('default' => ''),
			'post_text'				=> array('default' => ''),
			'post_text_bitfield'	=> array('default' => ''),
			'post_text_uid'			=> array('default' => ''),
			'post_text_options'		=> array('default' => 7),
		));

		switch ($type)
		{
			case 'tracker' :
			case TITANIA_TRACKER :
				$this->post_type = TITANIA_TRACKER;
			break;

			case 'queue' :
			case TITANIA_QUEUE :
				$this->post_type = TITANIA_QUEUE;
			break;

			default :
				$this->post_type = TITANIA_SUPPORT;
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
			$this->topic = new titania_topic($this->post_type, $topic);
			$this->topic->load();
		}
		else
		{
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
	* @param object $message The message object
	*/
	public function post_data($message)
	{
		$post_data = $message->request_data();

		$this->__set_array(array(
			'post_subject'			=> $post_data['subject'],
			'post_text'				=> $post_data['message'],
			'post_access'			=> $post_data['access'],
			'post_locked'			=> $post_data['lock'],
		));
		$this->topic->__set_array(array(
			'topic_sticky'			=> ($message->auth['sticky_topic']) ? $post_data['sticky_topic'] : $this->topic->topic_sticky,
			'topic_locked'			=> ($message->auth['lock_topic']) ? $post_data['lock_topic'] : $this->topic->topic_locked,
		));

		$this->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);
	}

	/**
	 * Get the url for the post
	 *
	 * @param string|bool $action An action (anchor will not be included if an action is sent)
	 * @param bool $use_anchor False to leave the anchor off of the URL
	 */
	public function get_url($action = false, $use_anchor = true)
	{
		$append = array(
			'p' => $this->post_id,
		);

		if ($action)
		{
			$append['action'] = $action;
		}
		else if ($use_anchor)
		{
			$append['#p'] = $this->post_id;
		}

		if (is_object($this->topic))
		{
			return titania_url::append_url($this->topic->get_url(), $append);
		}

		switch ($this->post_type)
		{
			case TITANIA_TRACKER :
				$page = 'tracker';
			break;

			case TITANIA_QUEUE :
				// We use a different URL completely
				return titania_url::build_url('manage/queue', $append);
			break;

			default :
				$page = 'support';
			break;
		}

		return titania_url::build_url($page, $append);
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
			'access'	=> $this->post_access,
			'locked'	=> $this->post_locked,

			'topic_sticky'	=> $this->topic->topic_sticky,
			'topic_locked'	=> $this->topic->topic_locked,

			'object_type'	=> $this->post_type,
			'object_id'		=> $this->post_id,
		));
	}

	/**
	* Check if the current user has permission to do something
	*
	* @param string $option The auth option to check ('post', 'edit', 'soft_delete', 'hard_delete')
	* @param object $contrib The contrib object this is for (false to use titania::$contrib)
	*
	* @return bool True if they have permission False if not
	*/
	public function acl_get($option, $contrib = false)
	{
		if ($contrib === false && isset($this->topic->contrib) && is_object($this->topic->contrib))
		{
			$contrib = $this->topic->contrib;
		}
		else if ($contrib === false)
		{
			$contrib = titania::$contrib;
		}

		// First check anonymous/bots for things they can *never* do
		$no_anon = array('edit', 'soft_delete', 'undelete', 'hard_delete');
		$no_bot = array('post', 'edit', 'soft_delete', 'undelete', 'hard_delete');
		if ((!phpbb::$user->data['is_registered'] && in_array($option, $no_anon)) || (phpbb::$user->data['is_bot'] && in_array($option, $no_bot)))
		{
			return false;
		}

		$is_poster = ($this->post_user_id == phpbb::$user->data['user_id']) ? true : false; // Poster
		$is_author = ($contrib->is_author || $contrib->is_active_coauthor) ? true : false; // Contribution author
		$is_deleter = ($this->post_delete_user == phpbb::$user->data['user_id']) ? true : false;

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

			case 'delete' :
				return ($this->post_deleted) ? $this->acl_get('hard_delete', $contrib) : $this->acl_get('soft_delete', $contrib);
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
				if (($is_poster && $is_deleter && phpbb::$auth->acl_get('titania_post_delete_own')) || // Is poster and can delete own and did delete their own
					($is_author && $is_deleter && phpbb::$auth->acl_get('titania_post_mod_own')) || // Is contrib author and can moderate own and did delete the message
					phpbb::$auth->acl_get('titania_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

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

		// Create the topic if required
		if (!$this->topic->topic_id)
		{
			$this->topic->__set_array(array(
				'topic_access'		=> $this->post_access,
				'topic_approved'	=> $this->post_approved,
				'topic_user_id'		=> $this->post_user_id,
				'topic_subject'		=> $this->post_subject,
			));

			$this->topic->submit();

			$this->topic_id = $this->topic->topic_id;
		}

		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage();
		}

		parent::submit();

		// If no topic_id it means we are creating a new topic, so we need to set the first_post_ data.
		// Respect the post_time!  If for some reason we want to insert a post before the first one...
		if (!$this->topic->topic_first_post_id || $this->topic->topic_first_post_time > $this->post_time)
		{
			$this->topic->__set_array(array(
				'topic_first_post_id'			=> $this->post_id,
				'topic_first_post_user_id'		=> $this->post_user_id,
				'topic_first_post_username'		=> phpbb::$user->data['username'],
				'topic_first_post_user_colour'	=> phpbb::$user->data['user_colour'],
				'topic_first_post_time'			=> $this->post_time,

				'topic_time'					=> $this->post_time,
			));
		}

		// Respect the post_time!  If for some reason we want to insert a post before the last one...
		if (!$this->topic->topic_last_post_id || $this->topic->topic_last_post_time < $this->post_time)
		{
			$this->topic->__set_array(array(
				'topic_last_post_id'			=> $this->post_id,
				'topic_last_post_user_id'		=> $this->post_user_id,
				'topic_last_post_username'		=> phpbb::$user->data['username'],
				'topic_last_post_user_colour'	=> phpbb::$user->data['user_colour'],
				'topic_last_post_time'			=> $this->post_time,
				'topic_last_post_subject'		=> $this->post_subject,
			));
		}

		// Gotta update the topic again with the first/last post data and update teh post count
		$this->topic->update_postcount($this->post_access, false, false);
		$this->topic->submit();
	}

	/**
	* Edit a post
	*/
	public function edit()
	{
		if (empty($this->sql_data))
		{
			throw new exception('Submitting an edited post requires you load it through the load() function (we require the original information).');
		}

		if (!$this->post_id)
		{
			return false;
		}

		if (!$this->acl_get('edit'))
		{
			trigger_error('NO_AUTH');
		}

		if ($this->post_id == $this->topic->topic_first_post_id)
		{
			$this->topic->__set_array(array(
				'topic_access'		=> $this->post_access,
				'topic_user_id'		=> $this->post_user_id,
				'topic_time'		=> $this->post_time,
				'topic_subject'		=> $this->post_subject,
			));
		}

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
		$this->post_delete_user = phpbb::$user->data['user_id'];
		$this->post_edit_reason = $reason;

		// A bit of a hack here - assuming team access can view soft deleted posts (and that even if it is wrong, perfect accuracy isn't a big deal for teams)
		$this->topic->update_postcount(TITANIA_ACCESS_TEAMS, $this->post_access, false);

		$this->topic->submit();

		parent::submit();
	}

	/**
	* Undelete a post
	*/
	public function undelete()
	{
		if (!$this->acl_get('undelete'))
		{
			trigger_error('NO_AUTH');
		}

		// Reverse the hack for soft delete
		$this->topic->update_postcount($this->post_access, TITANIA_ACCESS_TEAMS, false);

		$this->post_deleted = 0;
		$this->post_delete_user = 0;

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

		$this->topic->update_postcount(false, $this->post_access, false);

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

	/**
	* Reparse the post text without editing (or with editing, just not recieving the raw code from the user and doing an internal edit)
	*/
	public function reparse()
	{
		$for_edit = $this->generate_text_for_edit();
		$this->post_text = $for_edit['text'];

		// Emulate what happens when sent from the user
		$this->post_text = html_entity_decode($this->post_text);
		set_var($this->post_text, $this->post_text, 'string', true);

		$this->generate_text_for_storage($for_edit['allow_bbcode'], $for_edit['allow_urls'], $for_edit['allow_smilies']);
	}

	/**
	* Assign details
	*
	* A little different from those in other classes, this one only returns the info ready for output
	*/
	public function assign_details()
	{
		$details = array(
			'POST_ID'						=> $this->post_id,
			'TOPIC_ID'						=> $this->topic_id,
			'POST_TYPE'						=> $this->post_type,
			'POST_ACCESS'					=> $this->post_access,
			'POST_LOCKED'					=> $this->post_locked,
			'POST_ATTACHMENT'				=> $this->post_attachment,
			'POST_USER_ID'					=> $this->post_user_id,
			'POST_IP'						=> $this->post_ip,
			'POST_TIME'						=> phpbb::$user->format_date($this->post_time),
			'POST_EDIT_REASON'				=> censor_text($this->post_edit_reason),
			'POST_SUBJECT'					=> censor_text($this->post_subject),
			'POST_TEXT'						=> $this->generate_text_for_display(),
			'EDITED_MESSAGE'				=> ($this->post_edited) ? sprintf(phpbb::$user->lang['EDITED_MESSAGE'], users_overlord::get_user($this->post_edit_user, '_full'), phpbb::$user->format_date($this->post_edited)) : '',
			'DELETED_MESSAGE'				=> ($this->post_deleted != 0) ? sprintf(phpbb::$user->lang['DELETED_MESSAGE'], users_overlord::get_user($this->post_delete_user, '_full'), phpbb::$user->format_date($this->post_deleted), $this->get_url('undelete')) : '',

			'U_VIEW'						=> $this->get_url(),
			'U_EDIT'						=> $this->acl_get('edit') ? $this->get_url('edit') : '',
			'U_DELETE'						=> $this->acl_get('delete') ? $this->get_url('delete') : '',
			'U_REPORT'						=> $this->get_url('report'),
			'U_WARN'						=> $this->get_url('warn'),
			'U_INFO'						=> $this->get_url('info'),
			'U_QUOTE'						=> $this->acl_get('post') ? $this->get_url('quote') : '',
			//U_MCP_APPROVE
			//U_MCP_REPORT

			'S_UNREAD_POST'					=> ($this->unread) ? true : false, // remember that you must set this up extra...
			'S_POST_APPROVED'				=> $this->post_approved,
			'S_POST_REPORTED'				=> $this->post_reported,
			'S_POST_DELETED'				=> ($this->post_deleted != 0) ? true : false,
		);

		return $details;
	}
}
