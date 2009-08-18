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

	/**
	 * Constructor class for titania faq
	 *
	 * @param int $faq_id
	 */
	public function __construct($faq_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'faq_id'			=> array('default' => 0),
			'contrib_id' 		=> array('default' => 0),
			'faq_order_id' 		=> array('default' => 0),
			'faq_subject' 		=> array('default' => '', 'max' => 255),
			'faq_text' 			=> array('default' => ''),
			'faq_text_bitfield'	=> array('default' => '', 'readonly' => true),
			'faq_text_uid'		=> array('default' => '', 'readonly' => true),
			'faq_text_options'	=> array('default' => 7, 'readonly' => true),
			'faq_views'			=> array('default' => 0),
			'faq_access'		=> array('default' => 2),
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
		}
	}

	/**
	* Validate that all the data is correct
	*
	* @return array empty array on success, array with (string) errors ready for output on failure
	*/
	public function validate()
	{
		$error = array();

		if (utf8_clean_string($this->faq_subject) === '')
		{
			$error[] = phpbb::$user->lang['EMPTY_SUBJECT'];
		}

		$message_length = utf8_strlen($this->faq_text);
		if ($message_length < (int) phpbb::$config['min_post_chars'])
		{
			$error[] = sprintf(phpbb::$user->lang['TOO_FEW_CHARS_LIMIT'], $message_length, (int) phpbb::$config['min_post_chars']);
		}
		else if ($message_length > (int) phpbb::$config['max_post_chars'])
		{
			$error[] = sprintf($user->lang['TOO_MANY_CHARS_POST'], $message_length, (int) phpbb::$config['max_post_chars']);
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
			'contrib_id'		=> titania::$contrib->contrib_id,
			'faq_subject'		=> $post_data['subject'],
			'faq_text'			=> $post_data['message'],
			'faq_access'		=> $post_data['access'],
		));

		$this->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);
	}

	/**
	* Build view URL for a faq
	*
	* @param string $action
	* @param int $faq_id
	*
	* @return string
	*/
	public function get_url($action = '', $faq_id = false)
	{
		$url = titania::$contrib->get_url('faq');
		$faq_id = (($faq_id) ? $faq_id : $this->faq_id);

		if ($action == 'create')
		{
			return titania::$url->append_url($url, array('action' => $action));
		}
		else if (!$action)
		{
			return titania::$url->append_url($url, array('f' => $faq_id, '#' => 'details'));
		}

		return titania::$url->append_url($url, array('action' => $action, 'f' => $faq_id));
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
	public function generate_text_for_storage($allow_bbcode = false, $allow_urls = false, $allow_smilies = false)
	{
		generate_text_for_storage($this->faq_text, $this->faq_text_uid, $this->faq_text_bitfield, $this->faq_text_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Parse text for display
	 *
	 * @return string text content from database for display
	 */
	public function generate_text_for_display()
	{
		return generate_text_for_display($this->faq_text, $this->faq_text_uid, $this->faq_text_bitfield, $this->faq_text_options);
	}

	/**
	 * Parse text for edit
	 *
	 * @return string text content from database for editing
	 */
	public function generate_text_for_edit()
	{
		return array_merge(generate_text_for_edit($this->faq_text, $this->faq_text_uid, $this->faq_text_options), array(
			'options'	=> $this->faq_text_options,
			'subject'	=> $this->faq_subject,
		));
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
			$this->generate_text_for_storage();
		}

		parent::submit();
	}

	/**
	* Move a FAQ item
	*
	* @param string $direction (up|down)
	*/
	public function move($direction)
	{
		$sql = 'SELECT faq_order_id
			FROM ' . TITANIA_CONTRIB_FAQ_TABLE . '
			WHERE faq_order_id ' . (($direction == 'move_up') ? '<' : '>') . $this->faq_order_id . '
				AND contrib_id = ' . $this->contrib_id . '
			ORDER BY faq_order_id ' . (($direction == 'move_up') ? 'DESC' : 'ASC');
		phpbb::$db->sql_query_limit($sql, 1);
		$new_order_id = phpbb::$db->sql_fetchfield('faq_order_id');
		phpbb::$db->sql_freeresult();

		if ($new_order_id === false)
		{
			return false;
		}

		// Update the item in the position where want to move it to have the current position
		 $sql = 'UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . '
			SET faq_order_id = ' . $this->faq_order_id . '
		 	WHERE faq_order_id = ' . $new_order_id . '
				AND contrib_id = ' . $this->contrib_id;
		 phpbb::$db->sql_query($sql);

		// Update the current faq item to have the new position
		 $sql = 'UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . '
			SET faq_order_id = ' . $new_order_id . '
		 	WHERE faq_id = ' . $this->faq_id;
		 phpbb::$db->sql_query($sql);

		 return true;
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
			WHERE contrib_id = ' . titania::$contrib->contrib_id . '
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
			WHERE contrib_id = ' . titania::$contrib->contrib_id;
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$max_order_id = phpbb::$db->sql_fetchfield('max_order_id');
		phpbb::$db->sql_freeresult($result);

		return $max_order_id + 1;
	}
}
