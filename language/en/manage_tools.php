<?php
/**
 *
 * This file is part of the phpBB Customisation Database package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'CONFIRM_TOOL_ACTION'			=> 'Are you sure you want to run this tool?',
	'FIX_LEFT_RIGHT_IDS'			=> 'Fix Left/Right ID’s',
	'FIX_LEFT_RIGHT_IDS_CONFIRM'	=> 'Are you sure you want to fix the left and right ID’s?<br /><br /><strong>Backup your database before running this tool!</strong>',

	'LEFT_RIGHT_IDS_FIX_SUCCESS'	=> 'The left/right ID’s have been successfully fixed.',
	'LEFT_RIGHT_IDS_NO_CHANGE'		=> 'The tool has finished going through all of the left and right id’s and all rows are already correct so no changes were made.',


	'INDEXING_CONTRIBS'	=> 'Indexing Contributions',
	'INDEXING_FAQ'		=> 'Indexing FAQ',
	'INDEXING_POSTS'	=> 'Indexing Posts',
	'REINDEX'			=> 'Reindex Search',
	'REINDEX_CONFIRM'	=> 'Are you sure you want to begin reindexing the search system?  This can take a significantly long period of time.',
	'REINDEX_STATUS'	=> '%s - section %d of 3 - %s',
	'SECTION_STATUS'	=> 'part %d of %d',
	'TRUNCATING_SEARCH'	=> 'Truncating Search',

	'PLEASE_WAIT'		=> 'Please wait...',

	'REBUILD_COMPOSER_REPO'	=> 'Rebuild Composer repository',

	'REBUILD_TOPIC_URLS'	=> 'Rebuild topic URL’s',

	'RESYNC_CONTRIB_COUNT'				=> 'Resynchronise contribution counts',
	'RESYNC_CONTRIB_COUNT_COMPLETE'		=> 'All contribution counts have been resynchronised.',
	'RESYNC_CONTRIB_COUNT_CONFIRM'		=> 'Are you sure that you want to resynchronise all contribution counts?  This can take a significantly long period of time.',
	'RESYNC_CONTRIB_COUNT_PROGRESS'		=> '%1$d contributions completed of %2$d. Please wait…',

	'RESYNC_DOTTED_TOPICS'				=> 'Resynchronise dotted topics',
	'RESYNC_DOTTED_TOPICS_COMPLETE'		=> 'All dotted topics have been resynchronised.',
	'RESYNC_DOTTED_TOPICS_CONFIRM'		=> 'Are you sure that you want to resynchronise all dotted topics?',


	'TOOL_PROGRESS'						=> '%1$d completed. Please wait…',
	'TOOL_PROGRESS_TOTAL'				=> '%1$d/%2$d completed. Please wait…',

	'UPDATE_RELEASE_TOPICS'				=> 'Update all contrib release topics in forum database',
	'UPDATE_RELEASE_TOPICS_COMPLETE'	=> 'All contrib release topics have been updated.',
	'UPDATE_RELEASE_TOPICS_CONFIRM'		=> 'Are you sure you want to update all contrib release topics in forum database?  This can take a significantly long period of time.',
	'UPDATE_RELEASE_TOPICS_PROGRESS'	=> '%1$d topics completed of %2$d. Please wait…',
));
