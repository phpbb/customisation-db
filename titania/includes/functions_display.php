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

// Order an array of phpBB versions from the database (phpbb_version_branch, phpbb_version_revision)
function order_phpbb_version_list_from_db($version_array)
{
	$versions = titania::$cache->get_phpbb_versions();

	$ordered_phpbb_versions = array();
	foreach ($version_array as $row)
	{
		$ordered_phpbb_versions[$versions[$row['phpbb_version_branch'] . $row['phpbb_version_revision']]] = true;
	}

	uksort($ordered_phpbb_versions, 'reverse_version_compare');

	return array_keys($ordered_phpbb_versions);
}

function titania_topic_folder_img(&$folder_img, &$folder_alt, $post_count = 0, $unread = false, $posted = false, $sticky = false, $locked = false)
{
	$folder = $folder_new = '';

	if ($sticky)
	{
		$folder = 'sticky_read';
		$folder_new = 'sticky_unread';
	}
	else
	{
		$folder = 'topic_read';
		$folder_new = 'topic_unread';

		// Hot topic threshold is for posts in a topic, which is replies + the first post. ;)
		if (phpbb::$config['hot_threshold'] && ($post_count + 1) >= phpbb::$config['hot_threshold'] && !$locked)
		{
			$folder .= '_hot';
			$folder_new .= '_hot';
		}
	}

	if ($locked)
	{
		$folder .= '_locked';
		$folder_new .= '_locked';
	}

	$folder_img = ($unread) ? $folder_new : $folder;
	$folder_alt = ($unread) ? 'NEW_POSTS' : 'NO_NEW_POSTS';

	// Posted image?
	if ($posted)
	{
		$folder_img .= '_mine';
	}
}

/**
* Display categories
*
* @param int $parent_id The parent id/name (only show categories under this category)
* @param string $blockname The name of the template block to use (categories by default)
*/
function titania_display_categories($parent_id = 0, $blockname = 'categories', $is_manage = false)
{
	$only_visible = (!$is_manage) ? 'AND category_visible = 1' : '';

	$sql = 'SELECT * FROM ' . TITANIA_CATEGORIES_TABLE . '
		WHERE parent_id = ' . (int) $parent_id . "
			$only_visible
		ORDER BY left_id ASC";
	$result = phpbb::$db->sql_query($sql);

	$category = new titania_category();

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$category->__set_array($row);

		phpbb::$template->assign_block_vars($blockname, $category->assign_display(true));
	}
	phpbb::$db->sql_freeresult($result);

	unset($category);
}