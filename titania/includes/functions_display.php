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
		if (phpbb::$config['hot_threshold'] && ($post_count + 1) >= phpbb::$config['hot_threshold'])
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
function titania_display_categories($parent_id = 0, $blockname = 'categories')
{
	$sql = 'SELECT * FROM ' . TITANIA_CATEGORIES_TABLE . '
		WHERE parent_id = ' . (int) $parent_id . '
			AND category_visible = 1
		ORDER BY left_id ASC';
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
 * Display contributions
 *
 * @param string $mode The mode (category, author)
 * @param int $id The parent id (only show contributions under this category, author, etc)
 * @param string $pagination_url The url to display for pagination.
 * @param string $blockname The name of the template block to use (contribs by default)
 */
function titania_display_contribs($mode, $id, $pagination_url, $blockname = 'contribs')
{
	// Setup sorting.
	$sort = new titania_sort();
	$sort->sort_request();

	switch ($mode)
	{
		case 'author' :
			$sort->set_sort_keys(array(
				array('SORT_CONTRIB_NAME',		'c.contrib_name', true),
			));

			$sql_ary = array(
				'SELECT'	=> '*',

				'FROM'		=> array(
					TITANIA_CONTRIBS_TABLE	=> 'c',
					USERS_TABLE				=> 'u',
				),

				'WHERE'		=> 'c.contrib_user_id = ' . (int) $id . '
					AND u.user_id = c.contrib_user_id
					AND c.contrib_visible = 1',

				'ORDER_BY'	=> $sort->get_order_by(),
			);

			titania_tracking::get_track_sql($sql_ary, TITANIA_TRACK_CONTRIB, 'c.contrib_id');
		break;

		case 'category' :
			$sort->set_sort_keys(array(
				array('SORT_CONTRIB_NAME',			'c.contrib_name', true),
			));

			$sql_ary = array(
				// DO NOT change to *, we do not need all rows from ANY table with the query!
				'SELECT'	=> 'c.contrib_name, c.contrib_name_clean, c.contrib_status, c.contrib_downloads, c.contrib_views, c.contrib_rating, c.contrib_rating_count, c.contrib_type, c.contrib_last_update,
					u.username, u.user_colour, u.username_clean',

				'FROM'		=> array(
					TITANIA_CONTRIB_IN_CATEGORIES_TABLE 	=> 'cic',
				),

				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(TITANIA_CONTRIBS_TABLE	=> 'c'),
						'ON'	=> 'cic.contrib_id = c.contrib_id',
					),
					array(
						'FROM'	=> array(USERS_TABLE	=> 'u'),
						'ON'	=> 'u.user_id = c.contrib_user_id',
					),
				),

				'WHERE'		=> 'cic.category_id = ' . (int) $id . '
					AND c.contrib_visible = 1',

				'ORDER_BY'	=> $sort->get_order_by(),
			);

			titania_tracking::get_track_sql($sql_ary, TITANIA_TRACK_CONTRIB, 'c.contrib_id');
		break;
	}

	// Setup the pagination tool
	$pagination = new titania_pagination();
	$pagination->default_limit = phpbb::$config['topics_per_page'];
	$pagination->request();

	// Main SQL Query
	$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

	// Handle pagination
	$pagination->sql_count($sql_ary, 'c.contrib_id');
	$pagination->build_pagination($pagination_url);

	// Setup some objects we'll use for temps
	$author = new titania_author();
	$contrib = new titania_contribution();

	$result = phpbb::$db->sql_query_limit($sql, $pagination->limit, $pagination->start);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$contrib->__set_array($row);

		$author->__set_array($row);

		// Store the tracking info we grabbed in the tool
		titania_tracking::store_track(TITANIA_TRACK_CONTRIB, $contrib->contrib_id, $row['track_time']);

		// Get the folder image
		$folder_img = $folder_alt = '';
		titania_topic_folder_img($folder_img, $folder_alt, 0, titania_tracking::is_unread(TITANIA_TRACK_CONTRIB, $contrib->contrib_id, $contrib->contrib_last_update));

		phpbb::$template->assign_block_vars($blockname, array(
			'CONTRIB_USERNAME'			=> $contrib->username,
			'CONTRIB_USERNAME_FULL'		=> $author->get_username_string(),
			'CONTRIB_NAME'				=> $contrib->contrib_name,
			'CONTRIB_TYPE'				=> titania::$types[$contrib->contrib_type]->lang,
			'CONTRIB_STATUS'			=> $contrib->contrib_status,
			'CONTRIB_DOWNLOADS'			=> $contrib->contrib_downloads,
			'CONTRIB_VIEWS'				=> $contrib->contrib_views,
			'CONTRIB_RATING'			=> $contrib->contrib_rating,
			'CONTRIB_RATING_COUNT'		=> $contrib->contrib_rating_count,

			'U_VIEW_CONTRIB'			=> $contrib->get_url(),

			'S_CONTRIB_TYPE'			=> $contrib->contrib_type,

			'FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
			'FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
			'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'FOLDER_IMG_WIDTH'			=> phpbb::$user->img($folder_img, '', false, '', 'width'),
			'FOLDER_IMG_HEIGHT'			=> phpbb::$user->img($folder_img, '', false, '', 'height'),
		));

		$contrib_type = $row['contrib_type'];
	}
	phpbb::$db->sql_freeresult($result);
	unset($contrib, $author);

	phpbb::$template->assign_vars(array(
		'U_ACTION'			=> titania_url::$current_page,
		'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
		'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
	));
}