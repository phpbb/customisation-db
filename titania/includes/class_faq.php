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
	 * Constructor class for titania authors
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
			'faq_version' 		=> array('default' => 0, 'max' => 15),
			'faq_order_id' 		=> array('default' => 0),
			'faq_subject' 		=> array('default' => 0, 'max' => 255),
			'faq_text' 			=> array('default' => 0)
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
		}
	}

	/**
	 * Submit data for storing into the database
	 *
	 * @return void
	 */
	public function submit()
	{
		parent::submit();
	}

	/**
	 * Remove data from database
	 *
	 * @return void
	 */	
	public function delete()
	{
		parent::delete();
	}

	/**
	 * Display FAQs for specific contrib
	 *
	 * @param int $contrib_id
	 */
	public function display($contrib_id = 0)
	{
		global $db, $template;
	
		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);
		}
		
		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		// Select number of total FAQs for this contrib
		$sql = 'SELECT COUNT(faq_id) as total_count
			FROM ' . $this->sql_table . '
			WHERE contrib_id = ' . $contrib_id;
		$sql = $db->sql_query($sql);	
		$total_results = $db->sql_fetchfield('total_count');
		$db->sql_freeresult($result);
		
		// Set number of total records
		$pagination->set_total_results($total_results);
		
		// Select the list of FAQs
		$sql = 'SELECT faq_id, faq_version, faq_subject, faq_text
			FROM ' . $this->sql_table . '
			WHERE contrib_id = ' . $contrib_id;
		$sql = $db->sql_query($sql);
		$result = $db->sql_query_limit($sql, $limit, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('faq', array(
				'ID'			=> $row['faq_id'],
				'VERSION'		=> $row['faq_version'],
				'SUBJECT'		=> $row['faq_subject'],
				'TEXT'			=> $row['faq_text'],
			));			
		}
		$db->sql_freeresult($result);
		
		$pagination->build_pagination($this->page);
	}
}

?>