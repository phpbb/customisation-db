<?php
/**
*
* @package Titania
* @version $Id$
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

class titania_type_mod extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 1;

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

	/**
	* Run MPV/Automod Test for this type?
	*/
	public $mpv_test = true;
	public $automod_test = true;
	public $clean_and_restore_root = true;

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['MODIFICATION'];
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
			// Can view the mod queue
			case 'view' :
				return phpbb::$auth->acl_get('m_titania_mod_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('m_titania_mod_validate');
			break;

			// Can moderate mods
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('m_titania_mod_moderate', 'm_titania_contrib_mod'));
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

		if (!isset(phpbb::$config['titania_num_mods']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'm_titania_mod_queue',
				'm_titania_mod_validate',
				'm_titania_mod_moderate',
			));

			// Mod count holder
			$umil->config_add('titania_num_mods', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_mods', ++phpbb::$config['titania_num_mods'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_mods', --phpbb::$config['titania_num_mods'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_mods'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_mods']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'm_titania_mod_queue',
				'm_titania_mod_validate',
				'm_titania_mod_moderate',
			));

			// Mod count holder
			$umil->config_remove('titania_num_mods');
		}
	}
}
