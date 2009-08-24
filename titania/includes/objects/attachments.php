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
* Class to abstract titania downloads.
*
* @package Titania
*/
class titania_attachments extends titania_database_object
{
	/**
	 * Holds all the attachments for the loaded contrib.
	 *
	 * @var array
	 */
	public $attachment_data = array();

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
	 * Upload class
	 *
	 * @var object
	 */
	public $uploader;

	/**
	 * Constructor for download class
	 *
	 * @param int $attachment_type Attachment type (check TITANIA_DOWNLOAD_ for constants)
	 * @param object $object_id int
	 * @param int $object_id
	 */
	public function __construct($attachment_type, $object_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attachment_id'			=> array('default' => 0),
			'attachment_type'		=> array('default' => 0),
			'attachment_access'		=> array('default' => 0),
			'object_id'				=> array('default' => 0),

			'attachment_status'		=> array('default' => 0),
			'physical_filename'		=> array('default' => '',	'max' => 255),
			'real_filename'			=> array('default' => '',	'max' => 255),

			'download_count'		=> array('default' => 0),

			'filesize'				=> array('default' => 0),
			'filetime'				=> array('default' => 0),
			'extension'				=> array('default' => '',	'max' => 100),
			'mimetype'				=> array('default' => '',	'max' => 100),
			'hash'					=> array('default' => '',	'max' => 32,	'multibyte' => false,	'readonly' => true),

			'thumbnail'				=> array('default' => 0),
			'is_orphan'				=> array('default' => 1),
		));

		// Do we have an object that we need to load.
		if ($object_id === false)
		{
			$this->attachment_type = $attachment_type;
			$this->filetime = titania::$time;
		}
		else
		{
			$this->object_id = $object_id;
			$this->load_object($attachment_type);
		}

		// Assign common template data for uploader.
		$this->assign_common_template();

		// Get attachment data, we almost always need this info.
		$this->get_submitted_attachments();

		// Do we need to display attachments?
		if (sizeof($this->attachment_data))
		{
			$this->display_attachments();
		}
	}

	/**
	 * Allows to load data identified by object_id
	 *
	 * @param int $attachment_type The type of download (check TITANIA_DOWNLOAD_ constants)
	 *
	 * @return void
	 */
	public function load_object($attachment_type)
	{
		// @todo This funtion should check the status of the relase.

		// We build our own query here due to needing to check the attachment_type.
		// @todo Add suppport for more complex where statements in database object.
		$sql = 'SELECT ' .  implode(', ', array_keys($this->object_config)) . '
			FROM ' . $this->sql_table . '
			WHERE object_id = ' . $this->object_id . '
				AND attachment_type = ' . $attachment_type;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		$this->__set_array($row);
	}

	/**
	 * Gets the latest download data of a contribution
	 *
	 * @param int $contrib_id	The contrib_id of the contribution
	 * @param bool $validated	Latest (false) or latest validated version (true)
	 *
	 * @return void
	 */
	public function load_contrib($contrib_id, $validated = true)
	{
		$sql = 'SELECT attachment_id
			FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $contrib_id .
				(($validated) ? ' AND contrib_validated = 1' : '');
		phpbb::$db->sql_query($sql);
		$attachment_id = (int) phpbb::$db->sql_fetchfield('attachment_id');
		phpbb::$db->sql_freeresult();

		if ($attachment_id)
		{
			$this->attachment_id = $attachment_id;
		}
		else
		{
			return false;
		}

		parent::load();
	}

	/**
	 * Displays attachments
	 *
	 * Data is pulled from $this->attachment_data;
	 *
	 * @param bool $hide_attachement_detail If temlpate variable for hiding the attachment will be sent.
	 *
	 */
	public function display_attachments($hide_attachement_detail = true)
	{
		// Single attachment. This will happen if we are displaying a download for a contrib or a post.
		if (!sizeof($this->attachment_data) && !$this->attachment_id)
		{
			parent::load();

			$this->assign_template($hide_attachement_detail);
		}
		// Multiple attachments.
		else if (sizeof($this->attachment_data))
		{
			// Loop through all our attachments and display.
			foreach ($this->attachment_data as $attachment)
			{
				// Set object for attachment data and send to template.
				$this->__set_array($attachment);

				$this->assign_template($hide_attachement_detail);
			}
		}
		else
		{
			$this->assign_template();
		}

		return;
	}

	/**
	* Create a new attachment
	*
	* @return array $filedata
	*/
	public function create()
	{
		titania::load_tool('uploader');

		// Setup uploader tool.
		$this->uploader = new titania_uploader('uploadify');

		// Try uploading the file.
		$this->uploader->upload_file();

		// If we had no problems we can submit the data to the database.
		if (!sizeof($this->uploader->filedata['error']))
		{
			$this->physical_filename	= $this->uploader->filedata['physical_filename'];
			$this->real_filename		= $this->uploader->filedata['real_filename'];
			$this->extension			= $this->uploader->filedata['extension'];
			$this->mimetype				= $this->uploader->filedata['mimetype'];
			$this->filesize				= $this->uploader->filedata['filesize'];
			$this->filetime				= $this->uploader->filedata['filetime'];
			$this->hash					= $this->uploader->filedata['md5_checksum'];
			$this->thumbnail			= 0; // @todo

			parent::submit();
		}

		// Display results.
		$this->uploader->response($this);
	}

	/**
	 * Name explains it all.
	 *
	 * Gets all the attachments thave have been submitted.
	 *
	 */
	public function get_submitted_attachments()
	{
		$attachment_data = (isset($_POST['attachment_data'])) ? $_POST['attachment_data'] : array();

		if (!sizeof($attachment_data))
		{
			return;
		}
		$not_orphan = $orphan = array();

		foreach ($attachment_data as $pos => $var_ary)
		{
			if ($var_ary['is_orphan'])
			{
				$orphan[(int) $var_ary['attachment_id']] = $pos;
			}
			else
			{
				$not_orphan[(int) $var_ary['attachment_id']] = $pos;
			}
		}

		// Regenerate already posted attachments. This is for non-ajax.
		if (sizeof($not_orphan))
		{
			// Get the attachment data, based on the poster id...
			$sql = 'SELECT attachment_id, is_orphan, real_filename
				FROM ' . $this->sql_table . '
				WHERE ' . phpbb::$db->sql_in_set('attachment_id', array_keys($not_orphan));
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$pos = $not_orphan[$row['attachment_id']];
				$this->attachment_data[$pos] = $row;

				unset($not_orphan[$row['attachment_id']]);
			}
			phpbb::$db->sql_freeresult($result);
		}

		// Regenerate newly uploaded attachments.
		if (sizeof($orphan))
		{
			$sql = 'SELECT attachment_id, is_orphan, real_filename
				FROM ' . $this->sql_table . '
				WHERE ' . phpbb::$db->sql_in_set('attachment_id', array_keys($orphan)) . '
					AND is_orphan = 1';
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$pos = $orphan[$row['attachment_id']];
				$this->attachment_data[$pos] = $row;

				unset($orphan[$row['attachment_id']]);
			}
			phpbb::$db->sql_freeresult($result);
		}

		ksort($this->attachment_data);
	}

	/**
	 * Updates submmited orphan attachments to be assigned to the current object_id
	 *
	 * @param int $object_id Object id that attachment should be assigned to.
	 */
	public function update_orphans($object_id)
	{
		$this->object_id = $object_id;
		$attachment_ids = array();

		// Loop through attachments.
		foreach ($this->attachment_data as $attachment)
		{
			if ($attachment['is_orphan'])
			{
				$attachment_ids[] = $attachment['attachment_id'];
			}
		}

		// Do we need to update?
		if (sizeof($attachment_ids))
		{
			$data = array(
				'is_orphan'		=> 0,
				'object_id'		=> $this->object_id
			);

			$sql = 'UPDATE ' . $this->sql_table . '
				SET ' . phpbb::$db->sql_build_array('UPDATE', $data) . '
				WHERE ' . phpbb::$db->sql_in_set('attachment_id', $attachment_ids);
			phpbb::$db->sql_query($sql);
		}
	}

	/**
	 * Removes file from server and database.
	 *
	 * @return void
	 */
	public function delete()
	{
		// @todo

		// This function will need to handle contrib revisions.

		// @todo Have response for it.
	}

	/**
	* Checks if the user is authorized to download this file.
	*
	* @return void
	*/
	public function check_access()
	{
		// @todo This should be all we need to check, but maybe not...
		if ($this->attachment_access < titania::$access_level)
		{
			throw new DownloadAccessDeniedException();
		}
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
	 * Enter description here...
	 *
	 */
	private function assign_common_template()
	{
		phpbb::$template->assign_vars(array(
			'UPLOADER'		=> DIRECTORY_SEPARATOR . titania::$config->titania_script_path . 'js/uploadify/uploader.swf',
			'UPLOAD_SCRIPT'	=> DIRECTORY_SEPARATOR . append_sid(titania::$config->titania_script_path . 'upload.' . PHP_EXT),
		));
	}

	/**
	 * Assign template data for given attachment.
	 *
	 * @param unknown_type $attachment_id
	 */
	private function assign_template($hide_attachement_detail = false)
	{
		$hidden_fields = array(
			'attachment_data'	=> array(
				$this->attachment_id = array(
					'attachment_id'	=> $this->attachment_id,
					'is_orphan'		=> $this->is_orphan
				),
			),
		);

		phpbb::$template->assign_block_vars('attachment', array(
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($hidden_fields),
			'S_HIDE_ATTACHMENT'	=> $hide_attachement_detail,

			'ID'			=> $this->attachment_id,
			'REAL_NAME'		=> $this->real_filename,
			'NAME'			=> str_replace('.' . $this->extension, '', $this->real_filename),
			'EXT'			=> $this->extension,
			'DATE'			=> phpbb::$user->format_date($this->filetime),
			'MIMETYPE'		=> $this->mimetype,
			'TITLE'			=> $this->real_filename,
		));

		// If there is no attachment data and no uploader object, we only have one attachment so assign download information as well.
		if (!sizeof($this->attachment_data) && !$this->uploader)
		{
			phpbb::$template->assign_vars(array(
				// @todo Reformat link. Should have a method for building links for titania.
				'U_DOWNLOAD'		=> append_sid(DIRECTORY_SEPARATOR . titania::$config->titania_script_path . 'download/file.' . PHP_EXT, array('contrib_id' => $this->object_id)),

				'DOWNLOAD_SIZE'		=> get_formatted_filesize($this->filesize),
				'DOWNLOAD_CHECKSUM'	=> $this->hash,
			));
		}
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

	/**
	* Immediately increases the download counter of this download
	*
	* @return void
	*/
	private function increase_counter()
	{
		$sql = 'UPDATE ' . $this->sql_table . '
			SET download_count = download_count + 1
			WHERE attachment_id = ' . $this->attachment_id;
		phpbb::$db->sql_query($sql);

		$this->download_count = $this->download_count + 1;
	}
}

/**
* Exception thrown when a user is not allowed to access a download.
*
* @package Titania
*/
class DownloadAccessDeniedException extends Exception
{
	function __construct($message = '', $code = 0)
	{
		if (empty($message))
		{
			$message = 'Access denied.';
		}

		parent::__construct($message, $code);
	}
}

/**
* Exception thrown when a download file is not found or is not accessible.
*
* @package Titania
*/
class FileNotFoundException
{
	function __construct($message = '', $code = 0)
	{
		if (empty($message))
		{
			$message = 'File not found or not accessible.';
		}

		parent::__construct($message, $code);
	}
}