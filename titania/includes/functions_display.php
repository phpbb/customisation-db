<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
function order_phpbb_version_list_from_db($version_array, $all_versions = false)
{
	if ($all_versions)
	{
		$all_versions = $version_array[0]['phpbb_version_branch'][0] . '.' . $version_array[0]['phpbb_version_branch'][1] . '.x';
		return array($all_versions);
	}

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

/**
* Creates a simple calendar array for each day in a time interval
*
* @param int $start_time Time to start the calendar
* @param int $end_time Time to end the calendar
* @param mixed $day_tpl Value to fill each day with
*
* @return array Retuns an array in the form of Array([year] => Array([month] => Array([day] => $day_tpl)))
*/
function create_calendar_ary($start_time, $end_time, $day_tpl)
{
	$start = gmgetdate(offset_user_time($start_time));
	$end = gmgetdate(offset_user_time($end_time));
	$calendar = array();

	$num_months = (($start['year'] == $end['year']) ? $end['mon'] : 12) - $start['mon'] + 1;

	// Set up the month array for the first year
	$calendar[$start['year']] = array_fill($start['mon'], $num_months, array());

	$first_day = $start['mday'];
	$month = $start['mon'];
	$year = $start['year'];
	$create_month = true;

	while ($create_month)
	{
		$num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		// Have we reached the final month?
		if ($end['mon'] . $end['year'] == $month . $year)
		{
			$num_days = $end['mday'];
			// Stop the loop
			$create_month = false;
		}

		if ($first_day != 1)
		{
			$num_days = $num_days - $first_day + 1;
		}

		// Fill the array for each day of the month
		$calendar[$year][$month] = array_fill($first_day, $num_days, $day_tpl);
		$first_day = 1;

		// Have we reached the end of the year?
		if ($create_month && $month == 12)
		{
			$month = 1;
			$year++;
			$num_months = 12;

			// Is this the final year?
			if ($end['year'] == $year)
			{
				$num_months = $end['mon'];
			}
			// Set up the month array for the following year
			$calendar[$year] = array_fill(1, $num_months, $day_tpl);
		}
		else
		{
			$month++;
		}
	}

	return $calendar;
}
