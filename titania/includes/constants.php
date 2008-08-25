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
define('CUSTOMISATION_AUTHORS_TABLE',			CDB_TABLE_PREFIX . 'authors');
define('CUSTOMISATION_CONTRIBS_TABLE',			CDB_TABLE_PREFIX . 'contribs');
define('CUSTOMISATION_CONTRIB_TAGS_TABLE',		CDB_TABLE_PREFIX . 'contrib_tags');
define('CUSTOMISATION_DOWNLOADS_TABLE',			CDB_TABLE_PREFIX . 'downloads');
define('CUSTOMISATION_QUEUE_TABLE',				CDB_TABLE_PREFIX . 'queue');
define('CUSTOMISATION_QUEUE_HISTORY_TABLE',		CDB_TABLE_PREFIX . 'queue_history');
define('CUSTOMISATION_REVIEWS_TABLE',			CDB_TABLE_PREFIX . 'reviews');
define('CUSTOMISATION_REVISIONS_TABLE',			CDB_TABLE_PREFIX . 'revisions');
define('CUSTOMISATION_TAG_FIELDS_TABLE',		CDB_TABLE_PREFIX . 'tag_fields');
define('CUSTOMISATION_TAG_TYPES_TABLE',			CDB_TABLE_PREFIX . 'tag_types');
define('CUSTOMISATION_WATCH_TABLE',				CDB_TABLE_PREFIX . 'watch');

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