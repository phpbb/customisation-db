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

/**
* authors_main
* Titania Authors and Maintainers
* @package authors
*/
class authors_details extends titania_object
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

		$user_id = request_var('u', 0);

		switch ($mode)
		{
			case 'details':
			default :
				$this->tpl_name = 'authors/author_details';
				$this->page_title = 'AUTHOR_DETAILS';

				$this->author_details($user_id);
			break;

		}
	}

	private function author_details($user_id)
	{
		titania::load_object('author');

		$author = new titania_author($user_id);

		if ($author->load() === false || !$author->author_visible)
		{
			trigger_error('AUTHOR_NOT_FOUND');
		}

		$author->get_rating();

		$author->assign_details();
	}
}