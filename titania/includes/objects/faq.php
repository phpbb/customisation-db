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
* Class to titania faq.
* @package Titania
*/
class titania_faq extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= TITANIA_CONTRIB_FAQ_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'faq_id';

	/**
	 * Text parsed for storage
	 *
	 * @var bool
	 */
	private $text_parsed_for_storage = false;

	/*
	 * Contrib data
	 *
	 * @var array
	 */
	public $contrib_data		= array();

	/**
	 * Constructor class for titania faq
	 *
	 * @param int $faq_id
	 */
	public function __construct($faq_id = false, $contrib_id)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'faq_id'		=> array('default' => 0),
			'contrib_id' 		=> array('default' => 0),
			'faq_order_id' 		=> array('default' => 0),
			'faq_subject' 		=> array('default' => '', 'max' => 255),
			'faq_text' 		=> array('default' => ''),
			'faq_text_bitfield'	=> array('default' => '', 'readonly' => true),
			'faq_text_uid'		=> array('default' => '', 'readonly' => true),
			'faq_text_options'	=> array('default' => 7, 'readonly' => true),
			'faq_views'		=> array('default' => 0),
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
		}

		$this->contrib_id = $contrib_id;

		// getting contrib data from the contribs table
		$this->get_contrib_data();
	}

	/**
	 * Update data or submit new faq
	 *
	 * @return void
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(true, true, false);
		}

		parent::submit();
	}

	/**
	 * Get faq data from the database
	 *
	 * @return void
	 */
	public function load()
	{
		parent::load();

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Delete an entry
	 *
	 * @return void
	 */
	public function delete()
	{
		parent::delete();
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
		$faq_text = $this->faq_text;
		$faq_text_uid = $this->faq_text_uid;
		$faq_text_bitfield = $this->faq_text_bitfield;
		$faq_text_options = $this->faq_text_options;

		generate_text_for_storage($faq_text, $faq_text_uid, $faq_text_bitfield, $faq_text_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->faq_text = $faq_text;
		$this->faq_text_uid = $faq_text_uid;
		$this->faq_text_bitfield = $faq_text_bitfield;
		$this->faq_text_options = $faq_text_options;

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Parse text for display
	 *
	 * @return string text content from database for display
	 */
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->faq_text, $this->faq_text_uid, $this->faq_text_bitfield, $this->faq_text_options);
	}

	/**
	 * Parse text for edit
	 *
	 * @return string text content from database for editing
	 */
	private function generate_text_for_edit()
	{
		$return = generate_text_for_edit($this->faq_text, $this->faq_text_uid, $this->faq_text_options);
		$this->faq_text = $return['text'];
	}

	/**
	 * Getter function for faq_text
	 *
	 * @param bool $editable
	 *
	 * @return string generate_text_for edit if editable is true, or display if false
	 */
	public function get_faq_text($editable = false)
	{
		// Text needs to be from database or parsed for database.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(true, true, false);
		}

		if ($editable)
		{
			$this->generate_text_for_edit();
		}
		else
		{
			$this->generate_text_for_display();
		}

		return $this->faq_text;
	}

	/**
	 * Setter function for faq_text
	 *
	 * @param string $text
	 * @param string $uid
	 * @param string $bitfield
	 * @param int $flags
	 *
	 * @return void
	 */
	public function set_faq_text($text, $uid = false, $bitfield = false, $flags = false)
	{
		$this->faq_text = $text;
		$this->text_parsed_for_storage = false;

		if ($uid !== false)
		{
			$this->faq_text_uid = $uid;
		}

		if ($bitfield !== false)
		{
			$this->faq_text_bitfield = $bitfield;
		}

		if ($flags !== false)
		{
			$this->faq_text_options = $flags;
		}
	}

	/**
	 * Get data about contrib
	 *
	 * @return void
	 */
	public function get_contrib_data()
	{
		$sql = 'SELECT *
			FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		$this->contrib_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$this->contrib_data)
		{
			trigger_error('CONTRIB_NOT_FOUND');
		}
	}

	/*
	 * Increase a FAQ views counter
	 *
	 * @return void
	 */
	public function increase_views_counter()
	{
		if (phpbb::$user->data['is_bot'])
		{
			return;
		}

		$sql = 'UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . '
			SET faq_views = faq_views + 1
			WHERE faq_id = ' . $this->faq_id;
		phpbb::$db->sql_query($sql);
	}

	/*
	 * Cleanup an entries order
	 *
	 * @return void
	 */
	public function cleanup_order()
	{
		$sql = 'SELECT faq_id, faq_order_id
			FROM ' . TITANIA_CONTRIB_FAQ_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id . '
			ORDER BY faq_order_id ASC';
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$order = 0;

			do
			{
				++$order;

				if ($row['faq_order_id'] != $order)
				{
					phpbb::$db->sql_query('UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . "
						SET faq_order_id = $order
						WHERE faq_id = {$row['faq_id']}");
				}
			}
			while ($row = phpbb::$db->sql_fetchrow($result));
		}
		phpbb::$db->sql_freeresult($result);
	}

	/*
	 * Obtain the next order id for a specified contrib
	 *
 	 * @return int
	 */
	public function get_next_order_id()
	{
		$sql = 'SELECT MAX(faq_order_id) as max_order_id
			FROM ' . TITANIA_CONTRIB_FAQ_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$max_order_id = phpbb::$db->sql_fetchfield('max_order_id');
		phpbb::$db->sql_freeresult($result);

		return $max_order_id + 1;
	}

	/*
	 * Return TRUE when an user is contrib's author 
	 *
 	 * @return bool
	 */	
	public function is_contrib_author()
	{
		return (phpbb::$user->data['user_id'] == $this->contrib_data['contrib_user_id']) ? true : false;
	}
}
