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
class contribs_faq extends titania_object
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
		titania::add_lang(array('faq', 'contributions'));

		$faq_id		= request_var('f', 0);
		$contrib_id	= request_var('c', 0);
		$action 	= request_var('action', '');
		$submit		= isset($_POST['submit']) ? true : false;

		add_form_key('mods_faq');

		$faq = new titania_faq($faq_id, $contrib_id);

		phpbb::$template->assign_vars(array(
			'CONTRIB_NAME'		=> $faq->contrib_data['contrib_name'],
		));
		
		$this->tpl_name = 'contributions/contribution_faq';
		
		if ($mode != 'faq')
		{
			$action = $mode;
		}
		
		switch ($action)
		{
			case 'create':
			case 'edit':
				
				if ($submit && !check_form_key('mods_faq'))
				{
					trigger_error('INVALID_FORM');
				}
		
				$this->page_title = ($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ';
				
				$faq->submit_faq($action);
				
			break;
			
			case 'delete':
				
				$this->page_title = 'DELETE_FAQ';
				
				if (!phpbb::$auth->acl_get('titania_faq_mod') && !phpbb::$auth->acl_get('titania_faq_delete') && phpbb::$user->data['user_id'] != $this->contrib_data['contrib_user_id'])
				{
					return;
				}

				if (confirm_box(true))
				{
					$faq->delete();

					titania::error_box('SUCCESS', 'FAQ_DELETED', TITANIA_SUCCESS);
					$this->main($id, 'list');					
				}
				else
				{
					confirm_box(false, 'DELETE_FAQ', build_hidden_fields(array(
						'mode'		=> 'faq',
						'action'	=> 'delete',
						'c'		=> $contrib_id,
						'f'		=> $faq_id,
					)));
				}
				
				redirect(titania_sid('contributions/index', "mode=faq&amp;c=$contrib_id"));
				
			break;
			
			case 'details':
			
				$this->page_title = 'FAQ_DETAILS';
				
				$found = $faq->faq_details();
				
				if (!$found)
				{
					trigger_error('FAQ_NOT_FOUND');
				}
			
			case 'list':
			default:

				$this->page_title = 'FAQ_LIST';
				
				$faq->faq_list();
				
			break;
		}
	}

}
