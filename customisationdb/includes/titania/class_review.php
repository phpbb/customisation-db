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
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('titania_database_object'))
{
	require($phpbb_root_path . 'includes/titania/class_base_db_object.' . $phpEx);
}

/**
* Class to abstract contribution reviews.
* @package Titania
*/
class titania_review extends titania_database_object
{
	// SQL settings
	protected $sql_table		= CDB_REVIEWS_TABLE;
	protected $sql_id_field		= 'review_id';

	// Additional attributes
	private $text_parsed_for_storage = false;

	// Constructor
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

	// Update data or submit new review
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		parent::submit();
	}

	// Get review data from the database
	public function load()
	{
		parent::load();

		$this->text_parsed_for_storage = true;
	}

	// Parse text for db
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

	// Parse text for display
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->review_text, $this->review_text_uid, $this->review_text_bitfield, $this->review_text_options);
	}

	// Parse text for edit
	private function generate_text_for_edit()
	{
		return generate_text_for_edit($this->review_text, $this->review_text_uid, $this->review_text_options);
	}

	// Special getter methods overwriting the default magic methods.
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

	// Special setter methods overwriting the default magic methods.
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
}