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

define('TITANIA_TYPE_TRANSLATION', 6);

class titania_type_translation extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 6;
	
	/**
	 * The type name
	 *
	 * @var string (any lang key that includes the type should match this value)
	 */
	public $name = 'translation';

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'translation';

	// Validation messages (for the PM)
	public $validation_subject = 'TRANSLATION_VALIDATION';
	public $validation_message_approve = 'TRANSLATION_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'TRANSLATION_VALIDATION_MESSAGE_DENY';

	public $root_search = array(array('language', 'is_directory', 'is_exactly'));
	public $clean_and_restore_root = true;

	public $root_not_found_key = 'COULD_NOT_FIND_TRANSLATION_ROOT';

	public $prerelease_submission_allowed = true;

	public $validate_translation = true;

	/* Extra upload files disabled on Translation revisions */
	public $extra_upload = false;

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['TRANSLATION'];
		$this->langs = phpbb::$user->lang['TRANSLATIONS'];

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
			// Can submit a translation
			case 'submit' :
				return true;
			break;

			// Can view the translation queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_translation_queue_discussion');
			break;

			// Can view the translation queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_translation_queue');
			break;

			// Can validate tranlations in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_translation_validate');
			break;

			// Can moderate translations
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_translation_moderate', 'u_titania_mod_contrib_mod'));
			break;
		}

		return false;
	}

	public function translation_validate(&$contrib, &$revision, &$revision_attachment, &$contrib_tools, $download_package)
	{
		if (empty($revision->phpbb_versions))
		{
			$revision->load_phpbb_versions();
		}

		$version = $revision->phpbb_versions[0];

		if ($version['phpbb_version_branch'] != 30)
		{
			return array();
		}
		// If the revision is on hold, it's being submitted for a future version.
		if ($revision->revision_status == TITANIA_REVISION_ON_HOLD)
		{
			$version['phpbb_version_revision'] = titania::$config->prerelease_phpbb_version[$version['phpbb_version_branch']];
		}
		$version_string = $version['phpbb_version_branch'][0] . '.' . $version['phpbb_version_branch'][1] . '.' . $version['phpbb_version_revision'];

		\titania::_include('library/translations/translation_validation', false, 'translation_validation');

		$new_dir_name = $contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version));
		$validation_tools = new translation_validation($contrib_tools->original_zip, $new_dir_name);

		$reference_filepath = $validation_tools->automod_phpbb_files($version_string); // path to files against which we will validate the package

		if (!empty($validation_tools->error))
		{
			return array('error' => implode('<br /><br />', $validation_tools->error));
		}

		$errors = $validation_tools->check_package($reference_filepath);

		if (!empty($errors))
		{
			return array('error' => implode('<br /><br />', $errors));
		}

		$validation_tools->remove_temp_files();

		phpbb::$template->assign_var('S_PASSED_TRANSLATION_VALIDATION', true);

		return array();
	}

	/**
	* @{inheritDoc}
	*/
	public function validate_contrib_fields($fields)
	{
		$error = array();

		if (empty($fields['contrib_iso_code']))
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_ISO_CODE'];
		}
		if (empty($fields['contrib_local_name']))
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_LOCAL_NAME'];
		}

		return $error;
	}
}
