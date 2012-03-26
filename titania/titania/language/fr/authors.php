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
	'AUTHOR_CONTRIBS'			=> 'Contributions',
	'AUTHOR_DATA_UPDATED'		=> 'Les informations de l’auteur ont été mises à jour.',
	'AUTHOR_DESC'				=> 'Description de l’auteur',
	'AUTHOR_DETAILS'			=> 'Informations sur l’auteur',
	'AUTHOR_MODS'				=> '%d modifications',
	'AUTHOR_MODS_ONE'			=> '1 modification',
	'AUTHOR_NOT_FOUND'			=> 'L’auteur est introuvable',
	'AUTHOR_PROFILE'			=> 'Profil de l’auteur',
	'AUTHOR_RATING'				=> 'Note de l’auteur',
	'AUTHOR_REAL_NAME'			=> 'Nom réel',
	'AUTHOR_SNIPPETS'			=> '%d snippets',
	'AUTHOR_SNIPPETS_ONE'		=> '1 snippet',
	'AUTHOR_STATISTICS'			=> 'Statistiques sur l’auteur',
	'AUTHOR_STYLES'				=> '%d styles',
	'AUTHOR_STYLES_ONE'			=> '1 style',
	'AUTHOR_SUPPORT'			=> 'Support',

	'ENHANCED_EDITOR'			=> 'Éditeur amélioré',
	'ENHANCED_EDITOR_EXPLAIN'	=> 'Active ou désactive l’éditeur amélioré (il permet de capturer les onglets et de redimensionner automatiquement les champs de texte).',

	'MANAGE_AUTHOR'				=> 'Gérer l’auteur',

	'NO_AVATAR'					=> 'Aucun avatar',

	'PHPBB_PROFILE'				=> 'Profil sur phpBB.com',

	'USER_INFORMATION'			=> ' : informations sur l’utilisateur',

	'VIEW_USER_PROFILE'			=> 'Consulter le profil de l’utilisateur',
));
