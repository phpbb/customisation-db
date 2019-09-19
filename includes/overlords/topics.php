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

use phpbb\titania\ext;

class topics_overlord
{
	/**
	* Topics array
	* Stores [id] => topic row
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
	public static function sql_permissions($prefix = 't.', $where = false, $no_where = false)
	{
		$access = phpbb::$container->get('phpbb.titania.access');
		$sql = ($no_where) ? '' : (($where) ? ' WHERE' : ' AND');
		$sql .= " {$prefix}topic_access >= " . $access->get_level();// . " OR {$prefix}topic_first_post_user_id = " . phpbb::$user->data['user_id'] . ')';

		if (!phpbb::$auth->acl_get('u_titania_mod_post_mod'))
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
	* @param bool $no_auth_check True to not check for authorization, false to check
	*/
	public static function load_topic_from_post($post_id, $no_auth_check = false)
	{
		$sql_ary = array(
			'SELECT' => 't.*',

			'FROM'		=> array(
				TITANIA_POSTS_TABLE		=> 'p',
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => 'p.post_id = ' . (int) $post_id . '
				AND t.topic_id = p.topic_id',
		);

		// Sometimes we must check the auth later on
		if (!$no_auth_check)
		{
			$sql_ary['WHERE'] .= self::sql_permissions('t.');
		}

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
	* @param bool $no_auth_check True to not check for authorization, false to check
	*/
	public static function load_topic($topic_id, $no_auth_check = false)
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
			'SELECT' => '*',

			'FROM'		=> array(
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => phpbb::$db->sql_in_set('t.topic_id', array_map('intval', $topic_id)),
		);

		// Sometimes we must check the auth later on
		if (!$no_auth_check)
		{
			$sql_ary['WHERE'] .= self::sql_permissions('t.');
		}

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

		return $topic;
	}

	/**
	* Do everything we need to display the forum like page
	*/
	public static function display_forums_complete($type, $object = false, $options = array())
	{
/*
user_topic_show_days

$limit_topic_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
*/
		phpbb::$user->add_lang('viewforum');

		$return = self::display_forums($type, $object, false, $options);
		self::assign_common();

		phpbb::$template->assign_vars(array(
			'S_TOPIC_LIST'			=> true,
		));

		return $return;
	}

	/**
	* Display "forum" like section for support/tracker/etc
	*
	* @param string $type The type (support, review, queue, tracker, author_support, author_tracker) author_ for displaying posts from the areas the given author is involved in (either an author/co-author)
	* @param object|boolean $object The object (for contrib related (support, review, queue, tracker) and author_ modes)
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	* @param array $options Some special options
	* @param string $contrib_type The type of the support topic list
	*/
	public static function display_forums($type, $object = false, $sort = false, $options = array())
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = self::build_sort();
		}
		$sort->request();
		$controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$tracking = phpbb::$container->get('phpbb.titania.tracking');
		$types = phpbb::$container->get('phpbb.titania.contribution.type.collection');

		$topic_ids = array();
		$switch_on_sticky = true; // Display the extra block after stickies end?  Not used when not sorting with stickies first

		$sql_ary = array(
			'SELECT' => 't.*, u.username as topic_first_post_username, u.user_colour as topic_first_post_user_colour, ul.username as topic_last_post_username, ul.user_colour as topic_last_post_user_colour, tp.topic_posted',

			'FROM'		=> array(
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_TOPICS_POSTED_TABLE => 'tp'),
					'ON'	=> 'tp.topic_id = t.topic_id AND tp.user_id = ' . (int) phpbb::$user->data['user_id'],
				),
			),

			'WHERE' => self::sql_permissions('t.', false, true),

			'ORDER_BY'	=> 't.topic_sticky DESC, ' . $sort->get_order_by(),
		);

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'u'),
			'ON'	=> 't.topic_first_post_user_id = u.user_id',
		);

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'ul'),
			'ON'	=> 't.topic_last_post_user_id = ul.user_id',
		);

		$tracking->get_track_sql($sql_ary, ext::TITANIA_TOPIC, 't.topic_id');

		// Setup the contribution/topic we will use for parsing the output (before the switch so we are able to do type specific things for it)
		$topic = new titania_topic();
		$contrib = new titania_contribution();

		// type specific things
		switch ($type)
		{
			case 'tracker' :
				$page_url = $object->get_url('tracker');
				$sql_ary['WHERE'] .= ' AND t.parent_id = ' . (int) $object->contrib_id;
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . ext::TITANIA_TRACKER;

				if (isset($options['category']))
				{
					$sql_ary['WHERE'] .= ' AND t.topic_category = ' . (int) $options['category'];
				}
			break;

			case 'queue' :
				$page_url = $controller_helper->route('phpbb.titania.queue');
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . ext::TITANIA_QUEUE;
			break;

			case 'queue_discussion' :
				$page_url = $controller_helper->route('phpbb.titania.queue_discussion.type', array(
					'queue_type' => $types->get($options['topic_category'])->url,
				));
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . ext::TITANIA_QUEUE_DISCUSSION;

				// Only display those in which the users are authed
				$authed = $types->find_authed('queue_discussion');
				if (!sizeof($authed))
				{
					return compact('sort');
				}
				if (isset($options['topic_category']))
				{
					if (!in_array((int) $options['topic_category'], $authed))
					{
						return compact('sort');
					}

					$sql_ary['WHERE'] .= ' AND t.topic_category = ' . (int) $options['topic_category'];
				}
				else
				{
					$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.topic_category', $authed);
				}

				// Additional tracking for all queue discussion topics
				$tracking->get_track_sql($sql_ary, ext::TITANIA_QUEUE_DISCUSSION, 0, 'tqt');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_QUEUE_DISCUSSION, 'id' => 0, 'type_match' => true);

				// Additional tracking for marking items as read in each contribution
				$tracking->get_track_sql($sql_ary, ext::TITANIA_SUPPORT, 't.parent_id', 'tst');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_SUPPORT, 'parent_match' => true);
			break;

			case 'author_support' :
				$page_url = $object->get_url('support');
				$contrib_ids = titania::$cache->get_author_contribs($object->user_id, $types, phpbb::$user, true);
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.parent_id', array_map('intval', $contrib_ids));

				// We also display the queue discussion topic between validators and authors in the support area
				$sql_ary['WHERE'] .= ' AND (t.topic_type = ' . ext::TITANIA_SUPPORT . ' OR t.topic_type = ' . ext::TITANIA_QUEUE_DISCUSSION . ')';

				// Do not display abandoned contribs in the author's support section
				$sql_ary['WHERE'] .= 'AND contrib.contrib_limited_support = 0';

				// Additional tracking for marking items as read in each contribution
				$tracking->get_tracks(ext::TITANIA_SUPPORT, $contrib_ids);
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_SUPPORT, 'parent_match' => true);

				// Additional tracking for all support topics
				$tracking->get_track_sql($sql_ary, ext::TITANIA_ALL_SUPPORT, 0, 'tstg');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_ALL_SUPPORT, 'id' => 0);

				// Track the queue discussion too if applicable
				if ($types->find_authed('queue_discussion'))
				{
					$tracking->get_track_sql($sql_ary, ext::TITANIA_QUEUE_DISCUSSION, 0, 'tqt');
					$topic->additional_unread_fields[] = array('type' => ext::TITANIA_QUEUE_DISCUSSION, 'id' => 0, 'type_match' => true);
				}

				// Try to grab the category/contrib name
				$sql_ary['SELECT'] .= ', contrib.contrib_name, contrib.contrib_name_clean, contrib.contrib_id, contrib.contrib_type';
				$sql_ary['LEFT_JOIN'] = array_merge(((isset($sql_ary['LEFT_JOIN'])) ? $sql_ary['LEFT_JOIN'] : array()), array(
					array(
						'FROM'	=> array(TITANIA_CONTRIBS_TABLE	=> 'contrib'),
						'ON'	=> 'contrib.contrib_id = t.parent_id',
					),
				));

				// Do not order stickies first
				$sql_ary['ORDER_BY'] = $sort->get_order_by();
				$switch_on_sticky = false;
			break;

			case 'author_tracker' :
				$page_url = $object->get_url('tracker');
				$contrib_ids = titania::$cache->get_author_contribs($object->user_id, $types, phpbb::$user);
				$sql_ary['WHERE'] .= ' AND ' . phpbb::$db->sql_in_set('t.parent_id', array_map('intval', $contrib_ids));

				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . ext::TITANIA_TRACKER;
			break;

			case 'all_support' :
				// Try to grab the category/contrib name
				$sql_ary['SELECT'] .= ', contrib.contrib_name, contrib.contrib_name_clean, contrib.contrib_id, contrib.contrib_type';
				$sql_ary['LEFT_JOIN'] = array_merge(((isset($sql_ary['LEFT_JOIN'])) ? $sql_ary['LEFT_JOIN'] : array()), array(
					array(
						'FROM'	=> array(TITANIA_CONTRIBS_TABLE	=> 'contrib'),
						'ON'	=> 'contrib.contrib_id = t.parent_id',
					),
				));

				if (!phpbb::$auth->acl_get('u_titania_mod_post_mod'))
				{
					$mod_in_types = $types->find_authed('moderate');

					$sql_ary['WHERE'] .= ' AND (' . phpbb::$db->sql_in_set('contrib.contrib_status', array(ext::TITANIA_CONTRIB_APPROVED, ext::TITANIA_CONTRIB_DOWNLOAD_DISABLED)) .
											(sizeof($mod_in_types) ? ' OR ' . phpbb::$db->sql_in_set('contrib.contrib_type', $mod_in_types) : '') . ')';
				}

				if ($types->exists($options['contrib_type']))
				{
					$page_url = $controller_helper->route('phpbb.titania.support', array(
						'type'	=> $types->get($options['contrib_type'])->url
					));

					$sql_ary['WHERE'] .= ' AND contrib.contrib_type = ' . $options['contrib_type'];
				}
				else
				{
					$page_url = $controller_helper->route('phpbb.titania.support');
				}

				// Additional tracking field (to allow marking all support/discussion as read)
				$sql_ary['WHERE'] .= ' AND t.topic_type = ' . ext::TITANIA_SUPPORT;

				$tracking->get_track_sql($sql_ary, ext::TITANIA_SUPPORT, 'contrib.contrib_id', 'tst');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_SUPPORT, 'parent_match' => true);

				// Additional tracking for all support topics
				$tracking->get_track_sql($sql_ary, ext::TITANIA_ALL_SUPPORT, 0, 'tstg');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_ALL_SUPPORT, 'id' => 0);

				// Do not order stickies first
				$sql_ary['ORDER_BY'] = $sort->get_order_by();
				$switch_on_sticky = false;
			break;

			case 'support' :
			default :
				$page_url = $object->get_url('support');
				$sql_ary['WHERE'] .= ' AND t.parent_id = ' . (int) $object->contrib_id;

				// We also display the queue discussion topic between validators and authors in the support area
				if ($object->is_author ||$object->is_active_coauthor || $object->type->acl_get('queue_discussion'))
				{
					$sql_ary['WHERE'] .= ' AND (t.topic_type = ' . ext::TITANIA_SUPPORT . ' OR t.topic_type = ' . ext::TITANIA_QUEUE_DISCUSSION . ')';
				}
				else
				{
					$sql_ary['WHERE'] .= ' AND t.topic_type = ' . ext::TITANIA_SUPPORT;
				}

				// Additional tracking for marking items as read in each contribution
				$tracking->get_track_sql($sql_ary, ext::TITANIA_SUPPORT, $object->contrib_id, 'tst');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_SUPPORT, 'parent_match' => true);

				// Additional tracking for all support topics
				$tracking->get_track_sql($sql_ary, ext::TITANIA_ALL_SUPPORT, 0, 'tstg');
				$topic->additional_unread_fields[] = array('type' => ext::TITANIA_ALL_SUPPORT, 'id' => 0);

				// Track the queue discussion too if applicable
				if ($object->type->acl_get('queue_discussion'))
				{
					$tracking->get_track_sql($sql_ary, ext::TITANIA_QUEUE_DISCUSSION, 0, 'tqt');
					$topic->additional_unread_fields[] = array('type' => ext::TITANIA_QUEUE_DISCUSSION, 'id' => 0, 'type_match' => true);
				}
			break;
		}

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if (!$sort->sql_count($sql_ary, 't.topic_id'))
		{
			// No results...no need to query more...
			return compact('sort');
		}

		$sort->build_pagination($page_url);

		$last_was_sticky = false;
		phpbb::$template->assign_var('S_SPLIT_BY_TOPIC_TYPE', $switch_on_sticky);

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Store the tracking info we grabbed from the DB
			$tracking->store_from_db($row);

			self::$topics[$row['topic_id']] = $row;

			$topic->__set_array($row);
			$contrib->__set_array($row);

			if (!empty($row['contrib_type']))
			{
				$contrib->set_type($row['contrib_type']);
			}
			$topic->topic_posted = $row['topic_posted'];

			phpbb::$template->assign_block_vars('topics', array_merge($topic->assign_details(), array(
				'S_TOPIC_TYPE_SWITCH'		=> ($switch_on_sticky && $last_was_sticky && !$topic->topic_sticky) ? true : false,

				'CONTRIB_TYPE'				=> (isset($row['contrib_type']) && $row['contrib_type']) ? $types->get($row['contrib_type'])->lang : '',
				'TOPIC_CONTRIB_NAME'		=> (isset($row['contrib_name']) && $row['contrib_name']) ? censor_text($row['contrib_name']) : '',

				'U_VIEW_TOPIC_CONTRIB'				=> (isset($row['contrib_type']) && $row['contrib_type']) ? $contrib->get_url() : '',
				'U_VIEW_TOPIC_CONTRIB_SUPPORT'		=> (isset($row['contrib_type']) && $row['contrib_type']) ? $contrib->get_url('support') : '',
			)));

			$sort = phpbb::$container->get('phpbb.titania.sort');
			$sort->__set_array(array(
				'template_block'	=> 'topics.pagination',
				'total'				=> $topic->get_postcount(),
				'limit'				=> phpbb::$config['posts_per_page'],
			));
			$sort->set_defaults(phpbb::$config['posts_per_page']);
			$sort->build_pagination($topic->get_url());

			$last_was_sticky = $topic->topic_sticky;
		}
		phpbb::$db->sql_freeresult($result);

		unset($topic);

		return compact('sort');
	}

	public static function assign_common()
	{
		phpbb::$template->assign_vars(array(
			'REPORTED_IMG'		=> phpbb::$user->img('icon_topic_reported', 'TOPIC_REPORTED'),
			'UNAPPROVED_IMG'	=> phpbb::$user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
			'NEWEST_POST_IMG'	=> phpbb::$user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
			'LAST_POST_IMG'		=> phpbb::$user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		));
	}

	/**
	* Setup the sort tool and return it for topics display
	*
	* @return \phpbb\titania\sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = phpbb::$container->get('phpbb.titania.sort');
		$sort->set_sort_keys(self::$sort_by);

		if (isset(self::$sort_by[phpbb::$user->data['user_topic_sortby_type']]))
		{
			$sort->default_sort_key = phpbb::$user->data['user_topic_sortby_type'];
		}
		$sort->default_sort_dir = phpbb::$user->data['user_topic_sortby_dir'];
		$sort->default_limit = phpbb::$config['topics_per_page'];

		$sort->result_lang = 'NUM_TOPICS';

		return $sort;
	}
}
