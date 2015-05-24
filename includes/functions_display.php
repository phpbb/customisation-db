<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
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
