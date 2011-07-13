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
	'CREATE_FAQ'			=> 'New FAQ',

	'DELETE_FAQ'			=> 'Delete FAQ',
	'DELETE_FAQ_CONFIRM'	=> 'Are you sure that you want to delete this FAQ?',

	'EDIT_FAQ'				=> 'Edit FAQ',

	'FAQ_CREATED'			=> 'The FAQ has been created successfully.',
	'FAQ_DELETED'			=> 'The FAQ entry has been deleted.',
	'FAQ_DETAILS'			=> 'FAQ Details Page',
	'FAQ_EDITED'			=> 'The FAQ has been edited successfully.',
	'FAQ_EXPANDED'			=> 'Frequently Asked Questions',
	'FAQ_LIST'				=> 'FAQ List',
	'FAQ_NOT_FOUND'			=> 'The specified FAQ could not be found.',

	'NO_FAQ'				=> 'There are no FAQ entries.',

	'QUESTIONS'				=> 'Questions',
));
