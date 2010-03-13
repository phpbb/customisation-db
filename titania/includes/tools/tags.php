<?php
/**
*
* @package Titania
* @version $Id$
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

/**
 * Class to handle some tag related stuff
 *
 * @package Titania
 */
class titania_tags
{
	public static $tags = array();

	/**
	* Load tags
	*
	* @param mixed $tag_ids
	*/
	public static function load_tag($tag_ids)
	{
		if (!is_array($tag_ids))
		{
			$tag_ids = array($tag_ids);
		}

		$sql = 'SELECT * FROM ' . TITANIA_TAG_FIELDS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('tag_id', array_map('intval', $tag_ids));
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$tags[$row['tag_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Get a tag row from what was loaded
	*
	* @param mixed $tag_id
	* @return titania_tags
	*/
	public static function get_tag($tag_id)
	{
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