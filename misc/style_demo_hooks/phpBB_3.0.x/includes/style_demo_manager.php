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

class titania_demo_manager
{
	/** @var array */
	protected $db;

	/** @var \user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $style_dir;

	/** @var array */
	public $error = array();

	/**
	* Constructor.
	*
	* @param array $db
	* @param \user $user
	* @param string $root_path
	* @param string $php_ext
	*/
	public function __construct($config, $db, $user, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->set_manager();
	}

	/**
	* Configure manager
	*
	* @param string $style_dir_name		Style directory name.
	* @return null
	*/
	public function configure($style_dir_name)
	{
		$this->style_dir = $style_dir_name;
	}

	/**
	* Install style.
	*
	* @return mixed Returns style id or error string.
	*/
	public function install()
	{
		// Define references.
		$error = array();
		$style_id = 0;
		$config = $this->get_style_configuration();
		$style_row = array(
			'install_name'			=> $config['style']['name'],
			'install_copyright'		=> $config['style']['copyright'],
			'template_id'			=> 0,
			'template_name'			=> $config['template']['name'],
			'template_copyright'	=> $config['template']['copyright'],
			'theme_id'				=> 0,
			'theme_name'			=> $config['theme']['name'],
			'theme_copyright'		=> $config['theme']['copyright'],
			'imageset_id'			=> 0,
			'imageset_name'			=> $config['imageset']['name'],
			'imageset_copyright'	=> $config['imageset']['copyright'],
			'store_db'				=> 0,
			'style_active'			=> 1,
			'style_default'			=> 0,
		);

		// Install the style.
		$success = $this->manager->install_style(
			$error,
			'install',
			$this->root_path . 'styles/' . $this->style_dir . '/',
			$style_id,
			$config['style']['name'],
			$this->style_dir,
			$config['style']['copyright'],
			true,
			false,
			$style_row
		);

		if ($success === false)
		{
			if ($error != array($this->user->lang['STYLE_ERR_NAME_EXIST']))
			{
				return $error;
			}
			else
			{
				$sql = 'SELECT style_id
					FROM ' . STYLES_TABLE . '
					WHERE style_name = "' . $this->db->sql_escape(basename($config['style']['name'])) . '"';
				$this->db->sql_query($sql);
				$style_id = (int) $this->db->sql_fetchfield('style_id');
				$this->db->sql_freeresult();
			}
		}

		return $style_id;
	}

	/**
	* Set manager from acp_styles class.
	*
	* @return null
	*/
	protected function set_manager()
	{
		$this->user->add_lang('acp/styles');

		if (!class_exists('acp_styles'))
		{
			include($this->root_path . 'includes/acp/acp_styles.' . $this->php_ext); 
		}

	    if (!defined('TEMPLATE_BITFIELD'))
	    {
		    // Hardcoded template bitfield to add for new templates
		    $bitfield = new bitfield();
		    $bitfield->set(0);
		    $bitfield->set(1);
		    $bitfield->set(2);
		    $bitfield->set(3);
		    $bitfield->set(4);
		    $bitfield->set(8);
		    $bitfield->set(9);
		    $bitfield->set(11);
		    $bitfield->set(12);
		    define('TEMPLATE_BITFIELD', $bitfield->get_base64());
		    unset($bitfield);
	    }
		$this->manager = new acp_styles();

		// Fill the configuration variables
		$this->manager->style_cfg = $this->manager->template_cfg = $this->manager->theme_cfg = $this->manager->imageset_cfg = '
#
# phpBB {MODE} configuration file
#
# @package phpBB3
# @copyright (c) 2005 phpBB Group
# @license http://opensource.org/licenses/gpl-license.php GNU Public License
#
#
# At the left is the name, please do not change this
# At the right the value is entered
# For on/off options the valid values are on, off, 1, 0, true and false
#
# Values get trimmed, if you want to add a space in front or at the end of
# the value, then enclose the value with single or double quotes.
# Single and double quotes do not need to be escaped.
#
#

# General Information about this {MODE}
name = {NAME}
copyright = {COPYRIGHT}
version = {VERSION}
';

		$this->manager->theme_cfg .= '
# Some configuration options

#
# You have to turn this option on if you want to use the
# path template variables ({T_IMAGESET_PATH} for example) within
# your css file.
# This is mostly the case if you want to use language specific
# images within your css file.
#
parse_css_file = {PARSE_CSS_FILE}
';

		$this->manager->template_cfg .= '
# Some configuration options

#
# You can use this function to inherit templates from another template.
# The template of the given name has to be installed.
# Templates cannot inherit from inheriting templates.
#';

		$this->manager->imageset_keys = array(
			'logos' => array(
				'site_logo',
			),
			'buttons'	=> array(
				'icon_back_top',
				'icon_contact_aim',
				'icon_contact_email',
				'icon_contact_icq',
				'icon_contact_jabber',
				'icon_contact_msnm',
				'icon_contact_pm',
				'icon_contact_yahoo',
				'icon_contact_www',
				'icon_post_delete',
				'icon_post_edit',
				'icon_post_info',
				'icon_post_quote',
				'icon_post_report',
				'icon_user_online',
				'icon_user_offline',
				'icon_user_profile',
				'icon_user_search',
				'icon_user_warn',
				'button_pm_forward',
				'button_pm_new',
				'button_pm_reply',
				'button_topic_locked',
				'button_topic_new',
				'button_topic_reply',
			),
			'icons'		=> array(
				'icon_post_target',
				'icon_post_target_unread',
				'icon_topic_attach',
				'icon_topic_latest',
				'icon_topic_newest',
				'icon_topic_reported',
				'icon_topic_unapproved',
				'icon_friend',
				'icon_foe',
			),
			'forums'	=> array(
				'forum_link',
				'forum_read',
				'forum_read_locked',
				'forum_read_subforum',
				'forum_unread',
				'forum_unread_locked',
				'forum_unread_subforum',
				'subforum_read',
				'subforum_unread',
			),
			'folders'	=> array(
				'topic_moved',
				'topic_read',
				'topic_read_mine',
				'topic_read_hot',
				'topic_read_hot_mine',
				'topic_read_locked',
				'topic_read_locked_mine',
				'topic_unread',
				'topic_unread_mine',
				'topic_unread_hot',
				'topic_unread_hot_mine',
				'topic_unread_locked',
				'topic_unread_locked_mine',
				'sticky_read',
				'sticky_read_mine',
				'sticky_read_locked',
				'sticky_read_locked_mine',
				'sticky_unread',
				'sticky_unread_mine',
				'sticky_unread_locked',
				'sticky_unread_locked_mine',
				'announce_read',
				'announce_read_mine',
				'announce_read_locked',
				'announce_read_locked_mine',
				'announce_unread',
				'announce_unread_mine',
				'announce_unread_locked',
				'announce_unread_locked_mine',
				'global_read',
				'global_read_mine',
				'global_read_locked',
				'global_read_locked_mine',
				'global_unread',
				'global_unread_mine',
				'global_unread_locked',
				'global_unread_locked_mine',
				'pm_read',
				'pm_unread',
			),
			'polls'		=> array(
				'poll_left',
				'poll_center',
				'poll_right',
			),
			'ui'		=> array(
				'upload_bar',
			),
			'user'		=> array(
				'user_icon1',
				'user_icon2',
				'user_icon3',
				'user_icon4',
				'user_icon5',
				'user_icon6',
				'user_icon7',
				'user_icon8',
				'user_icon9',
				'user_icon10',
			),
		);
	}

	/**
	* Get component configuration from .cfg file.
	*
	* @param string $component		style|imageset|template|theme
	* @return array
	*/
	protected function get_component_configuration($component)
	{
		$file = ($component == 'style') ? 'style.cfg' : "$component/$component.cfg";
		$file = $this->root_path . 'styles/' . $this->style_dir . '/' . $file;

		return (file_exists($file)) ? parse_cfg_file($file) : array();
	}

	/**
	* Get configuration for complete style ("main" style and its components).
	*
	* @return array
	*/
	protected function get_style_configuration()
	{
		$components = array('style', 'template', 'theme', 'imageset');
		$config = array();

		foreach ($components as $component)
		{
			$config[$component] = $this->get_component_configuration($component);

			// Merge only specific things. We may need them later.
			foreach (array('inherit_from', 'parse_css_file') as $key)
			{
				if (!empty($config[$component][$key]) && !isset($config['style'][$key]))
				{
					$config['style'][$key] = $config[$component][$key];
				}
			}
		}
		return $config;
	}
}
