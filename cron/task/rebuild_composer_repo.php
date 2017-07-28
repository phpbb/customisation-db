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

namespace phpbb\titania\cron\task;

use phpbb\config\config;
use phpbb\titania\manage\tool\composer\rebuild_repo as rebuild_tool;

class rebuild_composer_repo extends \phpbb\cron\task\base
{
	/** @var config */
	protected $config;

	/** @var rebuild_tool */
	protected $tool;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param rebuild_tool $tool
	 */
	public function __construct(config $config, rebuild_tool $tool)
	{
		$this->config = $config;
		$this->tool = $tool;
	}

	/**
	 * Check whether the cron task should run.
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return time() >= (int) $this->config['titania_next_repo_rebuild'];
	}

	/**
	 * Run cron task.
	 */
	public function run()
	{
		// Rebuild again in 24 hours
		$next_rebuild = time() + (24 * 60 * 60);
		$this->config->set('titania_next_repo_rebuild', $next_rebuild, false);
		$this->tool
			->set_start(0)
			->set_limit(false)
			->run()
		;
	}
}
