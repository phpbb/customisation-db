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

class topics_overlord
{
	/**
	* Topics array
	* Stores [id] => topic object
	*
	* @var array
	*/
	public static $topics = array();

	public static $sort_by = array(
		'a' => array('AUTHOR', 't.topic_first_post_username'),
		't' => array('POST_TIME', 't.topic_time', true),
		's' => array('SUBJECT', 't.topic_subject'),
		//'r' => array('REPLIES', ),
		'v' => array('VIEWS', 't.topic_views'),
	);

	/**
	* Load a topic from a post
	*
	* @param int $post_id
	* @return mixed false if post does not exist, topic_id if it does
	*/
	public static function load_topic_from_post($post_id)
	{
		$sql = 'SELECT t.* FROM ' . TITANIA_POSTS_TABLE . ' p, ' . TITANIA_TOPICS_TABLE . ' t
			WHERE p.post_id = ' . (int) $post_id . '
				AND t.topic_id = p.topic_id';
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($row)
		{
			self::$topics[$row['topic_id']] = $row;
			return $row['topic_id'];
		}

		return false;
	}

	/**
	* Load topic(s) from topic id(s)
	*
	* @param int|array $topic_id topic_id or an array of topic_ids
	*/
	public static function load_topic($topic_id)
	{
		titania::load_object('topic');

		if (!is_array($topic_id))
		{
			$topic_id = array($topic_id);
		}

		// Only get the rows for those we have not gotten already
		$topic_id = array_diff($topic_id, array_keys(self::$topics));

		if (!sizeof($topic_id))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('topic_id', $topic_id) . '
			AND topic_access >= ' . titania::$access_level;
		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$topics[$row['topic_id']] = new titania_topic();
			self::$topics[$row['topic_id']]->__set_array($row);
		}

		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Do everything we need to display the forum like page
	*
	* @param string $type
	* @param mixed $object
	*/
	public static function display_forums_complete($type, $object = false)
	{
		$start = request_var('start', 0);
		$limit = request_var('limit', (int) phpbb::$config['topics_per_page']);

		// Setup the sort tool
		titania::load_tool('sort');
		$sort = new titania_sort();
		$sort->set_sort_keys(self::$sort_by);
		if (isset(self::$sort_by[phpbb::$user->data['user_topic_sortby_type']]))
		{
			$sort->default_key = phpbb::$user->data['user_topic_sortby_type'];
		}
		$sort->default_dir = phpbb::$user->data['user_topic_sortby_dir'];

		// if a post_id was given we must start from the appropriate page
		$post_id = request_var('p', 0);
		if ($post_id)
		{
			$sql = 'SELECT COUNT(post_id) as start FROM ' . TITANIA_POSTS_TABLE . '
				WHERE post_id < ' . $post_id . '
					AND topic_id = ' . $topic_id . '
				ORDER BY ' . $sort->get_order_by();
			phpbb::$db->sql_query($sql);
			$start = phpbb::$db->sql_fetchfield('start');
			phpbb::$db->sql_freeresult();

			$start = ($start > 0) ? (floor($start / $limit) * $limit) : 0;
		}

/*
user_topic_show_days

$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
*/

		self::display_forums($type, $object, $sort, array('start' => $start, 'limit' => $limit));
		self::assign_common();

		phpbb::$template->assign_vars(array(
			'U_CREATE_TOPIC'		=> (phpbb::$auth->acl_get('titania_topic')) ? titania::$url->append_url($object->get_url('support'), array('action' => 'post')) : '',

			'S_TOPIC_LIST'			=> true,
		));
	}

	/**
	* Display "forum" like section for support/tracker/etc
	*
	* @param string $type The type (support, review, queue, tracker, author_support, author_tracker) author_ for displaying posts from the areas the given author is involved in (either an author/co-author)
	* @param object|boolean $object The object (for contrib related (support, review, queue, tracker) and author_ modes)
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	* @param array $options Extra options (limit, category (for tracker))
	*/
	public static function display_forums($type, $object = false, $sort = false, $options = array('start' => 0, 'limit' => 10))
	{
		titania::load_object('topic');

		$topic_ids = array();

		$sql_ary = array(
			'SELECT' => 't.*',
			'FROM'		=> array(
				TITANIA_TOPICS_TABLE => 't',
			),
			'WHERE' => 't.topic_access >= ' . titania::$access_level,
			'ORDER_BY'	=> 't.topic_sticky DESC',
		);

		// Sort options
		if ($sort !== false)
		{
			$sql_ary['ORDER_BY'] .= ', ' . $sort->get_order_by();
		}
		else
		{
			$sql_ary['ORDER_BY'] .= ', t.topic_last_post_time DESC';
		}

		// If they are not moderators we need to add some more checks
		if (!phpbb::$auth->acl_get('titania_post_mod'))
		{
			$sql_ary['WHERE'] .= ' AND t.topic_deleted = 0';
			$sql_ary['WHERE'] .= ' AND t.topic_approved = 1';
		}

		// type specific things
		switch ($type)
		{
			case 'tracker' :
				$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_TRACKER;

				if (isset($options['category']))
				{
					$sql_ary['WHERE'] .= ' AND t.topic_category = ' . (int) $options['category'];
				}
			break;

			case 'queue' :
				$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_QUEUE;
			break;

			case 'author_support' :
				$contrib_ids = titania::$cache->get_author_contribs($object->user_id);
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', $contrib_ids);

				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_DEFAULT;
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', titania::$cache->get_author_contribs($object->user_id));
			break;

			case 'author_tracker' :
				$contrib_ids = titania::$cache->get_author_contribs($object->user_id);
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', $contrib_ids);

				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_TRACKER;
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', titania::$cache->get_author_contribs($object->user_id));
			break;

			case 'support' :
			default :
				$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_POST_DEFAULT;
			break;
		}

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Count SQL Query
		// @todo This is done by pagination class...
		$sql_ary['SELECT'] = 'COUNT(topic_id) AS cnt';
		$count_sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		phpbb::$db->sql_query($count_sql);
		$count = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $options['limit'], $options['start']);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// DO NOT create a new object for every row. Adds way to much memory when you deal with large topics!!!!
			self::$topics[$row['topic_id']] = $row;

			$topic_ids[] = $row['topic_id'];
		}
		phpbb::$db->sql_freeresult($result);

		// @todo Get the read info

		$topic = new titania_topic();

		// Loop de loop
		$last_was_sticky = false;
		foreach ($topic_ids as $topic_id)
		{
			$topic->__set_array(self::$topics[$topic_id]);

			phpbb::$template->assign_block_vars('topics', array_merge($topic->assign_details(), array(
				'S_TOPIC_TYPE_SWITCH'		=> ($last_was_sticky && !$topic->topic_sticky) ? true : false,
			)));

			$last_was_sticky = $topic->topic_sticky;
		}
		phpbb::$db->sql_freeresult($result);

		unset($topic);
	}

	public static function assign_common()
	{
		phpbb::$template->assign_vars(array(
			'REPORTED_IMG'		=> phpbb::$user->img('icon_topic_reported', 'TOPIC_REPORTED'),
			'UNAPPROVED_IMG'	=> phpbb::$user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
			'NEWEST_POST_IMG'	=> phpbb::$user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
		));
	}
}