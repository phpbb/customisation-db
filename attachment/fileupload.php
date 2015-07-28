<?php
/**
 *
 * This file is part of the phpBB Customisation Database package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\titania\attachment;

use phpbb\request\request_interface;

class fileupload extends \fileupload
{
	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var bool */
	protected $use_plupload = false;

	/**
	 * Constructor
	 *
	 * @param request_interface $request
	 * @param \phpbb\user $user
	 * @param plupload $plupload
	 */
	public function __construct(request_interface $request, \phpbb\user $user, plupload $plupload)
	{
		$this->request = $request;
		$this->user = $user;
		$this->plupload = $plupload;
	}

	/**
	 * Configure
	 *
	 * @param bool $use_plupload	Whether to use Plupload to upload the file.
	 */
	public function configure($use_plupload)
	{
		$this->use_plupload = $use_plupload;
	}

	/**
	 * Form upload method
	 * Upload file from users harddisk
	 *
	 * Same method as one in functions_upload.php except using some different methods to allow us to place the file outside the phpbb_root_path
	 *
	 * @param string $form_name Form name assigned to the file input field (if it is an array, the key has to be specified)
	 * @param \phpbb\mimetype\guesser $mimetype_guesser Mimetype guesser
	 * @param \phpbb\plupload\plupload $plupload The plupload object
	 *
	 * @return \phpbb\titania\attachment\filespec Object "filespec" is returned, all further operations can be done with this object
	 * @access public
	 */
	public function form_upload($form_name, \phpbb\mimetype\guesser $mimetype_guesser = null, \phpbb\plupload\plupload $plupload = null)
	{
		$upload = $this->request->file($form_name);
		unset($upload['local_mode']);
		$this->request->overwrite($form_name, $upload, request_interface::FILES);

		if ($this->use_plupload)
		{
			$result = $this->plupload->handle_upload($form_name);
			if (is_array($result))
			{
				$upload = array_merge($upload, $result);
			}
		}

		$file = new filespec($upload, $this, $this->user, $mimetype_guesser, $this->plupload);

		if ($file->init_error)
		{
			$file->error[] = '';
			return $file;
		}

		// Error array filled?
		if (isset($upload['error']))
		{
			$error = $this->assign_internal_error($upload['error']);

			if ($error !== false)
			{
				$file->error[] = $error;
				return $file;
			}
		}

		// Check if empty file got uploaded (not catched by is_uploaded_file)
		if (isset($upload['size']) && $upload['size'] == 0)
		{
			$file->error[] = $this->user->lang[$this->error_prefix . 'EMPTY_FILEUPLOAD'];
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

			$file->error[] = (empty($max_filesize)) ? $this->user->lang[$this->error_prefix . 'PHP_SIZE_NA'] : $this->user->lang($this->error_prefix . 'PHP_SIZE_OVERRUN', $max_filesize, $this->user->lang[$unit]);
			return $file;
		}

		// Not correctly uploaded
		if (!$file->is_uploaded())
		{
			$file->error[] = $this->user->lang($this->error_prefix . 'NOT_UPLOADED');
			return $file;
		}

		$this->common_checks($file);

		return $file;
	}
}
