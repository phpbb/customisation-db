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

use phpbb\event\php_exporter;

/**
 * Class extracted_php_exporter
 * Override the constructor so we can specify a custom directory
 */
class extracted_php_exporter extends php_exporter
{
	/**
	 * extracted_php_exporter constructor.
	 * @param $phpbb_root_path
	 * @param $directory
	 */
	public function __construct($phpbb_root_path, $directory)
	{
		parent::__construct($phpbb_root_path);
		$this->path = $directory;
	}

	/**
	 * Override the path so we don't have any conflicts
	 * @param string $file
	 * @return int|void
	 */
	public function crawl_php_file($file)
	{
		$old_path = $this->path;
		$this->path = '';

		parent::crawl_php_file($file);

		$this->path = $old_path;
	}
}
