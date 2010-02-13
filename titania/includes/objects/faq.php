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

if (!class_exists('titania_message_object'))
{
	require TITANIA_ROOT . 'includes/core/object_message.' . PHP_EXT;
}

/**
* Class to titania faq.
* @package Titania
*/
class titania_faq extends titania_message_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_CONTRIB_FAQ_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'faq_id';

	/**
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = TITANIA_FAQ;

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
			'faq_subject' 		=> array('default' => '',	'message_field' => 'subject', 'max' => 255),
			'faq_text' 			=> array('default' => '',	'message_field' => 'message'),
			'faq_text_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'faq_text_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'faq_text_options'	=> array('default' => 7,	'message_field' => 'message_options'),
			'faq_views'			=> array('default' => 0),
			'faq_access'		=> array('default' => 2,	'message_field'	=> 'access'),
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
			$this->load();
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
	* @param object $message The message object
	*/
	public function post_data($message)
	{
		$this->__set_array(array(
			'contrib_id'		=> titania::$contrib->contrib_id,
		));

		parent::post_data($message);
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
			return titania_url::append_url($url, array('action' => $action));
		}
		else if (!$action)
		{
			return titania_url::append_url($url, array('f' => $faq_id, '#' => 'details'));
		}

		return titania_url::append_url($url, array('action' => $action, 'f' => $faq_id));
	}

	/**
	 * Update data or submit new faq
	 *
	 * @return void
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->message_parsed_for_storage)
		{
			$this->generate_text_for_storage();
		}

		titania_search::index(TITANIA_FAQ, $this->faq_id, array(
			'title'			=> $this->faq_subject,
			'text'			=> $this->faq_text,
			'text_uid'		=> $post->faq_text_uid,
			'text_bitfield'	=> $post->faq_text_bitfield,
			'text_options'	=> $post->faq_text_options,
			'author'		=> 0,
			'date'			=> 0,
			'url'			=> titania_url::unbuild_url($this->get_url()),
			'access_level'	=> $this->faq_access,
		));

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
			FROM ' . $this->sql_table . '
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
		 $sql = 'UPDATE ' . $this->sql_table . '
			SET faq_order_id = ' . $this->faq_order_id . '
		 	WHERE faq_order_id = ' . $new_order_id . '
				AND contrib_id = ' . $this->contrib_id;
		 phpbb::$db->sql_query($sql);

		// Update the current faq item to have the new position
		 $sql = 'UPDATE ' . $this->sql_table . '
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

		$sql = 'UPDATE ' . $this->sql_table . '
			SET faq_views = faq_views + 1
			WHERE faq_id = ' . (int) $this->faq_id;
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
			FROM ' . $this->sql_table . '
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
					phpbb::$db->sql_query('UPDATE ' . $this->sql_table . "
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
			FROM ' . $this->sql_table . '
			WHERE contrib_id = ' . titania::$contrib->contrib_id;
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$max_order_id = phpbb::$db->sql_fetchfield('max_order_id');
		phpbb::$db->sql_freeresult($result);

		return $max_order_id + 1;
	}
}
