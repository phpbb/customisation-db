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
		't' => array('POST_TIME', 't.topic_last_post_time', true),
		's' => array('SUBJECT', 't.topic_subject'),
		//'r' => array('REPLIES', ),
		'v' => array('VIEWS', 't.topic_views'),
	);

	/**
	 * Generate the permissions stuff for sql queries to the topics table (handles topic_access, topict_deleted, topic_approved)
	 *
	 * @param <string> $prefix prefix for the query
	 * @param <bool> $where true to use WHERE, false if you already did use WHERE
	 * @return <string>
	 */
	public static function sql_permissions($prefix = 't.', $where = false)
	{
		$sql = ($where) ? ' WHERE' : ' AND';
		$sql .= " ({$prefix}topic_access >= " . titania::$access_level . " OR {$prefix}topic_first_post_user_id = " . phpbb::$user->data['user_id'] . ")
			AND ({$prefix}topic_deleted = 0 OR {$prefix}topic_deleted = " . phpbb::$user->data['user_id'] . ')';

		if (phpbb::$auth->acl_get('m_titania_post_mod'))
		{
			$sql .= " AND {$prefix}topic_approved = 1";
		}

		return $sql;
	}

	/**
	* Load a topic from a post
	*
	* @param int $post_id
	* @return mixed false if post does not exist, topic_id if it does
	*/
	public static function load_topic_from_post($post_id)
	{
		$sql_ary = array(
			'SELECT' => 't.*, c.contrib_type, c.contrib_name_clean',

			'FROM'		=> array(
				TITANIA_POSTS_TABLE		=> 'p',
				TITANIA_TOPICS_TABLE	=> 't',
				TITANIA_CONTRIBS_TABLE	=> 'c',
			),

			'WHERE' => 'p.post_id = ' . (int) $post_id . '
				AND t.topic_id = p.topic_id
				AND c.contrib_id = t.contrib_id' .
				self::sql_permissions('t.'),
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
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

		$sql_ary = array(
			'SELECT' => 't.*, c.contrib_type, c.contrib_name_clean',

			'FROM'		=> array(
				TITANIA_TOPICS_TABLE	=> 't',
				TITANIA_CONTRIBS_TABLE	=> 'c',
			),

			'WHERE' => phpbb::$db->sql_in_set('t.topic_id', array_map('intval', $topic_id)) . '
				AND c.contrib_id = t.contrib_id' .
				self::sql_permissions('t.'),
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$topics[$row['topic_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	/**
	 * Get the topic object
	 *
	 * @param <int> $topic_id
	 * @return <object|bool> False if the topic does not exist in the self::$topics array (load it first!) topic object if it exists
	 */
	public static function get_topic_object($topic_id)
	{
		if (!isset(self::$topics[$topic_id]))
		{
			return false;
		}

		$topic = new titania_topic();
		$topic->__set_array(self::$topics[$topic_id]);
		if (is_object(titania::$contrib) && titania::$contrib->contrib_id == self::$topics[$topic_id]['contrib_id'])
		{
			$topic->contrib = titania::$contrib;
		}
		else if (isset(self::$topics[$topic_id]['contrib_type']))
		{
			$topic->contrib = array(
				'contrib_type'			=> self::$topics[$topic_id]['contrib_type'],
				'contrib_name_clean'	=> self::$topics[$topic_id]['contrib_name_clean'],
			);
		}

		return $topic;
	}

	/**
	* Do everything we need to display the forum like page
	*
	* @param string $type
	* @param mixed $object
	*/
	public static function display_forums_complete($type, $object = false)
	{
/*
user_topic_show_days

$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
*/

		self::display_forums($type, $object);
		self::assign_common();

		phpbb::$template->assign_vars(array(
			'S_TOPIC_LIST'			=> true,
		));
	}

	/**
	* Display "forum" like section for support/tracker/etc
	*
	* @param string $type The type (support, review, queue, tracker, author_support, author_tracker) author_ for displaying posts from the areas the given author is involved in (either an author/co-author)
	* @param object|boolean $object The object (for contrib related (support, review, queue, tracker) and author_ modes)
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	* @param object|boolean $pagination The pagination object (includes/tools/pagination.php)
	*/
	public static function display_forums($type, $object = false, $sort = false, $pagination = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_sort_keys(self::$sort_by);
			if (isset(self::$sort_by[phpbb::$user->data['user_topic_sortby_type']]))
			{
				$sort->default_key = phpbb::$user->data['user_topic_sortby_type'];
			}
			$sort->default_dir = phpbb::$user->data['user_topic_sortby_dir'];
		}

		if ($pagination === false)
		{
			// Setup the pagination tool
			$pagination = new titania_pagination();
			$pagination->default_limit = phpbb::$config['topics_per_page'];
			$pagination->request();
		}

		$topic_ids = array();

		$sql_ary = array(
			'SELECT' => 't.*, c.contrib_type, c.contrib_name_clean',

			'FROM'		=> array(
				TITANIA_TOPICS_TABLE	=> 't',
				TITANIA_CONTRIBS_TABLE	=> 'c',
			),

			'WHERE' => 'c.contrib_id = t.contrib_id' .
				self::sql_permissions('t.'),

			'ORDER_BY'	=> 't.topic_sticky DESC, ' . $sort->get_order_by(),
		);

		titania_tracking::get_track_sql($sql_ary, TITANIA_TOPIC, 't.topic_id');

		// type specific things
		switch ($type)
		{
			case 'tracker' :
				$page_url = $object->get_url('tracker');
				$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_TRACKER;

				if (isset($options['category']))
				{
					$sql_ary['WHERE'] .= ' AND t.topic_category = ' . (int) $options['category'];
				}
			break;

			case 'queue' :
				$page_url = titania_url::build_url('manage/queue');
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_QUEUE . '
					AND t.topic_status <> ' . TITANIA_QUEUE_HIDE;
			break;

			case 'author_support' :
				$page_url = $object->get_url('support');
				$contrib_ids = titania::$cache->get_author_contribs($object->user_id);
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', array_map('intval', $contrib_ids));

				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_SUPPORT;
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', array_map('intval', titania::$cache->get_author_contribs($object->user_id)));
			break;

			case 'author_tracker' :
				$page_url = $object->get_url('tracker');
				$contrib_ids = titania::$cache->get_author_contribs($object->user_id);
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', array_map('intval', $contrib_ids));

				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_TRACKER;
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.contrib_id', array_map('intval', titania::$cache->get_author_contribs($object->user_id)));
			break;

			case 'support' :
			default :
				$page_url = $object->get_url('support');
				$sql_ary['WHERE'] .= ' AND t.contrib_id = ' . (int) $object->contrib_id;
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . TITANIA_SUPPORT;
			break;
		}

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		$pagination->sql_count($sql_ary, 't.topic_id');
		$pagination->build_pagination($page_url);

		$topic = new titania_topic();
		$last_was_sticky = false;

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $pagination->limit, $pagination->start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Store the tracking info we grabbed in the tool
			if (isset($row['track_time']))
			{
				titania_tracking::store_track(TITANIA_TOPIC, $row['topic_id'], $row['track_time']);
			}

			self::$topics[$row['topic_id']] = $row;

			$topic->__set_array($row);

			$topic->contrib = array(
				'contrib_type'			=> $row['contrib_type'],
				'contrib_name_clean'	=> $row['contrib_name_clean'],
			);

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
