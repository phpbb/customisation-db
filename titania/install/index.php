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
			array('customisation_categories', array(
				'COLUMNS'		=> array(
					'category_id'			=> array('UINT', NULL, 'auto_increment'),
					'parent_id'				=> array('UINT', 0),
					'left_id'				=> array('UINT', 0),
					'right_id'				=> array('UINT', 0),
					'category_type'			=> array('TINT:1', 0), // Check TITANIA_TYPE_ constants
					'category_contribs'		=> array('UINT', 0), // Number of items
					'category_visible'		=> array('BOOL', 1),
					'category_name'			=> array('STEXT_UNI', '', 'true_sort'),
				),
				'PRIMARY_KEY'	=> 'category_id',
				'KEYS'			=> array(
					'parent_id'			=> array('INDEX', 'parent_id'),
					'left_right_id'		=> array('INDEX', array('left_id', 'right_id')),
					'category_type'		=> array('INDEX', 'category_type'),
					'category_visible'	=> array('INDEX', 'category_visible'),
				),
			)),
			array('customisation_contribs', array(
				'COLUMNS'		=> array(
					'contrib_id'					=> array('UINT', NULL, 'auto_increment'),
					'contrib_user_id'				=> array('UINT', 0),
					'contrib_type'					=> array('TINT:1', 0),
					'contrib_name'					=> array('STEXT_UNI', '', 'true_sort'),
					'contrib_name_clean'			=> array('VCHAR_CI', ''),
					'contrib_description'			=> array('MTEXT_UNI', ''),
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
			array('customisation_contrib_in_categories', array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'category_id'			=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'category_id'),
			)),
			array('customisation_contrib_coauthors', array(
				'COLUMNS'		=> array(
					'contrib_id'			=> array('UINT', 0),
					'user_id'				=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('contrib_id', 'user_id'),
			)),
			array('customisation_contrib_faq', array(
				'COLUMNS'		=> array(
					'faq_id'				=> array('UINT', NULL, 'auto_increment'),
					'contrib_id'			=> array('UINT', 0),
					'parent_id'				=> array('UINT', 0), // Removed in 0.1.1
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

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array('titania_rate_reset')),
			array('ROLE_MOD_FULL', array('titania_rate_reset')),
			array('ROLE_USER_FULL', array('titania_rate')),
			array('ROLE_USER_STANDARD', array('titania_rate')),
		),

		'module_add' => array(
			//array('titania', 0, 'TITANIA_CAT_MAIN'),
			//array('titania', 'TITANIA_MAIN',	array('module_basename' => 'main'),		TITANIA_ROOT . 'modules/'),

			array('contribs', 0, 'CONTRIB_CAT_DETAILS'),
			array('contribs', 'CONTRIB_CAT_DETAILS',	array('module_basename' => 'details'),	TITANIA_ROOT . 'modules/'),
			array('contribs', 0, 'CONTRIB_CAT_FAQ'),
			array('contribs', 'CONTRIB_CAT_FAQ',		array('module_basename' => 'faq'),		TITANIA_ROOT . 'modules/'),
			array('contribs', 0, 'CONTRIB_CAT_SUPPORT'),
			array('contribs', 'CONTRIB_CAT_SUPPORT',	array('module_basename' => 'support'),	TITANIA_ROOT . 'modules/'),

			array('authors', 0, 'AUTHORS_CAT_DETAILS'),
			array('authors', 'AUTHORS_CAT_DETAILS',	array('module_basename' => 'details'),					TITANIA_ROOT . 'modules/'),
			array('authors', 0, 'AUTHORS_CAT_CONTRIBUTIONS'),
			array('authors', 'AUTHORS_CAT_CONTRIBUTIONS',	array('module_basename' => 'contributions'),	TITANIA_ROOT . 'modules/'),
			array('authors', 0, 'AUTHORS_CAT_SUPPORT'),
			array('authors', 'AUTHORS_CAT_SUPPORT',	array('module_basename' => 'support'),					TITANIA_ROOT . 'modules/'),
		),

		'custom' => 'titania_data',

		'cache_purge' => '',
	),

	'0.1.1' => array(
		'table_column_remove' => array(
			array('customisation_contrib_faq', 'parent_id'),
		),

		'permission_add' => array(
			'titania_faq_create',
			'titania_faq_edit',
			'titania_faq_delete',
			'titania_faq_mod',
		),

		'permission_set' => array(
			array('ROLE_ADMIN_FULL', array('titania_faq_mod')),
			array('ROLE_MOD_FULL', array('titania_faq_mod')),
		),
	),

	'0.1.2' => array(
		'table_column_add' => array(
			array('customisation_authors', 'author_desc', array('MTEXT_UNI', '')),
			array('customisation_authors', 'author_desc_bitfield', array('VCHAR:255', '')),
			array('customisation_authors', 'author_desc_uid', array('VCHAR:8', '')),
			array('customisation_authors', 'author_desc_options', array('UINT:11', 7)),
		),
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

	$categories = array(
		array(
			'category_id'	=> 1,
			'parent_id'		=> 0,
			'left_id'		=> 1,
			'right_id'		=> 22,
			'category_type'	=> TITANIA_TYPE_CATEGORY,
			'category_name'	=> 'phpBB3',
		),
		array(
			'category_id'	=> 2,
			'parent_id'		=> 1,
			'left_id'		=> 2,
			'right_id'		=> 19,
			'category_type'	=> TITANIA_TYPE_CATEGORY,
			'category_name'	=> 'CAT_MODIFICATIONS',
		),
		array(
			'category_id'	=> 3,
			'parent_id'		=> 1,
			'left_id'		=> 20,
			'right_id'		=> 21,
			'category_type'	=> TITANIA_TYPE_STYLE,
			'category_name'	=> 'CAT_STYLES',
		),
		array(
			'category_id'	=> 4,
			'parent_id'		=> 2,
			'left_id'		=> 3,
			'right_id'		=> 4,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_COSMETIC',
		),
		array(
			'category_id'	=> 5,
			'parent_id'		=> 2,
			'left_id'		=> 5,
			'right_id'		=> 6,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_ADMIN_TOOLS',
		),
		array(
			'category_id'	=> 6,
			'parent_id'		=> 2,
			'left_id'		=> 7,
			'right_id'		=> 8,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_SECURITY',
		),
		array(
			'category_id'	=> 7,
			'parent_id'		=> 2,
			'left_id'		=> 9,
			'right_id'		=> 10,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_COMMUNICATION',
		),
		array(
			'category_id'	=> 8,
			'parent_id'		=> 2,
			'left_id'		=> 11,
			'right_id'		=> 12,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_PROFILE_UCP',
		),
		array(
			'category_id'	=> 9,
			'parent_id'		=> 2,
			'left_id'		=> 13,
			'right_id'		=> 14,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_ADDONS',
		),
		array(
			'category_id'	=> 10,
			'parent_id'		=> 2,
			'left_id'		=> 15,
			'right_id'		=> 16,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_ANTI_SPAM',
		),
		array(
			'category_id'	=> 11,
			'parent_id'		=> 2,
			'left_id'		=> 17,
			'right_id'		=> 18,
			'category_type'	=> TITANIA_TYPE_MOD,
			'category_name'	=> 'CAT_ENTERTAINMENT',
		),
	);

	$umil->table_row_insert('customisation_categories', $categories);

	$author = array(array(
		'user_id'			=> phpbb::$user->data['user_id'],
		'author_realname'	=> '|nub',
		'author_website'	=> 'http://teh.nub.com/',
		'author_contribs'	=> 1,
		'author_mods'		=> 1,
	));
	$umil->table_row_insert('customisation_authors', $author);

	$mod = array(array(
		'contrib_id'			=> 1,
		'contrib_user_id'		=> phpbb::$user->data['user_id'],
		'contrib_type'			=> TITANIA_TYPE_MOD,
		'contrib_name'			=> 'Nub Mod',
		'contrib_name_clean'	=> 'nub mod',
		'contrib_description'	=> 'This mod will turn all users into nubs.',
		'contrib_desc_bitfield'	=> '',
		'contrib_desc_uid'		=> '',
		'contrib_desc_options'	=> 0,
		'contrib_status'		=> TITANIA_STATUS_NEW,
	));
	$umil->table_row_insert('customisation_contribs', $mod);

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
	$umil->table_row_insert('customisation_contrib_in_categories', $in_categories);
}

include(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT);
