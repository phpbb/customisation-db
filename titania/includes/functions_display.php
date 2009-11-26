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
* @param string $blockname The name of the template block to use (contribs by default)
*/
function titania_display_contribs($mode, $id, $blockname = 'contribs')
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

			$sql = 'SELECT * FROM ' . TITANIA_CONTRIBS_TABLE . ' c, ' . USERS_TABLE . ' u
				WHERE c.contrib_user_id = ' . (int) $id . '
					AND u.user_id = c.contrib_user_id
					AND c.contrib_visible = 1
				ORDER BY ' . $sort->get_order_by();
		break;

		case 'category' :
			$sort->set_sort_keys(array(
				array('SORT_CONTRIB_NAME',			'c.contrib_name', true),
			));

			$sql = phpbb::$db->sql_build_query('SELECT', array(
				// DO NOT change to *, we do not need all rows from ANY table with the query!
				'SELECT'	=> 'c.contrib_name, c.contrib_name_clean, c.contrib_status, c.contrib_downloads, c.contrib_views, c.contrib_rating, c.contrib_rating_count, c.contrib_type, u.username, u.user_colour, u.username_clean',

				'FROM'		=> array(
					TITANIA_CONTRIB_IN_CATEGORIES_TABLE 	=> 'cic',
				),

				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(TITANIA_CONTRIBS_TABLE	=> 'c'),
						'ON'	=> 'cic.contrib_id = c.contrib_id'
					),
					array(
						'FROM'	=> array(USERS_TABLE	=> 'u'),
						'ON'	=> 'u.user_id = c.contrib_user_id'
					),
				),

				'WHERE'		=> 'cic.category_id = ' . (int) $id . '
					AND c.contrib_visible = 1',

				'ORDER_BY'	=> $sort->get_order_by(),
			));
		break;
	}

	// Setup pagination.
	$pagination = new titania_pagination();
	$start = $pagination->get_start(0);
	$limit = $pagination->get_limit();
	$contrib_type = 0;
	$author = new titania_author();
	$contrib = new titania_contribution();

	$result = phpbb::$db->sql_query_limit($sql, $limit, $start);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$contrib->__set_array($row);

		$author->__set_array($row);

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
		));

		$contrib_type = $row['contrib_type'];
	}
	phpbb::$db->sql_freeresult($result);
	unset($contrib, $author);

	$pagination->set_params(array(
		'sk'		=> $sort->sort_key,
		'sd'		=> $sort->sort_dir,
	));

	$pagination->build_pagination('');

	phpbb::$template->assign_vars(array(
		'U_ACTION'			=> titania::$url->current_page,
		'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
		'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
	));
}