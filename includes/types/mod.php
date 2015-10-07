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

define('TITANIA_TYPE_MOD', 1);

class titania_type_mod extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 1;

	/**
	 * The type name
	 *
	 * @var string (any lang key that includes the type should match this value)
	 */
	public $name = 'mod';

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'mod';

	/**
	 * The name of the field used to hold the number of this item in the authors table
	 *
	 * @var string author count
	 */
	public $author_count = 'author_mods';

	// Validation messages (for the PM)
	public $validation_subject = 'MOD_VALIDATION';
	public $validation_message_approve = 'MOD_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'MOD_VALIDATION_MESSAGE_DENY';
	public $create_public = 'MOD_CREATE_PUBLIC';
	public $reply_public = 'MOD_REPLY_PUBLIC';
	public $update_public = 'MOD_UPDATE_PUBLIC';
	public $upload_agreement = 'MOD_UPLOAD_AGREEMENT';

	public $allowed_branches = array('<=', 30);

	/**
	* Run MPV/Automod Test for this type?
	*/
	public $mpv_test = true;
	public $automod_test = true;
	public $restore_root = true;
	public $clean_package = true;
	public $root_search = array('files' => array('required' => 'install*.xml'));

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['MODIFICATION'];
		$this->langs = phpbb::$user->lang['MODIFICATIONS'];
		$this->forum_database = titania::$config->forum_mod_database;
		$this->forum_robot = titania::$config->forum_mod_robot;

		if (titania::$config->use_queue && $this->use_queue)
		{
			if ($this->mpv_test)
			{
				$this->upload_steps[] = array(
					'name'		=> 'MVP_TEST',
					'function'	=> array($this, 'mpv_test'),
				);
			}

			if ($this->automod_test)
			{
				$this->upload_steps[] = array(
					'name'		=> 'AUTOMOD_TEST',
					'function'	=> array($this, 'automod_test'),
				);
			}
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
				return false;
			break;

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_modification_queue_discussion');
			break;

			// Can view the mod queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_modification_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_modification_validate');
			break;

			// Can moderate mods
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_modification_moderate', 'u_titania_mod_contrib_mod'));
			break;
		}

		return false;
	}

	public function mpv_test(&$contrib, &$revision, &$revision_attachment, $download_package, $package)
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
			phpbb::$template->assign_var('PV_RESULTS', $mpv_results);

			phpbb::$template->assign_var('S_AUTOMOD_TEST', $this->automod_test);
		}
	}

	public function automod_test(&$contrib, &$revision, &$revision_attachment, $download_package, $package)
	{
		$package->ensure_extracted();
		$prevalidator = $this->get_prevalidator();

		// Automod testing time
		$details = '';
		$error = $html_results = $bbcode_results = array();
		$sql = 'SELECT row_id, phpbb_version_branch, phpbb_version_revision
			FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			WHERE revision_id = ' . $revision->revision_id;
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$version_string = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' .$row['phpbb_version_revision'];
			$phpbb_path = $prevalidator->get_helper()->prepare_phpbb_test_directory($version_string);

			if ($phpbb_path === false)
			{
				$error = array_merge($error, $prevalidator->get_helper()->get_errors());
				continue;
			}

			phpbb::$template->assign_vars(array(
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
		phpbb::$db->sql_freeresult($result);

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

		phpbb::$template->assign_var('AUTOMOD_RESULTS', $html_results);

		return array(
			'error'	=> $error,
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function get_prevalidator()
	{
		return phpbb::$container->get('phpbb.titania.mod.prevalidator');
	}
}
