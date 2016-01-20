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

namespace phpbb\titania\controller\manage\tool\contribution;


use phpbb\titania\controller\manage\tool\tool;

class resync_count extends tool
{
	/**
	 * {@inheritDoc}
	 */
	protected function get_next_params(array $result)
	{
		return array_merge(
			parent::get_next_params($result),
			array('prev_contrib' => $result['prev_contrib'])
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function run()
	{
		$start = $this->request->variable('start', 0);
		$step = $this->request->variable('step', '');
		$prev_contrib = $this->request->variable('prev_contrib', 0);

		return $this->tool
			->set_start($start)
			->set_step($step)
			->run($prev_contrib)
		;
	}
}
