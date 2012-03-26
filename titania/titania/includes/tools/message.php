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

/**
 * Message handler class for Titania
 */
class titania_message
{
	/**
	 * Post Object
	 *
	 * @var object
	 */
	public $post_object = false;

	/**
	 * Hidden fields to display on the posting page
	 *
	 * @var string
	 */
	public $s_hidden_fields = array();

	/**
	* Populated with any errors if we encounter them
	*
	* @var array
	*/
	public $error = array();

	/**
	* Attachment object
	*
	* @var mixed
	*/
	public $attachments = false;

	/**
	 * Permissions, set with set_auth() function
	 */
	public $auth = array(
		'bbcode'		=> false,
		'smilies'		=> false,
		'attachments'	=> false,
		'polls'			=> false,
		'lock'			=> false,
		'sticky_topic'	=> false,
		'lock_topic'	=> false,
		'edit_subject'	=> true,
		'edit_message'	=> true,
	);

	/**
	 * Settings, set with set_settings() function
	 */
	public $settings = array(
		'form_name'				=> 'postform',
		'text_name'				=> 'message',
		'subject_name'			=> 'subject',
		'display_preview'		=> true, // If set to false you will need to handle the preview output yourself (otherwise calls $this->preview if isset($_POST['preview']))
		'display_error'			=> true, // If set to false make sure you output the error in the template yourself (turns the S_DISPLAY_ERROR on/off)
		'display_subject'		=> true, // Display the subject field or not
		'display_edit_reason'	=> false, // Display the edit reason field or not
		'display_captcha'		=> false, // Display the captcha or not
		'attachment_tpl'		=> 'posting/attachments/default.html', // Attachments template to use for output

		'subject_default_override'	=> false, // Force over-ride the subject with one you specify, false to use the one gotten from the post object
		'text_default_override'		=> false, // Force over-ride the text with one you specify, false to use the one gotten from the post object
	);

	/**
	 * Array of posting panels
	 *
	 * @var array
	 */
	private $posting_panels = array(
		'options-panel'			=> 'OPTIONS',
	);

	public function __construct($post_object)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('posting');

		if (!function_exists('titania_access_select'))
		{
			include(TITANIA_ROOT . 'includes/functions_posting.' . PHP_EXT);
		}

		$this->post_object = $post_object;
	}

	/**
	 * Set the auth settings
	 *
	 * @param array $auth
	 */
	public function set_auth($auth)
	{
		$this->auth = array_merge($this->auth, $auth);
	}

	/**
	 * Set the settings
	 *
	 * @param array $settings
	 */
	public function set_settings($settings)
	{
		$this->settings = array_merge($this->settings, $settings);
	}

	/**
	* Submit check
	* Runs $this->post_object->post_data if required (and exists)
	* Displays the preview automatically if requested
	*
	* @return bool True if the form was submitted, False if not
	*/
	public function submit_check()
	{
		// Setup the attachments!
		$this->setup_attachments();

		$submit = isset($_POST['submit']) ? true : false;
		$preview = isset($_POST['preview']) ? true : false;
		$full_editor = isset($_POST['full_editor']) ? true : false;

		// Submit the data to the post object
		if (method_exists($this->post_object, 'post_data') && ($submit || $preview || $full_editor || ($this->attachments && ($this->attachments->uploaded || $this->attachments->deleted))))
		{
			// Resync inline attachments if any were deleted
			if ($this->attachments && $this->attachments->deleted)
			{
				$delete = request_var('delete_file', array(0));
				foreach ($delete as $attach_id => $null)
				{
					$index = request_var('index_' . $attach_id, 0);
					$_REQUEST[$this->settings['text_name']] = preg_replace('#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e', "(\\1 == \$index) ? '' : ((\\1 > \$index) ? '[attachment=' . (\\1 - 1) . ']\\2[/attachment]' : '\\0')", $_REQUEST[$this->settings['text_name']]);
				}
			}

			// We have to reset some request data if we are going to a full editor (checkboxes will be set according to their settings)
			if ($full_editor)
			{
				$this->reset_request_data();
			}

			$this->post_object->post_data($this);
		}

		// Display the preview
		if ($preview && $this->settings['display_preview'])
		{
			$this->preview();
		}

		return $submit;
	}

	/**
	 * Display the message box
	 */
	public function display()
	{
		$for_edit = $this->post_object->generate_text_for_edit();

		// Initialize our post options class
		$post_options = new post_options();
		$post_options->set_auth($this->auth['bbcode'], $this->auth['smilies'], true, true, true);
		$post_options->set_status($for_edit['allow_bbcode'], $for_edit['allow_smilies'], $for_edit['allow_urls']);

		// Setup the attachments!
		$this->setup_attachments();

		if ($this->auth['polls'])
		{
			$this->posting_panels['poll-panel'] = 'POLL';
		}

		// Add the forum key
		add_form_key($this->settings['form_name']);

		// Generate smiley listing
		if ($post_options->get_status('smilies'))
		{
			phpbb::_include('functions_posting', 'generate_smilies');

			generate_smilies('inline', false);
		}

		// Build custom bbcodes array
		if ($post_options->get_status('bbcode'))
		{
			phpbb::_include('functions_display', 'display_custom_bbcodes');

			display_custom_bbcodes();
		}

		// Display the Captcha if required
		if ($this->settings['display_captcha'])
		{
			phpbb::_include('captcha/captcha_factory', false, 'phpbb_captcha_factory');

			$captcha =& phpbb_captcha_factory::get_instance(phpbb::$config['captcha_plugin']);
			$captcha->init(CONFIRM_POST);

			if ($captcha->validate($this->request_data()) !== false)
			{
				phpbb::reset_template();

				// Parse the captcha template
				phpbb::$template->set_filenames(array(
					'captcha'	=> $captcha->get_template(),
				));

				// Correct confirm image link
				phpbb::$template->assign_var('CONFIRM_IMAGE_LINK', phpbb::append_sid('ucp', 'mode=confirm&amp;confirm_id=' . $captcha->confirm_id . '&amp;type=' . $captcha->type));

				phpbb::$template->assign_display('captcha', 'CAPTCHA', false);

				titania::set_custom_template();
			}

			$this->s_hidden_fields = array_merge($this->s_hidden_fields, $captcha->get_hidden_fields());
		}

		$post_options->set_in_template();

		// Save the opened panel to show again
		$default_panel = request_var('open_panel', 'options-panel');
		$default_panel = (isset($this->posting_panels[$default_panel])) ? $default_panel :  'options-panel';

		phpbb::$template->assign_vars(array(
			'ACCESS_OPTIONS'			=> titania_access_select((isset($for_edit['access'])) ? $for_edit['access'] : TITANIA_ACCESS_PUBLIC),

			'EDIT_REASON'				=> (isset($for_edit['edit_reason'])) ? $for_edit['edit_reason'] : '',

			'POSTING_FORM_NAME'			=> $this->settings['form_name'],
			'POSTING_TEXT_NAME'			=> $this->settings['text_name'],
			'POSTING_SUBJECT_NAME'		=> $this->settings['subject_name'],

			'POSTING_PANELS_DEFAULT'	=> $default_panel,

			'POSTING_TEXT'				=> ($this->settings['text_default_override'] !== false) ? $this->settings['text_default_override'] : $for_edit['text'],

			'SUBJECT'					=> ($this->settings['subject_default_override'] !== false) ? $this->settings['subject_default_override'] : ((isset($for_edit['subject'])) ? $for_edit['subject'] : ''),

			'S_ENHANCED_EDITOR'			=> phpbb::$user->data['titania_enhanced_editor'],
			'S_DISPLAY_ERROR'			=> $this->settings['display_error'],
			'S_DISPLAY_SUBJECT'			=> $this->settings['display_subject'],
			'S_STICKY_TOPIC_ALLOWED'	=> $this->auth['sticky_topic'],
			'S_STICKY_TOPIC_CHECKED'	=> (isset($for_edit['topic_sticky'])) ? $for_edit['topic_sticky'] : false,
			'S_LOCK_TOPIC_ALLOWED'		=> $this->auth['lock_topic'],
			'S_LOCK_TOPIC_CHECKED'		=> (isset($for_edit['topic_locked'])) ? $for_edit['topic_locked'] : false,
			'S_LOCK_POST_ALLOWED'		=> $this->auth['lock'],
			'S_LOCK_POST_CHECKED'		=> (isset($for_edit['locked'])) ? $for_edit['locked'] : false,
			'S_EDIT_REASON'				=> $this->settings['display_edit_reason'],
			'S_HIDDEN_FIELDS'			=> build_hidden_fields($this->s_hidden_fields),
		));

		if ($this->attachments)
		{
			phpbb::$template->assign_vars(array(
				'UPLOADER'					=> $this->attachments->parse_uploader($this->settings['attachment_tpl']),
				'S_FORM_ENCTYPE'			=> ' enctype="multipart/form-data"',
			));
		}

		$this->display_panels();
	}

	/**
	* Display Quick Reply
	* This function expects the $post_object sent to be titania_topic object
	*/
	public function display_quick_reply()
	{
		// Add the forum key
		add_form_key($this->settings['form_name']);

		$qr_hidden_fields = array();

		if (phpbb::$user->data['user_notify'] && $this->post_object->topic_type == TITANIA_SUPPORT)
		{
			$qr_hidden_fields['notify'] = true;
		}

		phpbb::$template->assign_vars(array(
			'QR_HIDDEN_FIELDS'	=> build_hidden_fields($qr_hidden_fields),
			'S_QUICK_REPLY'		=> true,
			'U_QR_ACTION'		=> $this->post_object->get_url('reply'),
			'SUBJECT'			=> 'Re: ' . $this->post_object->topic_subject,
		));
	}

	/**
	 * Output a basic preview
	 */
	public function preview()
	{
		// Setup the attachments!
		$this->setup_attachments();

		$for_edit = $this->post_object->generate_text_for_edit(); // Use the info from the post object instead of request_data

		// This seems unneccessary, it works as expected without running generate_text_for_storage first.
		//$request_data = $this->request_data();
		//$this->post_object->generate_text_for_storage($request_data['bbcode_enabled'], $request_data['magic_url_enabled'], $request_data['smilies_enabled']);

		$message = $this->post_object->generate_text_for_display();

		if ($this->attachments)
		{
			$parsed_attachments = $this->attachments->parse_attachments($message, 'common/attachment.html', true);
		}

		phpbb::$template->assign_vars(array(
			'PREVIEW_SUBJECT'		=> (isset($for_edit['subject'])) ? censor_text($for_edit['subject']) : '',
			'PREVIEW_MESSAGE'		=> $message,

			'S_DISPLAY_PREVIEW'		=> true,
		));

		if ($this->attachments)
		{
			foreach ($parsed_attachments as $attachment)
			{
				phpbb::$template->assign_block_vars('preview_attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment,
				));
			}
		}
	}

	/**
	 * Grab the posted subject from the request
	 */
	public function request_data()
	{
		// Setup the attachments!
		$this->setup_attachments();

		$for_edit = $this->post_object->generate_text_for_edit();

		// Initialize our post options class
		$post_options = new post_options();
		$post_options->set_auth($this->auth['bbcode'], $this->auth['smilies'], true, true, true);

		$bbcode_disabled = (isset($_POST['disable_bbcode']) || !$post_options->get_status('bbcode')) ? true : false;
		$smilies_disabled = (isset($_POST['disable_smilies']) || !$post_options->get_status('smilies')) ? true : false;
		$magic_url_disabled = (isset($_POST['disable_magic_url'])) ? true : false;

		$data = array(
			'access'			=> request_var('message_access', (int) ((isset($for_edit['access'])) ? $for_edit['access'] : TITANIA_ACCESS_PUBLIC)),
			'lock'				=> ($this->auth['lock'] && isset($_POST['lock'])) ? true : false,
			'has_attachments'	=> ($this->attachments !== false && sizeof($this->attachments->get_attachments())) ? true : false,

			'bbcode_enabled'	=> !$bbcode_disabled,
			'smilies_enabled'	=> !$smilies_disabled,
			'magic_url_enabled'	=> !$magic_url_disabled,

			'sticky_topic'		=> ($this->auth['sticky_topic'] && isset($_POST['sticky_topic'])) ? true : false,
			'lock_topic'		=> ($this->auth['lock_topic'] && isset($_POST['lock_topic'])) ? true : false,
		);

		if ($this->auth['edit_subject'])
		{
			$data['subject'] = utf8_normalize_nfc(request_var($this->settings['subject_name'], ((isset($for_edit['subject'])) ? $for_edit['subject'] : ''), true));
		}

		if ($this->auth['edit_message'])
		{
			$data = array_merge($data, array(
				'message'	=> utf8_normalize_nfc(request_var($this->settings['text_name'], ((isset($for_edit['text'])) ? $for_edit['text'] : ''), true)),
				'options'	=> get_posting_options(!$bbcode_disabled, !$smilies_disabled, !$magic_url_disabled),
			));
		}

		return $data;
	}

	/**
	* Reset the request data to the post object's options (used for some stuff like the full editor link)
	*/
	public function reset_request_data()
	{
		$for_edit = $this->post_object->generate_text_for_edit();

		// ! in the first position means we will do a false check
		$check_boxes = array(
			'!allow_bbcode'		=> 'disable_bbcode',
			'!allow_url'		=> 'disable_magic_url',
			'!allow_smilies'	=> 'disable_smilies',
			'topic_sticky'		=> 'sticky_topic',
			'topic_locked'		=> 'lock_topic',
			'lock'				=> 'lock',
		);

		// Handle checkboxes (isset($_POST[]))
		foreach ($check_boxes as $edit_name => $post_name)
		{
			if ($edit_name[0] == '!')
			{
				$edit_name = substr($edit_name, 1);

				if (isset($for_edit[$edit_name]) && !$for_edit[$edit_name])
				{
					$_POST[$post_name] = true;
				}
			}
			else if (isset($for_edit[$edit_name]) && $for_edit[$edit_name])
			{
				$_POST[$post_name] = true;
			}
		}
	}

	/**
	* Call this function after you submit (to update attachments and other misc things)
	*/
	public function submit()
	{
		$for_edit = $this->post_object->generate_text_for_edit();

		// Setup the attachments!
		$this->setup_attachments();

		if ($this->attachments !== false)
		{
			$this->attachments->object_id = $for_edit['object_id'];
			$this->attachments->submit($for_edit['access']);
		}
	}

	/**
	 * If you display the captcha, run this function to check if they entered the correct captcha setting
	 *
	 * @return mixed $captcha->validate(); results (false on success, error string on failure)
	 */
	public function validate_captcha()
	{
		phpbb::_include('captcha/captcha_factory', false, 'phpbb_captcha_factory');

		$captcha =& phpbb_captcha_factory::get_instance(phpbb::$config['captcha_plugin']);
		$captcha->init(CONFIRM_POST);

		return $captcha->validate($this->request_data());
	}

	/**
	 * Validate the form key
	 *
	 * @return mixed false on success, error string on failure
	 */
	public function validate_form_key()
	{
		if (!check_form_key($this->settings['form_name']))
		{
			return phpbb::$user->lang['FORM_INVALID'];
		}

		return false;
	}

	/**
	 * Display the panels (tabs)
	 */
	public function display_panels()
	{
		foreach ($this->posting_panels as $name => $lang)
		{
			phpbb::$template->set_filenames(array(
				$name		=> 'posting/panels/' . $name . '.html'
			));

			phpbb::$template->assign_block_vars('panels', array(
				'NAME'		=> $name,
				'TITLE'		=> (isset(phpbb::$user->lang[$lang])) ? phpbb::$user->lang[$lang] : $lang,

				'OUTPUT'	=> phpbb::$template->assign_display($name),
			));
		}
	}

	/**
	* Setup the attachments
	* Unfortunately there is not much of a good way of doing this besides requiring extra calls to the message class (which I do not want to do)
	*/
	private function setup_attachments()
	{
		// We set it up already...
		if (is_object($this->attachments))
		{
			return;
		}

		$for_edit = $this->post_object->generate_text_for_edit();
		if ($this->auth['attachments'] && isset($for_edit['object_type']))
		{
			$this->posting_panels['attach-panel'] = 'ATTACH';

			$this->attachments = new titania_attachment($for_edit['object_type'], $for_edit['object_id']);
			$this->attachments->load_attachments();
			$this->attachments->upload();
			$this->error = array_merge($this->error, $this->attachments->error);
			$this->attachments->error = array(); // Empty the error array to prevent showing duplicates
		}
	}
}

/**
 * Check permission and settings for bbcode, img, url, etc
 */
class post_options
{
	// directly from permissions
	public $auth_bbcode = false;
	public $auth_smilies = false;
	public $auth_img = false;
	public $auth_url = false;
	public $auth_flash = false;

	// whether or not they are enabled in the post
	private $enable_bbcode = false;
	private $enable_smilies = false;
	private $enable_magic_url = false;

	// final setting whether they are allowed or not
	private $bbcode_status = false;
	private $smilies_status = false;
	private $img_status = false;
	private $url_status = false;
	private $flash_status = false;

	public function set_auth($bbcode, $smilies = false, $img = false, $url = false, $flash = false)
	{
		$this->auth_bbcode = $bbcode;
		$this->auth_smilies = $smilies;
		$this->auth_img = $img;
		$this->auth_url = $url;
		$this->auth_flash = $flash;

		$this->bbcode_status = (phpbb::$config['allow_bbcode'] && $this->auth_bbcode) ? true : false;
		$this->smilies_status = (phpbb::$config['allow_smilies'] && $this->auth_smilies) ? true : false;
		$this->img_status = ($this->auth_img && $this->bbcode_status) ? true : false;
		$this->url_status = (phpbb::$config['allow_post_links'] && $this->auth_url && $this->bbcode_status) ? true : false;
		$this->flash_status = ($this->auth_flash && $this->bbcode_status) ? true : false;
	}

	/**
	 * set the status to the  variables above, the enabled options are if they are enabled in the posts(by who ever is posting it)
	 */
	public function set_status($bbcode, $smilies, $url)
	{
		$this->enable_bbcode = ($this->bbcode_status && $bbcode) ? true : false;
		$this->enable_smilies = ($this->smilies_status && $smilies) ? true : false;
		$this->enable_magic_url = ($this->url_status && $url) ? true : false;
	}

	/**
	* Get the status of a type
	*
	* @param mixed $mode (bbcode|smilies|img|url|flash)
	*/
	public function get_status($mode)
	{
		$var = $mode . '_status';
		return $this->{$var};
	}

	/**
	 * Set the options in the template
	 */
	public function set_in_template()
	{
		// Assign some variables to the template parser
		phpbb::$template->assign_vars(array(
			// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
			'S_BBCODE_CHECKED'			=> ($this->enable_bbcode) ? '' : ' checked="checked"',
			'S_SMILIES_CHECKED'			=> ($this->enable_smilies) ? '' : ' checked="checked"',
			'S_MAGIC_URL_CHECKED'		=> ($this->enable_magic_url) ? '' : ' checked="checked"',

			// To show the Options: section on the bottom left
			'BBCODE_STATUS'				=> sprintf(phpbb::$user->lang[(($this->bbcode_status) ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF')], '<a href="' . phpbb::append_sid('faq', 'mode=bbcode') . '">', '</a>'),
			'IMG_STATUS'				=> ($this->img_status) ? phpbb::$user->lang['IMAGES_ARE_ON'] : phpbb::$user->lang['IMAGES_ARE_OFF'],
			'FLASH_STATUS'				=> ($this->flash_status) ? phpbb::$user->lang['FLASH_IS_ON'] : phpbb::$user->lang['FLASH_IS_OFF'],
			'SMILIES_STATUS'			=> ($this->smilies_status) ? phpbb::$user->lang['SMILIES_ARE_ON'] : phpbb::$user->lang['SMILIES_ARE_OFF'],
			'URL_STATUS'				=> ($this->url_status) ? phpbb::$user->lang['URL_IS_ON'] : phpbb::$user->lang['URL_IS_OFF'],

			// To show the option to turn each off while posting
			'S_BBCODE_ALLOWED'			=> $this->bbcode_status,
			'S_SMILIES_ALLOWED'			=> $this->smilies_status,
			'S_LINKS_ALLOWED'			=> $this->url_status,

			// To show the BBCode buttons for each on top
			'S_BBCODE_IMG'				=> $this->img_status,
			'S_BBCODE_URL'				=> $this->url_status,
			'S_BBCODE_FLASH'			=> $this->flash_status,
			'S_BBCODE_QUOTE'			=> true,
		));
	}
}