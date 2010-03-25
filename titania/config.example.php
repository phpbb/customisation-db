<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2009 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* Customisation Database (Titania) Configuration File.
*
*/

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

$config = array(
	/**
	* phpBB versions array
	*
	* @param array(
	*	(release branch) => array(
	*		'latest_revision' => (revision number)
	* 		'allow_uploads' => (allow submission of revisions for this version of phpBB?),
	*	),
	* ),
	*/
	'phpbb_versions' => array(
		'20'	=> array('latest_revision' => '23', 'name' => 'phpBB 2.0.x', 'allow_uploads' => false),
		'30'	=> array('latest_revision' => '7-pl1', 'name' => 'phpBB 3.0.x', 'allow_uploads' => true),
	),

	/**
	* Relative path to the phpBB installation.
	*
	* @param	string	$phpbb_root_path	Path relative from the titania root path.
	*/
	'phpbb_root_path' => '../phpBB/',

	/**
	* Relative path from the server root (generate_board_url(true))
	*
	* @param	string	Path to the phpBB folder
	*/
	'phpbb_script_path' => 'phpBB/',

	/**
	* Relative path from the server root (generate_board_url(true))
	*
	* @param	string	Path to the titania folder
	*/
	'titania_script_path' => 'customisation/',

	/**
	* Prefix of the sql tables.  Not the prefix for the phpBB tables, prefix for the Titania tables only.
	* This MUST NOT be the same as the phpBB prefix!
	*
	* @param	string	$titania_table_prefix	Table prefix
	*/
	'table_prefix' => 'customisation_',

	/**
	* Style Path (titania/style/ *path* /)
	*/
	'style' => 'default',

	/**
	* Team groups (members will get TITANIA_TEAMS_ACCESS)
	*/
	'team_groups' => array(5),

	/**
	* IDs of database forum
	*/
	'forum_mod_database' => 0,
	'forum_style_database' => 0,

	/**
	* IDs of account used for topic/post release in database forum
	*/
	'forum_mod_robot' => 0,
	'forum_style_robot' => 0,
	
	/**
	* Show the support/discussion panel to the public?
	*/
	'support_in_titania' => true,
);