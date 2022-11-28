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

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var string */
	protected $ext_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config  $config
	 * @param \phpbb\config\db_text $config_text
	 * @param string                $ext_root_path
	 * @param string                $php_ext
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\config\db_text $config_text, $ext_root_path, $php_ext)
	{
		$this->config = $config;
		$this->config_text = $config_text;
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
			'table_prefix'				=> array('default' => 'customisation_'),
			'site_home_url'				=> array('default' => 'https://www.phpbb.com'),

			// Increment the user's post count? Array of the post_types for which we will increment the post count
			'increment_postcount'		=> array('default' => array(ext::TITANIA_SUPPORT)),

			// Path to demo board we will install styles on
			'demo_style_path'			=> array('default' => array(
				'30'	=> '',
				'31'	=> '',
				'32'	=> '',
				'33'	=> '',
			)),

			// URL for style demo board management hook
			'demo_style_hook'			=> array('default' => array(
				'30'	=> '',
				'31'	=> '',
				'32'	=> '',
				'33'	=> '',
			)),

			// Demo board URL
			'demo_style_url'			=> array('default' => array(
				'30'	=> '',
				'31'	=> '',
				'32'	=> '',
				'33'	=> '',
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

			// Mod/style database release forums (receive announcements on updates/approval)
			'forum_mod_database'		=> array('default' => array(
				'30'	=> 0,
			)),
			'forum_style_database'		=> array('default' => array(
				'30'	=> 0,
				'31'	=> 0,
				'32'	=> 0,
				'33'	=> 0,
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
				'33'	=> 0,
			)),

			// Extension/style validation queue forums
			'titania_extensions_queue'	=> array('default' => array(
				ext::TITANIA_QUEUE				=> 0,
				ext::TITANIA_QUEUE_DISCUSSION	=> 0,
				'trash'							=> 0,
			)),
			'titania_mods_queue'		=> array('default' => array(
				ext::TITANIA_QUEUE				=> 0,
				ext::TITANIA_QUEUE_DISCUSSION	=> 0,
				'trash'							=> 0,
			)),
			'titania_styles_queue'		=> array('default' => array(
				ext::TITANIA_QUEUE				=> 0,
				ext::TITANIA_QUEUE_DISCUSSION	=> 0,
				'trash'							=> 0,
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
				'31'	=> array('latest_revision' => '12', 'name' => 'phpBB 3.1.x', 'allow_uploads' => false),
				'32'	=> array('latest_revision' => '11', 'name' => 'phpBB 3.2.x', 'allow_uploads' => true),
				'33'	=> array('latest_revision' => '9', 'name' => 'phpBB 3.3.x', 'allow_uploads' => true),
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
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff', 'yml', 'yaml'
				),
				ext::TITANIA_QUEUE				=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff', 'yml', 'yaml'
				),
				ext::TITANIA_QUEUE_DISCUSSION	=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff', 'yml', 'yaml'
				),
				ext::TITANIA_FAQ				=> array(
					'zip', 'tar', 'gz', '7z', 'bz2', 'gtar',
					'jpg', 'jpeg', 'gif', 'png', 'tif', 'tiff', 'yml', 'yaml'
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
		$configs = array();

		foreach ($this->get_configurables() as $config => $type)
		{
			if (strpos($type, 'array') === 0 && $config_text = $this->config_text->get(ext::TITANIA_CONFIG_PREFIX . $config))
			{
				$configs[$config] = json_decode($config_text, true);
			}

			else if ($this->config->offsetExists(ext::TITANIA_CONFIG_PREFIX . $config))
			{
				$configs[$config] = json_decode($this->config->offsetGet(ext::TITANIA_CONFIG_PREFIX . $config), true);
			}
		}

		$this->__set_array($configs);
	}

	/**
	 * Get an array of common configurable options
	 * Add additional config variables to this array to be able to configure them
	 * in Titania's administration, or remove them from this array to remove them
	 * from Titania's administration.
	 *
	 * Types:
	 * string - For a simple string field item.
	 * int    - For a simple number field item.
	 * bool   - For a true/false radio button.
	 * array  - These are for multidimensional field items, i.e.: multiple phpBB
	 *          version branches. Each array field should be appended with their
	 *          common type such as string, int or bool, i.e: array|string
	 * forums - For a Select forum drop down item.
	 * groups - For a Group multi-select box item.
	 *
	 * @return array
	 */
	public function get_configurables()
	{
		return array(
			'phpbb_root_path' 				=> 'string',
			'phpbb_script_path' 			=> 'string',
			'titania_script_path' 			=> 'string',
			'table_prefix' 					=> 'string',
			'site_home_url'					=> 'string',
			'search_enabled'				=> 'bool',
			'search_backend' 				=> 'string',
			'search_backend_ip'				=> 'string',
			'search_backend_port'			=> 'int',
			'forum_extension_database' 		=> 'array|forums',
			'forum_extension_robot' 		=> 'int',
			'forum_style_database' 			=> 'array|forums',
			'forum_style_robot' 			=> 'int',
			'forum_mod_database' 			=> 'array|forums',
			'forum_mod_robot' 				=> 'int',
//			'forum_converter_database'		=> 'array|int',
//			'forum_converter_robot'			=> 'int',
//			'forum_bbcode_database'			=> 'array|int',
//			'forum_bbcode_robot'			=> 'int',
			'titania_extensions_queue' 		=> 'array|forums',
			'titania_styles_queue'			=> 'array|forums',
			'titania_mods_queue'			=> 'array|forums',
			'colorizeit' 					=> 'string',
			'colorizeit_url'				=> 'string',
			'colorizeit_auth' 				=> 'string',
			'colorizeit_var' 				=> 'string',
			'colorizeit_value' 				=> 'string',
			'can_modify_style_demo_url' 	=> 'bool',
			'demo_style_path' 				=> 'array|string',
			'demo_style_url' 				=> 'array|string',
			'demo_style_hook' 				=> 'array|string',
			'upload_max_filesize'			=> 'array|int',
			'team_groups'					=> 'groups',
			'cleanup_titania' 				=> 'bool',
//			'style'							=> 'string',
//			'theme'							=> 'string',
//			'max_rating'					=> 'int',
//			'support_in_titania'			=> 'bool',
//			'display_backtrace'				=> 'int',
//			'require_validation'			=> 'bool',
//			'use_queue'						=> 'bool',
//			'allow_self_validation'			=> 'bool',
//			'upload_directory'				=> 'array|string',

			// Not going to support these in the admin panel
//			'upload_allowed_extensions'
//			'mpv_server_list'
//			'phpbb_versions'
//			'repack_diff_extensions'
		);
	}
}
