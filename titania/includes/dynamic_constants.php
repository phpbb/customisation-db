<?php
/**
*
* @package Titania
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

// Some often used path constants
if (!defined('PHPBB_ROOT_PATH'))
{
	define('PHPBB_ROOT_PATH', TITANIA_ROOT . titania::$config->phpbb_root_path);
}

// phpBB 3.x compatibility
global $phpbb_root_path, $phpEx;
$phpbb_root_path = PHPBB_ROOT_PATH;
$phpEx = PHP_EXT;

// Table names
$table_prefix = titania::$config->table_prefix;
define('TITANIA_ATTACHMENTS_TABLE',				$table_prefix . 'attachments');
define('TITANIA_ATTENTION_TABLE',				$table_prefix . 'attention');
define('TITANIA_AUTOMOD_QUEUE_TABLE',			$table_prefix . 'automod_queue');
define('TITANIA_AUTHORS_TABLE',					$table_prefix . 'authors');
define('TITANIA_CATEGORIES_TABLE',				$table_prefix . 'categories');
define('TITANIA_CONTRIBS_TABLE',				$table_prefix . 'contribs');
define('TITANIA_CONTRIB_COAUTHORS_TABLE',		$table_prefix . 'contrib_coauthors');
define('TITANIA_CONTRIB_FAQ_TABLE',				$table_prefix . 'contrib_faq');
define('TITANIA_CONTRIB_IN_CATEGORIES_TABLE',	$table_prefix . 'contrib_in_categories');
define('TITANIA_POSTS_TABLE',					$table_prefix . 'posts');
define('TITANIA_QUEUE_TABLE',					$table_prefix . 'queue');
define('TITANIA_RATINGS_TABLE',					$table_prefix . 'ratings');
define('TITANIA_REVISIONS_TABLE',				$table_prefix . 'revisions');
define('TITANIA_REVISIONS_PHPBB_TABLE',			$table_prefix . 'revisions_phpbb');
define('TITANIA_TAG_APPLIED_TABLE',				$table_prefix . 'tag_applied');
define('TITANIA_TAG_FIELDS_TABLE',				$table_prefix . 'tag_fields');
define('TITANIA_TAG_TYPES_TABLE',				$table_prefix . 'tag_types');
define('TITANIA_TOPICS_TABLE',					$table_prefix . 'topics');
define('TITANIA_TRACK_TABLE',					$table_prefix . 'track');
define('TITANIA_WATCH_TABLE',					$table_prefix . 'watch');