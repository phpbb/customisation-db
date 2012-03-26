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
	'NEW_PHPBB_VERSION'				=> 'Nouvelle version de phpBB',
	'NEW_PHPBB_VERSION_EXPLAIN'		=> 'Nouvelle version de phpBB compatible avec la révision.',
	'NO_REVISIONS_UPDATED'			=> 'Aucune révision n’a été mise à jour d’aprés les limitations indiquées.',
	'NO_VERSION_SELECTED'			=> 'Vous devez fournir une version de phpBB valide.  Par exemple : 3.0.7 ou 3.0.7-PL1.',

	'PHPBB_VERSION_TEST'			=> 'Support de la version de test de phpBB pour les révisions de la modification',

	'REVISIONS_ADDED_TO_QUEUE'		=> '%d révisions ont été ajoutées à la file d’attente de test d’AutoMOD.',

	'VERSION_RESTRICTION'			=> 'Restriction de version',
	'VERSION_RESTRICTION_EXPLAIN'	=> 'Limiter le support de la nouvelle version aux versions sélectionnées.',
));
