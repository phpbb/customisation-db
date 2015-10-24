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
	 * @return array
	 */
	protected function get_result($message, $total, $next_batch)
	{
		return array(
			'message'		=> $message,
			'total'			=> $total,
			'next_batch'	=> $next_batch,
		);
	}
}
