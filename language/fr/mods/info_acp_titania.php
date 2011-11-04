<?php
/**
* titania acp language [French]
*
* @package language
* @copyright (c) 2008 phpBB Customisation Database Team, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
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
	'ROLE_TITANIA_MODIFICATION_TEAM'	=> 'Rôle pour l’équipe des modifications dans Titania',
	'ROLE_TITANIA_STYLE_TEAM'			=> 'Rôle pour l’équipe des styles dans Titania',
	'ROLE_TITANIA_MODERATOR_TEAM'		=> 'Rôle pour l’équipe de modération dans Titania',
	'ROLE_TITANIA_ADMINISTRATOR_TEAM'	=> 'Rôle pour l’équipe d’administration dans Titania',
));

?>