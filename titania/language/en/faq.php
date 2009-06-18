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
	'SORT_SUBJECT'			=> 'Subject',
	'SORT_VIEWS'			=> 'Views',
	
	'CREATE_FAQ'			=> 'Create an new FAQ entry',
	'EDIT_FAQ'			=> 'Edit FAQ',
	
	'DELETE_FAQ'			=> 'Delete FAQ',
	'DELETE_FAQ_CONFIRM'		=> 'Are you sure delete the FAQ?',
	
	'FAQ_MANAGEMENT'		=> 'FAQ Management Panel',
	
	'FAQ_MANAGEMENT_LIST'		=> 'FAQ Management List',
	'FAQ_DETAILS'			=> 'FAQ Details Page',
	'FAQ_LIST'			=> 'FAQ List',

	'SUBJECT_EMPTY'			=> 'You need to enter a subject',
	'TEXT_EMPTY'			=> 'You need to enter a text',

	'RETURN_FAQ'			=> '%sReturn to FAQ Details Page%s',
	'RETURN_FAQ_LIST'		=> '%sReturn to FAQ List%s',

	'FAQ_CREATED'			=> 'This FAQ has been created successfully.',
	'FAQ_EDITED'			=> 'This FAQ has been edited successfully.',
	'FAQ_DELETED'			=> 'This FAQ has been deleted successfully.',
	
	'FAQ_SUBJECT'			=> 'Subject',
	'FAQ_TEXT'			=> 'Text',
	
	'FAQ_NOT_FOUND'			=> 'The FAQ specified could not be found.',
	
	'NO_FAQ'			=> 'Nobody created an FAQ entry yet.',
));
