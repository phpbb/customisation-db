<?php
/**
*
* @package Titania
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
	* phpBB versions, translations
	*
	* @var mixed
	*/
	public $phpbb_versions = array();
	public $translations = array();

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
			'revision_license'		=> array('default' => ''),
			'revision_clr_options'  => array('default' => ''),
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
	* Load the Translations for this revision
	* Stored in $this->translations
	*/
	public function load_translations()
	{
		$sql = 'SELECT * FROM ' . TITANIA_ATTACHMENTS_TABLE . '
			WHERE object_type = ' . TITANIA_TRANSLATION . '
				AND object_id = ' . $this->revision_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->translations[] = $row;
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

		$install_time = false;
		if ($this->install_time > 0)
		{
			if ($this->install_time < 60)
			{
				$install_time = phpbb::$user->lang['INSTALL_LESS_THAN_1_MINUTE'];
			}
			else
			{
				$install_time = phpbb::$user->lang('INSTALL_MINUTES', (int) ($this->install_time / 60));
			}
		}
		
        // ColorizeIt stuff
        $url_colorizeit = '';
        if($this->revision_status == TITANIA_REVISION_APPROVED && strlen(titania::$config->colorizeit) && $this->contrib && $this->contrib->has_colorizeit())
        {
            $url_colorizeit = 'http://' . titania::$config->colorizeit_url . '/custom/' . titania::$config->colorizeit . '.html?id=' . $this->attachment_id . '&amp;sample=' . $this->contrib->clr_sample['attachment_id'];
        }

		phpbb::$template->assign_block_vars($tpl_block, array(
			'REVISION_ID'		=> $this->revision_id,
			'CREATED'			=> phpbb::$user->format_date($this->revision_time),
			'NAME'				=> ($this->revision_name) ? censor_text($this->revision_name) : (($this->contrib) ? $this->contrib->contrib_name . ' ' . $this->revision_version : ''),
			'VERSION'			=> $this->revision_version,
			'VALIDATED_DATE'	=> ($this->validation_date) ? phpbb::$user->format_date($this->validation_date) : phpbb::$user->lang['NOT_VALIDATED'],
			'REVISION_QUEUE'	=> ($show_queue && $this->revision_queue_id) ? titania_url::build_url('manage/queue', array('q' => $this->revision_queue_id)) : '',
			'PHPBB_VERSION'		=> (sizeof($ordered_phpbb_versions) == 1) ? $ordered_phpbb_versions[0] : '',
			'REVISION_LICENSE'	=> ($this->revision_license) ? censor_text($this->revision_license) : (($this->contrib && sizeof(titania_types::$types[$this->contrib->contrib_type]->license_options)) ? phpbb::$user->lang['UNKNOWN'] : ''),
			'INSTALL_TIME'		=> $install_time,
			'INSTALL_LEVEL'		=> ($this->install_level > 0) ? phpbb::$user->lang['INSTALL_LEVEL_' . $this->install_level] : '',

			'U_DOWNLOAD'		=> $this->get_url(),
			'U_COLORIZEIT'      => $url_colorizeit,
			'U_EDIT'			=> ($this->contrib && ($this->contrib->is_author || $this->contrib->is_active_coauthor || titania_types::$types[$this->contrib->contrib_type]->acl_get('moderate'))) ? $this->contrib->get_url('revision_edit', array('revision' => $this->revision_id)) : '',
			'U_VIEW_INSTALL'	=> (titania_types::$types[$this->contrib->contrib_type]->display_install_file && file_exists(titania::$config->modx_storage_path . $this->revision_id)) ? titania_url::build_url('view-install', array('id' => $this->revision_id)) : '',

			'S_USE_QUEUE'			=> (titania::$config->use_queue && titania_types::$types[$this->contrib->contrib_type]->use_queue) ? true : false,
			'S_NEW'					=> ($this->revision_status == TITANIA_REVISION_NEW) ? true : false,
			'S_APPROVED'			=> ($this->revision_status == TITANIA_REVISION_APPROVED) ? true : false,
			'S_DENIED'				=> ($this->revision_status == TITANIA_REVISION_DENIED) ? true : false,
			'S_PULLED_SECURITY'		=> ($this->revision_status == TITANIA_REVISION_PULLED_SECURITY) ? true : false,
			'S_PULLED_OTHER'		=> ($this->revision_status == TITANIA_REVISION_PULLED_OTHER) ? true : false,
			'S_REPACKED'			=> ($this->revision_status == TITANIA_REVISION_REPACKED) ? true : false,
			'S_RESUBMITTED'			=> ($this->revision_status == TITANIA_REVISION_RESUBMITTED) ? true : false,
		));

		phpbb::$user->date_format = $old_date_format;

		// Output phpBB versions
		foreach ($ordered_phpbb_versions as $version)
		{
			phpbb::$template->assign_block_vars($tpl_block . '.phpbb_versions', array(
				'VERSION'		=> $version,
			));
		}

		// Output translations
		if (sizeof($this->translations))
		{
			$translations = new titania_attachment(TITANIA_TRANSLATION, $this->revision_id);
			$translations->store_attachments($this->translations);

			foreach ($translations->parse_attachments($message = false) as $attachment)
			{
				phpbb::$template->assign_block_vars($tpl_block . '.translations', array(
					'DISPLAY_ATTACHMENT'	=> $attachment,
				));
			}
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
			if (!titania::$config->require_validation || !titania_types::$types[$this->contrib->contrib_type]->require_validation)
			{
				$this->contrib->contrib_last_update = titania::$time;
				$sql_ary = array(
					'contrib_last_update' 		=> $this->contrib->contrib_last_update,
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

		// Update the release topic
		if ($this->revision_status == TITANIA_REVISION_APPROVED)
		{
			$this->contrib->update_release_topic();
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

				// Update the contributions table if this is the newest validated revision
				$sql = 'SELECT revision_id FROM ' . $this->sql_table . '
					WHERE revision_status = ' . TITANIA_REVISION_APPROVED . '
					AND contrib_id = ' . $this->contrib_id . '
					ORDER BY revision_id DESC';
				phpbb::$db->sql_query_limit($sql, 1);
				$newest_revision_id = phpbb::$db->sql_fetchfield('revision_id');
				phpbb::$db->sql_freeresult();

				if ($newest_revision_id && (int) $newest_revision_id < $this->revision_id)
				{
					$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
						SET contrib_last_update = ' . titania::$time . '
						WHERE contrib_id = ' . (int) $this->contrib_id;
					phpbb::$db->sql_query($sql);
				}
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

		// Update the release topic
		if ($this->revision_status == TITANIA_REVISION_APPROVED)
		{
			$this->contrib->update_release_topic();
		}
	}

	/**
	* Repack a revision
	*
	* @param titania_revision $old_revision titania_revision object
	*/
	public function repack($old_revision)
	{
		if (!$this->revision_id)
		{
			throw new exception('Submit the revision before repacking');
		}

		titania::add_lang('manage');

		// Get the old and new queue objects
		$old_queue = $old_revision->get_queue();
		$queue = $this->get_queue();

		if ($old_queue === false)
		{
			throw new exception('Old queue missing. Revision ID: ' . $old_revision->revision_id);
		}

		// Reply to the queue topic to say that it's been repacked and have the old mpv/automod results listed in it as well
		$repack_message = phpbb::$user->lang['REVISION_REPACKED'] . "\n\n";

		// Add the MPV results
		if ($old_queue->mpv_results)
		{
			$mpv_results = $old_queue->mpv_results;
			titania_decode_message($mpv_results, $old_queue->mpv_results_uid);
			$repack_message .= '[quote=&quot;' . phpbb::$user->lang['OLD_VALIDATION_MPV'] . '&quot;]' . $mpv_results . "[/quote]\n";
		}

		// Add the Automod results
		if ($old_queue->automod_results)
		{
			$repack_message .= '[quote=&quot;' . phpbb::$user->lang['OLD_VALIDATION_AUTOMOD'] . '&quot;]' . $old_queue->automod_results . "[/quote]\n";
		}

		// Reply
		$old_queue->topic_reply($repack_message);

		// Update the old queue with the new results
		$old_queue->revision_id = $queue->revision_id;
		$old_queue->mpv_results = $queue->mpv_results;
		$old_queue->mpv_results_bitfield = $queue->mpv_results_bitfield;
		$old_queue->mpv_results_uid = $queue->mpv_results_uid;
		$old_queue->automod_results = $queue->automod_results;
		$old_queue->submit();

		// Delete the new queue we made for this revision
		$queue->delete();

		// Unlink the old queue_id from the old revision and set it to repacked
		$old_revision->change_status(TITANIA_REVISION_REPACKED);
		$old_revision->revision_queue_id = 0;
		$old_revision->submit();

		// Update the queue_id for this revision to point to the old queue_id
		$this->revision_queue_id = $old_queue->queue_id;
		$this->submit();

		// Move any translations
		$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
			SET object_id = ' . $this->revision_id . '
			WHERE object_type = ' . TITANIA_TRANSLATION . '
				AND object_id = ' . $old_revision->revision_id;
		phpbb::$db->sql_query($sql);

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	public function delete()
	{
		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);

		// Delete the queue item
		$queue = $this->get_queue();

		if ($queue !== false)
		{
			$queue->delete();
		}

		// Delete the attachment
		$attachment = new titania_attachment(TITANIA_CONTRIB);
		$attachment->attachment_id = $this->attachment_id;
		$attachment->load();
		$attachment->delete();

		// Delete translations
		// $translations = new titania_attachment(TITANIA_TRANSLATION, $this->revision_id);
		// $attachment->delete_all();

		// Self-destruct
		parent::delete();
	}

	/**
	* Update/create the queue entry for this revision
	*/
	public function update_queue()
	{
		// Create the queue entry if required, else update it
		if (titania::$config->use_queue && titania_types::$types[$this->contrib->contrib_type]->use_queue)
		{
			$queue = $this->get_queue();

			// Only create the queue for revisions set as new
			if ($queue === false && $this->revision_status == TITANIA_REVISION_NEW)
			{
				$queue = new titania_queue;
			}

			// If we have to create or update one...
			if ($queue !== false)
			{
				$queue->__set_array(array(
					'revision_id'			=> $this->revision_id,
					'contrib_id'			=> $this->contrib_id,
					'contrib_name_clean'	=> $this->contrib->contrib_name_clean,
				));

				// Set the queue status to new if it's submitted and the queue status is set to hide it
				if ($this->revision_submitted && $queue->queue_status == TITANIA_QUEUE_HIDE)
				{
					// Only set the queue as new if there are not any newer revisions in the queue
					$sql = 'SELECT queue_id FROM ' . TITANIA_QUEUE_TABLE . '
						WHERE contrib_id = ' . (int) $this->contrib_id . '
							AND revision_id > ' . $this->revision_id;
					$result = phpbb::$db->sql_query($sql);
					if(!($row = phpbb::$db->sql_fetchrow($result)))
					{
						$queue->queue_status = TITANIA_QUEUE_NEW;
					}
				}

				$queue->submit();

				// Set the revision queue id
				$this->revision_queue_id = $queue->queue_id;
				parent::submit();

				if ($this->revision_submitted)
				{
					// Change the status on any old revisions that were in the queue and marked as New to repacked
					$sql = 'SELECT * FROM ' . TITANIA_QUEUE_TABLE . '
						WHERE contrib_id = ' . (int) $this->contrib_id . '
							AND revision_id < ' . $this->revision_id . '
							AND queue_status = ' . TITANIA_QUEUE_NEW;
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$queue = new titania_queue;
						$queue->__set_array($row);
						$queue->close(TITANIA_REVISION_RESUBMITTED);
						unset($queue);
					}
					phpbb::$db->sql_freeresult($result);
				}
			}
		}
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
