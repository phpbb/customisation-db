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

if (!class_exists('titania_object'))
{
	require TITANIA_ROOT . 'includes/core/object.' . PHP_EXT;
}

/**
* Titania configuration
*
* @package Titania
*/
class titania_config extends titania_object
{
	/**
	 * Setup default configuration
	 */
	public function __construct()
	{
		$this->object_config = array_merge($this->object_config, array(
			'language_path'				=> array('default' => TITANIA_ROOT . 'language/'),
			'modules_path'				=> array('default' => TITANIA_ROOT . 'modules/'),
			'phpbb_root_path'			=> array('default' => '../community/'),
			'titania_script_path'		=> array('default' => 'titania/'),
			'phpbbcom_profile'			=> array('default' => true),
			'phpbbcom_viewprofile_url'	=> array('default' => 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=%u'),
			'table_prefix'				=> array('default' => 'customisation_'),
			'style'						=> array('default' => 'default'),

			'team_groups'				=> array('default' => array(5)),

			'max_rating'				=> array('default' => 5),
		));
	}

	/**
	 * Read configuration settings from assoc. array
	 */
	public function read_array($config)
	{
		$this->__set_array($config);
	}
}
