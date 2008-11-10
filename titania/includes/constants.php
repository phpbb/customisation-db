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

define('THEME_PATH', TITANIA_ROOT . 'theme/');

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
define('CUSTOMISATION_CONTRIB_FAQ_TABLE',		CDB_TABLE_PREFIX . 'contrib_faq');

// Customisation (contrib) status
define('STATUS_NEW', 0);
define('STATUS_APPROVED', 1);
define('STATUS_DENIED', 2);

// Tag types
define('TAG_TYPE_MOD_CATEGORY', 1);
define('TAG_TYPE_COMPONENT', 2);
define('TAG_TYPE_COMPLEXITY', 3);

// Errorbox types
define('ERROR_ERROR', 1);
define('ERROR_SUCCESS', 2);

// Header status codes
define('HEADER_OK',					200);
define('HEADER_CREATED',			201);
define('HEADER_ACCEPTED',			202);
define('HEADER_NO_CONTENT', 		204);
define('HEADER_RESET_CONTENT',		205);
define('HEADER_MULTIPLE_CHOICES',	300);
define('HEADER_MOVED_PERMANENTLY',	301);
define('HEADER_FOUND',				302); // Moved Temporarily
define('HEADER_SEE_OTHER',			303);
define('HEADER_NOT_MODIFIED',		304);
define('HEADER_TEMPORARY_REDIRECT',	307);
define('HEADER_BAD_REQUEST',		400);
define('HEADER_UNAUTHORIZED',		401);
define('HEADER_FORBIDDEN',			403);
define('HEADER_NOT_FOUND',			404);
define('HEADER_NOT_ACCEPTABLE',		406);
define('HEADER_CONFLICT',			409);
define('HEADER_GONE',				410);
define('HEADER_INTERNAL_SERVER_ERROR',	500);
define('HEADER_NOT_IMPLEMENTED',	501);
define('HEADER_BAD_GATEWAY',		502);
define('HEADER_SERVICE_UNAVAILABLE',503);

// Customisation (contrib) type
define('CONTRIB_TYPE_MOD', 1);
define('CONTRIB_TYPE_STYLE', 2);
define('CONTRIB_TYPE_SNIPPET', 3);
define('CONTRIB_TYPE_LANG_PACK', 4);

// Author constants
define('AUTHOR_HIDDEN', 0);
define('AUTHOR_VISIBLE', 1);
// Define further contrib types based on the tags, and tag_types tables.

define('DEFAULT_OFFSET_LIMIT', 25);
define('MAX_OFFSET_LIMIT', 100);

define('U_PHPBBCOM_VIEWPROFILE', 'http://www.phpbb.com/community/memberlist.php?mode=viewprofile');
