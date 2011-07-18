<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_type_base'))
{
	include(TITANIA_ROOT . 'includes/types/base.' . PHP_EXT);
}

if (!class_exists('translation_validation'))
{
	include(TITANIA_ROOT . 'includes/library/translations/translation_validation.' . PHP_EXT);
}

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

	public $validate_translation = true;

	/* Extra upload files disabled on Translation revisions */
	public $extra_upload = false;

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['TRANSLATION'];
		$this->langs = phpbb::$user->lang['TRANSLATIONS'];

		if ($this->validate_translation)
		{
			$this->upload_steps[] = array('contrib_type', 'translation_validate');
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

	/**
	* Automatically install the type if required
	*
	* For adding type specific permissions, etc.
	*/
	public function auto_install()
	{
		// If you change anything in here, remember to add the reverse to the uninstall() function below!

		if (!isset(phpbb::$config['titania_num_translations']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_translation_queue_discussion',
				'u_titania_mod_translation_queue',
				'u_titania_mod_translation_validate',
				'u_titania_mod_translation_moderate',
			));

			// Translation count holder
			$umil->config_add('titania_num_translations', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_translations', ++phpbb::$config['titania_num_translations'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_translations', --phpbb::$config['titania_num_translations'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_translations'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_translations']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'u_titania_mod_translation_queue_discussion',
				'u_titania_mod_translation_queue',
				'u_titania_mod_translation_validate',
				'u_titania_mod_translation_moderate',
			));

			// Translation count holder
			$umil->config_remove('titania_num_translations');
		}
	}

	public function translation_validate(&$contrib, &$revision, &$revision_attachment, &$contrib_tools, $download_package)
	{
		$new_dir_name = $contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version));
		$validation_tools = new translation_validation($contrib_tools->original_zip, $new_dir_name);

		$sql = 'SELECT row_id, phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			WHERE revision_id = ' . $revision->revision_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$version_string = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' .$row['phpbb_version_revision'];
			$reference_filepath = $validation_tools->automod_phpbb_files($version_string); // path to files against which we will validate the package
		}

		$errors = $validation_tools->check_package($reference_filepath);

		if (!empty($errors))
		{
			trigger_error(implode('<br /><br />', $errors));
		}

		$validation_tools->remove_temp_files();

		phpbb::$template->assign_var('S_PASSED_TRANSLATION_VALIDATION', true);
	}
}
