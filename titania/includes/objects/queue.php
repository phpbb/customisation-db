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
			$sql = 'SELECT c.contrib_name, c.contrib_type, r.revision_version
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

			titania::add_lang('manage');
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

		// Add the queue notes
		$queue_notes = $this->queue_notes;
		decode_message($queue_notes, $this->queue_notes_uid);
		$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_NOTES'] . '&quot;]' . $queue_notes . "[/quote]\n\n";

		// Add the MPV results
		if ($this->mpv_results)
		{
			$mpv_results = $this->mpv_results;
			decode_message($mpv_results, $this->mpv_results_uid);
			$post->post_text .= '[quote=&quot;' . phpbb::$user->lang['VALIDATION_MPV'] . '&quot;]' . $mpv_results . '[/quote]';
		}

		// @todo Add the Automod results

		// Prevent errors from different configurations
		phpbb::$config['min_post_chars'] = 1;
		phpbb::$config['max_post_chars'] = 0;

		// Store the post
		$post->generate_text_for_storage(true, true, true);
		$post->submit();

		$this->queue_topic_id = $post->topic_id;
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

		// Assplode
		parent::delete();
	}

	public function move($new_status)
	{
		$this->queue_status = (int) $new_status;
		$this->queue_progress = 0;
		$this->queue_progress_time = 0;
		$this->submit(false);
	}

	public function in_progress()
	{
		$this->queue_progress = phpbb::$user->data['user_id'];
		$this->queue_progress_time = titania::$time;
		$this->submit(false);
	}

	public function no_progress()
	{
		$this->queue_progress = 0;
		$this->queue_progress_time = 0;
		$this->submit(false);
	}

	public function approve()
	{
		// Send notification message
		$this->send_approve_deny_notification();

		// Update the revisions table
		$sql_ary = array(
			'revision_validated'	=> true,
			'validation_date'		=> titania::$time,
		);
		$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE revision_id = ' . (int) $this->revision_id;
		phpbb::$db->sql_query($sql);

		// Update the contribs table
		$sql_ary = array(
			'contrib_status'		=> TITANIA_CONTRIB_APPROVED,
			'contrib_last_update'	=> titania::$time,
		);
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE contrib_id = ' . (int) $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Self-updating
		$this->queue_status = TITANIA_QUEUE_APPROVED;
		$this->queue_close_time = titania::$time;
		$this->queue_close_user = phpbb::$user->data['user_id'];
		$this->submit(false);
	}

	public function deny()
	{
		// Send notification message
		$this->send_approve_deny_notification(false);

		// Self-updating
		$this->queue_status = TITANIA_QUEUE_DENIED;
		$this->queue_close_time = titania::$time;
		$this->queue_close_user = phpbb::$user->data['user_id'];
		$this->submit(false);
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
		$notes = $this->queue_notes;
		decode_message($notes, $this->queue_notes_uid);
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
}
