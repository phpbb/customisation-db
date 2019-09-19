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

use phpbb\titania\ext;
use phpbb\titania\message\message;
use phpbb\titania\versions;

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
		$contrib->set_type(self::$contribs[$contrib_id]['contrib_type']);

		return $contrib;
	}

	/**
	 * Get list of the hidden categories
	 * @return array
	 */
	public static function get_hidden_categories()
	{
		$hidden_categories_ary = array(
			'SELECT'	=> 'c.category_id',

			'FROM'		=> array(
				TITANIA_CATEGORIES_TABLE	=> 'c',
			),

			'WHERE'		=> 'c.category_visible = 0',
		);

		$hidden_categories_sql = phpbb::$db->sql_build_query('SELECT', $hidden_categories_ary);
		$hidden_categories_result = phpbb::$db->sql_query($hidden_categories_sql);
		$hidden_categories_ids = array();

		while ($hidden_categories_row = phpbb::$db->sql_fetchrow($hidden_categories_result))
		{
			$hidden_categories_ids[] = (int) $hidden_categories_row['category_id'];
		}

		phpbb::$db->sql_freeresult($hidden_categories_result);

		return $hidden_categories_ids;
	}

	/**
	 * Display contributions
	 *
	 * @param string $mode The mode (category, author)
	 * @param int|array $hierarchy_ids The parent id (plus any subcategory ids; if categories) (only show contributions under this category, author, etc)
	 * @param int|bool $branch Branch to limit results to: 20|30|31. Defaults to false.
	 * @param \phpbb\titania\sort|bool $sort
	 * @param string $blockname The name of the template block to use (contribs by default)
	 * @param string $status Approval status
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function display_contribs($mode, $hierarchy_ids, $branch = false, $sort = false, $blockname = 'contribs', $status = null)
	{
		phpbb::$user->add_lang_ext('phpbb/titania', 'contributions');

		$tracking = phpbb::$container->get('phpbb.titania.tracking');
		$types = phpbb::$container->get('phpbb.titania.contribution.type.collection');
		$cache = phpbb::$container->get('phpbb.titania.cache');

		// Handle status filter
		$status_filter = false;

		if (!empty($status))
		{
			// Filter by status
			$status_filter_type = ($status == 'approved') ? ext::TITANIA_CONTRIB_APPROVED : ext::TITANIA_CONTRIB_NEW;
			$status_filter = ' AND c.contrib_status = ' . $status_filter_type;
		}

		// Setup the sort tool if not sent, then request
		if ($sort === false)
		{
			$sort = self::build_sort();
		}
		$sort->request();

		$branch = ($branch) ? (int) $branch : null;

		$select = 'DISTINCT(c.contrib_id), c.contrib_name, c.contrib_name_clean,
			c.contrib_status, c.contrib_downloads, c.contrib_views, c.contrib_rating,
			c.contrib_rating_count, c.contrib_type, c.contrib_last_update, c.contrib_user_id,
			c.contrib_limited_support, c.contrib_categories, c.contrib_desc, c.contrib_desc_uid';

		$check_hidden_categories_on_all = false;
		$hidden_categories_ids = self::get_hidden_categories();

		switch ($mode)
		{
			case 'author' :
				// Get the contrib_ids this user is an author in (includes as a co-author)
				$contrib_ids = titania::$cache->get_author_contribs($hierarchy_ids, $types, phpbb::$user);

				if (!sizeof($contrib_ids))
				{
					return compact('sort');
				}

				$sql_ary = array(
					'SELECT'	=> $select . ', a.attachment_id, a.thumbnail',

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE	=> 'c',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_ATTACHMENTS_TABLE => 'a'),
							'ON'	=> 'c.contrib_id = a.object_id
								AND a.object_type = ' . ext::TITANIA_SCREENSHOT . '
								AND a.is_orphan = 0
								AND a.is_preview = 1',
						),
					),

					'WHERE'		=> phpbb::$db->sql_in_set('c.contrib_id', $contrib_ids) . '
						AND c.contrib_visible = 1',

					'ORDER_BY'	=> $sort->get_order_by(),
				);
			break;

			case 'category':
				// We need to determine if we are currently inside the "hidden" category
				// The only way we can do that is to look at each id, get it's children and then compare the array
				// against the $hierarchy_ids - if we get a match on any of them, we know we are in the actual category
				$actual_category = array(
					'actual' => false,
					'id' => 0,
					'all' => array(),
				);

				// Simplify it - just make it an array
				if (!is_array($hierarchy_ids))
				{
					$hierarchy_ids = array($hierarchy_ids);
				}

				foreach ($hierarchy_ids as $hierarchy_id)
				{
					$all_subcategory_ids = array_keys($cache->get_category_children($hierarchy_id));
					$all_subcategory_ids[] = (int) $hierarchy_id;

					asort($all_subcategory_ids);
					asort($hierarchy_ids);

					if (array_values($all_subcategory_ids) == array_values($hierarchy_ids))
					{
						$actual_category['actual'] = true;
						$actual_category['id'] = $hierarchy_id;
						$actual_category['all'] = $all_subcategory_ids;
						break;
					}
				}

				// If we found a hit, and that hit is inside the hidden category list - then that means
				// we are inside the category
				if ($actual_category['actual'] && in_array($actual_category['id'], $hidden_categories_ids))
				{
					$visible_category_ids = $actual_category['all'];
				}

				// Otherwise, it's some parent category so we just strip out the hidden subcategories
				else
				{
					$visible_category_ids = array_diff($hierarchy_ids, $hidden_categories_ids);
				}

				$sql_ary = array(
					'SELECT'	=> $select . ', a.attachment_id, a.thumbnail',

					'FROM'		=> array(
						TITANIA_CONTRIB_IN_CATEGORIES_TABLE => 'cic',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_CONTRIBS_TABLE => 'c'),
							'ON'	=> 'cic.contrib_id = c.contrib_id',
						),
						array(
							'FROM'	=> array(TITANIA_REVISIONS_PHPBB_TABLE => 'rp'),
							'ON'	=> 'cic.contrib_id = rp.contrib_id',
						),
						array(
							'FROM'	=> array(TITANIA_ATTACHMENTS_TABLE => 'a'),
							'ON'	=> 'c.contrib_id = a.object_id
								AND a.object_type = ' . ext::TITANIA_SCREENSHOT . '
								AND a.is_orphan = 0
								AND a.is_preview = 1',
						)
					),

					// If multiple categories, use the stripped list. If it's just the single hidden category, that's okay
					// as presumably someone has gone looking for the hidden category, or it has been linked to, etc.
					'WHERE'		=> phpbb::$db->sql_in_set('cic.category_id', $visible_category_ids, false, true) . '
						AND c.contrib_visible = 1 ' . (($branch) ? " AND rp.phpbb_version_branch = $branch" : '') .
						(($status_filter) ? $status_filter : ''),

					'ORDER_BY'	=> $sort->get_order_by(),
				);
			break;

			case 'all' :
				$check_hidden_categories_on_all = true;
				$sql_ary = array(
					'SELECT'	=> $select . ', a.attachment_id, a.thumbnail',

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE	=> 'c',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_REVISIONS_PHPBB_TABLE => 'rp'),
							'ON'	=> 'c.contrib_id = rp.contrib_id',
						),

						array(
							'FROM'	=> array(TITANIA_ATTACHMENTS_TABLE => 'a'),
							'ON'	=> 'c.contrib_id = a.object_id
								AND a.object_type = ' . ext::TITANIA_SCREENSHOT . '
								AND a.is_orphan = 0
								AND a.is_preview = 1',
						),
					),

					'WHERE'		=> 'c.contrib_visible = 1' .
						(($branch) ? " AND rp.phpbb_version_branch = $branch" : '') .
						(($status_filter) ? $status_filter : ''),

					'ORDER_BY'	=> $sort->get_order_by(),
				);
			break;
		}

		$tracking->get_track_sql($sql_ary, ext::TITANIA_CONTRIB, 'c.contrib_id');

		$mod_contrib_mod = (bool) phpbb::$auth->acl_get('u_titania_mod_contrib_mod');

		// Permissions
		if (!$mod_contrib_mod)
		{
			$sql_ary['SELECT'] .= ', cc.user_id AS coauthor';

			$sql_ary['LEFT_JOIN'][] = array(
				'FROM'	=> array(TITANIA_CONTRIB_COAUTHORS_TABLE => 'cc'),
				'ON'	=> 'cc.contrib_id = c.contrib_id AND cc.user_id = ' . phpbb::$user->data['user_id'],
			);
			$view_unapproved = array();
			if ($types->find_authed('moderate'))
			{
				$view_unapproved = array_merge($view_unapproved, $types->find_authed('moderate'));
			}
			if ($types->find_authed('view'))
			{
				$view_unapproved = array_merge($view_unapproved, $types->find_authed('view'));
			}

			$view_unapproved = array_unique($view_unapproved);
			$sql_ary['WHERE'] .= ' AND (' . phpbb::$db->sql_in_set('c.contrib_status', array(ext::TITANIA_CONTRIB_APPROVED, ext::TITANIA_CONTRIB_DOWNLOAD_DISABLED)) .
				($branch ? ' AND rp.revision_validated = 1' : '') .
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

		$controller_helper = phpbb::$container->get('controller.helper');
		$path_helper = phpbb::$container->get('path_helper');
		$access = phpbb::$container->get('phpbb.titania.access');

		$url = $path_helper->get_url_parts($controller_helper->get_current_url());
		$sort->build_pagination($url['base']);

		$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

		$contrib_ids = $user_ids = array();

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if ($check_hidden_categories_on_all)
			{
				$contrib_categories = explode(',', $row['contrib_categories']);

				if (!count(array_diff($contrib_categories, $hidden_categories_ids)))
				{
					// Don't show it on the "all" listings page, because the contrib is only in hidden categories
					continue;
				}
			}

			//Check to see if user has permission
			if (!$mod_contrib_mod && $row['contrib_user_id'] != phpbb::$user->data['user_id'] && $row['coauthor'] != phpbb::$user->data['user_id'] && !$access->is_team())
			{
				//If the contribution has a status that is not accessible by the current user let's not add it
				if (in_array($row['contrib_status'], array(ext::TITANIA_CONTRIB_NEW, ext::TITANIA_CONTRIB_CLEANED, ext::TITANIA_CONTRIB_HIDDEN, ext::TITANIA_CONTRIB_DISABLED)))
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
			$validation_free = $types->find_validation_free();
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
		$author_contribs = titania::$cache->get_author_contribs(phpbb::$user->data['user_id'], $types, phpbb::$user, true);

		// Get the mark all tracking
		$tracking->get_track(ext::TITANIA_CONTRIB, 0);

		foreach ($contrib_ids as $contrib_id)
		{
			$row = self::$contribs[$contrib_id];

			$contrib->__set_array($row);
			$contrib->set_type($row['contrib_type']);

			$contrib->author->user_id = $contrib->contrib_user_id;
			$contrib->author->__set_array($row);

			$contrib->fill_categories();

			// Author contrib variables
			$contrib->is_author = ($contrib->contrib_user_id == phpbb::$user->data['user_id']) ? true : false;
			$contrib->is_active_coauthor = (in_array($contrib->contrib_id, $author_contribs)) ? true : false;
			$rating = new \titania_rating('contrib',$contrib);
			$rating->cannot_rate = true;
			$contrib->rating = $rating;

			// Store the tracking info we grabbed from the DB
			$tracking->store_from_db($row);

			// Get the folder image
			$folder_img = $folder_alt = '';
			$last_read_mark = $tracking->get_track(ext::TITANIA_CONTRIB, $contrib->contrib_id, true);
			$last_complete_mark = $tracking->get_track(ext::TITANIA_CONTRIB, 0, true);
			$is_unread = ($contrib->contrib_last_update > $last_read_mark && $contrib->contrib_last_update > $last_complete_mark) ? true : false;
			phpbb::$container->get('phpbb.titania.display')->topic_folder_img($folder_img, $folder_alt, 0, $is_unread);

			// Only get unique phpBB versions supported
			if (isset($row['phpbb_versions']))
			{
				$ordered_phpbb_versions = versions::order_phpbb_version_list_from_db(
					titania::$cache,
					$row['phpbb_versions'],
					$contrib->options['all_versions']
				);
			}

			$preview_params = array();
			$stripped_desc = message::generate_clean_excerpt(
				$contrib->contrib_desc,
				$contrib->contrib_desc_uid,
				255,
				'&hellip;'
			);

			if (!empty($row['attachment_id']))
			{
				$preview_params['id'] = $row['attachment_id'];
				if ($row['thumbnail'])
				{
					$preview_params['thumb'] = 1;
				}
			}

			phpbb::$template->assign_block_vars($blockname, array_merge($contrib->assign_details(true, true), array(
				'FOLDER_STYLE'				=> $folder_img,
				'FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
				'FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
				'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
				'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
				'FOLDER_IMG_WIDTH'			=> phpbb::$user->img($folder_img, '', false, '', 'width'),
				'FOLDER_IMG_HEIGHT'			=> phpbb::$user->img($folder_img, '', false, '', 'height'),
				'PHPBB_VERSION'				=> (isset($row['phpbb_versions']) && sizeof($ordered_phpbb_versions) == 1) ? $ordered_phpbb_versions[0] : '',
				'DESC_SNIPPET'				=> $stripped_desc,
				'PREVIEW'					=> ($preview_params) ? $controller_helper->route('phpbb.titania.download', $preview_params) : '',
			)));

			if (isset($row['phpbb_versions']))
			{
				$prev_branch = '';

				foreach ($ordered_phpbb_versions as $version_row)
				{
					phpbb::$template->assign_block_vars($blockname . '.phpbb_versions', array(
						'NAME'		=> $version_row,
					));
					$branch = versions::get_branch_from_string($version_row);

					if ($prev_branch != $branch)
					{
						phpbb::$template->assign_block_vars($blockname . '.branches', array(
							'NAME'	=> $version_row,
						));
					}
					$prev_branch = $branch;
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
	* @return \phpbb\titania\sort
	*/
	public static function build_sort()
	{
		// Setup the sort and set the sort keys
		$sort = phpbb::$container->get('phpbb.titania.sort');
		$sort->set_sort_keys(self::$sort_by);

		// Show update time descending and limit to the topics per page by default
		$sort->set_defaults(phpbb::$config['topics_per_page'], 't', 'd');

		$sort->result_lang = 'NUM_CONTRIBS';

		return $sort;
	}

	/**
	 * Create a feed either for an individual contribution or for all contributions
	 * @param $template
	 * @param $helper
	 * @param $path_helper
	 * @param mixed $contrib
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws Exception
	 */
	public static function build_feed($template, $helper, $path_helper, $contrib = false)
	{
		$contrib_id = ($contrib) ? $contrib->contrib_id : false;

		if (!phpbb::$config['feed_overall'])
		{
			// Don't proceed if feeds are disabled
			trigger_error('NO_FEED_ENABLED');
		}

		// Show one contribution only, if on the specific contrib page
		$contrib_specific = ($contrib_id) ? 'AND r.contrib_id = ' . (int) $contrib_id : '';

		$sql_ary = [
			'SELECT' => 'r.*, c.*, u.username_clean',

			'FROM' => [
				TITANIA_REVISIONS_TABLE => 'r',
				TITANIA_CONTRIBS_TABLE => 'c',
				USERS_TABLE => 'u',
			],

			'WHERE'	=> 'r.revision_status = ' . ext::TITANIA_REVISION_APPROVED . '
				AND r.revision_submitted = 1
				AND c.contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
				AND r.contrib_id = c.contrib_id
				AND u.user_id = c.contrib_user_id 
				' . $contrib_specific,

			'ORDER_BY'	=> 'r.validation_date DESC',
		];

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query_limit($sql, 100);

		$rows = [];
		$feed_updated_time = false;

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Only proceed if the contribution is not in categories that are all hidden
			if (!self::feed_hidden_category_check($row['contrib_id']))
			{
				$feed_rows = [];
				$feed_rows['item_date'] = date(\DateTime::ATOM, $row['validation_date']);

				// Get the most recent time
				if (!$feed_updated_time)
				{
					$feed_updated_time = $row['validation_date'];
				}

				// Make the name including the version
				$feed_rows['item_title'] = $row['contrib_name'] . ' ' . $row['revision_version'];

				if ($row['revision_name'])
				{
					// Include the code name if it's supplied
					$feed_rows['item_title'] .= ' (' . $row['revision_name'] . ')';
				}

				$feed_rows['item_author'] = $row['username_clean'];
				$feed_rows['item_description'] = phpbb::$user->lang('FEED_CDB_NEW_VERSION', $row['revision_version'], $row['contrib_name']);
				$feed_rows['item_link'] = '';

				if ($row['attachment_id'])
				{
					// Include the download link
					$feed_rows['item_link'] = $helper->route('phpbb.titania.download', array('id' => $row['attachment_id']));
				}

				else
				{
					// This is so we can link to a bbCode customisation details page for example, because it doesn't
					// have a download link.
					if ($contrib)
					{
						$feed_rows['item_link'] = $contrib->get_url();
					}

					else
					{
						// Load the contribution if we don't have it already.
						self::load_contrib($row['contrib_id']);
						$feed_rows['item_link'] = self::get_contrib_object($row['contrib_id'])->get_url();
					}
				}

				if ($feed_rows['item_link'])
				{
					// Strip session
					$feed_rows['item_link'] = $path_helper->strip_url_params($feed_rows['item_link'], 'sid');
				}

				$rows[] = $feed_rows;
			}
		}

		phpbb::$db->sql_freeresult($result);
		$template->assign_block_vars_array('feed', $rows);

		/** @var \Symfony\Component\HttpFoundation\Response $content */
		$content = $helper->render('feed.xml.twig');

		// Return the response
		$feed_updated_time = (!$feed_updated_time) ? time() : $feed_updated_time;

		$response = $content;
		$response->headers->set('Content-Type', 'application/atom+xml');
		$response->setCharset('UTF-8');
		$response->setLastModified(new \DateTime('@' . $feed_updated_time));

		if (!empty(phpbb::$user->data['is_bot']))
		{
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}

	/**
	 * Check if the contribution is visible somewhere
	 * @param $contrib_id
	 * @return bool
	 */
	private static function feed_hidden_category_check($contrib_id)
	{
		$sql = 'SELECT cc.category_id, ct.category_visible
				FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . ' cc, ' . TITANIA_CATEGORIES_TABLE . ' ct 
				WHERE cc.contrib_id = ' . (int) $contrib_id . '
					AND ct.category_id = cc.category_id';

		$result = phpbb::$db->sql_query($sql);
		$count = $hidden = 0;

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$count++;

			if (!$row['category_visible'])
			{
				$hidden++;
			}
		}

		// True if all the categories the contribution is in are hidden
		return ($count > 0 && $count === $hidden);
	}
}
