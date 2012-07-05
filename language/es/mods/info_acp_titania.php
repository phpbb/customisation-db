<?php
/**
* titania acp language [English]
*
* @package language
* @version $Id$
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
	'ROLE_TITANIA_MODIFICATION_TEAM'	=> 'Rol equipo de modificaciones titania',
	'ROLE_TITANIA_STYLE_TEAM'			=> 'Rol equipo de estilos titania',
	'ROLE_TITANIA_MODERATOR_TEAM'		=> 'Rol equipo de moderación titania',
	'ROLE_TITANIA_ADMINISTRATOR_TEAM'	=> 'Rol administración titania',
));

?>