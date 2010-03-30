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

if (!class_exists('titania_message_object'))
{
	require TITANIA_ROOT . 'includes/core/object_message.' . PHP_EXT;
}

/**
* Class to abstract titania queue
* @package Titania
*/
class titania_queue extends titania_message_object
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
	protected $object_type = TITANIA_QUEUE;

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
			'queue_status'			=> array('default' => TITANIA_QUEUE_HIDE), // Uses either TITANIA_QUEUE_NEW or one of the tags for the queue status from the DB
			'queue_submit_time'		=> array('default' => titania::$time),
			'queue_close_time'		=> array('default' => 0),
			'queue_close_user'		=> array('default' => 0),
			'queue_progress'		=> array('default' => 0), // User_id of whoever marked this as in progress
			'queue_progress_time'	=> array('default' => 0),

			'queue_notes'			=> array('default' => '',	'message_field' => 'message'),
			'queue_notes_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'queue_notes_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'queue_notes_options'	=> array('default' => 7,	'message_field' => 'message_options'),

			'queue_validation_notes'			=> array('default' => '',	'message_field' => 'message_validation'),
			'queue_validation_notes_bitfield'	=> array('default' => '',	'message_field' => 'message_validation_bitfield'),
			'queue_validation_notes_uid'		=> array('default' => '',	'message_field' => 'message_validation_uid'),
			'queue_validation_notes_options'	=> array('default' => 7,	'message_field' => 'message_validation_options'),

			'mpv_results'			=> array('default' => ''),
			'mpv_results_bitfield'	=> array('default' => ''),
			'mpv_results_uid'		=> array('default' => ''),
			'automod_results'		=> array('default' => ''),
		));
	}

	public function submit($update_first_post = true)
	{
		if (!$this->queue_id)
		{
			titania::add_lang('manage');

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
			$sql = 'SELECT topic_id FROM ' . TITANIA_TOPICS_TABLE . '
				WHERE parent_id = ' . $this->contrib_id . '
					AND topic_type = ' . TITANIA_QUEUE_DISCUSSION;
			$result = phpbb::$db->sql_query($sql);
			if (!phpbb::$db->sql_fetchrow($result))
			{
				titania::add_lang('posting');

				$post = new titania_post(TITANIA_QUEUE_DISCUSSION);
				$post->topic->__set_array(array(
					'parent_id'			=> $row['contrib_id'],
					'topic_category'	=> $row['contrib_type'],
					'topic_url'			=> titania_types::$types[$row['contrib_type']]->url . '/' . $row['contrib_name_clean'] . '/support/',
					'topic_sticky'		=> true,
				));
				$post->__set_array(array(
					'post_access'		=> TITANIA_ACCESS_AUTHORS,
					'post_subject'		=> sprintf(phpbb::$user->lang['QUEUE_DISCUSSION_TOPIC_TITLE'], $row['contrib_name']),
					'post_text'			=> phpbb::$user->lang['QUEUE_DISCUSSION_TOPIC_MESSAGE'],
				));
				$post->generate_text_for_storage(true, true, true);
				$post->submit();
			}
			phpbb::$db->sql_freeresult($result);

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
		titania::add_lang('manage');

		if (!$this->queue_topic_id)
		{
			// Create the topic
			$post = new titania_post(TITANIA_QUEUE);
			$post->topic->parent_id = $this->queue_id;
			$post->topic->topic_url = 'manage/queue/q_' . $this->queue_id;
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

		// Need at least some text in the post body...
		$post->post_text = phpbb::$user->lang['VALIDATION_SUBMISSION'] . "\n\n";

		// Put the queue discussion link in the post
		$sql = 'SELECT topic_id, topic_url, topic_subject_clean FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE parent_id = ' . $this->contrib_id . '
				AND topic_type = ' . TITANIA_QUEUE_DISCUSSION;
		$result = phpbb::$db->sql_query($sql);
		$queue_topic_row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($queue_topic_row)
		{
			$queue_topic = new titania_topic;
			$queue_topic->__set_array($queue_topic_row);

			$post->post_text .= '<a href="' . $queue_topic->get_url() . '">' . phpbb::$user->lang['QUEUE_DISCUSSION_TOPIC'] . "</a>\n\n";
		}

		// Add the queue notes
		$queue_notes = $this->queue_notes;
		decode_message($queue_notes, $this->queue_notes_uid);
		if ($queue_notes)
		{
			$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_NOTES'] . '&quot;]' . $queue_notes . "[/quote]\n\n";
		}

		// Add the MPV results
		if ($this->mpv_results)
		{
			$mpv_results = $this->mpv_results;
			decode_message($mpv_results, $this->mpv_results_uid);
			$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_MPV'] . '&quot;]' . $mpv_results . '[/quote]';
		}

		// @todo Add the Automod results
		if ($this->automod_results)
		{
			$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_AUTOMOD'] . '&quot;]' . $this->automod_results . '[/quote]';
		}

		// Prevent errors from different configurations
		phpbb::$config['min_post_chars'] = 1;
		phpbb::$config['max_post_chars'] = 0;

		// Store the post
		$post->generate_text_for_storage(true, true, true);
		$post->submit();

		$this->queue_topic_id = $post->topic_id;
	}

	/**
	* Reply to the queue topic with a message
	*
	* @param string $message
	*/
	public function topic_reply($message, $teams_only = true)
	{
		titania::add_lang('manage');

		$message = (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message;

		$post = new titania_post(TITANIA_QUEUE, $this->queue_topic_id);
		$post->__set_array(array(
			'post_subject'		=> 'Re: ' . $post->topic->topic_subject,
			'post_text'			=> $message,
		));

		if ($teams_only)
		{
			$post->post_access = TITANIA_ACCESS_TEAMS;
		}

		$post->generate_text_for_storage(true, true, true);
		$post->submit();
	}

	/**
	* Reply to the discussion topic with a message
	*
	* @param string $message
	*/
	public function discussion_reply($message, $teams_only = false)
	{
		titania::add_lang('manage');

		$message = (isset(phpbb::$user->lang[$message])) ? phpbb::$user->lang[$message] : $message;

		$sql = 'SELECT topic_id FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE parent_id = ' . $this->contrib_id . '
				AND topic_type = ' . TITANIA_QUEUE_DISCUSSION;
		phpbb::$db->sql_query($sql);
		$topic_id = phpbb::$db->sql_fetchfield('topic_id');
		phpbb::$db->sql_freeresult();

		if (!$topic_id)
		{
			return;
		}

		$post = new titania_post(TITANIA_QUEUE_DISCUSSION, $topic_id);
		$post->__set_array(array(
			'post_subject'		=> 'Re: ' . $post->topic->topic_subject,
			'post_text'			=> $message,
		));

		if ($teams_only)
		{
			$post->post_access = TITANIA_ACCESS_TEAMS;
		}

		$post->generate_text_for_storage(true, true, true);
		$post->submit();
	}

	public function delete()
	{
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

	public function move($new_status)
	{
		titania::add_lang('manage');

		$from = titania_tags::get_tag_name($this->queue_status);
		$to = titania_tags::get_tag_name($new_status);

		$this->topic_reply(sprintf(phpbb::$user->lang['QUEUE_REPLY_MOVE'], $from, $to));

		$this->queue_status = (int) $new_status;
		$this->queue_progress = 0;
		$this->queue_progress_time = 0;
		$this->submit(false);
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

	public function approve()
	{
		// Reply to the queue topic and discussion with the message
		titania::add_lang(array('manage', 'contributions'));
		$revision = $this->get_revision();
		$notes = $this->queue_validation_notes;
		decode_message($notes, $this->queue_validation_notes_uid);
		$message = sprintf(phpbb::$user->lang['QUEUE_REPLY_APPROVED'], $revision->revision_version, $notes);
		$this->topic_reply($message, false);
		$this->discussion_reply($message);

		// Update the revisions table
		$sql_ary = array(
			'revision_validated'	=> true,
			'validation_date'		=> titania::$time,
		);
		$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE revision_id = ' . (int) $this->revision_id;
		phpbb::$db->sql_query($sql);

		// Update the revisions phpbb version table
		$sql = 'UPDATE ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			SET revision_validated = 1
			WHERE revision_id = ' . (int) $this->revision_id;
		phpbb::$db->sql_query($sql);

		// Update the contribs table
		$contrib = new titania_contribution;
		$contrib->load((int) $this->contrib_id);
		$contrib->change_status(TITANIA_CONTRIB_APPROVED);

		// Start process to post on forum topic/post release
		$contrib->get_download($this->revision_id);

		if ($contrib->contrib_release_topic_id)
		{
			$body = sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->update_public],
				$revision->revision_version
			);

			$options = array(
				'poster_id'				=> titania_types::$types[$contrib->contrib_type]->forum_robot,
				'forum_id' 				=> titania_types::$types[$contrib->contrib_type]->forum_database,
				'topic_id'				=> $contrib->contrib_release_topic_id,
				'topic_title'			=> 'Re: ' . $contrib->contrib_name,
				'post_text'				=> $body
			);

			if ($options['forum_id'] && $options['poster_id'])
			{
				phpbb_post_add($options);
			}
		}
		else
		{
			$body = sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->create_public],
				$contrib->contrib_name,
				$contrib->author->get_url(),
				$contrib->author->username,
				$contrib->contrib_desc,
				$revision->revision_version,
				titania_url::build_url('download', array('id' => $revision->attachment_id)),
				$contrib->download['real_filename'],
				$contrib->download['filesize'],
				$contrib->get_url()
			);

			$options = array(
				'poster_id'				=> titania_types::$types[$contrib->contrib_type]->forum_robot,
				'forum_id' 				=> titania_types::$types[$contrib->contrib_type]->forum_database,
				'topic_title'			=> $contrib->contrib_name,
				'post_text'				=> $body
			);

			if ($options['forum_id'] && $options['poster_id'])
			{
				$topic_id = phpbb_topic_add($options);
			}
		}

		$sql_ary = array(
			'contrib_last_update' 		=> titania::$time,
			'contrib_release_topic_id' 	=> ($contrib->contrib_release_topic_id) ? $contrib->contrib_release_topic_id : $topic_id,
		);

		// Update contrib last update time and release topic ic
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Self-updating
		$this->queue_status = TITANIA_QUEUE_APPROVED;
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
		titania_subscriptions::send_notifications(TITANIA_CONTRIB, $this->contrib_id, 'subscribe_notify.txt', $email_vars);
	}

	public function deny()
	{
		// Reply to the queue topic and discussion with the message
		titania::add_lang('manage');
		$revision = $this->get_revision();
		$notes = $this->queue_validation_notes;
		decode_message($notes, $this->queue_validation_notes_uid);
		$message = sprintf(phpbb::$user->lang['QUEUE_REPLY_DENIED'], $revision->revision_version, $notes);
		$this->topic_reply($message, false);
		$this->discussion_reply($message);

		// Self-updating
		$this->queue_status = TITANIA_QUEUE_DENIED;
		$this->queue_close_time = titania::$time;
		$this->queue_close_user = phpbb::$user->data['user_id'];
		$this->submit(false);

		// Send notification message
		$this->send_approve_deny_notification(false);
	}

	/**
	* Send the approve/deny notification
	*/
	private function send_approve_deny_notification($approve = true)
	{
		titania::add_lang('manage');
		phpbb::_include('functions_privmsgs', 'submit_pm');

		// Generate the authors list to send it to
		$authors = array($this->submitter_user_id => 'to');
		$sql = 'SELECT user_id FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE contrib_id = ' . (int) $this->contrib_id . '
				AND active = 1';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$authors[$row['user_id']] = 'to';
		}
		phpbb::$db->sql_freeresult($result);

		// Need some stuff
		$contrib = new titania_contribution();
		$contrib->load((int) $this->contrib_id);
		$revision = new titania_revision($contrib, $this->revision_id);
		$revision->load();

		// Subject
		$subject = sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->validation_subject], $contrib->contrib_name, $revision->revision_version);

		// Message
		$notes = $this->queue_validation_notes;
		decode_message($notes, $this->queue_validation_notes_uid);
		if ($approve)
		{
			$message = titania_types::$types[$contrib->contrib_type]->validation_message_approve;
		}
		else
		{
			$message = titania_types::$types[$contrib->contrib_type]->validation_message_deny;
		}
		$message = sprintf(phpbb::$user->lang[$message], $notes);

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
		submit_pm('post', $subject, $data, false);
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
}
