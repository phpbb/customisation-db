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
			'revision_validated'	=> array('default' => false),
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
	}

	/**
	* Load the phpBB branches we've selected for this revision
	* Stored in $this->phpbb_versions
	*/
	public function load_phpbb_versions()
	{
		$sql = 'SELECT phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
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
		$versions = titania::$cache->get_phpbb_versions();

		phpbb::$template->assign_block_vars($tpl_block, array(
			'REVISION_ID'		=> $this->revision_id,
			'CREATED'			=> phpbb::$user->format_date($this->revision_time),
			'NAME'				=> censor_text($this->revision_name),
			'VERSION'			=> $this->revision_version,
			'VALIDATED_DATE'	=> ($this->validation_date) ? phpbb::$user->format_date($this->validation_date) : phpbb::$user->lang['NOT_VALIDATED'],
			'REVISION_QUEUE'	=> ($show_queue && $this->revision_queue_id) ? titania_url::build_url('manage/queue', array('q' => $this->revision_queue_id)) : '',
			'PHPBB_VERSION'		=> (sizeof($this->phpbb_versions) == 1) ? $versions[$this->phpbb_versions[0]['phpbb_version_branch'] . $this->phpbb_versions[0]['phpbb_version_revision']] : '',

			'U_DOWNLOAD'		=> $this->get_url(),

			'S_VALIDATED'		=> (!$this->revision_validated && titania::$config->use_queue) ? false : true,
		));

		foreach ($this->phpbb_versions as $row)
		{
			phpbb::$template->assign_block_vars($tpl_block . '.phpbb_versions', array(
				'VERSION'		=> $versions[$row['phpbb_version_branch'] . $row['phpbb_version_revision']],
			));
		}
	}

	/**
	 * Handle some stuff we need when submitting an attachment
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
				if ($this->contrib->contrib_release_topic_id)
				{
					$body = sprintf(phpbb::$user->lang[titania_types::$types[$this->contrib->contrib_type]->update_public],
						$this->revision_version
					);

					$options = array(
						'poster_id'				=> titania_types::$types[$this->contrib->contrib_type]->forum_robot,
						'forum_id' 				=> titania_types::$types[$this->contrib->contrib_type]->forum_database,
						'topic_id'				=> $this->contrib->contrib_release_topic_id,
						'topic_title'			=> 'Re: ' . $this->contrib->contrib_name,
						'post_text'				=> $body
					);

					if ($options['forum_id'] && $options['poster_id'])
					{
						phpbb_post_add($options);
					}
				}
				else
				{
					$body = sprintf(phpbb::$user->lang[titania_types::$types[$this->contrib->contrib_type]->create_public],
						$this->contrib->contrib_name,
						$this->contrib->author->get_url(),
						$this->contrib->author->username,
						$this->contrib->contrib_desc,
						$this->revision_version,
						titania_url::build_url('download', array('id' => $this->attachment_id)),
						$this->contrib->download['real_filename'],
						$this->contrib->download['filesize'],
						$this->contrib->get_url()
					);

					$options = array(
						'poster_id'				=> titania_types::$types[$this->contrib->contrib_type]->forum_robot,
						'forum_id' 				=> titania_types::$types[$this->contrib->contrib_type]->forum_database,
						'topic_title'			=> $this->contrib->contrib_name,
						'post_text'				=> $body
					);

					if ($options['forum_id'] && $options['poster_id'])
					{
						$topic_id = phpbb_topic_add($options);
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
					'phpbb_version_branch'		=> $row['phpbb_version_branch'],
					'phpbb_version_revision'	=> (isset($row['phpbb_version_revision'])) ? $row['phpbb_version_revision'] : titania::$config->phpbb_versions[$row['phpbb_version_branch']]['latest_revision'],
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
				'queue_status'			=> ($this->revision_submitted) ? TITANIA_QUEUE_NEW : TITANIA_QUEUE_HIDE,
			));
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
	}

	public function delete()
	{
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
