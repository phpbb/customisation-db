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
* @package Titania
*/
class titania_attachments extends titania_database_object
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
	 * Constructor for download class
	 *
	 * @param unknown_type $download_id
	 */
	public function __construct($type, $object_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attachment_id'			=> array('default' => 0),
			'attachment_type'		=> array('default' => 0),
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
		));

		if ($object_id === false)
		{
			$this->filetime = titania::$time;
		}
		else
		{
			$this->object_id = $object_id;
			$this->load_object($type, $object_id);
		}
	}

	/**
	 * Allows to load data identified by object_id
	 *
	 * @param int $download_type The type of download (check TITANIA_DOWNLOAD_ constants)
	 * @param int $object_id The id of the item to download
	 *
	 * @return void
	 */
	public function load_object($download_type, $object_id)
	{
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
		$attachment_id = (int) phpbb::$db->sql_fetchfield($column);
		phpbb::$db->sql_freeresult();

		if ($attachment_id)
		{
			$this->attachment_id = $attachment_id;
		}

		parent::load();
	}

	/**
	* Create a new download/upload
	*
	* @return void
	*/
	public function create()
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
		// @todo
		return;

		throw new DownloadAccessDeniedException();
	}

	/**
	* Triggers a 'download not found' message.
	*
	* @return void
	*/
	public function trigger_not_found()
	{
		header('HTTP/1.0 404 not found');

		trigger_error('DOWNLOAD_NOT_FOUND');
	}

	/**
	* Triggers a 'access denied' message.
	*
	* @return void
	*/
	public function trigger_forbidden()
	{
		header('HTTP/1.0 403 Forbidden');

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
			$name = 'Access denied.';
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