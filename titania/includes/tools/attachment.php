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
 * Attachment handler
 *
 * @package Titania
 */
class titania_attachment extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_ATTACHMENTS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'attachment_id';

	/**
	* Handles the name of the form field
	*
	* @var string
	*/
	protected $form_name = '';

	/**
	* Additional fields to handle during output
	* Use an array with the keys being output keys, values being $row keys, EX: array('TEST' => 'attachment_test')
	* See parse_uploader for more
	*
	* @var array
	*/
	public $additional_fields = array();

	/**
	* Stores the currently attached attachments to the object (send with store_attachments())
	* Used for outputting the with the uploader and for grabbing any updated info on them
	* Private to make sure the array is setup correct
	*
	* @var array
	*/
	private $attachments = array();

	/**
	 * Upload class
	 *
	 * @var object
	 */
	public $uploader = false;

	/**
	* Stores the errors (if any) when attaching
	*
	* @var array
	*/
	public $error = array();

	/**
	* Did we upload/delete a file?
	*
	* @param bool True if we did False if not
	*/
	public $uploaded = false;
	public $deleted = false;

	/**
	 * Constructor for attachment/download class
	 *
	 * @param int $object_type Attachment type (check main type constants)
	 * @param object $object_id int
	 */
	public function __construct($object_type, $object_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attachment_id'			=> array('default' => 0),
			'attachment_access'		=> array('default' => TITANIA_ACCESS_PUBLIC),
			'attachment_user_id'	=> array('default' => (int) phpbb::$user->data['user_id']),
			'object_type'			=> array('default' => 0),
			'object_id'				=> array('default' => 0),

			'physical_filename'		=> array('default' => '',	'max' => 255),
			'attachment_directory'	=> array('default' => '',	'max' => 255),
			'real_filename'			=> array('default' => '',	'max' => 255),
			'attachment_comment'	=> array('default' => ''),

			'download_count'		=> array('default' => 0),

			'filesize'				=> array('default' => 0),
			'filetime'				=> array('default' => titania::$time),
			'extension'				=> array('default' => '',	'max' => 100),
			'mimetype'				=> array('default' => '',	'max' => 100),
			'hash'					=> array('default' => '',	'max' => 32,	'multibyte' => false),

			'thumbnail'				=> array('default' => 0),
			'is_orphan'				=> array('default' => 1),
			'is_preview'			=> array('default' => 0),
		));

		$this->object_type = (int) $object_type;
		$this->object_id = (int) $object_id;

		$this->form_name = 'titania_attachment_' . $this->object_type . '_' . $this->object_id;

		phpbb::$user->add_lang('posting');
	}

	/**
	* Send me the attachments already attached to this item!
	* Handled this way to prevent a nubbing of the attachments array.
	*
	* @param array $attachments (should be the row directly from the attachments table)
	*/
	public function store_attachments($attachments)
	{
		foreach ($attachments as $row)
		{
			$this->attachments[$row['attachment_id']] = $row;
		}
	}

	/**
	* Load the attachments from the database from the ids and store them in $this->attachments
	*
	* @param array $attachment_ids
	* @param bool $include_orphans False (default) to not include orphans, true to include orphans
	*/
	public function load_attachments($attachment_ids = false, $include_orphans = false)
	{
		// Do not load if we do not have an object_id or an empty array of attachment_ids
		if (!$this->object_id || (!sizeof($attachment_ids) && $attachment_ids !== false))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND object_id = ' . (int) $this->object_id .
				(($attachment_ids !== false) ? ' AND ' . phpbb::$db->sql_in_set('attachment_id', array_map('intval', $attachment_ids)) : '') .
				((!$include_orphans) ? ' AND is_orphan = 0' : '');
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->attachments[$row['attachment_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Load the attachments from the database from the ids and store them in $this->attachments
	*
	* @param array $object_ids Array of object_ids to load
	* @param bool $include_orphans False (default) to not include orphans, true to include orphans
	*
	* @return array of attachments in array(object_id => array(attachment rows))
	*/
	public function load_attachments_set($object_ids, $include_orphans = false)
	{
		$attachments_set = array();

		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND ' . phpbb::$db->sql_in_set('object_id', array_map('intval', $object_ids)) .
				((!$include_orphans) ? ' AND is_orphan = 0' : '');
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$attachments_set[$row['object_id']][] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		return $attachments_set;
	}

	/**
	* In case they are needed...
	*/
	public function get_attachments()
	{
		return $this->attachments;
	}

	/**
	* Used for outputting multiple items with different attachments and not having to create tons of objects
	*/
	public function clear_attachments()
	{
		$this->attachments = array();
	}

	/**
	 * Parse the uploader
	 *
	 * @param <string> $tpl_file The name of the template file to use to create the uploader
	 * @return <string> The parsed HTML code ready for output
	 */
	public function parse_uploader($tpl_file = 'posting/attachments/default.html')
	{
		// If the upload max filesize is less than 0, do not show the uploader (0 = unlimited)
		if (titania::$access_level != TITANIA_ACCESS_TEAMS)
		{
			if (isset(titania::$config->upload_max_filesize[$this->object_type]) && titania::$config->upload_max_filesize[$this->object_type] < 0)
			{
				return '';
			}
		}

		phpbb::$template->assign_vars(array(
			'FORM_NAME'			=> $this->form_name,
			'MAX_LENGTH'		=> (titania::$access_level != TITANIA_ACCESS_TEAMS) ? phpbb::$config['max_filesize'] : false,

			// Make sure the form type is correct...doing it here just in case someone leaves {S_FORM_ENCTYPE} and forgets about it
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',

			'S_INLINE_ATTACHMENT_OPTIONS'	=> true,
			'SELECT_PREVIEW'	=> ($this->object_type == TITANIA_SCREENSHOT || $this->object_type == TITANIA_CLR_SCREENSHOT) ? true : false,
			'SELECT_REVIEW_VAR' => 'set_preview_file' . $this->object_type
		));

		// Sort correctly
		if (phpbb::$config['display_order'])
		{
			// Ascending sort
			krsort($this->attachments);
		}
		else
		{
			// Descending sort
			ksort($this->attachments);
		}

        // Delete previous attachments list
        unset(phpbb::$template->_tpldata['attach_row']);

		foreach ($this->attachments as $attachment_id => $row)
		{
			$output = array(
				'FILENAME'			=> basename($row['real_filename']),
				'FILE_COMMENT'		=> utf8_normalize_nfc(request_var('attachment_comment_' . $attachment_id, (string) $row['attachment_comment'], true)),
				'ATTACH_ID'			=> $row['attachment_id'],

				'U_VIEW_ATTACHMENT'	=> titania_url::build_url('download', array('id' => $row['attachment_id'])),

				'S_DELETE'			=> (!isset($row['no_delete']) || !$row['no_delete']) ? true : false,
				'S_PREVIEW'			=> (isset($row['is_preview']) && $row['is_preview']) ? true : false,
				//'S_DELETED'			=> (isset($row['deleted']) && $row['deleted']) ? true : false,
			);

			// Allow additional things to be outputted
			foreach ($this->additional_fields as $output_key => $row_key)
			{
				// Try to grab it from post first
				if (isset($_POST[$row_key . '_' . $row['attachment_id']]))
				{
					$output[$output_key] = utf8_normalize_nfc(request_var($row_key . '_' . $row['attachment_id'], '', true));
				}
				else if (isset($row[$row_key]))
				{
					$output[$output_key] = $row[$row_key];
				}
			}

			phpbb::$template->assign_block_vars('attach_row', $output);
		}

		phpbb::$template->set_filenames(array(
			$tpl_file	=> $tpl_file,
		));

		return phpbb::$template->assign_display($tpl_file);
	}

	/**
	* Upload any files we attempted to attach
	*
	* @param bool|int $max_thumbnail_width The maximum thumbnail width (if we create one)
	*/
	public function upload($max_thumbnail_width = false)
	{
		// First, we shall handle the items already attached
		$attached_ids = request_var($this->form_name . '_attachments', array(0));

		// Query the ones we must
		$to_query = array_diff($attached_ids, array_keys($this->attachments));
		if (sizeof($to_query))
		{
			$sql = 'SELECT * FROM ' . $this->sql_table . '
				WHERE ' . phpbb::$db->sql_in_set('attachment_id', array_map('intval', $to_query)) . '
					AND object_type = ' . (int) $this->object_type . '
					AND object_id = ' . (int) $this->object_id; // Don't let them be messin with us
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$this->attachments[$row['attachment_id']] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}

		// Next, delete those requested
		$delete = request_var('delete_file', array(0));
		foreach ($delete as $attach_id => $null)
		{
			$this->delete($attach_id);

			$this->deleted = true;

			// Sometime I'll look into this again; having it setup to only delete attachments after the form is submitted
			/*if (isset($this->attachments[$attach_id]))
			{
				$this->attachments[$attach_id]['deleted'] = true;
			}*/
		}

		// set requested attachment as preview
		$preview = request_var('set_preview_file' . $this->object_type, 0);
		if ($preview)
		{
			$this->set_preview($preview);
		}


		// And undelete any
		/*$undelete = request_var('undelete_file', array(0));
		foreach ($delete as $attach_id => $null)
		{
			if (isset($this->attachments[$attach_id]))
			{
				$this->attachments[$attach_id]['deleted'] = false;
			}
		}*/

		if (isset($_FILES[$this->form_name]))
		{
			// In order to save ourselves from rewriting the phpBB uploader to support multi-uploads, we have to do some hacking
			$uploaded_files = array();

			if (is_array($_FILES[$this->form_name]['name']))
			{
				// Store the files in our own data array
				foreach ($_FILES[$this->form_name]['name'] as $id => $name)
				{
					$uploaded_files[] = array(
						'name'		=> $name,
						'type'		=> $_FILES[$this->form_name]['type'][$id],
						'tmp_name'	=> $_FILES[$this->form_name]['tmp_name'][$id],
						'error'		=> $_FILES[$this->form_name]['error'][$id],
						'size'		=> $_FILES[$this->form_name]['size'][$id],
					);
				}
			}
			else
			{
				// Compatibility with non-multi-upload forms
				$uploaded_files[] = $_FILES[$this->form_name];
			}

			// Finally upload new items if required
			foreach ($uploaded_files as $uploaded_file)
			{
				// Hack time
				$_FILES[$this->form_name] = $uploaded_file;

				if ($_FILES[$this->form_name]['name'] != 'none' && trim($_FILES[$this->form_name]['name']))
				{
					// Setup uploader tool.
					$this->uploader = new titania_uploader($this->form_name, $this->object_type);

					// Try uploading the file.
					$this->uploader->upload_file();

					// Store for easier access
					$this->error = array_merge($this->error, $this->uploader->filedata['error']);

					// If we had no problems we can submit the data to the database.
					if (!sizeof($this->uploader->filedata['error']))
					{
						// Create thumbnail
						$has_thumbnail = false;
						$is_preview = false;
						if ($this->uploader->filedata['is_image'])
						{
							phpbb::_include('functions_posting', 'create_thumbnail');
							$src = titania::$config->upload_path . utf8_basename($this->uploader->filedata['attachment_directory']) . '/' . utf8_basename($this->uploader->filedata['physical_filename']);
							$dst = titania::$config->upload_path . utf8_basename($this->uploader->filedata['attachment_directory']) . '/thumb_' . utf8_basename($this->uploader->filedata['physical_filename']);
							$has_thumbnail = $this->create_thumbnail($src, $dst, $this->uploader->filedata['mimetype'], $max_thumbnail_width, (($max_thumbnail_width === false) ? false : 0));
							
							// set first screenshot as preview image when it is uploaded
							$is_preview = (empty($this->attachments)) ? true : false;
						}

						$this->__set_array(array(
							'attachment_id'			=> 0,
							'physical_filename'		=> $this->uploader->filedata['physical_filename'],
							'attachment_directory'	=> $this->uploader->filedata['attachment_directory'],
							'real_filename'			=> $this->uploader->filedata['real_filename'],
							'extension'				=> $this->uploader->filedata['extension'],
							'mimetype'				=> $this->uploader->filedata['mimetype'],
							'filesize'				=> $this->uploader->filedata['filesize'],
							'filetime'				=> $this->uploader->filedata['filetime'],
							'hash'					=> $this->uploader->filedata['md5_checksum'],
							'thumbnail'				=> $has_thumbnail,
							'is_preview'			=> $is_preview,

							'attachment_comment'	=> utf8_normalize_nfc(request_var('filecomment', '', true)),
						));
						parent::submit();

						// Store in $this->attachments[]
						$this->attachments[$this->attachment_id] = $this->__get_array();

						// Additional fields
						foreach ($this->additional_fields as $output_key => $row_key)
						{
							$this->attachments[$this->attachment_id][$row_key] = utf8_normalize_nfc(request_var($row_key, '', true));
						}
					}

					$this->uploaded = true;
				}
			}


			// We do not want to upload it again if this function is called again.
			unset($_FILES[$this->form_name]);
		}
	}

	/**
	* Submit the attachments
	* Handles setting orphans, access level, deleting
	* @todo Deleting
	*
	* @param int $attachment_access Access level of the parent (to handle download permissions for the attachments)
	*/
	public function submit($attachment_access = TITANIA_ACCESS_PUBLIC)
	{
		if (!sizeof($this->attachments))
		{
			return;
		}

		// Update the attachment comments if necessary
		foreach ($this->attachments as $attachment_id => $row)
		{
			// Delete those requested
			/*if (isset($row['deleted']) && $row['deleted'])
			{
				$this->delete($attachment_id);

				continue;
			}*/

			$attachment_comment = utf8_normalize_nfc(request_var('attachment_comment_' . $attachment_id, '', true));
			if ($row['attachment_comment'] != $attachment_comment)
			{
				$sql = 'UPDATE ' . $this->sql_table . '
					SET attachment_comment = \'' . phpbb::$db->sql_escape($attachment_comment) . '\'
					WHERE attachment_id = ' . $attachment_id;
				phpbb::$db->sql_query($sql);
			}
		}

		// Check again...could have deleted all of those attached
		/*if (!sizeof($this->attachments))
		{
			return;
		}*/

		// Update access and is_orphan
		$sql_ary = array(
			'object_id'			=> $this->object_id, // needed when items are attached during initial creation.
			'attachment_access'	=> $attachment_access,
			'is_orphan'			=> 0,
		);

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE ' . phpbb::$db->sql_in_set('attachment_id', array_map('intval', array_keys($this->attachments)));
		phpbb::$db->sql_query($sql);
	}

	/**
	* Delete all attachments for the current object type/id
	*/
	public function delete_all()
	{
		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND object_id = ' . (int) $this->object_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->attachments[$row['attachment_id']] = $row;

			$this->delete($row['attachment_id']);
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	 * Removes file from server and database.
	 *
	 * @return void
	 */
	public function delete($attachment_id = false)
	{
		$attachment_id = ($attachment_id === false) ? $this->attachment_id : (int) $attachment_id;

		if (!$attachment_id)
		{
			return;
		}

		if ($attachment_id == $this->attachment_id)
		{
			@unlink(titania::$config->upload_path . utf8_basename($this->attachment_directory) . '/' . utf8_basename($this->physical_filename));
			@unlink(titania::$config->upload_path . utf8_basename($this->attachment_directory) . '/thumb_' . utf8_basename($this->physical_filename));
			parent::delete();
		}
		else if (isset($this->attachments[$attachment_id]))
		{
			@unlink(titania::$config->upload_path . utf8_basename($this->attachments[$attachment_id]['attachment_directory']) . '/' . utf8_basename($this->attachments[$attachment_id]['physical_filename']));
			@unlink(titania::$config->upload_path . utf8_basename($this->attachments[$attachment_id]['attachment_directory']) . '/thumb_' . utf8_basename($this->attachments[$attachment_id]['physical_filename']));

			$sql = 'DELETE FROM ' . $this->sql_table . ' WHERE attachment_id = ' . $attachment_id;
			phpbb::$db->sql_query($sql);
			
			if ($this->attachments[$attachment_id]['is_preview'] && ($this->attachments[$attachment_id]['object_type'] == TITANIA_SCREENSHOT || $this->attachments[$attachment_id]['object_type'] == TITANIA_CLR_SCREENSHOT))
			{
				$set_new_preview = true;
			}
		}

		if (isset($this->attachments[$attachment_id]))
		{
			unset($this->attachments[$attachment_id]);
		}
		
		// set the next screenshot as preview
		if (key($this->attachments) && isset($set_new_preview) && $set_new_preview)
		{
			$this->set_preview(key($this->attachments));
		}
	}
	
	/**
	* Sets images as preview image
	*
	* @return void
	*/
	public function set_preview($attachment_id = false)
	{
		$attachment_id = ($attachment_id === false) ? $this->attachment_id : (int) $attachment_id;

		$unset_preview = array();

		if (!$attachment_id)
		{
			return;
		}

		if((isset($this->attachments[$attachment_id]) && $this->attachments[$attachment_id]['is_preview']) || (isset($this->attachments['is_preview']) && $this->attachments['is_preview']))
		{
			return;
		}

		if($attachment_id == $this->attachment_id)
		{
			// since this is only for screenshots, just return
			return;
		}

		// only set screenshots as preview
		if (isset($this->attachments[$attachment_id]) && ($this->attachments[$attachment_id]['object_type'] == TITANIA_SCREENSHOT || $this->attachments[$attachment_id]['object_type'] == TITANIA_CLR_SCREENSHOT))
		{
			foreach($this->attachments as $attach_id => $null)
			{
				if($attach_id == $attachment_id)
				{
					continue;
				}

				$unset_preview[] = $attach_id;
				$this->attachments[$attach_id]['is_preview'] = 0;
			}

			// Update database for attachments that are not used as preview
			if(!empty($unset_preview))
			{
				$sql = 'UPDATE ' . $this->sql_table . ' SET is_preview = 0 WHERE ' . phpbb::$db->sql_in_set('attachment_id', $unset_preview);
				phpbb::$db->sql_query($sql);
			}

			$sql = 'UPDATE ' . $this->sql_table . ' SET is_preview = 1 WHERE attachment_id = ' . $attachment_id;
			phpbb::$db->sql_query($sql);

			$this->attachments[$attachment_id]['is_preview'] = 1;
		}
	}

	/**
	* Get id of preview image
	*
	* @return false/array
	*/
	public function get_preview()
	{
		// Do not load if we do not have an object_id
		if (!$this->object_id)
		{
			return false;
		}

        // Find attachment with is_preview = 1
		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND object_id = ' . (int) $this->object_id . '
				AND is_orphan = 0
				AND is_preview = 1';
        $result = phpbb::$db->sql_query_limit($sql, 1);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
		return $row;
	}

	/**
	* General attachment parsing
	* From phpBB (includes/functions_content.php)
	*
	* @param string &$message The message
	* @param string $tpl The template file to use
	* @param bool $preview true if previewing from the posting page
	* @param string|bool $template_block If not false we will output the parsed attachments to this template block
	*
	* @return array the parsed attachments
	*/
	public function parse_attachments(&$message, $tpl = 'common/attachment.html', $preview = false, $template_block = false)
	{
		if (!sizeof($this->attachments))
		{
			return array();
		}

		phpbb::$user->add_lang('viewtopic');

		$compiled_attachments = array();

		if ($tpl !== false && !isset(phpbb::$template->filename['titania_attachment_tpl']))
		{
			phpbb::$template->set_filenames(array(
				'titania_attachment_tpl'	=> $tpl,
			));
		}

		// Sort correctly
		if (phpbb::$config['display_order'])
		{
			// Ascending sort
			krsort($this->attachments);
		}
		else
		{
			// Descending sort
			ksort($this->attachments);
		}

		foreach ($this->attachments as $attachment_id => $attachment)
		{
			if (!sizeof($attachment))
			{
				continue;
			}

			// We need to reset/empty the _file block var, because this function might be called more than once
			phpbb::$template->destroy_block_vars('_file');

			$block_array = array();

			// Some basics...
			$attachment['extension'] = strtolower(trim($attachment['extension']));
			$filename = titania::$config->upload_path . $attachment['attachment_directory'] . '/' . utf8_basename($attachment['attachment_directory']) . '/' . utf8_basename($attachment['physical_filename']);
			$thumbnail_filename = titania::$config->upload_path . $attachment['attachment_directory'] . '/' . utf8_basename($attachment['attachment_directory']) . '/thumb_' . utf8_basename($attachment['physical_filename']);

			$filesize = get_formatted_filesize($attachment['filesize'], false);

			if ($preview)
			{
				$comment = bbcode_nl2br(censor_text(utf8_normalize_nfc(request_var('attachment_comment_' . $attachment_id, (string) $attachment['attachment_comment'], true))));
			}
			else
			{
				$comment = bbcode_nl2br(censor_text($attachment['attachment_comment']));
			}

			$block_array += array(
				'FILESIZE'			=> $filesize['value'],
				'SIZE_LANG'			=> $filesize['unit'],
				'DOWNLOAD_NAME'		=> utf8_basename($attachment['real_filename']),
				'COMMENT'			=> $comment,
			);


			$l_downloaded_viewed = $download_link = '';
			$display_cat = (strpos($attachment['mimetype'], 'image') === 0) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE; // @todo Probably should add support for more types...

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if (phpbb::$config['img_display_inlined'])
					{
						if (phpbb::$config['img_link_width'] || phpbb::$config['img_link_height'])
						{
							$dimension = @getimagesize($filename);

							// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
							if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
							{
								$display_cat = ATTACHMENT_CATEGORY_NONE;
							}
							else
							{
								$display_cat = ($dimension[0] <= phpbb::$config['img_link_width'] && $dimension[1] <= phpbb::$config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;
							}
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			// Make some descisions based on user options being set.
			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !phpbb::$user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			if ($display_cat == ATTACHMENT_CATEGORY_FLASH && !phpbb::$user->optionget('viewflash'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			$download_link = titania_url::build_url('download', array('id' => $attachment['attachment_id']));

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$download_link = titania_url::append_url($download_link, array('mode' => 'view'));

					$block_array += array(
						'S_IMAGE'			=> true,
						'U_INLINE_LINK'		=> titania_url::append_url($download_link, array('mode' => 'view')),
					);
				break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$download_link = titania_url::append_url($download_link, array('mode' => 'view'));

					$block_array += array(
						'S_THUMBNAIL'		=> true,
						'THUMB_IMAGE'		=> titania_url::append_url($download_link, array('mode' => 'view', 'thumb' => 1)),
					);
				break;

				// Windows Media Streams
				case ATTACHMENT_CATEGORY_WM:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					// Giving the filename directly because within the wm object all variables are in local context making it impossible
					// to validate against a valid session (all params can differ)
					// $download_link = $filename;

					$block_array += array(
						'ATTACH_ID'		=> $attachment['attachment_id'],
						'S_WM_FILE'		=> true,
					);
				break;

				// Real Media Streams
				case ATTACHMENT_CATEGORY_RM:
				case ATTACHMENT_CATEGORY_QUICKTIME:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_RM_FILE'			=> ($display_cat == ATTACHMENT_CATEGORY_RM) ? true : false,
						'S_QUICKTIME_FILE'	=> ($display_cat == ATTACHMENT_CATEGORY_QUICKTIME) ? true : false,
						'ATTACH_ID'			=> $attachment['attachment_id'],
					);
				break;

				// Macromedia Flash Files
				case ATTACHMENT_CATEGORY_FLASH:
					list($width, $height) = @getimagesize($filename);

					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_FLASH_FILE'	=> true,
						'WIDTH'			=> $width,
						'HEIGHT'		=> $height,
						'U_VIEW_LINK'	=> titania_url::append_url($download_link, array('view' => 1)),
					);
				break;

				default:
					$l_downloaded_viewed = 'DOWNLOAD_COUNT';

					$block_array += array(
						'S_FILE'		=> true,
					);
				break;
			}

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? phpbb::$user->lang[$l_downloaded_viewed . '_NONE'] : (($attachment['download_count'] == 1) ? sprintf(phpbb::$user->lang[$l_downloaded_viewed], $attachment['download_count']) : sprintf(phpbb::$user->lang[$l_downloaded_viewed . 'S'], $attachment['download_count']));

			$block_array += array(
				'U_DOWNLOAD_LINK'		=> $download_link,
				'L_DOWNLOAD_COUNT'		=> $l_download_count
			);

			// If a template block is specified, output to that also
			if ($template_block)
			{
				phpbb::$template->assign_block_vars($template_block, $block_array);
			}

			if ($tpl !== false)
			{
				phpbb::$template->assign_block_vars('_file', $block_array);

				$compiled_attachments[] = phpbb::$template->assign_display('titania_attachment_tpl');
			}
		}

		$tpl_size = sizeof($compiled_attachments);

		$unset_tpl = array();

		// For inline attachments
		if ($message)
		{
			preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $message, $matches, PREG_PATTERN_ORDER);

			$replace = array();
			foreach ($matches[0] as $num => $capture)
			{
				// Flip index if we are displaying the reverse way
				$index = (phpbb::$config['display_order']) ? ($tpl_size-($matches[1][$num] + 1)) : $matches[1][$num];

				$replace['from'][] = $matches[0][$num];
				$replace['to'][] = (isset($compiled_attachments[$index])) ? $compiled_attachments[$index] : sprintf(phpbb::$user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);

				$unset_tpl[] = $index;
			}

			if (isset($replace['from']))
			{
				$message = str_replace($replace['from'], $replace['to'], $message);
			}

			$unset_tpl = array_unique($unset_tpl);

			// Needed to let not display the inlined attachments at the end of the post again
			foreach ($unset_tpl as $index)
			{
				unset($compiled_attachments[$index]);
			}
		}

		return $compiled_attachments;
	}

	/**
	* Create a thumbnail
	* From functions_posting (modified to include option to specify max_width/min_width)
	*
	* @param string $source
	* @param string $destination
	* @param string $mimetype
	* @param bool|string max_width specify the max_width
	* @param bool|string min_filesize specify the min_filesize
	*/
	public function create_thumbnail($source, $destination, $mimetype, $max_width = false, $min_filesize = false)
	{
		$min_filesize = ($min_filesize !== false) ? $min_filesize : (int) phpbb::$config['img_min_thumb_filesize'];
		$img_filesize = (file_exists($source)) ? @filesize($source) : false;

		if (!$img_filesize || $img_filesize <= $min_filesize)
		{
			return false;
		}

		$dimension = @getimagesize($source);

		if ($dimension === false)
		{
			return false;
		}

		list($width, $height, $type, ) = $dimension;

		if (empty($width) || empty($height))
		{
			return false;
		}

		list($new_width, $new_height) = $this->get_img_size_format($width, $height, $max_width);

		// Do not create a thumbnail if the resulting width/height is bigger than the original one
		if ($new_width >= $width && $new_height >= $height)
		{
			return false;
		}

		$used_imagick = false;

		// Only use imagemagick if defined and the passthru function not disabled
		if (phpbb::$config['img_imagick'] && function_exists('passthru'))
		{
			if (substr(phpbb::$config['img_imagick'], -1) !== '/')
			{
				phpbb::$config['img_imagick'] .= '/';
			}

			@passthru(escapeshellcmd(phpbb::$config['img_imagick']) . 'convert' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -geometry ' . $new_width . 'x' . $new_height . ' "' . str_replace('\\', '/', $source) . '" "' . str_replace('\\', '/', $destination) . '"');

			if (file_exists($destination))
			{
				$used_imagick = true;
			}
		}

		if (!$used_imagick)
		{
			$type = get_supported_image_types($type);

			if ($type['gd'])
			{
				// If the type is not supported, we are not able to create a thumbnail
				if ($type['format'] === false)
				{
					return false;
				}

				switch ($type['format'])
				{
					case IMG_GIF:
						$image = @imagecreatefromgif($source);
					break;

					case IMG_JPG:
						@ini_set('gd.jpeg_ignore_warning', 1);
						$image = @imagecreatefromjpeg($source);
					break;

					case IMG_PNG:
						$image = @imagecreatefrompng($source);
					break;

					case IMG_WBMP:
						$image = @imagecreatefromwbmp($source);
					break;
				}

				if (empty($image))
				{
					return false;
				}

				if ($type['version'] == 1)
				{
					$new_image = imagecreate($new_width, $new_height);

					if ($new_image === false)
					{
						return false;
					}

					imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}
				else
				{
					$new_image = imagecreatetruecolor($new_width, $new_height);

					if ($new_image === false)
					{
						return false;
					}

					// Preserve alpha transparency (png for example)
					@imagealphablending($new_image, false);
					@imagesavealpha($new_image, true);

					imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}

				// If we are in safe mode create the destination file prior to using the gd functions to circumvent a PHP bug
				if (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on')
				{
					@touch($destination);
				}

				switch ($type['format'])
				{
					case IMG_GIF:
						imagegif($new_image, $destination);
					break;

					case IMG_JPG:
						imagejpeg($new_image, $destination, 90);
					break;

					case IMG_PNG:
						imagepng($new_image, $destination);
					break;

					case IMG_WBMP:
						imagewbmp($new_image, $destination);
					break;
				}

				imagedestroy($new_image);
			}
			else
			{
				return false;
			}
		}

		if (!file_exists($destination))
		{
			return false;
		}

		phpbb_chmod($destination, CHMOD_READ | CHMOD_WRITE);

		return true;
	}
	
	/**
	* Return if there is a preview image and assign block vars if necessary
	*/
	public function preview_image()
	{
		foreach ($this->attachments as $attachment_id => $attachment)
		{
			if (!sizeof($attachment) || !$attachment['is_preview'])
			{
				continue;
			}

			$block_array = array();

			// Some basics...
			$attachment['extension'] = strtolower(trim($attachment['extension']));
			$filename = titania::$config->upload_path . $attachment['attachment_directory'] . '/' . utf8_basename($attachment['attachment_directory']) . '/' . utf8_basename($attachment['physical_filename']);
			$thumbnail_filename = titania::$config->upload_path . $attachment['attachment_directory'] . '/' . utf8_basename($attachment['attachment_directory']) . '/thumb_' . utf8_basename($attachment['physical_filename']);

			$filesize = get_formatted_filesize($attachment['filesize'], false);

			$comment = bbcode_nl2br(censor_text($attachment['attachment_comment']));

			$block_array += array(
				'FILESIZE'			=> $filesize['value'],
				'SIZE_LANG'			=> $filesize['unit'],
				'DOWNLOAD_NAME'		=> utf8_basename($attachment['real_filename']),
				'COMMENT'			=> $comment,
			);
			

			$l_downloaded_viewed = $download_link = '';
			$display_cat = (strpos($attachment['mimetype'], 'image') === 0) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE; // @todo Probably should add support for more types...

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if (phpbb::$config['img_display_inlined'])
					{
						if (phpbb::$config['img_link_width'] || phpbb::$config['img_link_height'])
						{
							$dimension = @getimagesize($filename);

							// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
							if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
							{
								$display_cat = ATTACHMENT_CATEGORY_NONE;
							}
							else
							{
								$display_cat = ($dimension[0] <= phpbb::$config['img_link_width'] && $dimension[1] <= phpbb::$config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;
							}
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			// Make some descisions based on user options being set.
			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !phpbb::$user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}
		
			$download_link = titania_url::build_url('download', array('id' => $attachment['attachment_id']));
			
			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$download_link = titania_url::append_url($download_link, array('mode' => 'view'));

					$block_array += array(
						'S_IMAGE'			=> true,
						'U_INLINE_LINK'		=> titania_url::append_url($download_link, array('mode' => 'view')),
					);
				break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$download_link = titania_url::append_url($download_link, array('mode' => 'view'));

					$block_array += array(
						'S_THUMBNAIL'		=> true,
						'THUMB_IMAGE'		=> titania_url::append_url($download_link, array('mode' => 'view', 'thumb' => 1)),
					);
				break;
				
				default:
					$l_downloaded_viewed = 'DOWNLOAD_COUNT';

					$block_array += array(
						'S_FILE'		=> true,
					);
				break;
			}

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? phpbb::$user->lang[$l_downloaded_viewed . '_NONE'] : (($attachment['download_count'] == 1) ? sprintf(phpbb::$user->lang[$l_downloaded_viewed], $attachment['download_count']) : sprintf(phpbb::$user->lang[$l_downloaded_viewed . 'S'], $attachment['download_count']));

			$block_array += array(
				'U_DOWNLOAD_LINK'		=> $download_link,
				'L_DOWNLOAD_COUNT'		=> $l_download_count
			);
		}
		
		if(!empty($block_array))
		{
			phpbb::$template->assign_block_vars('preview', $block_array);
			
			return true;
		}
		else
		{
			return false;
		}
	}	

	/**
	* Calculate the needed size for Thumbnail
	* From functions_posting (modified to include option to specify max_width)
	*/
	function get_img_size_format($width, $height, $max_width = false)
	{
		// Maximum Width the Image can take
		$max_width = ($max_width !== false) ? $max_width : ((phpbb::$config['img_max_thumb_width']) ? phpbb::$config['img_max_thumb_width'] : 400);

		if ($width > $height)
		{
			return array(
				round($width * ($max_width / $width)),
				round($height * ($max_width / $width))
			);
		}
		else
		{
			return array(
				round($width * ($max_width / $height)),
				round($height * ($max_width / $height))
			);
		}
	}
}
