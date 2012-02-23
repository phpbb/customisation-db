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

define('TITANIA_TYPE_CONVERTER', 3);

class titania_type_converter extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 3;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'converter';

	// Validation messages (for the PM)
	public $validation_subject = 'CONVERTER_VALIDATION';
	public $validation_message_approve = 'CONVERTER_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'CONVERTER_VALIDATION_MESSAGE_DENY';

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['CONVERTER'];
		$this->langs = phpbb::$user->lang['CONVERTERS'];
		$this->forum_database = titania::$config->forum_converter_database;
		$this->forum_robot = titania::$config->forum_converter_robot;
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
			// Can submit a converter
			case 'submit' :
				return true;
			break;

			// Can view the convertor queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_converter_queue_discussion');
			break;

			// Can view the convertor queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_converter_queue');
			break;

			// Can validate convertors in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_converter_validate');
			break;

			// Can moderate convertors
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_converter_moderate', 'u_titania_mod_contrib_mod'));
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

		if (!isset(phpbb::$config['titania_num_converters']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_converter_queue_discussion',
				'u_titania_mod_converter_queue',
				'u_titania_mod_converter_validate',
				'u_titania_mod_converter_moderate',
			));

			// Converter count holder
			$umil->config_add('titania_num_converters', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_converters', ++phpbb::$config['titania_num_converters'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_converters', --phpbb::$config['titania_num_converters'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_converters'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_converters']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'u_titania_mod_converter_queue_discussion',
				'u_titania_mod_converter_queue',
				'u_titania_mod_converter_validate',
				'u_titania_mod_converter_moderate',
			));

			// Converter count holder
			$umil->config_remove('titania_num_converters');
		}
	}
}
