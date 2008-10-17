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
			'contrib_version' 	=> array('default' => 0, 'max' => 15),
			'faq_order_id' 		=> array('default' => 0),
			'faq_subject' 		=> array('default' => 0, 'max' => 255),
			'faq_text' 			=> array('default' => 0),
			'faq_text_bitfield'	=> array('default' => '', 'readonly' => true),
			'faq_text_uid'		=> array('default' => '', 'readonly' => true),
			'faq_text_options'	=> array('default' => 7, 'readonly' => true)
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
		}
	}

	/**
	 *  Creating list with similar FAQs
	 *
	 * @param int $faq_id
	 */ 
	public function get_similar_faqs($faq_id)
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
	 * Display FAQs list for selected contrib
	 *
	 * @param int $contrib_id
	 */
	public function get_faqs_list($contrib_id)
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

?>
