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

class queue_overlord
{
	/**
	* Queue array
	* Stores [id] => row
	*
	* @var array
	*/
	public static $queue = array();

	public static $sort_by = array(
		't' => array('SUBMIT_TIME', 'q.queue_submit_time', true),
	);

	/**
	* Load queue(s) from queue id(s)
	*
	* @param int|array $queue_id queue_id or an array of queue_ids
	*/
	public static function load_queue($queue_id)
	{
		if (!is_array($queue_id))
		{
			$queue_id = array($queue_id);
		}

		// Only get the rows for those we have not gotten already
		$queue_id = array_diff($queue_id, array_keys(self::$queue));

		if (!sizeof($queue_id))
		{
			return;
		}

		$sql_ary = array(
			'SELECT' => 'q.*',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE	=> 'q',
			),

			'WHERE' => phpbb::$db->sql_in_set('q.queue_id', array_map('intval', $queue_id))
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$queue[$row['queue_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	public static function get_queue_object($queue_id, $query = false)
	{
		if (!isset(self::$queue[$queue_id]) && $query)
		{
			self::load_queue($queue_id);
		}

		if (!isset(self::$queue[$queue_id]))
		{
			return false;
		}

		$queue = new titania_queue;
		$queue->__set_array(self::$queue[$queue_id]);

		return $queue;
	}

	/**
	* Display forum-like list for queue
	*
	* @param string $type The type of queue (the contrib type)
	* @param object|boolean $sort The sort object (includes/tools/sort.php)
	*/
	public static function display_queue($type, $queue_status = TITANIA_QUEUE_NEW, $sort = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = self::build_sort();
		}
		$sort->request();

		$queue_ids = array();

		$sql_ary = array(
			'SELECT' => '*, u.username as topic_first_post_username, u.user_colour as topic_first_post_user_colour, ul.username as topic_last_post_username, ul.user_colour as topic_last_post_user_colour',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE		=> 'q',
				TITANIA_CONTRIBS_TABLE	=> 'c',
				TITANIA_REVISIONS_TABLE	=> 'r',
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => 'q.queue_type = ' . (int) $type .
				(($queue_status) ? ' AND q.queue_status = ' . (int) $queue_status : ' AND q.queue_status > 0 ') . '
				AND c.contrib_id = q.contrib_id
				AND r.revision_id = q.revision_id
				AND t.topic_id = q.queue_topic_id',

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'u'),
			'ON'	=> 't.topic_first_post_user_id = u.user_id',
		);

		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(USERS_TABLE => 'ul'),
			'ON'	=> 't.topic_last_post_user_id = ul.user_id',
		);

		titania_tracking::get_track_sql($sql_ary, TITANIA_TOPIC, 't.topic_id');

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if (!$sort->sql_count($sql_ary, 'q.queue_id'))
		{
			// No results...no need to query more...
			return;
		}

		$sort->build_pagination(titania_url::$current_page, titania_url::$params);

		$queue_ids = $user_ids = array();

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Store the tracking info we grabbed from the DB
			titania_tracking::store_from_db($row);

			$queue_ids[] = $row['queue_id'];
			$user_ids[] = $row['topic_first_post_user_id'];
			$user_ids[] = $row['topic_last_post_user_id'];
			$user_ids[] = $row['submitter_user_id'];

			self::$queue[$row['queue_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		users_overlord::load_users($user_ids);

		$topic = new titania_topic;

		foreach ($queue_ids as $queue_id)
		{
			$row = self::$queue[$queue_id];

			$topic->__set_array($row);

			phpbb::$template->assign_block_vars('topics', array_merge($topic->assign_details(), array(
				'TOPIC_SUBJECT'				=> $row['contrib_name'] . ' - ' . $row['revision_version'],
				'S_TOPIC_PROGRESS'			=> ($row['queue_progress']) ? true : false,
				'U_VIEW_TOPIC'				=> titania_url::append_url($topic->get_url(), array('tag' => $queue_status)),
			)));
		}

		unset($topic);

		phpbb::$template->assign_vars(array(
			'S_TOPIC_LIST'		=> true,
		));

		// Assign common stuff for topics list
		topics_overlord::assign_common();
	}

	/**
	* Display the complete queue item (includes the topic)
	*
	* @param int $queue_id
	* @return array data from display_queue_item
	*/
	public static function display_queue_item_complete($queue_id)
	{
		$data = self::display_queue_item($queue_id);

		// Display the posts
		posts_overlord::display_topic_complete($data['topic']);

		return $data;
	}

	/**
	* Display a single queue item
	*
	* @param int $queue_id
	* @return array('row' => (sql selection), 'contrib' => $contrib, 'topic' => $topic)
	*/
	public static function display_queue_item($queue_id)
	{
		titania::add_lang('contributions');

		$sql_ary = array(
			'SELECT' => '*',

			'FROM'		=> array(
				TITANIA_QUEUE_TABLE		=> 'q',
				TITANIA_REVISIONS_TABLE	=> 'r',
				TITANIA_TOPICS_TABLE	=> 't',
			),

			'WHERE' => 'q.queue_id = ' . (int) $queue_id . '
				AND r.revision_id = q.revision_id
				AND t.topic_id = q.queue_topic_id',
		);

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('NO_QUEUE_ITEM');
		}

		self::$queue[$queue_id] = $row;

		// Load the contribution
		$contrib = new titania_contribution();
		$contrib->load((int) $row['contrib_id']);
		$contrib->get_download($row['revision_id']);
		$contrib->get_revisions();
		$contrib->get_screenshots();
		$contrib->assign_details();

		// Load the topic (with the already selected data)
		$topic = new titania_topic;
		$topic->__set_array($row);

		// Bit of a hack for the posting
		$_REQUEST['t'] = $topic->topic_id;

		// Some quick-actions
		$quick_actions = array();
		if ($row['queue_status'] > 0)
		{
			if ($row['queue_progress'] == phpbb::$user->data['user_id'])
			{
				$quick_actions['MARK_NO_PROGRESS'] = array(
					'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'no_progress')),
					'class'		=> 'queue_progress',
				);
			}
			else if (!$row['queue_progress'])
			{
				$quick_actions['MARK_IN_PROGRESS'] = array(
					'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'in_progress')),
					'class'		=> 'queue_progress',
				);
			}

			$tags = titania::$cache->get_tags(TITANIA_QUEUE);
			unset($tags[$row['queue_status']]);

			$quick_actions['CHANGE_STATUS'] = array(
				'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'move')),
				'class'		=> 'change_status',
				'tags'		=> $tags,
			);

			if (titania_types::$types[$contrib->contrib_type]->acl_get('moderate'))
			{
				$quick_actions['REPACK'] = array(
					'url'		=> titania_url::append_url($contrib->get_url('revision'), array('repack' => $row['revision_id'])),
					'class'		=> 'repack',
				);
			}

			// This allows you to alter the author submitted notes to the validation team, not really useful as the field's purpose was changed, so commenting out
			/*$quick_actions['ALTER_NOTES'] = array(
				'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'notes')),
			);*/

			// Misc actions
			$subactions = array();
			if (/*!$row['mpv_results'] && */titania_types::$types[$contrib->contrib_type]->mpv_test)
			{
				$subactions['RETEST_MPV'] = array(
					'url'		=> titania_url::build_url('', array('action' => 'mpv', 'revision' => $row['revision_id'])),
				);
			}

			if (/*!$row['automod_results'] && */titania_types::$types[$contrib->contrib_type]->automod_test)
			{
				$subactions['RETEST_AUTOMOD'] = array(
					'url'		=> titania_url::build_url('', array('action' => 'automod', 'revision' => $row['revision_id'])),
				);
			}

			// misc subactions
			$subactions['REBUILD_FIRST_POST'] = array(
				'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'rebuild')),
			);
			if (titania_types::$types[$contrib->contrib_type]->acl_get('moderate') && !$row['allow_author_repack'])
			{
				$subactions['ALLOW_AUTHOR_REPACK'] = array(
					'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'allow_author_repack')),
				);
			}

			$quick_actions['CAT_MISC'] = array(
				'subactions'	=> $subactions,
				'class'			=> 'misc',
			);

			// Validation
			if (titania_types::$types[$contrib->contrib_type]->acl_get('validate'))
			{
				$quick_actions['APPROVE'] = array(
					'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'approve', 'start' => '*destroy*')),
					'class'		=> 'approve',
				);
				$quick_actions['DENY'] = array(
					'url'		=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'deny', 'start' => '*destroy*')),
					'class'		=> 'deny',
				);
			}
		}

		foreach ($quick_actions as $lang_key => $data)
		{
			phpbb::$template->assign_block_vars('queue_actions', array(
				'NAME'		=> (isset(phpbb::$user->lang[$lang_key])) ? phpbb::$user->lang[$lang_key] : $lang_key,
				'CLASS'		=> (isset($data['class'])) ? $data['class'] : '',

				'U_VIEW'	=> (isset($data['url'])) ? $data['url'] : '',
			));

			if (isset($data['tags']))
			{
				foreach ($data['tags'] as $tag_id => $tag_row)
				{
					phpbb::$template->assign_block_vars('queue_actions.subactions', array(
						'ID'		=> $tag_id,
						'NAME'		=> ((isset(phpbb::$user->lang[$tag_row['tag_field_name']])) ? phpbb::$user->lang[$tag_row['tag_field_name']] : $tag_row['tag_field_name']),

						'U_ACTION'	=> titania_url::append_url($data['url'], array('id' => $tag_id, 'hash' => generate_link_hash('quick_actions'))),
					));
				}
			}

			if (isset($data['subactions']))
			{
				foreach ($data['subactions'] as $sublang_key => $subdata)
				{
					phpbb::$template->assign_block_vars('queue_actions.subactions', array(
						'NAME'		=> ((isset(phpbb::$user->lang[$sublang_key])) ? phpbb::$user->lang[$sublang_key] : $sublang_key),

						'U_ACTION'	=> $subdata['url'],
					));
				}
			}
		}

		if ($row['queue_status'] == -2)
		{
			$current_status = phpbb::$user->lang['REVISION_DENIED'];
		}
		else if ($row['queue_status'] == -1)
		{
			$current_status = phpbb::$user->lang['REVISION_APPROVED'];
		}
		else
		{
			$current_status = titania_tags::get_tag_name($row['queue_status']);
		}
		phpbb::$template->assign_vars(array(
			'CURRENT_STATUS'			=> $current_status,

			'S_DISPLAY_CONTRIBUTION'	=> true,
			'S_IN_QUEUE'				=> true,

			'U_POST_REPLY'				=> titania_url::append_url(titania_url::$current_page_url, array('action' => 'reply', 't' => $topic->topic_id)),
			'U_NEW_REVISION'			=> false, // Prevent nubs from trying to submit a new revision when they want to actually repack
		));

		// Subscriptions
		titania_subscriptions::handle_subscriptions(TITANIA_TOPIC, $topic->topic_id, titania_url::$current_page_url);

		return compact('row', 'contrib', 'topic');
	}

	/**
	* Display the categories (tags)
	*
	* @param int $type
	*/
	public static function display_categories($type, $selected = false)
	{
		$tags = titania::$cache->get_tags(TITANIA_QUEUE);
		$tag_count = array();
		$total = 0;

		$sql = 'SELECT queue_status, COUNT(queue_id) AS cnt FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . (int) $type . '
			GROUP BY queue_status';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$total += ($row['queue_status'] > 0) ? $row['cnt'] : 0;
			$tag_count[$row['queue_status']] = $row['cnt'];
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$template->assign_block_vars('queue_tags', array(
			'TAG_NAME'		=> phpbb::$user->lang['ALL'],
			'TAG_COUNT'		=> $total,
			'U_VIEW_TAG'	=> titania_url::append_url(titania_url::$current_page_url, array('tag' => 'all', 'start' => '*destroy*')),
			'S_SELECTED'	=> ($selected == 0) ? true : false,
		));

		foreach ($tags as $tag_id => $row)
		{
			if (!isset($tag_count[$tag_id]))
			{
				// Hide empty ones
				continue;
			}

			phpbb::$template->assign_block_vars('queue_tags', array(
				'TAG_NAME'		=> (isset(phpbb::$user->lang[$row['tag_field_name']])) ? phpbb::$user->lang[$row['tag_field_name']] : $row['tag_field_name'],
				'TAG_COUNT'		=> $tag_count[$tag_id],
				'U_VIEW_TAG'	=> titania_url::append_url(titania_url::$current_page_url, array('tag' => $tag_id, 'start' => '*destroy*')),
				'S_SELECTED'	=> ($selected == $tag_id) ? true : false,
			));
		}
	}

	/**
	* Generate the stats page
	*/
	public function display_stats()
	{
		$stats = false;//titania::$cache->get('queue_stats');

		if ($stats === false)
		{
			$stats = array();

			foreach (titania_types::$types as $type_id => $class)
			{
				foreach (titania::$config->queue_stats_periods as $name => $data)
				{
					// Shorten
					$temp_stats = array();

					// Select the stats for this type
					$sql = 'SELECT revision_id, contrib_id, queue_type, submitter_user_id, queue_submit_time, queue_close_time, queue_close_user
						FROM ' . TITANIA_QUEUE_TABLE . '
							WHERE queue_type = ' . (int) $type_id . '
								AND queue_close_time > 0 ' .
								((isset($data['where'])) ? 'AND ' . $data['where'] : '');
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						// List of submitters with totals
						$temp_stats['submitters'][$row['submitter_user_id']] = (isset($temp_stats['submitters'][$row['submitter_user_id']])) ? $temp_stats['submitters'][$row['submitter_user_id']] + 1 : 1;

						// List of users who closed the queue items with totals
						$temp_stats['closers'][$row['queue_close_user']] = (isset($temp_stats['closers'][$row['queue_close_user']])) ? $temp_stats['closers'][$row['queue_close_user']] + 1 : 1;

						// Count
						$temp_stats['total'] = (isset($temp_stats['total'])) ? $temp_stats['total'] + 1 : 1;

						// Total time in validation
						$temp_stats['total_validation_time'] = (isset($temp_stats['total_validation_time'])) ? $temp_stats['total_validation_time'] + ($row['queue_close_time'] - $row['queue_submit_time']): ($row['queue_close_time'] - $row['queue_submit_time']);
					}
					phpbb::$db->sql_freeresult($result);

					// Shorten
					$stats[$type_id][$name] = $temp_stats;
				}

				// Handle the data
				foreach (titania::$config->queue_stats_periods as $name => $data)
				{
					// Shorten
					$temp_stats = $stats[$type_id][$name];

					// List of submitters with totals
					if (isset($temp_stats['submitters']) && sizeof($temp_stats['submitters']))
					{
						arsort($temp_stats['submitters']);
					}

					// List of users who closed the queue items with totals
					if (isset($temp_stats['closers']) && sizeof($temp_stats['closers']))
					{
						arsort($temp_stats['closers']);
					}

					// Average time in validation
					$temp_stats['average_validation_time'] = floor((isset($temp_stats['total_validation_time']) && $temp_stats['total_validation_time'] > 0) ? $temp_stats['total_validation_time'] / $temp_stats['total'] : 0);

					// Shorten
					$stats[$type_id][$name] = $temp_stats;
				}
			}

			titania::$cache->put('queue_stats', $stats, (60 * 60));
		}

		// Need to grab some user data
		$user_ids = array();
		foreach (titania_types::$types as $type_id => $class)
		{
			foreach (titania::$config->queue_stats_periods as $name => $data)
			{
				// Shorten
				$temp_stats = $stats[$type_id][$name];

				foreach (array('submitters', 'closers') as $type)
				{
					if (isset($temp_stats[$type]) && sizeof($temp_stats[$type]))
					{
						$i = 1;
						foreach ($temp_stats[$type] as $user_id => $cnt)
						{
							// Only grab the first 5
							if ($i > 5)
							{
								break;
							}
							$i++;

							$user_ids[] = $user_id;
						}
					}
				}
			}
		}

		// Load the users
		users_overlord::load_users($user_ids);

		// Output
		foreach (titania_types::$types as $type_id => $class)
		{
			phpbb::$template->assign_block_vars('stats', array(
				'TITLE'		=> $class->lang,
			));

			foreach (titania::$config->queue_stats_periods as $name => $data)
			{
				// Shorten
				$temp_stats = $stats[$type_id][$name];

				$avg_num_weeks = $avg_num_days = 0;
				if ($temp_stats['average_validation_time'] > 0)
				{
					$avg_num_weeks = floor($temp_stats['average_validation_time'] / (60 * 60 * 24 * 7));
					$avg_num_days = floor($temp_stats['average_validation_time'] / (60 * 60 * 24)) %7;
				}

				phpbb::$template->assign_block_vars('stats.periods', array(
					'TITLE'		=> (isset(phpbb::$user->lang[$data['lang']])) ? phpbb::$user->lang[$data['lang']] : $data['lang'],

					'AVERAGE_VALIDATION_TIME'	=> phpbb::$user->lang('NUM_WEEKS', $avg_num_weeks) . ' ' . phpbb::$user->lang('NUM_DAYS', $avg_num_days),
				));

				// Submitter/closer data
				foreach (array('submitters', 'closers') as $type)
				{
					if (isset($temp_stats[$type]) && sizeof($temp_stats[$type]))
					{
						$i = 1;
						foreach ($temp_stats[$type] as $user_id => $cnt)
						{
							// Only output the first 5
							if ($i > 5)
							{
								break;
							}
							$i++;

							// Assign user details and total
							phpbb::$template->assign_block_vars('stats.periods.' . $type, array_merge(
								users_overlord::assign_details($user_id), array(
								'TOTAL'		=> $cnt,
							)));
						}
					}
				}
			}
		}
	}

	/**
	* Generate topic status
	*/
	public static function folder_img($is_unread, &$folder_img, &$folder_alt, $replies = 0)
	{
		titania::_include('functions_display', 'titania_topic_folder_img');

		titania_topic_folder_img($folder_img, $folder_alt, $replies, $is_unread);
	}

	/**
	* Setup the sort tool and return it for posts display
	*
	* @return titania_sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = new titania_sort();
		$sort->set_sort_keys(self::$sort_by);

		$sort->set_defaults(phpbb::$config['topics_per_page']);

		return $sort;
	}
}
