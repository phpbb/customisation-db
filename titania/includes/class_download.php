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
	require(TITANIA_ROOT . 'includes/class_base_db_object.' . PHP_EXT);
}

/**
* Class to abstract titania downloads.
* @package Titania
*/
class titania_download extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_DOWNLOADS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'download_id';

	/**
	 * Constructor for download class
	 *
	 * @param unknown_type $download_id
	 */
	public function __construct($download_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'download_id'			=> array('default' => 0),
			'revision_id'			=> array('default' => 0),

			'download_type'			=> array('default' => 0),
			'download_status'		=> array('default' => 0),

			'filesize'				=> array('default' => 0),
			'filetime'				=> array('default' => 0),

			'physical_filename'		=> array('default' => '',	'max' => 255),
			'real_filename'			=> array('default' => '',	'max' => 255),

			'download_count'		=> array('default' => 0),

			'extension'				=> array('default' => '',	'max' => 100),
			'mimetype'				=> array('default' => '',	'max' => 100),

			'download_url'			=> array('default' => '',	'max' => 255,	'multibyte' => false),
			'download_hash'			=> array('default' => '',	'max' => 32,	'readonly' => true),

			'thumbnail'				=> array('default' => 0),
		));

		if ($download_id === false)
		{
			$this->filetime = time();
		}
		else
		{
			$this->download_id = $download_id;
		}
	}

	/**
	 * Allows to load data identified by revision_id
	 *
	 * @param int $revision_id
	 *
	 * @return void
	 */
	public function load($revision_id = false)
	{
		if ($revision_id === false)
		{
			parent::load();
		}
		else
		{
			$identifier = $this->sql_id_field;

			$this->sql_id_field = 'revision_id';
			$this->revision_id = $revision_id;

			parent::load();

			$this->sql_id_field = $identifier;
		}
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
	* @param int $user_id
	* @return bool
	*/
	public function has_access($user_id)
	{
		// @todo
		return true;
	}

	/**
	* Stream the download to the browser
	*
	* @return void
	*/
	public function stream()
	{
		$file = TITANIA_ROOT . 'files/' . $this->physical_filename;

		if (headers_sent())
		{
			exit;
		}

		if (file_exists($file) && is_readable($file))
		{
			global $user;
			
			if (!$user->data['is_bot'])
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

			header('Content-Disposition: attachment; ' . $this->header_filename($this->real_filename) . '"');

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
		}
		else
		{
			header('HTTP/1.0 404 not found');
		}

		exit;
	}

	/**
	* Get a browser friendly UTF-8 encoded filename
	*
	* @param $file string
	* @return string
	*/
	private function header_filename($file)
	{
		$user_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']) : '';

		// There be dragons here.
		// Not many follows the RFC...
		if (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Safari') !== false || strpos($user_agent, 'Konqueror') !== false)
		{
			return "filename=" . rawurlencode($file);
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
		global $db;

		$sql = 'UPDATE ' . $this->sql_table . '
			SET download_count = download_count + 1
			WHERE download_id = ' . $this->download_id;
		$db->sql_query($sql);

		$this->download_count = $this->download_count + 1;
	}
}