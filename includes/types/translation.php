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

	public $root_search = array('directories' => array('required' => 'language'));
	public $restore_root = true;
	public $clean_package = true;

	public $root_not_found_key = 'COULD_NOT_FIND_TRANSLATION_ROOT';

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

	public function translation_validate(&$contrib, &$revision, &$revision_attachment, $download_package, &$package)
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
		$version_string = $version['phpbb_version_branch'][0] . '.' . $version['phpbb_version_branch'][1] . '.' . $version['phpbb_version_revision'];

		$prevalidator = $this->get_prevalidator();

		$reference_filepath = $prevalidator->get_helper()->prepare_phpbb_test_directory($version_string); // path to files against which we will validate the package
		$errors = $prevalidator->get_helper()->get_errors();

		if (!empty($errors))
		{
			return array('error' => implode('<br /><br />', $errors));
		}

		$errors = $prevalidator->check_package($package, $reference_filepath);

		if (!empty($errors))
		{
			return array('error' => $errors);
		}

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

	/**
	 * @{inheritDoc}
	 */
	public function get_prevalidator()
	{
		return phpbb::$container->get('phpbb.titania.translation.prevalidator');
	}
}
