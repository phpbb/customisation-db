<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team, (c) 2011 phpBB.fr
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License 2.0
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'UPDATE_RELEASE_TOPICS'				=> 'Mettre à jour tous les sujets de sortie de la contribution dans la base de données du forum',
	'UPDATE_RELEASE_TOPICS_COMPLETE'	=> 'Tous les sujets de sortie de la contribution ont été mis à jour !',
	'UPDATE_RELEASE_TOPICS_CONFIRM'		=> 'Êtes-vous sûr de vouloir mettre à jour tous les sujets de sortie de la contribution dans la base de données du forum ?  Cela peut prendre un certain temps.',
	'UPDATE_RELEASE_TOPICS_PROGRESS'	=> '%1$d sujets effectués sur %2$d.  Veuillez patienter…',
));
