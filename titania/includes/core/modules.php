<?php
/**
 *
 * @package titania
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

if (!class_exists('p_master'))
{
	require(PHPBB_ROOT_PATH . 'includes/functions_module.' . PHP_EXT);
}

/**
 * @package modules
 */
class titania_modules extends p_master
{
	/**
	 * Constructor to set the custom module include path
	 */
	public function __construct()
	{
		$this->set_custom_include_path(titania::$config->modules_path);
	}

	/**
	* Check module authorisation
	*
	* Addition of the titania_access_ auth check
	*/
	public function module_auth($module_auth, $forum_id = false)
	{
		global $auth, $config;

		$module_auth = trim($module_auth);

		// Generally allowed to access module if module_auth is empty
		if (!$module_auth)
		{
			return true;
		}

		// With the code below we make sure only those elements get eval'd we really want to be checked
		preg_match_all('/(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
			\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
			[(),]                                  |
			[^\s(),]+)/x', $module_auth, $match);

		$tokens = $match[0];
		for ($i = 0, $size = sizeof($tokens); $i < $size; $i++)
		{
			$token = &$tokens[$i];

			switch ($token)
			{
				case ')':
				case '(':
				case '&&':
				case '||':
				case ',':
				break;

				default:
					if (!preg_match('#(?:titania_access_([0-9]+))|(?:acl_([a-z0-9_]+)(,\$id)?)|(?:\$id)|(?:aclf_([a-z0-9_]+))|(?:cfg_([a-z0-9_]+))|(?:request_([a-zA-Z0-9_]+))#', $token))
					{
						$token = '';
					}
				break;
			}
		}

		$module_auth = implode(' ', $tokens);

		// Make sure $id seperation is working fine
		$module_auth = str_replace(' , ', ',', $module_auth);

		$forum_id = ($forum_id === false) ? $this->acl_forum_id : $forum_id;

		$is_auth = false;
		eval('$is_auth = (int) (' . preg_replace(array('#titania_access_([0-9]+)#', '#acl_([a-z0-9_]+)(,\$id)?#', '#\$id#', '#aclf_([a-z0-9_]+)#', '#cfg_([a-z0-9_]+)#', '#request_([a-zA-Z0-9_]+)#'), array('\\1 >= titania::$access_level','(int) $auth->acl_get(\'\\1\'\\2)', '(int) $forum_id', '(int) $auth->acl_getf_global(\'\\1\')', '(int) $config[\'\\1\']', '!empty($_REQUEST[\'\\1\'])'), $module_auth) . ');');

		return $is_auth;
	}
}
