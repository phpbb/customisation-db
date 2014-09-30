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

namespace phpbb\titania\controller;

class faq
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\titania\display */
	protected $display;

	/**
	* Constructor
	*
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\titania\display $display
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\titania\display $display)
	{
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->display = $display;
	}

	public function display_list()
	{
		$this->user->add_lang_ext('phpbb/titania', 'faq', false, true);

		/**
		* From phpBB faq.php
		*/

		// Pull the array data from the lang pack
		$switch_column = $found_switch = false;
		$help_blocks = array();

		foreach ($this->user->help as $help_ary)
		{
			if ($help_ary[0] == '--')
			{
				if ($help_ary[1] == '--')
				{
					$switch_column = true;
					$found_switch = true;
					continue;
				}

				$this->template->assign_block_vars('faq_block', array(
					'BLOCK_TITLE'		=> $help_ary[1],
					'SWITCH_COLUMN'		=> $switch_column,
				));

				if ($switch_column)
				{
					$switch_column = false;
				}
				continue;
			}

			$this->template->assign_block_vars('faq_block.faq_row', array(
				'FAQ_QUESTION'		=> $help_ary[0],
				'FAQ_ANSWER'		=> $help_ary[1])
			);
		}

		// Lets build a page ...
		$this->template->assign_vars(array(
			'L_FAQ_TITLE'				=> $this->user->lang['FAQ_EXPLAIN'],
			'L_BACK_TO_TOP'				=> $this->user->lang['BACK_TO_TOP'],

			'SWITCH_COLUMN_MANUALLY'	=> !$found_switch,
		));
		$this->display->assign_global_vars();

		return $this->helper->render('faq_body.html', 'FAQ_EXPLAIN');
	}
}
