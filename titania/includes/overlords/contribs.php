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

class contribs_overlord
{
	/**
	* Contribs array
	* Stores [id] => contrib row
	*
	* @var array
	*/
	public static $contribs = array();

	public static $sort_by = array(
		'a' => array('AUTHOR', 'u.username_clean'),
		't' => array('UPDATE_TIME', 'c.contrib_last_update'),
		'c' => array('SORT_CONTRIB_NAME', 'c.contrib_name'),
	);

	/**
	* Load contrib(s) from contrib id(s)
	*
	* @param int|array $contrib_id topic_id or an array of contrib_ids
	*/
	public static function load_contrib($contrib_id)
	{
		if (!is_array($contrib_id))
		{
			$contrib_id = array($contrib_id);
		}

		// Only get the rows for those we have not gotten already
		$contrib_id = array_diff($contrib_id, array_keys(self::$contribs));

		if (!sizeof($contrib_id))
		{
			return;
		}

		$sql_ary = array(
			'SELECT' => '*',

			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE	=> 'c',
			),

			'WHERE' => phpbb::$db->sql_in_set('c.contrib_id', array_map('intval', $contrib_id)),
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		while($row = phpbb::$db->sql_fetchrow($result))
		{
			self::$contribs[$row['contrib_id']] = $row;
		}

		phpbb::$db->sql_freeresult($result);
	}

	/**
	 * Get the contrib object
	 *
	 * @param <int> $contrib_id
	 * @param <bool> $query True to always query if it doesn't exist in self::$contribs, False to only grab from self::$contribs (if this is False and it does not exist in self::$contribs we return false)
	 * @return <object|bool> False if the contrib does not exist in the self::$contribs array (load it first!) contrib object if it exists
	 */
	public static function get_contrib_object($contrib_id, $query = false)
	{
		if (!isset(self::$contribs[$contrib_id]))
		{
			if ($query)
			{
				self::load_contrib($contrib_id);

				if (!isset(self::$contribs[$contrib_id]))
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		$contrib = new titania_contribution();
		$contrib->__set_array(self::$contribs[$contrib_id]);

		return $contrib;
	}

	/**
	 * Display contributions
	 *
	 * @param string $mode The mode (category, author)
	 * @param int $id The parent id (only show contributions under this category, author, etc)
	 * @param string $pagination_url The url to display for pagination.
	 * @param string $blockname The name of the template block to use (contribs by default)
	 */
	function display_contribs($mode, $id, $sort = false, $pagination = false, $blockname = 'contribs')
	{
		titania::add_lang('contributions');
		titania::_include('functions_display', 'titania_topic_folder_img');

		if ($sort === false)
		{
			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_sort_keys(self::$sort_by);
			$sort->default_key = 'c';
		}

		if ($pagination === false)
		{
			// Setup the pagination tool
			$pagination = new titania_pagination();
			$pagination->default_limit = phpbb::$config['topics_per_page'];
			$pagination->request();
		}
		$pagination->result_lang = 'TOTAL_CONTRIBS';

		$select = 'c.contrib_id, c.contrib_name, c.contrib_name_clean, c.contrib_status, c.contrib_downloads, c.contrib_views, c.contrib_rating, c.contrib_rating_count, c.contrib_type, c.contrib_last_update,
						u.username, u.user_colour, u.username_clean';
		switch ($mode)
		{
			case 'author' :
				// Get the contrib_ids this user is an author in (includes as a co-author)
				$contrib_ids = titania::$cache->get_author_contribs($id);

				if (!sizeof($contrib_ids))
				{
					return;
				}

				$sql_ary = array(
					'SELECT'	=> $select,

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE	=> 'c',
						USERS_TABLE				=> 'u',
					),

					'WHERE'		=> phpbb::$db->sql_in_set('c.contrib_id', $contrib_ids) . '
						AND u.user_id = c.contrib_user_id
						AND c.contrib_visible = 1',

					'ORDER_BY'	=> $sort->get_order_by(),
				);

				titania_tracking::get_track_sql($sql_ary, TITANIA_CONTRIB, 'c.contrib_id');
			break;

			case 'category' :
				$sql_ary = array(
					'SELECT'	=> $select,

					'FROM'		=> array(
						TITANIA_CONTRIB_IN_CATEGORIES_TABLE => 'cic',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_CONTRIBS_TABLE => 'c'),
							'ON'	=> 'cic.contrib_id = c.contrib_id',
						),
						array(
							'FROM'	=> array(USERS_TABLE => 'u'),
							'ON'	=> 'u.user_id = c.contrib_user_id',
						),
					),

					'WHERE'		=> 'cic.category_id = ' . (int) $id . '
						AND c.contrib_visible = 1',

					'ORDER_BY'	=> $sort->get_order_by(),
				);

				titania_tracking::get_track_sql($sql_ary, TITANIA_CONTRIB, 'c.contrib_id');
			break;
		}

		// Permissions
		if (titania::$config->require_validation && !titania::$access_level == TITANIA_ACCESS_TEAMS)
		{
			$sql_ary['LEFT_JOIN'][] = array(
				'FROM'	=> array(TITANIA_CONTRIB_COAUTHORS_TABLE => 'cc'),
				'ON'	=> 'cc.contrib_id = c.contrib_id AND cc.user_id = ' . phpbb::$user->data['user_id'],
			);
			$sql_ary['WHERE'] .= ' AND (c.contrib_status = ' . TITANIA_CONTRIB_APPROVED . '
				OR c.contrib_user_id = ' . phpbb::$user->data['user_id'] . '
				OR cc.active = 1)';
		}

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		$pagination->sql_count($sql_ary, 'c.contrib_id');
		$pagination->build_pagination(titania_url::$current_page, titania_url::$params);

		// Setup some objects we'll use for temps
		$contrib = new titania_contribution();
		$contrib->author = new titania_author();

		$result = phpbb::$db->sql_query_limit($sql, $pagination->limit, $pagination->start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$contrib->__set_array($row);

			$contrib->author->__set_array($row);

			// Store the tracking info we grabbed from the DB
			titania_tracking::store_from_db($row);

			// Get the folder image
			$folder_img = $folder_alt = '';
			titania_topic_folder_img($folder_img, $folder_alt, 0, titania_tracking::is_unread(TITANIA_CONTRIB, $contrib->contrib_id, $contrib->contrib_last_update));

			phpbb::$template->assign_block_vars($blockname, array_merge($contrib->assign_details(true, true), array(
				'FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
				'FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
				'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
				'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
				'FOLDER_IMG_WIDTH'			=> phpbb::$user->img($folder_img, '', false, '', 'width'),
				'FOLDER_IMG_HEIGHT'			=> phpbb::$user->img($folder_img, '', false, '', 'height'),
			)));

			$contrib_type = $row['contrib_type'];
		}
		phpbb::$db->sql_freeresult($result);
		unset($contrib);

		phpbb::$template->assign_vars(array(
			'U_ACTION'			=> titania_url::$current_page,
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}
}
