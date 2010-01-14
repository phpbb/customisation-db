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
			'phpbb_root_path'			=> array('default' => '../community/'),
			'titania_script_path'		=> array('default' => 'customisation/'),
			'upload_path'				=> array('default' => TITANIA_ROOT . 'files/'),
			'contrib_temp_path'			=> array('default' => TITANIA_ROOT . 'files/contrib_temp/'),
			'language_path'				=> array('default' => TITANIA_ROOT . 'language/'),

			'phpbbcom_profile'			=> array('default' => true),
			'phpbbcom_viewprofile_url'	=> array('default' => 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=%u'),

			'table_prefix'				=> array('default' => 'customisation_'),

			'style'						=> array('default' => 'default'),

			'team_groups'				=> array('default' => array(5)),

			'max_rating'				=> array('default' => 5),

			// Validation/queue related
			'require_validation'		=> array('default' => true),
			'use_queue'					=> array('default' => true),

			// Latest versions array
			'phpbb_versions'			=> array('default' => array(
				'30'	=> '3.0.6',  // We only support 3.0.x right now, will be changed in the future
			)),

			'mpv_server_list'			=> array('default' => array(
				array(
					'host'		=> 'mpv.davidiq.net',
					'directory'	=> '',
					'file'		=> 'index.php',
				),
			)),
		));
	}
}
