<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
define('IN_TITANIA', true);
define('IN_TITANIA_INSTALL', true);
define('UMIL_AUTO', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
titania::add_lang('install');

// Just to be on the safe side, add a php version check.
if (version_compare(PHP_VERSION, '5.2.0') < 0)
{
	die('You are running an unsupported PHP version. Please upgrade to PHP 5.2.0 or higher before trying to install Titania');
}

if (!file_exists(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// Make sure we are not using the same table prefix as phpBB (will cause conflicts).
if (titania::$config->table_prefix == $GLOBALS['table_prefix'])
{
	trigger_error('You can not use the same table prefix for Titania as you are using for phpBB.');
}

$mod_name = 'CUSTOMISATION_DATABASE';
$version_config_name = 'titania_version';

$versions = array(
	'0.1.0'	=> array(
		'table_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, array(
				'COLUMNS'		=> array(
					'attachment_id'			=> array('UINT', NULL, 'auto_increment'),
					'attachment_type'		=> array('TINT:1', 0),
					'object_id'				=> array('UINT', 0),
					'attachment_status'		=> array('TINT:1', 0),
					'physical_filename'		=> array('VCHAR', ''),
					'real_filename'			=> array('VCHAR', ''),
					'download_count'		=> array('UINT', 0),
					'filesize'				=> array('INT:11', 0),
					'filetime'				=> array('INT:11', 0),
					'extension'				=> array('VCHAR:100', ''),
					'mimetype'				=> array('VCHAR:100', ''),
					'hash'					=> array('VCHAR:32', ''),
					'thumbnail'				=> array('BOOL', 0),
				),
				'PRIMARY_KEY'	=> 'attachment_id',
				'KEYS'			=> array(
					'attachment_type'		=> array('INDEX', 'attachment_type'),
					'object_id'				=> array('INDEX', 'object_id'),
					'attachment_status'		=> array('INDEX', 'attachment_status'),
				),
			)),
			array(TITANIA_AUTHORS_TABLE, array(
				'COLUMNS'		=> array(
					'user_id'				=> array('UINT', 0),
					'phpbb_user_id'			=> array('UINT', 0),
					'author_realname'		=> array('VCHAR_CI', ''),
					'author_website'		=> array('VCHAR_UNI:200', ''),
					'author_rating'			=> array('DECIMAL', 0),
					'author_rating_count'	=> array('UINT', 0),
					'author_contribs'		=> array('UINT', 0), // Total # of contribs
					'author_snippets'		=> array('UINT', 0), // Number of snippets
					'author_mods'			=> array('UINT', 0), // Number of mods
					'author_styles'			=> array('UINT', 0), // Number of styles
					'author_visible'		=> array('BOOL', 1),
					'author_desc'			=> array('MTEXT_UNI', ''),
					'author_desc_bitfield'	=> array('VCHAR:255', ''),
					'author_desc_uid'		=> array('VCHAR:8', ''),
					'author_desc_options'	=> array('UINT:11', 7),
				),
				'PRIMARY_KEY'	=> 'user_id',
				'KEYS'			=> array(
					'author_rating'			=> array('INDEX', 'author_rating'),
					'author_contribs'		=> array('INDEX', 'author_contribs'),
					'author_snippets'		=> array('INDEX', 'author_snippets'),
					'author_mods'			=> array('INDEX', 'author_mods'),
					'author_styles'			=> array('INDEX', 'author_styles'),
					'author_visible'		=> array('INDEX', 'author_visible'),
				),
			)),
			array(TITANIA_CATEGORIES_TABLE, array(
				'COLUMNS'		=> array(
					'category_id'				=> array('UINT', NULL, 'auto_increment'),
					'parent_id'					=> array('UINT', 0),
					'left_id'					=> array('UINT', 0),
					'right_id'					=> array('UINT', 0),
					'category_type'				=> array('TINT:1', 0),
					'category_contribs'			=> array('UINT', 0), // Number of items
					'category_visible'			=> array('BOOL', 1),
					'category_name'				=> array('STEXT_UNI', '', 'true_sort'),
					'category_name_clean'		=> array('VCHAR_CI', ''),
					'category_desc'				=> array('MTEXT_UNI', ''),
					'category_desc_bitfield'	=> array('VCHAR:255', ''),
					'category_desc_uid'			=> array('VCHAR:8', ''),
					'category_desc_options'		=> array('UINT:11', 7),
				),
				'PRIMARY_KEY'	=> 'category_id',
				'KEYS'			=> array(
					'parent_id'			=> array('INDEX', 'parent_id'),
					'left_right_id'		=> array('INDEX', array('left_id', 'right_id')),
					'category_type'		=> array('INDEX', 'category_type'),
					'category_visible'	=> array('INDEX', 'category_visible'),
				),
			)),
			array(TITANIA_CONTRIBS_TABLE, array(
				'COLUMNS'		=> array(
					'contrib_id'					=> array('UINT', NULL, 'auto_increment'),
					'contrib_user_id'				=> array('UINT', 0),
					'contrib_type'					=> array('TINT:1', 0),
					'contrib_name'					=> array('STEXT_UNI', '', 'true_sort'),
					'contrib_name_clean'			=> array('VCHAR_CI', ''),
					'contrib_desc'					=> array('MTEXT_UNI', ''),
					'contrib_desc_bitfield'			=> array('VCHAR:255', ''),
					'contrib_desc_uid'				=> array('VCHAR:8', ''),
					'contrib_desc_options'			=> array('UINT:11', 7),
					'contrib_status'				=> array('TINT:2', 0),
					'contrib_downloads'				=> array('UINT', 0),
					'contrib_views'					=> array('UINT', 0),
					'contrib_rating'				=> array('DECIMAL', 0),
					'contrib_rating_count'			=> array('UINT', 0),
					'contrib_visible'				=> array('BOOL', 1),
				),
				'PRIMARY_KEY'	=> 'contrib_id',
				'KEYS'			=> array(
					'contrib_user_id'		=> array('INDEX', 'contrib_user_id'),
					'contrib_type'			=> array('INDEX', 'contrib_type'),
					'contrib_name_clean'	=> array('INDEX', 'contrib_name_clean'),
					'contrib_status'		=> array('INDEX', 'contrib_status'),
					'contrib_downloads'		=> array('INDEX', 'contrib_downloads'),
					'contrib_rating'		=> array('INDEX', 'contrib_rating'),
					'contrib_visible'		=> array('INDEX', 'contrib_visible'),
				),
			)),
			array(TITANIA_CONTRIB_COAUTHORS_TABLE, array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'user_id'				=> array('UINT', 0),
					'active'				=> array('BOOL', 0),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'user_id'),
				'KEYS'			=> array(
					'active'		=> array('INDEX', 'active'),
				),
			)),
			array(TITANIA_CONTRIB_FAQ_TABLE, array(
				'COLUMNS'		=> array(
					'faq_id'				=> array('UINT', NULL, 'auto_increment'),
					'contrib_id'			=> array('UINT', 0),
					'faq_order_id'			=> array('UINT', 0),
					'faq_subject'			=> array('STEXT_UNI', '', 'true_sort'),
					'faq_text'				=> array('MTEXT_UNI', ''),
					'faq_text_bitfield'		=> array('VCHAR:255', ''),
					'faq_text_uid'			=> array('VCHAR:8', ''),
					'faq_text_options'		=> array('UINT:11', 7),
					'faq_views'				=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> 'faq_id',
				'KEYS'			=> array(
					'contrib_id'		=> array('INDEX', 'contrib_id'),
					'faq_order_id'		=> array('INDEX', 'faq_order_id'),
				),
			)),
			array(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'category_id'			=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'category_id'),
			)),
			array(TITANIA_CONTRIB_TAGS_TABLE, array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'tag_id'				=> array('UINT', 0),
					'tag_value'				=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'tag_id'),
			)),
			array(TITANIA_QUEUE_TABLE, array(
				'COLUMNS'		=> array(
					'queue_id'				=> array('UINT', NULL, 'auto_increment'),
					'revision_id'			=> array('UINT', 0),
					'contrib_id'			=> array('UINT', 0),
					'queue_type'			=> array('TINT:1', 0),
					'queue_status'			=> array('TINT:1', 0),
					'submitter_user_id'		=> array('UINT', 0),
					'queue_notes'			=> array('MTEXT_UNI', ''), // Not sure why we need this?
					'queue_notes_bitfield'	=> array('VCHAR:255', ''), // Not sure why we need this?
					'queue_notes_uid'		=> array('VCHAR:8', ''), // Not sure why we need this?
					'queue_notes_options'	=> array('UINT:11', 7), // Not sure why we need this?
					'queue_progress'		=> array('TINT:3', 0),
					'queue_submit_time'		=> array('UINT:11', 0),
					'queue_close_time'		=> array('UINT:11', 0),
				),
				'PRIMARY_KEY'	=> 'queue_id',
				'KEYS'			=> array(
					'revision_id'			=> array('INDEX', 'revision_id'),
					'contrib_id'			=> array('INDEX', 'contrib_id'),
					'queue_type'			=> array('INDEX', 'queue_type'),
					'queue_status'			=> array('INDEX', 'queue_status'),
					'submitter_user_id'		=> array('INDEX', 'submitter_user_id'),
					'queue_submit_time'		=> array('INDEX', 'queue_submit_time'),
				),
			)),
			array(TITANIA_RATINGS_TABLE, array(
				'COLUMNS'		=> array(
					'rating_id'				=> array('UINT', NULL, 'auto_increment'),
					'rating_type_id'		=> array('UINT', 0),
					'rating_user_id'		=> array('UINT', 0),
					'rating_object_id'		=> array('UINT', 0),
					'rating_value'			=> array('DECIMAL', 0), // Not sure if we should allow partial ratings (like 4.5/5) or just integer ratings...
				),
				'PRIMARY_KEY'	=> 'rating_id',
				'KEYS'			=> array(
					'type_user_object'		=> array('UNIQUE', array('rating_type_id', 'rating_user_id', 'rating_object_id')),
				),
			)),
			array(TITANIA_REVISIONS_TABLE, array(
				'COLUMNS'		=> array(
					'revision_id'			=> array('UINT', NULL, 'auto_increment'),
					'contrib_id'			=> array('UINT', 0),
					'contrib_validated'		=> array('BOOL', 0),
					'attachment_id'			=> array('UINT', 0),
					'revision_name'			=> array('STEXT_UNI', '', 'true_sort'),
					'revision_time'			=> array('UINT:11', 0),
				),
				'PRIMARY_KEY'	=> 'revision_id',
				'KEYS'			=> array(
					'contrib_id'			=> array('INDEX', 'contrib_id'),
					'contrib_validated'		=> array('INDEX', 'contrib_validated'),
				),
			)),
			array(TITANIA_TAG_FIELDS_TABLE, array(
				'COLUMNS'		=> array(
					'tag_id'				=> array('UINT', NULL, 'auto_increment'),
					'tag_type_id'			=> array('UINT', 0),
					'tag_field_name'		=> array('XSTEXT_UNI', '', 'true_sort'),
					'tag_clean_name'		=> array('XSTEXT_UNI', '', 'true_sort'),
					'tag_field_desc'		=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> 'tag_id',
				'KEYS'			=> array(
					'tag_type_id'			=> array('INDEX', 'tag_type_id'),
				),
			)),
			array(TITANIA_TAG_TYPES_TABLE, array(
				'COLUMNS'		=> array(
					'tag_type_id'			=> array('UINT', NULL, 'auto_increment'),
					'tag_type_name'			=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> 'tag_type_id',
			)),
			array(TITANIA_WATCH_TABLE, array(
				'COLUMNS'		=> array(
					'watch_type'			=> array('TINT:1', 0),
					'watch_object_id'		=> array('UINT', 0),
					'watch_user_id'			=> array('UINT', 0),
					'watch_mark_time'		=> array('UINT:11', 0),
				),
				'PRIMARY_KEY'	=> array('watch_type', 'watch_object_id', 'watch_user_id'),
			)),
		),

		'permission_add' => array(
			'titania_',
			'titania_rate', // Can rate items
			'titania_rate_reset', // Can reset the rating on items
			'titania_faq_create', // Can create FAQ entries
			'titania_faq_edit', // Can edit own FAQ entries
			'titania_faq_delete', // Can delete own FAQ entries
			'titania_faq_mod', // Can moderate FAQ entries
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array('titania_rate_reset', 'titania_faq_mod')),
			array('ROLE_MOD_FULL', array('titania_rate_reset', 'titania_faq_mod')),
			array('ROLE_USER_FULL', array('titania_rate')),
			array('ROLE_USER_STANDARD', array('titania_rate')),
		),

		/*'module_add' => array(
			array('titania', 0, 'TITANIA_CAT_MAIN'),
			array('titania', 'TITANIA_MAIN',	array('module_basename' => 'main'),		TITANIA_ROOT . 'modules/'),
		),*/

		'custom' => 'titania_data',

		'cache_purge' => '',
	),

	// Merged in 0.1.4
	'0.1.1' => array(),
	'0.1.2' => array(),
	'0.1.3' => array(),
	'0.1.4' => array(),

	'0.1.5' => array(
		'table_add' => array(
			array(TITANIA_TOPICS_TABLE, array(
				'COLUMNS'		=> array(
					'topic_id'						=> array('UINT', NULL, 'auto_increment'),
					'topic_type'					=> array('TINT:1', 0), // Post Type, Main TITANIA_ constants
					'topic_access'					=> array('TINT:1', 0), // Access level, TITANIA_ACCESS_ constants
					'topic_category'				=> array('UINT', 0), // Category for the topic. For the Tracker
					'topic_status'					=> array('UINT', 0), // Topic Status, use tags from the DB
					'topic_assigned'				=> array('VCHAR:255', ''), // Topic assigned status; u- for user, g- for group (followed by the id).  For the tracker
					'topic_sticky'					=> array('BOOL', 0),
					'topic_locked'					=> array('BOOL', 0),
					'topic_approved'				=> array('BOOL', 1),
					'topic_reported'				=> array('BOOL', 0), // True if any posts in the topic are reported
					'topic_deleted'					=> array('BOOL', 0), // True if the topic is soft deleted
					'topic_posts'					=> array('VCHAR', ''), // Post count; separated by : between access levels ('10:9:8' = 10 team; 9 Mod Author; 8 Public)
					'topic_subject'					=> array('STEXT_UNI', ''),
					'topic_time'					=> array('UINT:11', 0),
					'topic_first_post_id'			=> array('UINT', 0),
					'topic_first_post_user_id'		=> array('UINT', 0),
					'topic_first_post_username'		=> array('VCHAR_UNI', ''),
					'topic_first_post_user_colour'	=> array('VCHAR:6', ''),
					'topic_first_post_time'			=> array('UINT:11', 0),
					'topic_last_post_id'			=> array('UINT', 0),
					'topic_last_post_user_id'		=> array('UINT', 0),
					'topic_last_post_username'		=> array('VCHAR_UNI', ''),
					'topic_last_post_user_colour'	=> array('VCHAR:6', ''),
					'topic_last_post_time'			=> array('UINT:11', 0),
					'topic_last_post_subject'		=> array('STEXT_UNI', ''),
				),
				'PRIMARY_KEY'	=> 'topic_id',
				'KEYS'			=> array(
					'topic_type'			=> array('INDEX', 'topic_type'),
					'topic_access'			=> array('INDEX', 'topic_access'),
					'topic_category'		=> array('INDEX', 'topic_category'),
					'topic_status'			=> array('INDEX', 'topic_status'),
					'topic_assigned'		=> array('INDEX', 'topic_assigned'),
					'topic_sticky'			=> array('INDEX', 'topic_sticky'),
					'topic_approved'		=> array('INDEX', 'topic_approved'),
					'topic_reported'		=> array('INDEX', 'topic_reported'),
					'topic_deleted'			=> array('INDEX', 'topic_deleted'),
					'topic_time'			=> array('INDEX', 'topic_time'),
				),
			)),
			array(TITANIA_POSTS_TABLE, array(
				'COLUMNS'		=> array(
					'post_id'				=> array('UINT', NULL, 'auto_increment'),
					'topic_id'				=> array('UINT', 0),
					'post_type'				=> array('TINT:1', 0), // Post Type, Main TITANIA_ constants
					'post_access'			=> array('TINT:1', 0), // Access level, TITANIA_ACCESS_ constants
					'post_locked'			=> array('BOOL', 0),
					'post_approved'			=> array('BOOL', 1),
					'post_reported'			=> array('BOOL', 0),
					'post_attachment'		=> array('BOOL', 0),
					'post_user_id'			=> array('UINT', 0),
					'post_ip'				=> array('VCHAR:40', ''),
					'post_time'				=> array('UINT:11', 0),
					'post_edited'			=> array('UINT:11', 0), // Post edited; 0 for not edited, timestamp if (when) last edited
					'post_deleted'			=> array('UINT:11', 0), // Post deleted; 0 for not edited, timestamp if (when) last edited
					'post_edit_user'		=> array('UINT', 0), // The last user to edit/delete the post
					'post_edit_reason'		=> array('STEXT_UNI', ''), // Reason for deleting/editing
					'post_subject'			=> array('STEXT_UNI', '', 'true_sort'),
					'post_text'				=> array('XSTEXT_UNI', '', 'true_sort'),
					'post_text_bitfield'	=> array('VCHAR:255', ''),
					'post_text_uid'			=> array('VCHAR:8', ''),
					'post_text_options'		=> array('UINT:11', 7),
				),
				'PRIMARY_KEY'	=> 'post_id',
				'KEYS'			=> array(
					'topic_id'				=> array('INDEX', 'topic_id'),
					'post_type'				=> array('INDEX', 'post_type'),
					'post_access'			=> array('INDEX', 'post_access'),
					'post_approved'			=> array('INDEX', 'post_approved'),
					'post_reported'			=> array('INDEX', 'post_reported'),
					'post_user_id'			=> array('INDEX', 'post_user_id'),
					'post_deleted'			=> array('INDEX', 'post_deleted'),
				),
			)),
		),

		'permission_add' => array(
			'titania_post', // Can create new posts
			'titania_post_edit_own', // Can edit own posts
			'titania_post_delete_own', // Can delete own posts
			'titania_post_mod_own', // Can moderate own topics
			'titania_post_mod', // Can moderate topics
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array('titania_post_mod')),
			array('ROLE_MOD_FULL', array('titania_post', 'titania_post_edit_own', 'titania_post_delete_own', 'titania_post_mod_own')),
			array('ROLE_USER_FULL', array('titania_post', 'titania_post_edit_own')),
			array('ROLE_USER_STANDARD', array('titania_post', 'titania_post_edit_own')),
		),
	),

	'0.1.6' => array(
		'table_column_add' => array(
			array(TITANIA_CONTRIB_FAQ_TABLE, 'faq_access', array('TINT:1', 2)),
		),
	),

	'0.1.7' => array(
		'table_column_add' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_views', array('UINT', 0)),
		),
	),

	'0.1.8' => array(
		'permission_add' => array(
			'titania_author_mod', // Can moderate author profiles
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array('titania_author_mod')),
		),
	),

	'0.1.9' => array(
		'permission_add' => array(
			'titania_contrib_submit', // Can submit contrib items
		),

		'permission_set' => array(
			array('ROLE_USER_FULL', array('titania_contrib_submit')),
			array('ROLE_USER_STANDARD', array('titania_contrib_submit')),
		),
	),

	'0.1.10' => array(
		// Add Titania ext groups and default allowed extentions for these groups.
		'custom'	=> 'titania_ext_groups',
	),

	'0.1.11' => array(
		'table_column_add' => array(
			array(TITANIA_TOPICS_TABLE, 'contrib_id', array('UINT', 0)),
		),

		'table_index_add' => array(
			array(TITANIA_TOPICS_TABLE, 'contrib_id'),
		),

		'table_column_update' => array(
			array(TITANIA_POSTS_TABLE, 'post_text', array('MTEXT_UNI', '')),
		),

		'table_index_remove' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_time'),
		),

		'table_column_remove' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_time'),
		),
	),

	'0.1.12' => array(
		'permission_add' => array(
			'titania_topic', // Can create new topics
			'titania_bbcode', // Can post bbcode
			'titania_smilies', // Can post smilies
		),

		'permission_set' => array(
			array('ROLE_USER_FULL', array('titania_topic', 'titania_bbcode', 'titania_smilies')),
			array('ROLE_USER_STANDARD', array('titania_topic', 'titania_bbcode', 'titania_smilies')),
		),
	),

	'0.1.13' => array(
		'table_column_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'is_orphan', array('TINT:1', 1)),
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_access', array('UINT', 2)),
		),
	),

	'0.1.14' => array(
		'table_column_add' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_subject_clean', array('STEXT_UNI', '')),
		),
		'custom' => 'titania_update',
	),

	'0.1.15' => array(
		 'permission_add' => array(
			'titania_contrib_mod',
			),

			'permission_set' => array(
				array('ROLE_ADMIN_FULL', array('titania_contrib_mod')),
			),
        ),

	'0.1.16' => array(
		'table_column_add' => array(
			array(TITANIA_REVISIONS_TABLE, 'validation_date', array('UINT:11', 0)),
			array(TITANIA_REVISIONS_TABLE, 'revision_version', array('VCHAR', '')),
		),
	),

	'0.1.17' => array(
		'table_column_add' => array(
			array(TITANIA_REVISIONS_TABLE, 'revision_release_notes', array('VCHAR', '')),
			array(TITANIA_REVISIONS_TABLE, 'revision_validation_notes', array('VCHAR', '')),
		),
	),

	'0.1.18' => array(
		'table_column_remove' => array(
			array(TITANIA_REVISIONS_TABLE, 'contrib_validated'),
		),
		'table_column_add' => array(
			array(TITANIA_REVISIONS_TABLE, 'revision_validated', array('UINT:11', 0)),
		),
	),

	'0.1.19' => array(
		'table_column_add' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_time', array('UINT:11', 0)),
		),
		'table_index_add' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_time'),
		),
	),

	'0.1.20' => array(
		'table_add' => array(
			array(TITANIA_TRACK_TABLE, array(
				'COLUMNS'		=> array(
					'track_type'			=> array('UINT', 0),
					'track_id'				=> array('UINT', 0),
					'track_user_id'			=> array('UINT', 0),
					'track_time'			=> array('UINT:11', 0),
				),
				'PRIMARY_KEY'	=> array('track_type', 'track_id', 'track_user_id'),
			)),
		),
	),

	'0.1.21' => array(
		'table_column_add' => array(
			array(TITANIA_CONTRIBS_TABLE, 'contrib_last_update', array('TIMESTAMP', 0)),
		),
	),

	'0.1.22' => array(
		'table_column_add' => array(
			array(TITANIA_POSTS_TABLE, 'post_delete_user', array('UINT', 0)),
		),
	),

	'0.1.23' => array(
		'table_column_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'object_type', array('UINT', 0)),
		),
		'table_index_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'object_type'),
		),
		'table_index_remove' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_type')
		),
		'table_column_remove' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_type'),
		),
	),

	'0.1.24' => array(
		'table_column_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_comment', array('TEXT_UNI', '')),
		),
		'table_index_remove' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_status')
		),
		'table_column_remove' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_status'),
		),
		'table_index_add' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_last_post_time'),
		),
	),

	'0.1.25' => array(
		'table_column_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, 'attachment_directory', array('VCHAR', '')),
		),
	),

	'0.1.26' => array(
		'permission_add' => array(
			'titania_post_attach',
		),

		'permission_set' => array(
			array('ROLE_USER_FULL', array('titania_post_attach')),
			array('ROLE_USER_STANDARD', array('titania_post_attach')),
		),
	),

	'0.1.27' => array(
		'permission_add' => array(
			'a_titaniaauth', // Can manage titania permissions
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array('a_titaniaauth')),
		),
	),

	// IF YOU ADD A NEW VERSION DO NOT FORGET TO INCREMENT THE VERSION NUMBER IN common.php!
);

function titania_update($action, $version)
{
	if ($action != 'update')
	{
		return;
	}

	switch ($version)
	{
		case '0.1.13' :
			$sql = 'SELECT topic_id, topic_subject FROM ' . TITANIA_TOPICS_TABLE;
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . ' SET
					topic_subject_clean = \'' . phpbb::$db->sql_escape(titania_url::url_slug($row['topic_subject'])) . '\'
					WHERE topic_id = ' . $row['topic_id'];
				phpbb::$db->sql_query($sql);
			}
			phpbb::$db->sql_freeresult($result);
		break;
	}
}

function titania_data($action, $version)
{
	global $umil;

	if ($action != 'install')
	{
		return;
	}

	$categories = array(
		array(
			'category_id'	=> 1,
			'parent_id'		=> 0,
			'left_id'		=> 1,
			'right_id'		=> 22,
			'category_type'	=> 0,
			'category_name'	=> 'phpBB3',
			'category_name_clean'	=> 'phpBB3',
			'category_desc'			=> '',
			'category_contribs'		=> 1,
		),
		array(
			'category_id'	=> 2,
			'parent_id'		=> 1,
			'left_id'		=> 2,
			'right_id'		=> 19,
			'category_type'	=> 0,
			'category_name'	=> 'CAT_MODIFICATIONS',
			'category_name_clean'	=> 'modifications',
			'category_desc'			=> '',
			'category_contribs'		=> 1,
		),
		array(
			'category_id'	=> 3,
			'parent_id'		=> 1,
			'left_id'		=> 20,
			'right_id'		=> 21,
			'category_type'	=> 2,
			'category_name'	=> 'CAT_STYLES',
			'category_name_clean'	=> 'styles',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 4,
			'parent_id'		=> 2,
			'left_id'		=> 3,
			'right_id'		=> 4,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_COSMETIC',
			'category_name_clean'	=> 'cosmetic',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 5,
			'parent_id'		=> 2,
			'left_id'		=> 5,
			'right_id'		=> 6,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ADMIN_TOOLS',
			'category_name_clean'	=> 'admin-tools',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 6,
			'parent_id'		=> 2,
			'left_id'		=> 7,
			'right_id'		=> 8,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_SECURITY',
			'category_name_clean'	=> 'security',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 7,
			'parent_id'		=> 2,
			'left_id'		=> 9,
			'right_id'		=> 10,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_COMMUNICATION',
			'category_name_clean'	=> 'communication',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 8,
			'parent_id'		=> 2,
			'left_id'		=> 11,
			'right_id'		=> 12,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_PROFILE_UCP',
			'category_name_clean'	=> 'profile',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 9,
			'parent_id'		=> 2,
			'left_id'		=> 13,
			'right_id'		=> 14,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ADDONS',
			'category_name_clean'	=> 'addons',
			'category_desc'			=> '',
			'category_contribs'		=> 1,
		),
		array(
			'category_id'	=> 10,
			'parent_id'		=> 2,
			'left_id'		=> 15,
			'right_id'		=> 16,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ANTI_SPAM',
			'category_name_clean'	=> 'anti-spam',
			'category_desc'			=> '',
			'category_contribs'		=> 0,
		),
		array(
			'category_id'	=> 11,
			'parent_id'		=> 2,
			'left_id'		=> 17,
			'right_id'		=> 18,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ENTERTAINMENT',
			'category_name_clean'	=> 'entertainment',
			'category_desc'			=> '',
			'category_contribs'		=> 1,
		),
	);

	$umil->table_row_insert(TITANIA_CATEGORIES_TABLE, $categories);

	$author = array(array(
		'user_id'			=> phpbb::$user->data['user_id'],
		'author_realname'	=> '|nub',
		'author_website'	=> 'http://teh.nub.com/',
		'author_contribs'	=> 1,
		'author_mods'		=> 1,
		'author_desc'		=> '',
	));
	$umil->table_row_insert(TITANIA_AUTHORS_TABLE, $author);

	$mod = array(array(
		'contrib_id'			=> 1,
		'contrib_user_id'		=> phpbb::$user->data['user_id'],
		'contrib_type'			=> 1,
		'contrib_name'			=> 'Nub Mod',
		'contrib_name_clean'	=> 'nub_mod',
		'contrib_desc'			=> 'This mod will turn all users into nubs.',
		'contrib_desc_bitfield'	=> '',
		'contrib_desc_uid'		=> '',
		'contrib_desc_options'	=> 7,
		'contrib_status'		=> TITANIA_STATUS_NEW,
	));
	$umil->table_row_insert(TITANIA_CONTRIBS_TABLE, $mod);

	$in_categories = array(
		array(
			'category_id'	=> 9,
			'contrib_id'	=> 1,
		),
		array(
			'category_id'	=> 11,
			'contrib_id'	=> 1,
		),
	);
	$umil->table_row_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $in_categories);

	$faq = array(
		array(
			'faq_id'				=> 1,
			'contrib_id'			=> 1,
			'faq_order_id'			=> 1,
			'faq_subject'			=> 'FAQ example 1',
			'faq_text'				=> 'It is only an FAQ example.',
			'faq_text_bitfield'		=> '',
			'faq_text_uid'			=> '',
			'faq_text_options'		=> 7,
		),
		array(
			'faq_id'				=> 2,
			'contrib_id'			=> 1,
			'faq_order_id'			=> 2,
			'faq_subject'			=> 'FAQ example 2',
			'faq_text'				=> 'It is only an FAQ example.',
			'faq_text_bitfield'		=> '',
			'faq_text_uid'			=> '',
			'faq_text_options'		=> 7,
		),
		array(
			'faq_id'				=> 3,
			'contrib_id'			=> 1,
			'faq_order_id'			=> 3,
			'faq_subject'			=> 'FAQ example 3',
			'faq_text'				=> 'It is only an FAQ example.',
			'faq_text_bitfield'		=> '',
			'faq_text_uid'			=> '',
			'faq_text_options'		=> 7,
		),
	);

	$umil->table_row_insert(TITANIA_CONTRIB_FAQ_TABLE, $faq);
}

function titania_ext_groups($action, $version)
{
	$ext_groups = array(
		TITANIA_ATTACH_EXT_CONTRIB		=> array('default' => 'Archives', 'sql' => array('upload_icon' => 'zip.png')),
		TITANIA_ATTACH_EXT_SCREENSHOTS	=> array('default' => 'Images'),
		TITANIA_ATTACH_EXT_SUPPORT		=> array('default' => array('Archives', 'Images')),
		TITANIA_ATTACH_EXT_FAQ			=> array('default' => array('Archives', 'Images')),
	);

	switch ($action)
	{
		case 'install':
		case 'update':
			// Add Titania ext groups.
			foreach ($ext_groups as $group_name => $extra)
			{
				$sql_ary = array(
					'group_name'		=> $group_name,
					'cat_id'			=> 0,
					'allow_group'		=> 0,
					'download_mode'		=> 1,
					'upload_icon'		=> '',
					'max_filesize'		=> 0,
					'allowed_forums'	=> '',
					'allow_in_pm'		=> 0,
				);
				if (isset($extra['sql']))
				{
					$sql_ary = array_merge($sql_ary, $extra['sql']);
				}

				phpbb::$db->sql_query('INSERT INTO ' . EXTENSION_GROUPS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));
				$group_id = phpbb::$db->sql_nextid();

				if (isset($extra['default']))
				{
					$sql_ary = array(
						'SELECT'	=> 'e.extension',

						'FROM'		=> array(EXTENSION_GROUPS_TABLE 	=> 'g'),

						'LEFT_JOIN'	=> array(
							array(
								'FROM'	=> array(EXTENSIONS_TABLE 		=> 'e'),
								'ON'	=> 'e.group_id = g.group_id'
							),
						),

						'WHERE'		=> ((is_array($extra['default'])) ? phpbb::$db->sql_in_set('g.group_name', $extra['default']) : "g.group_name = '{$extra['default']}'"),
					);
					$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
					$result = phpbb::$db->sql_query($sql);

					$sql_ary = array();

					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$sql_ary[] = array(
							'group_id'		=> $group_id,
							'extension'		=> $row['extension'],
						);
					}

					phpbb::$db->sql_freeresult($result);

					phpbb::$db->sql_multi_insert(EXTENSIONS_TABLE, $sql_ary);
				}
			}
		break;

		case 'uninstall':
			// Get group ids Titania ext groups.
			$sql = 'SELECT group_id
				FROM ' . EXTENSION_GROUPS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('group_name', array_keys($ext_groups));
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$sql = 'DELETE FROM ' . EXTENSIONS_TABLE . '
					WHERE group_id = ' . $row['group_id'];
				phpbb::$db->sql_query($sql);
			}
			phpbb::$db->sql_freeresult($result);

			$sql = 'DELETE FROM ' . EXTENSION_GROUPS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('group_name', array_keys($ext_groups));
			phpbb::$db->sql_query($sql);
		break;
	}
}
include(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT);
