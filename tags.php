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

class tags
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var array */
	protected $tags = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\cache\service $cache
	 */
	public function __construct(\phpbb\user $user, \phpbb\titania\cache\service $cache)
	{
		$this->user = $user;
		$this->cache = $cache;
	}

	/**
	 * Load tags
	 */
	protected function load_tags()
	{
		if (!empty($this->tags))
		{
			return;
		}

		foreach ($this->cache->get_tags() as $children)
		{
			foreach ($children as $id => $row)
			{
				$this->tags[$id] = $row;
			}
		}
	}

	/**
	* Get a tag row from what was loaded
	*
	* @param mixed $tag_id
	* @return array
	*/
	public function get_tag($tag_id)
	{
		$this->load_tags();

		if (!isset($this->tags[$tag_id]))
		{
			return false;
		}

		return $this->tags[$tag_id];
	}

	/**
	* Get the tag name
	*
	* @param mixed $tag_id
	* @return string
	*/
	public function get_tag_name($tag_id)
	{
		$lang = $this->user->lang('UNKNOWN');

		$row = $this->get_tag($tag_id);
		if ($row)
		{
			$lang = $this->user->lang($row['tag_field_name']);
		}

		return $lang;
	}
}
