<?php
/**
*
* authors [English]
*
* @package Titania
* @version $Id: titania_mods.php 78 2008-08-26 02:22:02Z HighwayofLife $
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
	'AUTHOR_LIST'		=> 'Author List',
	'AUTHOR_PROFILE'	=> 'Author Profile',
	'AUTHOR_NOT_FOUND'	=> 'Author not found',
	'NUM_CONTRIB'		=> '%s Contribution',
	'NUM_CONTRIBS'		=> '%s Contributions',
	'NUM_MOD'			=> '%s MOD',
	'NUM_MODS'			=> '%s MODs',
	'NUM_STYLE'			=> '%s Style',
	'NUM_STYLES'		=> '%s Styles',
	'NUM_SNIPPET'		=> '%s Snippet',
	'NUM_SNIPPETS'		=> '%s Snippets',
));

