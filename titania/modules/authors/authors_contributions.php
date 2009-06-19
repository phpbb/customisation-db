<?php
/**
 *
 * @package titania
 * @version $Id$
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

class authors_contributions extends titania_object
{
	public $p_master;
	public $u_action;

	/**
	 * Constructor
	 */
	public function __construct($p_master)
	{
		$this->p_master = $p_master;
		$this->page = titania::$page;
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		titania::add_lang('authors');

		$user_id	= request_var('u', 0);

		$this->tpl_name = 'authors/author_contributions';
		$this->page_title = 'AUTHOR_CONTRIBUTIONS';

		display_contribs('author', $user_id);
	}
}