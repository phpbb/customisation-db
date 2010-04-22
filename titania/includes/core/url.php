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
	* Current page we are on (built with self::$current_page and self::$params)
	*
	* @var string
	*/
	public static $current_page_url;

	/**
	* Build URL by appending the needed parameters to a base URL
	*
	* @param string $base The base URL, Ex: customisation/mod/
	* @param array $params Array of parameters we need to clean and append to the base url
	* @return string
	*/
	public static function build_url($base, $params = array())
	{
		if (!empty(titania::$hook) && titania::$hook->call_hook(array(__CLASS__, __FUNCTION__), $base, $params))
		{
			if (titania::$hook->hook_return(array(__CLASS__, __FUNCTION__)))
			{
				return titania::$hook->hook_return_result(array(__CLASS__, __FUNCTION__));
			}
		}

		// Prevent rebuilding...
		if (self::is_built($base))
		{
			return self::append_url($base, $params);
		}

		// URL Encode the base
		$base = explode('/', $base);
		$base = array_map('urlencode', $base);
		$base = implode('/', $base);

		// Add a slash to the end if we do not have one
		if (substr($base, -1) != '/')
		{
			$base .= '/';
		}

		// Start building the final URL
		$final_url = self::$root_url . $base;

		// Add the Session ID if required.
		global $_SID;
		if ($_SID)
		{
			if (!is_array($params))
			{
				$params = self::split_params($params);
			}

			$params['sid'] = $_SID;
		}

		// Use the append_url function to add the parameters and return
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
		if (!is_array($params))
		{
			$params = self::split_params($params);
		}

		if (!empty(titania::$hook) && titania::$hook->call_hook(array(__CLASS__, __FUNCTION__), $url, $params))
		{
			if (titania::$hook->hook_return(array(__CLASS__, __FUNCTION__)))
			{
				return titania::$hook->hook_return_result(array(__CLASS__, __FUNCTION__));
			}
		}

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

			// Does this field already exist in the url?  If so replace it
			if (strpos(substr($url, strrpos($url, '/')), self::$separator . $name . '_') !== false)
			{
				$url = substr($url, 0, strrpos($url, '/')) . preg_replace('#' . self::$separator . $name . '_[^' . self::$separator . ']+' . self::$separator . '?#', '', substr($url, strrpos($url, '/')));
			}
			else if (strpos(substr($url, strrpos($url, '/')), '/' . $name . '_') !== false)
			{
				$url = substr($url, 0, strrpos($url, '/')) . preg_replace('#/' . $name . '_[^' . self::$separator . ']+' . self::$separator . '?#', '/', substr($url, strrpos($url, '/')));
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
	* Unbuild a url (used for the indexer)
	*
	* @param mixed $url
	*/
	public static function unbuild_url($url)
	{
		// Remove the root url
		$url = str_replace(self::$root_url, '', $url);

		// Replace SID
		$url = preg_replace('#sid' . self::$separator . '[a-z0-9]+#', '', $url);

		return $url;
	}

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
	public function split_params($params)
	{
		$new_params = array();

		if (strpos($params, '#') !== false)
		{
			$new_params['#'] = substr($params, (strpos($params, '#') + 1));
			$params = substr($params, 0, strpos($params, '#'));
		}

		foreach (explode(self::$separator, $params) as $section)
		{
			$parts = explode('_', $section, 2);
			if (sizeof($parts) == 2)
			{
				$new_params[$parts[0]] = $parts[1];
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
		$match = array('+', '#', '?', '/', '\\', '\'', '&amp;', '&lt;', '&gt;', '&quot;', ':', self::$separator);
		$url = str_replace($match, '', $url);

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
		foreach (self::split_params($args) as $name => $value)
		{
			self::$params[$name] = $value;
			$_GET[$name] = $_REQUEST[$name] = $value;
		}

		// Build the full current page url
		self::$current_page_url = self::build_url(self::$current_page, self::$params);
	}
}
