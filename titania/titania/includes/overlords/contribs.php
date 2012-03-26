<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
		't' => array('UPDATE_TIME', 'c.contrib_last_update'),
		'c' => array('SORT_CONTRIB_NAME', 'c.contrib_name'),
		'r' => array('RATING', 'c.contrib_rating'),
		'd' => array('DOWNLOADS', 'c.contrib_downloads'),
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
	 * @param string $blockname The name of the template block to use (contribs by default)
	 */
	function display_contribs($mode, $id, $sort = false, $blockname = 'contribs')
	{
		titania::add_lang('contributions');
		titania::_include('functions_display', 'titania_topic_folder_img');

		// Setup the sort tool if not sent, then request
		if ($sort === false)
		{
			$sort = self::build_sort();
		}
		$sort->request();

		$select = 'DISTINCT(c.contrib_id), c.contrib_name, c.contrib_name_clean, c.contrib_status, c.contrib_downloads, c.contrib_views, c.contrib_rating, c.contrib_rating_count, c.contrib_type, c.contrib_last_update, c.contrib_user_id';
		switch ($mode)
		{
			case 'author' :
				// Get the contrib_ids this user is an author in (includes as a co-author)
				$contrib_ids = titania::$cache->get_author_contribs($id);

				if (!sizeof($contrib_ids))
				{
					return compact('sort');
				}

				$sql_ary = array(
					'SELECT'	=> $select,

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE	=> 'c',
					),

					'WHERE'		=> phpbb::$db->sql_in_set('c.contrib_id', $contrib_ids) . '
						AND c.contrib_visible = 1',

					'ORDER_BY'	=> $sort->get_order_by(),
				);
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
					),

					'WHERE'		=> ((is_array($id) && sizeof($id)) ? phpbb::$db->sql_in_set('cic.category_id', array_map('intval', $id)) : 'cic.category_id = ' . (int) $id) . '
						AND c.contrib_visible = 1',

					'ORDER_BY'	=> $sort->get_order_by(),
				);
			break;

			case 'all' :
				$sql_ary = array(
					'SELECT'	=> $select,

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE	=> 'c',
					),

					'WHERE'		=> 'c.contrib_visible = 1',

					'ORDER_BY'	=> $sort->get_order_by(),
				);
			break;
		}

		titania_tracking::get_track_sql($sql_ary, TITANIA_CONTRIB, 'c.contrib_id');

		$validation_free_types = array();
		$mod_contrib_mod = (bool) phpbb::$auth->acl_get('u_titania_mod_contrib_mod');

		// Permissions
		if (titania::$config->require_validation && !$mod_contrib_mod)
		{
			$sql_ary['LEFT_JOIN'][] = array(
				'FROM'	=> array(TITANIA_CONTRIB_COAUTHORS_TABLE => 'cc'),
				'ON'	=> 'cc.contrib_id = c.contrib_id AND cc.user_id = ' . phpbb::$user->data['user_id'],
			);
			$view_unapproved = array();
			if (sizeof(titania_types::find_authed('moderate')))
			{
				$view_unapproved = array_merge($view_unapproved, titania_types::find_authed('moderate'));
			}
			if (sizeof(titania_types::find_authed('view')))
			{
				$view_unapproved = array_merge($view_unapproved, titania_types::find_authed('view'));
			}

			// Find the ones that do not require validation
			$validation_free_types = titania_types::find_validation_free();
			$view_unapproved = array_merge($view_unapproved, $validation_free_types);

			$view_unapproved = array_unique($view_unapproved);
			$sql_ary['WHERE'] .= ' AND (' . phpbb::$db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) .
				((sizeof($view_unapproved)) ? ' OR ' . phpbb::$db->sql_in_set('c.contrib_type', array_map('intval', $view_unapproved)) : '') . '
				OR c.contrib_user_id = ' . phpbb::$user->data['user_id'] . '
				OR cc.active = 1)';
		}

		// Main SQL Query
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if (!$sort->sql_count($sql_ary, 'DISTINCT(c.contrib_id)'))
		{
			// No results...no need to query more...
			return compact('sort');
		}

		$sort->build_pagination(titania_url::$current_page, titania_url::$params);

		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

		$contrib_ids = $user_ids = array();
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			//Check to see if user has permission
			if (sizeof($validation_free_types) && in_array($row['contrib_type'], $validation_free_types) && !$mod_contrib_mod && $row['contrib_user_id'] != phpbb::$user->data['user_id'] && titania::$access_level != TITANIA_ACCESS_TEAMS)
			{
				//If the contribution has a status that is not accessible by the current user let's not add it
				if (in_array($row['contrib_status'], array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_HIDDEN, TITANIA_CONTRIB_DISABLED)))
				{
					continue;
				}
			}
			$user_ids[] = $row['contrib_user_id'];
			$contrib_ids[] = $row['contrib_id'];
			self::$contribs[$row['contrib_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		// Get user data
		users_overlord::load_users($user_ids);

		// Get phpBB versions
		if (sizeof($contrib_ids))
		{
			$validation_free = titania_types::find_validation_free();
			if (sizeof($validation_free) && titania::$config->require_validation)
			{
				$sql = 'SELECT rp.contrib_id, rp.phpbb_version_branch, rp.phpbb_version_revision
					FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . ' rp, ' . TITANIA_CONTRIBS_TABLE . ' c
					WHERE ' . phpbb::$db->sql_in_set('rp.contrib_id', array_map('intval', $contrib_ids)) .'
					AND c.contrib_id = rp.contrib_id
					AND (rp.revision_validated = 1
						OR ' . phpbb::$db->sql_in_set('c.contrib_type', $validation_free) . ')
					ORDER BY rp.row_id DESC';
			}
			else
			{
				$sql = 'SELECT contrib_id, phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
					WHERE ' . phpbb::$db->sql_in_set('contrib_id', array_map('intval', $contrib_ids)) .
					((titania::$config->require_validation) ? ' AND revision_validated = 1' : '') . '
					ORDER BY row_id DESC';
			}
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				self::$contribs[$row['contrib_id']]['phpbb_versions'][] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}

		// Setup some objects we'll use for temps
		$contrib = new titania_contribution();
		$contrib->author = new titania_author();
		$versions = titania::$cache->get_phpbb_versions();
		$author_contribs = titania::$cache->get_author_contribs(phpbb::$user->data['user_id'], true);

		// Get the mark all tracking
		titania_tracking::get_track(TITANIA_CONTRIB, 0);

		foreach ($contrib_ids as $contrib_id)
		{
			$row = self::$contribs[$contrib_id];

			$contrib->__set_array($row);

			$contrib->author->user_id = $contrib->contrib_user_id;
			$contrib->author->__set_array($row);

			// Author contrib variables
			$contrib->is_author = ($contrib->contrib_user_id == phpbb::$user->data['user_id']) ? true : false;
			$contrib->is_active_coauthor = (in_array($contrib->contrib_id, $author_contribs)) ? true : false;

			// Store the tracking info we grabbed from the DB
			titania_tracking::store_from_db($row);

			// Get the folder image
			$folder_img = $folder_alt = '';
			$last_read_mark = titania_tracking::get_track(TITANIA_CONTRIB, $contrib->contrib_id, true);
			$last_complete_mark = titania_tracking::get_track(TITANIA_CONTRIB, 0, true);
			$is_unread = ($contrib->contrib_last_update > $last_read_mark && $contrib->contrib_last_update > $last_complete_mark) ? true : false;
			titania_topic_folder_img($folder_img, $folder_alt, 0, $is_unread);

			// Only get unique phpBB versions supported
			if (isset($row['phpbb_versions']))
			{
				titania::_include('functions_display', 'order_phpbb_version_list_from_db');

				$ordered_phpbb_versions = order_phpbb_version_list_from_db($row['phpbb_versions']);
			}

			phpbb::$template->assign_block_vars($blockname, array_merge($contrib->assign_details(true, true), array(
				'FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
				'FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
				'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
				'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
				'FOLDER_IMG_WIDTH'			=> phpbb::$user->img($folder_img, '', false, '', 'width'),
				'FOLDER_IMG_HEIGHT'			=> phpbb::$user->img($folder_img, '', false, '', 'height'),
				'PHPBB_VERSION'				=> (isset($row['phpbb_versions']) && sizeof($ordered_phpbb_versions) == 1) ? $ordered_phpbb_versions[0] : '',
			)));

			if (isset($row['phpbb_versions']))
			{
				foreach ($ordered_phpbb_versions as $version_row)
				{
					phpbb::$template->assign_block_vars($blockname . '.phpbb_versions', array(
						'NAME'		=> $version_row,
					));
				}
			}

			$contrib_type = $row['contrib_type'];
		}
		unset($contrib);

		return compact('sort');
	}

	/**
	* Setup the sort tool and return it for contributions display
	*
	* @return titania_sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = new titania_sort();
		$sort->set_sort_keys(self::$sort_by);

		// Show update time descending and limit to the topics per page by default
		$sort->set_defaults(phpbb::$config['topics_per_page'], 't', 'd');

		$sort->result_lang = 'TOTAL_CONTRIBS';

		return $sort;
	}
}
