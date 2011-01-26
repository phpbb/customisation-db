<?php
/**
*
* @package Titania
* @copyright (c) 2011 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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

define('TITANIA_TYPE_BBCODE', 7);

class titania_type_bbcode extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 7;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'bbcode';

	// Validation messages (for the PM)
	public $validation_subject = 'BBCODE_VALIDATION';
	public $validation_message_approve = 'BBCODE_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'BBCODE_VALIDATION_MESSAGE_DENY';
	public $create_public = 'BBCODE_CREATE_PUBLIC';
	public $reply_public = 'BBCODE_REPLY_PUBLIC';
	public $update_public = 'BBCODE_UPDATE_PUBLIC';
//	public $upload_agreement = 'BBCODE_UPLOAD_AGREEMENT';

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['BBCODE'];
		$this->langs = phpbb::$user->lang['BBCODES'];
		$this->forum_database = titania::$config->forum_bbcode_database;
		$this->forum_robot = titania::$config->forum_bbcode_robot;
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
			// Can submit a bbcode
			case 'submit' :
				return true;
			break;

			// Can view the bbcode queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_bbcode_queue_discussion');
			break;

			// Can view the bbcode queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_bbcode_queue');
			break;

			// Can validate bbcodes in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_bbcode_validate');
			break;

			// Can moderate bbcodes
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_bbcode_moderate', 'u_titania_mod_contrib_mod'));
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

		if (!isset(phpbb::$config['titania_num_bbcodes']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_bbcode_queue_discussion',
				'u_titania_mod_bbcode_queue',
				'u_titania_mod_bbcode_validate',
				'u_titania_mod_bbcode_moderate',
			));

			// bbcode count holder
			$umil->config_add('titania_num_bbcodes', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_bbcodes', ++phpbb::$config['titania_num_bbcodes'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_bbcodes', --phpbb::$config['titania_num_bbcodes'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_bbcodes'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_bbcodes']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'u_titania_mod_bbcode_queue_discussion',
				'u_titania_mod_bbcode_queue',
				'u_titania_mod_bbcode_validate',
				'u_titania_mod_bbcode_moderate',
			));

			// bbcode count holder
			$umil->config_remove('titania_num_bbcodes');
		}
	}
}