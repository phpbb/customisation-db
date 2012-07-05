<?php
/**
* titania mcp language [English]
*
* @package language
* @version $Id: info_mcp_titania.php 1071 2010-04-17 05:10:36Z exreaction $
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'MCP_TITANIA'				=> 'Titania',
	'MCP_TITANIA_ATTENTION'		=> 'Atención Titania',
));

?>