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

namespace phpbb\titania\url;

class url
{
	protected static $separator = '-';
	protected static $separator_replacement = '%96';

	/** @var array */
	protected $params = array();

	/** @var string */
	protected $route = '';

	/** @var string */
	protected $route_prefix = 'phpbb.titania.';

	/**
	* Set route prefix.
	*
	* @param string $prefix
	* @return \phpbb\titania\url\url
	*/
	public function set_route_prefix($prefix)
	{
		$this->route_prefix = $prefix;
		return $this;
	}

	/**
	* Set route.
	*
	* @param string $route
	* @return \phpbb\titania\url\url
	*/
	public function set_route($route)
	{
		$this->route = $this->route_prefix . $route;
		return $this;
	}

	/**
	* Get route.
	*
	* @return string
	*/
	public function get_route()
	{
		return $this->route;
	}

	/**
	* Append string to existing route.
	*
	* @param string $appendage
	* @return \phpbb\titania\url\url
	*/
	public function append_route($appendage)
	{
		$this->route .= ".$appendage";
		return $this;
	}

	/**
	* Set parameters.
	*
	* @param array $params
	* @return \phpbb\titania\url\url
	*/
	public function set_params(array $params)
	{
		$this->params = $params;
		return $this;
	}

	/**
	* Get parameters.
	*
	* @return array
	*/
	public function get_params()
	{
		return $this->params;
	}

	/**
	* Add parameters to existing collection.
	*
	* @param array $params
	* @return \phpbb\titania\url\url
	*/
	public function add_params(array $params)
	{
		$this->params = array_merge($this->params, $params);
		return $this;
	}

	/**
	* Check whether given parameter is set.
	*
	* @param string $param
	* @return bool
	*/
	public function has_param($param)
	{
		return isset($this->params[$param]);
	}

	/**
	* Check whether the given value equals the parameter's value.
	*
	* @param string $param
	* @param mixed $value
	* @return bool
	*/
	public function param_equals($param, $value)
	{
		return $this->has_param($param) && $this->params[$param] === $value;
	}

	/**
	* Set parameter.
	*
	* @param string $param
	* @param mixed $value
	* @return \phpbb\titania\url\url
	*/
	public function set_param($param, $value)
	{
		$this->params[$param] = $value;
		return $this;
	}

	/**
	* Rename parameter.
	*
	* @param string $old 	Old name
	* @param string $new 	New name
	* @return \phpbb\titania\url\url
	*/
	public function rename_param($old, $new)
	{
		if (isset($this->params[$old]))
		{
			$this->params[$new] = $this->params[$old];
			unset($this->params[$old]);
		}
		return $this;
	}

	/**
	* Remove parameter.
	*
	* @param string $param
	* @return \phpbb\titania\url\url
	*/
	public function remove_param($param)
	{
		unset($this->params[$param]);
		return $this;
	}

	/**
	* Remove nth parameter.
	*
	* @param int $nth
	* @return \phpbb\titania\url\url
	*/
	public function remove_nth_param($nth)
	{
		array_splice($this->params, $nth - 1, 1);
		return $this;
	}

	/**
	 * Create a safe string for the URLs
	 *
	 * @param string $string
	 * @return string
	 */
	public static function generate_slug($string)
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
