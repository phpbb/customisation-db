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
	require(TITANIA_ROOT . 'includes/class_base_object.' . PHP_EXT);
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
			'phpbbcom_profile'			=> array('default' => true),
			'phpbbcom_viewprofile_url'	=> array('default' => 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=%u'),
			'table_prefix'				=> array('default' => 'customisation_'),
			'template_path'				=> array('default' => TITANIA_ROOT . 'template/'),
			'theme_path'				=> array('default' => TITANIA_ROOT . 'theme/'),
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

?>