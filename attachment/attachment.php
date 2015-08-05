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

use phpbb\titania\access;

class attachment extends \phpbb\titania\entity\database_base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var string */
	protected $attachments_table;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_ATTACHMENTS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'attachment_id';

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\controller\helper $controller_helper
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\titania\config\config $ext_config, \phpbb\titania\controller\helper $controller_helper, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->ext_config = $ext_config;
		$this->controller_helper = $controller_helper;
		$this->attachments_table = $this->sql_table;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->user->add_lang('posting');
		$this->configure_properties();
	}

	/**
	 * Configure object properties.
	 */
	protected function configure_properties()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attachment_id'			=> array('default' => 0),
			'attachment_access'		=> array('default' => access::PUBLIC_LEVEL),
			'attachment_user_id'	=> array('default' => (int) $this->user->data['user_id']),
			'object_type'			=> array('default' => 0),
			'object_id'				=> array('default' => 0),

			'physical_filename'		=> array('default' => '',	'max' => 255),
			'attachment_directory'	=> array('default' => '',	'max' => 255),
			'real_filename'			=> array('default' => '',	'max' => 255),
			'attachment_comment'	=> array('default' => ''),

			'download_count'		=> array('default' => 0),

			'filesize'				=> array('default' => 0),
			'filetime'				=> array('default' => time()),
			'extension'				=> array('default' => '',	'max' => 100),
			'mimetype'				=> array('default' => '',	'max' => 100),
			'hash'					=> array('default' => '',	'max' => 32,	'multibyte' => false),

			'thumbnail'				=> array('default' => 0),
			'is_orphan'				=> array('default' => 1),
			'is_preview'			=> array('default' => 0),
			'attachment_order'		=> array('default' => 0),
		));
	}

	/**
	 * Get property.
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function get($property)
	{
		return $this->__get($property);
	}

	/**
	 * Set property value.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function set($property, $value)
	{
		$this->__set($property, $value);
	}

	/**
	 * Get attachment id.
	 *
	 * @return mixed
	 */
	public function get_id()
	{
		return (int) $this->get('attachment_id');
	}

	/**
	 * Get filename.
	 *
	 * @return string
	 */
	public function get_filename()
	{
		return utf8_basename($this->get('real_filename'));
	}

	/**
	 * Check whether the attachment is the given type.
	 *
	 * @param int $type
	 * @return bool
	 */
	public function is_type($type)
	{
		return $this->get('object_type') == $type;
	}

	/**
	 * Check whether the attachment is a preview.
	 *
	 * @return bool
	 */
	public function is_preview()
	{
		return (bool) $this->get('is_preview');
	}

	/**
	 * Check whether the attachment has a thumbnail.
	 *
	 * @return bool
	 */
	public function has_thumbnail()
	{
		return (bool) $this->get('thumbnail');
	}

	/**
	 * Submit the attachment.
	 * Handles setting orphans, access level, and comment
	 *
	 * @param array|null $data		Fields to update.
	 * @return $this
	 */
	public function submit($data = null)
	{
		if (!$this->get_id())
		{
			parent::submit();
			return $this;
		}

		if (!empty($data))
		{
			$sql = 'UPDATE ' . $this->sql_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE attachment_id = ' . $this->get_id();
			$this->db->sql_query($sql);
			$this->__set_array($data);
		}
		return $this;
	}

	/**
	 * Removes file from server and database.
	 *
	 * @return void
	 */
	public function delete()
	{
		@unlink($this->get_filepath());
		@unlink($this->get_filepath(true));
		parent::delete();
	}

	/**
	* Create a thumbnail
	* From functions_posting (modified to include option to specify max_width/min_width)
	*
	* @param bool|string $max_width		(Optional) Maximum width
	* @param bool|string $min_filesize 	(Optional) Minimum file size
	 *
	 * @return bool
	*/
	public function create_thumbnail($max_width = false, $min_filesize = false)
	{
		$source = $this->get_filepath();
		$destination = $this->get_filepath(true);
		$min_filesize = ($min_filesize !== false) ? $min_filesize : (int) $this->config['img_min_thumb_filesize'];
		$img_filesize = (file_exists($source)) ? @filesize($source) : false;

		if (!$img_filesize || $img_filesize <= $min_filesize)
		{
			return false;
		}

		$dimension = @getimagesize($source);

		if ($dimension === false)
		{
			return false;
		}

		list($width, $height, $type, ) = $dimension;

		if (empty($width) || empty($height))
		{
			return false;
		}

		list($new_width, $new_height) = $this->get_img_size_format($width, $height, $max_width);

		// Do not create a thumbnail if the resulting width/height is bigger than the original one
		if ($new_width >= $width && $new_height >= $height)
		{
			return false;
		}

		$used_imagick = false;

		// Only use imagemagick if defined and the passthru function not disabled
		if ($this->config['img_imagick'] && function_exists('passthru'))
		{
			if (substr($this->config['img_imagick'], -1) !== '/')
			{
				$this->config['img_imagick'] .= '/';
			}

			@passthru(escapeshellcmd($this->config['img_imagick']) . 'convert' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -geometry ' . $new_width . 'x' . $new_height . ' "' . str_replace('\\', '/', $source) . '" "' . str_replace('\\', '/', $destination) . '"');

			if (file_exists($destination))
			{
				$used_imagick = true;
			}
		}

		if (!$used_imagick)
		{
			if (!function_exists('get_supported_image_types'))
			{
				include($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
			}
			$type = get_supported_image_types($type);

			if ($type['gd'])
			{
				// If the type is not supported, we are not able to create a thumbnail
				if ($type['format'] === false)
				{
					return false;
				}

				switch ($type['format'])
				{
					case IMG_GIF:
						$image = @imagecreatefromgif($source);
					break;

					case IMG_JPG:
						@ini_set('gd.jpeg_ignore_warning', 1);
						$image = @imagecreatefromjpeg($source);
					break;

					case IMG_PNG:
						$image = @imagecreatefrompng($source);
					break;

					case IMG_WBMP:
						$image = @imagecreatefromwbmp($source);
					break;
				}

				if (empty($image))
				{
					return false;
				}

				if ($type['version'] == 1)
				{
					$new_image = imagecreate($new_width, $new_height);

					if ($new_image === false)
					{
						return false;
					}

					imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}
				else
				{
					$new_image = imagecreatetruecolor($new_width, $new_height);

					if ($new_image === false)
					{
						return false;
					}

					// Preserve alpha transparency (png for example)
					@imagealphablending($new_image, false);
					@imagesavealpha($new_image, true);

					imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}

				// If we are in safe mode create the destination file prior to using the gd functions to circumvent a PHP bug
				if (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on')
				{
					@touch($destination);
				}

				switch ($type['format'])
				{
					case IMG_GIF:
						imagegif($new_image, $destination);
					break;

					case IMG_JPG:
						imagejpeg($new_image, $destination, 90);
					break;

					case IMG_PNG:
						imagepng($new_image, $destination);
					break;

					case IMG_WBMP:
						imagewbmp($new_image, $destination);
					break;
				}

				imagedestroy($new_image);
			}
			else
			{
				return false;
			}
		}

		if (!file_exists($destination))
		{
			return false;
		}

		phpbb_chmod($destination, CHMOD_READ | CHMOD_WRITE);

		return true;
	}

	/**
	 * Calculate the needed size for Thumbnail
	 * From functions_posting (modified to include option to specify max_width)
	 *
	 * @param int $width			Width
	 * @param int $height			Height
	 * @param bool|int $max_width	(Optional) Maximum width
	 * @return array
	 */
	function get_img_size_format($width, $height, $max_width = false)
	{
		// Maximum Width the Image can take
		$max_width = ($max_width !== false) ? $max_width : (($this->config['img_max_thumb_width']) ?: 400);

		if ($width > $height)
		{
			return array(
				round($width * ($max_width / $width)),
				round($height * ($max_width / $width))
			);
		}
		else
		{
			return array(
				round($width * ($max_width / $height)),
				round($height * ($max_width / $height))
			);
		}
	}

	/**
	* Change the realname of an existing attachment
	*
	* @param string $real_filename New filename
	*/
	public function change_real_filename($real_filename)
	{
		$sql = 'UPDATE ' . $this->attachments_table . '
			SET real_filename = "' . $this->db->sql_escape($real_filename) . '"
			WHERE attachment_id = ' . $this->get_id();
		$this->db->sql_query($sql);
	}

	/**
	* Get attachment download URL.
	*
	* @param array $parameters			Optional parameters to add to the URL.
	* @return string Returns generated URL.
	*/
	public function get_url($parameters = array())
	{
		$parameters += array(
			'id'	=> $this->get_id(),
		);

		return $this->controller_helper->route('phpbb.titania.download', $parameters);
	}

	/**
	* Get full path to an attachment's file.
	*
	* @param bool $thumb				Whether to provide the path to the thumbnail.
	* @return string
	*/
	public function get_filepath($thumb = false)
	{
		$prefix = ($thumb) ? 'thumb_' : '';

		return $this->ext_config->__get('upload_path') . '/' .
			utf8_basename($this->get('attachment_directory')) . '/' .
			$prefix .
			utf8_basename($this->get('physical_filename'));
	}

	/**
	 * Get template vars for displaying the attachment.
	 *
	 * @param string $comment		Attachment comment from request.
	 * @return array
	 */
	public function get_display_vars($comment)
	{
		$filesize = get_formatted_filesize($this->get('filesize'), false);
		$comment = bbcode_nl2br(censor_text($comment));

		$vars = array(
			'FILESIZE'			=> $filesize['value'],
			'SIZE_LANG'			=> $filesize['unit'],
			'DOWNLOAD_NAME'		=> $this->get_filename(),
			'COMMENT'			=> $comment,
		);
		$display_cat = $this->get_display_category();

		if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
		{
			$vars += $this->get_image_vars();
		}
		else if ($display_cat == ATTACHMENT_CATEGORY_THUMB)
		{
			$vars += $this->get_thumb_vars();
		}
		else
		{
			$vars += $this->get_default_file_vars();
		}

		$l_download_count = (int) (empty($attachment['download_count'])) ? 0 : $attachment['download_count'];
		$vars['L_DOWNLOAD_COUNT'] = $this->user->lang($vars['L_DOWNLOAD_COUNT'], $l_download_count);

		return $vars;
	}

	/**
	 * Get template vars for an image.
	 *
	 * @return array
	 */
	protected function get_image_vars()
	{
		$download_link = ($this->has_thumbnail()) ? $this->get_url(array('mode' => 'view')) : $this->get_url();

		return array(
			'U_DOWNLOAD_LINK'	=> $download_link,
			'L_DOWNLOAD_COUNT'	=> 'VIEWED_COUNTS',
			'S_IMAGE'			=> true,
			'U_INLINE_LINK'		=> $download_link,
			'IS_PREVIEW'		=> $this->is_preview(),
		);
	}

	/**
	 * Get template vars for a thumbnail.
	 *
	 * @return array
	 */
	protected function get_thumb_vars()
	{
		$download_link = $this->get_url(array('mode' => 'view'));
		$thumb_download_link = $this->get_url(array(
			'mode' 	=> 'view',
			'thumb'	=> 1,
		));

		return array(
			'U_DOWNLOAD_LINK'	=> $download_link,
			'L_DOWNLOAD_COUNT'	=> 'VIEWED_COUNTS',
			'S_THUMBNAIL'		=> true,
			'THUMB_IMAGE'		=> $thumb_download_link,
			'IS_PREVIEW'		=> $this->is_preview(),
		);
	}

	/**
	 * Get default template vars.
	 *
	 * @return array
	 */
	protected function get_default_file_vars()
	{
		return array(
			'U_DOWNLOAD_LINK'	=> $this->get_url(),
			'L_DOWNLOAD_COUNT'	=> 'DOWNLOAD_COUNTS',
			'S_FILE'			=> true,
		);
	}

	/**
	 * Get the attachment's display category.
	 *
	 * @return int	Returns ATTACHMENT_CATEGORY_NONE|ATTACHMENT_CATEGORY_IMAGE|ATTACHMENT_CATEGORY_THUMB
	 */
	protected function get_display_category()
	{
		$image = ATTACHMENT_CATEGORY_IMAGE;
		$none = ATTACHMENT_CATEGORY_NONE;
		$thumb = ATTACHMENT_CATEGORY_THUMB;

		$display_cat = (strpos($this->get('mimetype'), 'image') === 0) ? $image : $none;

		if ($display_cat == $image)
		{
			if ($this->get('thumbnail'))
			{
				$display_cat = $thumb;
			}
			else
			{
				if ($this->config['img_display_inlined'])
				{
					if ($this->config['img_link_width'] || $this->config['img_link_height'])
					{
						$dimension = @getimagesize($this->get_filepath());

						// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
						if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
						{
							$display_cat = $none;
						}
						else
						{
							$display_cat = ($dimension[0] <= $this->config['img_link_width'] && $dimension[1] <= $this->config['img_link_height']) ? $image : $none;
						}
					}
				}
				else
				{
					$display_cat = $none;
				}
			}
		}

		// Make some decisions based on user options being set.
		if (($display_cat == $image || $display_cat == $thumb) && !$this->user->optionget('viewimg'))
		{
			$display_cat = $none;
		}
		return $display_cat;
	}

	/**
	 * Set the attachment as the object preview.
	 *
	 * @return bool
	 */
	public function set_preview()
	{
		if (!$this->is_type(TITANIA_SCREENSHOT))
		{
			return false;
		}

		if (!$this->is_preview())
		{
			$sql = 'UPDATE ' . $this->attachments_table . '
				SET is_preview = 0
				WHERE object_type = ' . (int) $this->get('object_type') . '
					AND object_id = ' . (int) $this->get('object_id');
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->attachments_table . '
				SET is_preview = 1
				WHERE attachment_id = ' . (int) $this->get_id();
			$this->db->sql_query($sql);
			$this->set('is_preview', 1);
		}
		return true;
	}
}
