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

use Symfony\Component\Filesystem\Filesystem;

/**
* Class to create diffs for updated versions
* @package Titania
*/
class titania_diff
{
	/**
	 * Identification for old and new
	 * This is used by from_dir to add an identification to the diff file
	 *
	 * @var string
	 */
	private $id_old, $id_new;

	/** @var string */
	protected $ext_root_path;

	/** @var string */
	protected $renderer_type = 'diff_renderer_unified';

	/** @var bool */
	protected $ignore_equal_files = false;

	/** @var array */
	protected $file_extensions = array();

	/**
	 * constructor
	 *
	 * @param string $renderer_type Classname of the renderer to use
	 */
	public function __construct()
	{
		$this->ext_root_path = \titania::$root_path;

		phpbb::_include('diff/diff', false, 'diff', false);
		phpbb::_include('diff/engine', false, 'diff_engine', false);
		phpbb::_include('diff/renderer', false, 'diff_renderer', false);
		phpbb::_include('functions_compress', false, 'compress', false);
	}

	/**
	 * Set id_old and id_new
	 *
	 * @param string $id_old
	 * @param string $id_new
	 */
	public function set_id($id_old, $id_new)
	{
		$this->id_old = $id_old;
		$this->id_new = $id_new;
	}

	/**
	 * Classname of the diff renderer to use
	 *
	 * @param $renderer_type
	 * @return titania_diff
	 */
	public function set_renderer_type($renderer_type)
	{
		$this->renderer_type = $renderer_type;

		return $this;
	}

	/**
	 * Whether or not unchanged file names should be listed
	 *
	 * @param $ignore_equal_files
	 * @return titania_diff
	 */
	public function set_ignore_equal_files($ignore_equal_files)
	{
		$this->ignore_equal_files = $ignore_equal_files;

		return $this;
	}

	/**
	 * Whitelist file extensions. Pass an array of file extensions that shall be processed.
	 * Files with extensions not in this array are not included in the diff.
	 * Pass an empty array to include all files in the diff (default)
	 *
	 * @param array $file_extensions ['txt', 'html']
	 * @return titania_diff
	 */
	public function set_file_extensions($file_extensions)
	{
		$this->file_extensions = array_map('strtolower', $file_extensions);

		return $this;
	}

	// diff layers

	/**
	 * Create diff from file
	 * Lowest layer
	 *
	 * @param string $filename_old Path to old file
	 * @param string $filename_new Path to new file
	 * @return string
	 */
	public function from_file($filename_old, $filename_new)
	{
		$file_old = $filename_old ? self::file_contents($filename_old) : '';
		$file_new = $filename_new ? self::file_contents($filename_new) : '';

		/** @var diff_renderer $renderer */
		$renderer = new $this->renderer_type();
		return $renderer->render(new diff($file_old, $file_new));
	}

	/**
	 * Create diff from dir
	 *
	 * @param string $dir_old Path to old dir
	 * @param string $dir_new Path to new dir
	 * @return bool|string
	 */
	public function from_dir($dir_old, $dir_new)
	{
		if (!file_exists($dir_old) || !file_exists($dir_new))
		{
			return false;
		}

		$result = '';

		$files_old = array_flip(self::list_files($dir_old));
		$files_new = array_flip(self::list_files($dir_new));

		$files_merged = array_keys(array_merge($files_old, $files_new));

		foreach ($files_merged as $filename)
		{
			$file_extension = (new SplFileInfo($filename))->getExtension();

			if ($this->file_extensions && !in_array(strtolower($file_extension), $this->file_extensions))
			{
				continue;
			}

			$diff = '';

			if (isset($files_old[$filename]) && isset($files_new[$filename]))
			{
				// old and new files exist
				$diff .= $this->from_file($dir_old . $filename, $dir_new . $filename) . "\n";
			}
			else if (!isset($files_old[$filename]))
			{
				// old file doesn't exist, so it gets added
				$diff .= $this->from_file(false, $dir_new . $filename) . "\n";
			}
			else if (!isset($files_new[$filename]))
			{
				// new file doesn't exist, so it gets removed
				$diff .= $this->from_file($dir_old . $filename, false) . "\n";
			}

			if (!$this->ignore_equal_files || trim($diff))
			{
				// add a context header for the file
				$result .= "--- $filename" . ($this->id_old ? "\t{$this->id_old}" : '') . "\n";
				$result .= "+++ $filename" . ($this->id_new ? "\t{$this->id_new}" : '') . "\n";
				$result .= $diff;
			}
		}

		return $result;
	}

	/**
	 * Create diff from zip
	 *
	 * @param string $filename_old Path to old zip file
	 * @param string $filename_new Path to new zip file
	 * @return bool|string
	 */
	public function from_zip($filename_old, $filename_new)
	{
		if (!file_exists($filename_old) || !file_exists($filename_new))
		{
			return false;
		}

		// temporary dirs
		$tmp_old = $this->ext_root_path . 'files/temp/' . basename($filename_old) . '/';
		$tmp_new = $this->ext_root_path . 'files/temp/' . basename($filename_new) . '/';

		// extract files
		self::extract_zip($filename_old, $tmp_old);
		self::extract_zip($filename_new, $tmp_new);

		// get diff
		$result = $this->from_dir($tmp_old, $tmp_new);

		// clean up
		(new Filesystem)->remove($tmp_old);
		(new Filesystem)->remove($tmp_new);

		return $result;
	}

	/**
	 * Create diff from revision
	 *
	 * @param int $rev_old Old revision
	 * @param int $rev_new New revision
	 * @return string
	 */
	public function from_revision($rev_old, $rev_new)
	{
		// get filenames
		$sql = 'SELECT a.physical_filename
			FROM ' . TITANIA_ATTACHMENTS_TABLE . ' a
			JOIN ' . TITANIA_REVISIONS_TABLE . ' r
				ON a.attachment_id = r.attachment_id
			WHERE ' . phpbb::$db->sql_in_set('r.revision_id', array($rev_old, $rev_new)) . '
			ORDER BY r.revision_time ASC';
		$result = phpbb::$db->sql_query($sql);
		$filename_old = phpbb::$db->sql_fetchfield('physical_filename');
		$filename_new = phpbb::$db->sql_fetchfield('physical_filename');
		phpbb::$db->sql_freeresult($result);

		if (!$filename_old || !$filename_new)
		{
			return false;
		}

		$this->set_id("revision $rev_old", "revision $rev_new");

		return $this->from_zip($filename_old, $filename_new);
	}

	// static api functions

	/**
	 * Extract a zip file
	 *
	 * @param string $filename Path to zip file
	 * @param string $destination Path to destination
	 */
	public static function extract_zip($filename, $destination)
	{
		// create dir if it doesn't exist
		if (!is_dir($destination))
		{
			mkdir($destination, 0777, true);
		}

		// extract files
		$zip = new compress_zip('r', $filename);
		$zip->extract($destination);
		$zip->close();
	}

	/**
	 * Recursively get all filenames from a dir
	 *
	 * @param string $dir Path to dir
	 * @return array of file paths
	 */
	public static function list_files($root, $dir = '')
	{
		$files = array();

		if ($dh = opendir($root . $dir))
		{
			while (false !== ($file = readdir($dh)))
			{
				if ($file == '.' || $file == '..')
				{
					continue;
				}

				if (is_file($root . $dir . $file))
				{
					$files[] = $dir . $file;
				}
				else
				{
					$files = array_merge($files, self::list_files($root, $dir . $file . '/'));
				}
			}

			closedir($dh);
		}

		return $files;
	}

	/**
	 * Recursively remove dir
	 *
	 * @param string $dir Path to dir
	 * @param boolean $rm_self Remove the dir itself
	 */
	public static function rmdir($dir, $rm_self = false)
	{
		foreach (self::list_files($dir) as $filename)
		{
			if (is_file($dir . $filename))
			{
				@unlink($dir . $filename);
			}
			else
			{
				self::rmdir($dir . $filename . '/', true);
			}
		}

		if ($rm_self)
		{
			rmdir($dir);
		}
	}

	/**
	 * Get contents of a file, don't mess up linefeeds
	 *
	 * @param string $filename Path to file
	 * @return string contents
	 */
	public static function file_contents($filename)
	{
		return preg_replace('#\\r(?:\\n|)#s', "\n", file_get_contents($filename));
	}

	/**
	 * @todo implement:
	 * 1) Show diff from last submitted revision.
	 * 2) Show diff from last approved revision (if any),
	 * 3) show diff from any revision to any revision.
	 */
}
