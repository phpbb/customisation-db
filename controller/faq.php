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

class faq extends \phpbb\help\controller\controller
{
	/** @var \phpbb\titania\display */
	protected $display;

	/**
	 * faq constructor.
	 *
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\help\manager      $manager
	 * @param \phpbb\template\template $template
	 * @param \phpbb\language\language $language
	 * @param \phpbb\titania\display   $display
	 * @param string                   $root_path
	 * @param                          $php_ext
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\help\manager $manager, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\titania\display $display, $root_path, $php_ext)
	{
		$this->display = $display;
		parent::__construct($helper, $manager, $template, $language, $root_path, $php_ext);
	}

	/**
	 * @return string The title of the page
	 */
	public function display()
	{
		$this->language->add_lang('help_faq', 'phpbb/titania');

		$this->display->generate_breadcrumbs(array(
			'CUSTOMISATION_DATABASE' => $this->helper->route('phpbb.titania.index'),
		));

		$this->manager->add_block(
			'HELP_FAQ_BLOCK_TITANIA',
			false,
			array(
				'HELP_FAQ_TITANIA_QUESTION'    => 'HELP_FAQ_TITANIA_ANSWER',
				'HELP_FAQ_VALIDATION_QUESTION' => 'HELP_FAQ_VALIDATION_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_USE_TITANIA',
			false,
			array(
				'HELP_FAQ_FIND_CONTRIB_QUESTION' => 'HELP_FAQ_FIND_CONTRIB_ANSWER',
				'HELP_FAQ_FIND_EXT_QUESTION'     => 'HELP_FAQ_FIND_EXT_ANSWER',
				'HELP_FAQ_FIND_STYLE_QUESTION'   => 'HELP_FAQ_FIND_STYLE_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_SUPPORT',
			false,
			array(
				'HELP_FAQ_RULES_QUESTION'       => 'HELP_FAQ_RULES_ANSWER',
				'HELP_FAQ_GET_SUPPORT_QUESTION' => 'HELP_FAQ_GET_SUPPORT_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_MANAGING',
			true,
			array(
				'HELP_FAQ_CREATING_CONTRIB_QUESTION' => 'HELP_FAQ_CREATING_CONTRIB_ANSWER',
				'HELP_FAQ_SUBMIT_CONTRIB_QUESTION'   => 'HELP_FAQ_SUBMIT_CONTRIB_ANSWER',
				'HELP_FAQ_MANAGING_CONTRIB_QUESTION' => 'HELP_FAQ_MANAGING_CONTRIB_ANSWER',
				'HELP_FAQ_SUBMIT_REVISION_QUESTION'  => 'HELP_FAQ_SUBMIT_REVISION_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_SUPPORTING',
			true,
			array(
				'HELP_FAQ_SUPPORT_FAQ_QUESTION'   => 'HELP_FAQ_SUPPORT_FAQ_ANSWER',
				'HELP_FAQ_SUPPORT_FORUM_QUESTION' => 'HELP_FAQ_SUPPORT_FORUM_ANSWER',
			)
		);
		$this->manager->add_block(
			'HELP_FAQ_BLOCK_VALIDATION',
			true,
			array(
				'HELP_FAQ_VALIDATION_FAIL_QUESTION' => 'HELP_FAQ_VALIDATION_FAIL_ANSWER',
				'HELP_FAQ_VALIDATION_PASS_QUESTION' => 'HELP_FAQ_VALIDATION_PASS_ANSWER',
				'HELP_FAQ_VALIDATORS_QUESTION'      => 'HELP_FAQ_VALIDATORS_ANSWER',
			)
		);

		return $this->language->lang('CUSTOMISATION_DATABASE') . ' ' . $this->language->lang('FAQ_EXPLAIN');
	}
}
