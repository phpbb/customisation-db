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
use phpbb\titania\ext;
use phpbb\titania\message\message;

/**
* Class to abstract titania queue
* @package Titania
*/
class titania_queue extends \phpbb\titania\entity\message_base
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_QUEUE_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'queue_id';

	/**
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = ext::TITANIA_QUEUE;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var  \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'queue_id'				=> array('default' => 0),
			'revision_id'			=> array('default' => 0),
			'contrib_id'			=> array('default' => 0),
			'submitter_user_id'		=> array('default' => (int) phpbb::$user->data['user_id']),
			'queue_topic_id'		=> array('default' => 0),
			'queue_allow_repack'	=> array('default' => 1),

			'queue_type'			=> array('default' => 0), // contrib type
			'queue_status'			=> array('default' => ext::TITANIA_QUEUE_HIDE), // Uses either TITANIA_QUEUE_NEW or one of the tags for the queue status from the DB
			'queue_submit_time'		=> array('default' => titania::$time),
			'queue_close_time'		=> array('default' => 0),
			'queue_close_user'		=> array('default' => 0),
			'queue_progress'		=> array('default' => 0), // User_id of whoever marked this as in progress
			'queue_progress_time'	=> array('default' => 0),

			'queue_notes'			=> array('default' => '',	'message_field' => 'message'),
			'queue_notes_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'queue_notes_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'queue_notes_options'	=> array('default' => 7,	'message_field' => 'message_options'),

			'validation_notes'			=> array('default' => '',	'message_field' => 'message_validation'),
			'validation_notes_bitfield'	=> array('default' => '',	'message_field' => 'message_validation_bitfield'),
			'validation_notes_uid'		=> array('default' => '',	'message_field' => 'message_validation_uid'),
			'validation_notes_options'	=> array('default' => 7,	'message_field' => 'message_validation_options'),

			'mpv_results'			=> array('default' => ''),
			'mpv_results_bitfield'	=> array('default' => ''),
			'mpv_results_uid'		=> array('default' => ''),
			'automod_results'		=> array('default' => ''),

			'allow_author_repack'	=> array('default' => false),
			'queue_tested'			=> array('default' => false),
		));

		$this->db = phpbb::$container->get('dbal.conn');
		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$this->user = phpbb::$container->get('user');
		$this->subscriptions = phpbb::$container->get('phpbb.titania.subscriptions');
	}

	public function submit($update_first_post = true)
	{
		if (!$this->queue_id)
		{
			$this->user->add_lang_ext('phpbb/titania', 'manage');

			$sql = 'SELECT c.contrib_id, c.contrib_name_clean, c.contrib_name, c.contrib_type, r.revision_version
				FROM ' . TITANIA_CONTRIBS_TABLE . ' c, ' . TITANIA_REVISIONS_TABLE . ' r
				WHERE r.revision_id = ' . (int) $this->revision_id . '
					AND c.contrib_id = r.contrib_id';
			$result = phpbb::$db->sql_query($sql);
			$row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			if (!$row)
			{
				trigger_error('NO_CONTRIB');
			}

			$this->queue_type = $row['contrib_type'];

			// Submit here first to make sure we have a queue_id for the topic url
			parent::submit();

			// Is there a queue discussion topic?  If not we should create one
			$this->get_queue_discussion_topic();

			$this->update_first_queue_post(phpbb::$user->lang['VALIDATION'] . ' - ' . $row['contrib_name'] . ' - ' . $row['revision_version']);
		}
		else if ($update_first_post)
		{
			$this->update_first_queue_post();
		}

		parent::submit();
	}

	/**
	* Rebuild (or create) the first post in the queue topic
	*/
	public function update_first_queue_post($post_subject = false)
	{
		$this->user->add_lang_ext('phpbb/titania', 'manage');

		if (!$this->queue_topic_id)
		{
			$sql = 'SELECT contrib_type FROM ' . TITANIA_CONTRIBS_TABLE . '
				WHERE contrib_id = ' . $this->contrib_id;
			phpbb::$db->sql_query($sql);
			$contrib_type = phpbb::$db->sql_fetchfield('contrib_type');
			phpbb::$db->sql_freeresult();

			// Create the topic
			$post = new titania_post(ext::TITANIA_QUEUE);
			$post->post_access = access::TEAM_LEVEL;
			$post->topic->parent_id = $this->queue_id;
			$post->topic->topic_category = $contrib_type;
			$post->topic->topic_url = serialize(array('id' => $this->queue_id));
		}
		else
		{
			// Load the first post
			$topic = new titania_topic;
			$topic->topic_id = $this->queue_topic_id;
			$topic->load();

			$post = new titania_post($topic->topic_type, $topic, $topic->topic_first_post_id);
			$post->load();
		}

		if ($post_subject)
		{
			$post->post_subject = $post_subject;
		}

		$post->post_user_id = $this->submitter_user_id;
		$post->post_time = $this->queue_submit_time;
		$revision = $this->get_revision();

		// Reset the post text
		$post->post_text = '';

		// Queue Discussion Link
		$queue_topic = $this->get_queue_discussion_topic();
		$post->post_text .= '[url=' . $queue_topic->get_url() . ']' . phpbb::$user->lang['QUEUE_DISCUSSION_TOPIC'] . "[/url]\n\n";

		if ($revision->revision_status == ext::TITANIA_REVISION_ON_HOLD)
		{
			$post->post_text .= '<strong>' . phpbb::$user->lang['REVISION_FOR_NEXT_PHPBB'] . "</strong>\n\n";
		}

		// Put text saying whether repacking is allowed or not
		$post->post_text .= phpbb::$user->lang[(($this->queue_allow_repack) ? 'QUEUE_REPACK_ALLOWED' : 'QUEUE_REPACK_NOT_ALLOWED')] . "\n\n";

		// Add the queue notes
		if ($this->queue_notes)
		{
			$queue_notes = $this->queue_notes;
			message::decode($queue_notes, $this->queue_notes_uid);
			$post->post_text .= '[quote=&quot;' . users_overlord::get_user($this->submitter_user_id, 'username', true) . '&quot;]' . $queue_notes . "[/quote]\n";
		}

		// Add the MPV results
		if ($this->mpv_results)
		{
			$mpv_results = $this->mpv_results;
			message::decode($mpv_results, $this->mpv_results_uid);
			$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_PV'] . '&quot;]' . $mpv_results . "[/quote]\n";
		}

		// Add the Automod results
		if ($this->automod_results)
		{
			$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_AUTOMOD'] . '&quot;]' . $this->automod_results . "[/quote]\n";
		}

		// Prevent errors from different configurations
		phpbb::$config['min_post_chars'] = 1;
		phpbb::$config['max_post_chars'] = 0;

		$this->forum_queue_update_first_queue_post($post);

		// Store the post
		$post->generate_text_for_storage(true, true, true);
		$post->submit();

		$this->queue_topic_id = $post->topic_id;
	}

	/**
	* Reply to the queue topic with a message
	*
	* @param string $message
	* @param bool $teams_only true to set to access level of teams
	* @return \titania_post Returns post object.
	*/
	public function topic_reply($message, $teams_only = true)
	{
		$this->user->add_lang_ext('phpbb/titania', 'manage');

		$message = (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message;

		$post = new titania_post(ext::TITANIA_QUEUE, $this->queue_topic_id);
		$post->__set_array(array(
			'post_subject'		=> 'Re: ' . $post->topic->topic_subject,
			'post_text'			=> $message,
		));

		if ($teams_only)
		{
			$post->post_access = access::TEAM_LEVEL;
		}

		$post->parent_contrib_type = $this->queue_type;

		$post->generate_text_for_storage(true, true, true);
		$post->submit();

		return $post;
	}

	/**
	* Reply to the discussion topic with a message
	*
	* @param string $message
	* @param bool $teams_only true to set to access level of teams
	* @param int $post_user_id
	*/
	public function discussion_reply($message, $teams_only = false, $post_user_id = 0)
	{
		$this->user->add_lang_ext('phpbb/titania', 'manage');

		$message = (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message;

		$topic = $this->get_queue_discussion_topic();

		$post = new titania_post(ext::TITANIA_QUEUE_DISCUSSION, $topic);
		$post->__set_array(array(
			'post_subject'		=> 'Re: ' . $post->topic->topic_subject,
			'post_text'			=> $message,
		));

		if ($teams_only)
		{
			$post->post_access = access::TEAM_LEVEL;
		}

		if ($post_user_id)
		{
			$post->post_user_id = (int) $post_user_id;
		}

		$post->parent_contrib_type = $this->queue_type;

		$post->generate_text_for_storage(true, true, true);
		$post->submit();
	}

	public function delete()
	{
		$this->trash_queue_topic();

		$post = new titania_post;

		// Remove posts and topic
		$sql = 'SELECT post_id FROM ' . TITANIA_POSTS_TABLE . '
			WHERE topic_id = ' . (int) $this->queue_topic_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$post->post_id = $row['post_id'];
			$post->hard_delete();
		}
		phpbb::$db->sql_freeresult($result);

		// Clear the revision queue id from the revisions table
		$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
			SET revision_queue_id = 0
			WHERE revision_id = ' . $this->revision_id;
		phpbb::$db->sql_query($sql);

		// Assplode
		parent::delete();
	}

	public function move($new_status, \phpbb\titania\tags $tags)
	{
		$this->user->add_lang_ext('phpbb/titania', 'manage');

		$from = $tags->get_tag_name($this->queue_status);
		$to = $tags->get_tag_name($new_status);

		$this->topic_reply(sprintf(phpbb::$user->lang['QUEUE_REPLY_MOVE'], $from, $to));

		$this->queue_status = (int) $new_status;
		$this->queue_progress = 0;
		$this->queue_progress_time = 0;
		$this->submit(false);

		// Send notifications
		$contrib = contribs_overlord::get_contrib_object($this->contrib_id, true);
		$topic = new titania_topic();
		$topic->load($this->queue_topic_id);
		$path_helper = phpbb::$container->get('path_helper');
		$u_view_queue = $topic->get_url(false, array('tag' => $new_status));

		$vars = array(
			'CONTRIB_NAME'	=> $contrib->contrib_name,
			'CATEGORY_NAME'	=> $to,
			'U_VIEW_QUEUE'	=> $path_helper->strip_url_params($u_view_queue, 'sid'),
		);
		$this->subscriptions->send_notifications(
			ext::TITANIA_QUEUE_TAG,
			$new_status,
			'new_contrib_queue_cat',
			$vars,
			phpbb::$user->data['user_id']
		);
	}

	public function in_progress()
	{
		$this->topic_reply('QUEUE_REPLY_IN_PROGRESS');

		$this->queue_progress = phpbb::$user->data['user_id'];
		$this->queue_progress_time = titania::$time;
		$this->submit(false);
	}

	public function no_progress()
	{
		$this->topic_reply('QUEUE_REPLY_NO_PROGRESS');

		$this->queue_progress = 0;
		$this->queue_progress_time = 0;
		$this->submit(false);
	}

	public function change_tested_mark($mark)
	{
		$this->queue_tested = (bool) $mark;
		$this->submit(false);
	}

	/**
	* Approve this revision
	*
	* @param mixed $public_notes
	*/
	public function approve($public_notes)
	{
		$this->user->add_lang_ext('phpbb/titania', array('manage', 'contributions'));
		$revision = $this->get_revision();
		$contrib = new titania_contribution;
		if (!$contrib->load($this->contrib_id) || !$contrib->is_visible())
		{
			return false;
		}
		$revision->contrib = $contrib;
		$revision->load_phpbb_versions();

		$notes = $this->validation_notes;
		message::decode($notes, $this->validation_notes_uid);
		$message = sprintf(phpbb::$user->lang['QUEUE_REPLY_APPROVED'], $revision->revision_version, $notes);

		// Replace empty quotes if there are no notes
		if (!$notes)
		{
			$message = str_replace('[quote][/quote]', '', $message);
		}

		$this->topic_reply($message, false);
		$this->discussion_reply($message);

		// Get branch information first
		$version_branches = array();
		foreach ($revision->phpbb_versions as $phpbb_version)
		{
			$branch = (int) $phpbb_version['phpbb_version_branch'];
			$version_branches[$branch] = $contrib->get_release_topic_id($branch);
		}

		// Update the revisions (this will create the release topics)
		$revision->change_status(ext::TITANIA_REVISION_APPROVED);
		$revision->submit();

		// Go through each version branch in this revision and create the replies as needed
		foreach ($version_branches as $branch => $contrib_release_topic_id)
		{
			// Reply to the release topic
			if ($contrib_release_topic_id && $contrib->type->update_public)
			{
				// Replying to an already existing topic, use the update message
				$post_public_notes = sprintf(phpbb::$user->lang[$contrib->type->update_public], $revision->revision_version) . (($public_notes) ? sprintf(phpbb::$user->lang[$contrib->type->update_public . '_NOTES'], $public_notes) : '');
				$contrib->reply_release_topic($branch, $post_public_notes);
			}
			elseif (!$contrib_release_topic_id && $contrib->type->reply_public)
			{
				// Replying to a topic that was just made, use the reply message
				$post_public_notes = phpbb::$user->lang[$contrib->type->reply_public] . (($public_notes) ? sprintf(phpbb::$user->lang[$contrib->type->reply_public . '_NOTES'], $public_notes) : '');
				$contrib->reply_release_topic($branch, $post_public_notes);
			}
		}

		// Self-updating
		$this->queue_status = ext::TITANIA_QUEUE_APPROVED;
		$this->queue_close_time = titania::$time;
		$this->queue_close_user = phpbb::$user->data['user_id'];
		$this->submit(false);

		// Send notification message
		$this->send_approve_deny_notification(true);

		// Subscriptions
		$email_vars = array(
			'NAME'		=> $contrib->contrib_name,
			'U_VIEW'	=> $contrib->get_url(),
		);
		$this->subscriptions->send_notifications(ext::TITANIA_CONTRIB, $this->contrib_id, 'subscribe_notify', $email_vars);

		$this->trash_queue_topic();
	}

	public function close($revision_status)
	{
		// Update the revision
		$revision = $this->get_revision();
		$revision->change_status($revision_status);

		// Self-updating
		$this->queue_status = ext::TITANIA_QUEUE_CLOSED;
		$this->queue_close_time = titania::$time;
		$this->queue_close_user = phpbb::$user->data['user_id'];
		$this->submit(false);

		$this->trash_queue_topic();
	}

	public function deny()
	{
		// Reply to the queue topic and discussion with the message
		$this->user->add_lang_ext('phpbb/titania', 'manage');
		$revision = $this->get_revision();

		$notes = $this->validation_notes;
		message::decode($notes, $this->validation_notes_uid);
		$message = sprintf(phpbb::$user->lang['QUEUE_REPLY_DENIED'], $revision->revision_version, $notes);

		// Replace empty quotes if there are no notes
		if (!$notes)
		{
			$message = str_replace('[quote][/quote]', '', $message);
		}

		$this->topic_reply($message, false);
		$this->discussion_reply($message);

		// Update the revision
		$revision->change_status(ext::TITANIA_REVISION_DENIED);

		// Self-updating
		$this->queue_status = ext::TITANIA_QUEUE_DENIED;
		$this->queue_close_time = titania::$time;
		$this->queue_close_user = phpbb::$user->data['user_id'];
		$this->submit(false);

		// Send notification message
		$this->send_approve_deny_notification(false);

		$this->trash_queue_topic();
	}

	/**
	* Send the approve/deny notification
	*/
	private function send_approve_deny_notification($approve = true)
	{
		$this->user->add_lang_ext('phpbb/titania', 'manage');
		phpbb::_include('functions_privmsgs', 'submit_pm');

		// Need some stuff
		$contrib = new titania_contribution();
		$contrib->load((int) $this->contrib_id);
		$revision = new titania_revision($contrib, $this->revision_id);
		$revision->load();

		// Generate the authors list to send it to
		$authors = array($contrib->contrib_user_id => 'to');
		$sql = 'SELECT user_id FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE contrib_id = ' . (int) $this->contrib_id . '
				AND active = 1';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$authors[$row['user_id']] = 'to';
		}
		phpbb::$db->sql_freeresult($result);

		// Subject
		$subject = sprintf(phpbb::$user->lang[$contrib->type->validation_subject], $contrib->contrib_name, $revision->revision_version);

		// Message
		$notes = $this->validation_notes;
		message::decode($notes, $this->validation_notes_uid);
		if ($approve)
		{
			$message = $contrib->type->validation_message_approve;
		}
		else
		{
			$message = $contrib->type->validation_message_deny;
		}
		$message = sprintf(phpbb::$user->lang[$message], $notes);

		// Replace empty quotes if there are no notes
		if (!$notes)
		{
			$message = str_replace('[quote][/quote]', phpbb::$user->lang['NO_NOTES'], $message);
		}

		// Parse the message
		$message_uid = $message_bitfield = $message_options = false;
		generate_text_for_storage($message, $message_uid, $message_bitfield, $message_options, true, true, true);

		$data = array(
			'address_list'		=> array('u' => $authors),
			'from_user_id'		=> phpbb::$user->data['user_id'],
			'from_username'		=> phpbb::$user->data['username'],
			'icon_id'			=> 0,
			'from_user_ip'		=> phpbb::$user->ip,
			'enable_bbcode'		=> true,
			'enable_smilies'	=> true,
			'enable_urls'		=> true,
			'enable_sig'		=> true,
			'message'			=> $message,
			'bbcode_bitfield'	=> $message_bitfield,
			'bbcode_uid'		=> $message_uid,
		);

		// Submit Plz
		submit_pm('post', $subject, $data, true);
	}

	/**
	* Get the revision object for this queue
	*/
	public function get_revision()
	{
		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . '
				AND revision_id = ' . $this->revision_id;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($row)
		{
			$revision = new titania_revision(contribs_overlord::get_contrib_object($this->contrib_id, true), $this->revision_id);
			$revision->__set_array($row);
			return $revision;
		}

		return false;
	}

	/**
	* Get the queue discussion topic or create one if needed
	*
	* @param bool $check_only Return false if topic does not exist instead of creating it
	*
	* @return titania_topic object
	*/
	public function get_queue_discussion_topic($check_only = false)
	{
		$sql = 'SELECT * FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE parent_id = ' . $this->contrib_id . '
				AND topic_type = ' . ext::TITANIA_QUEUE_DISCUSSION;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($row)
		{
			$topic = new titania_topic;
			$topic->__set_array($row);
			$this->queue_discussion_topic_id = $topic->topic_id;

			return $topic;
		}
		else if ($check_only)
		{
			return false;
		}

		// No queue discussion topic...so we must create one
		$this->user->add_lang_ext('phpbb/titania', 'posting');

		$contrib = contribs_overlord::get_contrib_object($this->contrib_id, true);

		$post = new titania_post(ext::TITANIA_QUEUE_DISCUSSION);
		$post->topic->__set_array(array(
			'parent_id'			=> $this->contrib_id,
			'topic_category'	=> $contrib->contrib_type,
			'topic_url'			=> serialize(array(
				'contrib_type'	=> $contrib->type->url,
				'contrib'		=> $contrib->contrib_name_clean,
			)),
			'topic_sticky'		=> true,
		));
		$post->__set_array(array(
			'post_access'		=> access::AUTHOR_LEVEL,
			'post_subject'		=> sprintf(phpbb::$user->lang['QUEUE_DISCUSSION_TOPIC_TITLE'], $contrib->contrib_name),
			'post_text'			=> phpbb::$user->lang['QUEUE_DISCUSSION_TOPIC_MESSAGE'],
		));
		$post->generate_text_for_storage(true, true, true);
		$post->submit();
		$this->queue_discussion_topic_id = $post->topic->topic_id;

		return $post->topic;
	}

	/**
	* Get queue item URL.
	*
	* @param bool|string $action	Optional action to link to.
	* @param array $params			Optional parameters to add to URL.
	*
	* @return string Returns generated URL.
	*/
	public function get_url($action = false, $params = array())
	{
		$controller = 'phpbb.titania.queue.item';
		$params += array(
			'id'	=> $this->queue_id,
		);

		if ($action)
		{
			$controller .= '.action';
			$params['action'] = $action;
		}

		return $this->controller_helper->route($controller, $params);
	}

	/**
	* Get URL to queue tool.
	*
	* @param string $tool		Tool.
	* @param int $revision_id	Revision id.
	* @param array $params		Additional parameters to append to the URL.
	*
	* @return string
	*/
	public function get_tool_url($tool, $revision_id, array $params = array())
	{
		$params += array(
			'tool'	=> $tool,
			'id'	=> $revision_id,
		);
		return $this->controller_helper->route('phpbb.titania.queue.tools', $params);
	}

	/**
	 * Copy new posts for queue discussion, queue to the forum
	 *
	 * @param titania_post $post_object
	 */
	protected function forum_queue_update_first_queue_post(&$post_object)
	{
		if ($this->queue_status == ext::TITANIA_QUEUE_HIDE || !$this->queue_topic_id)
		{
			return;
		}

		$path_helper = phpbb::$container->get('path_helper');

		// First we copy over the queue discussion topic if required
		$sql = 'SELECT topic_id, phpbb_topic_id, topic_category FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE parent_id = ' . $this->contrib_id . '
			AND topic_type = ' . ext::TITANIA_QUEUE_DISCUSSION;
		$result = phpbb::$db->sql_query($sql);
		$topic_row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		// Do we need to create the queue discussion topic or not?
		if ($topic_row['topic_id'] && !$topic_row['phpbb_topic_id'])
		{
			$forum_id = titania_post::get_queue_forum_id($post_object->topic->topic_category, ext::TITANIA_QUEUE_DISCUSSION);

			$temp_post = new titania_post;

			// Go through any posts in the queue discussion topic and copy them
			$topic_id = false;
			$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . ' WHERE topic_id = ' . $topic_row['topic_id'];
			$result = phpbb::$db->sql_query($sql);
			while($row = phpbb::$db->sql_fetchrow($result))
			{
				titania::_include('functions_posting', 'phpbb_posting');

				$temp_post->__set_array($row);

				$post_text = $row['post_text'];

				handle_queue_attachments($temp_post, $post_text);
				message::decode($post_text, $row['post_text_uid']);

				$post_text .= "\n\n" . $path_helper->strip_url_params($temp_post->get_url(), 'sid');

				$options = array(
					'poster_id'				=> $row['post_user_id'],
					'forum_id' 				=> $forum_id,
					'topic_title'			=> $row['post_subject'],
					'post_text'				=> $post_text,
				);

				if ($topic_id)
				{
					$options = array_merge($options, array(
						'topic_id'	=> $topic_id,
					));

					phpbb_posting('reply', $options);
				}
				else
				{
					switch ($topic_row['topic_category'])
					{
						case ext::TITANIA_TYPE_EXTENSION:
							$options['poster_id'] = titania::$config->forum_extension_robot;
							break;

						case ext::TITANIA_TYPE_MOD:
							$options['poster_id'] = titania::$config->forum_mod_robot;
							break;

						case ext::TITANIA_TYPE_STYLE:
							$options['poster_id'] = titania::$config->forum_style_robot;
							break;
					}

					$topic_id = phpbb_posting('post', $options);
				}
			}
			phpbb::$db->sql_freeresult($result);

			if ($topic_id)
			{
				$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
					SET phpbb_topic_id = ' . $topic_id . '
					WHERE topic_id = ' . $topic_row['topic_id'];
				phpbb::$db->sql_query($sql);
			}

			unset($temp_post);
		}

		// Does a queue topic already exist?  If so, don't repost.
		$sql = 'SELECT phpbb_topic_id FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE topic_id = ' . $this->queue_topic_id;
		phpbb::$db->sql_query($sql);
		$phpbb_topic_id = phpbb::$db->sql_fetchfield('phpbb_topic_id');
		phpbb::$db->sql_freeresult();
		if ($phpbb_topic_id)
		{
			return;
		}

		$forum_id = titania_post::get_queue_forum_id($post_object->topic->topic_category, $post_object->topic->topic_type);

		if (!$forum_id)
		{
			return;
		}

		$post_object->submit();

		titania::_include('functions_posting', 'phpbb_posting');

		// Need some stuff
		phpbb::$user->add_lang_ext('phpbb/titania', 'contributions');
		$contrib = new titania_contribution;
		$contrib->load((int) $this->contrib_id);
		$revision = $this->get_revision();
		$contrib->get_download($revision->revision_id);

		switch ($post_object->topic->topic_category)
		{
			case ext::TITANIA_TYPE_EXTENSION:
				$post_object->topic->topic_first_post_user_id = titania::$config->forum_extension_robot;
				$lang_var = 'EXTENSION_QUEUE_TOPIC';
				break;

			case ext::TITANIA_TYPE_MOD:
				$post_object->topic->topic_first_post_user_id = titania::$config->forum_mod_robot;
				$lang_var = 'MOD_QUEUE_TOPIC';
				break;

			case ext::TITANIA_TYPE_STYLE:
				$post_object->topic->topic_first_post_user_id = titania::$config->forum_style_robot;
				$lang_var = 'STYLE_QUEUE_TOPIC';
				break;

			default:
				return;
				break;
		}

		$description = $contrib->contrib_desc;
		message::decode($description, $contrib->contrib_desc_uid);
		$download = current($contrib->download);

		$post_text = sprintf(phpbb::$user->lang[$lang_var],
			$contrib->contrib_name,
			$path_helper->strip_url_params($contrib->author->get_url(), 'sid'),
			users_overlord::get_user($contrib->author->user_id, '_username'),
			$description,
			$revision->revision_version,
			$path_helper->strip_url_params($revision->get_url(), 'sid'),
			$download['real_filename'],
			get_formatted_filesize($download['filesize'])
		);

		$post_text .= "\n\n" . $post_object->post_text;

		handle_queue_attachments($post_object, $post_text);
		message::decode($post_text, $post_object->post_text_uid);

		$post_text .= "\n\n" . $path_helper->strip_url_params($post_object->get_url(), 'sid');

		$options = array(
			'poster_id'				=> $post_object->topic->topic_first_post_user_id,
			'forum_id' 				=> $forum_id,
			'topic_title'			=> $post_object->topic->topic_subject,
			'post_text'				=> $post_text,
		);

		$topic_id = phpbb_posting('post', $options);

		$post_object->topic->phpbb_topic_id = $topic_id;

		$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
			SET phpbb_topic_id = ' . (int) $topic_id . '
			WHERE topic_id = ' . $post_object->topic->topic_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	 * Move queue topics to the trash can
	 */
	protected function trash_queue_topic()
	{
		$sql = 'SELECT phpbb_topic_id, topic_category FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $this->queue_topic_id;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row['phpbb_topic_id'])
		{
			return;
		}

		phpbb::_include('functions_admin', 'move_topics');

		move_topics($row['phpbb_topic_id'], titania_post::get_queue_forum_id($row['topic_category'], 'trash'));
	}
}
