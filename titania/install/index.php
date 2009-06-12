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
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', '../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
titania::add_lang('install');

if (!file_exists(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

$mod_name = 'CUSTOMISATION_DATABASE';
$version_config_name = 'titania_version';


$versions = array(
	'0.1.0'	=> array(
		'table_add' => array(
			array('customisation_attachments', array(
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
			array('customisation_authors', array(
				'COLUMNS'		=> array(
					'author_id'				=> array('UINT', NULL, 'auto_increment'),
					'user_id'				=> array('UINT', 0),
					'phpbb_user_id'			=> array('UINT', 0),
					'author_username'		=> array('VCHAR_CI', ''),
					'author_username_clean'	=> array('VCHAR_CI', ''),
					'author_realname'		=> array('VCHAR_CI', ''),
					'author_website'		=> array('VCHAR_UNI:200', ''),
					'author_email'			=> array('VCHAR_UNI:100', ''),
					'author_email_hash'		=> array('BINT', 0),
					'author_rating'			=> array('DECIMAL', 0),
					'author_rating_count'	=> array('UINT', 0),
					'author_contribs'		=> array('UINT', 0), //
					'author_snippets'		=> array('UINT', 0), // Number of snippets
					'author_mods'			=> array('UINT', 0), // Number of mods
					'author_styles'			=> array('UINT', 0), // Number of styles
					'author_visible'		=> array('BOOL', 1),
				),
				'PRIMARY_KEY'	=> 'author_id',
				'KEYS'			=> array(
					'user_id'				=> array('INDEX', 'user_id'),
					'phpbb_user_id'			=> array('INDEX', 'phpbb_user_id'),
					'author_username_clean'	=> array('INDEX', 'author_username_clean'),
					'author_rating'			=> array('INDEX', 'author_rating'),
					'author_contribs'		=> array('INDEX', 'author_contribs'),
					'author_snippets'		=> array('INDEX', 'author_snippets'),
					'author_mods'			=> array('INDEX', 'author_mods'),
					'author_styles'			=> array('INDEX', 'author_styles'),
					'author_visible'		=> array('INDEX', 'author_visible'),
				),
			)),
			array('customisation_contribs', array(
				'COLUMNS'		=> array(
					'contrib_id'					=> array('UINT', NULL, 'auto_increment'),
					'contrib_author_id'				=> array('UINT', 0), // would like to replace with user_id...
					'contrib_maintainer'			=> array('UINT', 0), // ???
					'contrib_type'					=> array('TINT:1', 0),
					'contrib_name'					=> array('STEXT_UNI', '', 'true_sort'),
					'contrib_name_clean'			=> array('VCHAR_CI', ''),
					'contrib_description'			=> array('MTEXT_UNI', ''),
					'contrib_desc_bitfield'			=> array('VCHAR:255', ''),
					'contrib_desc_uid'				=> array('VCHAR:8', ''),
					'contrib_desc_options'			=> array('UINT:11', 7),
					'contrib_status'				=> array('TINT:2', 0),
					'contrib_version'				=> array('VCHAR:15', 0), // don't think we need, we will need to get all the revisions anyways when displaying the mod
					'contrib_revision'				=> array('UINT', 0), // don't think we need...
					'contrib_validated_version'		=> array('VCHAR:15', 0), // don't think we need...
					'contrib_validated_revision'	=> array('UINT', 0), // don't think we need...
					'contrib_release_date'			=> array('INT:11', 0), // don't think we need...
					'contrib_update_date'			=> array('INT:11', 0), // don't think we need...
					'contrib_downloads'				=> array('UINT', 0),
					'contrib_views'					=> array('UINT', 0),
					'contrib_phpbb_version'			=> array('TINT:2', 0), // 3.0.0 -> 30, 3.2.0 -> 32
					'contrib_rating'				=> array('DECIMAL', 0),
					'contrib_rating_count'			=> array('UINT', 0),
					'contrib_demo'					=> array('VCHAR:255', ''),
					'contrib_visible'				=> array('BOOL', 1),
				),
				'PRIMARY_KEY'	=> 'contrib_id',
				'KEYS'			=> array(
					'contrib_author_id'		=> array('INDEX', 'contrib_author_id'),
					'contrib_type'			=> array('INDEX', 'contrib_type'),
					'contrib_name_clean'	=> array('INDEX', 'contrib_name_clean'),
					'contrib_status'		=> array('INDEX', 'contrib_status'),
					'contrib_downloads'		=> array('INDEX', 'contrib_downloads'),
					'contrib_rating'		=> array('INDEX', 'contrib_rating'),
					'contrib_visible'		=> array('INDEX', 'contrib_visible'),
				),
			)),
			array('customisation_contrib_coauthors', array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'author_id'				=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'author_id'),
			)),
			array('customisation_contrib_faq', array(
				'COLUMNS'		=> array(
					'faq_id'				=> array('UINT', NULL, 'auto_increment'),
					'contrib_id'			=> array('UINT', 0),
					'parent_id'				=> array('UINT', 0),
					'contrib_version'		=> array('VCHAR:15', 0), // Remove this later (if it applies to a specific version the Mod author should just note it in the FAQ item, this is too clunky)
					'faq_order_id'			=> array('UINT', 0),
					'faq_subject'			=> array('STEXT_UNI', '', 'true_sort'),
					'faq_text'				=> array('MTEXT_UNI', ''),
					'faq_text_bitfield'		=> array('VCHAR:255', ''),
					'faq_text_uid'			=> array('VCHAR:8', ''),
					'faq_text_options'		=> array('UINT:11', 7),
				),
				'PRIMARY_KEY'	=> 'faq_id',
				'KEYS'			=> array(
					'contrib_id'		=> array('INDEX', 'contrib_id'),
					'faq_order_id'		=> array('INDEX', 'faq_order_id'),
				),
			)),
			array('customisation_contrib_tags', array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'tag_id'				=> array('UINT', 0),
					'tag_value'				=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'tag_id'),
			)),
			array('customisation_queue', array(
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
			array('customisation_ratings', array(
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
			array('customisation_revisions', array(
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
			array('customisation_tag_fields', array(
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
			array('customisation_tag_types', array(
				'COLUMNS'		=> array(
					'tag_type_id'			=> array('UINT', NULL, 'auto_increment'),
					'tag_type_name'			=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> 'tag_type_id',
			)),
			array('customisation_watch', array(
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
			'titania_rate',
			'titania_rate_reset',
		),

		'module_add' => array(
			array('mods', 0, 'MODS_CAT_MAIN'),
			array('mods', 0, 'MODS_CAT_DETAILS'),
			array('mods', 0, 'MODS_CAT_FAQ'),
			array('mods', 0, 'MODS_CAT_REVIEWS'),
			array('mods', 0, 'MODS_CAT_SUPPORT'),
			array('mods', 'MODS_CAT_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'MODS_CATEGORIES',
				'module_mode'		=> 'categories',
			)),
			array('mods', 'MODS_CAT_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'MODS_LIST',
				'module_mode'		=> 'list',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_DETAILS',
				'module_mode'		=> 'details',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_SCREENSHOTS',
				'module_mode'		=> 'screenshots',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_PREVIEW',
				'module_mode'		=> 'preview',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_CHANGES',
				'module_mode'		=> 'changes',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_EMAIL_FRIEND',
				'module_mode'		=> 'email',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_STYLES',
				'module_mode'		=> 'styles',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'details',
				'module_langname'	=> 'MODS_TRANSLATIONS',
				'module_mode'		=> 'translations',
			)),
			array('mods', 'MODS_CAT_DETAILS', array(
				'module_basename'	=> 'faq',
				'module_langname'	=> 'MODS_VIEW_FAQ',
				'module_mode'		=> 'faq',
			)),
			array('mods', 'MODS_CAT_FAQ', array(
				'module_basename'	=> 'faq',
				'module_langname'	=> 'MODS_FAQ',
				'module_mode'		=> 'faq',
			)),
			array('mods', 'MODS_CAT_FAQ', array(
				'module_basename'	=> 'faq',
				'module_langname'	=> 'MODS_MANAGE_FAQ',
				'module_mode'		=> 'manage',
			)),
			array('mods', 'MODS_CAT_FAQ', array(
				'module_basename'	=> 'faq',
				'module_langname'	=> 'MODS_VIEW_FAQ',
				'module_mode'		=> 'view',
			)),
			array('mods', 'MODS_CAT_SUPPORT', array(
				'module_basename'	=> 'support',
				'module_langname'	=> 'MODS_SUPPORT',
				'module_mode'		=> 'support',
			)),
			array('mods', 'MODS_CAT_SUPPORT', array(
				'module_basename'	=> 'support',
				'module_langname'	=> 'MODS_VIEW_SUPPORT',
				'module_mode'		=> 'view',
			)),
			array('mods', 'MODS_CAT_SUPPORT', array(
				'module_basename'	=> 'support',
				'module_langname'	=> 'MODS_POST_SUPPORT',
				'module_mode'		=> 'post',
			)),
			array('mods', 'MODS_CAT_SUPPORT', array(
				'module_basename'	=> 'support',
				'module_langname'	=> 'MODS_EDIT_SUPPORT',
				'module_mode'		=> 'edit',
			)),

			array('titania', 0, 'TITANIA_MAIN'),
			array('titania', 'TITANIA_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'TITANIA_HOME',
				'module_mode'		=> 'home',
			)),

			array('authors', 0, 'AUTHORS_MAIN'),
			array('authors', 'AUTHORS_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'AUTHORS_LIST',
				'module_mode'		=> 'list',
			)),
			array('authors', 'AUTHORS_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'AUTHOR_PROFILE',
				'module_mode'		=> 'profile',
			)),
			array('authors', 'AUTHORS_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'AUTHOR_SEARCH',
				'module_mode'		=> 'search',
			)),
			array('authors', 'AUTHORS_MAIN', array(
				'module_basename'	=> 'main',
				'module_langname'	=> 'AUTHOR_SEARCH_RESULTS',
				'module_mode'		=> 'results',
			)),
		),

		'custom' => 'titania_data',

		'cache_purge' => '',
	),

	// IF YOU ADD A NEW VERSION DO NOT FORGET TO INCREMENT THE VERSION NUMBER IN common.php!
);

function titania_data($action, $version)
{
	global $umil;

	if ($action != 'install')
	{
		return;
	}

	$default_categories = array(
		'Add-ons',
		'Cosmetic',
		'Admin Tools',
		'Syndication',
		'BBCode',
		'Security',
		'Communication',
		'Profile/User Control Panel',
		'Tools',
		'Anti-Spam',
		'Moderator Tools',
		'Entertainment',
	);

	$sql_ary = array();
	foreach ($default_categories as $cat_name)
	{
		$sql_ary[] = array(
			'tag_type_id'		=> 1,
			'tag_field_name'	=> $cat_name,
			'tag_clean_name'	=> utf8_clean_string($cat_name),
			'tag_field_desc'	=> '',
		);
	}

	$umil->table_row_insert('customisation_tag_fields', $sql_ary);
}

include(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT);

?>