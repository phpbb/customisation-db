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

namespace phpbb\titania;

class date
{
	/**
	 * Format the delta between two timestamps.
	 *
	 * @param \phpbb\user $user
	 * @param int $start_time Lower time limit. If only $start_time is provided, then its value is used as the delta.
	 * @param int $end_time Upper time limit.
	 *
	 * @return string Returns a translated string containing the appropriate label (up to days) for the time delta. Eg. Less than a minute, 2 Minutes, 1 Hour, 10 Days
	 */
	public static function format_time_delta(\phpbb\user $user, $start_time, $end_time = 0)
	{
		if ($end_time)
		{
			$delta = abs($end_time - $start_time);
		}
		else
		{
			$delta = $start_time;
		}

		if ($delta < 60)
		{
			$delta = '';
			$delta_label = 'LESS_THAN_A_MINUTE';
		}
		else
		{
			if ($delta < 3600)
			{
				$delta = floor($delta / 60);
				$delta_label = 'MINUTE';
			}
			else if ($delta < 86400)
			{
				$delta = floor($delta / 3600);
				$delta_label = 'HOUR';
			}
			else
			{
				$delta = floor($delta / 86400);
				$delta_label = 'DAY';
			}

			$delta_label .= ($delta != 1) ? 'S' : '';
		}

		return $delta . ' ' . $user->lang($delta_label);
	}

	/**
	 * Creates a simple calendar array for each day in a time interval
	 *
	 * @param \phpbb\user $user	User object
	 * @param \DateTime $start	Time to start the calendar
	 * @param \DateTime $end		Time to end the calendar
	 * @param mixed $day_tpl		Value to fill each day with
	 *
	 * @return array Returns an array in the form of Array([year] => Array([month] => Array([day] => $day_tpl)))
	 */
	public static function get_calendar_ary(\phpbb\user $user, \DateTime $start, \DateTime $end, $day_tpl)
	{
		$start->setTimezone($user->timezone);
		$end->setTimezone($user->timezone);
		$period = new \DatePeriod($start, new \DateInterval('P1D'), $end);
		$calendar = array();

		foreach ($period as $time)
		{
			list($day, $month, $year) = explode(' ', $time->format('j n Y'));
			$calendar[$year][$month][$day] = $day_tpl;
		}
		return $calendar;
	}
}
