<?php
/**
*
* @package Titania
* @version $Id: $
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
	protected $sql_table		= CUSTOMISATION_MOD_FAQ_TABLE;

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
			'faq_mod_version' 	=> array('default' => 0, 'max' => 15),
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
	 * Display faq for MOD
	 *
	 * @param int $contrib_id
	 */
	public function display($contrib_id)
	{
		global $db, $template;
	
		$sql = 'SELECT *
			FROM ' . $this->sql_table . '
			WHERE contrib_id = ' . (int) $contrib_id;
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrowset($result))
		{
			$template->assign_block_vars('faq', array(
				'ID'			=> $row['faq_id'],
				'MOD_VERSION'	=> $row['faq_version'],
				'SUBJECT'		=> $row['faq_subject'],
				'TEXT'			=> $row['faq_text']
			));
			
			$exists = true;
		}
		$db->sql_freeresult($result);

		if ($exists !== true)
		{
			$template->assign_var('S_NO_FAQ', true);
		}
	}
}

?>