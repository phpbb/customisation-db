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

if (!class_exists('titania_classbase'))
{
	require($phpbb_root_path . 'includes/titania/classbase.' . $phpEx);
}

/**
* Class to abstract contribution reviews.
* @package Titania
*/
class titania_review extends titania_classbase
{
	// Table properties
	protected $properties = array(
		'review_id'				=> 0,
		'contrib_id'			=> 0,
		'review_text'			=> '',
		'review_text_bitfield'	=> '',
		'review_text_uid'		=> '',
		'review_text_options'	=> 7,
		'review_rating'			=> 3,
		'review_user_id'		=> 0,
		'review_status'			=> 1,
		'review_time'			=> 0,
	);

	// Database data
	private $data = array();

	// Additional attributes
	private $text_parsed_for_storage = false;

	// Constructor
	public function __construct($review_id = false)
	{
		if ($review_id === false)
		{
			// We're going to create a new review
			$this->review_time = time();
		}
		else
		{
			// We're going to perform operations on an existing review
			$this->review_id = $review_id;
			$this->load();
		}
	}

	// Update data or submit new review
	public function submit()
	{
		// Nobody parsed the text for storage before.
		// Parse text with lowest settings.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		if (!$this->review_id)
		{
			$this->insert();
		}
		else
		{
			$this->update();
		}
	}

	// Update data
	private function update()
	{
		$sql_array = array();
		foreach ($this->data as $key => $value)
		{
			if ($key == 'review_id' || $this->$key == $value)
			{
				continue;
			}

			$sql_array[$key] = $this->$key;
		}

		if (!sizeof($sql_array))
		{
			return;
		}

		global $db;

		$sql = 'UPDATE ' . CDB_REVIEWS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_array) . '
			WHERE review_id = ' . $this->review_id;
		$db->sql_query($sql);
	}

	// Insert data
	private function insert()
	{
		global $db;

		$sql_array = array();
		foreach ($this->properties as $key => $value)
		{
			$sql_array[$key] = $value;
		}

		$sql = 'INSERT INTO ' . CDB_REVIEWS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
		$db->sql_query($sql);

		$this->review_id = $db->sql_nextid();
	}

	// Get review data from the database
	private function load()
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . CDB_REVIEWS_TABLE . '
			WHERE review_id = ' . $this->review_id;
		$result = $db->sql_query($sql);
		$this->data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->set_text($this->data['review_text'], $this->data['review_text_bitfield'], $this->data['review_text_uid'], $this->data['review_text_options']);

		$this->review_id		= $this->data['review_id'];
		$this->contrib_id		= $this->data['contrib_id'];
		$this->review_rating	= $this->data['review_rating'];
		$this->review_user_id	= $this->data['review_user_id'];
		$this->review_status	= $this->data['review_status'];
		$this->review_time		= $this->data['review_time'];

		$this->text_parsed_for_storage = true;
	}

	// Parse text for db
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		// As per naderman
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

	// Getters
	public function get_text($editable = false)
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

	// Setters
	public function set_text($text, $uid = false, $bitfield = false, $flags = false)
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