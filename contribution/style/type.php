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

namespace phpbb\titania\contribution\style;

use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\titania\attachment\attachment;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\base;
use phpbb\titania\entity\package;
use phpbb\user;

class type extends base
{
	/** @var demo\demo */
	protected $demo;

	/** @var demo\manager */
	protected $demo_manager;

	const ID = 2;
	const NAME = 'style';
	const URL = 'style';

	/**
	 * Constructor
	 *
	 * @param ext_config $ext_config
	 * @param user $user
	 * @param auth $auth
	 * @param demo\demo $demo
	 * @param demo\manager $demo_manager
	 */
	public function __construct(ext_config $ext_config, user $user, auth $auth, demo\demo $demo, demo\manager $demo_manager)
	{
		parent::__construct($ext_config, $user, $auth);

		$this->demo = $demo;
		$this->demo_manager = $demo_manager;
	}

	/**
	 * @{inheritDoc}
	 */
	protected function configure()
	{
		$this->forum_database = $this->ext_config->forum_style_database;
		$this->forum_robot = $this->ext_config->forum_style_robot;

		$this->clean_package = true;
		$this->license_allow_custom = true;

		// License options
		$this->license_options = array(
			'GPL v2.0',
			'GPL v3.0',
			'LGPL v2.1',
			'LGPL v3.0',
		);

		$this->author_count = 'author_styles';

		// Language strings
		$this->lang = array(
			'lang'		=> $this->user->lang('STYLE'),
			'langs'		=> $this->user->lang('STYLES'),
			'new'		=> $this->user->lang('STYLE_CONTRIB_NEW'),
			'cleaned'	=> $this->user->lang('STYLE_CONTRIB_CLEANED'),
			'hidden'	=> $this->user->lang('STYLE_CONTRIB_HIDDEN'),
			'disabled'	=> $this->user->lang('STYLE_CONTRIB_DISABLED'),
		);
		$this->validation_subject = 'STYLE_VALIDATION';
		$this->validation_message_approve = 'STYLE_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'STYLE_VALIDATION_MESSAGE_DENY';
		$this->create_public = 'STYLE_CREATE_PUBLIC';
		$this->reply_public = 'STYLE_REPLY_PUBLIC';
		$this->update_public = 'STYLE_UPDATE_PUBLIC';
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// Can submit a style
			case 'submit' :
				return true;
			break;

			// Can view the style queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_style_queue_discussion');
			break;

			// Can view the style queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_style_queue');
			break;

			// Can validate styles in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_style_validate');
			break;

			// Can moderate styles
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_style_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;

			// Can edit ColorizeIt settings
			case 'colorizeit' :
				return $this->auth->acl_get('u_titania_mod_style_clr');
			break;
		}

		return false;
	}

	/**
	 * @{inheritDoc}
	 */
	public function upload_check(attachment $attachment)
	{
		$package = new package;
		$package
			->set_source($attachment->get_filepath())
			->set_temp_path($this->ext_config->__get('contrib_temp_path'), true)
			->extract()
		;
		$license_location = $package->find_directory(array(
			'files' => array(
				'required' => 'license.txt',
			),
		));
		$package->cleanup();

		if ($license_location !== null && substr_count($license_location, '/') < 2)
		{
			return array();
		}

		return array($this->user->lang('LICENSE_FILE_MISSING'));
	}

	/**
	 * @{inheritDoc}
	 */
	public function fix_package_name(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $root_dir = null)
	{
		// If we managed to find a single parent directory, then we use that in the zip name, otherwise we fall back to using contrib_name_clean
		if ($root_dir !== null)
		{
			$new_real_filename = $root_dir . '_' . strtolower($revision->revision_version) . '.' . $attachment->extension;
		}
		else
		{
			$new_real_filename = $contrib->contrib_name_clean . '_' . strtolower($revision->revision_version) . '.' . $attachment->extension;
		}

		$attachment->change_real_filename($new_real_filename);
		return $root_dir;
	}

	/**
	 * @{inheritDoc}
	 */
	public function approve(\titania_contribution $contrib, \titania_queue $queue, request_interface $request)
	{
		if (!$request->is_set_post('style_demo_install'))
		{
			return;
		}

		$revision = $queue->get_revision();
		$this->install_demo($contrib, $revision);
	}

	/**
	 * @{inheritDoc}
	 */
	public function install_demo(\titania_contribution $contrib, \titania_revision $revision)
	{
		$revision->load_phpbb_versions();
		$attachment = $revision->get_attachment();
		$branch = $revision->phpbb_versions[0]['phpbb_version_branch'];
		$package = new package;
		$package
			->set_source($attachment->get_filepath())
			->set_temp_path($this->ext_config->__get('contrib_temp_path'), true)
		;

		$demo_url = '';

		if ($this->demo_manager->configure($branch, $contrib, $package))
		{
			$result = $this->demo_manager->install();

			if (empty($result['error']))
			{
				$demo_url = $this->demo_manager->get_demo_url($branch, $result['id']);
				$contrib->set_demo_url($branch, $demo_url);
				$contrib->submit();
			}
		}
		$package->cleanup();

		return $demo_url;
	}

	/**
	 * @{inheritDoc}
	 */
	public function display_validation_options($action, request_interface $request, template $template)
	{
		if ($action != 'approve')
		{
			return;
		}

		$template->assign_vars(array(
			'S_STYLE_DEMO_INSTALL'			=> true,
			'S_STYLE_DEMO_INSTALL_CHECKED'	=> $request->variable('style_demo_install', false),
		));
	}

	/**
	 * Get instance of type demo class.
	 *
	 * @return demo\demo
	 */
	public function get_demo()
	{
		return $this->demo;
	}
}
