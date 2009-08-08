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

		return parent::submit();
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
		$sql = 'SELECT * FROM ' . $this->sql_table . ' WHERE ';

		if (is_numeric($contrib))
		{
			$sql .= 'contrib_id = ' . (int) $contrib;
		}
		else
		{
			$sql .= 'contrib_name_clean = \'' . phpbb::$db->sql_escape(utf8_clean_string($contrib)) . '\'';
		}
		$result = phpbb::$db->sql_query($sql);
		$this->sql_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (empty($this->sql_data))
		{
			return false;
		}

		foreach ($this->sql_data as $key => $value)
		{
			$this->$key = $value;
		}

		$this->description_parsed_for_storage = true;

		titania::load_object('author');

		// Get the author
		$this->author = new titania_author($this->contrib_user_id);
		$this->author->load();

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
			$this->coauthors[$row['user_id']] = new titania_author($row['user_id']);
			$this->coauthors[$row['user_id']]->__set_array($row);
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

		titania::load_object('rating');

		$this->rating = new titania_rating('contrib', $this);
		$this->rating->load();
		$this->rating->assign_common();

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
	private function generate_text_for_display()
	{
		$this->contrib_desc = generate_text_for_display($this->contrib_desc, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options);
	}

	/**
	 * Parse description for edit
	 *
	 * @return string
	 */
	private function generate_text_for_edit()
	{
		decode_message($this->contrib_desc, $this->contrib_desc_uid);
	}

	/**
	 * Return contrib description
	 *
	 * @return string
	 */
	public function get_text($editable = false)
	{
		if ($editable)
		{
			$this->generate_text_for_edit();
		}
		else
		{
			$this->generate_text_for_display();
		}

		return $this->contrib_desc;
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

		// Output author data
		$this->author->assign_details();

		phpbb::$template->assign_vars(array(
			// Contribution data
			'CONTRIB_TITLE'					=> $this->contrib_name,
			'CONTRIB_DESC'					=> $this->generate_text_for_display(),

			'CONTRIB_VIEWS'					=> $this->contrib_views,
			'CONTRIB_DOWNLOADS'				=> $this->contrib_downloads,

			'CONTRIB_RATING'				=> $this->contrib_rating,
			'CONTRIB_RATING_COUNT'			=> $this->contrib_rating_count,
			'CONTRIB_RATING_STRING'			=> $this->rating->get_rating_string(),
		));

		// Display Co-authors
		foreach ($this->coauthors as $user_id => $author)
		{
			phpbb::$template->assign_block_vars($author->assign_details(true));
		}

		// Display Revisions
		foreach ($this->revisions as $revision_id => $revision)
		{
			phpbb::$template->assign_block_vars('revisions', array(
				'REVISION_NAME'		=> $revision['revision_name'],
				'REVISION_TIME'		=> phpbb::$user->format_date($revision['revision_time']),

				'U_DOWNLOAD'		=> '',

				'S_VALIDATED'		=> ($revision['contrib_validated']) ? true : false,
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
			return titania::$url->build_url(get_contrib_type_string($this->contrib_type, 'url') . '/' . $this->contrib_name_clean . '/' . $page);
		}

		return titania::$url->build_url(get_contrib_type_string($this->contrib_type, 'url') . '/' . $this->contrib_name_clean);
	}
}
