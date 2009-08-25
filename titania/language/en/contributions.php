<?php
/**
*
* contribution [English]
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
	'CONTRIB'						=> 'Contribution',
	'CONTRIB_AUTHOR'				=> 'Contribution Author',
	'CONTRIB_DESCRIPTION'			=> 'Contribution Description',
	'CONTRIB_DETAILS'				=> 'Contribution Details',
	'CONTRIB_FAQ'					=> 'FAQ',
	'CONTRIB_TYPE'					=> 'Contribution Type',
	'CONTRIB_CATEGORY'				=> 'Contribution Category',
	'CONTRIB_NAME'					=> 'Contribution',
	'CONTRIB_PERMALINK'				=> 'Contribution Permalink',
	'CONTRIB_RELEASE_DATE'			=> 'Release date',
	'CONTRIB_SUPPORT'				=> 'Discussion/Support',
	'CONTRIB_TITLE'					=> 'Contribution Title',
	'CONTRIB_UPDATE_DATE'			=> 'Last updated',
	'CONTRIBUTIONS'					=> 'Contributions',
	'CONTRIB_NOT_FOUND'				=> 'The contribution you requested could not be found.',
	'CONTRIB_NAME_EXISTS'			=> 'The unique name has already been reserved.',

	'CONTRIB_CREATED'				=> 'The contribution has been created successfully',
	
	'DOWNLOADS'						=> 'Downloads',
	'DOWNLOADS_PER_DAY'				=> '%.2f Downloads per Day',
	'DOWNLOADS_TOTAL'				=> 'Total Downloads',
	'DOWNLOADS_VERSION'				=> 'Version Downloads',
	'DOWNLOAD_CHECKSUM'				=> 'MD5 checksum',

	'ERROR_CONTRIB_EMAIL_FRIEND'	=> 'You are not permitted to recommend this contribution to someone else.',

	'EMPTY_CONTRIB_NAME'			=> 'Enter the contrib name',
	'EMPTY_CONTRIB_DESC'			=> 'Enter the contrib description',
	'EMPTY_CONTRIB_PERMALINK'		=> 'Enter your proposal for permalink for the contrib',
	'EMPTY_CONTRIB_TYPE'			=> 'Select one contrib type at least',
	'EMPTY_CATEGORY'				=> 'Select one category at least',
	
	'LOGIN_EXPLAIN_CONTRIB'			=> 'In order to create a new contribution you need to be registered',

	'NEW_TOPIC' 					=> 'New Topic',
	'NEW_CONTRIBUTION'				=> 'New Contribution',
	'EDIT_CONTRIBUTION'				=> 'Edit Contribution',
	
	'CONTRIB_EDITED'				=> 'The contribution has been successfully edited.',
	
	'SELECT_CONTRIB_TYPE'			=> '-- Select contribution type --',
	
	'INVALID_PERMALINK'				=> 'You need to enter a valid permalink, for example: %s',
));
