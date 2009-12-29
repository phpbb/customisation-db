<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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
	* Build URL by appending the needed parameters to a base URL
	*
	* @param string $base The base URL, Ex: customisation/mod/
	* @param array $params Array of parameters we need to clean and append to the base url
	* @return string
	*/
	public static function build_url($base, $params = array())
	{
		global $_SID;

		// Prevent rebuilding...
		if (self::is_built($base))
		{
			return self::append_url($base, $params);
		}

		$final_url = self::$root_url . $base;

		// Append a / at the end if required
		if (substr($final_url, -1) != '/')
		{
			$final_url .= '/';
		}

		// Add style= to the url data if it is in there
		if (isset($_REQUEST['style']))
		{
			$params['style'] = request_var('style', '');
		}

		// Add the Session ID if required.
		if ($_SID)
		{
			$params['sid'] = $_SID;
		}

		return self::append_url($final_url, $params);
	}

	/**
	 * Check if the url was built once already (contains the root URL)
	 *
	 * @param <string> $base The URL you want to check
	 * @return <bool> True if it was already built, false if it was not
	 */
	public static function is_built($base)
	{
		if (strpos($base, self::$root_url) !== false)
		{
			return true;
		}

		return false;
	}

	/**
	* Append parameters to a base URL
	*
	* Different from build_url in this does not prepare the base, nor worry about session_id.  Only use this if you've already used build_url
	*
	* @param string $url The URL we currently have
	* @param array $params Array of parameters we need to clean and append to the base url
	* @return string
	*/
	public static function append_url($url, $params = array())
	{
		// Extract the anchor from the end of the base if there is one
		$anchor = '';
		if (strpos($url, '#') !== false)
		{
			$anchor = substr($url, strpos($url, '#'));
			$url = substr($url, 0, strpos($url, '#'));
		}

		// Now clean and append the items
		foreach ($params as $name => $value)
		{
			// Special case when we just want to add one thing to the URL (ex, the topic title)
			if (is_int($name))
			{
				$url .= ((substr($url, -1) != '/') ? self::$separator : '') . self::url_replace($value);
				continue;
			}

			if (substr($name, 0, 1) == '#')
			{
				$anchor = $name . $value;
				continue;
			}

			if (substr($url, -1) != '/')
			{
				$url .= self::$separator;
			}

			$url .= self::url_replace($name) . '_' . self::url_replace($value);
		}

		// Now append the anchor again
		$url .= $anchor;

		return $url;
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

		return utf8_clean_string(utf8_strtolower($string));
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
		$match	= array('#', '?', '/', '\\', '\'', '&amp;', '&lt;', '&gt;', '&quot;', ':', self::$separator);
		$url	= str_replace($match, '', $url);

		return ($urlencode) ? urlencode($url) : $url;
	}

	/**
	* Decode the url we are currently on and put the things in $_REQUEST/$_GET
	*
	* This function should be called before phpBB is initialized
	*/
	public static function decode_url($script_path)
	{
		$url = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');

		// Grab the arguments
		$args = substr($url, (strrpos($url, '/') + 1));

		// Store the current page
		self::$current_page = substr($url, 0, (strrpos($url, '/') + 1));
		self::$current_page = (self::$current_page[0] == '/') ? substr(self::$current_page, 1) : self::$current_page;
		self::$current_page = str_replace($script_path, '', self::$root_url) . self::$current_page;

		// Split up the arguments
		$args = explode(self::$separator, $args);

		foreach ($args as $arg)
		{
			$arg = explode('_', $arg, 2);

			if (sizeof($arg) == 1)
			{
				self::$params[] = $arg[0];
				$_GET[$arg[0]] = $_REQUEST[$arg[0]] = $arg[0];

				continue;
			}

			self::$params[$arg[0]] = $arg[1];
			$_GET[$arg[0]] = $_REQUEST[$arg[0]] = $arg[1];
		}
	}
}
