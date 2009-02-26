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
	'CONTRIB_NOT_FOUND'		=> 'The contribution you requested could not be found.',
	
	'NO_CONTRIB_SELECTED'		=> 'No contrib has been selected.',

	'FAQ_NOT_FOUND'			=> 'The FAQ you requested could not be found.',
	
	'MOD_FAQ_LIST'			=> 'FAQ list',
	'MOD_FAQ_DETAILS'		=> 'FAQ details',

	'FAQ_SUBJECT'			=> 'Subject',
	'FAQ_TEXT'			=> 'Text',
	
	'SUBJECT_EMPTY'			=> 'Subject is empty',
	'TEXT_EMPTY'			=> 'Text is empty',
	
	'EDIT_FAQ'			=> 'Edit FAQ',
	'DELETE_FAQ'			=> 'Delete FAQ',
	'CREATE_FAQ'			=> 'Create FAQ',
	
	'FAQ_CREATED'			=> 'New FAQ entry has been created.',
	'FAQ_EDITED'			=> 'FAQ entry has been updated.',
	
	'RETURN_FAQ'			=> '%sReturn to the FAQ%s',
	'RETURN_FAQ_LIST'		=> '%sReturn to FAQ list%s',
	'BACK_TO_FAQ_LIST'		=> '&laquo; Back to FAQ list',
	
	'FAQ_DESCRIPTION'		=> 'Here is a list of common issues and solution for them.',
	
	
	'SORT_REVISION'			=> 'Sort by revision',
	'SORT_SUBJECT'			=> 'Sort by subject',
	
	'REVISION'			=> 'Revision',
	
	'AUTHOR_BY'			=> 'By %s',
	'U_SEARCH_MODS_AUTHOR'		=> '%1$sOther MODs by %2$s%3$s',

	'rating'				=> array(
		5	=> 'Excellent',
		4	=> 'Good',
		3	=> 'Average',
		2	=> 'Poor',
		1	=> 'Horrible',
	),
));

