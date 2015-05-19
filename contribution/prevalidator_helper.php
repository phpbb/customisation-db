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

namespace phpbb\titania\contribution;

class prevalidator_helper
{
	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $ext_root_path;

	/** @var array */
	protected $errors = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param string $ext_root_path
	 */
	public function __construct(\phpbb\user $user, $ext_root_path)
	{
		$this->user = $user;
		$this->ext_root_path = $ext_root_path;
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * Prepare a test directory containing phpBB source files.
	 *
	 * @param string $version the full phpBB version number.  Ex: 2.0.23, 3.0.1, 3.0.7-pl1
	 * @return string Returns the path to the test directory
	 */
	public function prepare_phpbb_test_directory($version)
	{
		$version = preg_replace('#[^a-zA-Z0-9\.\-]+#', '', $version);

		$phpbb_root = $this->ext_root_path . 'store/extracted/' . $version . '/';
		$phpbb_package = $this->ext_root_path . 'includes/phpbb_packages/phpBB-' . $version . '.zip';

		$package = new \phpbb\titania\entity\package;
		$package
			->set_temp_path($phpbb_root)
			->set_source($phpbb_package);

		if (!file_exists($phpbb_root . 'common.php'))
		{
			if (!$package->source_exists())
			{
				$this->errors[] = $this->user->lang('FILE_NOT_EXIST', $phpbb_package);
				return false;
			}

			// Unzip to our temp directory
			$package->extract();

			// Find the phpBB root
			$package_root = $package->find_directory(array(
				'files' => array(
					'required' => 'common.php',
				),
			));

			// Move it to the correct location
			if ($package_root != '')
			{
				$package->restore_root($package_root, '');
			}
		}

		return $phpbb_root;
	}
}
