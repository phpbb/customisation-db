<?php
/**
 *
 * @package titania
 * @version $Id: mods_faq.php 122 2008-11-07 20:20:10Z daro $
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

		$user->add_lang(array('titania_faq'));

		$faq_id		= request_var('faq_id', 0);
		$submit		= isset($_POST['submit']) ? true : false;
		
		$form_key = 'mods_details';
		add_form_key($form_key);

		require(TITANIA_ROOT . 'includes/class_faq.' . PHP_EXT);
		$faq = new titania_faq($faq_id);

		switch ($mode)
		{
			case 'add':
			case 'edit':
				if ($submit)
				{
					$subject 	= utf8_normalize_nfc(request_var('subject', '', true));
					$text 		= utf8_normalize_nfc(request_var('text', '', true));
					
					$faq->submit();
				}
				
				if ($mode == 'edit')
				{
					$faq->load();
				}
				
				$template->assign_vars(array(
					'U_ACTION'		=> '',
					
					'FAQ_SUBJECT'	=> $faq->faq_subject,
				));
			break;

			case 'delete':
				if (confirm_box(true))
				{
					$faq->delete();
				}
				else
				{
					$s_hidden_fields = build_hidden_fields(array(
						'submit'	=> true,
						'faq_id'	=> $faq_id
					));
					confirm_box(false, 'DELETE_FAQ', $s_hidden_fields);
				}	
			break;

			case 'details':
			
			break;
			
			case 'list':
			default:
				$this->tpl_name = 'mods/mod_faq';
				$this->page_title = 'MODS_FAQ_LIST';
				
				$this->faq_list(request_var('contrib_id', 0));
			break;
		}		
	}

	private function faq_details($faq_id)
	{
		// $this->load();
	}
	
	/**
	 *  Creating list with similar FAQ
	 *
	 * @param int $faq_id
	 */ 
	private function get_similar_faq($faq_id)
	{
		global $db;
		
		$sql = 'SELECT faq_id, faq_subject
			FROM ' . $this->sql_table . '
			WHERE parent_id = ' . (int) $faq_id;
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('similarfaqs', array(
				'U_FAQ'		=> append_sid(TITANIA_ROOT . "mods/index.$phpEx", 'mode=faq&amp;action=details&amp;faq_id=' . $row['faq_id']),
				'SUBJECT'	=> $row['faq_subject']
			));
		}
	}

	/**
	 * Display FAQ list for selected contrib
	 *
	 * @param int $contrib_id
	 */
	private function faq_list($contrib_id)
	{
		global $db, $template;

		if (!class_exists('sort'))
		{
			include(TITANIA_ROOT . 'includes/class_sort.' . PHP_EXT);
		}
			
		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);
		}
		
		$sort = new sort();
		
		$sort->set_sort_keys(array(
			array('SORT_CONTRIB_VERSION',		'f.contrib_version', 'default' => true),
			array('SORT_SUBJECT',				'f.fat_subject'),
		));

		$sort->sort_request(false);		
		
		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();
		
		// select the list of faqs for this contrib
		$sql_array = array(
			'SELECT'	=> 'f.faq_id, f.contrib_version, f.faq_subject',
			'FROM'		=> array(
				$this->sql_table => 'f'
			),
			'WHERE'		=> 'f.contrib_id = ' . $contrib_id,
			'ORDER_BY'	=> $sort->get_order_by()
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $limit, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('faqs', array(
				'U_FAQ'				=> append_sid(TITANIA_ROOT . "mods/index.$phpEx", 'mode=faq&amp;action=details&amp;faq_id=' . $row['faq_id']),
				'CONTRIB_VERSION'	=> $row['contrib_version'],
				'SUBJECT'			=> $row['faq_subject'],
			));			
		}
		$db->sql_freeresult($result);
		
		$pagination->sql_total_count($sql_ary, 'f.faq_id');
		
		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(),
			'sd'	=> $sort->get_sort_dir(),
		));
		
		// Build a pagination
		$pagination->build_pagination($this->page);
		
		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}
}
