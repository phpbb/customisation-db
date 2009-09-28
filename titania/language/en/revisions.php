<?php
/**
*
* authors [English]
*
* @package Titania
* @version $Id$
* @copyright (c) 2009 phpBB Customisation Database Team
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
	'ADD_REVISION'				=> 'Add New Revision',

	'CREATED'					=> 'Created',
	'CREATE_REVISION'			=> 'Create new revision',

	'NO_REVISIONS'				=> 'No revisions for this contribution exist',
	'NOT_VALIDATED'				=> '<b>Currently not validated</b>',

	'REVISION_NOTES'			=> 'Revision Release Notes',
	'REVISION'					=> 'Revision',
	'REVISIONS'					=> 'Revisions',
	'REVISION_UPLOAD_EXPLAIN'	=> 'Please select the file you would like to upload with the button below. Once the file is uploaded, you can set the version, name and release notes',

	'SELECT_FILE'				=> 'Select File',
	'SAVE'						=> 'Save',

	'VALIDATED'					=> 'Validated',
	'VALIDATED_DATE'			=> 'Validation Date',
	'VALIDATION_NOTES'			=> 'Validation Notes',
	'VALIDATION_NOTES_EXPLAIN'	=> 'The',
));
