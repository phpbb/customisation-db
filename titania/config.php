<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
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

/**
* Relative path to the phpBB installation.
* 
* @param	string	$phpbb_root_path	Path relative to the titania root path.
*/
$phpbb_root_path = '../community/';

/**
* Prefix of the sql tables.
*
* @param	string	$cdb_table_prefix	Table prefix
*
* Example: You may use the phpBB table prefix here.
* <code>
* 	$cdb_table_prefix = $table_prefix . 'moddb_';
* </code>
*
* Default:
* <code>
* 	$cdb_table_prefix = 'customisation_';
* </code>
*/
$cdb_table_prefix = 'customisation_';

/**
* Custom template path where templates are stored.
*
* @param	string	$template_location	Path relative to the titania root path.
*
* Example: You may use $phpbb_root_path.
* <code>
* 	$template_location = $phpbb_root_path . 'styles/prosilver/template/moddb/';
* </code>
*
* Default: The folder 'template' in the titania root.
* <code>
* 	$template_location = './template/';
* </code>
*/
$template_location = 'template/';