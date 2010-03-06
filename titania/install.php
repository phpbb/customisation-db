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
include(TITANIA_ROOT . 'common.' . PHP_EXT);
titania::add_lang('install');

include(TITANIA_ROOT . 'includes/functions_install.' . PHP_EXT);

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
	'0.1.31'	=> array(
		'table_add' => array(
			array(TITANIA_ATTACHMENTS_TABLE, array(
				'COLUMNS'		=> array(
					'attachment_id'			=> array('UINT', NULL, 'auto_increment'),
					'object_type'			=> array('TINT:1', 0),
					'object_id'				=> array('UINT', 0),
					'attachment_access'		=> array('UINT', 2),
					'attachment_comment'	=> array('TEXT_UNI', ''),
					'attachment_directory'	=> array('VCHAR', ''),
					'physical_filename'		=> array('VCHAR', ''),
					'real_filename'			=> array('VCHAR', ''),
					'download_count'		=> array('UINT', 0),
					'filesize'				=> array('INT:11', 0),
					'filetime'				=> array('INT:11', 0),
					'extension'				=> array('VCHAR:100', ''),
					'mimetype'				=> array('VCHAR:100', ''),
					'hash'					=> array('VCHAR:32', ''),
					'thumbnail'				=> array('BOOL', 0),
					'is_orphan'				=> array('TINT:1', 1),
				),
				'PRIMARY_KEY'	=> 'attachment_id',
				'KEYS'			=> array(
					'object_type'			=> array('INDEX', 'object_type'),
					'object_id'				=> array('INDEX', 'object_id'),
					'attachment_access'		=> array('INDEX', 'attachment_access'),
					'is_orphan'				=> array('INDEX', 'is_orphan'),
				),
			)),
			array(TITANIA_AUTHORS_TABLE, array(
				'COLUMNS'		=> array(
					'author_id'				=> array('UINT', NULL, 'auto_increment'),
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
				'PRIMARY_KEY'	=> 'author_id',
				'KEYS'			=> array(
					'user_id'				=> array('UNIQUE', 'user_id'),
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
					'contrib_last_update'			=> array('TIMESTAMP', 0),
					'contrib_demo'					=> array('VCHAR_UNI:200', ''),
					'contrib_topic'					=> array('UINT', 0), // Store the old topic_id from ariel for the forums
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
					'faq_access'			=> array('TINT:1', 2),
				),
				'PRIMARY_KEY'	=> 'faq_id',
				'KEYS'			=> array(
					'contrib_id'		=> array('INDEX', 'contrib_id'),
					'faq_order_id'		=> array('INDEX', 'faq_order_id'),
					'faq_access'		=> array('INDEX', 'faq_access'),
				),
			)),
			array(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'category_id'			=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'category_id'),
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
					'post_delete_user'		=> array('UINT', 0), // The last user to delete the post
					'post_edit_user'		=> array('UINT', 0), // The last user to edit the post
					'post_edit_reason'		=> array('STEXT_UNI', ''), // Reason for deleting/editing
					'post_subject'			=> array('STEXT_UNI', '', 'true_sort'),
					'post_text'				=> array('MTEXT_UNI', '', 'true_sort'),
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
			array(TITANIA_QUEUE_TABLE, array(
				'COLUMNS'		=> array(
					'queue_id'				=> array('UINT', NULL, 'auto_increment'),
					'revision_id'			=> array('UINT', 0),
					'contrib_id'			=> array('UINT', 0),
					'queue_type'			=> array('TINT:1', 0),
					'queue_status'			=> array('TINT:1', 0),
					'submitter_user_id'		=> array('UINT', 0),
					'queue_notes'			=> array('MTEXT_UNI', ''),
					'queue_notes_bitfield'	=> array('VCHAR:255', ''),
					'queue_notes_uid'		=> array('VCHAR:8', ''),
					'queue_notes_options'	=> array('UINT:11', 7),
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
					'revision_id'				=> array('UINT', NULL, 'auto_increment'),
					'contrib_id'				=> array('UINT', 0),
					'attachment_id'				=> array('UINT', 0),
					'revision_version'			=> array('VCHAR', ''),
					'revision_name'				=> array('STEXT_UNI', '', 'true_sort'),
					'revision_time'				=> array('UINT:11', 0),
					'revision_release_notes'	=> array('VCHAR', ''),
					'revision_validation_notes'	=> array('VCHAR', ''),
					'revision_validated'		=> array('UINT:11', 0),
					'validation_date'			=> array('UINT:11', 0),
					'phpbb_version'				=> array('STEXT', 0),
					'install_time'				=> array('USINT', 0),
					'install_level'				=> array('TINT:1', 0),
					'revision_submitted'		=> array('BOOL', 0), // So we can hide the revision while we are creating it, false means someone is working on creating it (or did not finish creating it)
					'queue_topic_id'			=> array('UINT:11', 0), // Store the queue topic id so we can track it
				),
				'PRIMARY_KEY'	=> 'revision_id',
				'KEYS'			=> array(
					'contrib_id'			=> array('INDEX', 'contrib_id'),
					'revision_validated'	=> array('INDEX', 'revision_validated'),
					'revision_time'			=> array('INDEX', 'revision_time'),
					'validation_date'		=> array('INDEX', 'validation_date'),
					'revision_submitted'	=> array('INDEX', 'revision_submitted'),
				),
			)),
			array(TITANIA_TAG_APPLIED_TABLE, array(
				'COLUMNS'		=> array(
					'object_type'			=> array('UINT', 0),
					'object_id'				=> array('UINT', 0),
					'tag_id'				=> array('UINT', 0),
					'tag_value'				=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> array('object_type', 'object_id', 'tag_id'),
			)),
			array(TITANIA_TAG_FIELDS_TABLE, array(
				'COLUMNS'		=> array(
					'tag_id'				=> array('UINT', NULL, 'auto_increment'),
					'tag_type_id'			=> array('UINT', 0),
					'tag_field_name'		=> array('XSTEXT_UNI', '', 'true_sort'),
					'tag_clean_name'		=> array('XSTEXT_UNI', '', 'true_sort'),
					'tag_field_desc'		=> array('STEXT_UNI', '', 'true_sort'),
					'no_delete'				=> array('BOOL', 0), // A few tags we have to hard-code (like new status for a queue item)
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
			array(TITANIA_TOPICS_TABLE, array(
				'COLUMNS'		=> array(
					'topic_id'						=> array('UINT', NULL, 'auto_increment'),
					'contrib_id'					=> array('UINT', 0),
					'topic_type'					=> array('TINT:1', 0), // Post Type, Main TITANIA_ constants
					'topic_access'					=> array('TINT:1', 0), // Access level, TITANIA_ACCESS_ constants
					'topic_category'				=> array('UINT', 0), // Category for the topic. For the Tracker
					'topic_status'					=> array('UINT', 0), // Topic Status, use tags from the DB
					'topic_assigned'				=> array('VCHAR:255', ''), // Topic assigned status; u- for user, g- for group (followed by the id).  For the tracker
					'topic_time'					=> array('UINT:11', 0),
					'topic_sticky'					=> array('BOOL', 0),
					'topic_locked'					=> array('BOOL', 0),
					'topic_approved'				=> array('BOOL', 1),
					'topic_reported'				=> array('BOOL', 0), // True if any posts in the topic are reported
					'topic_deleted'					=> array('BOOL', 0), // True if the topic is soft deleted
					'topic_views'					=> array('UINT', 0),
					'topic_posts'					=> array('VCHAR', ''), // Post count; separated by : between access levels ('10:9:8' = 10 team; 9 Mod Author; 8 Public)
					'topic_subject'					=> array('STEXT_UNI', ''),
					'topic_subject_clean'			=> array('STEXT_UNI', ''), // used for building the url
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
					'contrib_id'			=> array('INDEX', 'contrib_id'),
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
					'topic_last_post_time'	=> array('INDEX', 'topic_last_post_time'),
				),
			)),
			array(TITANIA_TRACK_TABLE, array(
				'COLUMNS'		=> array(
					'track_type'			=> array('UINT', 0),
					'track_id'				=> array('UINT', 0),
					'track_user_id'			=> array('UINT', 0),
					'track_time'			=> array('UINT:11', 0),
				),
				'PRIMARY_KEY'	=> array('track_type', 'track_id', 'track_user_id'),
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
			'u_titania_',

			'm_titania_author_mod',			// Can moderate author profiles
			'm_titania_contrib_mod',		// Can moderate all contrib items
			'm_titania_rate_reset',			// Can reset the rating on items
			'm_titania_faq_mod',			// Can moderate FAQ entries
			'm_titania_post_mod',			// Can moderate topics

			'u_titania_contrib_submit',		// Can submit contrib items
			'u_titania_rate',				// Can rate items
			'u_titania_faq_create',			// Can create FAQ entries
			'u_titania_faq_edit',			// Can edit own FAQ entries
			'u_titania_faq_delete',			// Can delete own FAQ entries
			'u_titania_topic',				// Can create new topics
			'u_titania_bbcode',				// Can post bbcode
			'u_titania_smilies',			// Can post smilies
			'u_titania_post',				// Can create new posts
			'u_titania_post_edit_own',		// Can edit own posts
			'u_titania_post_delete_own',	// Can delete own posts
			'u_titania_post_mod_own',		// Can moderate own topics
			'u_titania_post_attach',		// Can attach files to posts
		),

		'permission_role_add' => array(
			array('ROLE_TITANIA_MODIFICATION_TEAM', 'm_'),
			array('ROLE_TITANIA_STYLE_TEAM', 'm_'),
			array('ROLE_TITANIA_MODERATOR_TEAM', 'm_'),
			array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'm_'),
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array(
				'm_titania_author_mod',			// Can moderate author profiles
				'm_titania_contrib_mod',		// Can moderate all contrib items
				'm_titania_rate_reset',			// Can reset the rating on items
				'm_titania_faq_mod',			// Can moderate FAQ entries
				'm_titania_post_mod',			// Can moderate topics
				'm_titania_mod_queue',			// Can see the modifications queue
				'm_titania_mod_validate',		// Can validate modifications
				'm_titania_mod_moderate',		// Can moderate modifications
				'm_titania_style_queue',		// Can see the styles queue
				'm_titania_style_validate',		// Can validate styles
				'm_titania_style_moderate',		// Can moderate styles
			)),
			array('ROLE_TITANIA_ADMINISTRATOR_TEAM', array(
				'm_titania_author_mod',			// Can moderate author profiles
				'm_titania_contrib_mod',		// Can moderate all contrib items
				'm_titania_rate_reset',			// Can reset the rating on items
				'm_titania_faq_mod',			// Can moderate FAQ entries
				'm_titania_post_mod',			// Can moderate topics
				'm_titania_mod_queue',			// Can see the modifications queue
				'm_titania_mod_validate',		// Can validate modifications
				'm_titania_mod_moderate',		// Can moderate modifications
				'm_titania_style_queue',		// Can see the styles queue
				'm_titania_style_validate',		// Can validate styles
				'm_titania_style_moderate',		// Can moderate styles
			)),
			array('ROLE_TITANIA_MODIFICATION_TEAM', array(
				'm_titania_author_mod',			// Can moderate author profiles
				'm_titania_faq_mod',			// Can moderate FAQ entries
				'm_titania_post_mod',			// Can moderate topics
				'm_titania_mod_queue',			// Can see the modifications queue
				'm_titania_mod_validate',		// Can validate modifications
				'm_titania_mod_moderate',		// Can moderate modifications
			)),
			array('ROLE_TITANIA_STYLE_TEAM', array(
				'm_titania_author_mod',			// Can moderate author profiles
				'm_titania_faq_mod',			// Can moderate FAQ entries
				'm_titania_post_mod',			// Can moderate topics
				'm_titania_style_queue',		// Can see the styles queue
				'm_titania_style_validate',		// Can validate styles
				'm_titania_style_moderate',		// Can moderate styles
			)),
			array('ROLE_TITANIA_MODERATOR_TEAM', array(
				'm_titania_author_mod',			// Can moderate author profiles
				'm_titania_faq_mod',			// Can moderate FAQ entries
				'm_titania_post_mod',			// Can moderate topics
			)),
			array('ROLE_USER_STANDARD', array(
				'u_titania_contrib_submit',		// Can submit contrib items
				'u_titania_rate',				// Can rate items
				'u_titania_faq_create',			// Can create FAQ entries
				'u_titania_faq_edit',			// Can edit own FAQ entries
				'u_titania_faq_delete',			// Can delete own FAQ entries
				'u_titania_topic',				// Can create new topics
				'u_titania_bbcode',				// Can post bbcode
				'u_titania_smilies',			// Can post smilies
				'u_titania_post',				// Can create new posts
				'u_titania_post_edit_own',		// Can edit own posts
				'u_titania_post_delete_own',	// Can delete own posts
				'u_titania_post_attach',		// Can attach files to posts
			)),
			array('ROLE_USER_FULL', array(
				'u_titania_contrib_submit',		// Can submit contrib items
				'u_titania_rate',				// Can rate items
				'u_titania_faq_create',			// Can create FAQ entries
				'u_titania_faq_edit',			// Can edit own FAQ entries
				'u_titania_faq_delete',			// Can delete own FAQ entries
				'u_titania_topic',				// Can create new topics
				'u_titania_bbcode',				// Can post bbcode
				'u_titania_smilies',			// Can post smilies
				'u_titania_post',				// Can create new posts
				'u_titania_post_edit_own',		// Can edit own posts
				'u_titania_post_delete_own',	// Can delete own posts
				'u_titania_post_attach',		// Can attach files to posts
			)),
		),

		'custom' => 'titania_custom',

		'cache_purge' => array('', 'auth'),
	),
	// Merged in 0.1.31

	'0.1.32' => array(
		'table_index_add' => array(
			array(TITANIA_CONTRIBS_TABLE, 'contrib_last_update'),
		),
		'table_remove' => array(
			TITANIA_WATCH_TABLE,
		),
		'table_add' => array(
			array(TITANIA_WATCH_TABLE, array(
				'COLUMNS'		=> array(
					'watch_type'			=> array('TINT:1', 0),
					'watch_object_type'		=> array('UINT', 0),
					'watch_object_id'		=> array('UINT', 0),
					'watch_user_id'			=> array('UINT', 0),
					'watch_mark_time'		=> array('UINT:11', 0),
				),
				'PRIMARY_KEY'	=> array('watch_object_type', 'watch_object_id', 'watch_user_id', 'watch_type'),
			)),
		)
	),

	'0.1.33' => array(
		'table_column_remove' => array(
			array(TITANIA_QUEUE_TABLE, 'queue_progress'),
		),

		'table_column_add' => array(
			array(TITANIA_QUEUE_TABLE, 'contrib_name_clean', array('VCHAR_CI', '')),
			array(TITANIA_QUEUE_TABLE, 'queue_topic_id', array('UINT', 0)),
			array(TITANIA_QUEUE_TABLE, 'mpv_results', array('MTEXT_UNI', '')),
			array(TITANIA_QUEUE_TABLE, 'mpv_results_bitfield', array('VCHAR:255', '')),
			array(TITANIA_QUEUE_TABLE, 'mpv_results_uid', array('VCHAR:8', '')),
			array(TITANIA_QUEUE_TABLE, 'automod_results', array('MTEXT_UNI', '')),
		),

		'custom' => 'titania_tags',
	),

	'0.1.34' => array(
		'custom' => 'titania_custom',
	),

	'0.1.35' => array(
		'table_column_remove' => array(
			array(TITANIA_REVISIONS_TABLE, 'queue_topic_id'),
		),
	),

	'0.1.36' => array(
		'config_add' => array(
			array('titania_num_contribs', 0, true),
		),
		'custom' => 'titania_ext_groups',
	),

	'0.1.37' => array(
		'table_column_add' => array(
			array(TITANIA_REVISIONS_TABLE, 'revision_queue_id', array('UINT', 0)),
		),
		'custom' => 'titania_custom',
	),

	'0.1.38' => array(
		'table_index_add' => array(
			array(TITANIA_REVISIONS_TABLE, 'revision_queue_id'),
		),
		'custom'	=> 'titania_tags',
	),

	'0.1.39' => array(
		'table_column_add' => array(
			array(TITANIA_QUEUE_TABLE, 'queue_close_user', array('UINT', 0)),
			array(TITANIA_QUEUE_TABLE, 'queue_progress', array('UINT', 0)), // user_id
			array(TITANIA_QUEUE_TABLE, 'queue_progress_time', array('UINT:11', 0)),
		),
	),

	'0.1.40' => array(
		'table_column_add' => array(
			array(TITANIA_TOPICS_TABLE, 'parent_id', array('UINT', 0)),
			array(TITANIA_TOPICS_TABLE, 'topic_url', array('VCHAR_CI', '')),
			array(TITANIA_POSTS_TABLE, 'post_url', array('VCHAR_CI', '')),
		),
		'table_index_add' => array(
			array(TITANIA_TOPICS_TABLE, 'parent_id'),
		),

		'custom' => 'titania_custom',

		'table_column_remove' => array(
			array(TITANIA_TOPICS_TABLE, 'contrib_id'),
			array(TITANIA_QUEUE_TABLE, 'contrib_name_clean'),
		),
	),

	'0.1.41' => array(
		// topic_deleted is now regulated automatically by the topic access level
		'table_index_remove' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_deleted'),
		),
		'table_column_remove' => array(
			array(TITANIA_TOPICS_TABLE, 'topic_deleted'),
		),
	),

	'0.1.42' => array(
		'permission_role_remove' => array(
			array('ROLE_TITANIA_MODIFICATION_TEAM'),
			array('ROLE_TITANIA_STYLE_TEAM'),
			array('ROLE_TITANIA_MODERATOR_TEAM'),
			array('ROLE_TITANIA_ADMINISTRATOR_TEAM'),
		),
		'permission_role_add' => array(
			array('ROLE_TITANIA_MODIFICATION_TEAM', 'u_'),
			array('ROLE_TITANIA_STYLE_TEAM', 'u_'),
			array('ROLE_TITANIA_MODERATOR_TEAM', 'u_'),
			array('ROLE_TITANIA_ADMINISTRATOR_TEAM', 'u_'),
		),

		'permission_remove' => array(
			'u_titania_',

			'm_titania_author_mod',
			'm_titania_contrib_mod',
			'm_titania_rate_reset',
			'm_titania_faq_mod',
			'm_titania_post_mod',
			'm_titania_mod_queue',
			'm_titania_mod_validate',
			'm_titania_mod_moderate',
			'm_titania_style_queue',
			'm_titania_style_validate',
			'm_titania_style_moderate',
		),

		'permission_add' => array(
			'u_titania_mod_author_mod',
			'u_titania_mod_contrib_mod',
			'u_titania_mod_rate_reset',
			'u_titania_mod_faq_mod',
			'u_titania_mod_post_mod',
			'u_titania_mod_modification_queue',
			'u_titania_mod_modification_validate',
			'u_titania_mod_modification_moderate',
			'u_titania_mod_style_queue',
			'u_titania_mod_style_validate',
			'u_titania_mod_style_moderate',
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array(
				'u_titania_mod_author_mod',
				'u_titania_mod_contrib_mod',
				'u_titania_mod_rate_reset',
				'u_titania_mod_faq_mod',
				'u_titania_mod_post_mod',
				'u_titania_mod_modification_queue',
				'u_titania_mod_modification_validate',
				'u_titania_mod_modification_moderate',
				'u_titania_mod_style_queue',
				'u_titania_mod_style_validate',
				'u_titania_mod_style_moderate',
			)),
			array('ROLE_TITANIA_ADMINISTRATOR_TEAM', array(
				'u_titania_mod_author_mod',
				'u_titania_mod_contrib_mod',
				'u_titania_mod_rate_reset',
				'u_titania_mod_faq_mod',
				'u_titania_mod_post_mod',
				'u_titania_mod_modification_queue',
				'u_titania_mod_modification_validate',
				'u_titania_mod_modification_moderate',
				'u_titania_mod_style_queue',
				'u_titania_mod_style_validate',
				'u_titania_mod_style_moderate',
			)),
			array('ROLE_TITANIA_MODIFICATION_TEAM', array(
				'u_titania_mod_author_mod',
				'u_titania_mod_rate_reset',
				'u_titania_mod_faq_mod',
				'u_titania_mod_post_mod',
				'u_titania_mod_modification_queue',
				'u_titania_mod_modification_validate',
				'u_titania_mod_modification_moderate',
			)),
			array('ROLE_TITANIA_STYLE_TEAM', array(
				'u_titania_mod_author_mod',
				'u_titania_mod_rate_reset',
				'u_titania_mod_faq_mod',
				'u_titania_mod_post_mod',
				'u_titania_mod_style_queue',
				'u_titania_mod_style_validate',
				'u_titania_mod_style_moderate',
			)),
			array('ROLE_TITANIA_MODERATOR_TEAM', array(
				'u_titania_mod_author_mod',
				'u_titania_mod_rate_reset',
				'u_titania_mod_faq_mod',
				'u_titania_mod_post_mod',
			)),
		),
	),
	
	'0.1.43' => array(
		'table_column_add' => array(
			array(TITANIA_QUEUE_TABLE, 'revision_repack', array('TINT:1', 1)),
		),
	),

	// IF YOU ADD A NEW VERSION DO NOT FORGET TO INCREMENT THE VERSION NUMBER IN common.php!
);

include(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT);