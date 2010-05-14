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
* Class to titania revision.
* @package Titania
*
* @todo Create revision_status field to store whether this revision is new, validated, or pulled (for security or other reasons)
*/
class titania_revision extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_REVISIONS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'revision_id';

	/**
	* Contribution object
	*
	* @var object
	*/
	public $contrib = false;

	/**
	* phpBB versions
	*
	* @var mixed
	*/
	public $phpbb_versions = array();

	public function __construct($contrib, $revision_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'revision_id'			=> array('default' => 0),
			'contrib_id' 			=> array('default' => 0),
			'revision_status'		=> array('default' => TITANIA_REVISION_NEW),
			'attachment_id' 		=> array('default' => 0),
			'revision_name' 		=> array('default' => '', 'max' => 255),
			'revision_time'			=> array('default' => (int) titania::$time),
			'validation_date'		=> array('default' => 0),
			'revision_version'		=> array('default' => ''),
			'install_time'			=> array('default' => 0),
			'install_level'			=> array('default' => 0),
			'revision_submitted'	=> array('default' => false), // False if it is still in the process of being submitted/verified; True if submission has finished
			'revision_queue_id'		=> array('default' => 0),
		));

		if ($contrib)
		{
			$this->contrib = $contrib;
			$this->contrib_id = $this->contrib->contrib_id;
		}

		$this->revision_id = $revision_id;

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Load the phpBB branches we've selected for this revision
	* Stored in $this->phpbb_versions
	*/
	public function load_phpbb_versions()
	{
		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			WHERE revision_id = ' . (int) $this->revision_id;
		 $result = phpbb::$db->sql_query($sql);
		 while ($row = phpbb::$db->sql_fetchrow($result))
		 {
		 	$this->phpbb_versions[] = $row;
		 }
		 phpbb::$db->sql_freeresult($result);
	}

	/**
	* Get the branches we've selected for this revision (load them first!)
	*/
	public function get_selected_branches()
	{
		$branches = array();
		foreach ($this->phpbb_versions as $row)
		{
			$branches[] = $row['phpbb_version_branch'];
		}

		return array_unique($branches);
	}

	public function display($tpl_block = 'revisions', $show_queue = false)
	{
		titania::_include('functions_display', 'order_phpbb_version_list_from_db');

		$ordered_phpbb_versions = order_phpbb_version_list_from_db($this->phpbb_versions);

		// Get rid of the day of the week if it exists in the dateformat
		$old_date_format = phpbb::$user->date_format;
		phpbb::$user->date_format = str_replace('D ', '', phpbb::$user->date_format);

		phpbb::$template->assign_block_vars($tpl_block, array(
			'REVISION_ID'		=> $this->revision_id,
			'CREATED'			=> phpbb::$user->format_date($this->revision_time),
			'NAME'				=> ($this->revision_name) ? censor_text($this->revision_name) : (($this->contrib) ? $this->contrib->contrib_name . ' ' . $this->revision_version : ''),
			'VERSION'			=> $this->revision_version,
			'VALIDATED_DATE'	=> ($this->validation_date) ? phpbb::$user->format_date($this->validation_date) : phpbb::$user->lang['NOT_VALIDATED'],
			'REVISION_QUEUE'	=> ($show_queue && $this->revision_queue_id) ? titania_url::build_url('manage/queue', array('q' => $this->revision_queue_id)) : '',
			'PHPBB_VERSION'		=> (sizeof($ordered_phpbb_versions) == 1) ? $ordered_phpbb_versions[0] : '',

			'U_DOWNLOAD'		=> $this->get_url(),
			'U_EDIT'			=> ($this->contrib && (phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[$this->contrib->contrib_type]->acl_get('moderate'))) ? $this->contrib->get_url('revision_edit', array('revision' => $this->revision_id)) : '',

			'S_NEW'					=> ($this->revision_status == TITANIA_REVISION_NEW) ? true : false,
			'S_APPROVED'			=> ($this->revision_status == TITANIA_REVISION_APPROVED) ? true : false,
			'S_DENIED'				=> ($this->revision_status == TITANIA_REVISION_DENIED) ? true : false,
			'S_PULLED_SECURITY'		=> ($this->revision_status == TITANIA_REVISION_PULLED_SECURITY) ? true : false,
			'S_PULLED_OTHER'		=> ($this->revision_status == TITANIA_REVISION_PULLED_OTHER) ? true : false,
		));

		phpbb::$user->date_format = $old_date_format;

		foreach ($ordered_phpbb_versions as $version)
		{
			phpbb::$template->assign_block_vars($tpl_block . '.phpbb_versions', array(
				'VERSION'		=> $version,
			));
		}

		phpbb::$template->assign_var('ICON_EDIT', '<img src="' . titania::$images_path . 'icon_edit.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />');

		// Hooks
		titania::$hook->call_hook(array(__CLASS__, __FUNCTION__), $this, $tpl_block);
	}

	/**
	 * Handle some stuff we need when submitting a revision
	 */
	public function submit()
	{
		if (!$this->revision_id)
		{
			// Update the contrib_last_update if required here
			if (!titania::$config->require_validation)
			{
				// Start process to post on forum topic/post release
				$this->contrib->get_download();

				if (titania_types::$types[$this->contrib->contrib_type]->forum_robot && titania_types::$types[$this->contrib->contrib_type]->forum_database)
				{
					// Global body and options
					$body = sprintf(phpbb::$user->lang[titania_types::$types[$this->contrib->contrib_type]->create_public],
						$this->contrib->contrib_name,
						$this->contrib->author->get_url(),
						users_overlord::get_user($this->contrib->author->user_id, '_username'),
						$this->contrib->contrib_desc,
						$revision->revision_version,
						titania_url::build_url('download', array('id' => $revision->attachment_id)),
						$this->contrib->download['real_filename'],
						$this->contrib->download['filesize'],
						$this->contrib->get_url(),
						$this->contrib->get_url('support')
					);

					$options = array(
						'poster_id'				=> titania_types::$types[$this->contrib->contrib_type]->forum_robot,
						'forum_id' 				=> titania_types::$types[$this->contrib->contrib_type]->forum_database,
						'topic_id'				=> $this->contrib->contrib_release_topic_id,
					);

					if ($this->contrib->contrib_release_topic_id)
					{
						// We edit the first post of contrib release topic
						$options_edit = array(
							'topic_title'			=> $this->contrib->contrib_name,
							'post_text'				=> $body,
						);
						$options_edit = array_merge($options_edit, $options);
						phpbb_posting('edit', $options_edit);

						// We reply to the contrib release topic
						$body_reply = sprintf(phpbb::$user->lang[titania_types::$types[$this->contrib->contrib_type]->update_public],
							$revision->revision_version,
							''
						);
						$options_reply = array(
							'topic_title'			=> 'Re: ' . $this->contrib->contrib_name,
							'post_text'				=> $body_reply
						);
						$options_reply = array_merge($options_reply, $options);
						phpbb_posting('reply', $options_reply, true);
					}
					else
					{
						// We create a new topic in database
						$options_post = array(
							'topic_title'			=> $this->contrib->contrib_name,
							'post_text'				=> $body,
							'topic_status'			=> (titania::$config->support_in_titania) ? ITEM_LOCKED : ITEM_UNLOCKED,
						);
						$options_post = array_merge($options_post, $options);
						$topic_id = phpbb_posting('post', $options_post);
					}
				}

				$sql_ary = array(
					'contrib_last_update' 		=> titania::$time,
					'contrib_release_topic_id' 	=> ($this->contrib->contrib_release_topic_id) ? $this->contrib->contrib_release_topic_id : $topic_id,
				);

				$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
					SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE contrib_id = ' . $this->contrib_id;
				phpbb::$db->sql_query($sql);

				// Subscriptions
				$email_vars = array(
					'NAME'		=> $this->contrib->contrib_name,
					'U_VIEW'	=> $this->contrib->get_url(),
				);
				titania_subscriptions::send_notifications(TITANIA_CONTRIB, $this->contrib_id, 'subscribe_notify.txt', $email_vars);
			}
		}
		else if (sizeof($this->phpbb_versions))
		{
			$sql = 'DELETE FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
				WHERE revision_id = ' . (int) $this->revision_id;
			phpbb::$db->sql_query($sql);
		}

		parent::submit();

		// Add phpBB versions supported
		if (sizeof($this->phpbb_versions))
		{
			$versions = titania::$cache->get_phpbb_versions();

			$sql_ary = array();
			foreach ($this->phpbb_versions as $row)
			{
				if (!is_array($row)) // Accept from user input
				{
					$row = array('phpbb_version_branch' => (int) $row);
				}

				if (!isset($row['phpbb_version_branch']) || !isset(titania::$config->phpbb_versions[$row['phpbb_version_branch']]))
				{
					continue;
				}

				// OMG, it's not in our cache!
				if (!isset($versions[$row['phpbb_version_branch'] . titania::$config->phpbb_versions[$row['phpbb_version_branch']]]))
				{
					titania::$cache->destroy('_titania_phpbb_versions');
				}

				$sql_ary[] = array(
					'revision_id'				=> $this->revision_id,
					'contrib_id'				=> $this->contrib_id,
					'revision_validated'		=> ($this->revision_status == TITANIA_REVISION_APPROVED) ? true : false,
					'phpbb_version_branch'		=> $row['phpbb_version_branch'],
					'phpbb_version_revision'	=> get_real_revision_version(((isset($row['phpbb_version_revision'])) ? $row['phpbb_version_revision'] : titania::$config->phpbb_versions[$row['phpbb_version_branch']]['latest_revision'])),
				);
			}

			if (sizeof($sql_ary))
			{
				phpbb::$db->sql_multi_insert(TITANIA_REVISIONS_PHPBB_TABLE, $sql_ary);
			}
		}

		// Create the queue entry if required, else update it
		if (titania::$config->use_queue)
		{
			$queue = $this->get_queue();
			if ($queue === false)
			{
				$queue = new titania_queue;
			}

			$queue->__set_array(array(
				'revision_id'			=> $this->revision_id,
				'contrib_id'			=> $this->contrib_id,
				'contrib_name_clean'	=> $this->contrib->contrib_name_clean,
			));

			// Set the queue status to new if it's submitted and the queue status is set to hide it
			if ($this->revision_submitted && $queue->queue_status == TITANIA_QUEUE_HIDE)
			{
				$queue->queue_status = TITANIA_QUEUE_NEW;
			}

			$queue->submit();

			// Set the revision queue id
			$this->revision_queue_id = $queue->queue_id;
			parent::submit();

			if ($this->revision_submitted)
			{
				// Delete any old revisions that were in the queue and marked as New
				$sql = 'SELECT * FROM ' . TITANIA_QUEUE_TABLE . '
					WHERE contrib_id = ' . (int) $this->contrib_id . '
						AND revision_id <> ' . $this->revision_id . '
						AND queue_status = ' . TITANIA_QUEUE_NEW;
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$queue = new titania_queue;
					$queue->__set_array($row);
					$queue->delete();
					unset($queue);
				}
				phpbb::$db->sql_freeresult($result);
			}
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	 * Change the status of this revision
	 *
	 * @param int $new_status
	 */
	public function change_status($new_status)
	{
		$new_status = (int) $new_status;
		$old_status = $this->revision_status;

		if ($old_status == $new_status)
		{
			return;
		}

		$this->revision_status = $new_status;

		$sql_ary = array(
			'revision_status'	=> $this->revision_status,
		);

		switch ($old_status)
		{
			case TITANIA_REVISION_APPROVED :
				// If there are no approved revisions left we will need to reset the contribution status
				$sql = 'SELECT COUNT(revision_id) AS cnt FROM ' . TITANIA_REVISIONS_TABLE . '
					WHERE revision_status = ' . TITANIA_REVISION_APPROVED . '
						AND contrib_id = ' . $this->contrib_id . '
						AND revision_id <> ' . $this->revision_id;
				phpbb::$db->sql_query($sql);
				$cnt = phpbb::$db->sql_fetchfield('cnt');

				if (!$cnt)
				{
					if (!$this->contrib)
					{
						$this->contrib = contribs_overlord::get_contrib_object($this->contrib_id, true);
					}

					if (in_array($this->contrib->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)))
					{
						$this->contrib->change_status(TITANIA_CONTRIB_NEW);
					}
				}
			break;
		}

		switch ($new_status)
		{
			case TITANIA_REVISION_APPROVED :
				// If approving this revision and the contribution is set to new, approve the contribution
				if (!$this->contrib)
				{
					$this->contrib = contribs_overlord::get_contrib_object($this->contrib_id, true);
				}

				if (in_array($this->contrib->contrib_status, array(TITANIA_CONTRIB_NEW)))
				{
					$this->contrib->change_status(TITANIA_CONTRIB_APPROVED);
				}

				// Update the revisions phpbb version table
				$sql = 'UPDATE ' . TITANIA_REVISIONS_PHPBB_TABLE . '
					SET revision_validated = 1
					WHERE revision_id = ' . (int) $this->revision_id;
				phpbb::$db->sql_query($sql);

				$sql_ary['validation_date'] = titania::$time;
			break;

			default :
				// Update the revisions phpbb version table
				$sql = 'UPDATE ' . TITANIA_REVISIONS_PHPBB_TABLE . '
					SET revision_validated = 0
					WHERE revision_id = ' . (int) $this->revision_id;
				phpbb::$db->sql_query($sql);

				$sql_ary['validation_date'] = 0;
			break;
		}

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE revision_id = ' . $this->revision_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	* Repack a revision
	*
	* @param object $old_revision
	*/
	public function repack($old_revision)
	{
		if (!$this->revision_id)
		{
			throw new exception('Submit the revision before repacking');
		}

		$old_queue = $old_revision->get_queue();
		$queue = $this->get_queue();

		// Update the old queue and delete the new one
		$old_queue->revision_id = $queue->revision_id;
		$old_queue->mpv_results = $queue->mpv_results;
		$old_queue->mpv_results_bitfield = $queue->mpv_results_bitfield;
		$old_queue->mpv_results_uid = $queue->mpv_results_uid;
		$old_queue->automod_results = $queue->automod_results;
		$old_queue->submit();

		// Delete the new queue we made for this revision
		$queue->delete();

		// Unlink the old queue_id from the old revision manually (don't resubmit and make another queue topic...)
		$sql = 'UPDATE ' . $this->sql_table . ' SET revision_queue_id = 0
			WHERE revision_id = ' . (int) $old_revision->revision_id;
		phpbb::$db->sql_query($sql);
		$old_revision->revision_queue_id = 0;

		// Update the queue_id here
		$this->revision_queue_id = $old_queue->queue_id;
		$this->submit();

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	public function delete()
	{
		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);

		// Delete the queue item
		$queue = $this->get_queue();
		$queue->delete();

		// Delete the attachment
		$attachment = new titania_attachment(TITANIA_CONTRIB);
		$attachment->attachment_id = $this->attachment_id;
		$attachment->load();
		$attachment->delete();

		// Self-destruct
		parent::delete();
	}

	/**
	* Get the queue object for this revision
	*/
	public function get_queue()
	{
		$sql = 'SELECT * FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . '
				AND revision_id = ' . $this->revision_id;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($row)
		{
			$queue = new titania_queue;
			$queue->__set_array($row);
			return $queue;
		}

		return false;
	}

	/**
	 * Download URL
	 */
	public function get_url()
	{
		return titania_url::build_url('download', array('id' => $this->attachment_id));
	}
}
