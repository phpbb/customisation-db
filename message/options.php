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

/**
 * Check permission and settings for bbcode, img, url, etc
 */
class options
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	// directly from permissions
	public $auth_bbcode = false;
	public $auth_smilies = false;
	public $auth_img = false;
	public $auth_url = false;
	public $auth_flash = false;

	// whether or not they are enabled in the post
	protected $enable_bbcode = false;
	protected $enable_smilies = false;
	protected $enable_magic_url = false;

	// final setting whether they are allowed or not
	protected $bbcode_status = false;
	protected $smilies_status = false;
	protected $img_status = false;
	protected $url_status = false;
	protected $flash_status = false;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\template\template $template)
	{
		$this->config = $config;
		$this->user = $user;
		$this->template = $template;
	}

	/**
	 * Set auth settings.
	 *
	 * @param bool $bbcode		Allow bbcode.
	 * @param bool $smilies		Allow smilies. Defaults to false.
	 * @param bool $img			Allow images. Defaults to false.
	 * @param bool $url			Allow url's. Defaults to false.
	 * @param bool $flash		Allow flash. Defaults to false.
	 * @return $this
	 */
	public function set_auth($bbcode, $smilies = false, $img = false, $url = false, $flash = false)
	{
		$this->auth_bbcode = $bbcode;
		$this->auth_smilies = $smilies;
		$this->auth_img = $img;
		$this->auth_url = $url;
		$this->auth_flash = $flash;

		$this->bbcode_status = $this->config['allow_bbcode'] && $this->auth_bbcode;
		$this->smilies_status = $this->config['allow_smilies'] && $this->auth_smilies;
		$this->img_status = $this->auth_img && $this->bbcode_status;
		$this->url_status = $this->config['allow_post_links'] && $this->auth_url && $this->bbcode_status;
		$this->flash_status = $this->auth_flash && $this->bbcode_status;

		return $this;
	}

	/**
	 * Set
	 * @param $bbcode
	 * @param $smilies
	 * @param $url
	 * @return $this
	 */
	public function set_status($bbcode, $smilies, $url)
	{
		$this->enable_bbcode = $this->bbcode_status && $bbcode;
		$this->enable_smilies = $this->smilies_status && $smilies;
		$this->enable_magic_url = $this->url_status && $url;

		return $this;
	}

	/**
	 * Get the status of a type
	 *
	 * @param mixed $mode (bbcode|smilies|img|url|flash)
	 * @return bool
	 */
	public function get_status($mode)
	{
		$var = $mode . '_status';
		return $this->{$var};
	}

	/**
	 * Set the options in the template
	 *
	 * @return $this
	 */
	public function set_in_template()
	{
		// Assign some variables to the template parser
		$this->template->assign_vars(array(
			// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
			'S_BBCODE_CHECKED'			=> ($this->enable_bbcode) ? '' : ' checked="checked"',
			'S_SMILIES_CHECKED'			=> ($this->enable_smilies) ? '' : ' checked="checked"',
			'S_MAGIC_URL_CHECKED'		=> ($this->enable_magic_url) ? '' : ' checked="checked"',

			// To show the Options: section on the bottom left
			'BBCODE_STATUS'				=> $this->user->lang((($this->bbcode_status) ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '', ''),
			'IMG_STATUS'				=> ($this->img_status) ? $this->user->lang('IMAGES_ARE_ON') : $this->user->lang('IMAGES_ARE_OFF'),
			'FLASH_STATUS'				=> ($this->flash_status) ? $this->user->lang('FLASH_IS_ON') : $this->user->lang('FLASH_IS_OFF'),
			'SMILIES_STATUS'			=> ($this->smilies_status) ? $this->user->lang('SMILIES_ARE_ON') : $this->user->lang('SMILIES_ARE_OFF'),
			'URL_STATUS'				=> ($this->url_status) ? $this->user->lang('URL_IS_ON') : $this->user->lang('URL_IS_OFF'),

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

		return $this;
	}

	/**
	 * Generate the _options flag from the given settings
	 *
	 * @param bool $bbcode
	 * @param bool $smilies
	 * @param bool $url
	 * @return int options flag
	 */
	public function get_posting_options($bbcode, $smilies, $url)
	{
		return (($bbcode) ? OPTION_FLAG_BBCODE : 0) + (($smilies) ? OPTION_FLAG_SMILIES : 0) + (($url) ? OPTION_FLAG_LINKS : 0);
	}
}
