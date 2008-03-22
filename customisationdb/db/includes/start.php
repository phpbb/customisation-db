<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

$root_path = '../../';

// For phpBB.com, normally include just common.php
include("{$phpbb_root}common.php");

include("{$root_path}db/includes/class_main.$phpEx");
include("{$root_path}db/includes/class_hooks.$phpEx");
include("{$root_path}db/includes/class_api.$phpEx");

// Now we init the template (this will later be replaced by the real template code)
$template->set_custom_template($phpbb_root_path . '../db/template/', 'website');

if (!titania_db::init())
{
	trigger_error('NO_DB');
}
?>