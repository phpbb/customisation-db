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
	public $root_url;

	/**
	* Parameters pulled from the current URL the user is accessing
	*
	* @var array
	*/
	public $params = array();

	/**
	* Current page we are on (minus all the parameters)
	*
	* @var string
	*/
	public $current_page;

	/**
	* Build URL by appending the needed parameters to a base URL
	*
	* @param string $base The base URL, Ex: customisation/mod/
	* @param array $params Array of parameters we need to clean and append to the base url
	* @return string
	*/
	public function build_url($base, $params = array())
	{
		global $_SID;

		$final_url = $this->root_url . $base;

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

		return $this->append_url($final_url, $params);
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
	public function append_url($url, $params = array())
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
				$url .= $this->url_replace($value);
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

			$url .= $this->url_replace($name) . self::$separator . $this->url_replace($value);
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
	public function decode_url()
	{
		$url = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');

		// Remove everything before the last /
		$args = substr($url, (strrpos($url, '/') + 1));

		// Store the current page
		$this->current_page = substr($url, 0, (strrpos($url, '/') + 1));

		// Split up the arguments
		$args = explode(self::$separator, $args);

		if (sizeof($args) < 2)
		{
			return;
		}

		/**
		* Go through all the arguments and put them in $_GET & $_REQUEST
		*
		* Going through all of them and setting $x to the value of $y (the next value in the args array) seems to be the safest way to make sure we get them all
		*	if we don't do this then urls like topic_title-t-1 would break because topic_title = t and 1 is ignored, this way topic_title = t and t = 1, so we should be good
		*/
		for ($i = 0; $i < (sizeof($args) - 1); $i++)
		{
			$name = $args[$i];
			$value = $args[($i + 1)];

			$this->params[$name] = $value;
			$_GET[$name] = $_REQUEST[$name] = $value;
		}
	}
}
