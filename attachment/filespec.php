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

class filespec extends \filespec
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param array $upload_ary
	 * @param \phpbb\titania\attachment\uploader $uploader
	 * @param \phpbb\user $user
	 * @param \phpbb\mimetype\guesser $mimetype_guesser
	 * @param \phpbb\plupload\plupload $plupload
	 */
	public function __construct($upload_ary, $uploader, \phpbb\user $user, \phpbb\mimetype\guesser $mimetype_guesser = null, \phpbb\plupload\plupload $plupload = null)
	{
		$this->user = $user;

		parent::filespec($upload_ary, $uploader, $mimetype_guesser, $plupload);
	}

	/**
	 * Move file to destination folder
	 *
	 * @param string $destination		Destination path, for example $config['avatar_path']
	 * @param bool $overwrite			If set to true, an already existing file will be overwritten
	 * @param bool $skip_image_check	Whether to skip check for valid image
	 * @param string|bool $chmod		Permission mask for chmodding the file after a successful move.
	 * 		The mode entered here reflects the mode defined by {@link phpbb_chmod()}
	 *
	 * @access public
	 * @return bool	Returns true on success, false on failure.
	 */
	function move_file($destination, $overwrite = false, $skip_image_check = false, $chmod = false)
	{
		if (sizeof($this->error))
		{
			return false;
		}

		$chmod = ($chmod === false) ? CHMOD_READ | CHMOD_WRITE : $chmod;

		// We need to trust the admin in specifying valid upload directories and an attacker not being able to overwrite it...
		$this->destination_path = $destination;

		// Check if the destination path exist...
		if (!file_exists($this->destination_path))
		{
			@unlink($this->filename);
			return false;
		}

		$upload_mode = (@ini_get('open_basedir') || @ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on') ? 'move' : 'copy';
		$upload_mode = ($this->local) ? 'local' : $upload_mode;
		$this->destination_file = $this->destination_path . '/' . basename($this->realname);

		// Check if the file already exist, else there is something wrong...
		if (file_exists($this->destination_file) && !$overwrite)
		{
			@unlink($this->filename);
		}
		else
		{
			if (file_exists($this->destination_file))
			{
				@unlink($this->destination_file);
			}

			switch ($upload_mode)
			{
				case 'copy':

					if (!@copy($this->filename, $this->destination_file))
					{
						if (!@move_uploaded_file($this->filename, $this->destination_file))
						{
							$this->error[] = $this->user->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
							return false;
						}
					}

					@unlink($this->filename);

					break;

				case 'move':

					if (!@move_uploaded_file($this->filename, $this->destination_file))
					{
						if (!@copy($this->filename, $this->destination_file))
						{
							$this->error[] = $this->user->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
							return false;
						}
					}

					@unlink($this->filename);

					break;

				case 'local':

					if (!@copy($this->filename, $this->destination_file))
					{
						$this->error[] = $this->user->lang($this->upload->error_prefix . 'GENERAL_UPLOAD_ERROR', $this->destination_file);
						return false;
					}
					@unlink($this->filename);

					break;
			}

			phpbb_chmod($this->destination_file, $chmod);
		}

		// Try to get real filesize from destination folder
		$this->filesize = (@filesize($this->destination_file)) ? @filesize($this->destination_file) : $this->filesize;

		// Get mimetype of supplied file
		$this->mimetype = $this->get_mimetype($this->destination_file);

		if ($this->is_image() && !$skip_image_check)
		{
			$this->width = $this->height = 0;

			if (($this->image_info = @getimagesize($this->destination_file)) !== false)
			{
				$this->width = $this->image_info[0];
				$this->height = $this->image_info[1];

				if (!empty($this->image_info['mime']))
				{
					$this->mimetype = $this->image_info['mime'];
				}

				// Check image type
				$types = $this->upload->image_types();

				if (!isset($types[$this->image_info[2]]) || !in_array($this->extension, $types[$this->image_info[2]]))
				{
					if (!isset($types[$this->image_info[2]]))
					{
						$this->error[] = $this->user->lang('IMAGE_FILETYPE_INVALID', $this->image_info[2], $this->mimetype);
					}
					else
					{
						$this->error[] = $this->user->lang('IMAGE_FILETYPE_MISMATCH', $types[$this->image_info[2]][0], $this->extension);
					}
				}

				// Make sure the dimensions match a valid image
				if (empty($this->width) || empty($this->height))
				{
					$this->error[] = $this->user->lang('ATTACHED_IMAGE_NOT_IMAGE');
				}
			}
			else
			{
				$this->error[] = $this->user->lang('UNABLE_GET_IMAGE_SIZE');
			}
		}

		$this->file_moved = true;
		$this->additional_checks();
		unset($this->upload);

		return true;
	}
}
