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

if (!class_exists('titania_database_object'))
{
	require TITANIA_ROOT . 'includes/core/object_database.' . PHP_EXT;
}

/**
 * Class to abstract contributions.
 * @package Titania
 */
class titania_contribution extends titania_database_object
{
	/**
	 * Database table to be used for the contribution object
	 *
	 * @var string
	 */
	protected $sql_table		= TITANIA_CONTRIBS_TABLE;

	/**
	 * Primary sql identifier for the contribution object
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'contrib_id';

	/**
	 * Description parsed for storage
	 *
	 * @var bool
	 */
	private $description_parsed_for_storage = false;

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
	 * Rating of this contribution
	 *
	 * @var titania_rating
	 */
	public $rating;

	/**
	 * is_author (true when visiting user is the author)
	 * is_active_coauthor (true when visiting user is an active co-author)
	 * is_coauthor (true when visiting user is a non-active co-author)
	 */
	public $is_author = false;
	public $is_active_coauthor = false;
	public $is_coauthor = false;

	/**
	 * Constructor class for the contribution object
	 */
	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'contrib_id'					=> array('default' => 0),
			'contrib_type'					=> array('default' => 0),
			'contrib_name'					=> array('default' => '',	'max' => 255),
			'contrib_name_clean'			=> array('default' => '',	'max' => 255),

			'contrib_desc'					=> array('default' => ''),
			'contrib_desc_bitfield'			=> array('default' => ''),
			'contrib_desc_uid'				=> array('default' => ''),
			'contrib_desc_options'			=> array('default' => 7),

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
		));
	}

	/**
	 * Submit data for storing into the database
	 * DO NOT USE THIS FUNCTION TO CHANGE THE STATUS ELSE THE AUTHORS CONTRIB COUNT WILL BE INCORRECT (use change_status function)!
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->description_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		if (!$this->contrib_id)
		{
			// Increment the contrib counter
			$this->change_author_contrib_count($this->contrib_user_id);
		}

		// Update index or insert (must do before submitting)?
		$update = ($this->contrib_id) ? false : true;

		parent::submit();

		// Index!
		$data = array(
			'title'			=> $this->contrib_name,
			'text'			=> $this->contrib_desc,
			'author'		=> $this->contrib_user_id,
			'date'			=> $this->contrib_last_update,
			'url'			=> titania_url::unbuild_url($this->get_url()),
			'approved'		=> (!titania::$config->require_validation || $this->contrib_status == TITANIA_CONTRIB_APPROVED) ? true : false,
		);

		titania_search::index($this->contrib_type, $this->contrib_id, $data, $update);
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

		if ($this->contrib_status == $new_status)
		{
			return;
		}

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

		if ($this->contrib_status != TITANIA_CONTRIB_APPROVED)
		{
			// Approving this contribution item...
			$this->change_author_contrib_count($author_list);
		}
		else
		{
			// Disapproving this contribution item...
			$this->change_author_contrib_count($author_list, '-', true);
		}

		$this->contrib_status = $new_status;

		$sql = 'UPDATE ' . $this->sql_table . '
			SET contrib_status = ' . $this->contrib_status . '
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

		if (!$this->contrib_id)
		{
			if (!$this->contrib_name_clean)
			{
				$error[] = phpbb::$user->lang['EMPTY_CONTRIB_PERMALINK'];
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

		return $error;
	}

	/**
	 * Submit data in the post_data format (from includes/tools/message.php)
	 *
	 * @param object $message The message object
	 */
	public function post_data($message)
	{
		$post_data = $message->request_data();

		$this->__set_array(array(
			'contrib_name'		=> $post_data['subject'],
			'contrib_desc'		=> $post_data['message'],
		));

		$this->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);
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
	 * Load the contrib
	 *
	 * @param int|string $contrib The contrib item (contrib_name_clean, contrib_id)
	 *
	 * @return bool True if the contrib exists, false if not
	 */
	public function load($contrib)
	{
		$sql_ary = array(
			'SELECT'	=> 'c.*, a.*, u.*',
			'FROM' 		=> array($this->sql_table => 'c'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = c.contrib_user_id'
				),
				array(
					'FROM'	=> array(TITANIA_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.user_id = u.user_id'
				),
			)
		);

		if (is_numeric($contrib))
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

		$this->description_parsed_for_storage = true;

		// Set author object and set the data for the author object.
		$this->author = new titania_author($this->contrib_user_id);
		$this->author->__set_array($sql_data);

		// Load co-authors list
		$this->coauthors = array();
		$sql_ary = array(
			'SELECT' => 'cc.*, a.*, u.*',
			'FROM'		=> array(
				TITANIA_CONTRIB_COAUTHORS_TABLE => 'cc',
				USERS_TABLE => 'u',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.user_id = u.user_id'
				),
			),
			'WHERE'		=> 'cc.contrib_id = ' . $this->contrib_id . ' AND u.user_id = cc.user_id'
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->coauthors[$row['user_id']] = $row;
			users_overlord::$users[$row['user_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

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

		return $this->rating;
	}

	/**
	 * Get the revisions for this contrib item
	 * (not always needed, so save us a query when it's not needed)
	 */
	public function get_revisions()
	{
		if (sizeof($this->revisions))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id .
				((titania::$config->require_validation && !titania::$access_level == TITANIA_ACCESS_TEAMS) ? ' AND revision_validated = 1 ' : '') . '
				AND revision_submitted = 1
			ORDER BY revision_id DESC';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->revisions[$row['revision_id']] = $row;
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	 * Get the latest revision (to download)
	 * Stored in $this->download; only gets the latest validated (if validation is required)
	 */
	public function get_download()
	{
		if ($this->download)
		{
			return;
		}

		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_ATTACHMENTS_TABLE . ' a
			WHERE r.contrib_id = ' . $this->contrib_id . '
				AND a.attachment_id = r.attachment_id ' .
				((titania::$config->require_validation) ? ' AND r.revision_validated = 1 ' : '') . '
				AND revision_submitted = 1
			ORDER BY r.revision_id DESC';
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$this->download = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
	}

	/**
	 * Generate text for storing description into the database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 *
	 * @return void
	 */
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		generate_text_for_storage($this->contrib_desc, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->description_parsed_for_storage = true;
	}

	/**
	 * Parse description for display
	 *
	 * @return string
	 */
	public function generate_text_for_display()
	{
		return generate_text_for_display($this->contrib_desc, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options);
	}

	/**
	 * Parse description for edit
	 *
	 * @return string
	 */
	public function generate_text_for_edit()
	{
		return array_merge(generate_text_for_edit($this->contrib_desc, $this->contrib_desc_uid, $this->contrib_desc_options), array(
			'object_type'	=> TITANIA_CONTRIB,
			'object_id'		=> $this->contrib_id,
		));
	}

	/**
	* Immediately increases the view counter for this contribution
	*
	* @return void
	*/
	private function increase_view_counter()
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
		if (!$simple)
		{
			// Get the rating object
			$this->get_rating();
		}

		$vars = array(
			// Contribution data
			'CONTRIB_NAME'					=> $this->contrib_name,
			'CONTRIB_DESC'					=> $this->generate_text_for_display(),
			'CONTRIB_VIEWS'					=> $this->contrib_views,
			'CONTRIB_UPDATE_DATE'			=> phpbb::$user->format_date($this->contrib_last_update),
			'CONTRIB_STATUS'				=> $this->contrib_status,
			'CONTRIB_TYPE'					=> ($this->contrib_type) ? titania_types::$types[$this->contrib_type]->lang : '', // Don't cause an error while we create a contrib item

			'CONTRIB_RATING'				=> $this->contrib_rating,
			'CONTRIB_RATING_COUNT'			=> $this->contrib_rating_count,
			'CONTRIB_RATING_STRING'			=> (!$simple) ? $this->rating->get_rating_string() : '',

			// Download data
			'CONTRIB_DOWNLOADS'				=> $this->contrib_downloads,
			'DOWNLOAD_SIZE'					=> (isset($this->download['filesize'])) ? $this->download['filesize'] : '',
			'DOWNLOAD_CHECKSUM'				=> (isset($this->download['hash'])) ? $this->download['hash'] : '',
			'DOWNLOAD_NAME'					=> (isset($this->download['revision_name'])) ? censor_text($this->download['revision_name']) : '',
			'DOWNLOAD_VERSION'				=> (isset($this->download['revision_version'])) ? censor_text($this->download['revision_version']) : '',

			'U_DOWNLOAD'					=> (isset($this->download['attachment_id'])) ? titania_url::build_url('download', array('id' => $this->download['attachment_id'])): '',
			'U_VIEW_CONTRIB'				=> ($this->contrib_type) ? $this->get_url() : '', // Don't cause an error while we create a contrib item
			'U_VIEW_DEMO'					=> $this->contrib_demo,

			'S_CONTRIB_VALIDATED'			=> ($this->contrib_status == TITANIA_CONTRIB_APPROVED) ? true : false,
		);

		// Display real author
		if ($return)
		{
			$vars = array_merge($vars, $this->author->assign_details(true));
		}
		else
		{
			$this->author->assign_details();
		}

		if (!$simple)
		{
			if (!$return)
			{
				// Display Co-authors
				foreach ($this->coauthors as $user_id => $row)
				{
					if ($row['author_visible'])
					{
						phpbb::$template->assign_block_vars('coauthors', $this->author->assign_details(true, $row));
					}
				}

				// Display Revisions
				if (sizeof($this->revisions))
				{
					$revision = new titania_revision($this);
					foreach ($this->revisions as $revision_id => $row)
					{
						$revision->__set_array($row);
						$revision->display();
					}
					unset($revision);
				}
			}

			if (!phpbb::$user->data['is_bot'])
			{
				$this->increase_view_counter();
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
	*/
	public function get_url($page = '')
	{
		if ($page)
		{
			return titania_url::build_url(titania_types::$types[$this->contrib_type]->url . '/' . $this->contrib_name_clean . '/' . $page);
		}

		return titania_url::build_url(titania_types::$types[$this->contrib_type]->url . '/' . $this->contrib_name_clean);
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
				phpbb::$cache->reset_author_contribs($row['user_id']);
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
			$sql_ary = array();
			foreach ($active as $user_id)
			{
				$sql_ary[] = array(
					'contrib_id'	=> $this->contrib_id,
					'user_id'		=> $user_id,
					'active'		=> true,
				);

				// reset each of the authors cached contrib list
				phpbb::$cache->reset_author_contribs($user_id);
			}

			phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_COAUTHORS_TABLE, $sql_ary);

			// Increment the contrib counter
			$this->change_author_contrib_count($active);
		}

		if (sizeof($nonactive))
		{
			$sql_ary = array();
			foreach ($nonactive as $user_id)
			{
				$sql_ary[] = array(
					'contrib_id'	=> $this->contrib_id,
					'user_id'		=> $user_id,
					'active'		=> false,
				);

				// reset each of the authors cached contrib list
				phpbb::$cache->reset_author_contribs($user_id);
			}

			phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_COAUTHORS_TABLE, $sql_ary);

			// Increment the contrib counter
			$this->change_author_contrib_count($nonactive);
		}
	}

	/*
	 * Set a new contrib_user_id for the current contribution
	 *
	 * @param int $user_id The new user_id that will be the owner
	 */
	public function set_contrib_user_id($user_id)
	{
		// Delete them from the co-authors list if they are in it...
		$sql = 'DELETE FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . '
				AND user_id = ' . (int) $user_id;
		phpbb::$db->sql_query($sql);

		// Update the data for this contrib item
		$sql = 'UPDATE ' . $this->sql_table . '
			SET contrib_user_id = ' . (int) $user_id . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Reset the author contribs that are cached for the new owner
		phpbb::$cache->reset_author_contribs($user_id);

		// Increment the contrib counter for the new owner
		$this->change_author_contrib_count($user_id);

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
		// Don't change if it's not approved
		if ($force == false && (titania::$config->require_validation && $this->contrib_status != TITANIA_CONTRIB_APPROVED))
		{
			return;
		}

		if (is_array($user_id))
		{
			foreach ($user_id as $uid)
			{
				$this->change_author_contrib_count($uid, $action, $force);
			}
			return;
		}

		$user_id = (int) $user_id;
		$action = ($action == '-') ? '-' : '+';

		// Increment/Decrement the contrib counter for the new owner
		$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . "
			SET author_contribs = author_contribs $action 1, " .
				titania_types::$types[$this->contrib_type]->author_count . ' = ' . titania_types::$types[$this->contrib_type]->author_count . " $action 1
			WHERE user_id = $user_id " .
				(($action == '-') ? 'AND author_contribs > 0' : '');
		phpbb::$db->sql_query($sql);

		// If the author profile does not exist set it up
		if (!phpbb::$db->sql_affectedrows())
		{
			$author = new titania_author($user_id);
			$author->load();

			$author->__set_array(array(
				'author_contribs'	=> 1,
				titania_types::$types[$this->contrib_type]->author_count => 1,
			));

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

		// Get all of the categories that we are in and their parents to resync the count
		$categories_to_update = array();
		$sql = 'SELECT category_id FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$categories_to_update[] = $row['category_id'];

			$parents = titania::$cache->get_category_parents($row['category_id']);
			foreach ($parents as $parent)
			{
				$categories_to_update[] = $parent['category_id'];
			}
		}
		phpbb::$db->sql_freeresult($result);

		// Resync the count
		if (sizeof($categories_to_update))
		{
			$categories_to_update = array_unique($categories_to_update);

			$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
				SET category_contribs = category_contribs - 1
				WHERE ' . phpbb::$db->sql_in_set('category_id', array_map('intval', $categories_to_update));
			phpbb::$db->sql_query($sql);
		}

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
		if (sizeof($categories_to_update))
		{
			$categories_to_update = array_unique($categories_to_update);

			$sql = 'UPDATE ' . TITANIA_CATEGORIES_TABLE . '
				SET category_contribs = category_contribs + 1
				WHERE ' . phpbb::$db->sql_in_set('category_id', array_map('intval', $categories_to_update));
			phpbb::$db->sql_query($sql);
		}
	}

	/**
	 * Check if there is a revision in the queue
	 *
	 * @return true if there is, false if not
	 */
	public function in_queue()
	{
		if (!titania::$config->use_queue)
		{
			return false;
		}

		$sql = 'SELECT revision_id FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . (int) $this->contrib_id . '
				AND revision_validated = 0
				AND revision_submitted = 1';
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		return (!$row) ? false : true;
	}
}
