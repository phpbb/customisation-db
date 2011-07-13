<?php
/**
*
* @package Titania
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

	/**
	* Display the list of attention items
	*
	* @param array $options
	* 	attention_type
	* 	attention_object_id
	* 	only_closed bool only display closed items
	* 	display_closed bool display closed and open items
	* 	template_block string the name of the template block to output to (attention if not sent)
	* @param titania_sort $sort
	*/
	public static function display_attention_list($options = array(), $sort = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = self::build_sort();
		}
		$sort->request();


		$sql_ary = array(
			'SELECT'	=> '*',

			'FROM'		=> array(
				TITANIA_ATTENTION_TABLE	=> 'a',
			),

			'WHERE'		=> array(),

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		// Limit to certain types if requested
		if (isset($options['attention_type']) && $options['attention_type'])
		{
			$sql_ary['WHERE'][] = 'a.attention_type = ' . (int) $options['attention_type'];
		}

		// Limit to certain item if requested
		if (isset($options['attention_object_id']))
		{
			$sql_ary['WHERE'][] = 'a.attention_object_id = ' . (int) $options['attention_object_id'];
		}

		// Do we want the closed ones?
		if (isset($options['only_closed']) && $options['only_closed'])
		{
			$sql_ary['WHERE'][] = 'a.attention_close_time <> 0';
		}
		else if (!isset($options['display_closed']) || $options['display_closed'] == false)
		{
			$sql_ary['WHERE'][] = 'a.attention_close_time = 0';
		}

		$sql_ary['WHERE'] = implode(' AND ', $sql_ary['WHERE']);

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if (!$sort->sql_count($sql_ary, 'a.attention_id'))
		{
			// No results...no need to query more...
			return;
		}

		$sort->build_pagination(titania_url::$current_page, titania_url::$params);

		// Get the data
		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

		$attention_ids = $user_ids = array();

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$attention_ids[] = $row['attention_id'];
			$user_ids[] = $row['attention_poster_id'];
			$user_ids[] = $row['attention_requester'];
			$user_ids[] = $row['attention_close_user'];

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
				users_overlord::assign_details($row['attention_poster_id']),
				users_overlord::assign_details($row['attention_requester'], 'REPORTER_'),
				users_overlord::assign_details($row['attention_close_user'], 'CLOSER_')
			);

			// Do we have to?
			if ($row['attention_close_user'])
			{
				$output = array_merge(
					$output,
					users_overlord::assign_details($row['attention_close_user'], 'CLOSE_')
				);
			}

			$template_block = (isset($options['template_block'])) ? $options['template_block'] : 'attention';
			phpbb::$template->assign_block_vars($template_block, $output);
		}
		unset($attention);
	}

	/**
	* Setup the sort tool and return it for posts display
	*
	* @return titania_sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = new titania_sort();
		$sort->set_sort_keys(self::$sort_by);

		$sort->default_sort_dir = 'd';
		if (isset(self::$sort_by[phpbb::$user->data['user_topic_sortby_type']]))
		{
			$sort->default_key = phpbb::$user->data['user_topic_sortby_type'];
		}
		$sort->default_limit = phpbb::$config['topics_per_page'];

		return $sort;
	}
}
