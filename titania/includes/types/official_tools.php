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

define('TITANIA_TYPE_OFFICIAL_TOOL', 4);

class titania_type_official_tools extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 4;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'official_tool';

	// Official tools do not require validation (only team members can submit them)
	public $require_validation = false;
	public $use_queue = false;

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['OFFICIAL_TOOL'];
		$this->langs = phpbb::$user->lang['OFFICIAL_TOOLS'];
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
			// No queue for the official tools
			case 'queue_discussion' :
			case 'view' :
			case 'validate' :
				return false;
			break;

			case 'submit' :
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_official_tool_moderate', 'u_titania_mod_contrib_mod', 'u_titania_admin'));
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

		if (!isset(phpbb::$config['titania_num_official_tools']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_official_tool_moderate',
			));

			// Offical Tool count holder
			$umil->config_add('titania_num_official_tools', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_official_tools', ++phpbb::$config['titania_num_official_tools'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_official_tools', --phpbb::$config['titania_num_official_tools'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_official_tools'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_official_tools']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'u_titania_mod_official_tool_moderate',
			));

			// Offical Tool count holder
			$umil->config_remove('titania_num_official_tools');
		}
	}
}
