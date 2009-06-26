<?php
/**
*
* authors [English]
*
* @package Titania
* @version $Id$
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
	'AUTHOR_CONTRIBUTIONS'		=> 'Contributions',
	'AUTHOR_DETAILS'			=> 'Author Details',
	'AUTHOR_NOT_FOUND'			=> 'Author not found',
	'AUTHOR_SUPPORT'			=> 'Support',
	'AUTHOR_RATING' => 'Author Rating',
	'AUTHOR_CONTRIBS' => 'Author Contributions',
	'AUTHOR_STATISTICS' => 'Author Statistics',

	'AUTHOR_CONTRIBS' => 'Contributions',
	'AUTHOR_MODS' => 'Modifications',
	'AUTHOR_STYLES' => 'Styles',
	'AUTHOR_SNIPPETS' => 'Snippets',
	'AUTHOR_DESC' => 'Author Description',

	'PHPBB_PROFILE'				=> 'phpBB.com profile',

	'REAL_NAME'					=> 'Real Name',

	'USER_INFORMATION'			=> '’s user information',
));
