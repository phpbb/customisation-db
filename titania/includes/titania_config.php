<?php
/**
*
* @package Titania
* @version $Id$
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

if (!class_exists('titania_object'))
{
	require(TITANIA_ROOT . 'includes/class_base_object.' . PHP_EXT);
}

/**
* Titania configuration
*
* @package Titania
*/
class titania_config extends titania_object
{

	/**
	 * Setup default configuration and merge with the contents of $titania_config (array) from config.php
	 */
	function __construct($config_array)
	{
		$this->object_config = array_merge($this->object_config, array(
			'phpbbcom_profile'			=> array('default' => 1),
		));

		foreach ($config_array as $key => $val)
		{
			$this->$key = $val;
		}
	}

	public function get_config_data()
	{
		return $this;
	}
}

?>