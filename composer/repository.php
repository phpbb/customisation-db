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

namespace phpbb\titania\composer;

use phpbb\config\config;
use phpbb\exception\runtime_exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class repository
{
	/** @var string */
	protected $repo_dir;

	/** @var string */
	protected $production_link;

	/** @var string */
	protected $build_dir;

	/** @var Filesystem */
	protected $fs;

	/**
	 * Constructor.
	 *
	 * @param string $ext_root_path
	 */
	public function __construct($ext_root_path)
	{
		$this->repo_dir = $ext_root_path . 'composer_packages/';
		$this->production_link = $this->repo_dir . 'prod';
		$this->build_dir = $this->repo_dir . 'build/';
		$this->fs = new Filesystem;
	}

	/**
	 * Prepare build directory.
	 *
	 * @param bool $force
	 * @return $this
	 */
	public function prepare_build_dir($force)
	{
		if ($this->fs->exists($this->build_dir))
		{
			if (!$force)
			{
				throw new runtime_exception('BUILD_IN_PROGRESS');
			}
			$this->fs->remove($this->build_dir);
		}
		$this->fs->mkdir($this->build_dir);

		return $this;
	}

	/**
	 * Deploy build.
	 *
	 * @throws runtime_exception If deployment failed
	 * @return $this
	 */
	public function deploy_build()
	{
		if (!$this->fs->exists($this->build_dir))
		{
			return $this;
		}
		$this->build_parents();
		$current_build = false;

		if ($this->fs->exists($this->production_link))
		{
			if (is_link($this->production_link))
			{
				$current_build = readlink($this->production_link);
			}
		}

		try
		{
			$new_build = $this->repo_dir . 'prod_' . time();
			$this->fs->rename($this->build_dir, $new_build);
			$this->fs->remove($this->production_link);
			$this->fs->symlink($new_build, $this->production_link);
		}
		catch (\Exception $e)
		{
			$this->fs->remove($this->production_link);

			if ($current_build)
			{
				$this->fs->symlink($current_build, $this->production_link);
			}

			throw new runtime_exception('PACKAGES_BUILD_DEPLOYMENT_FAILED');
		}
		if ($current_build)
		{
			$this->fs->remove($current_build);
		}
		return $this;
	}

	/**
	 * Set release in packages array.
	 *
	 * @param array $packages
	 * @param string $composer_json
	 * @param string $download_url
	 * @return array Returns $packages array with $composer_json
	 * 	set in appropriate package if valid
	 */
	public function set_release(array $packages, $composer_json, $download_url)
	{
		$composer_json = json_decode($composer_json, true);

		if (!is_array($composer_json) || empty($composer_json['name']) || empty($composer_json['version']))
		{
			return $packages;
		}
		if (!isset($packages[$composer_json['name']]))
		{
			$packages[$composer_json['name']] = array();
		}
		$composer_json['dist'] = array(
			'url'	=> $download_url,
			'type'	=> 'zip',
		);
		$packages[$composer_json['name']][$composer_json['version']] = $composer_json;

		return $packages;
	}

	/**
	 * Dump include file.
	 *
	 * @param string $name		File name
	 * @param array $packages	Packages to be dumped
	 */
	public function dump_include($name, array $packages)
	{
		foreach ($packages as $package_name => $versions)
		{
			uksort($packages[$package_name], array('\phpbb\titania\versions', 'reverse_version_compare'));
		}
		$packages = json_encode(
			array('packages' => $packages),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
		$file = $this->build_dir . $name;
		$this->fs->dumpFile($file, $packages);
	}

	/**
	 * Get include files.
	 *
	 * @return Finder
	 */
	protected function get_include_files()
	{
		$finder = new Finder;
		$finder
			->files()
			->depth('== 0')
			->name('/^packages\-[a-z]+\-\d+\.json$/')
			->in($this->build_dir)
		;

		return $finder;
	}

	/**
	 * Build include file parents included package.json
	 */
	protected function build_parents()
	{
		$includes = $this->get_include_files();
		$parent = $types = array();

		foreach ($includes as $file)
		{
			$type = $this->get_include_type($file->getFilename());

			if ($type)
			{
				if (!isset($types[$type]))
				{
					$types[$type] = array();
				}
				$types[$type][$file->getFilename()] = array(
					'sha1'	=> hash_file('sha1', $file->getPathname()),
				);
			}
		}

		foreach ($types as $type => $includes)
		{
			$type_filename = 'packages-' . $type . '.json';
			$type_filepath = $this->build_dir . $type_filename;
			$contents = json_encode(array('includes' => $includes));
			$this->fs->dumpFile($type_filepath, $contents);

			$parent[$type_filename] = array(
				'sha1'	=> hash_file('sha1', $type_filepath),
			);
		}
		if (!empty($parent))
		{
			$contents = json_encode(array('includes' => $parent));
			$this->fs->dumpFile($this->build_dir . 'packages.json', $contents);
		}
	}

	/**
	 * Get contrib type name from include file name.
	 *
	 * @param string $filename
	 * @return bool|string Returns contrib type name or false if not a valid filename
	 */
	protected function get_include_type($filename)
	{
		$filename = utf8_basename($filename);
		$match = array();

		if (preg_match('/^packages\-([a-z]+)\-\d+\.json$/', $filename, $match))
		{
			return $match[1];
		}
		return false;
	}

	/**
	 * Trigger cron job.
	 *
	 * @param config $config
	 */
	public static function trigger_cron(config $config)
	{
		// Trigger it in the next 5 minutes.
		$config->set('titania_next_repo_rebuild', time() + 300, false);
	}
}
