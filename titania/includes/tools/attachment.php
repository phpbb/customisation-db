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

/**
 * Attachment handler
 *
 * @package Titania
 */
class titania_attachment extends titania_database_object
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
	* Handles the name of the form field
	*
	* @var string
	*/
	protected $form_name		= '';

	/**
	* Additional fields to handle during output
	* Use an array with the keys being output keys, values being $row keys, EX: array('TEST' => 'attachment_test')
	* See parse_uploader for more
	*
	* @var array
	*/
	public $additional_fields	= array();

	/**
	* Stores the currently attached attachments to the object (send with store_attachments())
	* Used for outputting the with the uploader and for grabbing any updated info on them
	* Private to make sure the array is setup correct
	*
	* @var array
	*/
	private $attachments		= array();

	/**
	 * Upload class
	 *
	 * @var object
	 */
	public $uploader			= false;

	/**
	* Stores the errors (if any) when attaching
	*
	* @var array
	*/
	public $error				= array();

	/**
	* Did we upload a file?
	*
	* @param bool True if we did False if not
	*/
	public $uploaded = false;

	/**
	 * Constructor for attachment/download class
	 *
	 * @param int $object_type Attachment type (check main type constants)
	 * @param object $object_id int
	 */
	public function __construct($object_type, $object_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attachment_id'			=> array('default' => 0),
			'attachment_access'		=> array('default' => 0),
			'object_type'			=> array('default' => 0),
			'object_id'				=> array('default' => 0),

			'physical_filename'		=> array('default' => '',	'max' => 255),
			'attachment_directory'	=> array('default' => '',	'max' => 255),
			'real_filename'			=> array('default' => '',	'max' => 255),
			'attachment_comment'	=> array('default' => ''),

			'download_count'		=> array('default' => 0),

			'filesize'				=> array('default' => 0),
			'filetime'				=> array('default' => titania::$time),
			'extension'				=> array('default' => '',	'max' => 100),
			'mimetype'				=> array('default' => '',	'max' => 100),
			'hash'					=> array('default' => '',	'max' => 32,	'multibyte' => false),

			'thumbnail'				=> array('default' => 0),
			'is_orphan'				=> array('default' => 1),
		));

		$this->object_type = (int) $object_type;
		$this->object_id = (int) $object_id;

		$this->form_name = 'titania_attachment_' . $this->object_type . '_' . $this->object_id;
	}

	/**
	* Send me the attachments already attached to this item!
	* Handled this way to prevent a nubbing of the attachments array.
	*
	* @param array $attachments (should be the row directly from the attachments table)
	*/
	public function store_attachments($attachments)
	{
		foreach ($attachments as $row)
		{
			$this->attachments[$row['attachment_id']] = $row;
		}
	}

	/**
	* Load the attachments from the database from the ids and store them in $this->attachments
	*
	* @param array $attachment_ids
	*/
	public function load_attachments($attachment_ids = false)
	{
		if (!sizeof($attachment_ids) && $attachment_ids !== false)
		{
			return;
		}

		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND object_id = ' . (int) $this->object_id .
				(($attachment_ids !== false) ? ' AND ' . phpbb::$db->sql_in_set('attachment_id', $attachment_ids) : '');
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->attachments[$row['attachment_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* In case they are needed...
	*/
	public function get_attachments()
	{
		return $this->attachments;
	}

	/**
	* Used for outputting multiple items with different attachments and not having to create tons of objects
	*/
	public function clear_attachments()
	{
		$this->attachments = array();
	}

	/**
	 * Parse the uploader
	 *
	 * @param <string> $tpl_file The name of the template file to use to create the uploader
	 * @return <string> The parsed HTML code ready for output
	 */
	public function parse_uploader($tpl_file = 'posting/attachments/default.html')
	{
		titania::add_lang('attachments');

		phpbb::$template->assign_vars(array(
			'FORM_NAME'			=> $this->form_name,
			'MAX_LENGTH'		=> (titania::$access_level != TITANIA_ACCESS_TEAMS) ? phpbb::$config['max_filesize'] : false,

			// Make sure the form type is correct...doing it here just in case someone leaves {S_FORM_ENCTYPE} and forgets about it
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',
		));

		foreach ($this->attachments as $row)
		{
			$output = array(
				'FILENAME'			=> basename($row['real_filename']),
				'FILE_COMMENT'		=> $row['attachment_comment'],
				'ATTACH_ID'			=> $row['attachment_id'],

				'U_VIEW_ATTACHMENT'	=> titania_url::build_url('download', array('id' => $row['attachment_id'])),
			);

			// Allow additional things to be outputted
			foreach ($this->additional_fields as $output_key => $row_key)
			{
				// Try to grab it from post first
				if (isset($_POST[$row_key . '_' . $row['attachment_id']]))
				{
					$output[$output_key] = utf8_normalize_nfc(request_var($row_key . '_' . $row['attachment_id'], '', true));
				}
				else if (isset($row[$row_key]))
				{
					$output[$output_key] = $row[$row_key];
				}
			}

			phpbb::$template->assign_block_vars('attach_row', $output);
		}

		phpbb::$template->set_filenames(array(
			$tpl_file	=> $tpl_file,
		));

		return phpbb::$template->assign_display($tpl_file);
	}

	/**
	* Upload any files we attempted to attach
	*
	* @param string $ext_group The name of the extension group to allow (see TITANIA_ATTACH_EXT_ constants)
	*/
	public function upload($ext_group)
	{
		// First, we shall handle the items already attached
		$attached_ids = request_var($this->form_name . '_attachments', array(0));

		// Query the ones we must
		$to_query = array_diff($attached_ids, array_keys($this->attachments));
		if (sizeof($to_query))
		{
			$sql = 'SELECT * FROM ' . $this->sql_table . '
				WHERE ' . phpbb::$db->sql_in_set('attachment_id', $to_query) . '
					AND object_type = ' . (int) $this->object_type . '
					AND object_id = ' . (int) $this->object_id; // Don't let them be messin with us
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$this->attachments[$row['attachment_id']] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}

		// Next, delete those requested
		// @todo (just hide them for now - remove when submit() happens)

		// Finally upload new items if required
		//if (isset($_FILES[$this->form_name]) && !empty($_FILES[$this->form_name]))
		if ((isset($_FILES[$this->form_name]) && $_FILES[$this->form_name]['name'] != 'none' && trim($_FILES[$this->form_name]['name'])))
		{
			// Setup uploader tool.
			$this->uploader = new titania_uploader($this->form_name, $ext_group);

			// Try uploading the file.
			$this->uploader->upload_file();

			// Store for easier access
			$this->error = $this->uploader->filedata['error'];

			// If we had no problems we can submit the data to the database.
			if (!sizeof($this->error))
			{
				$this->__set_array(array(
					'physical_filename'		=> $this->uploader->filedata['physical_filename'],
					'attachment_directory'	=> $this->uploader->filedata['attachment_directory'],
					'real_filename'			=> $this->uploader->filedata['real_filename'],
					'extension'				=> $this->uploader->filedata['extension'],
					'mimetype'				=> $this->uploader->filedata['mimetype'],
					'filesize'				=> $this->uploader->filedata['filesize'],
					'filetime'				=> $this->uploader->filedata['filetime'],
					'hash'					=> $this->uploader->filedata['md5_checksum'],
					'thumbnail'				=> 0, // @todo Create thumbnail if required

					'attachment_comment'	=> utf8_normalize_nfc(request_var('filecomment', '', true)),
				));

				parent::submit();

				// Store in $this->attachments[]
				$this->attachments[$this->attachment_id] = $this->__get_array();

				// Additional fields
				foreach ($this->additional_fields as $output_key => $row_key)
				{
					$this->attachments[$this->attachment_id][$row_key] = utf8_normalize_nfc(request_var($row_key, '', true));
				}
			}

			// We do not want to upload it again if this function is called again.
			unset($_FILES[$this->form_name]);

			$this->uploaded = true;
		}
	}

	/**
	* Submit the attachments
	* Handles setting orphans, access level, deleting
	* @todo Deleting
	*
	* @param int $attachment_access Access level of the parent (to handle download permissions for the attachments)
	*/
	public function submit($attachment_access)
	{
		if (!sizeof($this->attachments))
		{
			return;
		}

		// Update the attachment comments if necessary
		foreach ($this->attachments as $attachment_id => $row)
		{
			$attachment_comment = utf8_normalize_nfc(request_var('attachment_comment_' . $attachment_id, '', true));
			if ($row['attachment_comment'] != $attachment_comment)
			{
				$sql = 'UPDATE ' . $this->sql_table . '
					SET attachment_comment = \'' . phpbb::$db->sql_escape($attachment_comment) . '\'
					WHERE attachment_id = ' . $attachment_id;
				phpbb::$db->sql_query($sql);
			}
		}

		// Update access and is_orphan
		$sql_ary = array(
			'object_id'			=> $this->object_id, // needed when items are attached during initial creation.
			'attachment_access'	=> $attachment_access,
			'is_orphan'			=> 0,
		);

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE ' . phpbb::$db->sql_in_set('attachment_id', array_keys($this->attachments));
		phpbb::$db->sql_query($sql);
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
	* General attachment parsing
	* From phpBB (includes/functions_content.php)
	*
	* @param string &$message The message
	* @return array the parsed attachments
	*/
	function parse_attachments(&$message)
	{
		if (!sizeof($this->attachments))
		{
			return array();
		}

		$compiled_attachments = array();

		if (!isset(phpbb::$template->filename['titania_attachment_tpl']))
		{
			phpbb::$template->set_filenames(array(
				'titania_attachment_tpl'	=> 'common/attachment.html')
			);
		}

		// Sort correctly
		if (phpbb::$config['display_order'])
		{
			// Ascending sort
			krsort($this->attachments);
		}
		else
		{
			// Descending sort
			ksort($this->attachments);
		}

		foreach ($this->attachments as $attachment)
		{
			if (!sizeof($attachment))
			{
				continue;
			}

			// We need to reset/empty the _file block var, because this function might be called more than once
			phpbb::$template->destroy_block_vars('_file');

			$block_array = array();

			// Some basics...
			$attachment['extension'] = strtolower(trim($attachment['extension']));
			$filename = titania::$config->upload_path . $attachment['attachment_directory'] . '/' . utf8_basename($attachment['attachment_directory']) . '/' . utf8_basename($attachment['physical_filename']);
			$thumbnail_filename = titania::$config->upload_path . $attachment['attachment_directory'] . '/' . utf8_basename($attachment['attachment_directory']) . '/thumb_' . utf8_basename($attachment['physical_filename']);

			$filesize = get_formatted_filesize($attachment['filesize'], false);

			$comment = bbcode_nl2br(censor_text($attachment['attachment_comment']));

			$block_array += array(
				'FILESIZE'			=> $filesize['value'],
				'SIZE_LANG'			=> $filesize['unit'],
				'DOWNLOAD_NAME'		=> utf8_basename($attachment['real_filename']),
				'COMMENT'			=> $comment,
			);


			$l_downloaded_viewed = $download_link = '';
			$display_cat = (strpos($attachment['mimetype'], 'image') === 0) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE; // @todo Probably should add support for more types...

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if (phpbb::$config['img_display_inlined'])
					{
						if (phpbb::$config['img_link_width'] || phpbb::$config['img_link_height'])
						{
							$dimension = @getimagesize($filename);

							// If the dimensions could not be determined or the image being 0x0 we display it as a link for safety purposes
							if ($dimension === false || empty($dimension[0]) || empty($dimension[1]))
							{
								$display_cat = ATTACHMENT_CATEGORY_NONE;
							}
							else
							{
								$display_cat = ($dimension[0] <= phpbb::$config['img_link_width'] && $dimension[1] <= phpbb::$config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE;
							}
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			// Make some descisions based on user options being set.
			if (($display_cat == ATTACHMENT_CATEGORY_IMAGE || $display_cat == ATTACHMENT_CATEGORY_THUMB) && !phpbb::$user->optionget('viewimg'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			if ($display_cat == ATTACHMENT_CATEGORY_FLASH && !phpbb::$user->optionget('viewflash'))
			{
				$display_cat = ATTACHMENT_CATEGORY_NONE;
			}

			$download_link = titania_url::build_url('download', array('id' => $attachment['attachment_id']));

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_IMAGE'			=> true,
						'U_INLINE_LINK'		=> titania_url::append_url($download_link, array('mode' => 'view')),
					);
				break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_THUMBNAIL'		=> true,
						'THUMB_IMAGE'		=> titania_url::append_url($download_link, array('mode' => 'view', 'thumb')),
					);
				break;

				// Windows Media Streams
				case ATTACHMENT_CATEGORY_WM:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					// Giving the filename directly because within the wm object all variables are in local context making it impossible
					// to validate against a valid session (all params can differ)
					// $download_link = $filename;

					$block_array += array(
						'ATTACH_ID'		=> $attachment['attachment_id'],
						'S_WM_FILE'		=> true,
					);
				break;

				// Real Media Streams
				case ATTACHMENT_CATEGORY_RM:
				case ATTACHMENT_CATEGORY_QUICKTIME:
					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_RM_FILE'			=> ($display_cat == ATTACHMENT_CATEGORY_RM) ? true : false,
						'S_QUICKTIME_FILE'	=> ($display_cat == ATTACHMENT_CATEGORY_QUICKTIME) ? true : false,
						'ATTACH_ID'			=> $attachment['attachment_id'],
					);
				break;

				// Macromedia Flash Files
				case ATTACHMENT_CATEGORY_FLASH:
					list($width, $height) = @getimagesize($filename);

					$l_downloaded_viewed = 'VIEWED_COUNT';

					$block_array += array(
						'S_FLASH_FILE'	=> true,
						'WIDTH'			=> $width,
						'HEIGHT'		=> $height,
						'U_VIEW_LINK'	=> titania_url::append_url($download_link, array('view' => 1)),
					);
				break;

				default:
					$l_downloaded_viewed = 'DOWNLOAD_COUNT';

					$block_array += array(
						'S_FILE'		=> true,
					);
				break;
			}

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? phpbb::$user->lang[$l_downloaded_viewed . '_NONE'] : (($attachment['download_count'] == 1) ? sprintf(phpbb::$user->lang[$l_downloaded_viewed], $attachment['download_count']) : sprintf(phpbb::$user->lang[$l_downloaded_viewed . 'S'], $attachment['download_count']));

			$block_array += array(
				'U_DOWNLOAD_LINK'		=> $download_link,
				'L_DOWNLOAD_COUNT'		=> $l_download_count
			);

			phpbb::$template->assign_block_vars('_file', $block_array);

			$compiled_attachments[] = phpbb::$template->assign_display('titania_attachment_tpl');
		}

		$tpl_size = sizeof($compiled_attachments);

		$unset_tpl = array();

		preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $message, $matches, PREG_PATTERN_ORDER);

		$replace = array();
		foreach ($matches[0] as $num => $capture)
		{
			// Flip index if we are displaying the reverse way
			$index = (phpbb::$config['display_order']) ? ($tpl_size-($matches[1][$num] + 1)) : $matches[1][$num];

			$replace['from'][] = $matches[0][$num];
			$replace['to'][] = (isset($compiled_attachments[$index])) ? $compiled_attachments[$index] : sprintf(phpbb::$user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);

			$unset_tpl[] = $index;
		}

		if (isset($replace['from']))
		{
			$message = str_replace($replace['from'], $replace['to'], $message);
		}

		$unset_tpl = array_unique($unset_tpl);

		// Needed to let not display the inlined attachments at the end of the post again
		foreach ($unset_tpl as $index)
		{
			unset($compiled_attachments[$index]);
		}

		return $compiled_attachments;
	}
}