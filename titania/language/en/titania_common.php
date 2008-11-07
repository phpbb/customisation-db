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
	'AUTHOR_BY'				=> 'By %s',

	'CACHE_PURGED'			=> 'Cache has been successfully purged',
	'CONFIRM_PURGE_CACHE'	=> 'Are you sure you want to purge the cache?',

	'DATE_ADDED'			=> 'Date Added',
	'DOWNLOAD_COUNT'		=> 'Downloads',

	'ERROR'					=> 'Error',

	'LAST_UPDATE'			=> 'Last Update',

	'NOTICE'				=> 'Notice',

	// MODs CP names
	'MODS_CATEGORIES'		=> 'MOD Categories',
	'MODS_LIST'				=> 'MODs List',
	'MODS_SEARCH_RESULTS'	=> 'MOD Search Results',

	'ORDER'					=> 'Order by',

	'PURGE_CACHE'			=> 'Purge Cache',

	'RATING'				=> 'Rating',
	'RETURN_LAST_PAGE'		=> 'Return to the previous page',

	'SEARCH_RESULTS'		=> 'Search Results',
	'SELECT_SORT_METHOD'	=> 'Select Sort Method',
	'SUCCESS'				=> 'Success',

	'WARNING'				=> 'Warning',

	// Sorting
	'SORT_AUTHOR'			=> 'Sort by Author',
	'SORT_AUTHOR_RATING'	=> 'Sort by Author rating',
	'SORT_CONTRIBS'			=> 'Sort by number of contributions',
	'SORT_MODS'				=> 'Sort by number of MODs',
	'SORT_STYLES'			=> 'Sort by number of Styles',

	'RETURNED_RESULT'		=> '%d result found',
	'RETURNED_RESULTS'		=> '%d results found',
	
	'TITANIA_HOME'			=> 'Titania Home',
	
	// For the MOD overview
	'MODO_TITLE'			=> 'MOD Overview',
	'MODO_GENERAL'			=> 'General Information | Next MOD Breadcrumbs',
	'MODO_ADVANCED'			=> 'Advanced Information',
	'MODO_COMPATABILITY'	=> 'Compatability Information',
	'MODO_DOWNLOAD'			=> 'Download Information',

	'DETAILS_TITLE'			=> 'MOD Details',
	'DETAILS_MOD_TITLE'		=> 'MOD title',
	'DETAILS_MOD_CATEGORY'	=> 'Category',
	'DETAILS_MOD_AUTHOR'	=> 'Mod Author',
	'DETAILS_MOD_VERSION'	=> 'Version',
	'DETAILS_MOD_BRANCH'	=> 'Branch phpBB version',
	'DETAILS_MOD_TIME'		=> 'Installation time',

	'AUTHOR_TITLE'			=> 'MOD Author Details',
	'AUTHOR_MOD_AUTHOR'		=> 'MOD Author (Username)',
	'AUTHOR_PROFILE'		=> 'MOD Author Profile',
	'AUTHOR_LAST'			=> 'Author last visit date',
	'AUTHOR_RR'				=> 'Author Rating and Rank',

	'VALID_TITLE'			=> 'MOD Validation',
	'VALID_STATUS'			=> 'Status',
	'VALID_DATE'			=> 'Validated Date',
	'VALID_VERSION'			=> 'Validated Version',
	'VALID_QUEUE'			=> 'Queue Topic',
	'VALID_TESTED'			=> 'Tested on phpBB Version(s)',
	'VALID_DOWNLOAD'		=> 'Validated Download',

	'RR_TITLE'				=> 'MOD Rating and Reviews',
	'RR_MOD'				=> 'MOD Rating',
	'RR_TEAM'				=> 'Team Rating',
	'RR_RANK'				=> 'MOD Rank',
	'RR_E_RATING'			=> 'Enter Rating',
	'RR_E_REVIEW'			=> 'Enter Review',
	'RR_LIST'				=> 'Review List',	
	
	'COMPLEX_TITLE'			=> 'Complexity',
	'COMPLEX_SCHEMA'		=> 'SQL Schema Changes',
	'COMPLEX_DATA'			=> 'SQL Data Changes',
	'COMPLEX_TEMPLATE'		=> 'Template Changes',
	'COMPLEX_LANGUAGE'		=> 'Language File Changes',
	'COMPLEX_EDIT'			=> 'File Edits',
	'COMPLEX_ADDITIONAL'	=> 'Additional Module',
	
	'STYLES_TITLE'			=> 'Styles Supported',
	'STYLES_PROSILVER'		=> 'proSilver',
	'STYLES_SUBSILVER'		=> 'subSilver2',
	'STYLES_OTHER'			=> 'Other Styles',
	
	'LANGUAGE_TITLE'		=> 'Languages Supported',
	'LANGUAGE_AVAILABLE'	=> 'Languages Available',

	'OTHER_TITLE'			=> 'Other',
	'OTHER_MODX'			=> 'View MODX files',
	'OTHER_FEATURES'		=> 'Features/Details in addition to MOD Description',
	'OTHER_SIMILAR'			=> 'Other Similar MODs',
	'OTHER_MODS'			=> 'Other MODs by this Author',
	
	'DOWNLOAD_TITLE'		=> 'MOD Download',
	'DOWNLOAD_CHECK'		=> 'Download checksum',
	'DOWNLOAD_MOD_TITLE'	=> 'Download Title',
	'DOWNLOAD_FILESIZE'		=> 'Download filesize',
	'DOWNLOAD_URL'			=> 'Download URL',
	'DOWNLOAD_COUNT'		=> 'Download count',

	'DEMO_TITLE'			=> 'MOD Demo and Screenshots',
	'DEMO_URL'				=> 'Demo URL',
	'DEMO_SCREEN'			=> 'Screenshots',

	'SUPPORT_TITLE'			=> 'MOD Support',
	'SUPPORT_TOPIC'			=> 'Announcement/Support Topic',
	'SUPPORT_AUTHOR_URL'	=> 'Author Support URL',
));

