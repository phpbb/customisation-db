<?php
/**
* titania acp language [Brazilian Portuguese]
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
* Tradução feita e revisada pela Equipe phpBB Brasil <http://www.phpbbrasil.com.br>!
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
	'ROLE_TITANIA_MODIFICATION_TEAM'	=> 'Regras da equipe de modificações na Titania',
	'ROLE_TITANIA_STYLE_TEAM'			=> 'Regras da equipe de estilos na Titania',
	'ROLE_TITANIA_MODERATOR_TEAM'		=> 'Regras da equipe de moderação na Titania',
	'ROLE_TITANIA_ADMINISTRATOR_TEAM'	=> 'Regras da equipe de administração na Titania',
));

?>