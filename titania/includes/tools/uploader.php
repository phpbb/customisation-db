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

if (!class_exists('fileupload'))
{
	include PHPBB_ROOT_PATH . 'includes/functions_upload.' . PHP_EXT;
}

/**
 * Handles uploading attachments for Titania.
 *
 * Still needs a lot of work done.
 *
 * @todo Resize all images.
 */
class titania_uploader extends fileupload
{
	/**
	 * Set some defualt class variables that we need.
	 *
	 * @var mix
	 */
	public $form_name = '';

	/**
	 * Array of data holding informatiom about the file being uploaded.
	 *
	 * @var array
	 */
	public $filedata = array();

	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	private $object_id = 0;

	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	private $file_type = '';

	/**
	 * Is this an ajax call?
	 *
	 * @var bool
	 */
	private $ajax = true;

	/**
	 * Class constructor
	 *
	 * @param string $form_name Form name from where we can find the file.
	 * @param int $object_id The contribution id which the attachment belongs to, if 0, the attachment will be an orphan and the
	 * attachment record in the DB should be updated once the contrib_id is known.
	 */
	public function __construct($form_name = 'uploadify', $object_id = 0, $file_type = 'contrib')
	{
		// Set class variables.
		$this->form_name = $form_name;
		$this->object_id = $object_id;
		$this->file_type = $file_type;
		$this->file_attachment = ($this->is_valid($this->form_name)) ? true : false;

		// Are we in a ajax request?
		$this->ajax = request_var('ajax', true);

		// Add posting language for the attachment language strings.
		phpbb::$user->add_lang('posting');
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
			'post_attach'	=> $this->file_attachment,
		);

		if (!$this->filedata['post_attach'])
		{
			$this->filedata['error'][] = phpbb::$user->lang['NO_UPLOAD_FORM_FOUND'];

			return false;
		}

		// @todo Allow more dynamic extensions
		$extensions = titania::$cache->obtain_attach_extensions();
		$this->set_allowed_extensions(array_keys($extensions['_allowed_' . $this->file_type]));

		$file = $this->form_upload($this->form_name);

		if ($file->init_error)
		{
			$this->filedata['post_attach'] = false;

			return false;
		}

		// @todo Support attachment categories

		// Set max file size for anyone but team members.
		if (titania::$access_level != TITANIA_ACCESS_TEAMS)
		{
			$this->set_max_filesize(phpbb::$config['max_filesize']);
		}

		$file->clean_filename('unique', phpbb::$user->data['user_id'] . '_');

		// @todo config for Titania upload path.
		$file->move_file(TITANIA_ROOT . 'files/', false, true);

		if (sizeof($file->error))
		{
			$file->remove();
			$this->filedata['error'] = array_merge($this->filedata['error'], $file->error);
			$this->filedata['post_attach'] = false;

			return false;
		}

		$this->filedata['filesize'] = $file->get('filesize');
		$this->filedata['mimetype'] = $file->get('mimetype');
		$this->filedata['extension'] = $file->get('extension');
		$this->filedata['physical_filename'] = $file->get('realname');
		$this->filedata['real_filename'] = $file->get('uploadname');
		$this->filedata['filetime'] = time();
		$this->filedata['md5_checksum'] = md5_file($file->get('destination_file'));

		// Check our complete quota
		//@todo Seperate config for titania attachments
		if (phpbb::$config['attachment_quota'])
		{
			if (phpbb::$config['upload_dir_size'] + $file->get('filesize') > phpbb::$config['attachment_quota'])
			{
				$this->filedata['error'][] = phpbb::$user->lang['ATTACH_QUOTA_REACHED'];
				$this->filedata['post_attach'] = false;

				$file->remove();

				var_dump($this->filedata);

				return false;
			}
		}

		// Check free disk space
		if ($free_space = @disk_free_space(PHPBB_ROOT_PATH . phpbb::$config['upload_path']))
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
	 * Displays results of upload.
	 *
	 * Handles displaying errors and generating newly uploaded file data.
	 *
	 * @vat object $attachment Attachment object.
	 */
	public function response($attachment)
	{
		if ($this->ajax)
		{
			// Set header for JSON response.
			header('Content-type: application/json');

			// We dont want the page_header to run.
			define('HEADER_INC', true);

			// Set up the template.
			phpbb::$template->set_filenames(array(
				'body'		=> 'json_response.html',
			));
		}

		if (sizeof($this->filedata['error']))
		{
			// Upload error.
			$response = array(
				'error'	=> implode('<br />', $this->filedata['error']),
			);

			phpbb::$template->assign_var('JSON', json_encode($response));
		}
		else if ($this->filedata['post_attach'])
		{
			phpbb::$template->set_filenames(array(
				'file'		=> 'uploadify_file.html'
			));

			$attachment->display_attachments();

			// We uploaded a file successfully.
			$response = array(
				'html'	=> phpbb::$template->assign_display('file'),
				'id'	=> $attachment->attachment_id,
			);

			phpbb::$template->assign_var('JSON', json_encode($response));
		}
		else
		{
			// @todo Something is wrong, just display general error.
		}

		titania::page_header();

		titania::page_footer();
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
		// Load our own file spec tool that expands the phpBB default version.
		titania::load_tool('filespec');

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