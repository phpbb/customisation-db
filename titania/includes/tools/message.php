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
	* Array of posting panels
	*
	* @var array
	*/
	private $posting_panels = array(
		'options-panel'			=> 'OPTIONS',
	);

	public function __construct($post_object)
	{
		$this->post_object = $post_object;
	}

	/**
	* Display the message box
	*/
	public function display()
	{
		phpbb::$user->add_lang('posting');

		if (true) // can attach
		{
			$this->posting_panels['attach-panel'] = 'ATTACH';
		}

		if (true) // can post poll
		{
			$this->posting_panels['poll-panel'] = 'POLL';
		}

		// Add the forum key
		add_form_key($this->form_name);

		// Generate smiley listing
		$this->generate_smilies('inline', false);

		// Build custom bbcodes array
		if (!function_exists('display_custom_bbcodes'))
		{
			include(PHPBB_ROOT_PATH . 'includes/functions_display.' . PHP_EXT);
		}
		display_custom_bbcodes();

		// Post options stuff...
		$post_options = new post_options();
		$post_options->set_auth(true, true, true, true, true);
		$post_options->set_status(true, true, true);
		$post_options->set_in_template();

		phpbb::$template->assign_vars(array(
			'POSTING_FORM_NAME'			=> $this->form_name,
			'POSTING_TEXT_NAME'			=> $this->text_name,

			'POSTING_PANELS_DEFAULT'	=> 'options-panel',

			'POSTING_TEXT'				=> $this->post_object->generate_text_for_edit(),

			'SUBJECT'					=> $this->post_object->post_subject,
		));

		$this->display_panels();
	}

	/**
	* Grab the posted subject from the request
	*/
	public function request_subject()
	{
		return utf8_normalize_nfc(request_var('subject', '', true));
	}

	/**
	* Grab the posted message from the request
	*/
	public function request_message()
	{
		return utf8_normalize_nfc(request_var($this->text_name, '', true));
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
	}

	/**
	 * set the status to the  variables above, the enabled options are if they are enabled in the posts(by who ever is posting it)
	 */
	public function set_status($bbcode, $smilies, $url)
	{
		$this->bbcode_status = (phpbb::$config['allow_bbcode'] && $this->auth_bbcode) ? true : false;
		$this->smilies_status = (phpbb::$config['allow_smilies'] && $this->auth_smilies) ? true : false;
		$this->img_status = ($this->auth_img && $this->bbcode_status) ? true : false;
		$this->url_status = (phpbb::$config['allow_post_links'] && $this->auth_url && $this->bbcode_status) ? true : false;
		$this->flash_status = ($this->auth_flash && $this->bbcode_status) ? true : false;

		$this->enable_bbcode = ($this->bbcode_status && $bbcode) ? true : false;
		$this->enable_smilies = ($this->smilies_status && $smilies) ? true : false;
		$this->enable_magic_url = ($this->url_status && $url) ? true : false;
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