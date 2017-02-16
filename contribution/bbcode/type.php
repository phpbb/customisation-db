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

namespace phpbb\titania\contribution\bbcode;

use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\base;
use phpbb\user;

class type extends base
{
	/** @var demo\demo */
	protected $demo;

	const ID = 7;
	const NAME = 'bbcode';
	const URL = 'bbcode';

	/**
	 * Constructor
	 *
	 * @param ext_config $ext_config
	 * @param user $user
	 * @param auth $auth
	 * @param demo\demo $demo
	 */
	public function __construct(ext_config $ext_config, user $user, auth $auth, demo\demo $demo)
	{
		parent::__construct($ext_config, $user, $auth);

		$this->demo = $demo;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configure()
	{
		$this->require_upload = false;
		$this->create_composer_packages = false;
		$this->extra_upload = false;

		$this->lang = $this->user->lang('BBCODE');
		$this->langs = $this->user->lang('BBCODES');

		$this->validation_subject = 'BBCODE_VALIDATION';
		$this->validation_message_approve = 'BBCODE_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'BBCODE_VALIDATION_MESSAGE_DENY';
		$this->upload_agreement = 'BBCODE_UPLOAD_AGREEMENT';

		$this->revision_fields = array(
			'revision_bbc_html_replace' => array(
				'type'		=> 'textarea',
				'name'		=> 'REVISION_HTML_REPLACE',
				'explain'	=> 'REVISION_HTML_REPLACE_EXPLAIN'
			),
			'revision_bbc_bbcode_usage' => array(
				'type'		=> 'textarea',
				'name'		=> 'REVISION_BBCODE_USE',
				'explain'	=> 'REVISION_BBCODE_USE_EXPLAIN'
			),
			'revision_bbc_help_line' => array(
				'type'		=> 'input',
				'name'		=> 'REVISION_HELP_LINE',
				'explain'	=> '',
			),
			'revision_bbc_demo' => array(
				'type'		=> 'textarea',
				'name'		=> 'CONTRIB_DEMO',
				'explain'	=> '',
			),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// Can submit a bbcode
			case 'submit' :
				return true;
			break;

			// Can view the bbcode queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_bbcode_queue_discussion');
			break;

			// Can view the bbcode queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_bbcode_queue');
			break;

			// Can validate bbcodes in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_bbcode_validate');
			break;

			// Can moderate bbcodes
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_bbcode_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;
		}

		return false;
	}

	/**
	 * @{inheritDoc}
	 */
	public function validate_revision_fields(array $fields)
	{
		$error = array();

		if (empty($fields['revision_bbc_html_replace']))
		{
			$error[] = $this->user->lang('NO_HTML_REPLACE');
		}

		if (empty($fields['revision_bbc_bbcode_usage']))
		{
			$error[] = $this->user->lang('NO_BBCODE_USAGE');
		}

		return $error;
	}

	/**
	 * @{inheritDoc}
	 */
	public function approve(\titania_contribution $contrib, \titania_queue $queue, request_interface $request)
	{
		$demo = $this->get_demo()->configure($contrib->contrib_id);
		$demo->clear_cache();
	}

	/**
	 * Get demo instance
	 *
	 * @return demo\demo
	 */
	public function get_demo()
	{
		return $this->demo;
	}
}
