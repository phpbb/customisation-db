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

use phpbb\request\request_interface;

/**
* URL handler class for Titania
*/
class titania_url
{
	/**
	* Separator used in the URL
	*
	* @var string
	*/
	private static $separator = '-';
	private static $separator_replacement = '%96';

	/**
	* Root URL, the Root URL to the base
	*
	* @var string
	*/
	public static $root_url;

	/**
	* Parameters pulled from the current URL the user is accessing
	*
	* @var array
	*/
	public static $params = array();

	/**
	* Current page we are on (minus all the parameters)
	*
	* @var string
	*/
	public static $current_page;

	/**
	* Current page we are on (built with self::$current_page and self::$params)
	*
	* @var string
	*/
	public static $current_page_url;

	/**
	* Unbuild a url (used from the indexer)
	*
	* @param string $base The base (send $url param here and we'll just update it properly)
	* @param string $params The params
	* @param string|bool $url The url to unbuild from storage (can send it through $base optionally and leave as false)
	*/
	public static function split_base_params(&$base, &$params, $url = false)
	{
		$base = ($url !== false) ? $url : $base;
		$params = array();

		if (substr($base, -1) != '/')
		{
			$params = substr($base, (strrpos($base, '/') + 1));
			$base = substr($base, 0, (strrpos($base, '/') + 1));
			$params = self::split_params($params);
		}
	}

	/**
	* Split up the parameters (from a string to an array, used for the search page from the indexer)
	*
	* @param string $params
	*/
	public static function split_params($params)
	{
		$new_params = array();

		if (strpos($params, '#') !== false)
		{
			$new_params['#'] = substr($params, (strpos($params, '#') + 1));
			$params = substr($params, 0, strpos($params, '#'));
		}

		foreach (explode(self::$separator, $params) as $section)
		{
			// Overwrite the sid_ with the ?sid= so we can use the current session.
			if ((strlen($section) == 37) && (strpos($section, '?sid=') === 0))
			{
				$section = 'sid_' . substr($section, 5);
			}

			$parts = explode('_', $section, 2);
			if (sizeof($parts) == 2)
			{
				if (strpos(urldecode($parts[0]), '[]'))
				{
					$parts[0] = str_replace('[]', '', urldecode($parts[0]));

					if (!isset($new_params[$parts[0]]))
					{
						$new_params[$parts[0]] = array();
					}

					$new_params[$parts[0]][] = urldecode(str_replace(self::$separator_replacement, self::$separator, $parts[1]));
				}
				else
				{
					$new_params[$parts[0]] = $parts[1];
				}
			}
			else if (sizeof($parts) == 1)
			{
				$new_params[] = $parts[0];
			}
		}

		return $new_params;
	}

	/**
	* Create a safe string for the URLs
	*
	* @param string $string
	* @return string
	*/
	public static function url_slug($string)
	{
		$string = self::url_replace($string, false);

		// Replace any number of spaces with a single underscore
		$string = preg_replace('#[\s]+#', '_', $string);

		// Replace a few ugly things
		$match = array('[', ']');
		$string = str_replace($match, '', $string);

		$clean_string = utf8_clean_string(utf8_strtolower($string));
		// Temp fix until issue is fixed in phpBB (http://tracker.phpbb.com/browse/PHPBB3-10921)
		return strtr($clean_string, array('!' => 'ǃ'));
	}

	/**
	* URL Replace
	*
	* Replaces tags and other items that could break the URLs
	*
	* @param string $url
	* @param bool $urlencode
	* @return string
	*/
	public static function url_replace($url, $urlencode = true)
	{

		$match = array('&amp;', '&lt;', '&gt;', '&quot;');
		$url = str_replace($match, ' ', $url);

		$url = trim($url);

		// Our separator replacement is probably a url encoded value, so make sure that it doesn't get re-encoded twice (%25 would replace the % every time it is run)
		$url = str_replace(self::$separator_replacement, self::$separator, $url);

		if ($urlencode)
		{
			$url = urlencode($url);
		}
		else
		{
			// We need to replace some stuff
			$match = array('+', '#', '?', '/', '\\', '\'', '%', '&', self::$separator);
			$url = str_replace($match, ' ', $url);
		}

		$url = str_replace(array('%5B', '%5D', self::$separator), array('[', ']', self::$separator_replacement), $url);

		return $url;
	}
}
