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


use phpbb\config\config;
use phpbb\titania\composer\repository;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\contribution\type\type_interface;
use phpbb\titania\versions;
use phpbb\titania\attachment\attachment;
use phpbb\titania\message\message;
use phpbb\titania\url\url;
use phpbb\titania\user\helper as user_helper;

/**
 * Class to abstract contributions.
 * @package Titania
 */
class titania_contribution extends \phpbb\titania\entity\message_base
{
	/**
	 * Database table to be used for the contribution object
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_CONTRIBS_TABLE;

	/**
	 * Primary sql identifier for the contribution object
	 *
	 * @var string
	 */
	protected $sql_id_field = 'contrib_id';

	/**
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = TITANIA_CONTRIB;

	/**
	 * Author & co-authors of this contribution
	 *
	 * @var titania_author
	 */
	public $author;
	public $coauthors = array();

	/**
	 * Revisions, download array
	 */
	public $revisions = array();
	public $download = array();

	/**
	 * Rating/Screenshots of this contribution
	 *
	 * @var titania_rating/titania_attachment
	 */
	public $rating;

	/** @var \phpbb\titania\attachment\operator */
	public $screenshots;

	/**
	 * Categories in which the contrib resides in.
	 */
	 public $category_data = array();

	/**
	 * Options inherited from categories.
	 */
	 public $options = array();

	/**
	 * is_author (true when visiting user is the author)
	 * is_active_coauthor (true when visiting user is an active co-author)
	 * is_coauthor (true when visiting user is a non-active co-author)
	 */
	public $is_author = false;
	public $is_active_coauthor = false;
	public $is_coauthor = false;

	/**
	 * ColorizeIt sample row
	 *
	 * @var array
     */
    public $clr_sample = false;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\titania\search\manager */
	protected $search_manager;

	/** @var type_collection */
	protected $types;

	/** @var config */
	protected $config;

	/**
	* @var type_interface
	*/
	public $type;

	/**
	 * Constructor class for the contribution object
	 */
	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'contrib_id'					=> array('default' => 0),
			'contrib_type'					=> array('default' => 0),
			'contrib_name'					=> array('default' => '',	'max' => 255,	'message_field' => 'subject'),
			'contrib_name_clean'			=> array('default' => '',	'max' => 255),

			'contrib_desc'					=> array('default' => '',	'message_field' => 'message'),
			'contrib_desc_bitfield'			=> array('default' => '',	'message_field' => 'message_bitfield'),
			'contrib_desc_uid'				=> array('default' => '',	'message_field' => 'message_uid'),
			'contrib_desc_options'			=> array('default' => 7,	'message_field' => 'message_options'),

			'contrib_categories'			=> array('default' => ''),

			'contrib_demo'					=> array('default' => ''),

			'contrib_status'				=> array('default' => TITANIA_CONTRIB_NEW),

			'contrib_user_id'				=> array('default' => 0),

			'contrib_downloads'				=> array('default' => 0),
			'contrib_views'					=> array('default' => 0),

			'contrib_visible'				=> array('default' => 0),

			'contrib_rating'				=> array('default' => 0.0),
			'contrib_rating_count'			=> array('default' => 0),

			'contrib_creation_time'			=> array('default' => 0),

			// Last time the contrib item was updated (created or added a new revision, etc).  Used for tracking
			'contrib_last_update'			=> array('default' => titania::$time),

			'contrib_release_topic_id'		=> array('default' => ''),

			// Number of FAQ items (titania_count format)
			'contrib_faq_count'				=> array('default' => ''),

			// Translation items
			'contrib_iso_code'				=> array('default' => ''),
			'contrib_local_name'			=> array('default' => ''),

			// ColorizeIt stuff
			'contrib_clr_colors'            => array('default' => ''),

			// Author does not provide support
			'contrib_limited_support'		=> array('default' => 0),

			'contrib_package_name'			 => array('default' => ''),
		));

		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$this->path_helper = phpbb::$container->get('path_helper');
		$this->screenshots = phpbb::$container->get('phpbb.titania.attachment.operator');
		$this->cache = phpbb::$container->get('phpbb.titania.cache');
		$this->db = phpbb::$container->get('dbal.conn');
		$this->search_manager = phpbb::$container->get('phpbb.titania.search.manager');
		$this->types = phpbb::$container->get('phpbb.titania.contribution.type.collection');
		$this->config = phpbb::$container->get('config');

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Set the contribution type.
	*
	* @param int $type	Contribution type id.
	* @return null
	*/
	public function set_type($type)
	{
		$this->contrib_type = $type;
		$this->type = $this->types->get($this->contrib_type);
	}

	/**
	 * Load the contrib
	 *
	 * @param int|string $contrib The contrib item (contrib_name_clean, contrib_id)
	 * @param int $type Contrib type
	 * @return bool True if the contrib exists, false if not
	 */
	public function load($contrib = false, $type = false)
	{
		if ($contrib === false)
		{
			$contrib = $this->contrib_id;
		}

		$sql_ary = array(
			'SELECT'	=> 'c.*, a.*',
			'FROM' 		=> array($this->sql_table => 'c'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.user_id = c.contrib_user_id'
				),
			),
		);

		if (is_int($contrib))
		{
			$sql_ary['WHERE'] = 'contrib_id = ' . (int) $contrib;
		}
		else
		{
			if (!$type)
			{
				return false;
			}
			// Temp fix until issue is fixed in phpBB (http://tracker.phpbb.com/browse/PHPBB3-10921)
			$contrib = strtr(utf8_clean_string($contrib), array('!' => 'ǃ'));
			$sql_ary['WHERE'] = 'contrib_name_clean = \'' . phpbb::$db->sql_escape($contrib) . '\'
				AND contrib_type = ' . (int) $type;
		}

		$result = phpbb::$db->sql_query(phpbb::$db->sql_build_query('SELECT', $sql_ary));
		$sql_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		// Make sure we have data.
		if (empty($sql_data))
		{
			return false;
		}

		// Set object data.
		$this->__set_array($sql_data);
		$this->set_type($this->contrib_type);
		// Fill categories
		$this->fill_categories();

		// Set author object and set the data for the author object.
		$this->author = new titania_author($this->contrib_user_id);
		$this->author->__set_array($sql_data);

		// Load co-authors list
		$this->coauthors = array();
		$sql_ary = array(
			'SELECT' => 'cc.*, a.*',
			'FROM'		=> array(
				TITANIA_CONTRIB_COAUTHORS_TABLE => 'cc',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.user_id = cc.user_id'
				),
			),
			'WHERE'		=> 'cc.contrib_id = ' . $this->contrib_id,
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->coauthors[$row['user_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		// Load the users table information
		users_overlord::load_users(array_merge(array($this->contrib_user_id), array_keys($this->coauthors)));

		// Check author/co-author status
		if ($this->contrib_user_id == phpbb::$user->data['user_id'])
		{
			$this->is_author = true;
		}
		else if (isset($this->coauthors[phpbb::$user->data['user_id']]))
		{
			$this->is_coauthor = true;

			if ($this->coauthors[phpbb::$user->data['user_id']]['active'])
			{
				$this->is_active_coauthor = true;
			}
		}

		return true;
	}

	/**
	 * Check whether the current user is an author.
	 *
	 * @return bool
	 */
	public function is_author()
	{
		return $this->is_author || $this->is_active_coauthor;
	}

	/**
	* Check whether the contribution is visible to the current user.
	*
	* @param bool $allow_new	Whether a new contrib is allowed to be visible.
	* @return bool				True if the contrib is visible, false otherwise.
	*/
	public function is_visible($allow_new = false)
	{
		$hidden_statuses = array(TITANIA_CONTRIB_NEW, TITANIA_CONTRIB_HIDDEN, TITANIA_CONTRIB_DISABLED);
		$is_mod = phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || $this->type->acl_get('moderate') || $this->type->acl_get('view');

		if (in_array($this->contrib_status, $hidden_statuses) && !$this->is_author && !$this->is_active_coauthor && !$is_mod)
		{
			if ($this->contrib_status == TITANIA_CONTRIB_NEW && $allow_new)
			{
				return true;
			}

			// Hide hidden and disabled contribs for non-(authors/moderators)
			return false;
		}

		return true;
	}

	/**
	* Check whether the current user can manage the contribution.
	*
	* @param string $overridable_permission		Permission to check that always grants access
	* @return bool
	*/
	public function is_manageable($overridable_permission = 'moderate')
	{
		return (!$this->is_restricted() && ($this->is_author || $this->is_active_coauthor)) ||
			$this->type->acl_get($overridable_permission);
	}

	/**
	* Check whether the contribution is restricted.
	* This means it's either disabled or cleaned.
	*
	* @return bool
	*/
	public function is_restricted()
	{
		return in_array($this->contrib_status, array(
			TITANIA_CONTRIB_CLEANED,
			TITANIA_CONTRIB_DISABLED,
		));
	}

	/**
	 * Get the rating as an object
	 *
	 * @return titania_rating
	 */
	public function get_screenshots()
	{
		if ($this->screenshots->get_object_type() !== null)
		{
			return $this->screenshots;
		}

		$this->screenshots
			->configure(TITANIA_SCREENSHOT, $this->contrib_id)
			->load()
		;

		return $this->screenshots;
	}

	/**
	 * Get the rating as an object
	 *
	 * @return titania_rating
	 */
	public function get_rating()
	{
		if ($this->rating)
		{
			return $this->rating;
		}

		$this->rating = new titania_rating('contrib', $this);
		$this->rating->load_user_rating();

		if ($this->is_author || $this->is_active_coauthor || $this->is_coauthor)
		{
			$this->rating->cannot_rate = true;
		}

		return $this->rating;
	}

	/**
	 * Get the revisions for this contrib item
	 * (not always needed, so save us a query when it's not needed)
	 */
	public function get_revisions()
	{
		if (sizeof($this->revisions) || ($this->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED && !$this->is_author && !$this->is_active_coauthor && !$this->type->acl_get('moderate') && !$this->type->acl_get('view')))
		{
			return;
		}

		// Can they view unapproved revisions?  Yes if validation not required, is author, is active coauthor, can view validation queue or can moderate this contribution
		$can_view_unapproved = ($this->is_author || $this->is_active_coauthor) ? true : false;
		$can_view_unapproved = ($can_view_unapproved || $this->type->acl_get('view')) ? true : false;
		$can_view_unapproved = ($can_view_unapproved || $this->type->acl_get('moderate')) ? true : false;

		$select = 'SELECT r.*, a.download_count FROM ' . TITANIA_REVISIONS_TABLE . ' r
			LEFT JOIN ' . TITANIA_ATTACHMENTS_TABLE . ' a
				ON (r.attachment_id = a.attachment_id)';

		$sql = $select .
			'WHERE r.contrib_id = ' . $this->contrib_id .
				((!$can_view_unapproved) ? ' AND r.revision_status = ' . TITANIA_REVISION_APPROVED : '') . '
				AND r.revision_submitted = 1
			ORDER BY r.revision_id DESC';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->revisions[$row['revision_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		if (sizeof($this->revisions))
		{
			$has_translations = false;
			// Get translations
			$sql = 'SELECT * FROM ' . TITANIA_ATTACHMENTS_TABLE . '
				WHERE object_type = ' . TITANIA_TRANSLATION . '
					AND is_orphan = 0
					AND ' . phpbb::$db->sql_in_set('object_id', array_map('intval', array_keys($this->revisions))) . '
				ORDER BY ' . phpbb::$db->sql_lower_text('real_filename') . ' ASC';
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$this->revisions[$row['object_id']]['translations'][] = $row;
				$has_translations = true;
			}
			phpbb::$db->sql_freeresult($result);

			if ($has_translations)
			{
				phpbb::$template->assign_var('S_TRANSLATIONS', true);
			}

			// Get phpBB versions supported
			$sql = 'SELECT revision_id, phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('revision_id', array_map('intval', array_keys($this->revisions))) . '
				ORDER BY row_id DESC';
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$this->revisions[$row['revision_id']]['phpbb_versions'][] = $row;
			}
			phpbb::$db->sql_freeresult($result);
		}
	}

	/**
	 * Get the latest revisions (to download)
	 * Stored in $this->download; only gets the latest validated (if validation is required)
	 *
	 * @param bool|int $revision_id False to get the latest validated, integer to get a
	 * 		specific revision_id (used in some places such as the queue)
	 * @return null
	 */
	public function get_download($revision_id = false)
	{
		if ($this->download || ($this->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED && !$this->is_author && !$this->is_active_coauthor && !$this->type->acl_get('moderate') && !$this->type->acl_get('view')))
		{
			return;
		}

		if ($revision_id)
		{
			$revisions = array((int) $revision_id);
		}
		else
		{
			$sql = 'SELECT DISTINCT(phpbb_version_branch), MAX(revision_id) AS revision_id
				FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
				WHERE contrib_id = ' . (int) $this->contrib_id . '
					AND revision_validated = 1
				GROUP BY phpbb_version_branch';
			$result = phpbb::$db->sql_query($sql);
			$revisions = array();

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$revisions[(int) $row['phpbb_version_branch']] = (int) $row['revision_id'];
			}
			phpbb::$db->sql_freeresult($result);
		}

		if (!empty($revisions))
		{
			$sql = 'SELECT r.*, a.*
				FROM ' . TITANIA_REVISIONS_TABLE . ' r
				LEFT JOIN ' . TITANIA_ATTACHMENTS_TABLE . ' a
					ON (a.attachment_id = r.attachment_id)
				WHERE r.contrib_id = ' . (int) $this->contrib_id . '
					AND ' . phpbb::$db->sql_in_set('r.revision_id', $revisions) .
					(($revision_id === false) ? ' AND r.revision_status = ' . TITANIA_REVISION_APPROVED : '') . '
					AND revision_submitted = 1';
			$result = phpbb::$db->sql_query($sql);
			$revisions = array_flip($revisions);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$this->download[$revisions[$row['revision_id']]] = $row;
			}
			phpbb::$db->sql_freeresult($result);
			krsort($this->download);
		}
	}

	/**
	 * Fill categories with data from cache.
	 */
	public function fill_categories()
	{
		// Empty out category_data in case object is being reused via __set_array()
		$this->category_data = array();

		// Very unlikely to have no categories, but fill out default options just in case
		if (!$this->contrib_categories)
		{
			$this->get_options();
			return;
		}

		$contrib_categories = explode(',', $this->contrib_categories);

		$categories = titania::$cache->get_categories();
		foreach ($contrib_categories as $category_id)
		{
			$this->category_data[$category_id] = $categories[$category_id];
		}

		// Determine options inherited from categories.
		$this->get_options();
	}

	/**
	 * Get all options inherited from category options.
	 */
	public function get_options()
	{
		$this->options = array(
			'demo'			=> false,
			'all_versions'	=> false,
		);

		if (!$this->contrib_categories)
		{
			return;
		}

		$map = array(
			TITANIA_CAT_FLAG_DEMO 			=> 'demo',
			TITANIA_CAT_FLAG_ALL_VERSIONS	=> 'all_versions'
		);

		foreach ($this->category_data as $cat_id => $data)
		{
			foreach ($map as $flag => $option)
			{
				if ($this->category_data[$cat_id]['category_options'] & $flag)
				{
					$this->options[$option] = true;
				}
			}
		}
	}

	/**
	* Immediately increases the view counter for this contribution
	*
	* @return void
	*/
	public function increase_view_counter()
	{
		$sql = 'UPDATE ' . $this->sql_table . '
			SET contrib_views = contrib_views + 1
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		$this->contrib_views = $this->contrib_views + 1;
	}

	/**
	 * Passes details to the template
	 *
	 * @param bool $simple True to output a simpler version (on the non-main pages)
	 */
	public function assign_details($simple = false, $return = false)
	{
		$vars = array(
			// Contribution data
			'CONTRIB_NAME'					=> $this->contrib_name,
			'CONTRIB_DESC'					=> $this->generate_text_for_display(),
			'CONTRIB_VIEWS'					=> $this->contrib_views,
			'CONTRIB_UPDATE_DATE'			=> ($this->contrib_last_update) ? phpbb::$user->format_date($this->contrib_last_update) : '',
			'CONTRIB_STATUS'				=> $this->contrib_status,
			'CONTRIB_LIMITED_SUPPORT'		=> $this->contrib_limited_support,

			'CONTRIB_LOCAL_NAME'			=> $this->contrib_local_name,
			'CONTRIB_ISO_CODE'				=> $this->contrib_iso_code,

			'CONTRIB_RATING'				=> $this->contrib_rating,
			'CONTRIB_RATING_COUNT'			=> $this->contrib_rating_count,
			'CONTRIB_RATING_STRING'			=> ($this->rating) ? $this->rating->get_rating_string($this->get_url('rate')) : '',

			'L_ANNOUNCEMENT_TOPIC'			=> (titania::$config->support_in_titania) ? phpbb::$user->lang['ANNOUNCEMENT_TOPIC'] : phpbb::$user->lang['ANNOUNCEMENT_TOPIC_SUPPORT'],

			'U_VIEW_DEMO'					=> $this->contrib_demo,
			'S_INTEGRATE_DEMO'				=> $this->options['demo'],
		);

		// Ignore some stuff before it is submitted else we can cause an error
		if ($this->contrib_id)
		{
			foreach ($this->type->get_allowed_branches(true, false) as $branch => $name)
			{
				$release_topic_id = $this->get_release_topic_id($branch);

				if ($release_topic_id)
				{
					phpbb::$template->assign_block_vars('announce_topic', array(
						'URL'		=> phpbb::append_sid('viewtopic', 't=' . $release_topic_id),
						'BRANCH'	=> $name,
					));
				}
			}

			if (!empty($this->download))
			{
				$this->assign_download_details();
				$vars = array_merge($vars, array(
					//Download Data
					'CONTRIB_DOWNLOADS'				=> $this->contrib_downloads,
				));
			}

			if ($this->contrib_type == TITANIA_TYPE_BBCODE && !empty($this->download))
			{
				$download = reset($this->download);
				$demo_output = $download['revision_bbc_demo'];
				$demo_rendered = false;

				if ($download['revision_status'] == TITANIA_REVISION_APPROVED && !empty($demo_output))
				{

					$demo = $this->type->get_demo()->configure(
						$this->contrib_id,
						$download['revision_bbc_bbcode_usage'],
						$download['revision_bbc_html_replace']
					);
					$demo_output = $demo->get_demo($demo_output);
					unset($demo);
					$demo_rendered = true;
				}

				$vars = array_merge($vars, array(
					'CONTRIB_BBC_HTML_REPLACEMENT'	=> (isset($download['revision_bbc_html_replace'])) ? $download['revision_bbc_html_replace']: '',
					'CONTRIB_BBC_BBCODE_USAGE'		=> (isset($download['revision_bbc_bbcode_usage'])) ? $download['revision_bbc_bbcode_usage'] : '',
					'CONTRIB_BBC_HELPLINE'			=> (isset($download['revision_bbc_help_line'])) ? $download['revision_bbc_help_line'] : '',
					'CONTRIB_BBC_DEMO'				=> $demo_output,
					'S_CONTRIB_BBC_DEMO_RENDERED'	=> $demo_rendered,
				));
			}
			$use_queue = titania::$config->use_queue && $this->type->use_queue;
			$u_view_reports = $u_manage = $u_new_revision = $u_queue_discussion = false;

			if ($this->type->acl_get('moderate'))
			{
				$u_view_reports = $this->controller_helper->route(
					'phpbb.titania.manage.attention.redirect',
					array(
						'type'	=> TITANIA_CONTRIB,
						'id'	=> $this->contrib_id,
					)
				);
			}
			if ($this->is_manageable())
			{
				$u_manage = $this->get_url('manage');

				if (phpbb::$auth->acl_get('u_titania_contrib_submit'))
				{
					$u_new_revision = $this->get_url('revision');
				}

			}
			if ($use_queue && $this->is_manageable('queue_discussion'))
			{
				$u_queue_discussion = $this->get_url('queue_discussion');
			}

			$vars = array_merge($vars, array(
				'CONTRIB_TYPE'					=> $this->type->lang,
				'CONTRIB_TYPE_ID'				=> $this->contrib_type,
				'CONTRIB_TYPE_CLEAN'        	=> $this->type->url,

				'U_CONTRIB_MANAGE'				=> $u_manage,
				'U_NEW_REVISION'				=> $u_new_revision,
				'U_QUEUE_DISCUSSION'			=> $u_queue_discussion,
				'U_VIEW_CONTRIB'				=> $this->get_url(),

				'U_REPORT'						=> (phpbb::$user->data['is_registered']) ? $this->get_url('report') : '',
				'U_VIEW_REPORTS'				=> $u_view_reports,

				// Contribution Status
				'S_CONTRIB_NEW'					=> ($this->contrib_status == TITANIA_CONTRIB_NEW) ? true : false,
				'S_CONTRIB_VALIDATED'			=> ($this->contrib_status == TITANIA_CONTRIB_APPROVED) ? true : false,
				'S_CONTRIB_CLEANED'				=> ($this->contrib_status == TITANIA_CONTRIB_CLEANED) ? true : false,
				'S_CONTRIB_DOWNLOAD_DISABLED'	=> ($this->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED) ? true : false,
				'S_CONTRIB_HIDDEN'				=> ($this->contrib_status == TITANIA_CONTRIB_HIDDEN) ? true : false,
				'S_CONTRIB_DISABLED'			=> ($this->contrib_status == TITANIA_CONTRIB_DISABLED) ? true : false,

				'JS_CONTRIB_TRANSLATION'		=> !empty($this->contrib_iso_code) ? 'true' : 'false', // contrib_iso_code is a mandatory field and must be included with all translation contributions
			));
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $vars, $this);

		// Display real author
		if ($return)
		{
			$vars['AUTHOR_NAME_FULL'] = $this->author->get_username_string();
		}
		else
		{
			$this->author->assign_details();
		}

		if (!$simple && !$return)
		{
			$active_coauthors = $past_coauthors = array();
			$author_sort = function($a, $b) {
				return strcmp($a['AUTHOR_NAME'], $b['AUTHOR_NAME']);
			};

			// Display Co-authors
			foreach ($this->coauthors as $user_id => $row)
			{
				if ($row['active'])
				{
					$active_coauthors[] = $this->author->assign_details(true, $row);
				}
				else
				{
					$past_coauthors[] = $this->author->assign_details(true, $row);
				}
			}
			usort($active_coauthors, $author_sort);
			usort($past_coauthors, $author_sort);
			phpbb::$template->assign_block_vars_array('coauthors', $active_coauthors);
			phpbb::$template->assign_block_vars_array('past_coauthors', $past_coauthors);

			// Display Revisions and phpBB versions
			if (sizeof($this->revisions))
			{
				$revision = new titania_revision($this);
				$revision->contrib = $this;
				$phpbb_versions = array();
				foreach ($this->revisions as $revision_id => $row)
				{
					$revision->__set_array($row);
					$revision->phpbb_versions = (isset($row['phpbb_versions'])) ? $row['phpbb_versions'] : array();
					$revision->translations = (isset($row['translations'])) ? $revision->set_translations($row['translations']) : array();
					$revision->display('revisions', $this->type->acl_get('view'), $this->options['all_versions']);
					$phpbb_versions = array_merge($phpbb_versions, $revision->phpbb_versions);
				}
				unset($revision);

				$ordered_phpbb_versions = versions::order_phpbb_version_list_from_db(
					$this->cache,
					$phpbb_versions,
					$this->options['all_versions']
				);

				if (sizeof($ordered_phpbb_versions) == 1)
				{
					phpbb::$template->assign_vars(array(
						'PHPBB_VERSION'		=> $ordered_phpbb_versions[0],
					));
				}
				else
				{
					foreach ($ordered_phpbb_versions as $version_row)
					{
						phpbb::$template->assign_block_vars('phpbb_versions', array(
							'NAME'		=> $version_row,
						));
					}
				}
			}

			// Display Screenshots
			if ($this->screenshots->get_count())
			{
				$message = false;
				$this->screenshots->parse_attachments($message, false, false, 'screenshots', true);
			}

			// Display categories
			$category = new titania_category();
			foreach ($this->category_data as $category_row)
			{
				$category->__set_array($category_row);

				phpbb::$template->assign_block_vars('categories', $category->assign_display(true));
			}
		}

		if ($return)
		{
			return $vars;
		}

		phpbb::$template->assign_vars($vars);
	}

	/**
	* Assign download details.
	*
	* @return null
	*/
	public function assign_download_details()
	{
		$u_colorizeit_base = '';

		// ColorizeIt stuff
		if (strlen(titania::$config->colorizeit) && $this->has_colorizeit())
		{
			$u_colorizeit_base = 'http://' . titania::$config->colorizeit_url . '/custom/' .
				titania::$config->colorizeit . '.html?sample=' . $this->clr_sample->get_id();
		}

		foreach ($this->download as $download)
		{
			$vendor_version = $install_level = $install_time = $u_colorizeit = '';

			if (!empty($this->revisions[$download['revision_id']]['phpbb_versions']))
			{
				$vendor_version = $this->revisions[$download['revision_id']]['phpbb_versions'];
				$vendor_version = versions::order_phpbb_version_list_from_db(
					$this->cache,
					$vendor_version,
					$this->options['all_versions']
				);
				$vendor_version = $vendor_version[0];
			}

			if ($download['install_time'])
			{
				if ($download['install_time'] < 60)
				{
					$install_time = phpbb::$user->lang['INSTALL_LESS_THAN_1_MINUTE'];
				}
				else
				{
					$install_time = phpbb::$user->lang('INSTALL_MINUTES', (int) ($download['install_time'] / 60));
				}
			}
			if ($download['install_level'])
			{
				$install_level = phpbb::$user->lang['INSTALL_LEVEL_' . $download['install_level']];
			}
			if ($u_colorizeit_base && $download['revision_status'] == TITANIA_REVISION_APPROVED)
			{
				$u_colorizeit = $u_colorizeit_base . '&amp;id=' . $download['attachment_id'];
			}

			phpbb::$template->assign_block_vars('downloads', array(
				'NAME'			=> censor_text($download['revision_name']),
				'VERSION'		=> censor_text($download['revision_version']),
				'SIZE'			=> get_formatted_filesize($download['filesize']),
				'CHECKSUM'		=> $download['hash'],
				'LICENSE'		=> censor_text($download['revision_license']),
				'RELEASE_TIME'	=> ($download['validation_date']) ? phpbb::$user->format_date($download['validation_date']) : '',
				'PHPBB_VERSION'	=> $vendor_version,
				'INSTALL_LEVEL'	=> $install_level,
				'INSTALL_TIME'	=> $install_time,
				'U_DOWNLOAD'	=> ($download['attachment_id']) ? $this->controller_helper->route('phpbb.titania.download', array('id' => $download['attachment_id'])) : '',
				'U_COLORIZEIT'	=> $u_colorizeit,
			));
		}
	}

	/**
	* Build view URL for a contribution
	*
	* @param string $page The page we are on (Ex: faq/support/details)
	* @param array $parameters The parameters for the page
	*/
	public function get_url($page = '', $parameters = array())
	{
		$controller = 'phpbb.titania.contrib';

		switch ($page)
		{
			case 'revision' :
				$controller .= '.revision';

				if (isset($parameters['page']))
				{
					$controller .= '.' . $parameters['page'];
					unset($parameters['page']);
				}
			break;

			case 'posting' :
				$controller .= '.support.post_topic';
				unset($parameters['page']);
			break;

			case 'demo' :
				$controller .= '.demo';
				unset($parameters['page']);
			break;

			case 'manage_demo' :
				$controller .= '.manage.demo';
				unset($parameters['page']);
			break;

			case 'version_check' :
				$controller .= '.version_check';
			break;

			default :
				$parameters['page']	= $page;
		}

		$parameters += array(
			'contrib_type'	=> $this->type->url,
			'contrib'		=> $this->contrib_name_clean,
		);

		return $this->controller_helper->route($controller, $parameters);
	}

	/**
	* Get demo URL.
	*
	* @param int $branch			Branch - example: 30, 31
	* @param bool $integrated_url	Whether to return the integrated demo URL
	*	if it's supported.
	* @return string
	*/
	public function get_demo_url($branch, $integrated_url = false)
	{
		if (empty($this->contrib_demo))
		{
			return '';
		}
		$demos = json_decode($this->contrib_demo, true);

		if (empty($demos[$branch]))
		{
			return '';
		}
		else if ($integrated_url && $this->options['demo'])
		{
			$branch = (string) $branch;
			return $this->get_url('demo', array(
				'branch'	=> "{$branch[0]}.{$branch[1]}",
			));
		}
		return $demos[$branch];
	}

	/**
	* Get demo URL.
	*
	* @param int $branch		Branch - example: 30, 31
	* @return string
	*/
	public function set_demo_url($branch, $url)
	{
		if (!empty($this->contrib_demo))
		{
			$demos = json_decode($this->contrib_demo, true);
		}
		else
		{
			$demos = array();
		}
		$demos[$branch] = $url;
		$this->contrib_demo = json_encode($demos);
	}

	/**
	 * Submit data for storing into the database
	 * DO NOT USE THIS FUNCTION TO CHANGE THE STATUS ELSE THE AUTHORS CONTRIB COUNT WILL BE INCORRECT (use change_status function)!
	 */
	public function submit()
	{
		if (!$this->contrib_id)
		{
			// Make sure the author exists, if not we create one (do this before returning if not approved...else we need to duplicate this code in a bunch of places)
			$user_id = $this->contrib_user_id;
			$sql = 'SELECT user_id FROM ' . TITANIA_AUTHORS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
			phpbb::$db->sql_query($sql);
			if (!phpbb::$db->sql_fetchfield('user_id'))
			{
				$author = new titania_author($user_id);
				$author->submit();
			}
			phpbb::$db->sql_freeresult();
		}

		if (!$this->contrib_id && in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)))
		{
			// Increment the contrib counter
			$this->change_author_contrib_count($this->contrib_user_id);
		}

		// Clear the author contribs cache
		titania::$cache->reset_author_contribs($this->contrib_user_id);

		parent::submit();

		// Index!
		$this->index();

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Update the release topic for this contribution
	*/
	public function update_release_topic()
	{
		if ($this->type->forum_robot && $this->type->forum_database && $this->type->create_public)
		{
			titania::_include('functions_posting', 'phpbb_posting');

			// Get the latest download
			$this->get_download();

			// If there is not a download do not update.
			if (!$this->download)
			{
				return;
			}

			// Get the latest revision
			$this->get_revisions();

			// If there is not a revision do not update.
			if (!$this->revisions)
			{
				return;
			}

			$contrib_description = $this->contrib_desc;
			message::decode($contrib_description, $this->contrib_desc_uid);

			foreach ($this->download as $download)
			{
				$phpbb_version = $this->revisions[$download['revision_id']]['phpbb_versions'][0];
				$branch = (int) $phpbb_version['phpbb_version_branch'];

				if (empty($this->type->forum_database[$branch]))
				{
					continue;
				}

				$u_download = $this->controller_helper->route('phpbb.titania.download', array(
					'id' => $download['attachment_id']
				));

				// Global body and options
				$body = phpbb::$user->lang($this->type->create_public,
					$this->contrib_name,
					$this->path_helper->strip_url_params($this->author->get_url(), 'sid'),
					users_overlord::get_user($this->author->user_id, '_username'),
					$contrib_description,
					$download['revision_version'],
					$this->path_helper->strip_url_params($u_download, 'sid'),
					$download['real_filename'],
					get_formatted_filesize($download['filesize']),
					$this->path_helper->strip_url_params($this->get_url(), 'sid'),
					$this->path_helper->strip_url_params($this->get_url('support'), 'sid'),
					$phpbb_version['phpbb_version_branch'][0] . '.' . $phpbb_version['phpbb_version_branch'][1] . '.' .$phpbb_version['phpbb_version_revision']
				);

				$options = array(
					'poster_id'		=> $this->type->forum_robot,
					'forum_id' 		=> $this->type->forum_database[$branch],
				);
				$release_topic_id = (int) $this->get_release_topic_id($branch);

				if ($release_topic_id)
				{
					// We edit the first post of contrib release topic
					$options_edit = array(
						'topic_id'				=> $release_topic_id,
						'topic_title'			=> $this->contrib_name,
						'post_text'				=> $body,
					);
					$options_edit = array_merge($options_edit, $options);
					phpbb_posting('edit_first_post', $options_edit);
				}
				else
				{
					// We create a new topic in database
					$options_post = array(
						'topic_title'			=> $this->contrib_name,
						'post_text'				=> $body,
						//'topic_status'			=> (titania::$config->support_in_titania) ? ITEM_LOCKED : ITEM_UNLOCKED,
					);
					$options_post = array_merge($options_post, $options);
					$release_topic_id = phpbb_posting('post', $options_post);
					$this->set_release_topic_id($branch, $release_topic_id);
				}
			}
		}
	}

	/**
	* Get release topic id for a particular branch.
	*
	* @param int $branch		30|31 .. etc.
	* @return int
	*/
	public function get_release_topic_id($branch)
	{
		if (empty($this->contrib_release_topic_id))
		{
			return 0;
		}
		$topics = json_decode($this->contrib_release_topic_id, true);

		return (isset($topics[$branch])) ? (int) $topics[$branch] : 0;
	}

	/**
	* Set release topic id for a particular branch.
	*
	* @param int $branch		30|31 .. etc.
	* @return null
	*/
	public function set_release_topic_id($branch, $topic_id)
	{
		$topics = (empty($this->contrib_release_topic_id)) ? array() : json_decode($this->contrib_release_topic_id, true);
		$topics[(int) $branch] = (int) $topic_id;
		$topics = json_encode($topics);

		$sql = 'UPDATE ' . $this->sql_table . '
			SET contrib_release_topic_id = "' . phpbb::$db->sql_escape($topics) . '"
			WHERE contrib_id = ' . (int) $this->contrib_id;
		phpbb::$db->sql_query($sql);
		$this->__set('contrib_release_topic_id', $topics);
	}

	/**
	* Reply to the release topic
	*
	* @param int $branch	Specific branch release topic to reply to
	* @param string $reply Message to reply to the topic with
	* @param array $options Any additional options for the reply
	*/
	public function reply_release_topic($branch, $reply, $options = array())
	{
		$release_topic_id = $this->get_release_topic_id($branch);

		if (!$release_topic_id)
		{
			return;
		}

		titania::_include('functions_posting', 'phpbb_posting');

		$options_reply = array_merge($options, array(
			'topic_id'				=> $release_topic_id,
			'topic_title'			=> 'Re: ' . $this->contrib_name,
			'post_text'				=> $reply,
		));
		phpbb_posting('reply', $options_reply);
	}

	public function report($reason = '', $notify_reporter = false, $attention_type = TITANIA_ATTENTION_REPORTED)
	{
		// Setup the attention object and submit it
		$attention = new titania_attention;
		$attention->__set_array(array(
			'attention_type'		=> $attention_type,
			'attention_object_type'	=> TITANIA_CONTRIB,
			'attention_object_id'	=> $this->contrib_id,
			'attention_poster_id'	=> $this->contrib_user_id,
			'attention_post_time'	=> $this->contrib_last_update,
			'attention_url'			=> $this->get_url(),
			'attention_title'		=> $this->contrib_name,
			'attention_description'	=> $reason,
			'notify_reporter'		=> $notify_reporter,
		));
		$attention->submit();
	}

	/**
	 * Change the status of this contrib item.
	 * YOU MUST USE THIS FUNCTION TO CHANGE THE STATUS ELSE THE AUTHORS CONTRIB COUNT WILL BE INCORRECT!
	 *
	 * @param int $new_status
	 */
	public function change_status($new_status)
	{
		$new_status = (int) $new_status;
		$old_status = $this->contrib_status;

		if ($old_status == $new_status)
		{
			return;
		}

		$this->contrib_status = $new_status;

		$sql_ary = array(
			'contrib_status'	=> $this->contrib_status,
		);

		// Grab the current authors
		$author_list = array($this->contrib_user_id);
		$sql = 'SELECT user_id
			FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE contrib_id = ' . (int) $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$author_list[] = $row['user_id'];
		}
		phpbb::$db->sql_freeresult($result);

		// First we are essentially resetting the contrib and category counts back to "New"
		switch ($old_status)
		{
			case TITANIA_CONTRIB_APPROVED :
				repository::trigger_cron($this->config);
			case TITANIA_CONTRIB_DOWNLOAD_DISABLED :
				// Decrement the count for the authors
				$this->change_author_contrib_count($author_list, '-', true);

				// Decrement the category count
				$this->update_category_count('-', true);
			break;
		}

		// Now, for the new status, if approved, we increment the contrib and category counts
		switch ($this->contrib_status)
		{
			case TITANIA_CONTRIB_APPROVED :
				repository::trigger_cron($this->config);
			case TITANIA_CONTRIB_DOWNLOAD_DISABLED :
				// Increment the count for the authors
				$this->change_author_contrib_count($author_list);

				// Increment the category count
				$this->update_category_count();

				$sql_ary['contrib_last_update'] = titania::$time;
			break;
		}

		$sql = 'UPDATE ' . $this->sql_table . '
			SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Index!
		$this->index();

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	* Change the permalink to the contribution
	* Do not change it yourself, always use this function to do so
	*
	* @param string $new_permalink
	*/
	public function change_permalink($new_permalink)
	{
		$old_permalink = $this->contrib_name_clean;
		$new_permalink = url::generate_slug($new_permalink);

		if ($this->validate_permalink($new_permalink, $old_permalink))
		{
			return false;
		}

		$this->contrib_name_clean = $new_permalink;

		$params = serialize(array(
			'contrib_type'	=> $this->type->url,
			'contrib'		=> $this->contrib_name_clean,
		));

		// Attention items
		$sql = 'UPDATE ' . TITANIA_ATTENTION_TABLE . '
			SET attention_url = "' . phpbb::$db->sql_escape($params) . '"
			WHERE attention_object_type = ' . TITANIA_CONTRIB . '
				AND attention_object_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Update the topics/posts under this
		$topics = $post_ids = array();
		$topic_where_sql = ' WHERE ' . phpbb::$db->sql_in_set('topic_type', array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION)) . '
				AND parent_id = ' . $this->contrib_id;

		$topic = new titania_topic;
		$sql = 'SELECT *
			FROM ' . TITANIA_TOPICS_TABLE .
			$topic_where_sql;
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$id = (int) $row['topic_id'];
			$topics[$id] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		if (sizeof($topics))
		{
			$post = new titania_post;
			$post->topic = $topic;
			$sql = 'SELECT *
				FROM ' . TITANIA_POSTS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('topic_id', array_keys($topics));
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$topic->__set_array($topics[$row['topic_id']]);
				$post->__set_array($row);

				$post->post_url = $params;
				$post_ids[] = (int) $row['post_id'];

				// Need to reindex as well...
				$post->index();
			}
			phpbb::$db->sql_freeresult($result);
			unset($topic, $post);

			// Update the posts table
			$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
				SET post_url = "' . phpbb::$db->sql_escape($params) . '"
				WHERE ' . phpbb::$db->sql_in_set('topic_id', array_keys($topics));
			phpbb::$db->sql_query($sql);

			// Update the topics table
			$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
				SET topic_url = "' . phpbb::$db->sql_escape($params) . '"' .
				$topic_where_sql;
			phpbb::$db->sql_query($sql);

			if (sizeof($post_ids))
			{
				// On to attention items for posts
				$sql = 'SELECT attention_id, attention_object_id
					FROM ' . TITANIA_ATTENTION_TABLE . '
					WHERE attention_object_type = ' . TITANIA_POST . '
						AND ' . phpbb::$db->sql_in_set('attention_object_id', $post_ids);
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . TITANIA_ATTENTION_TABLE . '
						SET attention_url = "' . phpbb::$db->sql_escape($params) . '"
						WHERE attention_id = ' . $row['attention_id'];
					phpbb::$db->sql_query($sql);
				}
				phpbb::$db->sql_freeresult($result);
			}
		}

		// Finally update the contrib_name_clean
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_name_clean = \'' . phpbb::$db->sql_escape($this->contrib_name_clean) . '\'
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	* Validate contribution settings.
	*
	* @param array $contrib_categories		Contribution categories.
	* @param array $authors					Array in the form of array('author' => array(username => user_id),
	*	'active_coauthors' => array(...), 'nonactive_coauthors' => array(...), 'missing' =>
		array('active_coauthors' => array(username => username)).
	* @param array $custom_fields			Custom field values.
	* @param string $old_permalink			Old permalink. Defaults to empty string.
	*
	* @return array Returns array containing any errors found.
	*/
	public function validate($contrib_categories = array(), $authors, $custom_fields, $old_permalink = '')
	{
		phpbb::$user->add_lang('ucp');

		$error = array();

		if (utf8_clean_string($this->contrib_name) == '')
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_NAME'];
		}

		if (!$this->contrib_type)
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_TYPE'];
		}
		else
		{
			// Check for a valid type
			$valid_type = false;
			foreach ($this->types->get_all() as $type_id => $class)
			{
				if (!$class->acl_get('submit'))
				{
					continue;
				}

				if ($this->contrib_type == $type_id)
				{
					$valid_type = true;
					break;
				}
			}

			if (!$valid_type)
			{
				$error[] = phpbb::$user->lang['EMPTY_CONTRIB_TYPE'];
			}
			else
			{
				$this->set_type($this->contrib_type);
				$error = array_merge($error, $this->type->validate_contrib_fields($custom_fields));

				if (!$this->contrib_name_clean)
				{
					// If they leave it blank automatically create it
					$this->generate_permalink();
				}

				if (($permalink_error = $this->validate_permalink($this->contrib_name_clean, $old_permalink)) !== false)
				{
					$error[] = $permalink_error;
				}
			}

			if (!$contrib_categories)
			{
				$error[] = phpbb::$user->lang['EMPTY_CATEGORY'];
			}
			else
			{
				$categories	= titania::$cache->get_categories();
				$category = new \titania_category;

				foreach ($contrib_categories as $category_id)
				{
					if (!isset($categories[$category_id]))
					{
						$error[] = phpbb::$user->lang['NO_CATEGORY'];
					}
					else if ($categories[$category_id]['category_type'] != $this->contrib_type)
					{
						$error[] = phpbb::$user->lang['WRONG_CATEGORY'];
					}

					if ($valid_type)
					{
						$category->__set_array($categories[$category_id]);

						if ($category->is_option_set('team_only') && !$this->type->acl_get('moderate'))
						{
							$error[] = phpbb::$user->lang['CATEGORY_NOT_ALLOWED'];
						}
					}
				}
			}
		}

		if (!$this->contrib_desc)
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_DESC'];
		}

		$author = key($authors['author']);
		$missing_coauthors = array_merge($authors['missing']['active_coauthors'], $authors['missing']['nonactive_coauthors']);

		if (!empty($missing_coauthors))
		{
			$error[] = phpbb::$user->lang('COULD_NOT_FIND_USERS', phpbb_generate_string_list($missing_coauthors, phpbb::$user));
		}
		$duplicates = array_intersect($authors['active_coauthors'], $authors['nonactive_coauthors']);

		if (!empty($duplicates))
		{
			$error[] = phpbb::$user->lang('DUPLICATE_AUTHORS', phpbb_generate_string_list(array_keys($duplicates), phpbb::$user));
		}

		if (isset($authors['active_coauthors'][$author]) || isset($authors['nonactive_coauthors'][$author]))
		{
			$error[] = phpbb::$user->lang['CANNOT_ADD_SELF_COAUTHOR'];
		}

		if (!empty($authors['missing']['new_author']))
		{
			$error[] = phpbb::$user->lang('CONTRIB_CHANGE_OWNER_NOT_FOUND', key($authors['missing']['new_author']));
		}

		if ($this->contrib_demo !== '')
		{
			$demos = json_decode($this->contrib_demo, true);

			foreach ($demos as $url)
			{
				if ($url !== '' && !preg_match('#^http[s]?://(.*?\.)*?[a-z0-9\-]{2,4}#i', $url))
				{
					$error[] = phpbb::$user->lang('FIELD_INVALID_URL', phpbb::$user->lang['CONTRIB_DEMO']);
					break;
				}
			}
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $error, $this);

		return $error;
	}

	/**
	* Automatically generate permalink value from contribution name.
	*
	* @return null
	*/
	public function generate_permalink()
	{
		$clean_name = url::generate_slug($this->contrib_name);
		$append = '';
		$i = 2;
		while ($this->permalink_exists($clean_name . $append))
		{
			$append = '_' . $i;
			$i++;
		}
		$this->contrib_name_clean = $clean_name . $append;
	}

	/*
	 * Validate a contrib permalink
	 *
	 * @param string $permalink			New permalink.
	 * @param string $old_permalink		Old permalink.
	 *
	 * @return bool|string Returns error string if error found. Otherwise returns false.
	 */
	public function validate_permalink($permalink, $old_permalink)
	{
		if (url::generate_slug($permalink) !== $permalink)
		{
			return phpbb::$user->lang('INVALID_PERMALINK', url::generate_slug($permalink));
		}
		else if ($permalink == '' || $permalink !== $old_permalink && $this->permalink_exists($permalink))
		{
			return phpbb::$user->lang['CONTRIB_NAME_EXISTS'];
		}
		return false;
	}

	/**
	* Check whether a contribution uses the given permalink.
	*
	* @param string $permalink	Permalink
	* @return bool Returns true if a contribution was found.
	*/
	public function permalink_exists($permalink)
	{
		$sql = 'SELECT contrib_id
			FROM ' . $this->sql_table . "
			WHERE contrib_name_clean = '" . phpbb::$db->sql_escape($permalink) . "'
				AND contrib_type = " . (int) $this->contrib_type;
		$result = phpbb::$db->sql_query($sql);
		$contrib_id = phpbb::$db->sql_fetchfield('contrib_id');
		phpbb::$db->sql_freeresult($result);

		return !empty($contrib_id);
	}

	/**
	* Get author id's and usernames from supplied usernames.
	*
	* @param array $authors	Array in the form of array('active' => 'usernames', nonactive => 'usernames')
	*	with the usernames separated by a new line.
	* @return array Returns array in the form of
	*	array('active' => array(username => user_id), 'nonactive' => (...), 'missing' => array('active' => username, ...)
	*/
	public function get_authors_from_usernames($authors)
	{
		$result = array('missing' => array());

		foreach ($authors as $group => $users)
		{
			$users = user_helper::get_user_ids_from_list($this->db, $users);
			$result[$group] = $users['ids'];
			$result['missing'][$group] = $users['missing'];
		}

		return $result;
	}

	/**
	* Set coauthors for contrib item
	*
	* @param array $active array of active coauthor user_ids
	* @param array $nonactive array of nonactive coauthor user_ids
	* @param bool $reset true to reset the coauthors and only add the ones given, false to keep past coauthors and just add some new ones
	*
	* @todo update $this->coauthors
	*/
	public function set_coauthors($active, $nonactive = array(), $reset = false)
	{
		if ($reset)
		{
			// Grab the current co-authors
			$current_list = array();
			$sql = 'SELECT user_id
				FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
				WHERE contrib_id = ' . (int) $this->contrib_id;
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$current_list[] = $row['user_id'];

				// reset each of the authors cached contrib list
				titania::$cache->reset_author_contribs($row['user_id']);
			}
			phpbb::$db->sql_freeresult($result);

			if (sizeof($current_list))
			{
				$this->change_author_contrib_count($current_list, '-');

				$sql = 'DELETE FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
					WHERE contrib_id = ' . (int) $this->contrib_id;
				phpbb::$db->sql_query($sql);
			}
		}

		if (sizeof($active))
		{
			// First check that each author has a author row.
			$this->validate_author_row($active);

			$sql_ary = array();
			foreach ($active as $user_id)
			{
				$sql_ary[] = array(
					'contrib_id'	=> $this->contrib_id,
					'user_id'		=> $user_id,
					'active'		=> true,
				);

				// reset each of the authors cached contrib list
				titania::$cache->reset_author_contribs($user_id);
			}

			phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_COAUTHORS_TABLE, $sql_ary);

			// Increment the contrib counter
			$this->change_author_contrib_count($active);
		}

		if (sizeof($nonactive))
		{
			// First check that each author has a author row.
			$this->validate_author_row($nonactive);

			$sql_ary = array();
			foreach ($nonactive as $user_id)
			{
				$sql_ary[] = array(
					'contrib_id'	=> $this->contrib_id,
					'user_id'		=> $user_id,
					'active'		=> false,
				);

				// reset each of the authors cached contrib list
				titania::$cache->reset_author_contribs($user_id);
			}

			phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_COAUTHORS_TABLE, $sql_ary);

			// Increment the contrib counter
			$this->change_author_contrib_count($nonactive);
		}
	}

	/**
	 * Check that each contributor has a author row.
	 * If not create one.
	 *
	 * $author_arr[] = array(
	 * 	'username' => 'user_id', // both are strings.
	 * );
	 *
	 * @param array $author_arr, array with contributor data from set_coauthors()
	 */
	public function validate_author_row($author_arr)
	{
		// Always make sure that we actually got some data to work with...
		if (empty($author_arr) || !is_array($author_arr))
		{
			return;
		}

		$sql = 'SELECT user_id FROM ' . TITANIA_AUTHORS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('user_id', $author_arr);
		$result = phpbb::$db->sql_query($sql);

		$existing = array();
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$existing[] = (int) $row['user_id'];
		}
		phpbb::$db->sql_freeresult($result);

		if (sizeof($existing) == sizeof($author_arr))
		{
			// All co-authors found, we are done.
			return;
		}

		$sql_ary = array();
		foreach ($author_arr as $username => $user_id)
		{
			if (!in_array($user_id, $existing))
			{
				// This author needs to be created.
				$sql_ary[] = array(
					'user_id'		=> $user_id,
					'author_desc' => '',
				);
			}
		}

		if (!empty($sql_ary))
		{
			phpbb::$db->sql_multi_insert(TITANIA_AUTHORS_TABLE, $sql_ary);
		}
	}

	/*
	 * Set a new contrib_user_id for the current contribution
	 *
	 * @param int $user_id The new user_id that will be the owner
	 */
	public function set_contrib_user_id($user_id)
	{
		if ($this->contrib_user_id == $user_id)
		{
			return;
		}

		$this->validate_author_row(array($user_id));

		// Delete them from the co-authors list if they are in it...
		$sql = 'SELECT COUNT(contrib_id) FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . '
				AND user_id = ' . (int) $user_id;
		$result = phpbb::$db->sql_query($sql);
		if (phpbb::$db->sql_fetchrow($result))
		{
			$sql = 'DELETE FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
				WHERE contrib_id = ' . $this->contrib_id . '
					AND user_id = ' . (int) $user_id;
			phpbb::$db->sql_query($sql);
		}
		else
		{
			// Increment the contrib counter for the new owner
			$this->change_author_contrib_count($user_id);
		}
		phpbb::$db->sql_freeresult($result);

		// Update the data for this contrib item
		$sql = 'UPDATE ' . $this->sql_table . '
			SET contrib_user_id = ' . (int) $user_id . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Reset the author contribs that are cached for the new owner
		titania::$cache->reset_author_contribs($user_id);

		// Decrement the contrib counter for the old owner (setting as a co-author increments it again)
		$this->change_author_contrib_count($this->contrib_user_id, '-');

		// Set old user as previous contributor
		$this->set_coauthors(array(), array($this->contrib_user_id));

		$this->contrib_user_id = $user_id;
	}

	/**
	* Increment the contrib count for an author (also verifies that there is a row in the authors table)
	* Always use this when updating the count for an author!
	*
	* @param int|array $user_id
	* @param string action + or -
	* @param bool $force Ignore the check on require_validation, contrib_status (DO NOT USE UNLESS YOU HAVE A VERY GOOD REASON; should only be required by the update_status function)
	*/
	private function change_author_contrib_count($user_id, $action = '+', $force = false)
	{
		if (is_array($user_id))
		{
			foreach ($user_id as $uid)
			{
				$this->change_author_contrib_count($uid, $action, $force);
			}
			return;
		}

		// Don't change if it's not approved
		if ($force == false && (!in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))))
		{
			return;
		}

		$user_id = (int) $user_id;
		$action = ($action == '-') ? '-' : '+';

		// Increment/Decrement the contrib counter for the new owner
		$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . "
			SET author_contribs = author_contribs $action 1" .
				((isset($this->type->author_count)) ? ', ' . $this->type->author_count . ' = ' . $this->type->author_count . " $action 1" : '') . "
			WHERE user_id = $user_id " .
				(($action == '-') ? 'AND author_contribs > 0' : '');
		phpbb::$db->sql_query($sql);


		if (!phpbb::$db->sql_affectedrows() && $action == '+')
		{
			$author = new titania_author($user_id);
			$author->author_contribs = 1;

			if (isset($this->type->author_count))
			{
				$author->{$this->type->author_count} = 1;
			}

			$author->submit();
		}
	}

	/**
	* Set the relations between contribs and categories
	*
	* @param array $contrib_categories		Categories to put the contribution in
	* @param bool $protect_team_only		Whether to protect "Team only" categories.
	*	If true, existing categories that are "Team only" and are not part of $contrib_categories
	*	will be preserved.
	*
	* @return null
	*/
	public function put_contrib_in_categories($contrib_categories = array(), $protect_team_only = true)
	{
		if (!$this->contrib_id)
		{
			return;
		}

		$protected_categories = array();
		$exclude_sql = '';

		if ($protect_team_only && !empty($this->category_data))
		{
			$category = new \titania_category;

			foreach ($this->category_data as $row)
			{
				$category->__set_array($row);

				if ($category->is_option_set('team_only'))
				{
					$protected_categories[] = (int) $category->category_id;
				}
			}
		}

		if (!empty($protected_categories))
		{
			$exclude_sql = 'AND ' . phpbb::$db->sql_in_set('category_id', $protected_categories, true);
		}

		// Resync the count
		$this->update_category_count('-');

		// Remove them from the old categories
		$sql = 'DELETE
			FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . "
				$exclude_sql";
		phpbb::$db->sql_query($sql);

		if (!sizeof($contrib_categories))
		{
			return;
		}

		$sql_ary = array();
		foreach ($contrib_categories as $category_id)
		{
			$sql_ary[] = array(
				'contrib_id' 	=> $this->contrib_id,
				'category_id'	=> $category_id,
			);
		}
		phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $sql_ary);

		$this->contrib_categories = implode(',', array_merge($contrib_categories, $protected_categories));
		$this->fill_categories();
		// Resync the count
		$this->update_category_count();
	}

	/**
	* Resync the category counts
	*
	* @param string $dir + or -
	*/
	public function update_category_count($dir = '+', $force = false)
	{
		if (!in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) && !$force)
		{
			return;
		}

		$categories = array();
		$sql = 'SELECT category_id FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$categories[] = $row['category_id'];

			$parents = titania::$cache->get_category_parents($row['category_id']);
			foreach ($parents as $parent)
			{
				$categories[] = $parent['category_id'];
			}
		}
		phpbb::$db->sql_freeresult($result);

		if (sizeof($categories))
		{
			$categories = array_unique($categories);

			$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
				SET category_contribs = category_contribs ' . (($dir == '+') ? '+' : '-') . ' 1
				WHERE ' . phpbb::$db->sql_in_set('category_id', array_map('intval', $categories));
			phpbb::$db->sql_query($sql);

			titania::$cache->destroy('_titania_categories');
		}
	}

	/**
	* Check if ColorizeIt is available
	*/
	public function has_colorizeit($force_update = false)
	{
	    if ($force_update || !$this->clr_sample)
	    {
	        // get sample id from database
			$operator = phpbb::$container->get('phpbb.titania.attachment.operator');
			$attachments = $operator
				->configure(TITANIA_CLR_SCREENSHOT, $this->contrib_id)
				->load()
				->get_all()
			;
			$this->clr_sample = array_shift($attachments);
	    }
	    return $this->clr_sample && strlen($this->contrib_clr_colors) > 0;
	}

	/**
	* Delete this contribution
	*/
	public function delete()
	{
		// Delete Revisions
		$revision = new titania_revision($this);
		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$revision->__set_array($row);

			$revision->delete();
		}
		phpbb::$db->sql_freeresult($result);

		// Delete Support/Discussion/Queue Discussion Topics
		$topic = new titania_topic;
		$sql = 'SELECT * FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('topic_type', array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION)) . '
				AND parent_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$topic->__set_array($row);

			$topic->delete();
		}
		phpbb::$db->sql_freeresult($result);

		// Change the status to new (handles resetting counts)
		$this->change_status(TITANIA_CONTRIB_NEW);

		// Remove any attention items
		$sql = 'DELETE FROM ' . TITANIA_ATTENTION_TABLE . '
			WHERE attention_object_type = ' . TITANIA_CONTRIB . '
				AND attention_object_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Delete the release topic
		if ($this->contrib_release_topic_id)
		{
			phpbb::_include('functions_admin', 'delete_topics');

			delete_topics('topic_id', $this->contrib_release_topic_id);
		}

		// Delete from categories
		$this->update_category_count('-');
		$sql = ' DELETE FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		repository::trigger_cron($this->config);

		// Self delete
		parent::delete();
	}

	/**
	* Get the branch and status of active revisions in the queue.
	*
	* @return array Returns array in form of array(array(branch => status))
	*/
	public function in_queue()
	{
		if (!titania::$config->use_queue || !$this->type->use_queue)
		{
			return array();
		}

		$sql = 'SELECT DISTINCT q.revision_id, rp.phpbb_version_branch, q.queue_status, q.queue_tested
			FROM ' . TITANIA_QUEUE_TABLE . ' q, ' .
				TITANIA_REVISIONS_PHPBB_TABLE . ' rp
			WHERE q.contrib_id = ' . (int) $this->contrib_id . '
				AND q.queue_status > 0
				AND q.revision_id = rp.revision_id';
		$result = phpbb::$db->sql_query($sql);
		$in_queue = array();

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$in_queue[(int) $row['phpbb_version_branch']] = array_map('intval', $row);
		}
		phpbb::$db->sql_freeresult();

		return $in_queue;
	}

	/**
	* Index the contribution
	*/
	public function index()
	{
		if (!sizeof($this->revisions))
		{
			$this->get_revisions();
		}

		$phpbb_versions = array();
		foreach ($this->revisions as $revision)
		{
			if ($revision['revision_status'] == TITANIA_REVISION_APPROVED)
			{
				$phpbb_versions = array_merge($phpbb_versions, $revision['phpbb_versions']);
			}
		}

		$phpbb_versions = array_unique(versions::order_phpbb_version_list_from_db($this->cache, $phpbb_versions));

		$data = array(
			'title'				=> $this->contrib_name,
			'text'				=> $this->contrib_desc,
			'text_uid'			=> $this->contrib_desc_uid,
			'text_bitfield'		=> $this->contrib_desc_bitfield,
			'text_options'		=> $this->contrib_desc_options,
			'author'			=> $this->contrib_user_id,
			'date'				=> $this->contrib_last_update,
			'url'				=> serialize(array(
				'contrib_type'	=> $this->type->url,
				'contrib'		=> $this->contrib_name_clean,
			)),
			'approved'			=> (in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))) ? true : false,
			'categories'		=> explode(',', $this->contrib_categories),
			'phpbb_versions' 	=> $phpbb_versions,
		);

		$this->search_manager->index(TITANIA_CONTRIB, $this->contrib_id, $data);
	}

	/**
	* Set custom field values. These should already be validated.
	*
	* @param array $values		Array in form of array(field_name => field_value)
	* @return null
	*/
	public function set_custom_fields($values)
	{
		foreach ($this->type->contribution_fields as $name => $field)
		{
			if (!isset($values[$name]) || ($this->contrib_id && !$field['editable']))
			{
				continue;
			}
			$this->__set($name, $values[$name]);
		}
	}

	/**
	* Set custom field values.
	*
	* @returns array Array in form of array(field_name => field_value)
	*/
	public function get_custom_fields()
	{
		$values = array();

		foreach ($this->type->contribution_fields as $name => $field)
		{
			$values[$name] = $this->__get($name);
		}
		return $values;
	}
}
