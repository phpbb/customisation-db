<?php
/**
* titania acp language [Bahasa Indonesia]
*
* @package language
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
	'ROLE_TITANIA_MODIFICATION_TEAM'	=> 'Peranan Tim Modifikasi Titania',
	'ROLE_TITANIA_STYLE_TEAM'			=> 'Peranan Tim Gaya Titania',
	'ROLE_TITANIA_MODERATOR_TEAM'		=> 'Peranan Tim Moderasi Titania',
	'ROLE_TITANIA_ADMINISTRATOR_TEAM'	=> 'Peranan Tim Administrasi Titania',
));

?>