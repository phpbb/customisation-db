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

		// Parameters must be an array to modify them
		if (!is_array($params))
		{
			$params = self::split_params($params);
		}

		// Handle the session id
		global $_SID;
		if ($_SID)
		{
			$params['sid'] = $_SID;
		}
		else
		{
			// Don't include the sid in the URL if not required
			unset($params['sid']);
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

		// Start building the final URL
		$final_url = self::$root_url . $base;

		// Add a slash to the end if we do not have one
		if (substr($final_url, -1) != '/')
		{
			$final_url .= '/';
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
			if (!is_array($value) && !trim($value))
			{
				continue;
			}

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
				$url = substr($url, 0, strrpos($url, '/')) . preg_replace('#' . self::$separator . $name . '_[^' . self::$separator . ']+#', '', substr($url, strrpos($url, '/')));
			}
			else if (strpos(substr($url, strrpos($url, '/')), '/' . $name . '_') !== false)
			{
				$url = substr($url, 0, strrpos($url, '/')) . preg_replace('#/' . $name . '_[^' . self::$separator . ']+#', '/', substr($url, strrpos($url, '/')));
			}

			// Specify the value as *destroy* to make sure the value isn't kept in the URL (old values are unset and new is not appended)
			if ($value != '*destroy*')
			{
				if (is_array($value))
				{
					foreach ($value as $val)
					{
						if (substr($url, -1) != '/')
						{
							$url .= self::$separator;
						}

						$url .= self::url_replace($name) . '[]_' . self::url_replace($val);
					}
				}
				else
				{
					if (substr($url, -1) != '/')
					{
						$url .= self::$separator;
					}

					$url .= self::url_replace($name) . '_' . self::url_replace($value);
				}
			}
		}

		// Now append the anchor again
		$url .= $anchor;

		return $url;
	}

	/**
	* Build a "clean" url (gets the built URL and then removes the SID)
	*/
	public static function build_clean_url($base, $params = array())
	{
		$url = self::build_url($base, $params);

		// Replace SID
		$url = self::remove_sid($url);

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
		$url = self::remove_sid($url);

		// Decode the URL (it'll be recoded again later)
		$url = urldecode($url);

		return $url;
	}

	/**
	* Remove the SID from the url
	*
	* @param mixed $url
	* @return mixed
	*/
	public static function remove_sid($url)
	{
		return preg_replace('#sid_[a-z0-9]+#', '', $url);
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

	/**
	* Decode the url request we are currently on and put the data in $_REQUEST/$_GET
	*
	* This function should be called before phpBB is initialized
	*/
	public static function decode_request()
	{
		$url = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');

		// Grab the arguments
		$args = substr($url, (strrpos($url, '/') + 1));

		// Split up the arguments
		foreach (self::split_params($args) as $name => $value)
		{
			$name = urldecode($name);

			self::$params[$name] = (!is_array($value)) ? urldecode(str_replace(self::$separator_replacement, self::$separator, $value)) : $value;
		}
		// Style cannot ever be allowed as a URL parameter because it is used by phpBB to request a custom board style
		unset(self::$params['style']);

		// Merge the parameters into the get/request superglobals.  Merge them to prevent a parameter in the parameters part of the URL from over-writting one that is already in here
		$_GET = array_merge(self::$params, $_GET);
		$_REQUEST = array_merge(self::$params, $_REQUEST);
	}

	/**
	* Decode the url to build the current page/current page url
	*/
	public static function decode_url($script_path)
	{
		$url = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');

		// Store the current page
		self::$current_page = substr($url, 0, (strrpos($url, '/') + 1));
		self::$current_page = (self::$current_page[0] == '/') ? substr(self::$current_page, 1) : self::$current_page;
		self::$current_page = str_replace($script_path, '', self::$root_url) . self::$current_page;

		// Build the full current page url
		self::$current_page_url = self::build_url(self::$current_page, self::$params);
	}
}
