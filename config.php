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

if (!defined('IN_PHPBB'))
{
	exit;
}

$config = array(
	// Display backtrace for TITANIA_TEAMS_ACCESS level
	'display_backtrace'	=> 2,

	/**
	* IDs of database forum
	*/
	'forum_mod_database'		=> array(
		'30'	=> 69,
	),
	'forum_style_database'		=> array(
		'30'	=> 73,
		'31'	=> 531,
	),
	'forum_extension_database'	=> array(
		'31'	=> 536,
	),

	/**
	* IDs of account used for topic/post release in database forum
	*/
	'forum_extension_robot' => 77503,
	'forum_mod_robot' => 77503,
	'forum_style_robot' => 93260,

	/**
	* Relative path to the phpBB installation.
	*
	* @param	string	$phpbb_root_path	Path relative from the titania root path.
	*/
	'phpbb_root_path' => '../../community/',

	/**
	* Relative path from the server root (generate_board_url(true))
	*
	* @param	string	Path to the phpBB folder
	*/
	'phpbb_script_path' => 'community/',

	/**
	* Relative path from the server root (generate_board_url(true))
	*
	* @param	string	Path to the titania folder
	*/
	'titania_script_path' => 'customise/db/',

	/**
	* Prefix of the sql tables.  Not the prefix for the phpBB tables, prefix for the Titania tables only.
	* This MUST NOT be the same as the phpBB prefix!
	*
	* @param	string	$titania_table_prefix	Table prefix
	*/
	'table_prefix' => 'customisation_',

	/**
	* Path to the style demo board you would like styles to be installed on upon validation
	* (there is a checkbox option for styles to be installed on the demo board when approving)
	*
	* @param bool|string false to not use a style demo board, path to the board root
	*/
	'demo_style_path' => array(
		'30'	=> '../../styles/demo/3.0/board',
		'31'	=> '../../styles/demo/3.1/board',
	),

	/**
	* Full URL to the demo style.  We will perform sprintf(demo_style_full, $style_id), so please write the url properly
	* Example (from phpbb.com) http://www.phpbb.com/styles/demo/3.0/?style_id=%s
	*
	* @param bool|string false to not use a style demo board
	*/
	'demo_style_url' =>	array(
		'30'	=> 'https://www.phpbb.com/styles/demo/3.0/board/index.php?style=%s',
		'31'	=> 'https://www.phpbb.com/styles/demo/3.1/board/index.php?style=%s',
	),

	'demo_style_hook' => array(
		'30'	=> 'https://www.phpbb.com/styles/demo/3.0/board/style_demo_install.php',
		'31'	=> 'https://www.phpbb.com/styles/demo/3.0/board/style_demo_install.php',	
	),

	// ColorizeIt Config for Styles Section
	'colorizeit'    	=> 'phpbb',
	'colorizeit_auth'   => 'HEADER',
	'colorizeit_var'    => 'X-phpBB-Clr',
	'colorizeit_value'  => '1',

	// When editing styles, do not allow non-team members to modify the demo URL
	'can_modify_style_demo_url' => false,

	/**
	* Team groups (members will get TITANIA_TEAMS_ACCESS)
	*/
	'team_groups' => array(4, 7331, 13330, 993, 7332, 228685, 228778, 47077, 228777, 7330),

	'search_backend' => 'solr',
	'search_backend_ip' => '140.211.15.49',
	'search_backend_port' => 8983,

	'upload_max_filesize'		=> array(
		TITANIA_CONTRIB		=> 10485760, // 10 MiB
		TITANIA_SCREENSHOT	=> 524288, // 512 Kib
		TITANIA_TRANSLATION	=> -1,
	),

	// Remove unsubmitted revisions and attachments
	'cleanup_titania'	=> true,
);
