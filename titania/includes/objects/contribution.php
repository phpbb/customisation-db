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

if (!class_exists('titania_message_object'))
{
	require TITANIA_ROOT . 'includes/core/object_message.' . PHP_EXT;
}

/**
 * Class to abstract contributions.
 * @package Titania
 */
class titania_contribution extends titania_message_object
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
	public $screenshots;

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

			'contrib_demo'					=> array('default' => ''),

			'contrib_status'				=> array('default' => TITANIA_CONTRIB_NEW),

			'contrib_user_id'				=> array('default' => 0),

			'contrib_downloads'				=> array('default' => 0),
			'contrib_views'					=> array('default' => 0),

			'contrib_visible'				=> array('default' => 0),

			'contrib_rating'				=> array('default' => 0.0),
			'contrib_rating_count'			=> array('default' => 0),

			// Last time the contrib item was updated (created or added a new revision, etc).  Used for tracking
			'contrib_last_update'			=> array('default' => titania::$time),

			'contrib_release_topic_id'		=> array('default' => 0),

			// Number of FAQ items (titania_count format)
			'contrib_faq_count'				=> array('default' => ''),

			// Translation items
			'contrib_iso_code'				=> array('default' => ''),
			'contrib_local_name'			=> array('default' => ''),
			
			// ColorizeIt stuff
			'contrib_clr_colors'            => array('default' => ''),
		));

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $this);
	}

	/**
	 * Load the contrib
	 *
	 * @param int|string $contrib The contrib item (contrib_name_clean, contrib_id)
	 *
	 * @return bool True if the contrib exists, false if not
	 */
	public function load($contrib)
	{
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
			$sql_ary['WHERE'] = 'contrib_name_clean = \'' . phpbb::$db->sql_escape(utf8_clean_string($contrib)) . '\'';
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

		if (in_array($this->contrib_status, array(TITANIA_CONTRIB_HIDDEN, TITANIA_CONTRIB_DISABLED)) && !($this->is_author ||$this->is_active_coauthor || phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[$this->contrib_type]->acl_get('moderate')))
		{
			// Hide hidden and disabled contribs for non-(authors/moderators)
			return false;
		}

		return true;
	}

	/**
	 * Get the rating as an object
	 *
	 * @return titania_rating
	 */
	public function get_screenshots()
	{
		if ($this->screenshots)
		{
			return $this->screenshots;
		}

		$this->screenshots = new titania_attachment(TITANIA_SCREENSHOT, $this->contrib_id);
		$this->screenshots->load_attachments();

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
		$this->rating->load();

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
		if (sizeof($this->revisions) || ($this->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED && !$this->is_author && !$this->is_active_coauthor && !titania_types::$types[$this->contrib_type]->acl_get('moderate') && !titania_types::$types[$this->contrib_type]->acl_get('view')))
		{
			return;
		}

		// Can they view unapproved revisions?  Yes if validation not required, is author, is active coauthor, can view validation queue or can moderate this contribution
		$can_view_unapproved = (!titania::$config->require_validation || !titania_types::$types[$this->contrib_type]->require_validation || $this->is_author || $this->is_active_coauthor) ? true : false;
		$can_view_unapproved = ($can_view_unapproved || titania_types::$types[$this->contrib_type]->acl_get('view')) ? true : false;
		$can_view_unapproved = ($can_view_unapproved || titania_types::$types[$this->contrib_type]->acl_get('moderate')) ? true : false;

		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id .
				((!$can_view_unapproved) ? ' AND revision_status = ' . TITANIA_REVISION_APPROVED : '') . '
				AND revision_submitted = 1
			ORDER BY revision_id DESC';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->revisions[$row['revision_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		if (sizeof($this->revisions))
		{
			// Get translations
			$sql = 'SELECT * FROM ' . TITANIA_ATTACHMENTS_TABLE . '
				WHERE object_type = ' . TITANIA_TRANSLATION . '
					AND is_orphan = 0
					AND ' . phpbb::$db->sql_in_set('object_id', array_map('intval', array_keys($this->revisions)));
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$this->revisions[$row['object_id']]['translations'][] = $row;
			}
			phpbb::$db->sql_freeresult($result);

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
	 * Get the latest revision (to download)
	 * Stored in $this->download; only gets the latest validated (if validation is required)
	 *
	 * @param bool|int $revision_id False to get the latest validated, integer to get a specific revision_id (used in some places such as the queue)
	 */
	public function get_download($revision_id = false)
	{
		if ($this->download || ($this->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED && !$this->is_author && !$this->is_active_coauthor && !titania_types::$types[$this->contrib_type]->acl_get('moderate') && !titania_types::$types[$this->contrib_type]->acl_get('view')))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_ATTACHMENTS_TABLE . ' a
			WHERE r.contrib_id = ' . $this->contrib_id . '
				AND a.attachment_id = r.attachment_id ' .
				((titania::$config->require_validation && titania_types::$types[$this->contrib_type]->require_validation && $revision_id === false) ? ' AND r.revision_status = ' . TITANIA_REVISION_APPROVED : '') .
				(($revision_id !== false) ? ' AND r.revision_id = ' . (int) $revision_id : '') . '
				AND revision_submitted = 1
			ORDER BY r.revision_id DESC';
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$this->download = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
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
		$install_time = false;
		if (isset($this->download['install_time']) && $this->download['install_time'] > 0)
		{
			if ($this->download['install_time'] < 60)
			{
				$install_time = phpbb::$user->lang['INSTALL_LESS_THAN_1_MINUTE'];
			}
			else
			{
				$install_time = phpbb::$user->lang('INSTALL_MINUTES', (int) ($this->download['install_time'] / 60));
			}
		}

		$vars = array(
			// Contribution data
			'CONTRIB_NAME'					=> $this->contrib_name,
			'CONTRIB_DESC'					=> $this->generate_text_for_display(),
			'CONTRIB_VIEWS'					=> $this->contrib_views,
			'CONTRIB_UPDATE_DATE'			=> ($this->contrib_last_update) ? phpbb::$user->format_date($this->contrib_last_update) : '',
			'CONTRIB_STATUS'				=> $this->contrib_status,
			'CONTRIB_SCREENSHOT'			=> ($this->screenshots) ? $this->screenshots->preview_image() : false,

			'CONTRIB_LOCAL_NAME'			=> $this->contrib_local_name,
			'CONTRIB_ISO_CODE'				=> $this->contrib_iso_code,

			'CONTRIB_RATING'				=> $this->contrib_rating,
			'CONTRIB_RATING_COUNT'			=> $this->contrib_rating_count,
			'CONTRIB_RATING_STRING'			=> ($this->rating) ? $this->rating->get_rating_string() : '',

			'CONTRIB_ANNOUNCEMENT_TOPIC'	=> ($this->contrib_release_topic_id) ? sprintf(phpbb::$user->lang['ANNOUNCEMENT_TOPIC_VIEW'], '<a href="' . phpbb::append_sid('viewtopic', 't='.$this->contrib_release_topic_id) . '">', '</a>') : false,
			'L_ANNOUNCEMENT_TOPIC'			=> (titania::$config->support_in_titania) ? phpbb::$user->lang['ANNOUNCEMENT_TOPIC'] : phpbb::$user->lang['ANNOUNCEMENT_TOPIC_SUPPORT'],

			// Download data
			'CONTRIB_DOWNLOADS'				=> $this->contrib_downloads,
			'DOWNLOAD_SIZE'					=> (isset($this->download['filesize'])) ? get_formatted_filesize($this->download['filesize']) : '',
			'DOWNLOAD_CHECKSUM'				=> (isset($this->download['hash'])) ? $this->download['hash'] : '',
			'DOWNLOAD_NAME'					=> (isset($this->download['revision_name'])) ? censor_text($this->download['revision_name']) : '',
			'DOWNLOAD_VERSION'				=> (isset($this->download['revision_version'])) ? censor_text($this->download['revision_version']) : '',
			'DOWNLOAD_LICENSE'				=> (isset($this->download['revision_license'])) ? censor_text($this->download['revision_license']) : '',
			'DOWNLOAD_LICENSE'				=> (isset($this->download['revision_license'])) ? censor_text($this->download['revision_license']) : '',
			'DOWNLOAD_INSTALL_TIME'			=> $install_time,
			'DOWNLOAD_INSTALL_LEVEL'		=> (isset($this->download['install_level']) && $this->download['install_level'] > 0) ? phpbb::$user->lang['INSTALL_LEVEL_' . $this->download['install_level']] : '',

			'U_VIEW_DEMO'					=> $this->contrib_demo,
		);
		
		// Ignore some stuff before it is submitted else we can cause an error
		if ($this->contrib_id)
		{
			$vars = array_merge($vars, array(
				'CONTRIB_TYPE'					=> titania_types::$types[$this->contrib_type]->lang,
				'CONTRIB_TYPE_ID'				=> $this->contrib_type,

				'U_CONTRIB_MANAGE'				=> ((($this->is_author || $this->is_active_coauthor) && !in_array($this->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED))) || titania_types::$types[$this->contrib_type]->acl_get('moderate')) ? $this->get_url('manage') : '',
				'U_DOWNLOAD'					=> (isset($this->download['attachment_id'])) ? titania_url::build_url('download', array('id' => $this->download['attachment_id'])): '',
				'U_NEW_REVISION'				=> (phpbb::$auth->acl_get('u_titania_contrib_submit')) && ((($this->is_author || $this->is_active_coauthor) && !in_array($this->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED))) || titania_types::$types[$this->contrib_type]->acl_get('moderate')) ? $this->get_url('revision') : '',
				'U_QUEUE_DISCUSSION'			=> (titania::$config->use_queue && titania_types::$types[$this->contrib_type]->use_queue && ((($this->is_author || $this->is_active_coauthor) && !in_array($this->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED))) || titania_types::$types[$this->contrib_type]->acl_get('queue_discussion'))) ? $this->get_url('queue_discussion') : '',
				'U_VIEW_CONTRIB'				=> $this->get_url(),

				'U_REPORT'						=> (phpbb::$user->data['is_registered']) ? $this->get_url('report') : '',
				'U_INFO'						=> (titania_types::$types[$this->contrib_type]->acl_get('moderate')) ? titania_url::build_url('manage/attention', array('type' => TITANIA_CONTRIB, 'id' => $this->contrib_id)) : '',

				// Contribution Status
				'S_CONTRIB_NEW'					=> (titania_types::$types[$this->contrib_type]->use_queue && titania::$config->use_queue && $this->contrib_status == TITANIA_CONTRIB_NEW) ? true : false,
				'S_CONTRIB_VALIDATED'			=> (!titania_types::$types[$this->contrib_type]->use_queue || !titania::$config->use_queue || $this->contrib_status == TITANIA_CONTRIB_APPROVED) ? true : false,
				'S_CONTRIB_CLEANED'				=> ($this->contrib_status == TITANIA_CONTRIB_CLEANED) ? true : false,
				'S_CONTRIB_DOWNLOAD_DISABLED'	=> ($this->contrib_status == TITANIA_CONTRIB_DOWNLOAD_DISABLED) ? true : false,
				'S_CONTRIB_HIDDEN'				=> ($this->contrib_status == TITANIA_CONTRIB_HIDDEN) ? true : false,
				'S_CONTRIB_DISABLED'			=> ($this->contrib_status == TITANIA_CONTRIB_DISABLED) ? true : false,

				'JS_CONTRIB_TRANSLATION'		=> !empty($this->contrib_iso_code) ? 'true' : 'false', // contrib_iso_code is a mandatory field and must be included with all translation contributions
			));
			
            // ColorizeIt stuff
            if(strlen(titania::$config->colorizeit) && $this->has_colorizeit() && isset($this->download['attachment_id']))
            {
                $vars['U_COLORIZEIT'] = 'http://' . titania::$config->colorizeit_url . '/custom/' . titania::$config->colorizeit . '.html?id=' . $this->download['attachment_id']  . '&amp;sample=' . $this->clr_sample['attachment_id'];
            }
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $vars, $this);

		// Display real author
		if ($return)
		{
			$vars = array_merge($vars, $this->author->assign_details(true));
		}
		else
		{
			$this->author->assign_details();
		}

		if (!$simple && !$return)
		{
			// Display Co-authors
			foreach ($this->coauthors as $user_id => $row)
			{
				if ($row['author_visible'])
				{
					phpbb::$template->assign_block_vars('coauthors', $this->author->assign_details(true, $row));
				}
			}

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
					$revision->translations = (isset($row['translations'])) ? $row['translations'] : array();
					$revision->display('revisions', titania_types::$types[$this->contrib_type]->acl_get('view'));
					$phpbb_versions = array_merge($phpbb_versions, $revision->phpbb_versions);
				}
				unset($revision);

				$ordered_phpbb_versions = order_phpbb_version_list_from_db($phpbb_versions);
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
			if ($this->screenshots)
			{
				$this->screenshots->parse_attachments($message = false, false, false, 'screenshots');
			}
		}

		if ($return)
		{
			return $vars;
		}

		phpbb::$template->assign_vars($vars);
	}

	/**
	* Build view URL for a contribution
	*
	* @param string $page The page we are on (Ex: faq/support/details)
	* @param array $parameters The parameters for the page
	*/
	public function get_url($page = '', $parameters = array())
	{
		if ($page)
		{
			return titania_url::build_url(titania_types::$types[$this->contrib_type]->url . '/' . $this->contrib_name_clean . '/' . $page, $parameters);
		}

		return titania_url::build_url(titania_types::$types[$this->contrib_type]->url . '/' . $this->contrib_name_clean, $parameters);
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

		if (!$this->contrib_id && (!titania::$config->require_validation || !titania_types::$types[$this->contrib_type]->require_validation || in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))))
		{
			// Increment the contrib counter
			$this->change_author_contrib_count($this->contrib_user_id);

			// Increment the count for this type
			titania_types::increment_count($this->contrib_type);
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
		if (titania_types::$types[$this->contrib_type]->forum_robot && titania_types::$types[$this->contrib_type]->forum_database && titania_types::$types[$this->contrib_type]->create_public)
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
			$phpbb_version = $this->revisions[$this->download['revision_id']]['phpbb_versions'][0];

			$contrib_description = $this->contrib_desc;
			titania_decode_message($contrib_description, $this->contrib_desc_uid);

			// Global body and options
			$body = sprintf(phpbb::$user->lang[titania_types::$types[$this->contrib_type]->create_public],
				$this->contrib_name,
				titania_url::remove_sid($this->author->get_url()),
				users_overlord::get_user($this->author->user_id, '_username'),
				$contrib_description,
				$this->download['revision_version'],
				titania_url::build_clean_url('download', array('id' => $this->download['attachment_id'])),
				$this->download['real_filename'],
				$this->download['filesize'],
				titania_url::remove_sid($this->get_url()),
				titania_url::remove_sid($this->get_url('support')),
				$phpbb_version['phpbb_version_branch'][0] . '.' . $phpbb_version['phpbb_version_branch'][1] . '.' .$phpbb_version['phpbb_version_revision']
			);

			$options = array(
				'poster_id'		=> titania_types::$types[$this->contrib_type]->forum_robot,
				'forum_id' 		=> titania_types::$types[$this->contrib_type]->forum_database,
			);

			if ($this->contrib_release_topic_id)
			{
				// We edit the first post of contrib release topic
				$options_edit = array(
					'topic_id'				=> $this->contrib_release_topic_id,
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
				$this->contrib_release_topic_id = phpbb_posting('post', $options_post);

				$sql = 'UPDATE ' . $this->sql_table . '
					SET contrib_release_topic_id = ' . $this->contrib_release_topic_id . '
					WHERE contrib_id = ' . $this->contrib_id;
				phpbb::$db->sql_query($sql);
			}
		}
	}

	/**
	* Reply to the release topic
	*
	* @param string $reply Message to reply to the topic with
	* @param array $options Any additional options for the reply
	*/
	public function reply_release_topic($reply, $options = array())
	{
		if (!$this->contrib_release_topic_id)
		{
			return;
		}

		titania::_include('functions_posting', 'phpbb_posting');

		$options_reply = array_merge($options, array(
			'topic_id'				=> $this->contrib_release_topic_id,
			'topic_title'			=> 'Re: ' . $this->contrib_name,
			'post_text'				=> $reply,
		));
		phpbb_posting('reply', $options_reply);
	}

	public function report($reason = '')
	{
		// Setup the attention object and submit it
		$attention = new titania_attention;
		$attention->__set_array(array(
			'attention_type'		=> TITANIA_ATTENTION_REPORTED,
			'attention_object_type'	=> TITANIA_CONTRIB,
			'attention_object_id'	=> $this->contrib_id,
			'attention_poster_id'	=> $this->contrib_user_id,
			'attention_post_time'	=> $this->contrib_last_update,
			'attention_url'			=> $this->get_url(),
			'attention_title'		=> $this->contrib_name,
			'attention_description'	=> $reason,
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

		// If validation is required we update the counts (otherwise updated when submitted)
		if (titania::$config->require_validation && titania_types::$types[$this->contrib_type]->require_validation)
		{
			// First we are essentially resetting the contrib and category counts back to "New"
			switch ($old_status)
			{
				case TITANIA_CONTRIB_APPROVED :
				case TITANIA_CONTRIB_DOWNLOAD_DISABLED :
					// Decrement the count for the authors
					$this->change_author_contrib_count($author_list, '-', true);

					// Decrement the count for this type
					titania_types::decrement_count($this->contrib_type);

					// Decrement the category count
					$this->update_category_count('-', true);
				break;
			}

			// Now, for the new status, if approved, we increment the contrib and category counts
			switch ($this->contrib_status)
			{
				case TITANIA_CONTRIB_APPROVED :
				case TITANIA_CONTRIB_DOWNLOAD_DISABLED :
					// Increment the count for the authors
					$this->change_author_contrib_count($author_list);

					// Increment the count for this type
					titania_types::increment_count($this->contrib_type);

					// Increment the category count
					$this->update_category_count();

					$sql_ary['contrib_last_update'] = titania::$time;
				break;
			}
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
		$new_permalink = titania_url::url_slug($new_permalink);

		if (!$this->validate_permalink($new_permalink))
		{
			return false;
		}

		$this->contrib_name_clean = $new_permalink;

		// Attention items
		$sql = 'UPDATE ' . TITANIA_ATTENTION_TABLE . '
			SET attention_url = \'' . phpbb::$db->sql_escape($this->get_url()) . '\'
			WHERE attention_object_type = ' . TITANIA_CONTRIB . '
				AND attention_object_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Update the topics/posts under this
		$topic_ids = $post_ids = array();
		$topic = new titania_topic;
		$topic->topic_url = $this->get_url('support');
		$sql = 'SELECT topic_id, topic_subject_clean FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('topic_type', array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION)) . '
				AND parent_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$topic_ids[$row['topic_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		if (sizeof($topic_ids))
		{
			$post = new titania_post;
			$post->topic = $topic;
			$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . '
				WHERE ' . phpbb::$db->sql_in_set('topic_id', array_map('intval', array_keys($topic_ids)));
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$topic->__set_array($topic_ids[$row['topic_id']]);
				$post->__set_array($row);

				$post->post_url = $topic->get_url();
				$post_ids[$row['post_id']] = $post->post_url;

				// Need to reindex as well...
				$post->index();

				// Update the posts table
				$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
					SET post_url = \'' . phpbb::$db->sql_escape($post->post_url) . '\'
					WHERE post_id = ' . $row['post_id'];
				phpbb::$db->sql_query($sql);
			}
			phpbb::$db->sql_freeresult($result);
			unset($topic, $post);

			// Update the topics table
			$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
				SET topic_url = \'' . phpbb::$db->sql_escape($this->get_url('support')) . '\'
				WHERE ' . phpbb::$db->sql_in_set('topic_type', array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION)) . '
					AND parent_id = ' . $this->contrib_id;
			phpbb::$db->sql_query($sql);

			if (sizeof($post_ids))
			{
				// On to attention items for posts
				$sql = 'SELECT attention_id, attention_object_id FROM ' . TITANIA_ATTENTION_TABLE . '
					WHERE attention_object_type = ' . TITANIA_POST . '
						AND ' . phpbb::$db->sql_in_set('attention_object_id', array_map('intval', array_keys($post_ids)));
				$result = phpbb::$db->sql_query($sql);
				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . TITANIA_ATTENTION_TABLE . '
						SET attention_url = \'' . phpbb::$db->sql_escape($post_ids[$row['attention_object_id']]) . '\'
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

	public function validate($contrib_categories = array())
	{
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
			foreach (titania_types::$types as $type_id => $class)
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

			if (!$contrib_categories)
			{
				$error[] = phpbb::$user->lang['EMPTY_CATEGORY'];
			}
			else
			{
				$categories	= titania::$cache->get_categories();

				foreach ($contrib_categories as $category)
				{
					if (!isset($categories[$category]))
					{
						$error[] = phpbb::$user->lang['NO_CATEGORY'];
					}
					else if ($categories[$category]['category_type'] != $this->contrib_type)
					{
						$error[] = phpbb::$user->lang['WRONG_CATEGORY'];
					}
				}
			}
		}

		if (!$this->contrib_desc)
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_DESC'];
		}

		if (defined('TITANIA_TYPE_TRANSLATION') && $this->contrib_type == TITANIA_TYPE_TRANSLATION && !$this->contrib_iso_code)
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_ISO_CODE'];
		}

		if (defined('TITANIA_TYPE_TRANSLATION') && $this->contrib_type == TITANIA_TYPE_TRANSLATION && !$this->contrib_local_name)
		{
			$error[] = phpbb::$user->lang['EMPTY_CONTRIB_LOCAL_NAME'];
		}

		if (!$this->contrib_id)
		{
			if (!$this->contrib_name_clean)
			{
				// If they leave it blank automatically create it
				$this->contrib_name_clean = titania_url::url_slug($this->contrib_name);

				$append = '';
				$i = 2;
				while ($this->validate_permalink($this->contrib_name_clean . $append) == false)
				{
					$append = '_' . $i;
					$i++;
				}

				$this->contrib_name_clean = $this->contrib_name_clean . $append;
			}
			elseif (titania_url::url_slug($this->contrib_name_clean) !== $this->contrib_name_clean)
			{
				$error[] = sprintf(phpbb::$user->lang['INVALID_PERMALINK'], titania_url::url_slug($this->contrib_name_clean));
			}
			elseif (!$this->validate_permalink($this->contrib_name_clean))
			{
				$error[] = phpbb::$user->lang['CONTRIB_NAME_EXISTS'];
			}
		}

		// Hooks
		titania::$hook->call_hook_ref(array(__CLASS__, __FUNCTION__), $error, $this);

		return $error;
	}

	/*
	 * Validate a contrib permalink
	 *
	 * @param string $permalink
	 * @return bool
	 */
	public function validate_permalink($permalink)
	{
		$sql = 'SELECT contrib_id
			FROM ' . $this->sql_table . "
			WHERE contrib_name_clean = '" . phpbb::$db->sql_escape($permalink) . "'";
		$result = phpbb::$db->sql_query($sql);
		$found = phpbb::$db->sql_fetchfield('contrib_id');
		phpbb::$db->sql_freeresult($result);

		return ($found) ? false : true;
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
		if ($force == false && (titania::$config->require_validation && titania_types::$types[$this->contrib_type]->require_validation && !in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))))
		{
			return;
		}

		$user_id = (int) $user_id;
		$action = ($action == '-') ? '-' : '+';

		// Increment/Decrement the contrib counter for the new owner
		$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . "
			SET author_contribs = author_contribs $action 1" .
				((isset(titania_types::$types[$this->contrib_type]->author_count)) ? ', ' . titania_types::$types[$this->contrib_type]->author_count . ' = ' . titania_types::$types[$this->contrib_type]->author_count . " $action 1" : '') . "
			WHERE user_id = $user_id " .
				(($action == '-') ? 'AND author_contribs > 0' : '');
		phpbb::$db->sql_query($sql);


		if (!phpbb::$db->sql_affectedrows() && $action == '+')
		{
			$author = new titania_author($user_id);
			$author->author_contribs = 1;

			if (isset(titania_types::$types[$this->contrib_type]->author_count))
			{
				$author->{titania_types::$types[$this->contrib_type]->author_count} = 1;
			}

			$author->submit();
		}
	}

	/*
	 * Set the relations between contribs and categories
	 *
	 * @param bool $update
	 * @return void
	 */
	public function put_contrib_in_categories($contrib_categories = array())
	{
		if (!$this->contrib_id)
		{
			return;
		}

		// Resync the count
		$this->update_category_count('-');

		// Remove them from the old categories
		$sql = 'DELETE
			FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		if (!sizeof($contrib_categories))
		{
			return;
		}

		$categories_to_update = $sql_ary = array();
		foreach ($contrib_categories as $category_id)
		{
			$sql_ary[] = array(
				'contrib_id' 	=> $this->contrib_id,
				'category_id'	=> $category_id,
			);

			$categories_to_update[] = $category_id;
			$parents = titania::$cache->get_category_parents($category_id);
			foreach ($parents as $parent)
			{
				$categories_to_update[] = $parent['category_id'];
			}
		}
		phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $sql_ary);

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
		if (titania::$config->require_validation && titania_types::$types[$this->contrib_type]->require_validation && !in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) && !$force)
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
		}
	}
	
	/**
	* Check if ColorizeIt is available
	*/
	public function has_colorizeit($force_update = false)
	{
	    if($force_update || $this->clr_sample === false)
	    {
	        // get sample id from database
            $attachment = new titania_attachment(TITANIA_CLR_SCREENSHOT, $this->contrib_id);
            $this->clr_sample = $attachment->get_preview();
	    }
	    return is_array($this->clr_sample) && strlen($this->contrib_clr_colors) > 0;
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

		// Self delete
		parent::delete();
	}

	/**
	 * Check if there is a revision in the queue
	 *
	 * @return true if there is, false if not
	 */
	public function in_queue()
	{
		if (!titania::$config->use_queue || !titania_types::$types[$this->contrib_type]->use_queue)
		{
			return false;
		}

		$sql = 'SELECT COUNT(revision_id) AS cnt FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE contrib_id = ' . (int) $this->contrib_id . '
				AND queue_status > 1';
		phpbb::$db->sql_query($sql);
		$cnt = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		return ($cnt) ? true : false;
	}

	/**
	* Index the contribution
	*/
	public function index()
	{
		$data = array(
			'title'			=> $this->contrib_name,
			'text'			=> $this->contrib_desc,
			'text_uid'		=> $this->contrib_desc_uid,
			'text_bitfield'	=> $this->contrib_desc_bitfield,
			'text_options'	=> $this->contrib_desc_options,
			'author'		=> $this->contrib_user_id,
			'date'			=> $this->contrib_last_update,
			'url'			=> titania_url::unbuild_url($this->get_url()),
			'approved'		=> ((!titania::$config->require_validation || !titania_types::$types[$this->contrib_type]->require_validation) || in_array($this->contrib_status, array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED))) ? true : false,
		);

		titania_search::index(TITANIA_CONTRIB, $this->contrib_id, $data);
	}
}
