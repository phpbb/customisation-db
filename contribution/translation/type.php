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

namespace phpbb\titania\contribution\translation;

use phpbb\auth\auth;
use phpbb\template\template;
use phpbb\titania\attachment\attachment;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\base;
use phpbb\titania\entity\package;
use phpbb\user;

class type extends base
{
	/** @var prevalidator */
	protected $prevalidator;

	const ID = 6;
	const NAME = 'translation';
	const URL = 'translation';

	const PHPBB_LATEST_VERSION = '3.2';

	/**
	 * Constructor
	 *
	 * @param ext_config $ext_config
	 * @param user $user
	 * @param auth $auth
	 * @param prevalidator $prevalidator
	 */
	public function __construct(ext_config $ext_config, user $user, auth $auth, prevalidator $prevalidator)
	{
		parent::__construct($ext_config, $user, $auth);

		$this->prevalidator = $prevalidator;
	}

	/**
	 * @{inheritDoc}
	 */
	public function configure()
	{
		$this->restore_root = true;
		$this->clean_package = true;
		$this->validate_translation = true;

		/* Extra upload files disabled on Translation revisions */
		$this->extra_upload = false;

		$this->root_search = array(
			'directories' => array(
				'required' => 'language',
			),
		);

		if ($this->validate_translation)
		{
			$this->upload_steps[] = array(
				'name'		=> 'PV_TEST',
				'function'	=> array($this, 'translation_validate'),
			);
		}

		$this->contribution_fields = array(
			'contrib_local_name'	=> array(
				'type'				=> 'input',
				'name'				=> 'CONTRIB_LOCAL_NAME',
				'explain'			=> 'CONTRIB_LOCAL_NAME_EXPLAIN',
				'editable'			=> true,
			),
			'contrib_iso_code'		=> array(
				'type'				=> 'input',
				'name'				=> 'CONTRIB_ISO_CODE',
				'explain'			=> 'CONTRIB_ISO_CODE_EXPLAIN',
				'editable'			=> true,
			),
		);

		// Language strings
		$this->lang = $this->user->lang('TRANSLATION');
		$this->langs = $this->user->lang('TRANSLATIONS');
		$this->root_not_found_key = 'COULD_NOT_FIND_TRANSLATION_ROOT';
		$this->validation_subject = 'TRANSLATION_VALIDATION';
		$this->validation_message_approve = 'TRANSLATION_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'TRANSLATION_VALIDATION_MESSAGE_DENY';
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($auth)
	{
		switch ($auth)
		{
			// Can submit a translation
			case 'submit' :
				return true;
			break;

			// Can view the translation queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_translation_queue_discussion');
			break;

			// Can view the translation queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_translation_queue');
			break;

			// Can validate tranlations in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_translation_validate');
			break;

			// Can moderate translations
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_translation_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;
		}

		return false;
	}

	/**
	 * Translation validation.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @param attachment $attachment
	 * @param $download_package
	 * @param package $package
	 * @param template $template
	 * @return array Returns array containing any errors found.
	 */
	public function translation_validate(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $download_package, package $package, template $template)
	{
		$phpbb_version = self::PHPBB_LATEST_VERSION;

		if (is_array($revision->phpbb_versions) && count($revision->phpbb_versions) > 0)
		{
			$branch_number = $revision->phpbb_versions[0]['phpbb_version_branch'];
			$phpbb_version = sprintf('%d.%d', $branch_number[0], $branch_number[1]);
		}

		// Run the translation validator
		$translation_validator_output = $this->get_prevalidator()->check_package($package, $contrib->contrib_iso_code, $phpbb_version);

		$template->assign_vars(array(
			'S_PASSED_TRANSLATION_VALIDATION'		=> true,
			'TRANSLATION_VALIDATOR_OUTPUT'			=> $translation_validator_output,
		));

		// Save the translation validation results (we need to save it here so that we can add it to the post later)
		$queue = $revision->get_queue();
		$queue->tv_results = $translation_validator_output;
		$queue->submit();

		return array(
			'message' => $this->user->lang('TRANSLATION_VALIDATION_TESTS'),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function validate_contrib_fields(array $fields)
	{
		$error = array();

		if (empty($fields['contrib_iso_code']))
		{
			$error[] = $this->user->lang('EMPTY_CONTRIB_ISO_CODE');
		}
		if (empty($fields['contrib_local_name']))
		{
			$error[] = $this->user->lang('EMPTY_CONTRIB_LOCAL_NAME');
		}

		return $error;
	}

	/**
	 * Get prevalidator
	 *
	 * @return prevalidator
	 */
	public function get_prevalidator()
	{
		return $this->prevalidator;
	}
}
