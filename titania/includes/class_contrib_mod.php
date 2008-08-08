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

if (!class_exists('titania_contribution'))
{
	require(TITANIA_ROOT . 'includes/class_contrib.' . PHP_EXT);
}

/**
* Class abstracting modifications.
* @package Titania
*/
class titania_modification extends titania_contribution
{
	/**
	 * Constructor for titania modification
	 *
	 * @param int $contrib_id
	 */
	public function __construct($contrib_id = false)
	{
		// Delegate ...
		parent::__construct($contrib_id);

		// ... and overwrite
		$this->object_config = array_merge($this->object_config, array(
			'contrib_type' => array('default' => MOD),
		));
	}
}