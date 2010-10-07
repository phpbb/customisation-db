<?php
/**
*
* @package Titania
* @version $Id: _translation.php 1623 2010-07-29 04:31:14Z exreaction $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['TRANSLATION'];
		$this->langs = phpbb::$user->lang['TRANSLATIONS'];
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

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_translation_queue_discussion');
			break;

			// Can view the mod queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_translation_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_translation_validate');
			break;

			// Can moderate mods
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

			// Converter count holder
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

			// Converter count holder
			$umil->config_remove('titania_num_translations');
		}
	}
}
