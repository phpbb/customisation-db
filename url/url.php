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
}
