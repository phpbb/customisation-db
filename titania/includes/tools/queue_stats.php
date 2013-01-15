<?php
/**
*
* @package Titania
* @copyright (c) 2013 phpBB Customisation Database Team
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

/**
 * Class to handle queue stats
 *
 * @package Titania
 */
class titania_queue_stats
{
	/**
	 * Queue type id
	 *
	 * @var int
	 */
	private $queue_type;

	public function __construct($queue_type)
	{
		$this->set_queue_type($queue_type);
	}

	public function set_queue_type($queue_type)
	{
		$this->queue_type = (int) $queue_type;
	}

	/**
	 * Get the oldest submission time of a revision submitted to the queue
	 *
	 * @param mixed $included_statuses Optional array|string of queue statuses to include.
	 * @param mixed $excluded_statuses Optional array|string of queue statuses to exclude.
	 *
	 * @return int Returns the timestamp for the oldest revision submitted within the status constraints
	 */
	public function get_queue_item_count($included_statuses = false, $excluded_statuses = false)
	{
		$sql = 'SELECT COUNT(queue_id) AS status_count 
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . $this->queue_type .
				((!empty($included_statuses)) ? ' AND ' . phpbb::$db->sql_in_set('queue_status', $included_statuses) : '') .
				((!empty($excluded_statuses)) ? ' AND ' . phpbb::$db->sql_in_set('queue_status', $excluded_statuses, true) : '');
		phpbb::$db->sql_query($sql);

		return phpbb::$db->sql_fetchfield('status_count');
	}

	/**
	 * Get the average wait of a set of queue items
	 *
	 * @param int $start_time Lower limit on submission time
	 * @param int $end_time Upper limit on submission time
	 * @param bool $from_current_time Determine the wait using the current time
	 * @param mixed $included_statuses Optional array|string of queue statuses to include.
	 * @param mixed $excluded_statuses Optional array|string of queue statuses to exclude.
	 * @param int $cache_ttl Total time to cache the result.
	 *
	 * @return string Returns the wait as a translated string. Eg: 1 Hour; 20 Days; 10 minutes
	 */
	public function get_average_wait($start_time, $end_time = 0, $wait_from_current_time = false, $included_statuses = false, $excluded_statuses = false, $cache_ttl = 0)
	{
		$sql = 'SELECT COUNT(*) AS total_items, SUM(queue_submit_time) AS sum_submit_time, SUM(queue_close_time) AS sum_close_time
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . $this->queue_type . ' AND queue_submit_time > ' . (int) $start_time .
				((!empty($end_time)) ? ' AND queue_submit_time < ' . (int) $end_time : '') .
				((!empty($included_statuses)) ? ' AND ' . phpbb::$db->sql_in_set('queue_status', $included_statuses) : '') .
				((!empty($excluded_statuses)) ? ' AND ' . phpbb::$db->sql_in_set('queue_status', $excluded_statuses, true) : '');
		$result = phpbb::$db->sql_query($sql, $cache_ttl);
		$data = phpbb::$db->sql_fetchrow($result);

		if ($data['total_items'])
		{
			if ($wait_from_current_time)
			{
				$average_wait = titania::$time - ($data['sum_submit_time'] / $data['total_items']);
			}
			else
			{
				$average_wait = ($data['sum_close_time'] - $data['sum_submit_time']) / $data['total_items'];
			}

			return format_time_delta($average_wait);
		}

		return '0';
	}

	/**
	 * Get the oldest submission time of a revision submitted to the queue
	 *
	 * @param mixed $included_statuses Optional array|string of queue statuses to include. 
	 * @param mixed $excluded_statuses Optional array|string of queue statuses to exclude.
	 *
	 * @return int Returns the timestamp for the oldest revision submitted within the status constraints
	 */
	public function get_oldest_revision_time($included_statuses = false, $excluded_statuses = false)
	{
		$sql = 'SELECT queue_submit_time
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . $this->queue_type . 
				((!empty($included_statuses)) ? ' AND ' . phpbb::$db->sql_in_set('queue_status', $included_statuses) : '') .
				((!empty($excluded_statuses)) ? ' AND ' . phpbb::$db->sql_in_set('queue_status', $excluded_statuses, true) : '') . '
			ORDER BY queue_submit_time ASC';
		$result = phpbb::$db->sql_query_limit($sql, 1);

		return (int) phpbb::$db->sql_fetchfield('queue_submit_time');
	}

	/**
	 * Get the queue history between two timestamps
	 *
	 * @param int $start_time Lower time limit of the queue activity calendar
	 * @param int $end_time Upper time limit of the queue activity calendar
	 *
	 * @return Retuns an array in the form of Array([year] => Array([month] => Array([day] => Array('new' => int, 'approved' => int, 'denied' => int))))
	 */
	public function get_queue_history($start_time, $end_time)
	{
		$start_time = (int) $start_time;
		$end_time = (int) $end_time;
		$day_tpl = array('new' => 0, 'approved' => 0, 'denied' => 0);
	
		$history = create_calendar_ary($start_time, $end_time, $day_tpl);

		$sql = 'SELECT queue_status AS status, queue_submit_time AS submit_time, queue_close_time AS close_time
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . $this->queue_type . '
				AND ' . phpbb::$db->sql_in_set('queue_status', array(TITANIA_QUEUE_CLOSED, TITANIA_QUEUE_HIDE), true) . '
				AND ((queue_submit_time > ' . $start_time . ' AND queue_submit_time < ' . $end_time . ') 
					OR (queue_close_time > ' . $start_time . ' AND queue_close_time < ' . $end_time . '))';
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->fill_history_actions($history, $start_time, $end_time, $row);
		}
		phpbb::$db->sql_freeresult($result);

		return $history;
	}

	/**
	 * Determine what actions took place for a queue item and fill the history array accordingly
	 *
	 * @param array $history History array
	 * @param int $start_time Lower time limit of the queue activity calendar
	 * @param int $end_time Upper time limit of the queue activity calendar
	 * @param array $item Array containing info about the queue item in the form of array('status' => int, 'submit_time' => int, 'close_time' => int)
	 *
	 * @return void
	 */
	public function fill_history_actions(&$history, $start_time, $end_time, $item)
	{
		if ($start_time <= $item['submit_time'])
		{
			$this->add_history_action($history, 'new', $item['submit_time']);
		}
		$validated_status_map = array(
			TITANIA_QUEUE_APPROVED	=> 'approved',
			TITANIA_QUEUE_DENIED 	=> 'denied',
		);

		if (isset($validated_status_map[$item['status']]) && $start_time <= $item['close_time'] && $end_time >= $item['close_time'])
		{
			$this->add_history_action($history, $validated_status_map[$item['status']], $item['close_time']);
		}
	}

	/**
	 * Increase the count for an action in the history array
	 *
	 * @param array $history History array
	 * @param string $action Status of the queue item; eg: new, approved, denied
	 * @param int $time Time that the action occurred
	 *
	 * @return void
	 */
	public function add_history_action(&$history, $action, $time)
	{
		$time = offset_user_time($time);
		list($day, $month, $year) = explode(' ', gmdate('j n Y', $time));

		$history[$year][$month][$day][$action]++;
	}

	/**
	 * Determine the maximum activity that occurred in a single day
	 *
	 * @param array $history History array
	 *
	 * @return int Sum of all actions that occured in the most active day
	 */
	public function get_max_day_activity($history)
	{
		$max_activity = 0;
		foreach ($history as $year)
		{
			foreach ($year as $month)
			{
				foreach ($month as $day)
				{
					$max_activity = max($max_activity, array_sum($day));
				}
			}
		}

		return $max_activity;
	}

	/**
	 * Assign the template block variables for the history array
	 *
	 * @param array $history History array
	 *
	 * @return void
	 */
	public function assign_history_display($history)
	{
		$max_activity = $this->get_max_day_activity($history);

		// If there hasn't been any activity, there's nothing to assign
		if (!$max_activity)
		{
			return;
		}
		$prev_month = 0;

		foreach ($history as $year => $months)
		{
			foreach ($months as $month => $days)
			{
				foreach($days as $day => $actions)
				{
					$action_vars = array();
					$remainder_pct = 100;
					$px_offset = sizeof($actions);

					foreach ($actions as $action => $count)
					{
						$action = strtoupper($action);
						$action_vars[$action . '_CNT'] = $count;
						$action_vars[$action . '_PCT'] = round(($count / $max_activity) * 100);

						$remainder_pct -= $action_vars[$action . '_PCT'];
						$px_offset -= ($count) ? 1 : 0; 
					}

					phpbb::$template->assign_block_vars('dayrow', array_merge($action_vars, array(
						'DAY'			=> $day,
						'MONTH'			=> $month,
						'YEAR'			=> $year,
						'REMAINDER_PCT'	=> $remainder_pct,
						'MONTH_SWITCH'	=> ($prev_month != $month) ? true : false,
						'PIXEL_OFFSET'	=> $px_offset,
					)));
					$prev_month = $month;
				}
			}
		}
	}
}
