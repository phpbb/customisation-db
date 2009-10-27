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
	* Revisions array
	*/
	public $revisions = array();

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

			'contrib_status'				=> array('default' => TITANIA_STATUS_NEW),

			'contrib_user_id'				=> array('default' => 0),

			'contrib_downloads'				=> array('default' => 0),
			'contrib_views'					=> array('default' => 0),

			'contrib_visible'				=> array('default' => 0),

			'contrib_rating'				=> array('default' => 0.0),
			'contrib_rating_count'			=> array('default' => 0),
		));
	}

	/**
	 * Submit data for storing into the database
	 *
	 * @return bool
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->description_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		// New entry
		if (!$this->contrib_id)
		{
			// Increment the contrib counter
			$this->change_author_contrib_count($this->contrib_user_id);
		}

		return parent::submit();
	}

	/**
	 * Validates given data
	 *
	 * @param unknown_type $contrib_categories
	 */
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

		if (!$contrib_categories)
		{
			$error[] = phpbb::$user->lang['EMPTY_CATEGORY'];
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
			elseif (titania::$url->url_slug($this->contrib_name_clean) !== $this->contrib_name_clean)
			{
				$error[] = sprintf(phpbb::$user->lang['INVALID_PERMALINK'], titania::$url->url_slug($this->contrib_name_clean));
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
	* @param mixed $post_data
	*/
	public function post_data($post_data)
	{
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
			FROM ' . TITANIA_CONTRIBS_TABLE . "
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

		// Load the revisions list
		// Let revision object load revisions ;) @todo

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

		$this->rating = new titania_rating('contrib');
		$this->rating->set_rating_object($this);
		$this->rating->load();

		return $this->rating;
	}

	/**
	* Get the revisions for this contrib item
	* (not always needed, so save us a query when it's not needed)
	*/
	public function get_revisions()
	{
		// @todo this should be in the revisions object
		if (sizeof($this->revisions))
		{
			return;
		}

		$sql = 'SELECT * FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . '
			ORDER BY revision_id DESC';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->revisions[$row['revision_id']] = $row;
		}
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
		$this->contrib_desc = generate_text_for_display($this->contrib_desc, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options);
	}

	/**
	 * Parse description for edit
	 *
	 * @return string
	 */
	public function generate_text_for_edit()
	{
		return generate_text_for_edit($this->contrib_desc, $this->contrib_desc_uid, $this->contrib_desc_options);
	}

	/**
	 * Get downloads per day
	 *
	 * @return string
	 *
	 * @todo Get the oldest revision_id to display this?
	 */
	public function get_downloads_per_day()
	{
		return 0;

		// Cannot calculate anything without release date
		// No point in showing this if there were no downloads
		if (!$this->contrib_release_date || !$this->contrib_downloads)
		{
			return '';
		}

		$time_elapsed = titania::$time - $this->contrib_release_date;

		// The release was just today, show nothing.
		if ($time_elapsed <= 86400)
		{
			return '';
		}

		return sprintf(phpbb::$user->lang['DOWNLOADS_PER_DAY'], $this->contrib_downloads / ($time_elapsed / 86400));
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
	 * @param bool $return True if you want the data prepared for output and returned as an array, false to output to the template
	 */
	public function assign_details()
	{
		// Get the rating object
		$this->get_rating();

		// Get revisions
		$this->get_revisions();

		phpbb::$template->assign_vars(array(
			// Contribution data
			'CONTRIB_NAME'					=> $this->contrib_name,
			'CONTRIB_DESC'					=> $this->generate_text_for_display(),

			'CONTRIB_VIEWS'					=> $this->contrib_views,
			'CONTRIB_DOWNLOADS'				=> $this->contrib_downloads,

			'CONTRIB_RATING'				=> $this->contrib_rating,
			'CONTRIB_RATING_COUNT'			=> $this->contrib_rating_count,
			'CONTRIB_RATING_STRING'			=> $this->rating->get_rating_string(),
		));

		// Display real author
		$this->author->assign_details();

		// Display Co-authors
		foreach ($this->coauthors as $user_id => $row)
		{
			if ($row['author_visible'])
			{
				phpbb::$template->assign_block_vars('coauthors', $this->author->assign_details(true, $row));
			}
		}

		// Display Revisions
		foreach ($this->revisions as $revision_id => $revision)
		{
			phpbb::$template->assign_block_vars('revisions', array(
				'REVISION_NAME'		=> $revision['revision_name'],
				'REVISION_TIME'		=> phpbb::$user->format_date($revision['revision_time']),

				'S_VALIDATED'		=> ($revision['revison_validated']) ? true : false,
			));
		}

		if (!phpbb::$user->data['is_bot'])
		{
			$this->increase_view_counter();
		}
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
			return titania::$url->build_url(titania::$type->types[$this->contrib_type]['type_slug'] . '/' . $this->contrib_name_clean . '/' . $page);
		}

		return titania::$url->build_url(titania::$type->types[$this->contrib_type]['type_slug'] . '/' . $this->contrib_name_clean);
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
			// Grab the current contribs
			$sql = 'SELECT user_id
				FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
				WHERE contrib_id = ' . (int) $this->contrib_id;
			$result = phpbb::$db->sql_query($sql);

			$decrement_list = array();
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$decrement_list[] = $row['user_id'];
			}
			phpbb::$db->sql_freeresult($result);

			if (sizeof($decrement_list))
			{
				// Don't need to call change_author_contrib_count here, since they should already exist and it uses quite a few extra queries
				$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . '
					SET author_contribs = author_contribs - 1
					WHERE ' . phpbb::$db->sql_in_set('user_id', $decrement_list);
				phpbb::$db->sql_query($sql);
			}

			$sql = 'DELETE FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . '
				WHERE contrib_id = ' . (int) $this->contrib_id;
			phpbb::$db->sql_query($sql);
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
			}

			phpbb::$db->sql_multi_insert(TITANIA_CONTRIB_COAUTHORS_TABLE, $sql_ary);

			// Increment the contrib counter
			$this->change_author_contrib_count($nonactive);
		}
	}

	/*
	 * Set a new contrib_user_id for the current contribution
	 *
	 * @param int $id
	 */
	public function set_contrib_user_id($user_id)
	{
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_user_id = ' . (int) $user_id . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Increment the contrib counter for the new owner
		$this->change_author_contrib_count($user_id);

		// Decrement the contrib counter for the old owner (setting as a co-author increments it)
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
	*/
	private function change_author_contrib_count($user_id, $action = '+')
	{
		// @todo this should be in author.
		return;

		if (is_array($user_id))
		{
			foreach ($user_id as $uid)
			{
				$this->change_author_contrib_count($uid, $action);
			}
			return;
		}

		$user_id = (int) $user_id;
		$action = ($action == '-') ? '-' : '+';

		// Increment the contrib counter for the new owner
		$sql = 'UPDATE ' . TITANIA_AUTHORS_TABLE . "
			SET author_contribs = author_contribs $action 1, " .
				titania::$types[$this->contrib_type]->author_count . ' = ' . titania::$types[$this->contrib_type]->author_count . " $action 1
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
				titania::$types[$this->contrib_type]->author_count => 1,
			));

			$author->submit();
		}
	}
}
