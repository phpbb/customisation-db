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

// phpBB 3.x compatibility
global $phpbb_root_path, $phpEx;
$phpbb_root_path = PHPBB_ROOT_PATH;
$phpEx = PHP_EXT;

// Table names
$table_prefix = titania::$config->table_prefix;
define('TITANIA_ATTACHMENTS_TABLE',				$table_prefix . 'attachments');
define('TITANIA_AUTHORS_TABLE',					$table_prefix . 'authors');
define('TITANIA_CATEGORIES_TABLE',				$table_prefix . 'categories');
define('TITANIA_CONTRIBS_TABLE',				$table_prefix . 'contribs');
define('TITANIA_CONTRIB_COAUTHORS_TABLE',		$table_prefix . 'contrib_coauthors');
define('TITANIA_CONTRIB_FAQ_TABLE',				$table_prefix . 'contrib_faq');
define('TITANIA_CONTRIB_IN_CATEGORIES_TABLE',	$table_prefix . 'contrib_in_categories');
define('TITANIA_CONTRIB_TAGS_TABLE',			$table_prefix . 'contrib_tags');  // todo remove
define('TITANIA_POSTS_TABLE',					$table_prefix . 'posts');
define('TITANIA_QUEUE_TABLE',					$table_prefix . 'queue');  // todo remove
define('TITANIA_RATINGS_TABLE',					$table_prefix . 'ratings');
define('TITANIA_REVISIONS_TABLE',				$table_prefix . 'revisions');
define('TITANIA_TAG_APPLIED_TABLE',				$table_prefix . 'tag_applied');
define('TITANIA_TAG_FIELDS_TABLE',				$table_prefix . 'tag_fields');
define('TITANIA_TAG_TYPES_TABLE',				$table_prefix . 'tag_types');
define('TITANIA_TOPICS_TABLE',					$table_prefix . 'topics');
define('TITANIA_TRACK_TABLE',					$table_prefix . 'track');
define('TITANIA_WATCH_TABLE',					$table_prefix . 'watch');

// Contribution revision/queue status
define('TITANIA_QUEUE_DENIED', -2); // Special case to hide denied revisions from the queue
define('TITANIA_QUEUE_APPROVED', -1); // Special case to hide approved revisions from the queue
define('TITANIA_QUEUE_NEW', 1); // Same as QUEUE_NEW in the Tag Fields table

// Contrib status
define('TITANIA_CONTRIB_NEW', 1); // Does not have any validated revisions
define('TITANIA_CONTRIB_APPROVED', 2); // Has at least one validated revision
define('TITANIA_CONTRIB_CLEANED', 3); // Cleaned up old items

// Main TYPE constants (use whenever possible)
define('TITANIA_CONTRIB', 1);
define('TITANIA_FAQ', 2);
define('TITANIA_QUEUE', 3);
define('TITANIA_SUPPORT', 4);
define('TITANIA_TRACKER', 5);
define('TITANIA_TOPIC', 6);
define('TITANIA_AUTHOR', 7);
define('TITANIA_CATEGORY', 8);

// Errorbox types
define('TITANIA_ERROR', 1);
define('TITANIA_SUCCESS', 2);

// Author constants
define('TITANIA_AUTHOR_HIDDEN', 0);
define('TITANIA_AUTHOR_VISIBLE', 1);

// Attachment extension groups; use the group_name stored in the phpbb extension groups table
define('TITANIA_ATTACH_EXT_CONTRIB', 'Titania Contributions');
define('TITANIA_ATTACH_EXT_SCREENSHOTS', 'Titania Screenshots');
define('TITANIA_ATTACH_EXT_FAQ', 'Titania Posts: FAQ');
define('TITANIA_ATTACH_EXT_SUPPORT', 'Titania Posts: Support');

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