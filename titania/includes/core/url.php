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
	private $separator = '-';

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
			if ($name == '#')
			{
				$anchor = '#' . $value;
				continue;
			}

			if (substr($url, -1) != '/')
			{
				$url .= $this->separator;
			}

			$url .= $this->url_replace($name) . $this->separator . $this->url_replace($value);
		}

		// Now append the anchor again
		$url .= $anchor;

		return $url;
	}

	/**
	* URL Replace
	*
	* Replaces tags and other items that could break the URL's
	*/
	public function url_replace($url)
	{
		$match = array('#', '?', '/', '\\', '\'', '&amp;', '&lt;', '&gt;', '&quot;', ':', $this->separator);

		return urlencode(str_replace($match, '', $url));
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
		$args = explode($this->separator, $args);

		if (sizeof($args) < 2)
		{
			return;
		}

		// Go through all the arguments and put them in $_GET & $_REQUEST
		for ($i = 0; $i < sizeof($args); $i+=2)
		{
			$name = $args[$i];
			$value = $args[($i + 1)];

			$this->params[$name] = $value;
			$_GET[$name] = $_REQUEST[$name] = $value;
		}
	}
}
