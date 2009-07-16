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
* Get contribution type string (from the type id)
*
* @param int $type The type (check TITANIA_TYPE_ constants)
*/
function get_contrib_type_string($type, $mode = 'lang')
{
	switch($mode)
	{
		case 'url' :
			switch ($type)
			{
				case TITANIA_TYPE_MOD :
					return 'mod';
				break;

				case TITANIA_TYPE_STYLE :
					return 'style';
				break;

				case TITANIA_TYPE_SNIPPET :
					return 'snippet';
				break;

				case TITANIA_TYPE_LANG_PACK :
					return 'language_pack';
				break;

				default :
					return 'contribution';
				break;
			}
		break;

		case 'lang' :
			switch ($type)
			{
				case TITANIA_TYPE_MOD :
					return phpbb::$user->lang['MODIFICATION'];
				break;

				case TITANIA_TYPE_STYLE :
					return phpbb::$user->lang['STYLE'];
				break;

				case TITANIA_TYPE_SNIPPET :
					return phpbb::$user->lang['SNIPPET'];
				break;

				case TITANIA_TYPE_LANG_PACK :
					return phpbb::$user->lang['LANGUAGE_PACK'];
				break;

				default :
					return phpbb::$user->lang['CONTRIBUTION'];
				break;
			}
		break;
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
	titania::load_object('category');

	$sql = 'SELECT * FROM ' . TITANIA_CATEGORIES_TABLE . '
		WHERE parent_id = ' . (int) $parent_id . '
			AND category_visible = 1
		ORDER BY left_id ASC';
	$result = phpbb::$db->sql_query($sql);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$category = new titania_category();
		$category->__set_array($row);

		phpbb::$template->assign_block_vars($blockname, $category->assign_display(true));

		unset($category);
	}
	phpbb::$db->sql_freeresult($result);
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
	titania::load_object(array('contribution', 'author'));

	switch ($mode)
	{
		case 'author' :
			$sql = 'SELECT * FROM ' . TITANIA_CONTRIBS_TABLE . ' c, ' . USERS_TABLE . ' u
				WHERE c.contrib_user_id = ' . (int) $id . '
					AND u.user_id = c.contrib_user_id
					AND c.contrib_visible = 1
				ORDER BY c.contrib_id DESC';
		break;

		case 'category' :
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

				'ORDER_BY'	=> 'c.contrib_id DESC',
			));
		break;
	}

	// @todo Build sorting and limits
	$result = phpbb::$db->sql_query_limit($sql, 25);

	$contrib_type = 0;
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$contrib = new titania_contribution();
		$contrib->__set_array($row);

		$author = new titania_author();
		$author->__set_array($row);

		phpbb::$template->assign_block_vars($blockname, array(
			'CONTRIB_USERNAME'			=> $contrib->username,
			'CONTRIB_USERNAME_FULL'		=> $author->get_username_string(),
			'CONTRIB_NAME'				=> $contrib->contrib_name,
			'CONTRIB_TYPE'				=> get_contrib_type_string($contrib->contrib_type),
			'CONTRIB_STATUS'			=> $contrib->contrib_status,
			'CONTRIB_DOWNLOADS'			=> $contrib->contrib_downloads,
			'CONTRIB_VIEWS'				=> $contrib->contrib_views,
			'CONTRIB_RATING'			=> $contrib->contrib_rating,
			'CONTRIB_RATING_COUNT'		=> $contrib->contrib_rating_count,

			'U_VIEW_CONTRIB'			=> $contrib->get_url(),

			'S_CONTRIB_TYPE'			=> $contrib->contrib_type,
		));

		$contrib_type = $row['contrib_type'];

		unset($contrib, $author);
	}
	phpbb::$db->sql_freeresult($result);
}

/**
* Display "forum" like section for support/tracker/etc
*
* @param string $type The type (support, review, queue, tracker, author_support, author_tracker) author_ for displaying posts from the areas the given author is involved in (either an author/co-author)
* @param object|boolean $object The object (for contrib related (support, review, queue, tracker) and author_ modes)
* @param object|boolean $sort The sort object (includes/tools/sort.php)
* @param array $options Extra options (limit, category (for tracker))
*/
function titania_display_forums($type, $object = false, $sort = false, $options = array('limit' => 10))
{
	titania::load_object('topic');

	$start = request_var('start', 0);
	$limit = request_var('limit', ((isset($options['limit'])) ? (int) $options['limit'] : 10));

	$sql_ary = array(
		'SELECT' => '*',
		'FROM'		=> array(
			TITANIA_TOPICS_TABLE => 't',
		),
		'WHERE' => 'topic_access >= ' . titania::$access_level,
		'ORDER_BY'	=> 'topic_sticky DESC',
	);

	// Sort options
	if ($sort !== false)
	{
		$sql_ary['ORDER_BY'] .= ', ' . $sort->get_order_by();
	}
	else
	{
		$sql_ary['ORDER_BY'] .= ', topic_last_post_time DESC';
	}

	// If they are not moderators we need to add some more checks
	if (!phpbb::$auth->acl_get('titania_post_mod'))
	{
		$sql_ary['WHERE'] .= ' AND topic_deleted = 0';
		$sql_ary['WHERE'] .= ' AND topic_approved = 1';
	}

	// type specific things
	switch ($type)
	{
		case 'tracker' :
			$sql_ary['WHERE'] .= ' AND topic_type = ' . TITANIA_POST_TRACKER;

			if (isset($options['category']))
			{
				$sql_ary['WHERE'] .= ' AND topic_category = ' . (int) $options['category'];
			}
		break;

		case 'queue' :
			$sql_ary['WHERE'] .= ' AND topic_type = ' . TITANIA_POST_QUEUE;
		break;

		case 'review' :
			$sql_ary['WHERE'] .= ' AND topic_type = ' . TITANIA_POST_REVIEW;
		break;

		case 'author_support' :
			$sql_ary['WHERE'] .= ' AND topic_type = ' . TITANIA_POST_DEFAULT;
			$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('contrib_id', titania::$cache->get_author_contribs($object->user_id));
		break;

		case 'author_tracker' :
			$sql_ary['WHERE'] .= ' AND topic_type = ' . TITANIA_POST_TRACKER;
			$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('contrib_id', titania::$cache->get_author_contribs($object->user_id));
		break;

		case 'support' :
		default :
			$sql_ary['WHERE'] .= ' AND topic_type = ' . TITANIA_POST_DEFAULT;
		break;
	}

	// Main SQL Query
	$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

	// Count SQL Query
	$sql_ary['SELECT'] = 'COUNT(topic_id) AS cnt';
	$count_sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
	phpbb::$db->sql_query($count_sql);
	$count = phpbb::$db->sql_fetchfield('cnt');
	phpbb::$db->sql_freeresult();

	// Get the data
	$topics = $topic_ids = array();
	$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$topics[$row['topic_id']] = $row;
		$topic_ids[] = $row['topic_id'];
	}
	phpbb::$db->sql_freeresult($result);

	// Get the read info

	// Loop de loop
	$last_was_sticky = false;
	foreach ($topics as $row)
	{
		$topic = new titania_topic($row['topic_type']);
		$topic->__set_array($row);

		phpbb::$template->assign_block_vars('topics', array_merge($topic->assign_details(), array(
			'S_TOPIC_TYPE_SWITCH'		=> ($last_was_sticky && !$topic->topic_sticky) ? true : false,
		)));

		$last_was_sticky = $topic->topic_sticky;

		unset($topic);
	}
	phpbb::$db->sql_freeresult($result);

	phpbb::$template->assign_vars(array(
		'REPORTED_IMG'		=> phpbb::$user->img('icon_topic_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'	=> phpbb::$user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
		'NEWEST_POST_IMG'	=> phpbb::$user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
	));
}