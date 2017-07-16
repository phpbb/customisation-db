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

namespace phpbb\titania\queue;

use phpbb\titania\date;
use phpbb\titania\ext;

/**
 * Class to handle queue stats
 */
class stats
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Queue type id
	 *
	 * @var int
	 */
	protected $queue_type;

	/** @var \DateTimezone */
	protected $utc;

	/** @var \DateTime */
	protected $date;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\template\template $template)
	{
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;

		$this->utc = new \DateTimezone('UTC');
		$this->date = new \DateTime();
	}

	/**
	 * Set queue type.
	 *
	 * @param int $queue_type
	 */
	public function set_queue_type($queue_type)
	{
		$this->queue_type = (int) $queue_type;
	}

	/**
	 * Returns a \DateTime object configured to the given timestamp
	 * in the user's timezone.
	 *
	 * @param int $time		Time stamp
	 * @return \DateTime
	 */
	protected function get_user_datetime($time)
	{
		return $this->date
			->setTimezone($this->utc)
			->setTimestamp($time)
			->setTimezone($this->user->timezone);
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
			((!empty($included_statuses)) ? ' AND ' . $this->db->sql_in_set('queue_status', $included_statuses) : '') .
			((!empty($excluded_statuses)) ? ' AND ' . $this->db->sql_in_set('queue_status', $excluded_statuses, true) : '');
		$this->db->sql_query($sql);

		return $this->db->sql_fetchfield('status_count');
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
			((!empty($included_statuses)) ? ' AND ' . $this->db->sql_in_set('queue_status', $included_statuses) : '') .
			((!empty($excluded_statuses)) ? ' AND ' . $this->db->sql_in_set('queue_status', $excluded_statuses, true) : '');
		$result = $this->db->sql_query($sql, $cache_ttl);
		$data = $this->db->sql_fetchrow($result);

		if ($data['total_items'])
		{
			if ($wait_from_current_time)
			{
				$average_wait = time() - ($data['sum_submit_time'] / $data['total_items']);
			}
			else
			{
				$average_wait = ($data['sum_close_time'] - $data['sum_submit_time']) / $data['total_items'];
			}

			return date::format_time_delta($this->user, $average_wait);
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
			((!empty($included_statuses)) ? ' AND ' . $this->db->sql_in_set('queue_status', $included_statuses) : '') .
			((!empty($excluded_statuses)) ? ' AND ' . $this->db->sql_in_set('queue_status', $excluded_statuses, true) : '') . '
			ORDER BY queue_submit_time ASC';
		$result = $this->db->sql_query_limit($sql, 1);

		return (int) $this->db->sql_fetchfield('queue_submit_time');
	}

	/**
	 * Get the queue history between two timestamps
	 *
	 * @param \DateTime $start	Lower time limit of the queue activity calendar
	 * @param \DateTime $end	Upper time limit of the queue activity calendar
	 *
	 * @return Retuns an array in the form of Array([year] => Array([month] => Array([day] => Array('new' => int, 'approved' => int, 'denied' => int))))
	 */
	public function get_queue_history(\DateTime $start, \DateTime $end)
	{
		$start_time = (int) $start->setTimezone($this->utc)->getTimestamp();
		$end_time = (int) $end->setTimezone($this->utc)->getTimestamp();
		$day_tpl = array('new' => 0, 'approved' => 0, 'denied' => 0);

		$history = date::get_calendar_ary($this->user, $start, $end, $day_tpl);

		$sql = 'SELECT queue_status AS status, queue_submit_time AS submit_time, queue_close_time AS close_time
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_type = ' . $this->queue_type . '
				AND ' . $this->db->sql_in_set('queue_status', array(ext::TITANIA_QUEUE_CLOSED, ext::TITANIA_QUEUE_HIDE), true) . '
				AND ((queue_submit_time > ' . $start_time . ' AND queue_submit_time < ' . $end_time . ')
					OR (queue_close_time > ' . $start_time . ' AND queue_close_time < ' . $end_time . '))';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->fill_history_actions($history, $start_time, $end_time, $row);
		}
		$this->db->sql_freeresult($result);

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
			ext::TITANIA_QUEUE_APPROVED	=> 'approved',
			ext::TITANIA_QUEUE_DENIED	=> 'denied',
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
		$time = $this->get_user_datetime($time);

		list($day, $month, $year) = explode(' ', $time->format('j n Y'));

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

					$this->template->assign_block_vars('dayrow', array_merge($action_vars, array(
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
