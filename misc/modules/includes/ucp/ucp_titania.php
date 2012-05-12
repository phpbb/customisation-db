<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

global $phpbb_root_path;

/**
* Configuration needed!
*
* Set the titania root path here
*/
define('TITANIA_ROOT', $phpbb_root_path . '../customise/db/');

/**
* Load the header/footer of the custom style
*/
define('LOAD_CUSTOM_STYLE', true);

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ucp_titania
* Handling the titania stuff (proxy for some stuff in titania/manage/
*/
class ucp_titania
{
	var $u_action;
	var $p_master;

	function ucp_titania(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $phpbb_root_path;

		define('PHPBB_INCLUDED', true);
		define('USE_PHPBB_TEMPLATE', true);

		define('IN_TITANIA', true);
		if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
		require TITANIA_ROOT . 'common.' . PHP_EXT;

		// Need a few hacks to be used from within phpBB
		titania_url::decode_url(titania::$config->phpbb_script_path);
		titania::$hook->register(array('titania_url', 'build_url'), 'titania_outside_build_url', 'standalone');
		titania::$hook->register(array('titania_url', 'append_url'), 'titania_outside_build_url', 'standalone');
		titania::$hook->register(array('titania', 'page_header'), 'titania_outside_page_header', 'standalone');
		titania::$hook->register(array('titania', 'page_footer'), 'titania_outside_page_footer', 'standalone');
		$this->p_master->assign_tpl_vars(phpbb::append_sid('ucp'));

		// Include some files
		titania::_include('functions_display', 'titania_topic_folder_img');

		// Setup the sort tool
		$sort = new titania_sort();
		$sort->default_limit = phpbb::$config['topics_per_page'];
		$sort->request();

		// Start initial var setup
		$url = $this->u_action;

		add_form_key('ucp_front_subscription');

		// User wants to unsubscribe?
		if (isset($_POST['unsubscribe']))
		{
			if (check_form_key('ucp_front_subscription'))
			{
				$sections	= request_var('sections', array(0 => array(0 => 0)));
				$items		= request_var('items', array(0 => array(0 => 0)));
				$subscriptions	= $sections + $items;

				if (sizeof($subscriptions))
				{
					foreach($subscriptions as $type => $type_id)
					{
						$object_ids = array_keys($type_id);
						foreach($object_ids as $object_id)
						{
							$sql = 'DELETE FROM ' . TITANIA_WATCH_TABLE . '
								WHERE watch_user_id = ' . phpbb::$user->data['user_id'] . '
								AND watch_object_type = ' . $type . '
								AND watch_object_id = ' . $object_id;
							phpbb::$db->sql_query($sql);
						}
					}
				}
				else
				{
					$msg = phpbb::$user->lang['NO_SUBSCRIPTIONS_SELECTED'];
				}
			}
			else
			{
				$msg = phpbb::$user->lang['FORM_INVALID'];
			}

			if (isset($msg))
			{
				meta_refresh(3, $url);
				$message = $msg . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_UCP'], '<a href="' . $url . '">', '</a>');
				trigger_error($message);
			}
		}

		switch($mode)
		{
			case 'subscription_items':

				$array_items = array(TITANIA_CONTRIB, TITANIA_TOPIC);

				// We prepare pagination stuff
				$sql = 'SELECT COUNT(*) AS subscription_count
					FROM ' . TITANIA_WATCH_TABLE . '
					WHERE ' . phpbb::$db->sql_in_set('watch_object_type', $array_items) . '
						AND watch_user_id = ' . phpbb::$user->data['user_id'];
				phpbb::$db->sql_query($sql);
				$subscription_count = phpbb::$db->sql_fetchfield('subscription_count');
				phpbb::$db->sql_freeresult();

				$sort->total = $subscription_count;
				$sort->build_pagination($url);

				$sql_ary = array(
					'SELECT' => '*,
						CASE w.watch_object_type
							WHEN ' . TITANIA_CONTRIB . ' THEN c.contrib_last_update
							WHEN ' . TITANIA_TOPIC . ' THEN t.topic_last_post_time
						END AS time',

					'FROM' => array(
						TITANIA_WATCH_TABLE => 'w',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_CONTRIBS_TABLE => 'c'),
							'ON'	=> '(w.watch_object_type = ' . TITANIA_CONTRIB. ')
								AND c.contrib_id = w.watch_object_id',
						),
						array(
							'FROM'	=> array(TITANIA_TOPICS_TABLE => 't'),
							'ON'	=> 'w.watch_object_type = ' . TITANIA_TOPIC. '
								AND t.topic_id = w.watch_object_id',
						),
					),

					'WHERE' => 'w.watch_user_id = ' . phpbb::$user->data['user_id'] . '
						AND ' . phpbb::$db->sql_in_set('watch_object_type', $array_items),

					'ORDER_BY' => 'time DESC',
				);

				// Additional tracking for support topics
				titania_tracking::get_track_sql($sql_ary, TITANIA_TOPIC, 't.topic_id');
				titania_tracking::get_track_sql($sql_ary, TITANIA_SUPPORT, 0, 'tsa');
				titania_tracking::get_track_sql($sql_ary, TITANIA_SUPPORT, 't.parent_id', 'tsc');
				titania_tracking::get_track_sql($sql_ary, TITANIA_QUEUE_DISCUSSION, 0, 'tqt');

				// Tracking for contributions
				titania_tracking::get_track_sql($sql_ary, TITANIA_CONTRIB, 'c.contrib_id', 'tc');

				$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

				// Get the data
				$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

				$user_ids = $rows = array();
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$rows[] = $row;

					titania_tracking::store_from_db($row);

					if ($row['watch_object_type'] == TITANIA_TOPIC)
					{
						$user_ids[] = $row['topic_first_post_user_id'];
						$user_ids[] = $row['topic_last_post_user_id'];
					}
					else if ($row['watch_object_type'] == TITANIA_CONTRIB)
					{
						$user_ids[] = $row['contrib_user_id'];
					}
				}
				phpbb::$db->sql_freeresult($result);

				// Get user data
				users_overlord::load_users($user_ids);

				foreach ($rows as $row)
				{
					$folder_img = $folder_alt = '';

					if ($row['watch_object_type'] == TITANIA_TOPIC)
					{
						if (!$row['topic_id'])
						{
							// Topic was deleted
							$sql = 'DELETE FROM ' . TITANIA_WATCH_TABLE . '
								WHERE watch_object_type = ' . (int) $row['watch_object_type'] . '
									AND watch_object_id = ' . (int) $row['watch_object_id'];
							phpbb::$db->sql_query($sql);

							continue;
						}

						$topic = new titania_topic;
						$topic->__set_array($row);
						$topic->additional_unread_fields[] = array('type' => TITANIA_SUPPORT, 'id' => 0);
						$topic->additional_unread_fields[] = array('type' => TITANIA_SUPPORT, 'parent_match' => true);
						$topic->additional_unread_fields[] = array('type' => TITANIA_QUEUE_DISCUSSION, 'id' => 0, 'type_match' => true);

						$tpl_block = 'items';
						$subscription_target = '';
						if ($row['topic_type'] == TITANIA_QUEUE_DISCUSSION)
						{
							$subscription_target = phpbb::$user->lang['SUBSCRIPTION_QUEUE_VALIDATION'];
						}
						if ($row['topic_type'] == TITANIA_QUEUE)
						{
							$subscription_target = phpbb::$user->lang['SUBSCRIPTION_QUEUE'];
						}
						if ($row['topic_type'] == TITANIA_SUPPORT)
						{
							$subscription_target = phpbb::$user->lang['SUBSCRIPTION_SUPPORT_TOPIC'];
						}

						// Tracking check
						$last_read_mark = titania_tracking::get_track(TITANIA_TOPIC, $topic->topic_id, true);
						$last_read_mark = max($last_read_mark, titania_tracking::find_last_read_mark($topic->additional_unread_fields, $topic->topic_type, $topic->parent_id));
						$topic->unread = ($topic->topic_last_post_time > $last_read_mark) ? true : false;

						// Get the folder image
						$topic->topic_folder_img($folder_img, $folder_alt);

						$vars = array(
							'LAST_POST_IMG'					=> phpbb::$user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
							'SUBSCRIPTION_AUTHOR_FULL'		=> users_overlord::get_user($row['topic_first_post_user_id'], '_full'),
							'SUBSCRIPTION_ID'				=> $row['topic_id'],
							'SUBSCRIPTION_LAST_AUTHOR_FULL' => users_overlord::get_user($row['topic_last_post_user_id'], '_full'),
							'SUBSCRIPTION_LAST_TIME'		=> phpbb::$user->format_date($row['topic_last_post_time']),
							'SUBSCRIPTION_TIME'				=> phpbb::$user->format_date($row['topic_time']),
							'SUBSCRIPTION_TARGET'			=> $subscription_target,
							'SUBSCRIPTION_TITLE'			=> censor_text($row['topic_subject']),
							'SUBSCRIPTION_TYPE'				=> $row['watch_object_type'],

							'U_VIEW_SUBSCRIPTION'			=> $topic->get_url(),
							'U_VIEW_LAST_POST'				=> titania_url::append_url($topic->get_url(), array('p' => $topic->topic_last_post_id, '#p' => $topic->topic_last_post_id)),

							'S_ACCESS_TEAMS'		=> ($row['topic_access'] == TITANIA_ACCESS_TEAMS || $row['topic_type'] == TITANIA_QUEUE) ? true : false,
							'S_ACCESS_AUTHORS'		=> ($row['topic_access'] == TITANIA_ACCESS_AUTHORS) ? true : false,
							'S_TOPIC'				=> true
						);
					}
					else if ($row['watch_object_type'] == TITANIA_CONTRIB)
					{
						$tpl_block = 'items';
						$contrib = new titania_contribution;
						$contrib->__set_array($row);
						titania_topic_folder_img($folder_img, $folder_alt, 0, titania_tracking::is_unread(TITANIA_CONTRIB, $contrib->contrib_id, $contrib->contrib_last_update));

						$vars = array(
							'SUBSCRIPTION_AUTHOR_FULL'		=> users_overlord::get_user($row['contrib_user_id'], '_full'),
							'SUBSCRIPTION_CONTRIB_TYPE'		=> titania_types::$types[$contrib->contrib_type]->lang,
							'SUBSCRIPTION_DOWNLOADS'		=> $row['contrib_downloads'],
							'SUBSCRIPTION_ID'				=> $row['contrib_id'],
							'SUBSCRIPTION_TARGET'			=> phpbb::$user->lang['SUBSCRIPTION_CONTRIB'],
							'SUBSCRIPTION_TIME'				=> phpbb::$user->format_date($row['contrib_last_update']),
							'SUBSCRIPTION_TITLE'			=> $row['contrib_name'],
							'SUBSCRIPTION_TYPE'				=> $row['watch_object_type'],
							'SUBSCRIPTION_VIEWS'			=> $row['contrib_views'],

							'U_VIEW_SUBSCRIPTION'			=> $contrib->get_url(),

							'S_CONTRIB'				=> true
						);
					}
					phpbb::$template->assign_block_vars($tpl_block, array_merge($vars, array(
						'FOLDER_IMG'					=> phpbb::$user->img($folder_img, $folder_alt),
						'FOLDER_IMG_SRC'				=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
						'FOLDER_IMG_ALT'				=> phpbb::$user->lang[$folder_alt],
						'FOLDER_IMG_WIDTH'				=> phpbb::$user->img($folder_img, '', false, '', 'width'),
						'FOLDER_IMG_HEIGHT'				=> phpbb::$user->img($folder_img, '', false, '', 'height'),
					)));
				}
			break;

			case 'subscription_sections':

				$array_items = array(TITANIA_SUPPORT, TITANIA_QUEUE, TITANIA_ATTENTION);

				// We prepare pagination stuff
				$sql = 'SELECT COUNT(*) AS subscription_count
					FROM ' . TITANIA_WATCH_TABLE . '
					WHERE ' . phpbb::$db->sql_in_set('watch_object_type', $array_items) . '
					AND watch_user_id = ' . phpbb::$user->data['user_id'];
				phpbb::$db->sql_query($sql);
				$subscription_count = phpbb::$db->sql_fetchfield('subscription_count');
				phpbb::$db->sql_freeresult();

				$sort->total = $subscription_count;
				$sort->build_pagination($url);

				$sql_ary = array(
					'SELECT' => '*,
						CASE w.watch_object_type
							WHEN ' . TITANIA_SUPPORT . ' THEN c.contrib_last_update
						END AS time',

					'FROM' => array(
						TITANIA_WATCH_TABLE => 'w',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_CONTRIBS_TABLE => 'c'),
							'ON'	=> '(w.watch_object_type = ' . TITANIA_SUPPORT. ')
								AND c.contrib_id = w.watch_object_id',
						),
					),

					'WHERE' => 'w.watch_user_id = ' . phpbb::$user->data['user_id'] . '
						AND ' . phpbb::$db->sql_in_set('watch_object_type', $array_items),

					'ORDER_BY' => 'time DESC',
				);

				$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

				// Get the data
				$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

				$user_ids = array();
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$rows[] = $row;
					$user_ids[] = $row['contrib_user_id'];
				}
				phpbb::$db->sql_freeresult($result);

				// Get user data
				users_overlord::load_users($user_ids);

				if (isset($rows))
				{
					foreach ($rows as $row)
					{
						if ($row['watch_object_type'] == TITANIA_SUPPORT)
						{
							$tpl_block = 'sections';
							$contrib = new titania_contribution;
							$contrib->__set_array($row);

							$vars = array(
								'SUBSCRIPTION_AUTHOR_FULL'		=> users_overlord::get_user($row['contrib_user_id'], '_full'),
								'SUBSCRIPTION_ID'				=> $row['watch_object_id'],
								'SUBSCRIPTION_TARGET'			=> phpbb::$user->lang['SUBSCRIPTION_SUPPORT'],
								'SUBSCRIPTION_TIME'				=> phpbb::$user->format_date($row['contrib_last_update']),
								'SUBSCRIPTION_TITLE'			=> $row['contrib_name'],
								'SUBSCRIPTION_TYPE'				=> $row['watch_object_type'],

								'U_VIEW_SUBSCRIPTION'			=> $contrib->get_url('support')
							);
						}
						else if ($row['watch_object_type'] == TITANIA_ATTENTION)
						{
							$tpl_block = 'sections';

							$vars = array(
								'SUBSCRIPTION_ID'		=> $row['watch_object_id'],
								'SUBSCRIPTION_TIME'		=> phpbb::$user->format_date($row['watch_mark_time']),
								'SUBSCRIPTION_TITLE'	=> phpbb::$user->lang['SUBSCRIPTION_ATTENTION'],
								'SUBSCRIPTION_TYPE'		=> $row['watch_object_type'],

								'S_ATTENTION'			=> true,
								'S_ACCESS_TEAMS'		=> true,

								'U_VIEW_SUBSCRIPTION'	=> titania_url::build_url('manage/attention')
							);
						}
						else if ($row['watch_object_type'] == TITANIA_QUEUE)
						{
							$tpl_block = 'sections';
							$queue_id = $row['watch_object_id'];
							// Setup the base url we will use
							$base_url = titania_url::build_url('manage/queue');

							$vars = array(
								'SUBSCRIPTION_ID'		=> $queue_id,
								'SUBSCRIPTION_TARGET'	=> titania_types::$types[$queue_id]->lang,
								'SUBSCRIPTION_TIME'		=> phpbb::$user->format_date($row['watch_mark_time']),
								'SUBSCRIPTION_TITLE'	=> phpbb::$user->lang['SUBSCRIPTION_QUEUE'],
								'SUBSCRIPTION_TYPE'		=> $row['watch_object_type'],

								'S_QUEUE'				=> true,
								'S_ACCESS_TEAMS'		=> true,

								'U_VIEW_SUBSCRIPTION'	=> titania_url::append_url($base_url, array('queue' => titania_types::$types[$queue_id]->url))
							);
						}
						phpbb::$template->assign_block_vars($tpl_block, $vars);
					}
				}
			break;
		}

		phpbb::$template->assign_vars(array(
			'S_ACTION'				=> $url,
			'TITANIA_THEME_PATH' 	=> titania::$absolute_path . 'styles/' . titania::$config->style . '/theme/'
		));

		titania::page_header(phpbb::$user->lang['SUBSCRIPTION_TITANIA']);
		titania::page_footer(true, 'manage/' . $mode . '.html');
	}
}

function titania_outside_build_url(&$hook, $base, $params = array())
{
	global $mode;

	if ($base == 'manage/' . $mode || $base == titania_url::$current_page || strpos($base, 'ucp.' . PHP_EXT))
	{
		return phpbb::append_sid('ucp', array_merge(array('i' => 'titania', 'mode' => $mode), $params));
	}
}

function titania_outside_page_header(&$hook, $page_title)
{
	page_header($page_title);

	return true;
}

function titania_outside_page_footer(&$hook, $run_cron)
{
	page_footer(false);

	return true;
}
