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

namespace phpbb\titania\ucp;

class subscriptions_module
{
	/** @var string */
	public $u_action;

	/** @var \p_master */
	public $p_master;

	/** @var string */
	protected $user;

	/** @var string */
	protected $template;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $phpbb_root_path;

	/** @var \phpbb\titania\controller\ucp\subscriptions */
	protected $controller;

	public function __construct(&$p_master)
	{
		global $user, $template, $phpbb_container, $phpbb_root_path, $phpEx;

		$this->p_master = &$p_master;

		$this->user = $user;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$this->ext_root = $this->phpbb_root_path . 'ext/phpbb/titania/';

		$this->startup();
		$this->controller = $phpbb_container->get('phpbb.titania.controller.ucp.subscriptions');
	}

	/**
	* Main module handler.
	*
	* @param string	$short_name	Module short name
	* @param string $mode		Module mode
	* @return null
	*/
	public function main($short_name, $mode)
	{
		try
		{
			$this->controller->base($mode, $this->u_action);
		}
		catch (\Exception $e)
		{
			$message = $e->getMessage() . '<br /><br />' .
				$this->user->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');

			meta_refresh(3, $this->u_action);
			trigger_error($message);
		}

		$style_path = $this->ext_root . 'styles/' . rawurlencode($this->user->style['style_path']) . '/';
		$u_ucp = append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext);

		$this->template->assign_vars(array(
			'S_ACTION'				=> $this->u_action,
			'TITANIA_THEME_PATH' 	=> $style_path . 'theme/'
		));
		$this->p_master->assign_tpl_vars($u_ucp);

		$this->template->set_filenames(array(
			'body'	=> '@phpbb_titania/ucp/subscriptions.html',
		));

		page_header($this->user->lang['SUBSCRIPTION_TITANIA']);
		page_footer();
	}

	/**
	* Start up Titania.
	*
	* @return null
	*/
	protected function startup()
	{
		require($this->ext_root . 'common.' . $this->php_ext);
	}
}
