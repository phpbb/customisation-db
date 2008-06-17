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
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

// Table names
define('CUSTOMISATION_AUTHORS_TABLE',			$cdb_table_prefix . 'authors');
define('CUSTOMISATION_CONTRIBS_TABLE',			$cdb_table_prefix . 'contribs');
define('CUSTOMISATION_CONTRIB_TAGS_TABLE',		$cdb_table_prefix . 'contrib_tags');
define('CUSTOMISATION_DOWNLOADS_TABLE',			$cdb_table_prefix . 'downloads');
define('CUSTOMISATION_QUEUE_TABLE',				$cdb_table_prefix . 'queue');
define('CUSTOMISATION_QUEUE_HISTORY_TABLE',		$cdb_table_prefix . 'queue_history');
define('CUSTOMISATION_REVIEWS_TABLE',			$cdb_table_prefix . 'reviews');
define('CUSTOMISATION_REVISIONS_TABLE',			$cdb_table_prefix . 'revisions');
define('CUSTOMISATION_TAG_FIELDS_TABLE',		$cdb_table_prefix . 'tag_fields');
define('CUSTOMISATION_TAG_TYPES_TABLE',			$cdb_table_prefix . 'tag_types');
define('CUSTOMISATION_WATCH_TABLE',				$cdb_table_prefix . 'watch');

// Customisation (contrib) status
define('STATUS_NEW', 0);
define('STATUS_APPROVED', 1);
define('STATUS_DENIED', 2);

// Customisation (contrib) type
define('CONTRIB_TYPE_MOD', 1);
define('CONTRIB_TYPE_STYLE', 2);
define('CONTRIB_TYPE_SNIPPET', 3);
define('CONTRIB_TYPE_LANG_PACK', 4);
// Define further contrib types based on the tags, and tag_types tables.