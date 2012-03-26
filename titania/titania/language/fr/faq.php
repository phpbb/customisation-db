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
if (!defined('IN_TITANIA'))
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

$lang = array_merge($lang, array(
	'CREATE_FAQ'			=> 'Nouvel élément de la FAQ',

	'DELETE_FAQ'			=> 'Supprimer l’élément de la FAQ',
	'DELETE_FAQ_CONFIRM'	=> 'Êtes-vous sûr de vouloir de vouloir supprimer cet élément de la FAQ ?',

	'EDIT_FAQ'				=> 'Éditer l’élément de la FAQ',

	'FAQ_CREATED'			=> 'L’élément de la FAQ a été créé avec succès.',
	'FAQ_DELETED'			=> 'L’élément de la FAQ a été supprimé.',
	'FAQ_DETAILS'			=> 'Page d’informations de la FAQ',
	'FAQ_EDITED'			=> 'L’élément de la FAQ a été édité avec succès.',
	'FAQ_EXPANDED'			=> 'Foire aux questions',
	'FAQ_LIST'				=> 'Liste de la FAQ',
	'FAQ_NOT_FOUND'			=> 'L’élémént de la FAQ que vous avez spécifié est introuvable.',

	'NO_FAQ'				=> 'Il n’y a aucun élément dans la FAQ.',

	'QUESTIONS'				=> 'Questions',
));
