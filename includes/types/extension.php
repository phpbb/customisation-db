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

define('TITANIA_TYPE_EXTENSION', 8);

class titania_type_extension extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 8;

	/**
	 * The type name
	 *
	 * @var string (any lang key that includes the type should match this value)
	 */
	public $name = 'extension';

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'extension';

	// Validation messages (for the PM)
	public $validation_subject = 'EXTENSION_VALIDATION';
	public $validation_message_approve = 'EXTENSION_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'EXTENSION_VALIDATION_MESSAGE_DENY';
	public $create_public = 'EXTENSION_CREATE_PUBLIC';
	public $reply_public = 'EXTENSION_REPLY_PUBLIC';
	public $update_public = 'EXTENSION_UPDATE_PUBLIC';
	public $upload_agreement = 'EXTENSION_UPLOAD_AGREEMENT';
	public $epv_test = true;
	public $clean_package = true;
	public $root_not_found_key = 'COULD_NOT_FIND_EXT_ROOT';

	public $allowed_branches = array('>=', 31);
	public function __construct()
	{
		$this->lang = phpbb::$user->lang['EXTENSION'];
		$this->langs = phpbb::$user->lang['EXTENSIONS'];
		$this->forum_database = titania::$config->forum_extension_database;
		$this->forum_robot = titania::$config->forum_extension_robot;

		if (titania::$config->use_queue && $this->use_queue && $this->epv_test)
		{
			$this->upload_steps[] = array(
				'name'		=> 'EVP_TEST',
				'function'	=> array($this, 'epv_test'),
			);
		}
	}

	/**
	* Check auth level
	*
	* @param string $auth ('view', 'test', 'validate')
	* @return bool
	*/
	public function acl_get($auth)
	{
		switch ($auth)
		{
			// Can submit a mod
			case 'submit' :
				return true;
			break;

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_extension_queue_discussion');
			break;

			// Can view the extensions queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_extension_queue');
			break;

			// Can validate extensions in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_extension_validate');
			break;

			// Can moderate extensions
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_extension_moderate', 'u_titania_mod_contrib_mod'));
			break;
		}

		return false;
	}

	/**
	* Run EPV test on new submissions and submit results to queue topic.
	*
	* @return array
	*/
	public function epv_test(&$contrib, &$revision, &$revision_attachment, $download_package, &$package)
	{
		try
		{
			$this->repack($package, $contrib);
		}
		catch (\Exception $e)
		{
			return array(
				'error'	=> array(phpbb::$user->lang($e->getMessage())),
			);
		}
		$prevalidator = $this->get_prevalidator();
		$results = $prevalidator->run_epv($package->get_temp_path());

		$uid = $bitfield = $flags = false;
		generate_text_for_storage($results, $uid, $bitfield, $flags, true, true, true);

		// Add the prevalidator results to the queue
		$queue = $revision->get_queue();
		$queue->mpv_results = $results;
		$queue->mpv_results_bitfield = $bitfield;
		$queue->mpv_results_uid = $uid;
		$queue->submit();

		$results = generate_text_for_display($results, $uid, $bitfield, $flags);
		phpbb::$template->assign_var('PV_RESULTS', $results);

		return array();
	}

	/**
	 * Repack extension to add version check info and match
	 * correct directory structure to given ext name.
	 *
	 * @param \phpbb\titania\entity\package $package
	 * @param \titania_contribution $contrib
	 * @throw Throws \Exception if an error occurred
	 */
	protected function repack($package, $contrib)
	{
		$package->ensure_extracted();
		$ext_base_path = $package->find_directory(
			array('files' => array(
				'required' => 'composer.json',
				'optional' => 'ext.php',
			)),
			'vendor'
		);

		if ($ext_base_path === null)
		{
			throw new \Exception($this->root_not_found_key);
		}
		$composer_file = $package->get_temp_path() . '/' . $ext_base_path . '/composer.json';
		$data = $this->get_composer_data($composer_file);

		if (!is_array($data) || empty($data['name']) || !preg_match('#^[a-zA-Z0-9\x7f-\xff]{2,}/[a-zA-Z0-9\x7f-\xff]{2,}$#', $data['name']))
		{
			throw new \Exception('INVALID_EXT_NAME');
		}

		$ext_name = $data['name'];
		$data = json_encode(
			$this->set_version_check($data, $contrib),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);

		file_put_contents($composer_file, $data);
		$package->restore_root($ext_base_path, $ext_name);
		$package->repack($this->clean_package);
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
			return;
		}
		$data = file_get_contents($file->getPathname());

		if (!$data)
		{
			return;
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
	protected function set_version_check($data, $contrib)
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
	 * @{inheritDoc}
	 */
	public function get_prevalidator()
	{
		return phpbb::$container->get('phpbb.titania.extension.prevalidator');
	}
}
