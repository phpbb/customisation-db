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

/**
* Get version string from version number
*
* @param int|string $version The version number 2/20/200 for 2.0.0, 3/30/300 for 3.0.0, 32/320 for 3.2.0
*/
function get_version_string($version)
{
	$version = (string) $version;

	$major = (isset($version[0])) ? $version[0] : 0;
	$minor = (isset($version[1])) ? $version[1] : 0;
	$revision = (isset($version[2])) ? substr($version, 2) : 0;

	return sprintf('%u.%u.%u', $major, $minor, $revision);
}

/**
* Display categories
*
* @param int $parent_id The parent id (only show categories under this category)
* @param string $blockname The name of the template block to use (categories by default)
*/
function display_categories($parent_id = 0, $blockname = 'categories')
{
	$sql = 'SELECT * FROM ' . TITANIA_CATEGORIES_TABLE . '
		WHERE parent_id = ' . (int) $parent_id . '
			AND category_visible = 1
		ORDER BY left_id ASC';
	$result = phpbb::$db->sql_query($sql);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		phpbb::$template->assign_block_vars($blockname, array(
			'CATEGORY_NAME'		=> (isset(phpbb::$user->lang[$row['category_name']])) ? phpbb::$user->lang[$row['category_name']] : $row['category_name'],
			'CATEGORY_CONTRIBS'	=> $row['category_contribs'],
			'CATEGORY_TYPE'		=> $row['category_type'],

			'U_VIEW_CATEGORY'	=> titania_sid('index', 'c=' . $row['category_id']),
		));
	}
	phpbb::$db->sql_freeresult($result);
}

/**
* Display contributions
*
* @param int $parent_id The parent id (only show contributions under this category)
* @param string $blockname The name of the template block to use (contribs by default)
*/
function display_contribs($category_id, $blockname = 'contribs')
{
	$sql = 'SELECT * FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . ' cic, ' . TITANIA_CONTRIBS_TABLE . ' c, ' . USERS_TABLE . ' u
		WHERE cic.category_id = ' . (int) $category_id . '
			AND c.contrib_id = cic.contrib_id
			AND u.user_id = c.contrib_user_id
			AND c.contrib_visible = 1
		ORDER BY c.contrib_id DESC';
	$result = phpbb::$db->sql_query($sql);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		phpbb::$template->assign_block_vars($blockname, array(
			'CONTRIB_USERNAME'			=> $row['username'],
			'CONTRIB_USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			'CONTRIB_NAME'				=> $row['contrib_name'],
			'CONTRIB_TYPE'				=> $row['contrib_type'],
			'CONTRIB_STATUS'			=> $row['contrib_status'],
			'CONTRIB_DOWNLOADS'			=> $row['contrib_downloads'],
			'CONTRIB_VIEWS'				=> $row['contrib_views'],
			'CONTRIB_RATING'			=> $row['contrib_rating'],
			'CONTRIB_RATING_COUNT'		=> $row['contrib_rating_count'],

			'U_VIEW_CONTRIB'			=> titania_sid('contributions/index', 'c=' . $row['contrib_id']),
		));
	}
	phpbb::$db->sql_freeresult($result);
}
