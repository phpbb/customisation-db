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
			'contrib_name_clean'	=> array('default' => ''),
			'submitter_user_id'		=> array('default' => (int) phpbb::$user->data['user_id']),
			'queue_topic_id'		=> array('default' => 0),

			'queue_type'			=> array('default' => 0),
			'queue_status'			=> array('default' => TITANIA_QUEUE_HIDE), // Uses either TITANIA_QUEUE_NEW or one of the tags for the queue status from the DB
			'queue_submit_time'		=> array('default' => titania::$time),
			'queue_close_time'		=> array('default' => 0),

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

	public function submit()
	{
		if (!$this->queue_id)
		{
			$sql = 'SELECT c.*, r.revision_version
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

			$contrib = new titania_contribution;
			$contrib->__set_array($row);

			$this->contrib_name_clean = $row['contrib_name_clean'];
			$this->queue_type = $row['contrib_type'];

			titania::add_lang('manage');
			$this->update_first_queue_post(phpbb::$user->lang['VALIDATION'] . ' - ' . $row['contrib_name'] . ' - ' . $row['revision_version'], $contrib);
		}
		else
		{
			$this->update_first_queue_post();
		}

		parent::submit();
	}

	/**
	* Rebuild (or create) the first post in the queue topic
	*/
	public function update_first_queue_post($post_subject = false, $contrib = false)
	{
		titania::add_lang('manage');

		if (!$this->queue_topic_id)
		{
			// Create the topic
			$post = new titania_post(TITANIA_QUEUE);
			$post->topic->contrib = $contrib;
			$post->topic->contrib_id = $contrib->contrib_id;
		}
		else
		{
			$topic = new titania_topic;
			$topic->topic_id = $this->queue_topic_id;
			$topic->load();

			if ($contrib === false)
			{
				$topic->contrib = new titania_contribution;
				$topic->contrib->load($topic->contrib_id);
			}
			else
			{
				$topic->contrib = $contrib;
				$topic->contrib_id = $contrib->contrib_id;
			}

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
		$post->post_text .= $queue_notes . "\n\n";

		// Add the MPV results
		$mpv_results = $this->mpv_results;
		decode_message($mpv_results, $this->mpv_results_uid);
		$post->post_text .= $mpv_results;

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
}
