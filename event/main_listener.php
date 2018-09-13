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

namespace phpbb\titania\event;

use phpbb\event\data;
use s9e\TextFormatter\Configurator\Items\TemplateDocument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var bool */
	protected $in_titania;

	/**
	* Constructor
	*
	* @param \phpbb\user $user
	* @param \phpbb\template\template $template
	* @param \phpbb\titania\controller\helper $controller_helper
	* @param string	$phpbb_root_path	phpBB root path
	* @param string	$php_ext			PHP file extension
	*/
	public function __construct(\phpbb\user $user, \phpbb\template\template $template, \phpbb\titania\controller\helper $controller_helper, $phpbb_root_path, $php_ext)
	{
		$this->user = $user;
		$this->template = $template;
		$this->controller_helper = $controller_helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->in_titania = false;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'							=> 'add_permissions',
			'kernel.request'							=> array(array('startup', -1)),
			'core.page_header_after'					=> 'overwrite_template_vars',
            'core.text_formatter_s9e_configure_after'	=> 'inject_bbcode_code_lang',
		);
	}

	public function add_permissions($event)
	{
		$event['categories'] = array_merge($event['categories'], array(
			'titania'			=> 'ACL_CAT_TITANIA',
			'titania_moderate'	=> 'ACL_CAT_TITANIA_MODERATE',
		));

		$event['permissions'] = array_merge($event['permissions'], array(
			// Common
			'u_titania_contrib_submit'	=> array('lang' => 'ACL_U_TITANIA_CONTRIB_SUBMIT', 'cat' => 'titania'),
			'u_titania_faq_create'		=> array('lang' => 'ACL_U_TITANIA_FAQ_CREATE', 'cat' => 'titania'),
			'u_titania_faq_edit'		=> array('lang' => 'ACL_U_TITANIA_FAQ_EDIT', 'cat' => 'titania'),
			'u_titania_faq_delete'		=> array('lang' => 'ACL_U_TITANIA_FAQ_DELETE', 'cat' => 'titania'),
			'u_titania_rate'			=> array('lang' => 'ACL_U_TITANIA_RATE', 'cat' => 'titania'),
			'u_titania_topic'			=> array('lang' => 'ACL_U_TITANIA_TOPIC', 'cat' => 'titania'),
			'u_titania_post'			=> array('lang' => 'ACL_U_TITANIA_POST', 'cat' => 'titania'),
			'u_titania_post_approved'	=> array('lang' => 'ACL_U_TITANIA_POST_APPROVED', 'cat' => 'titania'),
			'u_titania_post_edit_own'	=> array('lang' => 'ACL_U_TITANIA_POST_EDIT_OWN', 'cat' => 'titania'),
			'u_titania_post_delete_own'	=> array('lang' => 'ACL_U_TITANIA_POST_DELETE_OWN', 'cat' => 'titania'),
			'u_titania_post_mod_own'	=> array('lang' => 'ACL_U_TITANIA_POST_MOD_OWN', 'cat' => 'titania'),
			'u_titania_post_attach'		=> array('lang' => 'ACL_U_TITANIA_POST_ATTACH', 'cat' => 'titania'),
			'u_titania_bbcode'			=> array('lang' => 'ACL_U_TITANIA_BBCODE', 'cat' => 'titania'),
			'u_titania_smilies'			=> array('lang' => 'ACL_U_TITANIA_SMILIES', 'cat' => 'titania'),

			'u_titania_post_hard_delete' => array('lang' => 'ACL_U_TITANIA_POST_HARD_DELETE', 'cat' => 'titania'),

			// Moderation
			'u_titania_mod_author_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_AUTHOR_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_contrib_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_CONTRIB_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_faq_mod'		=> array('lang' => 'ACL_U_TITANIA_MOD_FAQ_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_rate_reset'	=> array('lang' => 'ACL_U_TITANIA_MOD_RATE_RESET', 'cat' => 'titania_moderate'),
			'u_titania_mod_post_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_POST_MOD', 'cat' => 'titania_moderate'),

			'u_titania_mod_style_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_MODERATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_clr'				=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_CLR', 'cat' => 'titania_moderate'),

			'u_titania_mod_modification_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_translation_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_converter_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_bbcode_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_queue'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_bridge_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_queue'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_official_tool_moderate' => array('lang' => 'ACL_U_TITANIA_MOD_OFFICIAL_TOOL_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_extension_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_admin' => array('lang' => 'ACL_U_TITANIA_ADMIN', 'cat' => 'titania_moderate'),
		));
	}

	public function startup($event)
	{
		if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST ||
			strpos($event->getRequest()->attributes->get('_controller'), 'phpbb.titania') !== 0)
		{
			return;
		}
		$this->in_titania = true;

		require($this->phpbb_root_path . 'ext/phpbb/titania/common.' . $this->php_ext);
	}

	public function overwrite_template_vars($event)
	{
		if ($this->user->data['is_registered'] && !$this->user->data['is_bot'])
		{
			$this->user->add_lang_ext('phpbb/titania', 'common');

			$u_my_contribs = $this->controller_helper->route('phpbb.titania.author', array(
				'author'	=> urlencode($this->user->data['username_clean']),
				'page'		=> 'contributions',
			));

			$this->template->assign_vars(array(
				'U_MY_CONTRIBUTIONS'		=> $this->controller_helper->get_real_url($u_my_contribs),
			));
		}

		if (!$this->in_titania)
		{
			return;
		}

		$this->template->assign_vars(array(
			'U_FAQ'		=> $this->controller_helper->route('phpbb.titania.faq'),
			'U_SEARCH'	=> $this->controller_helper->route('phpbb.titania.search'),
			'S_BODY_CLASS'	=> 'customisation-database',
		));

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$this->template->assign_vars(array(
				'U_LOGIN_LOGOUT'	=> append_sid(
					"{$this->phpbb_root_path}ucp.{$this->php_ext}",
					array(
						'mode'		=> 'login',
						'redirect'	=> urlencode($this->controller_helper->get_current_url()),
					)
				),
			));
		}
	}

    /**
     * Reads the value for the lang attribute passed to each code BBCode, e.g. [code=diff] or [code lang=diff],
     * and adds it as a class attribute to the code element, e.g. <code class="diff">
     *
     * @param data $event
     */
    public function inject_bbcode_code_lang(data $event)
    {
        $tag = $event['configurator']->tags['CODE'];

        /** @var TemplateDocument $dom */
        $dom = $tag->template->asDOM();

        foreach ($dom->getElementsByTagName('code') as $code)
        {
        	/** @var \DOMElement $code */
			$code->setAttribute('class', '{@lang}');
		}

        $dom->saveChanges();
    }
}
