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

namespace phpbb\titania\message;

use phpbb\request\request_interface;
use phpbb\titania\access;
use phpbb\titania\ext;

class message
{
	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var object */
	protected $captcha;

	/** @var \phpbb\titania\attachment\uploader */
	protected $uploader;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var \phpbb\titania\message\options */
	protected $options;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

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

	/** @var string */
	protected $message_text;

	/**
	 * Array of posting panels
	 *
	 * @var array
	 */
	protected $posting_panels = array(
		'options-panel'			=> 'OPTIONS',
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param request_interface $request
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 * @param \phpbb\captcha\factory $captcha_factory
	 * @param \phpbb\titania\attachment\uploader $uploader
	 * @param access $access
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\config\config $config, request_interface $request, \phpbb\user $user, \phpbb\template\template $template, \phpbb\captcha\factory $captcha_factory, \phpbb\titania\attachment\uploader $uploader, access $access, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->captcha = $captcha_factory->get_instance($this->config['captcha_plugin']);
		$this->uploader = $uploader;
		$this->access = $access;
		$this->options = new options($this->config, $this->user, $this->template);
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->user->add_lang_ext('phpbb/titania', 'posting');
		$this->user->add_lang('posting');
	}

	/**
	 * Set parent object.
	 *
	 * @param mixed $parent
	 * @return $this
	 */
	public function set_parent($parent)
	{
		$this->post_object = $parent;
		return $this;
	}

	/**
	 * Set the auth settings
	 *
	 * @param array $auth
	 * @return $this
	 */
	public function set_auth($auth)
	{
		$this->auth = array_merge($this->auth, $auth);
		return $this;
	}

	/**
	 * Set the settings
	 *
	 * @param array $settings
	 * @return $this
	 */
	public function set_settings($settings)
	{
		$this->settings = array_merge($this->settings, $settings);
		return $this;
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

		$submit = $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');
		$full_editor = $this->request->is_set_post('full_editor');
		$submit_data = $submit || $preview || $full_editor || $this->uploader->uploaded || $this->uploader->deleted;

		// Submit the data to the post object
		if (method_exists($this->post_object, 'post_data') && $submit_data)
		{
			$message = $this->get_message_text();

			// Resync inline attachments if any were deleted
			if ($this->uploader->deleted)
			{
				$delete = $this->request->variable('delete_file', array(0 => 0));

				foreach ($delete as $attach_id)
				{
					$index = $this->request->variable('index_' . $attach_id, 0);
					$message = preg_replace(
						'#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e',
						"(\\1 == \$index) ? '' : ((\\1 > \$index) ? '[attachment=' . (\\1 - 1) . ']\\2[/attachment]' : '\\0')",
						$message
					);
				}
			}

			// Resync inline attachments if any were added
			if ($this->uploader->uploaded)
			{
				$message = preg_replace(
					'#\[attachment=([0-9]+)\](.*?)\[\/attachment\]#e',
					"'[attachment='.(\\1 + 1).']\\2[/attachment]'",
					$message
				);
			}
			$this->set_message_text($message);

			// We have to reset some request data if we are going to a full editor
			// (checkboxes will be set according to their settings)
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
	 *
	 * @@param int $topic_access_level		Topic access level
	 * @return $this
	 */
	public function display($topic_access_level = access::PUBLIC_LEVEL)
	{
		$for_edit = $this->post_object->generate_text_for_edit();

		$this->options
			->set_auth(
				$this->auth['bbcode'],
				$this->auth['smilies'],
				true,
				true,
				true
			)
			->set_status(
				$for_edit['allow_bbcode'],
				$for_edit['allow_smilies'],
				$for_edit['allow_urls']
			)
		;

		// Setup the attachments!
		$this->setup_attachments();

		if ($this->auth['polls'])
		{
			$this->posting_panels['poll-panel'] = 'POLL';
		}

		// Add the forum key
		add_form_key($this->settings['form_name']);

		// Generate smiley listing
		if ($this->options->get_status('smilies'))
		{
			if (!function_exists('generate_smilies'))
			{
				require($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
			}

			generate_smilies('inline', false);
		}

		// Build custom bbcodes array
		if ($this->options->get_status('bbcode'))
		{
			if (!function_exists('display_custom_bbcodes'))
			{
				require($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
			}

			display_custom_bbcodes();
		}

		// Display the Captcha if required
		if ($this->settings['display_captcha'])
		{
			$this->captcha->init(CONFIRM_POST);

			if ($this->captcha->validate($this->request_data()) !== false)
			{
				// Correct confirm image link
				$this->template->assign_var('CAPTCHA_TEMPLATE', $this->captcha->get_template());
			}

			$this->s_hidden_fields = array_merge(
				$this->s_hidden_fields,
				$this->captcha->get_hidden_fields()
			);
		}

		$this->options->set_in_template();

		// Save the opened panel to show again
		$default_panel = $this->request->variable('show_panel', 'options-panel');
		$default_panel = (isset($this->posting_panels[$default_panel])) ? $default_panel :  'options-panel';
		$access_level = (isset($for_edit['access'])) ? $for_edit['access'] : access::PUBLIC_LEVEL;
		$text = ($this->settings['text_default_override'] !== false) ? $this->settings['text_default_override'] : $for_edit['text'];
		$subject = ($this->settings['subject_default_override'] !== false) ? $this->settings['subject_default_override'] : ((isset($for_edit['subject'])) ? $for_edit['subject'] : '');

		$this->template->assign_vars(array(
			'ACCESS_OPTIONS'			=> $this->get_access_select($access_level, $topic_access_level),

			'EDIT_REASON'				=> (isset($for_edit['edit_reason'])) ? $for_edit['edit_reason'] : '',

			'POSTING_FORM_NAME'			=> $this->settings['form_name'],
			'POSTING_TEXT_NAME'			=> $this->settings['text_name'],
			'POSTING_SUBJECT_NAME'		=> $this->settings['subject_name'],

			'POSTING_PANELS_DEFAULT'	=> $default_panel,

			'POSTING_TEXT'				=> $text,
			'SUBJECT'					=> $subject,

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

		if ($this->auth['attachments'])
		{
			$this->template->assign_vars(array(
				'UPLOADER'					=> $this->uploader->parse_uploader($this->settings['attachment_tpl']),
				'S_FORM_ENCTYPE'			=> ' enctype="multipart/form-data"',
			));
		}

		$this->display_panels();

		return $this;
	}

	/**
	 * Display Quick Reply
	 * This function expects the $post_object sent to be titania_topic object
	 */
	public function display_quick_reply()
	{
		// Add the forum key
		add_form_key($this->settings['form_name']);

		$qr_hidden_fields = array(
			'quick_reply_mode' => true,
		);

		if ($this->user->data['user_notify'] && $this->post_object->topic_type == ext::TITANIA_SUPPORT)
		{
			$qr_hidden_fields['notify'] = true;
		}

		$this->template->assign_vars(array(
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

		// Use the info from the post object instead of request_data
		$for_edit = $this->post_object->generate_text_for_edit();
		$message = $this->post_object->generate_text_for_display();

		if ($this->auth['attachments'])
		{
			$parsed_attachments = $this->uploader->get_operator()->parse_attachments(
				$message,
				'common/attachment.html',
				$this->uploader->get_request_comments()
			);

			foreach ($parsed_attachments as $attachment)
			{
				$this->template->assign_block_vars('preview_attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment,
				));
			}
		}

		$this->template->assign_vars(array(
			'PREVIEW_SUBJECT'		=> (isset($for_edit['subject'])) ? censor_text($for_edit['subject']) : '',
			'PREVIEW_MESSAGE'		=> $message,

			'S_DISPLAY_PREVIEW'		=> true,
		));
	}

	/**
	 * Grab the posted subject from the request
	 */
	public function request_data()
	{
		// Setup the attachments!
		$this->setup_attachments();

		$for_edit = $this->post_object->generate_text_for_edit();

		$this->options->set_auth($this->auth['bbcode'], $this->auth['smilies'], true, true, true);

		$bbcode_disabled = $this->request->is_set_post('disable_bbcode') || !$this->options->get_status('bbcode');
		$smilies_disabled = $this->request->is_set_post('disable_smilies') || !$this->options->get_status('smilies');
		$magic_url_disabled = $this->request->is_set_post('disable_magic_url');

		$default_access = (int) ((isset($for_edit['access'])) ? $for_edit['access'] : access::PUBLIC_LEVEL);
		$data = array(
			'access'			=> $this->request->variable('message_access', $default_access),
			'lock'				=> $this->auth['lock'] && $this->request->is_set_post('lock'),
			'has_attachments'	=> $this->has_attachments(),

			'bbcode_enabled'	=> !$bbcode_disabled,
			'smilies_enabled'	=> !$smilies_disabled,
			'magic_url_enabled'	=> !$magic_url_disabled,

			'sticky_topic'		=> $this->auth['sticky_topic'] && $this->request->is_set_post('sticky_topic'),
			'lock_topic'		=> $this->auth['lock_topic'] && $this->request->is_set_post('lock_topic'),

			// Are we in Quick Reply mode
			'quick_reply_mode'	=> $this->request->variable('quick_reply_mode', 0),
		);

		if ($this->auth['edit_subject'])
		{
			$default_subject = ((isset($for_edit['subject'])) ? $for_edit['subject'] : '');
			$data['subject'] = $this->request->variable(
				$this->settings['subject_name'],
				$default_subject,
				true
			);
		}

		if ($this->auth['edit_message'])
		{
			$default_message = (isset($for_edit['text'])) ? $for_edit['text'] : '';
			$data = array_merge($data, array(
				'message'	=> $this->get_message_text($default_message),
				'options'	=> $this->options->get_posting_options(
					!$bbcode_disabled,
					!$smilies_disabled,
					!$magic_url_disabled
				),
			));
		}

		return $data;
	}

	/**
	 * Get message text.
	 *
	 * @param string $default_value
	 * @return string
	 */
	public function get_message_text($default_value = '')
	{
		if (!$this->request->is_set($this->settings['text_name']))
		{
			return $default_value;
		}
		if ($this->message_text === null)
		{
			$this->message_text = $this->request->variable($this->settings['text_name'], '', true);
		}
		return $this->message_text;
	}

	/**
	 * Set message text.
	 *
	 * @param string $message
	 * @return null
	 */
	public function set_message_text($message)
	{
		$this->message_text = $message;
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
					$this->request->overwrite($post_name, true, request_interface::POST);
				}
			}
			else if (isset($for_edit[$edit_name]) && $for_edit[$edit_name])
			{
				$this->request->overwrite($post_name, true, request_interface::POST);
			}
		}
	}

	/**
	 * Call this function after you submit (to update attachments and other misc things)
	 *
	 * @return $this
	 */
	public function submit()
	{
		$for_edit = $this->post_object->generate_text_for_edit();

		// Setup the attachments!
		$this->setup_attachments();

		if ($this->auth['attachments'])
		{
			$comments = $this->uploader->get_request_comments();
			$this->uploader
				->set_object_id($for_edit['object_id'])
				->get_operator()->submit($for_edit['access'], $comments);
		}
	}

	/**
	 * If you display the captcha, run this function to check if they entered the correct captcha setting
	 *
	 * @return mixed $captcha->validate(); results (false on success, error string on failure)
	 */
	public function validate_captcha()
	{
		$this->captcha->init(CONFIRM_POST);

		return $this->captcha->validate($this->request_data());
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
			return $this->user->lang['FORM_INVALID'];
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
			$this->template->set_filenames(array(
				$name		=> 'posting/panels/' . $name . '.html'
			));

			$this->template->assign_block_vars('panels', array(
				'NAME'		=> $name,
				'TITLE'		=> $this->user->lang($lang),

				'OUTPUT'	=> $this->template->assign_display($name),
			));
		}
	}

	/**
	 * Setup the attachments
	 * Unfortunately there is not much of a good way of doing this besides
	 * 	requiring extra calls to the message class (which I do not want to do)
	 */
	protected function setup_attachments()
	{
		// We set it up already...
		if ($this->uploader->get_object_type() !== null)
		{
			return;
		}
		$for_edit = $this->post_object->generate_text_for_edit();

		if ($this->auth['attachments'] && isset($for_edit['object_type']))
		{
			$this->posting_panels['attach-panel'] = 'ATTACHMENTS';

			$this->uploader
				->configure($for_edit['object_type'], $for_edit['object_id'], true)
				->get_operator()->load()
			;
			$this->uploader->handle_form_action();
			$this->error = array_merge($this->error, $this->uploader->get_errors());
			$this->uploader->clear_errors(); // Empty the error array to prevent showing duplicates
		}
	}

	/**
	 * Check whether any attachments are loaded.
	 *
	 * @return bool
	 */
	public function has_attachments()
	{
		return (bool) $this->uploader->get_operator()->get_count();
	}

	/**
	 * Create select with Titania's accesses
	 *
	 * @param int|bool $default		Default access level. False for none.
	 * @param int $min_access		Minimum access level to display
	 * @return string
	 */
	protected function get_access_select($default = false, $min_access = access::PUBLIC_LEVEL)
	{
		if ($this->access->is_public())
		{
			return '';
		}

		$access_types = array(
			access::TEAM_LEVEL 		=> 'ACCESS_TEAMS',
			access::AUTHOR_LEVEL 	=> 'ACCESS_AUTHORS',
			access::PUBLIC_LEVEL 	=> 'ACCESS_PUBLIC',
		);

		if ($default === false)
		{
			$default = access::PUBLIC_LEVEL;
		}

		$s_options = '';

		foreach ($access_types as $type => $lang_key)
		{
			if ($this->access->get_level() > $type || $min_access < $type)
			{
				continue;
			}

			$selected = ($default == $type) ? ' selected="selected"' : '';

			$s_options .=
				'<option value="' . $type . '"' . $selected . '>' .
				$this->user->lang($lang_key) .
				'</option>'
			;
		}

		return $s_options;
	}

	/**
	 * Check whether the request is from Plupload.
	 *
	 * @return bool
	 */
	public function is_plupload_request()
	{
		return $this->uploader->plupload_active();
	}

	/**
	 * Get response data to return to Plupload.
	 *
	 * @return array
	 */
	public function get_plupload_response_data()
	{
		return $this->uploader->get_plupload_response_data();
	}

	/**
	 * Decode a message from the database (properly)
	 *
	 * @param string $message
	 * @param mixed $bbcode_uid
	 */
	public static function decode(&$message, $bbcode_uid = '')
	{
		decode_message($message, $bbcode_uid);

		// We have to do all sorts of crap because decode_message doesn't properly
		// decode a message for reinserting into the database

		// Replace &nbsp; with spaces - otherwise a number of issues happen...
		$message = str_replace('&nbsp;', ' ', $message);

		// Decode HTML entities, else bbcode reparsing will fail
		$message = html_entity_decode($message, ENT_QUOTES);

		// With magic_quotes_gpc on slashes are stripped too many times, so add them
		$message = (defined('STRIP') && STRIP) ? addslashes($message) : $message;

		// Run set_var to re-encode the proper entities as if the user had submitted it themselves
		set_var($message, $message, 'string', true);
	}

	/**
	 * Get clean message excerpt free of bbcode.
	 *
	 * @param string $string	Message
	 * @param string $uid		BBCode uid
	 * @param int $length		Max excerpt length
	 * @param string $append	String to append to excerpt if original length exceeds
	 * 	max excerpt length.
	 * @return string
	 */
	public static function generate_clean_excerpt($string, $uid, $length, $append = '')
	{
		$_uid = preg_quote($uid, '#');
		$full_bbcode_removal = array(
			'#\[flash=([0-9]+),([0-9]+):' . $_uid . '\](.*?)\[/flash:' . $_uid . '\]#',
			'#\[img:' . $_uid . '\](.*?)\[/img:' . $_uid . '\]#s',
		);
		$string = preg_replace($full_bbcode_removal, '', $string);

		strip_bbcode($string, $uid);
		$string = str_replace(array('&#58;', '&#46;'), array(':', '.'), $string);
		$string = truncate_string($string, $length, $length, false, $append);

		return $string;
	}

}
