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

use phpbb\titania\access;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\count;
use phpbb\titania\ext;
use phpbb\titania\message\message;

/**
* Class to abstract titania posts
* @package Titania
*/
class titania_post extends \phpbb\titania\entity\message_base
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
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = 'post_type';

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
	* Contrib type of parent
	*
	* @var int
	*/
	public $parent_contrib_type = 0;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var \phpbb\titania\search\manager */
	protected $search_manager;

	/** @var type_collection */
	protected $types;

	/**
	 * Constructor class for titania posts
	 *
	 * @param int|string $type The type of post ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion.  Constants for the type can be sent instead of a string
	 * @param object|bool|int $topic The topic object, topic_id to load it ourselves for an existing topic, boolean false for making a new post (we will create the topic object)
	 * @param int $post_id The post_id, 0 for making a new post
	 */
	public function __construct($type = ext::TITANIA_SUPPORT, $topic = false, $post_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'post_id'				=> array('default' => 0),
			'topic_id'				=> array('default' => 0),
			'post_type'				=> array('default' => 0), // Post Type, Main TITANIA_ constants
			'post_access'			=> array('default' => access::PUBLIC_LEVEL,	'message_field' => 'access'), // Access level, access class constants
			'post_url'				=> array('default' => ''), // URL for the post (simple unbuilt URL)

			'post_locked'			=> array('default' => false,	'message_field' => 'lock'),
			'post_approved'			=> array('default' => true),
			'post_reported'			=> array('default' => false),
			'post_attachment'		=> array('default' => false),

			'post_user_id'			=> array('default' => (int) phpbb::$user->data['user_id']),
			'post_ip'				=> array('default' => phpbb::$user->ip),

			'post_time'				=> array('default' => (int) titania::$time),
			'post_edited'			=> array('default' => 0), // Post edited; 0 for not edited, timestamp if (when) last edited
			'post_deleted'			=> array('default' => 0), // Post deleted; 0 for not edited, timestamp if (when) last edited

			'post_edit_time'		=> array('default' => 0), // The last time that user edit the post
			'post_edit_user'		=> array('default' => 0), // The last user to edit the post
			'post_edit_reason'		=> array('default' => ''), // Reason for deleting/editing
			'post_delete_user'		=> array('default' => 0), // The last user to delete the post

			'post_subject'			=> array('default' => '',	'message_field' => 'subject', 'max' => 255),
			'post_text'				=> array('default' => '',	'message_field' => 'message'),
			'post_text_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'post_text_uid'			=> array('default' => '',	'message_field' => 'message_uid'),
			'post_text_options'		=> array('default' => 7,	'message_field' => 'message_options'),
		));

		$this->db = phpbb::$container->get('dbal.conn');
		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$this->access = phpbb::$container->get('phpbb.titania.access');
		$this->search_manager = phpbb::$container->get('phpbb.titania.search.manager');
		$this->types = phpbb::$container->get('phpbb.titania.contribution.type.collection');

		switch ($type)
		{
			case 'queue_discussion' :
			case ext::TITANIA_QUEUE_DISCUSSION :
				$this->post_type = ext::TITANIA_QUEUE_DISCUSSION;
			break;

			case 'tracker' :
			case ext::TITANIA_TRACKER :
				$this->post_type = ext::TITANIA_TRACKER;
			break;

			case 'queue' :
			case ext::TITANIA_QUEUE :
				$this->post_type = ext::TITANIA_QUEUE;
			break;

			default :
				$this->post_type = ext::TITANIA_SUPPORT;
			break;
		}

		$this->post_id = $post_id;

		// copy/create the topic object
		if (is_object($topic))
		{
			$this->topic = $topic;

			if ($topic->topic_id)
			{
				$this->post_type = $topic->topic_type;
				$this->post_access = $topic->topic_access;
			}
		}
		else if (is_numeric($topic))
		{
			$this->topic = new titania_topic((int) $topic);
			if (!$this->topic->load())
			{
				trigger_error('NO_TOPIC');
			}

			$this->post_type = $this->topic->topic_type;
			$this->post_access = $this->topic->topic_access;
		}
		else
		{
			$this->topic = new titania_topic;
			$this->topic->topic_type = $this->post_type;
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Validate that all the data is correct
	*
	* @return array empty array on success, array with (string) errors ready for output on failure
	*/
	public function validate()
	{
		phpbb::$user->add_lang('posting');

		$error = array();

		if (utf8_clean_string($this->post_subject) === '')
		{
			$error[] = phpbb::$user->lang['EMPTY_SUBJECT'];
		}

		$this->post_subject = truncate_string($this->post_subject);

		$message_length = utf8_strlen($this->post_text);

		if ($message_length < (int) phpbb::$config['min_post_chars'])
		{
			if ($message_length)
			{
				$error[] = phpbb::$user->lang('CHARS_POST_CONTAINS', $message_length) . '<br />' .
					phpbb::$user->lang('TOO_FEW_CHARS_LIMIT', (int) phpbb::$config['min_post_chars']);
			}
			else
			{
				$error[] = phpbb::$user->lang['TOO_FEW_CHARS'];
			}
		}
		else if (phpbb::$config['max_post_chars'] != 0 && $message_length > (int) phpbb::$config['max_post_chars'])
		{
			$error[] = sprintf(phpbb::$user->lang['TOO_MANY_CHARS_POST'], $message_length, (int) phpbb::$config['max_post_chars']);
		}

		return $error;
	}

	/**
	 * {@inheritDoc}
	 */
	public function post_data(message $message)
	{
		$post_data = $message->request_data();

		$this->topic->__set_array(array(
			'topic_sticky'			=> ($message->auth['sticky_topic']) ? $post_data['sticky_topic'] : $this->topic->topic_sticky,
			'topic_locked'			=> ($message->auth['lock_topic']) ? $post_data['lock_topic'] : $this->topic->topic_locked,
		));

		parent::post_data($message);
	}

	/**
	 * Get the url for the post
	 *
	 * @param string|bool $action An action (anchor will not be included if an action is sent)
	 * @param bool $use_anchor False to leave the anchor off of the URL
	 */
	public function get_url($action = false, $use_anchor = true)
	{
		$params = $this->get_url_params($action, $use_anchor);
		$controller = 'phpbb.titania.';
		$controller .= ($this->post_type === ext::TITANIA_QUEUE) ? 'queue.item' : 'contrib.support.topic';

		if ($action)
		{
			$controller .= '.action';
		}

		return $this->controller_helper->route($controller, $params);
	}

	/**
	* Get the url parameters for the post
	*
	* @param string|bool $action An action (anchor will not be included if an action is sent)
	* @param bool $use_anchor False to leave the anchor off of the URL
	*/
	public function get_url_params($action = false, $use_anchor = true)
	{
		$params = unserialize($this->post_url);
		$params['p'] = $this->post_id;

		if ($this->post_type !== ext::TITANIA_QUEUE)
		{
			$params['topic_id'] = $this->topic_id;
		}

		if ($action)
		{
			$params['action'] = $action;
		}
		else if ($use_anchor)
		{
			$params['#'] = 'p' . $this->post_id;
		}
		return $params;
	}

	/**
	 * Parse text for edit
	 *
	 * @return array of data for editing
	 */
	public function generate_text_for_edit()
	{
		return array_merge(parent::generate_text_for_edit(), array(
			'topic_sticky'	=> $this->topic->topic_sticky,
			'topic_locked'	=> $this->topic->topic_locked,
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

		// Can never do anything if the topic access level is greater than current access level
		if (is_object($this->topic) && $this->topic->topic_access < $this->access->get_level())
		{
			return false;
		}

		$is_poster = ($this->post_user_id == phpbb::$user->data['user_id']) ? true : false; // Poster
		$is_author = (is_object($this->topic) && is_object(titania::$contrib) && titania::$contrib->contrib_id == $this->topic->parent_id && (titania::$contrib->is_author || titania::$contrib->is_active_coauthor)) ? true : false; // Contribution author
		$is_deleter = ($this->post_delete_user == phpbb::$user->data['user_id']) ? true : false;

		switch ($option)
		{
			case 'post' :
			case 'reply' :
				if (((!is_object($this->topic) || !$this->topic->topic_locked) && phpbb::$auth->acl_get('u_titania_post')) || // Can post
					($is_author && phpbb::$auth->acl_get('u_titania_post_mod_own')) || // Is contrib author and can moderate own
					phpbb::$auth->acl_get('u_titania_mod_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'edit' :
				if (($is_poster && !$this->post_locked && $this->post_access >= $this->access->get_level() && phpbb::$auth->acl_get('u_titania_post_edit_own')) || // Is poster and can edit own
					($is_author && !$this->post_locked && $this->post_access >= $this->access->get_level() && phpbb::$auth->acl_get('u_titania_post_mod_own')) || // Is contrib author and can moderate own
					phpbb::$auth->acl_get('u_titania_mod_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'delete' :
				return ($this->post_deleted) ? $this->acl_get('hard_delete') : $this->acl_get('soft_delete');
			break;

			case 'soft_delete' :
				if (($is_poster && !$this->post_locked && $this->post_access >= $this->access->get_level() && phpbb::$auth->acl_get('u_titania_post_delete_own')) || // Is poster and can delete own
					($is_author && !$this->post_locked && $this->post_access >= $this->access->get_level() && phpbb::$auth->acl_get('u_titania_post_mod_own')) || // Is contrib author and can moderate own
					phpbb::$auth->acl_get('u_titania_mod_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'undelete' :
				if (($is_poster && $is_deleter && !$this->post_locked && $this->post_access >= $this->access->get_level() && phpbb::$auth->acl_get('u_titania_post_delete_own')) || // Is poster and can delete own and did delete their own
					($is_author && $is_deleter && !$this->post_locked && $this->post_access >= $this->access->get_level() && phpbb::$auth->acl_get('u_titania_post_mod_own')) || // Is contrib author and can moderate own and did delete the message
					phpbb::$auth->acl_get('u_titania_mod_post_mod')) // Can moderate posts
				{
					return true;
				}
			break;

			case 'hard_delete' :
				if (phpbb::$auth->acl_get('u_titania_mod_post_mod')) // Can moderate posts
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
			$this->post();
		}
		else
		{
			$this->edit();
		}

		return true;
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
		// Create the topic if required
		if (!$this->topic->topic_id)
		{
			$this->topic->__set_array(array(
				'topic_type'		=> $this->post_type,
				'topic_access'		=> $this->post_access,
				'topic_approved'	=> $this->post_approved,
				'topic_user_id'		=> $this->post_user_id,
				'topic_subject'		=> $this->post_subject,
			));

			$this->topic->submit();
		}

		// Update the post count for the topic (before calling parent::submit())
		$this->update_topic_postcount();

		$this->topic_id = $this->topic->topic_id;
		$this->post_url = $this->topic->topic_url;

		parent::submit();

		// Post approved?
		if (!$this->post_approved)
		{
			// Setup the attention object and submit it
			$attention = new titania_attention;
			$attention->__set_array(array(
				'attention_type'		=> ext::TITANIA_ATTENTION_UNAPPROVED,
				'attention_object_type'	=> ext::TITANIA_POST,
				'attention_object_id'	=> $this->post_id,
				'attention_poster_id'	=> $this->post_user_id,
				'attention_post_time'	=> $this->post_time,
				'attention_url'			=> $this->get_url(),
				'attention_title'		=> $this->post_subject,
			));
			$attention->submit();
		}
		else
		{
			// Update posted topics table
			$this->topic->update_posted_status('add', $this->post_user_id);
		}

		// If no topic_id it means we are creating a new topic, so we need to set the first_post_ data.
		// Respect the post_time!  If for some reason we want to insert a post before the first one...
		if (!$this->topic->topic_first_post_id || ($this->post_approved && $this->topic->topic_first_post_time > $this->post_time))
		{
			if ($this->post_user_id == phpbb::$user->data['user_id'])
			{
				$post_username = phpbb::$user->data['username'];
				$post_user_colour = phpbb::$user->data['user_colour'];
			}
			else
			{
				$post_username = users_overlord::get_user($this->post_user_id, 'username', true);
				$post_user_colour = users_overlord::get_user($this->post_user_id, 'user_colour', true);
			}

			$this->topic->__set_array(array(
				'topic_first_post_id'			=> $this->post_id,
				'topic_first_post_user_id'		=> $this->post_user_id,
				'topic_first_post_username'		=> $post_username,
				'topic_first_post_user_colour'	=> $post_user_colour,
				'topic_first_post_time'			=> $this->post_time,

				'topic_time'					=> $this->post_time,
			));
		}

		// Respect the post_time!  If for some reason we want to insert a post before the last one...
		if (!$this->topic->topic_last_post_id || ($this->post_approved && $this->topic->topic_last_post_time < $this->post_time))
		{
			if ($this->post_user_id == phpbb::$user->data['user_id'])
			{
				$post_username = phpbb::$user->data['username'];
				$post_user_colour = phpbb::$user->data['user_colour'];
			}
			else
			{
				$post_username = users_overlord::get_user($this->post_user_id, 'username', true);
				$post_user_colour = users_overlord::get_user($this->post_user_id, 'user_colour', true);
			}

			$this->topic->__set_array(array(
				'topic_last_post_id'			=> $this->post_id,
				'topic_last_post_user_id'		=> $this->post_user_id,
				'topic_last_post_username'		=> $post_username,
				'topic_last_post_user_colour'	=> $post_user_colour,
				'topic_last_post_time'			=> $this->post_time,
				'topic_last_post_subject'		=> $this->post_subject,
			));
		}

		// Gotta update the topic again with the first/last post data
		$this->topic->submit();

		$this->index();

		// Increment the user's postcount if we must
		if ($this->post_approved && in_array($this->post_type, titania::$config->increment_postcount))
		{
			phpbb::update_user_postcount($this->post_user_id);
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
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

		// Make sure we have a topic here
		if (!$this->topic->topic_id)
		{
			$this->topic->topic_id = $this->topic_id;
			$this->topic->load();
		}

		if ($this->post_id == $this->topic->topic_first_post_id)
		{
			if ($this->post_user_id == phpbb::$user->data['user_id'])
			{
				$post_username = phpbb::$user->data['username'];
				$post_user_colour = phpbb::$user->data['user_colour'];
			}
			else
			{
				$post_username = users_overlord::get_user($this->post_user_id, 'username', true);
				$post_user_colour = users_overlord::get_user($this->post_user_id, 'user_colour', true);
			}

			$this->topic->__set_array(array(
				'topic_access'					=> $this->post_access,
				'topic_subject'					=> $this->post_subject,

				'topic_first_post_user_id'		=> $this->post_user_id,
				'topic_first_post_username'		=> $post_username,
				'topic_first_post_user_colour'	=> $post_user_colour,
				'topic_first_post_time'			=> $this->post_time,
			));
		}

		// Update the postcount for the topic and submit it
		$this->update_topic_postcount();
		$this->topic->submit();

		$this->topic_id = $this->topic->topic_id;
		$this->post_url = $this->topic->topic_url;

		$this->index();

		parent::submit();

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Soft delete a post
	*
	* @todo Soft delete topics...
	*/
	public function soft_delete($reason = '')
	{
		if ($this->post_deleted)
		{
			return;
		}

		$this->post_deleted = titania::$time;
		$this->post_delete_user = phpbb::$user->data['user_id'];
		$this->post_edit_reason = $reason;

		// Update the postcount for the topic
		$this->update_topic_postcount();

		// Set the visibility appropriately if no posts are visibile to the public/authors
		$flags = count::get_flags(access::PUBLIC_LEVEL);
		if (count::from_db($this->topic->topic_posts, $flags) <= 0)
		{
			// There are no posts visible to the public, change it to authors level access
			$this->topic->topic_access = access::AUTHOR_LEVEL;

			$flags = count::get_flags(access::AUTHOR_LEVEL);
			if (count::from_db($this->topic->topic_posts, $flags) <= 0)
			{
				// There are no posts visible to authors, change it to teams level access
				$this->topic->topic_access = access::TEAM_LEVEL;
			}
		}

		// Sync the first topic post if required
		if ($this->post_id == $this->topic->topic_first_post_id)
		{
			$this->topic->sync_first_post($this->post_id);
		}

		// Sync the last topic post if required
		if ($this->post_id == $this->topic->topic_last_post_id)
		{
			$this->topic->sync_last_post($this->post_id);
		}

		// Submit the topic to store the updated information
		$this->topic->submit();

		parent::submit();

		// Update topics posted table
		$this->topic->update_posted_status('remove', $this->post_user_id);

		// Decrement the user's postcount if we must
		if ($this->post_approved && in_array($this->post_type, titania::$config->increment_postcount))
		{
			phpbb::update_user_postcount($this->post_user_id, '-');
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Undelete a post
	*
	* @todo Unsoft delete topics...
	*/
	public function undelete()
	{
		if (!$this->post_deleted)
		{
			return;
		}

		$this->post_deleted = 0;
		$this->post_delete_user = 0;

		// Update the postcount for the topic
		$this->update_topic_postcount();

		// Set the visibility appropriately
		$flags = count::get_flags(access::AUTHOR_LEVEL);
		if (count::from_db($this->topic->topic_posts, $flags) > 0)
		{
			// There are posts visible to the authors, change it to authors level access
			$this->topic->topic_access = access::AUTHOR_LEVEL;

			$flags = count::get_flags(access::PUBLIC_LEVEL);
			if (count::from_db($this->topic->topic_posts, $flags) > 0)
			{
				// There are posts visible to the public, change it to public level access
				$this->topic->topic_access = access::PUBLIC_LEVEL;
			}
		}

		// Sync the first topic post if required
		if ($this->post_time < $this->topic->topic_first_post_time)
		{
			$this->topic->__set_array(array(
				'topic_first_post_id'			=> $this->post_id,
				'topic_first_post_user_id'		=> $this->post_user_id,
				'topic_first_post_username'		=> users_overlord::get_user($this->post_user_id, 'username'),
				'topic_first_post_user_colour'	=> users_overlord::get_user($this->post_user_id, 'user_colour'),
				'topic_first_post_time'			=> $this->post_time,
			));
		}

		// Sync the last topic post if required
		if ($this->post_time > $this->topic->topic_last_post_time)
		{
			$this->topic->__set_array(array(
				'topic_last_post_id'			=> $this->post_id,
				'topic_last_post_user_id'		=> $this->post_user_id,
				'topic_last_post_username'		=> users_overlord::get_user($this->post_user_id, 'username'),
				'topic_last_post_user_colour'	=> users_overlord::get_user($this->post_user_id, 'user_colour'),
				'topic_last_post_time'			=> $this->post_time,
				'topic_last_post_subject'		=> $this->post_subject,
			));
		}

		// Submit the topic to store the updated information
		$this->topic->submit();

		parent::submit();

		// Update topics posted table
		$this->topic->update_posted_status('add', $this->post_user_id);

		// Increment the user's postcount if we must
		if ($this->post_approved && in_array($this->post_type, titania::$config->increment_postcount))
		{
			phpbb::update_user_postcount($this->post_user_id);
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Hard delete a post
	*/
	public function hard_delete()
	{
		if (!$this->topic->topic_posts)
		{
			if (!$this->topic->load($this->topic_id))
			{
				return false;
			}
		}

		// Update the postcount for the topic
		$this->update_topic_postcount(true);

		// Set the visibility appropriately if no posts are visibile to the public/authors
		$flags = count::get_flags(access::PUBLIC_LEVEL);
		if (count::from_db($this->topic->topic_posts, $flags) <= 0)
		{
			// There are no posts visible to the public, change it to authors level access
			$this->topic->topic_access = access::AUTHOR_LEVEL;

			$flags = count::get_flags(access::AUTHOR_LEVEL);
			if (count::from_db($this->topic->topic_posts, $flags) <= 0)
			{
				// There are no posts visible to authors, change it to teams level access
				$this->topic->topic_access = access::TEAM_LEVEL;
			}
		}

		// Sync the first topic post if required
		if ($this->post_id == $this->topic->topic_first_post_id)
		{
			$this->topic->sync_first_post($this->post_id);
		}

		// Sync the last topic post if required
		if ($this->post_id == $this->topic->topic_last_post_id)
		{
			$this->topic->sync_last_post($this->post_id);
		}

		// Submit the topic to store the updated information
		$this->topic->submit();

		// Remove from the search index
		$this->search_manager->delete($this->post_type, $this->post_id);

		// @todo remove attachments and other things

		// Remove any attention items
		$sql = 'DELETE FROM ' . TITANIA_ATTENTION_TABLE . '
			WHERE attention_object_type = ' . ext::TITANIA_POST . '
				AND attention_object_id = ' . $this->post_id;
		phpbb::$db->sql_query($sql);

		// Decrement the user's postcount if we must
		if (!$this->post_deleted && $this->post_approved && in_array($this->post_type, titania::$config->increment_postcount))
		{
			phpbb::update_user_postcount($this->post_user_id, '-');
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);

		// Initiate self-destruct mode
		parent::delete();

		// Update topics posted table
		$this->topic->update_posted_status('remove', $this->post_user_id);

		// Check if the topic is empty
		$flags = count::get_flags(access::TEAM_LEVEL, true, true);
		if (count::from_db($this->topic->topic_posts, $flags) <= 0)
		{
			$this->topic->delete();
		}
	}

	public function report($reason = '', $notify_reporter = false)
	{
		// Mark the post as reported
		$this->post_reported = true;

		// Setup the attention object and submit it
		$attention = new titania_attention;
		$attention->__set_array(array(
			'attention_type'		=> ext::TITANIA_ATTENTION_REPORTED,
			'attention_object_type'	=> ext::TITANIA_POST,
			'attention_object_id'	=> $this->post_id,
			'attention_poster_id'	=> $this->post_user_id,
			'attention_post_time'	=> $this->post_time,
			'attention_url'			=> $this->get_url(),
			'attention_title'		=> $this->post_subject,
			'attention_description'	=> $reason,
			'notify_reporter'		=> $notify_reporter,
		));
		$attention->submit();

		// Update the postcount and mark as reported for the topic and submit it
		$this->update_topic_postcount();
		$this->topic->topic_reported = true;
		$this->topic->submit();

		// Self submission
		parent::submit();

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Reparse the post text without editing (or with editing, just not recieving the raw code from the user and doing an internal edit)
	* May not fully work correctly
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
	* Index this post
	*/
	public function index()
	{
		$this->search_manager->index($this->post_type, $this->post_id, array(
			'parent_id'		=> $this->topic->parent_id,
			'title'			=> $this->post_subject,
			'text'			=> $this->post_text,
			'text_uid'		=> $this->post_text_uid,
			'text_bitfield'	=> $this->post_text_bitfield,
			'text_options'	=> $this->post_text_options,
			'author'		=> $this->post_user_id,
			'date'			=> $this->post_time,
			'url'			=> serialize($this->get_url_params()),
			'access_level'	=> min($this->post_access, $this->topic->topic_access), // If the topic access level is lower than the post access level we still can not see it without access to the topic
			'approved'		=> $this->post_approved,
			'reported'		=> $this->post_reported,
			'parent_contrib_type'	=> $this->parent_contrib_type,
		));
	}

	/**
	* Update postcount on the parent topic
	*/
	public function update_topic_postcount($hard_delete = false)
	{
		// shouldn't need to load through load() to delete it...
		if ($hard_delete && empty($this->sql_data))
		{
			$this->sql_data = $this->__get_array();
		}

		if ($this->post_id && empty($this->sql_data))
		{
			throw new exception('Modifying a post requires you load it through the load() function (we require the original information).');
		}

		// Get the current count
		$to_db = count::from_db($this->topic->topic_posts, false);

		// Revert the old count from this post
		if ($this->post_id)
		{
			if ($this->sql_data['post_deleted'] != 0)
			{
				$to_db['deleted']--;
			}
			else if (!$this->sql_data['post_approved'])
			{
				$to_db['unapproved']--;
			}
			else
			{
				switch ($this->sql_data['post_access'])
				{
					case access::PUBLIC_LEVEL :
						$to_db['public']--;
					break;

					case access::AUTHOR_LEVEL :
						$to_db['authors']--;
					break;

					case access::TEAM_LEVEL :
						$to_db['teams']--;
					break;
				}
			}
		}

		// Then recount those options for this post if we are not hard deleting it.
		if (!$hard_delete)
		{
			if ($this->post_deleted != 0)
			{
				$to_db['deleted']++;
			}
			else if (!$this->post_approved)
			{
				$to_db['unapproved']++;
			}
			else
			{
				switch ($this->post_access)
				{
					case access::PUBLIC_LEVEL :
						$to_db['public']++;
					break;

					case access::AUTHOR_LEVEL :
						$to_db['authors']++;
					break;

					case access::TEAM_LEVEL :
						$to_db['teams']++;
					break;
				}
			}
		}

		// Update the field on the topic
		$this->topic->topic_posts = count::to_db($to_db);
	}

	/**
	* Assign details
	*
	* A little different from those in other classes, this one only returns the info ready for output
	*/
	public function assign_details($output_text = true)
	{
		$details = array(
			'POST_ID'						=> $this->post_id,
			'TOPIC_ID'						=> $this->topic_id,
			'POST_TYPE'						=> $this->post_type,
			'POST_ACCESS'					=> $this->post_access,
			'POST_LOCKED'					=> $this->post_locked,
			'POST_ATTACHMENT'				=> $this->post_attachment,
			'POST_USER_ID'					=> $this->post_user_id,
			'POST_IP'						=> (phpbb::$auth->acl_get('u_titania_mod_post_mod')) ? $this->post_ip : false,
			'POST_TIME'						=> phpbb::$user->format_date($this->post_time),
			'POST_EDIT_REASON'				=> censor_text($this->post_edit_reason),
			'POST_SUBJECT'					=> censor_text($this->post_subject),
			'POST_TEXT'						=> ($output_text) ? $this->generate_text_for_display() : '',
			'EDITED_MESSAGE'				=> ($this->post_edited) ? sprintf(phpbb::$user->lang['EDITED_MESSAGE'], users_overlord::get_user($this->post_edit_user, '_full'), phpbb::$user->format_date($this->post_edited)) : '',
			'DELETED_MESSAGE'				=> ($this->post_deleted != 0) ? sprintf(phpbb::$user->lang['DELETED_MESSAGE'], users_overlord::get_user($this->post_delete_user, '_full'), phpbb::$user->format_date($this->post_deleted), $this->get_url('undelete')) : '',

			'U_VIEW'						=> $this->get_url(),
			'U_EDIT'						=> $this->acl_get('edit') ? $this->get_url('edit') : '',
			'U_QUICKEDIT'					=> $this->acl_get('edit') ? $this->get_url('quick_edit') : '',
			'U_DELETE'						=> ($this->acl_get('delete') && (!$this->post_deleted || phpbb::$auth->acl_get('u_titania_post_hard_delete'))) ? $this->get_url('delete') : '',
			'U_REPORT'						=> (phpbb::$user->data['is_registered']) ? $this->get_url('report') : '',
			'U_WARN'						=> false, //$this->get_url('warn'),
			'U_INFO'						=> (phpbb::$auth->acl_gets('u_titania_mod_author_mod', 'u_titania_mod_contrib_mod', 'u_titania_mod_faq_mod', 'u_titania_mod_post_mod') || $this->types->find_authed('moderate')) ? $this->controller_helper->route('phpbb.titania.manage.attention.redirect', array('type' => ext::TITANIA_POST, 'id' => $this->post_id)) : '',
			'U_QUOTE'						=> $this->acl_get('post') ? $this->get_url('quote') : '',

			'S_UNREAD_POST'					=> ($this->unread) ? true : false, // remember that you must set this up extra...
			'S_POST_APPROVED'				=> (phpbb::$auth->acl_get('u_titania_mod_post_mod')) ? $this->post_approved : true,
			'S_POST_REPORTED'				=> (phpbb::$auth->acl_get('u_titania_mod_post_mod')) ? $this->post_reported : false,
			'S_POST_DELETED'				=> ($this->post_deleted != 0) ? true : false,
			'S_ACCESS_TEAMS'				=> $this->access->is_team($this->post_access),
			'S_ACCESS_AUTHORS'				=> $this->access->is_author($this->post_access),
		);

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $details, $this);

		return $details;
	}
}
