<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
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
	'ROLE_TITANIA_MODIFICATION_TEAM'	=> 'Titania Modifications Team Role',
	'ROLE_TITANIA_STYLE_TEAM'			=> 'Titania Style Team Role',
	'ROLE_TITANIA_MODERATOR_TEAM'		=> 'Titania Moderation Team Role',
	'ROLE_TITANIA_ADMINISTRATOR_TEAM'	=> 'Titania Administration Team Role',
));

?>
