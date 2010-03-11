<?php
/**
*
* @package Titania
* @version $Id$
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

class attention_overlord
{
	// Attention items
	public static $attention_items = array();

	// Sort By items
	public static $sort_by = array(
		'a' => array('AUTHOR', 'a.attention_requester'),
		't' => array('POST_TIME', 'a.attention_time', true),
		's' => array('TYPE', 'a.attention_type'),
	);

	/**
	* Load an attention item by attention_id or the type/id
	*
	* @param mixed $attention_id
	* @param mixed $object_type
	* @param mixed $object_id
	*/
	public static function load_attention($attention_id, $object_type = false, $object_id = false)
	{
		$sql = 'SELECT * FROM ' . TITANIA_ATTENTION_TABLE . '
			WHERE ';

		if ($attention_id)
		{
			if (isset(self::$attention_items[$attention_id]))
			{
				return self::$attention_items[$attention_id];
			}

			$sql .= 'attention_id = ' . (int) $attention_id;
		}
		else
		{
			$sql .= 'attention_object_type = ' . (int) $object_type . ' AND attention_object_id = ' . (int) $object_id;
		}

		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			return false;
		}

		self::$attention_items[$row['attention_id']] = $row;

		return $row;
	}

	public static function display_attention_list($type = false, $display_closed = false, $sort = false, $pagination = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_sort_keys(self::$sort_by);
			if (isset(self::$sort_by[phpbb::$user->data['user_topic_sortby_type']]))
			{
				$sort->default_key = phpbb::$user->data['user_topic_sortby_type'];
			}
		}

		if ($pagination === false)
		{
			// Setup the pagination tool
			$pagination = new titania_pagination();
			$pagination->default_limit = phpbb::$config['topics_per_page'];
			$pagination->request();
		}

		$sql_ary = array(
			'SELECT'	=> '*',

			'FROM'		=> array(
				TITANIA_ATTENTION_TABLE	=> 'a',
			),

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		// Limit to certain types if requested
		if ($type)
		{
			$sql_ary['WHERE'] .= 'a.attention_type = ' . (int) $type;
		}

		// Do we want the closed ones?
		if (!$display_closed)
		{
			if (!isset($sql_ary['WHERE']))
			{
				$sql_ary['WHERE'] = '';
			}
			else
			{
				$sql_ary['WHERE'] .= ' AND ';
			}

			$sql_ary['WHERE'] .= 'a.attention_close_time = 0';
		}

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		$pagination->sql_count($sql_ary, 'a.attention_id');
		$pagination->build_pagination(titania_url::$current_page, titania_url::$params);

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $pagination->limit, $pagination->start);

		$attention_ids = $user_ids = array();

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$attention_ids[] = $row['attention_id'];
			$user_ids[] = $row['attention_requester'];

			if ($row['attention_close_user'])
			{
				$user_ids[] = $row['attention_close_user'];
			}

			self::$attention_items[$row['attention_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		// Grab some users
		users_overlord::load_users($user_ids);

		// Output time
		$attention = new titania_attention;
		foreach ($attention_ids as $attention_id)
		{
			$row = self::$attention_items[$attention_id];

			$attention->__set_array($row);

			$output = array_merge(
				$attention->assign_details(true),
				users_overlord::assign_details($row['attention_requester'])
			);

			// Do we have to?
			if ($row['attention_close_user'])
			{
				$output = array_merge(
					$output,
					users_overlord::assign_details($row['attention_close_user'], 'CLOSE_')
				);
			}

			phpbb::$template->assign_block_vars('attention', $output);
		}
		unset($attention);
	}
}
