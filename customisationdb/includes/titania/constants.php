<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Table prefix
$cdb_table_prefix = 'customisation_';

// Table names
define('CDB_AUTHORS_TABLE',				$cdb_table_prefix . 'authors');
define('CDB_CONTRIBS_TABLE',			$cdb_table_prefix . 'contribs');
define('CDB_CONTRIB_TAGS_TABLE',		$cdb_table_prefix . 'contrib_tags');
define('CDB_DOWNLOADS_TABLE',			$cdb_table_prefix . 'downloads');
define('CDB_QUEUE_TABLE',				$cdb_table_prefix . 'queue');
define('CDB_QUEUE_HISTORY_TABLE',		$cdb_table_prefix . 'queue_history');
define('CDB_REVIEWS_TABLE',				$cdb_table_prefix . 'reviews');
define('CDB_REVISIONS_TABLE',			$cdb_table_prefix . 'revisions');
define('CDB_TAG_FIELDS_TABLE',			$cdb_table_prefix . 'tag_fields');
define('CDB_TAG_TYPES_TABLE',			$cdb_table_prefix . 'tag_types');
define('CDB_WATCH_TABLE',				$cdb_table_prefix . 'watch');
