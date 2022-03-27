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

namespace phpbb\titania\contribution\mod;

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

	const ID = 1;
	const NAME = 'mod';
	const URL = 'mod';

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
	 * {@inheritDoc}
	 */
	protected function configure()
	{
		$this->allowed_branches = array('<=', 30);

		$this->mpv_test = true;
		$this->automod_test = true;
		$this->restore_root = true;
		$this->clean_package = true;
		$this->root_search = array(
			'files' => array(
				'required' => 'install*.xml',
			),
		);

		$this->forum_database = $this->ext_config->forum_mod_database;
		$this->forum_robot = $this->ext_config->forum_mod_robot;

		if ($this->ext_config->use_queue && $this->use_queue)
		{
			if ($this->mpv_test)
			{
				$this->upload_steps[] = array(
					'name'     => 'MPV_TEST',
					'function' => array($this, 'mpv_test'),
				);
			}

			if ($this->automod_test)
			{
				$this->upload_steps[] = array(
					'name'     => 'AUTOMOD_TEST',
					'function' => array($this, 'automod_test'),
				);
			}
		}

		$this->author_count = 'author_mods';

		// Language strings
		$this->lang = array(
			'lang'		=> $this->user->lang('MODIFICATION'),
			'langs'		=> $this->user->lang('MODIFICATIONS'),
			'new'		=> $this->user->lang('MOD_CONTRIB_NEW'),
			'cleaned'	=> $this->user->lang('MOD_CONTRIB_CLEANED'),
			'hidden'	=> $this->user->lang('MOD_CONTRIB_HIDDEN'),
			'disabled'	=> $this->user->lang('MOD_CONTRIB_DISABLED'),
		);
		$this->validation_subject = 'MOD_VALIDATION';
		$this->validation_message_approve = 'MOD_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'MOD_VALIDATION_MESSAGE_DENY';
		$this->create_public = 'MOD_CREATE_PUBLIC';
		$this->reply_public = 'MOD_REPLY_PUBLIC';
		$this->update_public = 'MOD_UPDATE_PUBLIC';
		$this->upload_agreement = 'MOD_UPLOAD_AGREEMENT';
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// Can submit a mod
			case 'submit' :
				return false; // Disabled MODs due to 3.0.x EOM
			break;

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_modification_queue_discussion');
			break;

			// Can view the mod queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_modification_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_modification_validate');
			break;

			// Can moderate mods
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_modification_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;
		}

		return false;
	}

	/**
	 * Prevalidator test.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @param attachment $attachment
	 * @param string $download_package
	 * @param package $package
	 * @param template $template
	 * @return array
	 */
	public function mpv_test(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $download_package, package $package, template $template)
	{
		// Run MPV
		$prevalidator = $this->get_prevalidator();
		$mpv_results = $prevalidator->run_mpv($download_package);

		if ($mpv_results === false)
		{
			return array(
				'notice'	=> $prevalidator->get_errors(),
			);
		}
		else
		{
			$uid = $bitfield = $flags = false;
			generate_text_for_storage($mpv_results, $uid, $bitfield, $flags, true, true, true);

			// Add the MPV Results to the queue
			$queue = $revision->get_queue();
			$queue->mpv_results = $mpv_results;
			$queue->mpv_results_bitfield = $bitfield;
			$queue->mpv_results_uid = $uid;
			$queue->submit();

			$mpv_results = generate_text_for_display($mpv_results, $uid, $bitfield, $flags);
			$template->assign_var('PV_RESULTS', $mpv_results);

			$template->assign_var('S_AUTOMOD_TEST', $this->automod_test);
		}
	}

	/**
	 * AutoMOD Test.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @param attachment $attachment
	 * @param string $download_package
	 * @param package $package
	 * @param template $template
	 * @return array
	 */
	public function automod_test(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $download_package, package $package, template $template)
	{
		$package->ensure_extracted();
		$prevalidator = $this->get_prevalidator();

		// Automod testing time
		$details = '';
		$error = $html_results = $bbcode_results = array();

		if (!$revision->phpbb_versions)
		{
			$revision->load_phpbb_versions();
		}

		foreach ($revision->phpbb_versions as $row)
		{
			$version_string = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' .$row['phpbb_version_revision'];
			$phpbb_path = $prevalidator->get_helper()->prepare_phpbb_test_directory($version_string);

			if ($phpbb_path === false)
			{
				$error = array_merge($error, $prevalidator->get_helper()->get_errors());
				continue;
			}

			$template->assign_vars(array(
				'PHPBB_VERSION'		=> $version_string,
				'TEST_ID'			=> $row['row_id'],
			));

			$html_result = $bbcode_result = '';
			$installed = $prevalidator->run_automod_test(
				$package,
				$phpbb_path,
				$details,
				$html_result,
				$bbcode_result
			);

			$html_results[] = $html_result;
			$bbcode_results[] = $bbcode_result;
		}

		if (is_array($details))
		{
			$revision->install_time = $details['INSTALLATION_TIME'];

			switch ($details['INSTALLATION_LEVEL'])
			{
				case 'easy' :
					$revision->install_level = 1;
				break;

				case 'intermediate' :
					$revision->install_level = 2;
				break;

				case 'advanced' :
					$revision->install_level = 3;
				break;
			}

			$revision->submit();
		}

		$html_results = implode('<br /><br />', $html_results);
		$bbcode_results = implode("\n\n", $bbcode_results);

		// Update the queue with the results
		$queue = $revision->get_queue();
		$queue->automod_results = $bbcode_results;
		$queue->submit();

		$template->assign_var('AUTOMOD_RESULTS', $html_results);

		return array(
			'error'	=> $error,
		);
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
