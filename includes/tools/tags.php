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

/**
 * Class to handle some tag related stuff
 *
 * @package Titania
 */
class titania_tags
{
	public static $tags = array();

	private static function load_tags()
	{
		if (sizeof(self::$tags))
		{
			return;
		}

		foreach (titania::$cache->get_tags() as $type => $children)
		{
			foreach ($children as $id => $row)
			{
				self::$tags[$id] = $row;
			}
		}
	}

	/**
	* Get a tag row from what was loaded
	*
	* @param mixed $tag_id
	* @return titania_tags
	*/
	public static function get_tag($tag_id)
	{
		self::load_tags();

		if (!isset(self::$tags[$tag_id]))
		{
			return false;
		}

		return self::$tags[$tag_id];
	}

	/**
	* Get the tag name
	*
	* @param mixed $tag_id
	*/
	public static function get_tag_name($tag_id)
	{
		$lang = phpbb::$user->lang['UNKNOWN'];

		$row = titania_tags::get_tag($tag_id);
		if ($row)
		{
			$lang= (isset(phpbb::$user->lang[$row['tag_field_name']])) ? phpbb::$user->lang[$row['tag_field_name']] : $row['tag_field_name'];
		}

		return $lang;
	}
}
