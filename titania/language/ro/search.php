<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'CONTRIBUTION_NAME_DESCRIPTION'	=> 'Nume/Descriere contribuţie',
	'CONTRIB_FAQ'					=> 'Contribuţie FAQ',
	'CONTRIB_NAME_DESCRIPTION'		=> 'Nume şi Descriere contribuţie',
	'CONTRIB_SUPPORT'				=> 'Discuţie/Suport contribuţie',

	'SEARCH_KEYWORDS_EXPLAIN'		=> 'Specificaţi o listă de cuvinte separate de <strong>|</strong> &in paranteze doar dacă unul dintre cuvinte trebuie găsit. Folosiţi * ca wildcard pentru potriviri parţiale.',
	'SEARCH_MSG_ONLY'				=> 'Doar Text/Descriere',
	'SEARCH_SUBCATEGORIES'			=> 'Caută Subcategorii',
	'SEARCH_TITLE_MSG'				=> 'Titluri şi Text/Descriere',
	'SEARCH_TITLE_ONLY'				=> 'Doar titluri',
	'SEARCH_WITHIN_TYPES'			=> 'Caută doar &in tipurile specificate',
));
