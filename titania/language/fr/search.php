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
	'CONTRIBUTION_NAME_DESCRIPTION'	=> 'Nom et description de la contribution',
	'CONTRIB_FAQ'					=> 'FAQ de la contribution',
	'CONTRIB_NAME_DESCRIPTION'		=> 'Nom et description de la contribution',
	'CONTRIB_SUPPORT'				=> 'Discussion et support de la contribution',

	'SEARCH_KEYWORDS_EXPLAIN'		=> 'Saisissez une liste de mots séparés par <strong>|</strong> entre parenthèses si seul un des mots doit être trouvé. Utilisez * comme joker concernant les recherches partielles.',
	'SEARCH_MSG_ONLY'				=> 'Texte et description uniquement',
	'SEARCH_SUBCATEGORIES'			=> 'Rechercher dans les sous-catégories',
	'SEARCH_TITLE_MSG'				=> 'Titres, texte et description',
	'SEARCH_TITLE_ONLY'				=> 'Titres uniquement',
	'SEARCH_WITHIN_TYPES'			=> 'Rechercher dans les types',
));
