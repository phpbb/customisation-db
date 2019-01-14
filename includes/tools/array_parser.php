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

use Battye\ArrayParser\parser;

class array_parser
{
	/**
	 * Find the array of subscribed events
	 * @param $file_path
	 * @return array
	 * @throws Exception
	 */
	public static function check_events($file_path)
	{
		// Find the events, if there are any
		$regex = '/\bclass\s+\S+\s+implements\s+EventSubscriberInterface\s*{\s*(?:[^{}]*{[^{}]*})*[^{}]*\bgetSubscribedEvents\(\)\s*{[^{}]*?(?:\barray\s*\(|\[)([^{}]*?)(?:\)|\])[^{}]*}/x';
		$result = parser::parse_regex($regex, $file_path);

		return (!empty($result)) ? $result : [];
	}
}