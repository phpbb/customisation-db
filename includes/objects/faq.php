<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

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

	/** @param \titania_contribution */
	public $contrib;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

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
			'faq_subject' 		=> array('default' => '',	'message_field' => 'subject', 'max' => 255),
			'faq_text' 			=> array('default' => '',	'message_field' => 'message'),
			'faq_text_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'faq_text_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'faq_text_options'	=> array('default' => 7,	'message_field' => 'message_options'),
			'faq_views'			=> array('default' => 0),
			'faq_access'		=> array('default' => 2,	'message_field'	=> 'access'),
			'left_id'			=> array('default' => 0),
			'right_id'			=> array('default' => 0),
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
			$this->load();
		}

		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
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
		else if (phpbb::$config['max_post_chars'] > 0 && $message_length > (int) phpbb::$config['max_post_chars'])
		{
			$error[] = sprintf(phpbb::$user->lang['TOO_MANY_CHARS_POST'], $message_length, (int) phpbb::$config['max_post_chars']);
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
			'contrib_id'		=> $this->contrib->contrib_id,
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
		$faq_id =  ($faq_id) ? (int) $faq_id : $this->faq_id;

		$controller = 'phpbb.titania.contrib.faq.item';
		$parameters = array(
			'contrib_type'	=> $this->contrib->type->url,
			'contrib'		=> $this->contrib->contrib_name_clean,
		);

		if ($action)
		{
			if ($action == 'create')
			{
				return $this->controller_helper->route('phpbb.titania.contrib.faq.create', $parameters);
			}

			$controller .= '.action';
			$parameters['action'] = $action;
		}
		$parameters['id'] = $faq_id;

		return $this->controller_helper->route($controller, $parameters);
	}

	/**
	 * Update data or submit new faq
	 *
	 * @return void
	 */
	public function submit()
	{
		// Get the FAQ count to update it
		$sql = 'SELECT contrib_faq_count FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);
		$contrib_faq_count = phpbb::$db->sql_fetchfield('contrib_faq_count');
		phpbb::$db->sql_freeresult();

		// If already submitted we need to decrement first
		if ($this->faq_id)
		{
			if (empty($this->sql_data))
			{
				throw new exception('Modifying a FAQ entry requires you load it through the load() function (we require the original information).');
			}

			$original_flags = titania_count::update_flags($this->sql_data['faq_access']);

			$contrib_faq_count = titania_count::decrement($contrib_faq_count, $original_flags);
		}

		// Update the FAQ count
		$flags = titania_count::update_flags($this->faq_access);

		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_faq_count = \'' . phpbb::$db->sql_escape(titania_count::increment($contrib_faq_count, $flags)) . '\'
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		// Submit this FAQ item
		parent::submit();

		// Index
		titania_search::index(TITANIA_FAQ, $this->faq_id, array(
			'title'			=> $this->faq_subject,
			'text'			=> $this->faq_text,
			'text_uid'		=> $this->faq_text_uid,
			'text_bitfield'	=> $this->faq_text_bitfield,
			'text_options'	=> $this->faq_text_options,
			'author'		=> 0,
			'date'			=> 0,
			'url'			=> serialize(array(
				'contrib_type'	=> $this->contrib->type->url,
				'contrib'		=> $this->contrib->contrib_name_clean
			)),
			'access_level'	=> $this->faq_access,
		));
	}

	public function delete()
	{
		titania_search::delete(TITANIA_FAQ, $this->faq_id);

		// Update the FAQ count
		$sql = 'SELECT contrib_faq_count FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);
		$contrib_faq_count = phpbb::$db->sql_fetchfield('contrib_faq_count');
		phpbb::$db->sql_freeresult();

		$flags = titania_count::update_flags($this->faq_access);

		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_faq_count = \'' . phpbb::$db->sql_escape(titania_count::decrement($contrib_faq_count, $flags)) . '\'
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		parent::delete();
	}

	/**
	* Move a FAQ item
	*
	* @param string $action (move_up|move_down)
	* @param int $steps		Number of steps to move by. Defaults to 1.
	*
	* @return bool Returns true on success.
	*/
	public function move($action = 'move_up', $steps = 1)
	{
		/**
		* Fetch all the siblings between the faq's current spot
		* and where we want to move it to. If there are less than $steps
		* siblings between the current spot and the target then the
		* faq will move as far as possible
		*/

		if ($action == 'move_up')
		{
			$sql_extra = "right_id < {$this->right_id} ORDER BY right_id DESC";
		}
		else
		{
			$sql_extra = "left_id > {$this->left_id} ORDER BY left_id ASC";
		}

		$sql = 'SELECT faq_id, left_id, right_id
			FROM ' . $this->sql_table . '
			WHERE contrib_id = ' . $this->contrib_id . '
				AND ' . $sql_extra;
		$result = phpbb::$db->sql_query_limit($sql, $steps);

		$target = array();
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$target = $row;
		}
		phpbb::$db->sql_freeresult($result);

		if (!sizeof($target))
		{
			// The faq is already on top or bottom
			return false;
		}

		/**
		* $left_id and $right_id define the scope of the nodes that are affected by the move.
		* $diff_up and $diff_down are the values to substract or add to each node's left_id
		* and right_id in order to move them up or down.
		* $move_up_left and $move_up_right define the scope of the nodes that are moving
		* up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		*/
		if ($action == 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $this->right_id;

			$diff_up = $this->left_id - $target['left_id'];
			$diff_down = $this->right_id + 1 - $this->left_id;

			$move_up_left = $this->left_id;
			$move_up_right = $this->right_id;
		}
		else
		{
			$left_id = $this->left_id;
			$right_id = $target['right_id'];

			$diff_up = $this->right_id + 1 - $this->left_id;
			$diff_down = $target['right_id'] - $this->right_id;

			$move_up_left = $this->right_id + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->sql_table . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END
			WHERE contrib_id = " . $this->contrib_id . "
				AND left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
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

	/**
	* Set the left/right id values for a newly created item.
	*
	* @return null
	*/
	public function set_left_right_ids()
	{
		$sql = "SELECT right_id
			FROM {$this->sql_table}
			WHERE contrib_id = " . (int) $this->contrib_id . '
			ORDER BY right_id DESC';
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$right_id = (int) phpbb::$db->sql_fetchfield('right_id');
		phpbb::$db->sql_freeresult($result);

		// Update the faqs table
		$sql_ary = array(
			'left_id'	=> $right_id + 1,
			'right_id'	=> $right_id + 2,
		);

		$sql = "UPDATE {$this->sql_table}
			SET " . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE faq_id = ' . (int) $this->faq_id;
		phpbb::$db->sql_query($sql);
	}
}
