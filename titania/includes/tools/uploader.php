<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}


phpbb::_include('functions_upload', false, 'fileupload');

/**
 * Handles uploading attachments for Titania.
 */
class titania_uploader extends fileupload
{
	/**
	 * Some vars we need
	 *
	 * @var mix
	 */
	public $form_name = '';
	public $ext_group = '';

	/**
	 * Array of data holding informatiom about the file being uploaded.
	 *
	 * @var array
	 */
	public $filedata = array();

	/**
	 * Class constructor
	 *
	 * @param string $form_name Form name from where we can find the file.
	 * @param string $ext_group The extension type (use the same as what is in titania::$config->upload_allowed_extensions)
	 */
	public function __construct($form_name, $ext_group)
	{
		// Set class variables.
		$this->form_name = $form_name;
		$this->ext_group = $ext_group;
	}

	/**
	 * Uploads a file to server
	 *
	 * @return array filedata
	 */
	public function upload_file()
	{
		$this->filedata = array(
			'error'			=> array(),
			'post_attach'	=> $this->is_valid($this->form_name),
		);

		if (!$this->filedata['post_attach'])
		{
			$this->filedata['error'][] = phpbb::$user->lang['NO_UPLOAD_FORM_FOUND'];

			return false;
		}

		if (!isset(titania::$config->upload_allowed_extensions[$this->ext_group]))
		{
			$this->filedata['error'][] = phpbb::$user->lang['NO_UPLOAD_FORM_FOUND'];

			return false;
		}

		$this->set_allowed_extensions(titania::$config->upload_allowed_extensions[$this->ext_group]);

		$file = $this->form_upload($this->form_name);

		if ($file->init_error)
		{
			$this->filedata['post_attach'] = false;

			return false;
		}

		// Set max file size for anyone but team members.
		if (titania::$access_level != TITANIA_ACCESS_TEAMS)
		{
			if (isset(titania::$config->upload_max_filesize[$this->ext_group]))
			{
				$this->set_max_filesize(titania::$config->upload_max_filesize[$this->ext_group]);
			}
			else
			{
				$this->set_max_filesize(phpbb::$config['max_filesize']);
			}
		}

		$file->clean_filename('unique', phpbb::$user->data['user_id'] . '_');

		// Move files into their own directory depending on the extension group assigned.  Should keep at least some of it organized.
		if (!isset(titania::$config->upload_directory[$this->ext_group]))
		{
			$this->filedata['error'][] = phpbb::$user->lang['NO_UPLOAD_FORM_FOUND'];

			return false;
		}
		$move_dir = titania::$config->upload_directory[$this->ext_group];

		if (!file_exists(titania::$config->upload_path . $move_dir))
		{
			@mkdir(titania::$config->upload_path . $move_dir);
			phpbb_chmod(titania::$config->upload_path . $move_dir, CHMOD_ALL);
		}

		$file->move_file(titania::$config->upload_path . $move_dir, false, true);

		if (!empty($file->error))
		{
			$file->remove();
			$this->filedata['error'] = array_merge($this->filedata['error'], $file->error);
			$this->filedata['post_attach'] = false;

			return false;
		}

		$this->filedata['filesize'] = $file->get('filesize');
		$this->filedata['mimetype'] = $file->get('mimetype');
		$this->filedata['extension'] = $file->get('extension');
		$this->filedata['is_image'] = $file->is_image();
		$this->filedata['physical_filename'] = $file->get('realname');
		$this->filedata['attachment_directory'] = $move_dir;
		$this->filedata['real_filename'] = $file->get('uploadname');
		$this->filedata['filetime'] = time();
		$this->filedata['md5_checksum'] = md5_file($file->get('destination_file'));

		// Check free disk space
		if ($free_space = @disk_free_space(titania::$config->upload_path))
		{
			if ($free_space <= $file->get('filesize'))
			{
				$this->filedata['error'][] = phpbb::$user->lang['ATTACH_QUOTA_REACHED'];
				$this->filedata['post_attach'] = false;

				$file->remove();

				return false;
			}
		}

		// Yippe!! File uploaded with no problems...
		return true;
	}

	/**
	* Form upload method
	* Upload file from users harddisk
	*
	* Same method as one in functions_upload.php except using some different methods to allow us to place the file outside the phpbb_root_path
	*
	* @param string $form_name Form name assigned to the file input field (if it is an array, the key has to be specified)
	* @return object $file Object "filespec" is returned, all further operations can be done with this object
	* @access public
	*/
	public function form_upload($form_name)
	{
		unset($_FILES[$form_name]['local_mode']);
		$file = new titania_filespec($_FILES[$form_name], $this);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}

		// Error array filled?
		if (isset($_FILES[$form_name]['error']))
		{
			$error = $this->assign_internal_error($_FILES[$form_name]['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// Check if empty file got uploaded (not catched by is_uploaded_file)
		if (isset($_FILES[$form_name]['size']) && $_FILES[$form_name]['size'] == 0)
		{
			$file->error[] = phpbb::$user->lang[$this->error_prefix . 'EMPTY_FILEUPLOAD'];
			return $file;
		}

		// PHP Upload filesize exceeded
		if ($file->get('filename') == 'none')
		{
			$max_filesize = @ini_get('upload_max_filesize');
			$unit = 'MB';

			if (!empty($max_filesize))
			{
				$unit = strtolower(substr($max_filesize, -1, 1));
				$max_filesize = (int) $max_filesize;

				$unit = ($unit == 'k') ? 'KB' : (($unit == 'g') ? 'GB' : 'MB');
			}

			$file->error[] = (empty($max_filesize)) ? phpbb::$user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : sprintf(phpbb::$user->lang[$this->error_prefix . 'PHP_SIZE_OVERRUN'], $max_filesize, phpbb::$user->lang[$unit]);
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = phpbb::$user->lang[$this->error_prefix . 'NOT_UPLOADED'];
			return $file;
		}

		$this->common_checks($file);

		return $file;
	}
}
