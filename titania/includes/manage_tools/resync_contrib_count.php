<?php
/**
*
* @package Titania
* @copyright (c) 2012 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

class resync_contrib_count
{
	/**
	* Tool overview page
	*/
	function display_options()
	{
		return 'RESYNC_CONTRIB_COUNT';
	}

	/**
	* Run the tool
	*/
	function run_tool()
	{
		// Define some vars that we'll need
		$start = request_var('start', 0);
		$limit = 100;

		$types = $defaults = array();
		$defaults['author_contribs'] = 0;
		$valid_statuses = array(TITANIA_CONTRIB_NEW, TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED);
		
		foreach (titania_types::$types as $id => $class)
		{
				$types[] = $id;
				
				if (isset($class->author_count))
				{
					$defaults[$class->author_count] = 0;
				}
		}

		if (!sizeof($types))
		{
			trigger_back('RESYNC_CONTRIB_COUNT_COMPLETE');
		}
		
		// Reset counts to 0
		if ($start == 0)
		{
			phpbb::$db->sql_query('UPDATE ' . TITANIA_CATEGORIES_TABLE . ' SET category_contribs = 0');
			phpbb::$db->sql_query('UPDATE ' . TITANIA_AUTHORS_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $defaults));
		}

		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE;
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();
			
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_type, c.contrib_status, c.contrib_user_id, ca.user_id',

			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE => 'c',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_CONTRIB_COAUTHORS_TABLE => 'ca'),
					'ON'	=> 'ca.contrib_id = c.contrib_id',
				),
			),

			'WHERE'		=> 'c.contrib_visible = 1 AND ' .  phpbb::$db->sql_in_set('c.contrib_status', $valid_statuses) . ' AND ' . phpbb::$db->sql_in_set('c.contrib_type', $types)
		);
		
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
					
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Require validation and status is new? We skip
			if (titania::$config->require_validation && titania_types::$types[$row['contrib_type']]->require_validation && $row['contrib_status'] == TITANIA_CONTRIB_NEW)
			{
				continue;
			}
			
			// Update category count	
			$contrib = new titania_contribution();
			$contrib->contrib_id = $row['contrib_id'];
			$contrib->contrib_status = $row['contrib_status'];
			$contrib->contrib_type = $row['contrib_type'];
			$contrib->update_category_count();
			
			$row['user_id'] = (int) (isset($row['user_id'])) ? $row['user_id'] : $row['contrib_user_id'];		
			$type_count = '';
			
			// Does the type have a field in the authors table for storing the type total?
			if (isset(titania_types::$types[$row['contrib_type']]->author_count))
			{
				$type_count = ', ' . titania_types::$types[$row['contrib_type']]->author_count . ' = ' . titania_types::$types[$row['contrib_type']]->author_count . '+ 1';
			}
			
			// Update user's count
			phpbb::$db->sql_query('UPDATE ' . TITANIA_AUTHORS_TABLE . ' SET author_contribs = author_contribs +1' . $type_count . ' WHERE user_id = ' . $row['user_id']);
		}
		phpbb::$db->sql_freeresult($result);

		if (($start + $limit) >= $total)
		{
			trigger_back('RESYNC_CONTRIB_COUNT_COMPLETE');
		}
		else
		{
			meta_refresh(0, titania_url::build_url('manage/administration', array('t' => 'resync_contrib_count', 'start' => ($start + $limit), 'mode' => 'authors', 'submit' => 1, 'hash' => generate_link_hash('manage'))));
			trigger_error(phpbb::$user->lang('RESYNC_CONTRIB_COUNT_PROGRESS', ($start + $limit), $total));
		}
	}
}