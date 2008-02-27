<?php
/**
*
* common [English]
*
* @package ariel
* @version $Id: lang_ariel.php,v 1.14 2007/11/08 21:03:55 evil3 Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'MODS'						=> 'Modifications',
	'STYLES'					=> 'Styles',

	'CONTRIB_HOME'				=> 'Home',
	'MODS_HOME'					=> 'Modifications home',
	'STYLES_HOME'				=> 'Styles home',

	'CONTRIB_SEARCH'			=> 'Search',

	'MODS_BROWSE'				=> 'Browse modifications',
	'MODS_ALL'					=> 'All modifications',
	'STYLES_BROWSE'				=> 'Browse styles',
	'STYLES_ALL'				=> 'All styles',

	'MODS_TEAM'					=> 'MOD Team',
	'MODS_MY_MODS'				=> 'My modifications',
	'MODS_DB'					=> 'Modifications database',
	'STYLES_TEAM'				=> 'Styles Team',
	'STYLES_MY_STYLES'			=> 'My styles',
	'STYLES_DB'					=> 'Styles database',
	'CONTRIB_POPULAR'			=> 'Most popular',
	'CONTRIB_POPULAR_DOWNLOADS'	=> 'By downloads',
	'CONTRIB_POPULAR_RATING'	=> 'By rating',
	'CONTRIB_NO_SUCH_TAG'		=> 'No matching tags found',

	'MODS_NO_PERMISSIONS'		=> 'You do not have permissions to alter this modification',
	'MODS_NO_MATCH'				=> 'There are no modifications matching this description',
	'STYLES_NO_PERMISSIONS'		=> 'You do not have permissions to alter this style',
	'STYLES_NO_MATCH'			=> 'There are no styles matching this description',

	'NO_TAG_FOUND' 				=> 'No valid tag found',

	'CONTRIB_OVERVIEW'			=> 'Overview',
	'CONTRIB_VALIDATE'			=> 'Validate',
	'CONTRIB_MANAGE'			=> 'Manage',
	'CONTRIB_HISTORY'			=> 'History',
	'CONTRIB_DISCUSS'			=> 'Discuss',
	'CONTRIB_REPACK'			=> 'Repack',

	'NO_ITEMS_QUEUE'			=> 'There are no queued items matching that description',

	'MODS_SEARCH'				=> 'Search the modifications database',
	'MODS_SEARCH_DESC'		    => 'Search for keywords',

	'STYLES_SEARCH'				=> 'Search the styles database',
	'STYLES_SEARCH_DESC'		=> 'Search for keywords',

	'MODS_CREATE'				=> 'Submit a modification',
	'STYLES_CREATE'				=> 'Submit a style',

	'MODS_QUEUE'				=> 'Modifications queue',
	'STYLES_QUEUE'				=> 'Styles queue',

	'MODS_VALIDATE_EXPLAIN'		=> 'Does this modification validate?',
	'STYLES_VALIDATE_EXPLAIN'	=> 'Does this style validate?',
	'PENDING_OPERATIONS'		=> 'Pending operations',

	'CONTRIB_ACTIONS'			=> array(
		QUEUE_CREATE		=> 'Create',
		QUEUE_UPDATE		=> 'Update',
		QUEUE_DESCRIPTION	=> 'Alter description',
		QUEUE_TAGS			=> 'Change tags',
		QUEUE_AUTHOR		=> 'Change ownership'
	),

	'CONTRIB_OLD'	=> 'Old',
	'CONTRIB_NEW'	=> 'New',

	'ARIEL_DISALLOWED_EXTENSION'	=> 'The extension %s is not allowed.',
	'ARIEL_EMPTY_REMOTE_DATA'		=> 'The file could not be uploaded; the remote data appears to be invalid or corrupted.',
	'ARIEL_EMPTY_FILEUPLOAD'		=> 'The uploaded file is empty.',
	'ARIEL_INVALID_FILENAME'		=> '%s is an invalid filename.',
	'ARIEL_NOT_UPLOADED'			=> 'File could not be uploaded.',
	'ARIEL_PARTIAL_UPLOAD'			=> 'The uploaded file was only partially uploaded.',
	'ARIEL_PHP_SIZE_NA'				=> 'The file is too large.',
	'ARIEL_PHP_SIZE_OVERRUN'		=> 'The file is too large; the maximum upload size is %d MB.',
	'ARIEL_URL_INVALID'				=> 'The URL you specified is invalid.',
	'ARIEL_URL_NOT_FOUND'			=> 'The file specified could not be found.',
	'ARIEL_WRONG_FILESIZE'			=> 'The avatar must be between 0 and %1d %2s.',

	// used in ariel adm
	'ARIEL_GLOBAL'					=> 'Ariel global configuration',
	'ARIEL_GLOBAL_EXPLAIN' 	=> 'Here you can set the global Ariel configurion, like the MOD/Style release forum etc.',
	'UNAUTHORISED_BBCODE'		=> '', // We love hacks.
));
?>