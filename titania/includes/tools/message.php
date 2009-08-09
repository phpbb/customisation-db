<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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
* Message handler class for Titania
*/
class titania_message
{
	/**
	* Hidden fields to display on the posting page
	*
	* @var string
	*/
	public $s_hidden_fields = '';

	/**
	* Form Name
	*
	* @var string
	*/
	public $form_name = 'postform';

	/**
	* Textarea Name
	*
	* @var string
	*/
	public $text_name = 'message';

	/**
	* Post Object
	*
	* @var object
	*/
	public $post_object = false;

	/**
	* Permissions
	*/
	public $auth_bbcode = false;
	public $auth_smilies = false;
	public $auth_attachments = false;
	public $auth_polls = false;

	/**
	* Extra options
	*/
	public $display_error = true; // Make sure you output the error yourself if you set to false!
	public $display_subject = true;
	public $attachments_group = 0; // The attachment extensions group to allow

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
	* Display the message box
	*/
	public function display()
	{
		phpbb::$user->add_lang('posting');

		$for_edit = $this->post_object->generate_text_for_edit();

		// Initialize our post options class
		$post_options = new post_options();
		$post_options->set_auth($this->auth_bbcode, $this->auth_smilies, true, true, true);
		$post_options->set_status($for_edit['allow_bbcode'], $for_edit['allow_smilies'], $for_edit['allow_urls']);

		if ($this->auth_attachments)
		{
			$this->posting_panels['attach-panel'] = 'ATTACH';
		}

		if ($this->auth_polls)
		{
			$this->posting_panels['poll-panel'] = 'POLL';
		}

		// Add the forum key
		add_form_key($this->form_name);

		// Generate smiley listing
		if ($post_options->get_status('smilies'))
		{
			$this->generate_smilies('inline', false);
		}

		// Build custom bbcodes array
		if ($post_options->get_status('bbcode'))
		{
			if (!function_exists('display_custom_bbcodes'))
			{
				include(PHPBB_ROOT_PATH . 'includes/functions_display.' . PHP_EXT);
			}
			display_custom_bbcodes();
		}

		$post_options->set_in_template();

		phpbb::$template->assign_vars(array(
			'ACCESS_OPTIONS'			=> titania_access_select(),

			'POSTING_FORM_NAME'			=> $this->form_name,
			'POSTING_TEXT_NAME'			=> $this->text_name,

			'POSTING_PANELS_DEFAULT'	=> 'options-panel',

			'POSTING_TEXT'				=> $for_edit['text'],

			'SUBJECT'					=> (isset($for_edit['subject'])) ? $for_edit['subject'] : '',

			'S_DISPLAY_ERROR'			=> $this->display_error,
			'S_DISPLAY_SUBJECT'			=> $this->display_subject,
			'S_FORM_ENCTYPE'			=> '',
		));

		$this->display_panels();
	}

	/**
	* Grab the posted subject from the request
	*/
	public function request_data()
	{
		// Initialize our post options class
		$post_options = new post_options();
		$post_options->set_auth($this->auth_bbcode, $this->auth_smilies, true, true, true);

		$bbcode_disabled = (isset($_POST['disable_bbcode']) || !$post_options->get_status('bbcode')) ? true : false;
		$smilies_disabled = (isset($_POST['disable_smilies']) || !$post_options->get_status('smilies')) ? true : false;
		$magic_url_disabled = (isset($_POST['disable_magic_url'])) ? true : false;

		return array(
			'subject'		=> utf8_normalize_nfc(request_var('subject', '', true)),
			'message'		=> utf8_normalize_nfc(request_var($this->text_name, '', true)),
			'options'		=> get_posting_options(!$bbcode_disabled, !$smilies_disabled, !$magic_url_disabled),
			'access'		=> request_var('message_access', TITANIA_ACCESS_PUBLIC),

			'bbcode_enabled'	=> !$bbcode_disabled,
			'smilies_enabled'	=> !$smilies_disabled,
			'magic_url_enabled'	=> !$magic_url_disabled,
		);
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
	* Fill smiley templates (or just the variables) with smilies, either in a window or inline
	*/
	public function generate_smilies()
	{
		// More smilies link
		$sql = 'SELECT smiley_id
			FROM ' . SMILIES_TABLE . '
			WHERE display_on_posting = 0';
		$result = phpbb::$db->sql_query_limit($sql, 1, 0, 3600);
		if ($row = phpbb::$db->sql_fetchrow($result))
		{
			phpbb::$template->assign_vars(array(
				'S_SHOW_SMILEY_LINK' 	=> true,
				'U_MORE_SMILIES' 		=> append_sid(titania::$absolute_board . 'posting.' . PHP_EXT, 'mode=smilies'))
			);
		}
		phpbb::$db->sql_freeresult($result);


		$sql = 'SELECT *
			FROM ' . SMILIES_TABLE . '
			WHERE display_on_posting = 1
			ORDER BY smiley_order';
		$result = phpbb::$db->sql_query($sql, 3600);

		$smilies = array();
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if (empty($smilies[$row['smiley_url']]))
			{
				$smilies[$row['smiley_url']] = $row;

				phpbb::$template->assign_block_vars('smiley', array(
					'SMILEY_CODE'	=> $row['code'],
					'A_SMILEY_CODE'	=> addslashes($row['code']),
					'SMILEY_IMG'	=> titania::$absolute_board . phpbb::$config['smilies_path'] . '/' . $row['smiley_url'],
					'SMILEY_WIDTH'	=> $row['smiley_width'],
					'SMILEY_HEIGHT'	=> $row['smiley_height'],
					'SMILEY_DESC'	=> $row['emotion'])
				);
			}
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* handle_captcha
	*
	* @param string $mode The mode, build or check, to either build the captcha/confirm box, or to check if the user entered the correct confirm_code
	*
	* @return Returns
	*	- True if the captcha code is correct and $mode is check or they do not need to view the captcha (permissions)
	*	- False if the captcha code is incorrect, or not given and $mode is check
	*/
	public function handle_captcha($mode)
	{
		if ($mode == 'check')
		{
			return true;
		}
		else if ($mode == 'build' && !$this->handle_captcha('check'))
		{

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
			'BBCODE_STATUS'				=> sprintf(phpbb::$user->lang[(($this->bbcode_status) ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF')], '<a href="' . append_sid(titania::$absolute_board . 'faq.' . PHP_EXT, 'mode=bbcode') . '">', '</a>'),
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