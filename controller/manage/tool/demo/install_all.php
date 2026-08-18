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

namespace phpbb\titania\controller\manage\tool\demo;

use phpbb\titania\controller\manage\tool\tool;

class install_all extends tool
{
	/**
	 * {@inheritDoc}
	 *
	 * Tells the user how many styles are missing from the demo board before
	 * asking for confirmation.
	 */
	protected function confirm_action()
	{
		$submit = $this->request->is_set('submit');
		$hash = $this->request->variable('hash', '');

		if (confirm_box(true) || ($submit && check_link_hash($hash, 'titania_manage')))
		{
			return true;
		}
		$pending = $this->tool
			->set_branch($this->request->variable('branch', 0))
			->count_pending();

		$message = ($pending === false)
			? $this->user->lang('CONFIRM_TOOL_ACTION')
			: $this->user->lang('INSTALL_DEMO_STYLES_CONFIRM', $pending);

		confirm_box(false, $message);

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_next_params(array $result)
	{
		return array_merge(
			parent::get_next_params($result),
			array(
				'branch'	=> $result['branch'],
				'installed'	=> $result['installed'],
				'already'	=> $result['already'],
				'skipped'	=> $result['skipped'],
				'failed'	=> $result['failed'],
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function run()
	{
		return $this->tool
			->set_branch($this->request->variable('branch', 0))
			->set_start($this->request->variable('start', 0))
			->run(
				$this->request->variable('installed', 0),
				$this->request->variable('already', 0),
				$this->request->variable('skipped', 0),
				$this->request->variable('failed', 0)
			)
		;
	}
}
