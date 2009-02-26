<?php
/**
*
* mods [English]
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
	'CATEGORY_DESCRIPTION'	=> 'Description',

	'EMAIL_MOD_BODY_EXPLAIN'=> 'This message will be sent as plain text, do not include any HTML or BBCode. Please note that the MOD information is already included in the message. The return address for this message will be set to your e-mail address.',

	'FIND_MOD'				=> 'Find a MOD',

	'HIDE_FIND_MOD'			=> 'Hide Find',

	'MOD_AUTHOR'			=> 'MOD Author',
	'MOD_CATEGORY'			=> 'Category',
	'MOD_VERSION'			=> 'MOD version',
	'MOD_CATEGORIES'		=> 'MOD Categories',
	'MOD_DESCRIPTION'		=> 'MOD Description',
	'MOD_EMAIL'				=> 'E-mail MOD to a friend',
	'MOD_LIST'				=> 'MODs list &bull; Search Results',
	'MOD_LIST_DESCRIPTION'	=> 'MOD list description',
	'MOD_NOT_FOUND'			=> 'MOD not found',
	'MOD_TITLE'				=> 'MOD Title',

	'NO_EMAIL_MOD'			=> 'You are not permitted to send an e-mail to a friend recommending this MOD.',
	'NO_MODS'				=> 'No MODs found in category: “%s”',
	'NO_CATEGORIES'			=> 'No Categories defined',

	'RECOMMEND_MOD'			=> 'Recommend this MOD to a friend',
	
	'FOR_VERSION'			=> 'It has been released for MOD in <b>%s</b> version.',
	
	'SORT_CONTRIB_NAME'		=> 'Sort by MOD title',
	'SORT_DOWNLOADS'		=> 'Sort by downloads',
	'SORT_RATING'			=> 'Sort by MOD rating',
	'SORT_TIME_ADDED'		=> 'Sort by date added',
	'SORT_TIME_UPDATED'		=> 'Sort by date updated',
	
	'TOTAL_MODS'			=> 'Total MODs Found',
));

