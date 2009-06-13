<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 Customisation Database Team
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

require(TITANIA_ROOT . 'includes/objects/faq.' . PHP_EXT);

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
	public function __construct($p_master)
	{
		global $user;

		$this->p_master = $p_master;

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

		titania::add_lang(array('contrib', 'contrib_mod'));

		$faq_id		= request_var('faq', 0);
		$action 	= request_var('action', '');

		$submit		= isset($_POST['submit']) ? true : false;

		add_form_key('mods_faq');

		if ($submit && !check_form_key('mods_faq'))
		{
			trigger_error('INVALID_FORM');
		}

		$faq = new titania_faq($faq_id);

		$this->tpl_name = 'faq/faq_manage';

		switch ($mode)
		{
			case 'main':

			break;

			case 'manage':

				$faq_ids = request_var('faq_id', array(0));

				if ($submit && $faq_ids)
				{
					switch ($action)
					{
						case 'delete':
							$sql = 'DELETE FROM ' . TITANIA_CONTRIB_FAQ_TABLE . ' WHERE ' . $db->sql_in_set('faq_id', $faq_ids);
							$db->sql_query($sql);

							$message = $user->lang['DELETE_FAQ_MARKED'];
						break;

						case 'move':

						break;
					}

					$faq->manage_list();
				}

			break;

			case 'view':
			default:

				$mod_id = request_var('mod', 0);

				if ($action && in_array($action, array('create', 'edit')))
				{
					if (!$mod_id)
					{
						trigger_error('NO_MOD_SELECTED');
					}

					$this->page_title = ($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ';

					$faq->submit_faq($mod_id, $action);
				}
				else
				{
					if ($faq_id)
					{
						$this->tpl_name 	= 'faq/faq_details';
						$this->page_title 	= 'MOD_FAQ_DETAILS';

						$found = $faq->faq_details();

						if (!$found)
						{
							trigger_error('FAQ_NOT_FOUND');
						}
					}
					else
					{
						if (!$mod_id)
						{
							trigger_error('NO_MOD_SELECTED');
						}

						$this->tpl_name 	= 'faq/faq_list';
						$this->page_title 	= 'MOD_FAQ_LIST';

						$found = $faq->faq_list($mod_id);

						if (!$found)
						{
							trigger_error('NO_FAQ_FOUND');
						}
					}
				}

			break;
		}
	}

}
