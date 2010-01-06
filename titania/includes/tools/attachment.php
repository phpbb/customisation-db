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
	protected $sql_table		= TITANIA_ATTACHMENTS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'attachment_id';

	/**
	* Handles the name of the form field
	*
	* @var string
	*/
	protected $form_name		= '';

	/**
	* Additional fields to handle during output
	* Use an array with the keys being output keys, values being $row keys, EX: array('TEST' => 'attachment_test')
	* See parse_uploader for more
	*
	* @var array
	*/
	public $additional_fields	= array();

	/**
	* Stores the currently attached attachments to the object (send with store_attachments())
	* Used for outputting the with the uploader and for grabbing any updated info on them
	* Private to make sure the array is setup correct
	*
	* @var array
	*/
	private $attachments		= array();

	/**
	 * Upload class
	 *
	 * @var object
	 */
	public $uploader			= false;

	/**
	* Stores the errors (if any) when attaching
	*
	* @var array
	*/
	public $error				= array();

	/**
	 * Constructor for attachment/download class
	 *
	 * @param int $object_type Attachment type (check TITANIA_DOWNLOAD_ for constants)
	 * @param object $object_id int
	 */
	public function __construct($object_type, $object_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attachment_id'			=> array('default' => 0),
			'attachment_access'		=> array('default' => 0),
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
		));

		$this->object_type = (int) $object_type;
		$this->object_id = (int) $object_id;

		$this->form_name = 'titania_attachment_' . $this->object_type . '_' . $this->object_id;
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
	*/
	public function load_attachments($attachment_ids)
	{
		if (!sizeof($attachment_ids))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE ' . phpbb::$db->sql_in_set('attachment_id', $attachment_ids);
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->attachments[$row['attachment_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* In case they are needed...
	*/
	public function get_attachments()
	{
		return $this->attachments;
	}

	/**
	 * Parse the uploader
	 *
	 * @param <string> $tpl_file The name of the template file to use to create the uploader
	 * @return <string> The parsed HTML code ready for output
	 */
	public function parse_uploader($tpl_file = 'posting/attachments/default.html')
	{
		titania::add_lang('attachments');

		phpbb::$template->assign_vars(array(
			'FORM_NAME'			=> $this->form_name,
			'MAX_LENGTH'		=> (titania::$access_level != TITANIA_ACCESS_TEAMS) ? phpbb::$config['max_filesize'] : false,

			// Make sure the form type is correct...doing it here just in case someone leaves {S_FORM_ENCTYPE} and forgets about it
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',
		));

		foreach ($this->attachments as $row)
		{
			$output = array(
				'FILENAME'			=> basename($row['real_filename']),
				'FILE_COMMENT'		=> $row['attachment_comment'],
				'ATTACH_ID'			=> $row['attachment_id'],

				'U_VIEW_ATTACHMENT'	=> titania_url::build_url('download', array('id' => $row['attachment_id'])),
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
	* @param string $ext_group The name of the extension group to allow (see TITANIA_ATTACH_EXT_ constants)
	*/
	public function upload($ext_group)
	{
		// First, we shall handle the items already attached
		$attached_ids = request_var($this->form_name . '_attachments', array(0));

		// Query the ones we must
		$to_query = array_diff($attached_ids, array_keys($this->attachments));
		if (sizeof($to_query))
		{
			$sql = 'SELECT * FROM ' . $this->sql_table . '
				WHERE ' . phpbb::$db->sql_in_set('attachment_id', $to_query) . '
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
		// @todo (just hide them for now - remove when submit() happens)

		// Finally upload new items if required
		//if (isset($_FILES[$this->form_name]) && !empty($_FILES[$this->form_name]))
		if ((isset($_FILES[$this->form_name]) && $_FILES[$this->form_name]['name'] != 'none' && trim($_FILES[$this->form_name]['name'])))
		{
			// Setup uploader tool.
			$this->uploader = new titania_uploader($this->form_name, $ext_group);

			// Try uploading the file.
			$this->uploader->upload_file();

			// Store for easier access
			$this->error = $this->uploader->filedata['error'];

			// If we had no problems we can submit the data to the database.
			if (!sizeof($this->error))
			{
				$this->__set_array(array(
					'physical_filename'		=> $this->uploader->filedata['physical_filename'],
					'attachment_directory'	=> $this->uploader->filedata['attachment_directory'],
					'real_filename'			=> $this->uploader->filedata['real_filename'],
					'extension'				=> $this->uploader->filedata['extension'],
					'mimetype'				=> $this->uploader->filedata['mimetype'],
					'filesize'				=> $this->uploader->filedata['filesize'],
					'filetime'				=> $this->uploader->filedata['filetime'],
					'hash'					=> $this->uploader->filedata['md5_checksum'],
					'thumbnail'				=> 0, // @todo Create thumbnail if required

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
		}
	}

	/**
	* Submit the attachments
	* Handles setting orphans, access level, deleting
	* @todo Deleting
	*
	* @param int $attachment_access Access level of the parent (to handle download permissions for the attachments)
	*/
	public function submit($attachment_access)
	{
		// Update the attachment comments if necessary
		foreach ($this->attachments as $attachment_id => $row)
		{
			$attachment_comment = utf8_normalize_nfc(request_var('attachment_comment_' . $attachment_id, '', true));
			if ($row['attachment_comment'] != $attachment_comment)
			{
				$sql = 'UPDATE ' . $this->sql_table . '
					SET attachment_comment = \'' . phpbb::$db->sql_escape($attachment_comment) . '\'
					WHERE attachment_id = ' . $attachment_id;
				phpbb::$db->sql_query($sql);
			}
		}

		// Update access and is_orphan
		$sql_ary = array(
			'attachment_access'	=> $attachment_access,
			'is_orphan'			=> 0,
		);

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE ' . phpbb::$db->sql_in_set('attachment_id', array_keys($this->attachments));
		phpbb::$db->sql_query($sql);
	}

	/**
	 * Removes file from server and database.
	 *
	 * @return void
	 */
	public function delete()
	{
		// @todo
	}

	/**
	 * Checks if the user is authorized to download this file.
	 */
	public function check_access()
	{
		// @todo This should be all we need to check, but maybe not...
		if ($this->attachment_access < titania::$access_level)
		{
			return false;
		}

		return true;
	}

	/**
	 * Triggers a 'download not found' message.
	 *
	 * @return void
	 */
	public function trigger_not_found()
	{
		titania::set_header_status(404);

		trigger_error('DOWNLOAD_NOT_FOUND');
	}

	/**
	 * Triggers a 'access denied' message.
	 *
	 * @return void
	 */
	public function trigger_forbidden()
	{
		titania::set_header_status(403);

		trigger_error('DOWNLOAD_ACCESS_DENIED');
	}

	/**
	 * Stream the download to the browser
	 *
	 * @return void
	 */
	public function stream()
	{
		if (headers_sent())
		{
			trigger_error('UNABLE_TO_DELIVER_FILE');
		}

		// Lets try to keep the lid on the jar - Kellanved
		if (isset($_SERVER['CONTENT_TYPE']))
		{
			if ($_SERVER['CONTENT_TYPE'] === 'application/x-java-archive')
			{
				exit;
			}
		}
		else if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Java') !== false)
		{
			exit;
		}

		$file = TITANIA_ROOT . 'files/' . $this->physical_filename;

		if (!@file_exists($file) || !@is_readable($file))
		{
			throw new FileNotFoundException();
		}

		if (!phpbb::$user->data['is_bot'])
		{
			$this->increase_counter();
		}

		header('Pragma: public');
		header('Content-Type: application/octet-stream');

		$size = ($this->filesize) ? $this->filesize : @filesize($file);
		if ($size)
		{
			header('Content-Length: ' . $size);
		}

		header('Content-Disposition: attachment; ' . $this->header_filename(htmlspecialchars_decode($this->real_filename)));

		// Try to deliver in chunks
		@set_time_limit(0);

		$fp = @fopen($file, 'rb');

		if ($fp !== false)
		{
			while (!feof($fp))
			{
				echo fread($fp, 8192);
			}
			fclose($fp);
		}
		else
		{
			@readfile($file);
		}

		flush();

		exit;
	}

	/**
	 * Get a browser friendly UTF-8 encoded filename
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	private function header_filename($file)
	{
		$user_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']) : '';

		// There be dragons here.
		// Not many follows the RFC...
		if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Safari') !== false || strpos($user_agent, 'Konqueror') !== false)
		{
			return 'filename=' . rawurlencode($file);
		}

		// Follow the RFC for extended filename for the rest
		return "filename*=UTF-8''" . rawurlencode($file);
	}
}