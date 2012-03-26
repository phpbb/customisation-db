<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
* Tradução feita e revisada pela Equipe phpBB Brasil <http://www.phpbbrasil.com.br>!
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
	'CONTRIBUTION_NAME_DESCRIPTION'	=> 'Nome/descrição da contribuição',
	'CONTRIB_FAQ'					=> 'FAQ da contribuição',
	'CONTRIB_NAME_DESCRIPTION'		=> 'Nome e descrição da contribuição',
	'CONTRIB_SUPPORT'				=> 'Discussão/suporte da contribuição',

	'SEARCH_KEYWORDS_EXPLAIN'		=> 'Coloque uma lista de palavras separadas por <strong>|</strong> entre parênteses se apenas uma das palavras deve ser encontrada. Use * como coringa para resultados parciais.',
	'SEARCH_MSG_ONLY'				=> 'Texto/descrição apenas',
	'SEARCH_SUBCATEGORIES'			=> 'Procurar sub-categorias',
	'SEARCH_TITLE_MSG'				=> 'Títulos e texto/descrição',
	'SEARCH_TITLE_ONLY'				=> 'Apenas títulos',
	'SEARCH_WITHIN_TYPES'			=> 'Buscar dentro dos tipos',
));
