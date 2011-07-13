<?php
/**
*
* @package Titania
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
			'phpbb_script_path'			=> array('default' => 'community/'),
			'titania_script_path'		=> array('default' => 'customisation/'),
			'upload_path'				=> array('default' => TITANIA_ROOT . 'files/'),
			'contrib_temp_path'			=> array('default' => TITANIA_ROOT . 'files/contrib_temp/'),
			'modx_storage_path'			=> array('default' => TITANIA_ROOT . 'files/modx_files/'),
			'language_path'				=> array('default' => TITANIA_ROOT . 'language/'),
			'table_prefix'				=> array('default' => 'customisation_'),

			// Increment the user's post count?  Array of the post_types for which we will increment the post count
			'increment_postcount'		=> array('default' => array(TITANIA_SUPPORT)),

			// Path to demo board we will install styles on
			'demo_style_path'			=> array('default' => ''),
			'demo_style_url'			=> array('default' => ''),

			// Allow non-team members to modify the style demo URL?
			'can_modify_style_demo_url'	=> array('default' => true),

			// Style to display
			'style'						=> array('default' => 'default'),

			// Groups who receive TITANIA_ACCESS_TEAMS level auth
			'team_groups'				=> array('default' => array(5)),

			// Maximum rating allowed when rating stuff
			'max_rating'				=> array('default' => 5),

			'phpbbcom_profile'			=> array('default' => true),
			'phpbbcom_viewprofile_url'	=> array('default' => 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=%u'),

			// Mod/style database release forums (receive announcements on updates/approval)
			'forum_mod_database'		=> array('default' => 0),
			'forum_style_database'		=> array('default' => 0),
			'forum_converter_database'	=> array('default' => 0),

			// Accounts to use for posting in the forum
			'forum_mod_robot'			=> array('default' => 0),
			'forum_style_robot'			=> array('default' => 0),
			'forum_converter_robot'		=> array('default' => 0),

			// Show the support/discussion panel to the public?
			'support_in_titania'		=> array('default' => true),

			// Display backtrace? 0 = never, 1 = for administrators, 2 = for TITANIA_ACCESS_TEAMS, 3 = for all
			'display_backtrace'			=> array('default' => 2),

			// Search backend (zend or solr (if solr, set the correct ip/port))
			'search_backend'			=> array('default' => 'zend'),
			'search_backend_ip'			=> array('default' => 'localhost'),
			'search_backend_port'		=> array('default' => 8983),

			// Validation/queue related
			'require_validation'		=> array('default' => true),
			'use_queue'					=> array('default' => true),
			'allow_self_validation'		=> array('default' => true),

			// phpBB versions array
			'phpbb_versions'			=> array('default' => array(
				'20'	=> array('latest_revision' => '23', 'name' => 'phpBB 2.0.x', 'allow_uploads' => false),
				'30'	=> array('latest_revision' => '8', 'name' => 'phpBB 3.0.x', 'allow_uploads' => true),
			)),

			// MPV server(s)
			'mpv_server_list'			=> array('default' => array(
				array(
					'host'		=> 'mpv.phpbb.com',
					'directory'	=> '',
					'file'		=> 'index.php',
				),
			)),

			// ColorizeIt
            'colorizeit'                => array('default' => ''),
            'colorizeit_url'            => array('default' => 'www.colorizeit.com'),
            'colorizeit_auth'           => array('default' => 'HEADER'),
            'colorizeit_var'            => array('default' => 'X-Colorizeit'),
            'colorizeit_value'          => array('default' => '1'),

			/**
			* Attachments -------
			*/
			'upload_max_filesize'		=> array('default' => array(
				TITANIA_CONTRIB		=> 10485760, // 10 MiB
				TITANIA_SCREENSHOT	=> 524288, // 512 Kib
				TITANIA_TRANSLATION	=> 1048576, // 1 Mib
				TITANIA_CLR_SCREENSHOT  => 131072, // 128 Kib
			)),

			// Extensions allowed
			'upload_allowed_extensions'	=> array('default' => array(
				TITANIA_CONTRIB				=> array('zip'),
				TITANIA_SCREENSHOT			=> array('jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'),
				TITANIA_TRANSLATION			=> array('zip'),
				TITANIA_SUPPORT				=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				TITANIA_QUEUE				=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				TITANIA_QUEUE_DISCUSSION	=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				TITANIA_FAQ					=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				// ColorizeIt sample image
				TITANIA_CLR_SCREENSHOT      => array('gif'),
			)),

			// Attachment directory names
			'upload_directory'	=> array('default' => array(
				TITANIA_CONTRIB				=> 'revisions',
				TITANIA_SCREENSHOT			=> 'screenshots',
				TITANIA_TRANSLATION			=> 'translations',
				TITANIA_SUPPORT				=> 'support',
				TITANIA_QUEUE				=> 'queue',
				TITANIA_QUEUE_DISCUSSION	=> 'queue_discussion',
				TITANIA_FAQ					=> 'faq',
				TITANIA_CLR_SCREENSHOT      => 'colorizeit',
			)),
			
		));
	}
}
