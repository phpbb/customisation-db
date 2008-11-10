<?php
/**
*
* @package Titania
* @version $Id: class_faq.php 49 2008-06-29 23:03:16Z HighwayofLife $
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

if (!class_exists('titania_database_object'))
{
	require(TITANIA_ROOT . 'includes/class_base_db_object.' . PHP_EXT);
}

/**
* Class to titania faq.
* @package Titania
*/
class titania_faq extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_CONTRIB_FAQ_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'faq_id';
	
	/**
	 * Constructor class for titania faq
	 *
	 * @param int $faq_id
	 */
	public function __construct($faq_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'faq_id'			=> array('default' => 0),
			'contrib_id' 		=> array('default' => 0),
			'parent_id' 		=> array('default' => 0),
			'contrib_version' 	=> array('default' => '', 'max' => 15),
			'faq_order_id' 		=> array('default' => 0),
			'faq_subject' 		=> array('default' => '', 'max' => 255),
			'faq_text' 			=> array('default' => ''),
			'faq_text_bitfield'	=> array('default' => '', 'readonly' => true),
			'faq_text_uid'		=> array('default' => '', 'readonly' => true),
			'faq_text_options'	=> array('default' => 7, 'readonly' => true)
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
		}
	}
	
	/*
	 * faq details
	 *
     * @param int $faq_id
	 */
	public function faq_details($contrib_type)
	{
		global $template, $titania;
		
		$this->load();

		if (!$this->faq_id)
		{
			titania::trigger_error('FAQ_DETAILS_NOT_FOUND');
		}		

		decode_message($this->faq_text, $this->faq_text_uid);
		
		$template->assign_vars(array(
			'FAQ_ID'			=> $this->faq_id,
			'FAQ_SUBJECT'		=> $this->faq_subject,
			'FAQ_TEXT'			=> $this->faq_text,
			'CONTRIB_VERSION' 	=> $this->contrib_version,

			'U_OTHERS_FAQ'		=> append_sid($titania->page, 'id=faq&amp;mode=view&amp;' . $contrib_type . '=' . $this->contrib_id),
		));
	}
	
	/**
	 * Creating list with similar FAQ
	 *
	 * @param int $faq_id
	 */ 
	public function similar_faq()
	{
		global $db, $template;
		
		$sql_ary = array(
			'SELECT'	=> 'f.faq_id, f.faq_subject',
			'FROM'		=> array(
				CUSTOMISATION_CONTRIB_FAQ_TABLE => 'f',
			),
			'WHERE'		=> 'f.parent_id = ' . $this->faq_id,
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_ary);		
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('similarfaq', array(
				'U_FAQ'		=> append_sid($titania->page, 'id=faq&amp;mode=view&amp;faq_id=' . $row['faq_id']),
	
				'SUBJECT'	=> $row['faq_subject']
			));
		}
	}
	
	/**
	 * Display FAQ list for selected contrib
	 *
	 * @param int $contrib_id
	 */
	public function faq_list($contrib_id, $contrib_type)
	{
		global $db, $template, $titania;

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
			array('SORT_SUBJECT',				'f.faq_subject'),
		));

		$sort->sort_request(false);		
		
		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();
		
		// select the list of faqs for this contrib
		$sql_ary = array(
			'SELECT'	=> 'f.faq_id, f.contrib_version, f.faq_subject',
			'FROM'		=> array(
				CUSTOMISATION_CONTRIB_FAQ_TABLE => 'f',
			),
			'WHERE'		=> 'f.contrib_id = ' . $contrib_id,
			'ORDER_BY'	=> $sort->get_order_by()
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		$results = 0;
		
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('faq', array(
				'U_FAQ'				=> append_sid($titania->page, 'id=faq&amp;mode=view&amp;faq_id=' . $row['faq_id']),
				'CONTRIB_VERSION'	=> $row['contrib_version'],
				'SUBJECT'			=> $row['faq_subject'],
			));
			
			$results++;
		}
		$db->sql_freeresult($result);
		
		if (!$results)
		{
			return false;
		}
		
		$pagination->sql_total_count($sql_ary, 'f.faq_id', $results);
		
		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(false),
			'sd'	=> $sort->get_sort_dir(false),
		));
		
		// Build a pagination
		$pagination->build_pagination(append_sid($titania->page, 'id=faq&amp;mode=view&amp;' . $contrib_type . '=' . $contrib_id));
		
		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
		
		return true;
	}
}

?>