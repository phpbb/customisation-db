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
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', '../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
$titania->add_lang('install');

if (!file_exists(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

$mod_name = 'CUSTOMISATION_DATABASE';
$version_config_name = 'cdb_version';


$versions = array(
	'0.1.0'	=> array(
		'table_add' => array(
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
		),

		'permission_add' => array(
			'cdb_',
			'cdb_rate',
			'cdb_rate_reset',
		),
	),

	// IF YOU ADD A NEW VERSION DO NOT FORGET TO INCREMENT THE VERSION NUMBER IN common.php!
);

// Include the UMIF Auto file and everything else will be handled automatically.
include(PHPBB_ROOT_PATH . 'umil/umil_auto.' . PHP_EXT);

?>