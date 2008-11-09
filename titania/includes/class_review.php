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
	require(TITANIA_ROOT . 'includes/class_base_db_object.' . PHP_EXT);
}

/**
* Class to abstract contribution reviews.
* @package Titania
*/
class titania_review extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_REVIEWS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'review_id';

	/**
	 * Text parsed for storage
	 *
	 * @var bool
	 */
	private $text_parsed_for_storage = false;

	/**
	 * Constructor
	 *
	 * @param bool $review_id
	 */
	public function __construct($review_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'review_id'				=> array('default' => 0),
			'contrib_id'			=> array('default' => 0),
			'review_text'			=> array('default' => ''),
			'review_text_bitfield'	=> array('default' => '',	'readonly' => true),
			'review_text_uid'		=> array('default' => '',	'readonly' => true),
			'review_text_options'	=> array('default' => 7,	'readonly' => true),
			'review_rating'			=> array('default' => 3),
			'review_user_id'		=> array('default' => 0),
			'review_status'			=> array('default' => 1),
			'review_time'			=> array('default' => 0),
		));

		if ($review_id === false)
		{
			// We're going to create a new review
			$this->review_time = time();
		}
		else
		{
			// We're going to perform operations on an existing review
			$this->review_id = $review_id;
		}
	}

	/**
	 * Update data or submit new review
	 *
	 * @return void
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		parent::submit();
	}

	/**
	 * Get review data from the database
	 *
	 * @return void
	 */
	public function load()
	{
		parent::load();

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Parse text to store in database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 *
	 * @return void
	 */
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		$review_text = $this->review_text;
		$review_text_uid = $this->review_text_uid;
		$review_text_bitfield = $this->review_text_bitfield;
		$review_text_options = $this->review_text_options;

		generate_text_for_storage($review_text, $review_text_uid, $review_text_bitfield, $review_text_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->review_text = $review_text;
		$this->review_text_uid = $review_text_uid;
		$this->review_text_bitfield = $review_text_bitfield;
		$this->review_text_options = $review_text_options;

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Parse text for display
	 *
	 * @return string text content from database for display
	 */
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->review_text, $this->review_text_uid, $this->review_text_bitfield, $this->review_text_options);
	}

	/**
	 * Parse text for edit
	 *
	 * @return string text content from database for editing
	 */
	private function generate_text_for_edit()
	{
		return generate_text_for_edit($this->review_text, $this->review_text_uid, $this->review_text_options);
	}

	/**
	 * Getter function for review_text
	 *
	 * @param bool $editable
	 *
	 * @return string generate_text_for edit if editable is true, or display if false
	 */
	public function get_review_text($editable = false)
	{
		// Text needs to be from database or parsed for database.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		if ($editable)
		{
			return $this->generate_text_for_edit();
		}
		else
		{
			return $this->generate_text_for_display();
		}
	}

	/**
	 * Setter function for review_text
	 *
	 * @param string $text
	 * @param string $uid
	 * @param string $bitfield
	 * @param int $flags
	 *
	 * @return void
	 */
	public function set_review_text($text, $uid = false, $bitfield = false, $flags = false)
	{
		$this->review_text = $text;
		$this->text_parsed_for_storage = false;

		if ($uid !== false)
		{
			$this->review_text_uid = $uid;
		}

		if ($bitfield !== false)
		{
			$this->review_text_bitfield = $bitfield;
		}

		if ($flags !== false)
		{
			$this->review_text_options = $flags;
		}
	}



	/**
	 * Display rating menu if user has not rated this contrib previously
	 *
	 * @return unknown
	 */
	public function rating_menu($contrib_id)
	{
		global $user, $db;

		$s_rating_options = '';
		foreach ($user->lang['ratings'] as $rating => $lang)
		{
			$s_rating_options .= '<option value="' . $rating . '">' . $lang . '</option>';
		}

		return $s_rating_options;
	}

	/**
	 * Obtain an array of reviews by contrib.
	 *
	 * @param int $contrib
	 * @param string $order_by @todo
	 */
	public function obtain_contrib_reviews($contrib_id, $order_by = false)
	{
		global $db;

		$sql = 'SELECT *
				FROM ' . CUSTOMISATION_REVIEWS_TABLE . '
				WHERE contrib_id = ' . (int) $contrib_id;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$reviews[$row['review_id']] = $row;
		}
		$db->sql_freeresult($result);
	}
}