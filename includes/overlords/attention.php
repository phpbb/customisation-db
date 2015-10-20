<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

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
	* Get the appropriate attention object for the attention item
	*
	* @param mixed $attention_id
	* @param mixed $object_type
	* @param mixed $object_id
	*/
	public static function get_attention_object($attention_id, $object_type = false, $object_id = false)
	{
		$data = self::load_attention($attention_id, $object_type, $object_id);

		if (!$data)
		{
			return false;
		}

		switch ($data['attention_object_type'])
		{
			case TITANIA_POST:
				titania::_include('objects/attention_types/post', false, 'titania_attention_post');
				$object = new titania_attention_post();
			break;

			case TITANIA_CONTRIB:
				titania::_include('objects/attention_types/contribution', false, 'titania_attention_contribution');
				$object = new titania_attention_contribution();
			break;

			default:
				$object = new titania_attention();
		}

		$object->__set_array($data);
		return $object;
	}

	/**
	* Load an attention item by attention_id or the type/id
	*
	* @param mixed $attention_id
	* @param mixed $object_type
	* @param mixed $object_id
	*/
	public static function load_attention($attention_id, $object_type = false, $object_id = false)
	{
		$sql = 'SELECT a.* FROM ' . TITANIA_ATTENTION_TABLE . ' a
			LEFT JOIN ' . TITANIA_CONTRIBS_TABLE . ' c
				ON (a.attention_object_type = ' . TITANIA_CONTRIB . ' AND a.attention_object_id = c.contrib_id)
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

		// Permissions
		$sql .= ' AND ' . self::get_permission_sql();

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
	*	exclude_attention_types
	* 	attention_object_id
	* 	only_closed bool only display closed items
	* 	display_closed bool display closed and open items
	* 	template_block string the name of the template block to output to (attention if not sent)
	* @param \phpbb\titania\sort $sort
	*/
	public static function display_attention_list($options = array(), $sort = false)
	{
		if ($sort === false)
		{
			// Setup the sort tool
			$sort = self::build_sort();
		}
		$sort->request();
		$path_helper = phpbb::$container->get('path_helper');
		$controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');

		$sql_ary = array(
			'SELECT'	=> 'a.*',

			'FROM'		=> array(
				TITANIA_ATTENTION_TABLE	=> 'a',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_CONTRIBS_TABLE => 'c'),
					'ON'	=> 'a.attention_object_type = ' . TITANIA_CONTRIB . ' AND a.attention_object_id = c.contrib_id',
				),
			),

			'WHERE'		=> array(),

			'ORDER_BY'	=> $sort->get_order_by(),
		);

		// Limit to certain types if requested
		if (isset($options['attention_type']) && $options['attention_type'])
		{
			$sql_ary['WHERE'][] = 'a.attention_type = ' . (int) $options['attention_type'];
		}

		// Exclude certain types
		if (!empty($options['exclude_attention_types']))
		{
			$sql_ary['WHERE'][] = phpbb::$db->sql_in_set('a.attention_type', $options['exclude_attention_types'], true);
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

		$sql_ary['WHERE'][] = self::get_permission_sql();

		$sql_ary['WHERE'] = implode(' AND ', $sql_ary['WHERE']);

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if (!$sort->sql_count($sql_ary, 'a.attention_id'))
		{
			// No results...no need to query more...
			return;
		}

		$url_parts = $path_helper->get_url_parts($controller_helper->get_current_url());
		$sort->build_pagination($url_parts['base'], $url_parts['params']);

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
		foreach ($attention_ids as $attention_id)
		{
			$attention = self::get_attention_object($attention_id);

			$output = array_merge(
				$attention->assign_details(true),
				users_overlord::assign_details($attention->attention_poster_id),
				users_overlord::assign_details($attention->attention_requester, 'REPORTER_'),
				users_overlord::assign_details($attention->attention_close_user, 'CLOSER_')
			);

			// Do we have to?
			if ($row['attention_close_user'])
			{
				$output = array_merge(
					$output,
					users_overlord::assign_details($attention->attention_close_user, 'CLOSE_')
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
	* @return \phpbb\titania\sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = phpbb::$container->get('phpbb.titania.sort');;
		$sort->set_sort_keys(self::$sort_by);

		$sort->default_sort_dir = 'd';
		if (isset(self::$sort_by[phpbb::$user->data['user_topic_sortby_type']]))
		{
			$sort->default_key = phpbb::$user->data['user_topic_sortby_type'];
		}
		$sort->default_limit = phpbb::$config['topics_per_page'];

		return $sort;
	}

	/**
	* Get permission check for WHERE clause
	*/
	public static function get_permission_sql()
	{
		$sql_where = '';
		$types = phpbb::$container->get('phpbb.titania.contribution.type.collection');
		$types_managed = $types->find_authed('moderate');

		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$sql_where .= '(a.attention_object_type = ' . TITANIA_POST . ')';
			$negated = false;
		}
		else
		{
			$sql_where .= '(a.attention_object_type <> ' . TITANIA_POST . ')';
			$negated = true;
		}

		if (!empty($types_managed))
		{
			$sql_where .= ($negated) ? ' AND ' : ' OR ';
			$sql_where .= '(a.attention_object_type = ' . TITANIA_CONTRIB . ' AND ' . phpbb::$db->sql_in_set('c.contrib_type', $types_managed) . ')';
		}
		else
		{
			$sql_where .= 'AND (a.attention_object_type <> ' . TITANIA_CONTRIB . ')';
		}

		return '(' . $sql_where . ')';
	}
}
