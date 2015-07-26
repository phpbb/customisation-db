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
use phpbb\titania\access;

class uploader
{
	/** @var \phpbb\titania\attachment\operator */
	protected $operator;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var  \phpbb\mimetype\guesser_interface */
	protected $mimetype_guesser;

	/** @var \phpbb\titania\attachment\plupload */
	protected $plupload;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var bool */
	protected $use_plupload = false;

	/** @var array */
	protected $filedata = array();

	/** @var string */
	protected $form_name;

	/** @var int */
	protected $object_type;

	/** @var int */
	protected $object_id;

	/** @var int */
	protected $max_thumbnail_width;

	/** @var bool */
	protected $set_custom_order;

	/** @var array */
	protected $errors = array();

	/** @var array */
	public $deleted = array();

	/** @var int */
	public $uploaded;

	/**
	 * Constructor
	 *
	 * @param operator $operator
	 * @param \phpbb\user $user
	 * @param \phpbb\config\config $config
	 * @param request_interface $request
	 * @param \phpbb\template\template $template
	 * @param \phpbb\path_helper $path_helper
	 * @param \phpbb\controller\helper $controller_helper
	 * @param \phpbb\titania\config\config $ext_config
	 * @param $mimetype_guesser
	 * @param plupload $plupload
	 * @param access $access
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(operator $operator, \phpbb\user $user, \phpbb\config\config $config, request_interface $request, \phpbb\template\template $template, \phpbb\path_helper $path_helper, \phpbb\controller\helper $controller_helper, \phpbb\titania\config\config $ext_config, $mimetype_guesser, plupload $plupload, access $access, $phpbb_root_path, $php_ext)
	{
		$this->operator = $operator;
		$this->user = $user;
		$this->config = $config;
		$this->request = $request;
		$this->template = $template;
		$this->path_helper = $path_helper;
		$this->controller_helper = $controller_helper;
		$this->ext_config = $ext_config;
		$this->mimetype_guesser = $mimetype_guesser;
		$this->plupload = $plupload;
		$this->access = $access;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Configure uploader
	 *
	 * @param int $object_type				Parent object type
	 * @param int $object_id				Parent object id
	 * @param bool $use_plupload			Whether to use Plupload
	 * @param bool|int $max_thumbnail_width	(Optional) Max thumbnail width
	 * @param bool $set_custom_order		Whether set supports custom order
	 * @return $this
	 */
	public function configure($object_type, $object_id, $use_plupload = false, $max_thumbnail_width = false, $set_custom_order = false)
	{
		// Set class variables.
		$this->object_type = (int) $object_type;
		$this->object_id = (int) $object_id;
		$this->use_plupload = $use_plupload;
		$this->form_name = 'titania_attachment_' . $this->object_type . '_' . $this->object_id;
		$this->max_thumbnail_width = $max_thumbnail_width;
		$this->set_custom_order = $set_custom_order;
		$upload_dir = $this->ext_config->upload_path . $this->ext_config->upload_directory[$this->object_type] . '/';

		if ($this->use_plupload)
		{
			$this->plupload->set_upload_directories($upload_dir, $upload_dir . 'plupload');
			$this->plupload->configure_ext(
				$this->ext_config,
				$this->template,
				$this->controller_helper->get_current_url(),
				$this->object_type,
				0,
				$this->get_max_filesize()
			);
		}
		$this->operator->configure($this->object_type, $this->object_id);

		return $this;
	}

	/**
	 * Get parent object type.
	 *
	 * @return int
	 */
	public function get_object_type()
	{
		return $this->object_type;
	}

	/**
	 * Set parent object id.
	 *
	 * @param int $id
	 * @return $this
	 */
	public function set_object_id($id)
	{
		$this->object_id = (int) $id;
		return $this;
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * Clear errors.
	 *
	 * @return $this
	 */
	public function clear_errors()
	{
		$this->errors = array();
		return $this;
	}

	/**
	 * Get attachment operator
	 *
	 * @return operator
	 */
	public function get_operator()
	{
		return $this->operator;
	}

	/**
	 * Get uploaded attachment.
	 *
	 * @return attachment|null
	 */
	public function get_uploaded_attachment()
	{
		return ($this->uploaded) ? $this->operator->get($this->uploaded) : null;
	}

	/**
	 * Uploads a file to server
	 *
	 * @return array filedata
	 */
	public function upload_file()
	{
		if (!class_exists('\fileupload'))
		{
			require($this->phpbb_root_path . 'includes/functions_upload.' . $this->php_ext);
		}

		$upload = new fileupload($this->request, $this->user, $this->plupload);
		$upload->configure($this->use_plupload);
		$this->filedata = array(
			'error'			=> array(),
			'post_attach'	=> $upload->is_valid($this->form_name),
		);

		if (!$this->filedata['post_attach'])
		{
			$this->filedata['error'][] = $this->user->lang['NO_UPLOAD_FORM_FOUND'];

			return false;
		}

		if (!isset($this->ext_config->upload_allowed_extensions[$this->object_type]))
		{
			$this->filedata['error'][] = $this->user->lang['NO_UPLOAD_FORM_FOUND'];

			return false;
		}

		$upload->set_allowed_extensions($this->ext_config->upload_allowed_extensions[$this->object_type]);
		$file = $upload->form_upload($this->form_name, $this->mimetype_guesser);

		if ($file->init_error)
		{
			$this->filedata['post_attach'] = false;

			return false;
		}

		// Set max file size for anyone but team members.
		if (!$this->access->is_team())
		{
			$upload->set_max_filesize($this->get_max_filesize());
		}

		$file->clean_filename('unique', $this->user->data['user_id'] . '_');

		// Move files into their own directory depending on the extension group assigned.  Should keep at least some of it organized.
		if (!isset($this->ext_config->upload_directory[$this->object_type]))
		{
			$this->filedata['error'][] = $this->user->lang('NO_UPLOAD_FORM_FOUND');

			return false;
		}
		$move_dir = $this->ext_config->upload_directory[$this->object_type];

		if (!file_exists($this->ext_config->upload_path . $move_dir))
		{
			@mkdir($this->ext_config->upload_path . $move_dir);
			phpbb_chmod($this->ext_config->upload_path . $move_dir, CHMOD_ALL);
		}

		$file->move_file($this->ext_config->upload_path . $move_dir, false, true);

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
		if ($free_space = @disk_free_space($this->ext_config->upload_path))
		{
			if ($free_space <= $file->get('filesize'))
			{
				$this->filedata['error'][] = $this->user->lang['ATTACH_QUOTA_REACHED'];
				$this->filedata['post_attach'] = false;

				$file->remove();

				return false;
			}
		}

		// Yippe!! File uploaded with no problems...
		return true;
	}

	/**
	 * Parse the uploader
	 *
	 * @param string $tpl_file The name of the template file to use to create the uploader
	 * @param bool $custom_sort Function used to sort the attachments
	 * @return string The parsed HTML code ready for output
	 */
	public function parse_uploader($tpl_file = 'posting/attachments/default.html', $custom_sort = false)
	{
		// If the upload max filesize is less than 0, do not show the uploader (0 = unlimited)
		if (!$this->access->is_team())
		{
			if (isset($this->ext_config->upload_max_filesize[$this->object_type]) && $this->ext_config->upload_max_filesize[$this->object_type] < 0)
			{
				return '';
			}
		}

		$this->template->assign_vars(array(
			'FORM_NAME'			=> $this->form_name,
			'MAX_LENGTH'		=> ($this->access->is_team()) ? $this->config['max_filesize'] : false,

			// Make sure the form type is correct...doing it here just in case someone leaves {S_FORM_ENCTYPE} and forgets about it
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',

			'S_INLINE_ATTACHMENT_OPTIONS'	=> true,
			'S_PLUPLOAD_ENABLED'			=> $this->use_plupload,
			'S_SET_CUSTOM_ORDER'			=> $this->set_custom_order,
			'SELECT_PREVIEW'				=> $this->object_type == TITANIA_SCREENSHOT,
			'SELECT_REVIEW_VAR' 			=> 'set_preview_file' . $this->object_type,
		));

		$index_dir = '-';
		$index = $this->operator->get_count() - 1;

		if ($custom_sort == false && !$this->config['display_order'])
		{
			$index_dir = '+';
			$index = 0;
		}
		$this->operator->sort($custom_sort);

		// Delete previous attachments list
		$this->template->destroy_block_vars('attach_row');
		$base_url = $this->controller_helper->get_current_url();
		$hash = generate_link_hash('attach_manage');
		$comments = $this->get_request_comments();
		$hidden_data = $this->get_basic_attachment_data();
		$index_prefix = ($this->use_plupload) ? '' : $this->form_name . '_';

		foreach ($this->operator->get_all() as $attachment_id => $attach)
		{
			$params = array(
				'a'		=> $attachment_id,
				'hash'	=> $hash,
			);
			$_hidden_data = array();

			foreach ($hidden_data[$attachment_id] as $property => $value)
			{
				$_hidden_data["attachment_data[$index_prefix$index][$property]"] = $value;
			}
			$output = array_merge(array(
				'FILENAME'			=> $attach->get_filename(),
				'FILE_COMMENT'		=> (isset($comments[$attachment_id])) ? $comments[$attachment_id] : $attach->get('attachment_comment'),
				'ATTACH_ID'			=> $attachment_id,
				'INDEX'				=> $index_prefix . $index,
				'FILESIZE'			=> get_formatted_filesize($attach->get('filesize')),

				'S_HIDDEN'			=> build_hidden_fields($_hidden_data),
				'S_PREVIEW'			=> $attach->is_preview(),
				'U_VIEW_ATTACHMENT'	=> $attach->get_url(),
				'U_DELETE'			=> $this->path_helper->append_url_params(
						$base_url,
						array_merge($params, array('action' => 'delete_attach'))
					),
			), $attach->get_display_vars(''));

			if ($attach->is_type(TITANIA_SCREENSHOT))
			{
				$output = array_merge($output, array(
					'U_MOVE_UP'		=> $this->path_helper->append_url_params(
							$base_url,
							array_merge($params, array('action' => 'attach_up'))
						),
					'U_MOVE_DOWN'	=> $this->path_helper->append_url_params(
							$base_url,
							array_merge($params, array('action' => 'attach_down'))
						),
				));
			}
			$index += (($index_dir == '+') ? 1 : -1);

			$this->template->assign_block_vars('attach_row', $output);
		}
		$this->template->assign_var(
			'S_ATTACH_DATA',
			json_encode(array_values($hidden_data))
		);

		$this->template->set_filenames(array(
			$tpl_file	=> $tpl_file,
		));

		return $this->template->assign_display($tpl_file);
	}

	/**
	 * Get attachment data from request.
	 *
	 * @return array
	 */
	public function get_filtered_request_data()
	{
		$attachments = $this->request->variable('attachment_data', array('' => array('' => '')));
		$comments = $this->request->variable('comment_list', array('' => ''), true);
		$filtered_data = array();

		foreach ($attachments as $index => $data)
		{
			if (!isset($data['attach_id']) || $data['type'] != $this->form_name)
			{
				continue;
			}
			$data['comment'] = (isset($comments[$index])) ? $comments[$index] : '';
			$filtered_data[(int) $data['attach_id']] = $data;
		}
		return $filtered_data;
	}

	/**
	 * Get attachment comments from request.
	 *
	 * @return array
	 */
	public function get_request_comments()
	{
		$comments = array();
		$attachments = $this->get_filtered_request_data();

		foreach ($attachments as $id => $data)
		{
			$comments[$id] = $data['comment'];
		}
		return $comments;
	}

	/**
	 * Handle any upload/deletion requests.
	 */
	public function handle_form_action()
	{
		// First, we shall handle the items already attached
		$attachments = $this->get_filtered_request_data();
		$attached_ids = array_keys($attachments);
		// Query the ones we must
		$to_query = array_diff($attached_ids, $this->operator->get_all_ids());

		if (!empty($to_query))
		{
			$this->operator->load($to_query, true);
		}
		$plupload_key = $this->request->header('X-PLUPLOAD_KEY', '');

		// Do not perform any Plupload actions without a valid key.
		if ($this->plupload_active() && !check_link_hash($plupload_key, 'plupload_key'))
		{
			return;
		}

		// Next, delete those requested
		$delete_indices = $this->request->variable('delete_file', array(0 => 0));

		if ($delete_indices)
		{
			$this->handle_delete($delete_indices);
		}

		// set requested attachment as preview
		$preview = $this->request->variable('set_preview_file' . $this->object_type, 0);
		if ($preview)
		{
			$this->operator->set_preview($preview);
		}

		if ($this->request->is_set($this->form_name, request_interface::FILES))
		{
			$this->handle_upload();
		}
	}

	/**
	 * Handle attachment deletion.
	 *
	 * @param array $indices	Attachment indices
	 */
	protected function handle_delete(array $indices)
	{
		$valid_indices = $this->operator->get_fixed_indices();
		$ids = array();

		foreach ($indices as $index => $null)
		{
			if (isset($valid_indices[$index]))
			{
				$ids[] = $valid_indices[$index];
			}
		}

		if ($ids)
		{
			$this->operator->delete($ids);
			$this->deleted = array_merge($this->deleted, $ids);
		}
	}

	/**
	 * Handle upload.
	 */
	protected function handle_upload()
	{
		$upload = $this->request->file($this->form_name);

		if ($upload['name'] != 'none' && trim($upload['name']))
		{
			// Try uploading the file.
			$this->upload_file();

			// Store for easier access
			$this->errors = array_merge($this->errors, $this->filedata['error']);

			// If we had no problems we can submit the data to the database.
			if (empty($this->filedata['error']))
			{
				$order_id = ($this->set_custom_order) ? $this->operator->get_max_custom_index() + 1 : 0;

				$data = array(
					'attachment_id'			=> 0,
					'physical_filename'		=> $this->filedata['physical_filename'],
					'attachment_directory'	=> $this->filedata['attachment_directory'],
					'real_filename'			=> $this->filedata['real_filename'],
					'extension'				=> $this->filedata['extension'],
					'mimetype'				=> $this->filedata['mimetype'],
					'filesize'				=> $this->filedata['filesize'],
					'filetime'				=> $this->filedata['filetime'],
					'hash'					=> $this->filedata['md5_checksum'],
					'attachment_order'		=> $order_id,
					'attachment_comment'	=> $this->request->variable('filecomment', '', true),
					'object_type'			=> $this->object_type,
					'object_id'				=> $this->object_id,
				);
				$attachment = $this->operator->get_new_entity($data);

				// Create thumbnail
				$has_thumbnail = $is_preview = false;

				if ($this->filedata['is_image'])
				{
					$has_thumbnail = $attachment->create_thumbnail(
						$this->max_thumbnail_width,
						(($this->max_thumbnail_width === false) ? false : 0)
					);

					// set first screenshot as preview image when it is uploaded
					$is_preview = !$this->operator->get_count();
				}

				$attachment->__set_array(array(
					'thumbnail'				=> $has_thumbnail,
					'is_preview'			=> $is_preview,
				));
				$attachment->submit();

				// Store in operator
				$this->operator->set($attachment);
				$this->uploaded = $attachment->get_id();
			}
		}

		// We do not want to upload it again if this function is called again.
		$this->request->overwrite($this->form_name, null, request_interface::FILES);
	}

	/**
	 * Get maximum file size allowed.
	 *
	 * @return int
	 */
	protected function get_max_filesize()
	{
		if (isset($this->ext_config->upload_max_filesize[$this->object_type]))
		{
			return $this->ext_config->upload_max_filesize[$this->object_type];
		}
		else
		{
			return $this->config['max_filesize'];
		}
	}

	/**
	 * Check whether Plupload is active.
	 *
	 * @return bool
	 */
	public function plupload_active()
	{
		return $this->use_plupload && $this->plupload->is_active();
	}

	/**
	 * Get very basic data for loaded attachments.
	 *
	 * @return array
	 */
	public function get_basic_attachment_data()
	{
		$data = array();

		foreach ($this->operator->get_fixed_indices() as $id)
		{
			$attach = $this->operator->get($id);
			$data[$id] = array(
				'attach_id'			=> $id,
				'real_filename'		=> $attach->get('real_filename'),
				'type'				=> $this->form_name,
				'url'				=> $attach->get_url(),
				'thumb'				=> ($attach->has_thumbnail()) ? $attach->get_url(array(
					'thumb' => 1,
					'view' => 'inline',
				)) : '',
			);
		}
		return $data;
	}

	/**
	 * Get data to return in Plupload request.
	 *
	 * @return array
	 */
	public function get_plupload_response_data()
	{
		if ($this->get_errors())
		{
			return array(
				'jsonrpc' => '2.0',
				'id' => 'id',
				'error' => array(
					'code' => 100,
					'message' => implode('<br />', $this->get_errors()),
				),
			);
		}
		$data = array_values($this->get_basic_attachment_data());

		if ($this->request->is_set('delete_file'))
		{
			return $data;
		}

		$result = array(
			'data' => array_values($this->get_basic_attachment_data())
		);

		if ($this->uploaded)
		{
			$result['download_url'] = $this->get_uploaded_attachment()->get_url();
		}
		return $result;
	}
}
