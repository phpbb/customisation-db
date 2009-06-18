<?php
/**
 *
 * @package titania
 * @version $Id: mods_faq.php 215 2009-06-13 20:40:08Z exreaction $
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
				
				$faq->delete_faq();
				
			break;
					
			case 'manage':
			
				$this->page_title = 'FAQ_MANAGEMENT_LIST';
						
				$faq->management_list();

			break;
			
			case 'details':
			
				$this->page_title = 'FAQ_DETAILS';
				
				$found = $faq->faq_details();
				
				if (!$found)
				{
					trigger_error('FAQ_NOT_FOUND');
				}
				
			default:

				$this->page_title = 'FAQ_LIST';
				
				$faq->faq_list();
				
			break;
		}
	}

}
