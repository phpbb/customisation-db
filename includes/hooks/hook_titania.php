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
if (!defined('IN_PHPBB'))
{
	exit;
}

$phpbb_hook->register(array('template', 'display'), 'titania_template_display', 'last');

function titania_template_display(&$hook, $handle, $include_once)
{
	global $template, $phpbb_root_path;

	foreach ($template->_tpldata['.'][0] as $id => &$row)
	{
		$row = str_replace($phpbb_root_path, generate_board_url() . '/', $row);

		//echo '<br /><br />' . $id . "\t - \t";
		//var_export($row);
	}
}