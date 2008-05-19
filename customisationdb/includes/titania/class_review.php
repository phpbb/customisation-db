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

/**
* Class to abstract contribution reviews.
* @package Titania
*/
class review
{
	// Table properties
	private $review_id;
	private $contrib_id				= 0;
	private $review_text			= '';
	private $review_text_bitfield	= '';
	private $review_text_uid		= '';
	private $review_text_options	= 7;
	private $review_rating			= 3;
	private $review_user_id			= 0;
	private $review_status			= 1;
	private $review_time			= 0;

	// Database data
	private $data = array();

	// Additional properties
	private $review_text_for_storage	= '';
	private $review_text_for_display	= '';
	private $review_text_for_edit		= '';

	// Constructor
	public function __construct($review_id = false)
	{
		if ($review_id === false)
		{
			// We're going to create a new review
			$this->review_time = (int) time();
		}
		else
		{
			// We're going to perform operations on an existing review
			$this->load($review_id);
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

		$sql = 'INSERT INTO ' . CDB_REVIEWS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'contrib_id'			=> $this->contrib_id,
			'review_text'			=> $this->review_text,
			'review_text_bitfield'	=> $this->review_text_bitfield,
			'review_text_uid'		=> $this->review_text_uid,
			'review_text_options'	=> $this->review_text_options,
			'review_rating'			=> $this->review_rating,
			'review_user_id'		=> $this->review_user_id,
			'review_status'			=> $this->review_status,
			'review_time'			=> $this->review_time,
		));

		$db->sql_query($sql);

		$this->review_id = (int) $db->sql_nextid();
	}

	// Get review data from the database
	private function load($review_id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . CDB_REVIEWS_TABLE . '
			WHERE review_id = ' . (int) $review_id;
		$result = $db->sql_query($sql);
		$this->data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->set_text($this->data['review_text'], $this->data['review_text_bitfield'], $this->data['review_text_uid'], $this->data['review_text_options']);
		$this->text_parsed_for_storage = true;

		$this->set_review_id($this->data['review_id']);
		$this->set_contrib_id($this->data['contrib_id']);
		$this->set_rating($this->data['review_rating']);
		$this->set_user_id($this->data['review_user_id']);
		$this->set_status($this->data['review_status']);

		$this->review_time = (int) $this->data['review_time'];
	}

	// Parse text for db
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		generate_text_for_storage($this->review_text, $this->review_text_uid, $this->review_text_bitfield, $this->review_text_options, $allow_bbcode, $allow_urls, $allow_smilies);
		$this->text_parsed_for_storage = true;
	}

	// Parse text for display
	private function generate_text_for_display()
	{
		$this->review_text = generate_text_for_display($this->review_text, $this->review_text_uid, $this->review_text_bitfield, $this->review_text_options);
		$this->text_parsed_for_storage = false;
	}

	// Parse text for edit
	private function generate_text_for_edit()
	{
		$this->review_text = generate_text_for_edit($this->review_text, $this->review_text_uid, $this->review_text_options);
		$this->text_parsed_for_storage = false;
	}

	// Getters
	public function get_review_id()
	{
		return $this->review_id;
	}

	public function get_contrib_id()
	{
		return $this->contrib_id;
	}

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

		return $this->review_text;
	}

	public function get_rating()
	{
		return $this->review_rating;
	}

	public function get_user_id()
	{
		return $this->review_user_id;
	}

	public function get_status()
	{
		return $this->review_status;
	}

	public function get_time()
	{
		return $this->review_time;
	}


	// Setters
	public function set_review_id($review_id)
	{
		$this->review_id = (int) $review_id;
	}
	
	public function set_contrib_id($contrib_id)
	{
		$this->contrib_id = (int) $contrib_id;
	}

	public function set_text($text, $uid = false, $bitfield = false, $flags = false)
	{
		$this->review_text = (string) $text;
		$this->text_parsed_for_storage = false;

		if ($uid !== false)
		{
			$this->review_text_uid = (string) $uid;
		}

		if ($bitfield !== false)
		{
			$this->review_text_bitfield = (string) $bitfield;
		}

		if ($flags !== false)
		{
			$this->review_text_options = (int) $flags;
		}
	}

	public function set_rating($rating)
	{
		$this->review_rating = (int) $rating;
	}

	public function set_user_id($user_id)
	{
		$this->review_user_id = (int) $user_id;
	}

	public function set_status($status)
	{
		$this->review_status = (int) $status;
	}
}