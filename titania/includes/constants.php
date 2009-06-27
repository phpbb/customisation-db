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

// Without this we cannot include phpBB 3.0.x scripts.
define('IN_PHPBB', true);

// Some often used path constants
define('PHPBB_ROOT_PATH', TITANIA_ROOT . titania::$config->phpbb_root_path);
define('UMIL_ROOT_PATH', TITANIA_ROOT . titania::$config->umil_root_path);

// Table names
$table_prefix = titania::$config->table_prefix;
define('TITANIA_ATTACHMENTS_TABLE',				$table_prefix . 'attachments');
define('TITANIA_AUTHORS_TABLE',					$table_prefix . 'authors');
define('TITANIA_CATEGORIES_TABLE',				$table_prefix . 'categories');
define('TITANIA_CONTRIBS_TABLE',				$table_prefix . 'contribs');
define('TITANIA_CONTRIB_COAUTHORS_TABLE',		$table_prefix . 'contrib_coauthors');
define('TITANIA_CONTRIB_FAQ_TABLE',				$table_prefix . 'contrib_faq');
define('TITANIA_CONTRIB_IN_CATEGORIES_TABLE',	$table_prefix . 'contrib_in_categories');
define('TITANIA_CONTRIB_TAGS_TABLE',			$table_prefix . 'contrib_tags');
define('TITANIA_POSTS_TABLE',					$table_prefix . 'posts');
define('TITANIA_QUEUE_TABLE',					$table_prefix . 'queue');
define('TITANIA_RATINGS_TABLE',					$table_prefix . 'ratings');
define('TITANIA_REVISIONS_TABLE',				$table_prefix . 'revisions');
define('TITANIA_TAG_FIELDS_TABLE',				$table_prefix . 'tag_fields');
define('TITANIA_TAG_TYPES_TABLE',				$table_prefix . 'tag_types');
define('TITANIA_TOPICS_TABLE',					$table_prefix . 'topics');
define('TITANIA_WATCH_TABLE',					$table_prefix . 'watch');

// Contribution revision status
define('TITANIA_STATUS_NEW', 0);
define('TITANIA_STATUS_APPROVED', 1);
define('TITANIA_STATUS_DENIED', 2);
define('TITANIA_STATUS_TESTING', 3);
define('TITANIA_STATUS_ATTENTION', 4);
define('TITANIA_STATUS_APPROVE', 5); // Awaiting approve
define('TITANIA_STATUS_DENY', 6); // Awating deny

// Tag types
define('TAG_TYPE_MOD_CATEGORY', 1);
define('TAG_TYPE_COMPONENT', 2);
define('TAG_TYPE_COMPLEXITY', 3);

// Errorbox types
define('TITANIA_ERROR', 1);
define('TITANIA_SUCCESS', 2);

// Contribution type
define('TITANIA_TYPE_CATEGORY', 0); // Used in categories
define('TITANIA_TYPE_MOD', 1);
define('TITANIA_TYPE_STYLE', 2);
define('TITANIA_TYPE_SNIPPET', 3);
define('TITANIA_TYPE_LANG_PACK', 4);

// Author constants
define('TITANIA_AUTHOR_HIDDEN', 0);
define('TITANIA_AUTHOR_VISIBLE', 1);

// Rating Type Constants
define('TITANIA_RATING_AUTHOR', 1);
define('TITANIA_RATING_CONTRIB', 2);

// Download types
define('TITANIA_DOWNLOAD_CONTRIB', 1);
define('TITANIA_DOWNLOAD_POST', 2);

// Post types
define('TITANIA_POST_DEFAULT', 1);
define('TITANIA_POST_QUEUE', 2);
define('TITANIA_POST_TRACKER', 3);
define('TITANIA_POST_REVIEW', 4);

// Access Levels
define('TITANIA_ACCESS_TEAMS', 0);
define('TITANIA_ACCESS_AUTHORS', 1);
define('TITANIA_ACCESS_PUBLIC', 2);

// Header status codes
define('HEADER_OK',						200);
define('HEADER_CREATED',				201);
define('HEADER_ACCEPTED',				202);
define('HEADER_NO_CONTENT', 			204);
define('HEADER_RESET_CONTENT',			205);
define('HEADER_MULTIPLE_CHOICES',		300);
define('HEADER_MOVED_PERMANENTLY',		301);
define('HEADER_FOUND',					302); // Moved Temporarily
define('HEADER_SEE_OTHER',				303);
define('HEADER_NOT_MODIFIED',			304);
define('HEADER_TEMPORARY_REDIRECT',		307);
define('HEADER_BAD_REQUEST',			400);
define('HEADER_UNAUTHORIZED',			401);
define('HEADER_FORBIDDEN',				403);
define('HEADER_NOT_FOUND',				404);
define('HEADER_NOT_ACCEPTABLE',			406);
define('HEADER_CONFLICT',				409);
define('HEADER_GONE',					410);
define('HEADER_INTERNAL_SERVER_ERROR',	500);
define('HEADER_NOT_IMPLEMENTED',		501);
define('HEADER_BAD_GATEWAY',			502);
define('HEADER_SERVICE_UNAVAILABLE',	503);