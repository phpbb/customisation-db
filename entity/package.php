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

namespace phpbb\titania\entity;

use Symfony\Component\Finder\Expression\Expression;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class package
{
	/** @var \SplFileInfo */
	protected $source;

	/** @var \SplFileInfo */
	protected $temp_path;

	/** @var \Symfony\Component\Filesystem\Filesystem */
	protected $filesystem;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->filesystem = new Filesystem;
	}

	/**
	 * Set package source.
	 *
	 * @param string $path
	 * @return \phpbb\titania\entity\package
	 */
	public function set_source($source)
	{
		$this->source = new \SplFileInfo($source);
		return $this;
	}

	/**
	 * Get package source.
	 *
	 * @return string
	 */
	public function get_source()
	{
		return ($this->source) ? $this->source->getPathname() : '';
	}

	/**
	 * Check whether the package source exists.
	 *
	 * @return bool
	 */
	public function source_exists()
	{
		return $this->source && $this->source->isReadable();
	}

	/**
	 * Set temporary extraction path.
	 *
	 * @param string $path
	 * @param bool $generate_dir	Generate temp directory under given path.
	 * @return \phpbb\titania\entity\package
	 */
	public function set_temp_path($path, $generate_dir = false)
	{
		if ($generate_dir)
		{
			$path = $this->generate_temp_path($path);
		}
		$this->temp_path = new \SplFileInfo($path);
		return $this;
	}

	/**
	 * Get temporary extraction path.
	 *
	 * @return string
	 */
	public function get_temp_path()
	{
		return ($this->temp_path) ? $this->temp_path->getPathname() : '';
	}

	/**
	 * Generate temporary extraction path.
	 *
	 * @param string $base_dir	Base directory to create temporary directory under.
	 * @return string
	 */
	public function generate_temp_path($base_dir)
	{
		$base_dir = new \SplFileInfo($base_dir);
		return $base_dir->getPathname() . '/' . unique_id() . '_' . time();
	}

	/**
	 * Get package size.
	 *
	 * @return int
	 */
	public function get_size()
	{
		return ($this->source_exists()) ? filesize($this->get_source()) : 0;
	}

	/**
	 * Get package md5 sum
	 *
	 * @return string
	 */
	public function get_md5_hash()
	{
		return ($this->source_exists()) ? md5_file($this->get_source()) : '';
	}

	protected function get_finder()
	{
		$finder = new Finder;
		return $finder
			->ignoreDotFiles(false)
			->ignoreVCS(false);
	}

	/**
	 * Find directory containing the specified files/directories.
	 *
	 * @param array $required		Required files/directories in form of
	 * 		array('files' => array(), 'directories' => array()
	 * @param array $optional		Optional files/directories in form of
	 * 		array('files' => array(), 'directories' => array()
	 * @param string|array $exclude	Directory names to exclude from search
	 * @return null|string			Returns best matched directory or null
	 * 		if no directory was found.
	 */
	public function find_directory(array $search, $exclude = null)
	{
		if (!$this->ensure_extracted())
		{
			return null;
		}

		$req_ary = array(
			'required' => array(),
			'optional' => array(),
		);

		$_search = array('files' => $req_ary, 'directories' => $req_ary);
		$names = array();

		foreach ($search as $type => $items)
		{
			foreach ($items as $requirement => $filenames)
			{
				$expressions = $this->to_expression($filenames);
				$names = array_merge($names, $expressions);
				$expressions = array_fill_keys($expressions, null);
				$_search[$type][$requirement] = $expressions;
			}
		}

		$found = $this->find($names, $exclude);
		$unique_dirs = array();

		foreach ($found as $file)
		{
			$dir = $file->getRelativePath();

			if (!isset($unique_dirs[$dir]))
			{
				$unique_dirs[$dir] = $_search;
			}
			$type = ($file->isDir()) ? 'directories' : 'files';
			$match = $this->get_matched_exp($file->getFilename(), $_search[$type]);

			if (!$match)
			{
				continue;
			}
			$unique_dirs[$dir][$type][$match['expression']] = true;
		}
		$possible = array();

		foreach ($unique_dirs as $dir => $types)
		{
			$optional_count = 0;
			foreach ($types as $type => $items)
			{
				if (in_array(null, $items['required']))
				{
					continue;
				}
				foreach ($items['optional'] as $found)
				{
					if ($found)
					{
						$optional_count++;
					}
				}
			}
			$possible[$dir] = $optional_count;
		}
		if (empty($possible))
		{
			return null;
		}

		$possible = array_keys($possible, max($possible));
		sort($possible);

		return $possible[0];
	}

	/**
	 * Convert search string to regular expression.
	 *
	 * @param string|array $strings
	 * @return array
	 */
	protected function to_expression($strings)
	{
		$strings = (is_array($strings)) ? $strings : array($strings);
		$expressions = array();

		foreach ($strings as $string)
		{
			$expressions[] = Expression::create($string)->getRegex()->render();
		}
		return $expressions;
	}

	/**
	 * Get matched expression.
	 *
	 * @param $name
	 * @param $search
	 * @return array|bool
	 */
	protected function get_matched_exp($name, $search)
	{
		foreach ($search as $type => $expressions)
		{
			foreach ($expressions as $expression => $null)
			{
				if (preg_match($expression, $name))
				{
					return array(
						'type'			=> $type,
						'expression'	=> $expression,
					);
				}
			}
		}
		return false;
	}

	/**
	 * Find files.
	 *
	 * @param $items
	 * @param $exclude
	 * @return $this|array|Finder
	 */
	protected function find($items, $exclude)
	{
		if (empty($items))
		{
			return array();
		}
		$finder = $this->get_finder();

		if (!empty($exclude))
		{
			$finder->exclude($exclude);
		}
		if (is_array($items))
		{
			foreach ($items as $item)
			{
				$finder->name($item);
			}
		}
		else
		{
			$finder->name($items);
		}
		return $finder->in($this->get_temp_path());
	}

	/**
	 * Restore package root.
	 *
	 * @param string $directory Root location relative to extraction path
	 * @param string $new_name	Optional new root name.
	 */
	public function restore_root($directory, $new_name = null)
	{
		if (!$this->ensure_extracted())
		{
			return;
		}
		$root_path = $this->get_temp_path() . '/' . $directory;

		if (!$this->filesystem->exists($root_path))
		{
			return;
		}

		$temp_path = $this->get_temp_path();
		$new_name = ($new_name !== null) ? $new_name : utf8_basename($directory);
		$copy_path = $temp_path . '_repack/';
		$copy_root_path = $copy_path . $new_name;
		$new_root_path = $temp_path . '/' . $new_name;

		$this->filesystem->remove($copy_path);

		if ($new_name)
		{
			$this->filesystem->mkdir($copy_root_path);
		}
		$this->filesystem->rename($root_path, $copy_root_path, true);
		$this->filesystem->remove($temp_path);
		if ($new_name)
		{
			$this->filesystem->mkdir($new_root_path);
		}
		$this->filesystem->rename($copy_root_path, $new_root_path, true);
		$this->filesystem->remove($copy_path);
	}

	/**
	 * Repack package.
	 *
	 * @param bool $clean	Remove extraneous VCS files and OS data files
	 * @return \phpbb\titania\entity\package
	 */
	public function repack($clean = true)
	{
		if (!$this->ensure_extracted())
		{
			return;
		}
		$archive = new \ZipArchive();

		if (!$archive->open($this->get_source(), \ZipArchive::OVERWRITE))
		{
			return $this;
		}
		$finder = $this->get_finder();

		if ($clean)
		{
			$finder
				->exclude(array('.git', '.svn', 'CVS', '__MACOSX'))
				->notName(
					'/^desktop\.ini|Thumbs\.db|\.DS_Store|\.gitmodules|\.gitignore$/'
				)
			;
		}
		$finder
			->files()
			->in($this->get_temp_path());

		foreach ($finder as $file)
		{
			$archive->addFile(
				$file->getRealPath(),
				$file->getRelativePath() . '/' . $file->getFilename()
			);
		}
		$archive->close();

		return $this;
	}

	/**
	 * Ensure that the package is extracted.
	 *
	 * @return bool Returns false if not possible to extract
	 */
	public function ensure_extracted()
	{
		if (!$this->source_exists())
		{
			return false;
		}

		if ($this->temp_path && $this->filesystem->exists($this->get_temp_path()))
		{
			return true;
		}
		try
		{
			$this->extract($this->get_source());
		}
		catch (\Exception $e)
		{
			return false;
		}
		return true;
	}

	/**
	 * Extract archived package.
	 *
	 * @param $source			Package archive source
	 * @param string $target	Extraction target (optional)
	 * @return \phpbb\titania\entity\package
	 * @throws \Exception
	 */
	public function extract($source = null, $target = null)
	{
		if ($source !== null)
		{
			$this->set_source($source);
		}
		if ($target !== null)
		{
			$this->set_temp_path($target);
		}
		if (!$this->source_exists() || !$this->get_temp_path())
		{
			throw new \Exception('INVALID_PACKAGE');
		}

		// Clear out old stuff if there is anything here...
		$this->filesystem->remove($this->get_temp_path());

		$archive = new \ZipArchive();
		$archive->open($this->get_source());
		$archive->extractTo($this->get_temp_path());
		$archive->close();

		return $this;
	}

	/**
	 * Remove temporary files.
	 */
	public function cleanup()
	{
		if ($this->temp_path)
		{
			$this->filesystem->remove($this->get_temp_path());
		}
	}
}
