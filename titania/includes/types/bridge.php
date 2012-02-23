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

define('TITANIA_TYPE_BRIDGE', 5);

class titania_type_bridge extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 5;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'bridge';

	// Validation messages (for the PM)
	public $validation_subject = 'BRIDGE_VALIDATION';
	public $validation_message_approve = 'BRIDGE_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'BRIDGE_VALIDATION_MESSAGE_DENY';

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['BRIDGE'];
		$this->langs = phpbb::$user->lang['BRIDGES'];
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
			// Can submit a bridge
			case 'submit' :
				return true;
			break;

			// Can view the bridge queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_bridge_queue_discussion');
			break;

			// Can view the bridge queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_bridge_queue');
			break;

			// Can validate bridges in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_bridge_validate');
			break;

			// Can moderate bridges
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_bridge_moderate', 'u_titania_mod_contrib_mod'));
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

		if (!isset(phpbb::$config['titania_num_bridges']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_bridge_queue_discussion',
				'u_titania_mod_bridge_queue',
				'u_titania_mod_bridge_validate',
				'u_titania_mod_bridge_moderate',
			));

			// bridge count holder
			$umil->config_add('titania_num_bridges', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_bridges', ++phpbb::$config['titania_num_bridges'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_bridges', --phpbb::$config['titania_num_bridges'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_bridges'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_bridges']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'u_titania_mod_bridge_queue_discussion',
				'u_titania_mod_bridge_queue',
				'u_titania_mod_bridge_validate',
				'u_titania_mod_bridge_moderate',
			));

			// bridge count holder
			$umil->config_remove('titania_num_bridges');
		}
	}
}
