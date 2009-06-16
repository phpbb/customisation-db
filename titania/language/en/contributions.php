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
	'CONTRIB_CAT_DETAILS'			=> 'Details',
	'CONTRIB_CAT_FAQ'				=> 'FAQ',
	'CONTRIB_CAT_MAIN'				=> 'Contributions',
	'CONTRIB_CAT_SUPPORT'			=> 'Discussion/Support',
	'CONTRIB_DESCRIPTION'			=> 'Contribution Description',
	'CONTRIB_DETAILS'				=> 'Contribution Details',
	'CONTRIB_FAQ'					=> 'FAQ',
	'CONTRIB_NAME'					=> 'Contribution',
	'CONTRIB_RELEASE_DATE'			=> 'Release date',
	'CONTRIB_SUPPORT'				=> 'Discussion/Support',
	'CONTRIB_TITLE'					=> 'Contribution Title',
	'CONTRIB_UPDATE_DATE'			=> 'Last updated',

	'DOWNLOADS'						=> 'Downloads',
	'DOWNLOADS_PER_DAY'				=> '%.2f Downloads per Day',
	'DOWNLOADS_TOTAL'				=> 'Total Downloads',
	'DOWNLOADS_VERSION'				=> 'Version Downloads',
	'DOWNLOAD_CHECKSUM'				=> 'MD5 checksum',

	'ERROR_CONTRIB_EMAIL_FRIEND'	=> 'You are not permitted to recommend this contribution to someone else.',
	'ERROR_CONTRIB_NOT_FOUND'		=> 'The contribution you requested could not be found.',
));
