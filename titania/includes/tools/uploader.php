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


phpbb::_include('functions_upload', false, 'fileupload');

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
	 * @var Template file that displays uploaded attachments
	 */
	private $upload_file_tpl = '';

	/**
	 * Class constructor
	 *
	 * @param string $form_name Form name from where we can find the file.
	 * @param string $file_type Type of file, used for allowed extenstions. @todo
	 * @param string $upload_file_tpl Template file that displays uploaded attachments
	 */
	public function __construct($form_name = 'uploadify', $file_type = 'contrib', $upload_file_tpl = false, $reponse_type = TITANIA_UPLOAD_RESPONSE_HTML)
	{
		// Set class variables.
		$this->form_name 		= $form_name;
		$this->file_type		= $file_type;
		$this->file_attachment 	= ($this->is_valid($this->form_name)) ? true : false;
		$this->upload_file_tpl 	= $upload_file_tpl;

		// Are we in a ajax request? @todo May not need this. Think we should only support AJAX IMO
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
		// Set header for JSON response.
		header('Content-type: application/json');

		// We dont want the page_header to run.
		define('HEADER_INC', true);

		// Set up the template.
		phpbb::$template->set_filenames(array(
			'body'		=> 'common/json_response.html',
		));

		// Do we have any errors?
		if (!empty($this->filedata['error']))
		{
			// Upload error.
			$response = array(
				'error'	=> implode('<br />', $this->filedata['error']),
			);

			phpbb::$template->assign_var('JSON', json_encode($response));
		}
		else if ($this->filedata['post_attach'])
		{
			// Add any language files we need.
			// Since we cant tell the script what to invlude, we have to find out ourself
			if ($attachment->attachment_type == TITANIA_DOWNLOAD_CONTRIB)
			{
				titania::add_lang('revisions');
			}
			else if ($attachment->attachment_type == TITANIA_DOWNLOAD_FAQ)
			{
				titania::add_lang('faq');
			}
			else if ($attachment->attachment_type == TITANIA_DOWNLOAD_POST)
			{
				titania::add_lang('posting');
			}

			$file = '';
			if (file_exists(TITANIA_ROOT . 'styles/' . titania::$config->style . '/template/uploadify/uploadify_' . $attachment->object_type . '_file.html'))
			{
				$file = 'uploadify/uploadify_' . $attachment->object_type . '_file.html';
			}
			else
			{
				$file = 'uploadify/uploadify_file.html';
			}

			// Set template file.
			phpbb::$template->set_filenames(array(
				'file'		=> $file
			));

			// Display attachment info
			$attachment->display();

			// We uploaded a file successfully, now send response
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

		// Call page header and footer.
		titania::page_header();
		titania::page_footer(false);
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