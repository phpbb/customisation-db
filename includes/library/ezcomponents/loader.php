<?php
/**
*
* @package ezcomponents
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* eZ components class loader
* Replaces the autoload mechanism eZ Components normally use
* @package ezcomponents
*/
class phpbb_ezcomponents_loader
{
	private static $loaded = array();

	/**
	* Loads all classes of a particular component.
	* The base component is always loaded first.
	*
	* @param	$component	string	Lower case component name
	*/
	public static function load_component($component)
	{
		// don't allow loading the same component twice
		if (isset(self::$loaded[$component]) && self::$loaded[$component])
		{
			return;
		}
		self::$loaded[$component] = true;

		// make sure base is always loaded first
		if ($component != 'base' && !isset(self::$loaded['base']))
		{
			self::load_component('base');
		}

		$ezc_path = TITANIA_ROOT . 'includes/library/ezcomponents/';

		// retrieve the autoload list
		$classes = include($ezc_path . ucfirst($component) . '/' . $component . '_autoload.php');

		// include all files related to this component
		foreach ($classes as $class => $path)
		{
			include($ezc_path . $path);
		}
	}
}