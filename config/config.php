<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\titania\config;

use phpbb\titania\ext;

class config extends \phpbb\titania\entity\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $ext_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config $config
	 * @param string $ext_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\config\config $config, $ext_root_path, $php_ext)
	{
		$this->config = $config;
		$this->ext_root_path = $ext_root_path;
		$this->php_ext = $php_ext;

		$this->set_default_config();
		$this->set_custom_config();
	}

	/**
	 * Set default configuration properties.
	 */
	public function set_default_config()
	{
		$this->object_config = array_merge($this->object_config, array(
			'phpbb_script_path'			=> array('default' => 'community/'),
			'titania_script_path'		=> array('default' => 'customisation/'),
			'upload_path'				=> array('default' => $this->ext_root_path . 'files/'),
			'contrib_temp_path'			=> array('default' => $this->ext_root_path . 'files/contrib_temp/'),
			'language_path'				=> array('default' => $this->ext_root_path . 'language/'),
			'table_prefix'				=> array('default' => 'cdb_'),

			// Increment the user's post count? Array of the post_types for which we will increment the post count
			'increment_postcount'		=> array('default' => array(ext::TITANIA_SUPPORT)),

			// Path to demo board we will install styles on
			'demo_style_path'			=> array('default' => array(
				'30'	=> '',
				'31'	=> '',
				'32'	=> '',
			)),

			// URL for style demo board management hook
			'demo_style_hook'			=> array('default' => array(
				'30'	=> '',
				'31'	=> '',
				'32'	=> '',
			)),

			// Demo board URL
			'demo_style_url'			=> array('default' => array(
				'30'	=> '',
				'31'	=> '',
				'32'	=> '',
			)),

			// Allow non-team members to modify the style demo URL?
			'can_modify_style_demo_url'	=> array('default' => true),

			// Style to display
			'style'						=> array('default' => 'prosilver'),

			// Use theme from a different style
			'theme'						=> array('default' => ''),

			// Groups who receive TITANIA_ACCESS_TEAMS level auth
			'team_groups'				=> array('default' => array(5)),

			// Maximum rating allowed when rating stuff
			'max_rating'				=> array('default' => 5),

			'phpbbcom_profile'			=> array('default' => true),
			'phpbbcom_viewprofile_url'	=> array('default' => 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile&amp;u=%u'),

			// Mod/style database release forums (receive announcements on updates/approval)
			'forum_mod_database'		=> array('default' => array(
				'30'	=> 0,
			)),
			'forum_style_database'		=> array('default' => array(
				'30'	=> 0,
				'31'	=> 0,
				'32'	=> 0,
			)),
			'forum_converter_database'	=> array('default' => array(
				'30'	=> 0,
				'31'	=> 0
			)),
			'forum_bbcode_database'		=> array('default' => array(
				'30'	=> 0,
				'31'	=> 0,
			)),
			'forum_extension_database'	=> array('default' => array(
				'31'	=> 0,
				'32'	=> 0,
			)),

			// Accounts to use for posting in the forum
			'forum_mod_robot'			=> array('default' => 0),
			'forum_style_robot'			=> array('default' => 0),
			'forum_converter_robot'		=> array('default' => 0),
			'forum_bbcode_robot'		=> array('default' => 0),
			'forum_extension_robot'		=> array('default' => 0),

			// Show the support/discussion panel to the public?
			'support_in_titania'		=> array('default' => true),

			// Display backtrace? 0 = never, 1 = for administrators, 2 = for TITANIA_ACCESS_TEAMS, 3 = for all
			'display_backtrace'			=> array('default' => 2),

			'search_enabled'			=> array('default' => true),

			// Search backend (zend or solr (if solr, set the correct ip/port))
			'search_backend'			=> array('default' => 'solr'),
			'search_backend_ip'			=> array('default' => 'localhost'),
			'search_backend_port'		=> array('default' => 8983),

			// Validation/queue related
			'require_validation'		=> array('default' => true),
			'use_queue'					=> array('default' => true),
			'allow_self_validation'		=> array('default' => true),

			// File extensions that are included in a repack diff
			'repack_diff_extensions'	=> array('default' => array(
				'php',
				'html', 'htm',
				'js', 'css',
				'cfg', 'json', 'yml', 'txt',
			)),

			// phpBB versions array
			'phpbb_versions'			=> array('default' => array(
				'20'	=> array('latest_revision' => '23', 'name' => 'phpBB 2.0.x', 'allow_uploads' => false),
				'30'	=> array('latest_revision' => '14', 'name' => 'phpBB 3.0.x', 'allow_uploads' => false),
				'31'	=> array('latest_revision' => '12', 'name' => 'phpBB 3.1.x', 'allow_uploads' => true),
				'32'	=> array('latest_revision' => '2', 'name' => 'phpBB 3.2.x', 'allow_uploads' => true),
			)),

			// MPV server(s)
			'mpv_server_list'			=> array('default' => array(
				array(
					'host'		=> 'mpv.phpbb.com',
					'ip'		=> '140.211.15.224',
					'directory'	=> '',
					'file'		=> 'index.php',
				),
			)),

			// ColorizeIt
			'colorizeit'		=> array('default' => ''),
			'colorizeit_url'	=> array('default' => 'www.colorizeit.com'),
			'colorizeit_auth'	=> array('default' => 'HEADER'),
			'colorizeit_var'	=> array('default' => 'X-Colorizeit'),
			'colorizeit_value'	=> array('default' => '1'),

			/**
			 * Attachments -------
			 */
			'upload_max_filesize'		=> array('default' => array(
				ext::TITANIA_CONTRIB			=> 10485760,	// 10 MiB
				ext::TITANIA_SCREENSHOT			=> 524288,		// 512 Kib
				ext::TITANIA_TRANSLATION		=> 1048576,		// 1 Mib
				ext::TITANIA_CLR_SCREENSHOT		=> 131072,		// 128 Kib
			)),

			// Extensions allowed
			'upload_allowed_extensions'	=> array('default' => array(
				ext::TITANIA_CONTRIB			=> array('zip'),
				ext::TITANIA_SCREENSHOT			=> array('jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'),
				ext::TITANIA_TRANSLATION		=> array('zip'),
				ext::TITANIA_SUPPORT			=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				ext::TITANIA_QUEUE				=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				ext::TITANIA_QUEUE_DISCUSSION	=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				ext::TITANIA_FAQ				=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff'
				),
				// ColorizeIt sample image
				ext::TITANIA_CLR_SCREENSHOT		=> array('gif'),
			)),

			// Attachment directory names
			'upload_directory'	=> array('default' => array(
				ext::TITANIA_CONTRIB			=> 'revisions',
				ext::TITANIA_SCREENSHOT			=> 'screenshots',
				ext::TITANIA_TRANSLATION		=> 'translations',
				ext::TITANIA_SUPPORT			=> 'support',
				ext::TITANIA_QUEUE				=> 'queue',
				ext::TITANIA_QUEUE_DISCUSSION	=> 'queue_discussion',
				ext::TITANIA_FAQ				=> 'faq',
				ext::TITANIA_CLR_SCREENSHOT		=> 'colorizeit',
			)),

			// Remove unsubmitted revisions and attachments
			'cleanup_titania'				=> array('default' => false),
		));
	}

	/**
	 * Set custom configuration property values.
	 */
	public function set_custom_config()
	{
		$this->set_from_file();
		$this->set_from_phpbb_config();
	}

	/**
	 * Set configuration values from file.
	 */
	protected function set_from_file()
	{
		$custom_config_file = $this->ext_root_path . 'config.' . $this->php_ext;

		if (file_exists($custom_config_file))
		{
			include($custom_config_file);

			if (!isset($config) || !is_array($config))
			{
				$config = array();
			}

			$this->__set_array($config);
		}
	}

	/**
	 * Set configuration values from phpBB's config.
	 */
	protected function set_from_phpbb_config()
	{
	}
}
