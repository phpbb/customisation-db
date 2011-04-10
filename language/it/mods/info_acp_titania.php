<?php
/**
* titania acp language [Italian]
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @copyright (c) 2011 portalxl.eu - translated on 2011/04/10
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
	'ROLE_TITANIA_MODIFICATION_TEAM'	=> 'Titania ruolo Team',
	'ROLE_TITANIA_STYLE_TEAM'			=> 'Titania ruolo Team Stili',
	'ROLE_TITANIA_MODERATOR_TEAM'		=> 'Titania ruolo Team Mod',
	'ROLE_TITANIA_ADMINISTRATOR_TEAM'	=> 'Titania ruolo Team Amministrazione',
));

?>