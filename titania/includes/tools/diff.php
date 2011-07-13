<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

phpbb::_include('diff/diff', false, 'diff');
phpbb::_include('diff/engine', false, 'diff');
phpbb::_include('diff/renderer', false, 'diff');
phpbb::_include('functions_compress', false, 'compress');

/**
* Class to create diffs for updated versions
* @package Titania
*/
class titania_diff
{
	/**
	 * Classname of the diff renderer to use
	 *
	 * @var string
	 */
	private $renderer_type;

	/**
	 * Identification for old and new
	 * This is used by from_dir to add an identification to the diff file
	 *
	 * @var string
	 */
	private $id_old, $id_new;

	/**
	 * constructor
	 *
	 * @param string $renderer_type Classname of the renderer to use
	 */
	public function __construct($renderer_type = 'diff_renderer_unified')
	{
		$this->renderer_type = $renderer_type;
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

	// diff layers

	/**
	 * Create diff from file
	 * Lowest layer
	 *
	 * @param string $filename_old Path to old file
	 * @param string $filename_new Path to new file
	 * @return diff
	 */
	public function from_file($filename_old, $filename_new)
	{
	    $file_old = ($filename_old) ? self::file_contents($filename_old) : '';
		$file_new = ($filename_new) ? self::file_contents($filename_new) : '';

		// create renderer and process diff
		$renderer = new $this->renderer_type();
		return $renderer->render(new diff($file_old, $file_new));
	}

	/**
	 * Create diff from dir
	 *
	 * @param string $dir_old Path to old dir
	 * @param string $dir_new Path to new dir
	 * @return diff
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

		$files_merged = array_merge($files_old, $files_new);

		while (list($filename) = each($files_merged))
		{
			// add a context header for the file
			$result .= "Index: $filename\n";
			$result .= "===================================================================\n";
			$result .= "--- $filename" . ($this->id_old ? "\t{$this->id_old}" : '') . "\n";
			$result .= "+++ $filename" . ($this->id_new ? "\t{$this->id_new}" : '') . "\n";

			if (isset($files_old[$filename]) && isset($files_new[$filename]))
			{
				// old and new files exist
				$result .= $this->from_file($dir_old . $filename, $dir_new . $filename) . "\n";
			}
			else if (!isset($files_old[$filename]))
			{
				// old file doesn't exist, so it gets added
				$result .= $this->from_file(false, $dir_new . $filename) . "\n";
			}
			else if (!isset($files_new[$filename]))
			{
				// new file doesn't exist, so it gets removed
				$result .= $this->from_file($dir_old . $filename, false) . "\n";
			}
		}

		return $result;
	}

	/**
	 * Create diff from zip
	 *
	 * @param string $filename_old Path to old zip file
	 * @param string $filename_new Path to new zip file
	 * @return diff
	 */
	public function from_zip($filename_old, $filename_new)
	{
	    if (!file_exists($filename_old) || !file_exists($filename_new))
	    {
			return false;
		}

		// temporary dirs
		$tmp_old = TITANIA_ROOT . 'files/temp/' . basename($filename_old) . '/';
		$tmp_new = TITANIA_ROOT . 'files/temp/' . basename($filename_new) . '/';

		// extract files
		$result_old = self::extract_zip($filename_old, $tmp_old);
		$result_new = self::extract_zip($filename_new, $tmp_new);

		// get diff
		$result = $this->from_dir($tmp_old, $tmp_new);

		// clean up
		self::rmdir($tmp_old, true);
		self::rmdir($tmp_new, true);

		return $result;
	}

	/**
	 * Create diff from revision
	 *
	 * @param int $rev_old Old revision
	 * @param int $rev_new New revision
	 * @return diff
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
	 * @return File contents
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