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
);
