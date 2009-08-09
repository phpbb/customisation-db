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
	 * Constructor for download class
	 *
	 * @param int $download_id
	 * @param object $contrib
	 * @param int $object_id
	 */
	public function __construct($type, $object_id = false)
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
			$this->filetime = titania::$time;
		}
		else
		{
			$this->object_id = $object_id;
			$this->load_object($type);
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
	 * @param int $download_type The type of download (check TITANIA_DOWNLOAD_ constants)
	 *
	 * @return void
	 */
	public function load_object()
	{
		// @todo This funtion should check the status of the relase as well as
		$this->sql_id_field = 'object_id';

		parent::load();

		$this->sql_id_field = 'attachment_id';
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
		$uploader = new titania_uploader('uploadify');

		// @todo Handle errors such as incorrect file extension.
		$filedata = $uploader->upload_file();

		if (sizeof($filedata['error']))
		{
			return $filedata;
		}

		$this->attachment_type		= TITANIA_DOWNLOAD_CONTRIB;
		$this->physical_filename	= $filedata['physical_filename'];
		$this->real_filename		= $filedata['real_filename'];
		$this->extension			= $filedata['extension'];
		$this->mimetype				= $filedata['mimetype'];
		$this->filesize				= $filedata['filesize'];
		$this->filetime				= $filedata['filetime'];
		$this->hash					= $filedata['md5_checksum'];
		$this->thumbnail			= 0; // @todo

		parent::submit();

		return true;
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

		// If there is no attachment data it means we only have one attachment so assign download information as well.
		if (!sizeof($this->attachment_data))
		{
			phpbb::$template->assign_vars(array(
				'U_DOWNLOAD'		=> append_sid('/' . titania::$config->titania_script_path . 'download/file.' . PHP_EXT, array('contrib_id' => $this->object_id)),

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