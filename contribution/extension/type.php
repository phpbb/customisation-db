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

namespace phpbb\titania\contribution\extension;

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

	const ID = 8;
	const NAME = 'extension';
	const URL = 'extension';

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
	protected function configure()
	{
		$this->epv_test = true;
		$this->clean_package = true;
		$this->create_composer_packages = true;

		$this->allowed_branches = array('>=', 31);
		$this->forum_database = $this->ext_config->forum_extension_database;
		$this->forum_robot = $this->ext_config->forum_extension_robot;

		if ($this->ext_config->use_queue && $this->use_queue && $this->epv_test)
		{
			$this->upload_steps[] = array(
				'name'		=> 'PERFORM_REPACK',
				'function'	=> array($this, 'perform_repack'),
			);

			$this->upload_steps[] = array(
				'name'		=> 'EPV_TEST',
				'function'	=> array($this, 'epv_test'),
			);
		}

		// Language strings
		$this->lang = $this->user->lang('EXTENSION');
		$this->langs = $this->user->lang('EXTENSIONS');
		$this->validation_subject = 'EXTENSION_VALIDATION';
		$this->validation_message_approve = 'EXTENSION_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'EXTENSION_VALIDATION_MESSAGE_DENY';
		$this->create_public = 'EXTENSION_CREATE_PUBLIC';
		$this->reply_public = 'EXTENSION_REPLY_PUBLIC';
		$this->update_public = 'EXTENSION_UPDATE_PUBLIC';
		$this->upload_agreement = 'EXTENSION_UPLOAD_AGREEMENT';
		$this->root_not_found_key = 'COULD_NOT_FIND_EXT_ROOT';
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
				return true;
			break;

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_extension_queue_discussion');
			break;

			// Can view the extensions queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_extension_queue');
			break;

			// Can validate extensions in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_extension_validate');
			break;

			// Can moderate extensions
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_extension_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;
		}

		return false;
	}

	/**
	 * Do the repack for the contribution
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @param attachment $attachment
	 * @param string $download_package
	 * @param package $package
	 * @param template $template
	 * @return array Returns array containing any errors found.
	 */
	public function perform_repack(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $download_package, package $package, template $template)
	{
		try
		{
			$this->repack($package, $contrib, $revision);
			$repack_complete = $this->user->lang('NEW_REVISION_REPACK_COMPLETE');

			// Oversized packages are over 2MB
			if ($package->get_size() > 2097152)
			{
				$repack_complete .= $this->user->lang('NEW_REVISION_REPACK_OVERSIZE');
			}

		}
		catch (\Exception $e)
		{
			return array(
				'error'	=> array($this->user->lang($e->getMessage())),
			);
		}
		return array(
			'message' => $repack_complete,
		);
	}

	/**
	 * Run EPV test on new submissions and submit results to queue topic.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @param attachment $attachment
	 * @param string $download_package
	 * @param package $package
	 * @param template $template
	 * @return array Returns array containing any errors found.
	 */
	public function epv_test(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $download_package, package $package, template $template)
	{
		if ($revision->skip_epv)
		{
			// Skip EPV
			$results = $this->user->lang('SKIP_EPV_MESSAGE');
		}

		else
		{
			$package->ensure_extracted();
			$prevalidator = $this->get_prevalidator();
			$results = $prevalidator->run_epv($package->get_temp_path());
		}

		$uid = $bitfield = $flags = false;
		generate_text_for_storage($results, $uid, $bitfield, $flags, true, true, true);

		// Add the prevalidator results to the queue
		$queue = $revision->get_queue();
		$queue->mpv_results = $results;
		$queue->mpv_results_bitfield = $bitfield;
		$queue->mpv_results_uid = $uid;
		$queue->submit();

		$results = generate_text_for_display($results, $uid, $bitfield, $flags);
		$template->assign_var('PV_RESULTS', $results);

		return array();
	}

	/**
	 * Validate extension name.
	 *
	 * @param string $name
	 * @return bool Returns true if valid, false otherwise.
	 */
	public function validate_ext_name($name)
	{
		return (bool) preg_match(
			'#^[a-zA-Z0-9\x7f-\xff]{2,}/[a-zA-Z0-9\x7f-\xff]{2,}$#',
			$name
		);
	}

	/**
	 * Repack extension to add version check info and match
	 * correct directory structure to given ext name.
	 *
	 * @param package $package
	 * @param \titania_contribution $contrib
	 * @pram \titania_revision $revision
	 * @throws \Exception if an error occurred
	 */
	protected function repack(package $package, \titania_contribution $contrib, \titania_revision $revision)
	{
		$ext_base_path = $package->find_directory(
			array(
				'files' => array(
					'required' => 'composer.json',
					'optional' => 'ext.php',
				),
			),
			'vendor'
		);

		if ($ext_base_path === null)
		{
			throw new \Exception($this->root_not_found_key);
		}
		$composer_file = $package->get_temp_path() . '/' . $ext_base_path . '/composer.json';
		$data = $this->get_composer_data($composer_file);

		if (!is_array($data))
		{
			throw new \Exception('INVALID_COMPOSER_FILE');
		}
		if (empty($data['name']))
		{
			throw new \Exception('MISSING_EXT_NAME');
		}
		if (!$this->validate_ext_name($data['name']))
		{
			throw new \Exception('INVALID_EXT_NAME');
		}
		if (empty($data['version']))
		{
			throw new \Exception('MISSING_COMPOSER_VERSION');
		}
		if ($data['version'] !== $revision->revision_version)
		{
			throw new \Exception($this->user->lang('MISMATCH_COMPOSER_VERSION', $data['version'], $revision->revision_version));
		}
		if (!$this->is_stable_version($data['version']))
		{
			throw new \Exception('UNSTABLE_COMPOSER_VERSION');
		}

		$ext_name = $data['name'];
		$data['type'] = 'phpbb-extension';
		$data = $this->update_phpbb_requirement($data);
		$data = $this->set_version_check($data, $contrib);

		$data = json_encode(
			$data,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);

		file_put_contents($composer_file, $data);
		$package->restore_root($ext_base_path, $ext_name);
		$package->repack($this->clean_package);

		$revision->revision_composer_json = $data;
		$contrib->contrib_package_name = $ext_name;
		$contrib->submit();
	}

	/**
	 * Get data from composer.json
	 *
	 * @param string $file	Full path to composer.json
	 * @return mixed
	 */
	protected function get_composer_data($file)
	{
		$file = new \SplFileInfo($file);

		if (!$file->isReadable() || !$file->isWritable())
		{
			return null;
		}
		$data = file_get_contents($file->getPathname());

		if (!$data)
		{
			return null;
		}
		return json_decode($data, true);
	}

	/**
	 * Set version check info for composer.json
	 *
	 * @param array $data						composer.json data
	 * @param \titania_contribution $contrib
	 * @return array Returns $data array with version check info set
	 */
	protected function set_version_check(array $data, \titania_contribution $contrib)
	{
		$data['extra'] = (isset($data['extra']) && is_array($data['extra'])) ? $data['extra'] : array();
		unset($data['extra']['version_check']);
		$version_check_url = $contrib->get_url('version_check');

		$parts = parse_url($version_check_url);

		if ($parts !== false)
		{
			$directory = substr($parts['path'], 0, strrpos($parts['path'], '/'));
			$data['extra']['version-check'] = array(
				'host'		=> $parts['host'],
				'directory' => $directory,
				'filename'	=> substr($parts['path'], strlen($directory) + 1),
			);
		}
		return $data;
	}

	/**
	 * Updates phpBB requirements in composer.json
	 *
	 * @param array $data composer.json data
	 * @return array Returns $data array with phpBB requirement updated
	 */
	protected function update_phpbb_requirement(array $data)
	{
		if (!isset($data['require']['phpbb/phpbb']))
		{
			if (isset($data['extra']['soft-require']['phpbb/phpbb']))
			{
				$data['require']['phpbb/phpbb'] = $data['extra']['soft-require']['phpbb/phpbb'];
			}
		}

		if (isset($data['require']['phpbb/phpbb']))
		{
			// fix common error (<=3.2.*@dev, >=3.1.x)
			$data['require']['phpbb/phpbb'] = preg_replace('/(<|<=|~|\^|>|>=)([0-9]+(\.[0-9]+)?)\.[*x]/', '$1$2', $data['require']['phpbb/phpbb']);
		}

		// Composer installers must be required by all extensions in order to be installed correctly
		$data['require']['composer/installers'] = '~1.0.0';

		return $data;
	}

	/**
	 * Checks if the version is stable (1.2.3, 4.5.6-PL1) or not (7.8.9-RC1, 0.9.8)
	 *
	 * @param string $version
	 * @return bool
	 */
	protected function is_stable_version($version)
	{
		return preg_match('#^\d+\.\d+\.\d+(-pl\d+)?$#i', $version) === 1 && phpbb_version_compare($version, '1.0.0', '>=');
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
