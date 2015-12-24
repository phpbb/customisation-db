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

namespace phpbb\titania\manage\tool;


class base
{
	/** @var int */
	protected $start = 0;

	/** @var int */
	protected $limit = 100;

	/** @var bool */
	protected $staggered = true;

	/** @var int */
	protected $total;

	/** @var string */
	protected $step;

	/** @var array */
	protected $steps = array();

	/**
	 * Check whether the tool is staggered.
	 *
	 * @return bool
	 */
	public function is_staggered()
	{
		return $this->staggered;
	}

	/**
	 * Set start.
	 *
	 * @param int $start
	 * @return $this
	 */
	public function set_start($start)
	{
		$this->start = (int) $start;
		return $this;
	}

	/**
	 * Set step.
	 *
	 * @param string $step
	 * @return $this
	 */
	public function set_step($step)
	{
		$this->step = $step;
		return $this;
	}

	/**
	 * Get current step.
	 *
	 * @return mixed
	 */
	public function get_step()
	{
		$steps = $this->steps;

		if (!strlen($this->step))
		{
			$this->step = array_shift($steps);
		}
		return $this->step;
	}

	/**
	 * Get next step
	 *
	 * @return string|null
	 */
	public function get_next_step()
	{
		$next_step = null;

		if ($this->steps)
		{
			$current = $this->get_step();
			$current_index = array_search($current, $this->steps);
			$next_index = $current_index + 1;

			if (isset($this->steps[$next_index]))
			{
				$next_step = $this->steps[$next_index];
			}
		}
		return $next_step;
	}

	/**
	 * Set limit.
	 *
	 * @param int $limit
	 * @return $this
	 */
	public function set_limit($limit)
	{
		$this->limit = (int) $limit;
		return $this;
	}

	/**
	 * Get tool result array.
	 *
	 * @param string $message
	 * @param int $total
	 * @param int|bool $next_batch
	 * @param string|null $next_step
	 * @return array
	 */
	protected function get_result($message, $total, $next_batch, $next_step = null)
	{
		return array(
			'message'		=> $message,
			'total'			=> $total,
			'next_batch'	=> $next_batch,
			'next_step'		=> $next_step,
		);
	}

	/**
	 * Get route name.
	 *
	 * @return string
	 */
	public function get_route()
	{
		return '';
	}
}
