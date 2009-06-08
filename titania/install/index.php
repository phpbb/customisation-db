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
					'author_rating'			=> array('DECIMAL:6', 0),
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
					'author_rating'			=> array('INDEX', 'author_uauthor_ratingsername_clean'),
					'author_contribs'		=> array('INDEX', 'author_contribs'),
					'author_snippets'		=> array('INDEX', 'author_snippets'),
					'author_mods'			=> array('INDEX', 'author_mods'),
					'author_styles'			=> array('INDEX', 'author_styles'),
				),
			)),
			array('customisation_ratings', array(
				'COLUMNS'		=> array(
					'rating_id'				=> array('UINT', NULL, 'auto_increment'),
					'rating_type_id'		=> array('UINT', 0),
					'rating_user_id'		=> array('UINT', 0),
					'rating_object_id'		=> array('UINT', 0),
					'rating_value'			=> array('DECIMAL:6', 0), // Not sure if we should allow partial ratings (like 4.5/5) or just integer ratings...
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