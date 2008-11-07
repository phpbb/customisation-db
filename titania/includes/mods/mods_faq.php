<?php
/**
 *
 * @package titania
 * @version $Id: mods_faq.php 122 2008-11-07 20:20:10Z daroPL $
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* faq_main
* Class for FAQ module
* @package mods
*/
class mods_faq extends titania_object
{
	public $p_master;
	public $u_action;

	/**
	 * Constructor
	 */
	public function __construct(&$p_master)
	{
		global $user;

		$this->p_master = &$p_master;

		$this->page = $user->page['script_path'] . $user->page['page_name'];
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $user, $template, $cache;

		$user->add_lang(array('titania_faq'));

		$faq_id		= request_var('faq_id', 0);
		$submit		= isset($_POST['submit']) ? true : false;
	}

	/**
	 * create a faq list for specific contrib
	 *
	 * @param int $contrib_id
	 */
	private function faq_list($contrib_id)
	{
		
	}
}
