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

namespace phpbb\titania;

class versions
{
	/**
	 * Compare two versions in reverse.
	 *
	 * @param string $version1
	 * @param string $version2
	 * @return mixed
	 */
	public static function reverse_version_compare($version1, $version2)
	{
		return version_compare($version2, $version1);
	}

	/**
	 * Order an array of phpBB versions from the database (phpbb_version_branch, phpbb_version_revision)
	 *
	 * @param cache\service $cache
	 * @param array $version_array		Array of versions
	 * @param bool $all_versions		Whether the revision in question supports all phpBB versions
	 * @return array
	 */
	public static function order_phpbb_version_list_from_db(cache\service $cache, array $version_array, $all_versions = false)
	{
		if ($all_versions)
		{
			$all_versions = self::to_string($version_array[0], 'x');
			return array($all_versions);
		}

		$versions = $cache->get_phpbb_versions();

		$ordered_phpbb_versions = array();
		foreach ($version_array as $row)
		{
			$ordered_phpbb_versions[$versions[$row['phpbb_version_branch'] . $row['phpbb_version_revision']]] = true;
		}

		uksort($ordered_phpbb_versions, array('self', 'reverse_version_compare'));

		return array_keys($ordered_phpbb_versions);
	}

	/**
	 * Generate string representation of a version.
	 *
	 * @param array $version	Version array in form of array(
	 * 		'phpbb_version_branch'		=> int,
	 * 		'phpbb_version_revision'	=> string,
	 * )
	 * @param null $revision	Optional revision to use instead of that provided in $version
	 * @return string
	 */
	public static function to_string(array $version, $revision = null)
	{
		$string = $version['phpbb_version_branch'][0] . '.' . $version['phpbb_version_branch'][1];
		$string .= ($revision !== null) ? ".$revision" : ".{$version['phpbb_version_revision']}";
		return $string;
	}
}
