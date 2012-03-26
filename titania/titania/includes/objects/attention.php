<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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
 * Class to abstract attention items
 * @package Titania
 */
class titania_attention extends titania_database_object
{
	/**
	 * Database table to be used
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_ATTENTION_TABLE;

	/**
	 * Primary sql identifier
	 *
	 * @var string
	 */
	protected $sql_id_field = 'attention_id';

	/**
	 * Constructor class for the attention object
	 */
	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'attention_id'					=> array('default' => 0),
			'attention_type'				=> array('default' => 0), // attention type constants (reported, needs approval, etc)
			'attention_object_type'			=> array('default' => 0),
			'attention_object_id'			=> array('default' => 0),
			'attention_poster_id'			=> array('default' => 0),
			'attention_post_time'			=> array('default' => 0),
			'attention_url'					=> array('default' => ''),
			'attention_requester'			=> array('default' => (int) phpbb::$user->data['user_id']),
			'attention_time'				=> array('default' => titania::$time),
			'attention_close_time'			=> array('default' => 0),
			'attention_close_user'			=> array('default' => 0),
			'attention_title'				=> array('default' => ''),
			'attention_description'			=> array('default' => ''),
		));
	}

	public function submit()
	{
		$this->attention_url = titania_url::unbuild_url($this->attention_url);

		// Subscriptions
		if (!$this->attention_id)
		{
			$email_vars = array(
				'NAME'		=> $this->attention_title,
				'U_VIEW'	=> titania_url::build_url('manage/attention', array('type' => $this->attention_type, 'id' => $this->attention_object_id)),
			);
			titania_subscriptions::send_notifications(TITANIA_ATTENTION, 0, 'subscribe_notify.txt', $email_vars, $this->attention_poster_id);
		}

		parent::submit();
	}

	/**
	* Close the attention
	*/
	public function close()
	{
		$this->attention_close_time = titania::$time;
		$this->attention_close_user = phpbb::$user->data['user_id'];

		$this->submit();
	}

	/**
	* Get the URL for the item needing attention
	*
	* @return string the built url
	*/
	public function get_url()
	{
		$base = $append = false;
		titania_url::split_base_params($base, $append, $this->attention_url);

		return titania_url::build_url($base, $append);
	}

	/**
	* Assign the details for the attention object
	*
	* @param bool $return True to return the data, false to display it
	*/
	public function assign_details($return = false)
	{
		$output = array(
			'ATTENTION_ID'			=> $this->attention_id,
			'ATTENTION_TYPE'		=> $this->attention_type,
			'ATTENTION_TIME'		=> phpbb::$user->format_date($this->attention_time),
			'ATTENTION_POST_TIME'	=> phpbb::$user->format_date($this->attention_post_time),
			'ATTENTION_CLOSE_TIME'	=> ($this->attention_close_time) ? phpbb::$user->format_date($this->attention_close_time) : '',
			'ATTENTION_TITLE'		=> $this->attention_title,
			'ATTENTION_REASON'		=> $this->get_reason_string(),
			'ATTENTION_DESCRIPTION'	=> $this->attention_description,

			'U_VIEW_ATTENTION'		=> $this->get_url(),
			'U_VIEW_DETAILS'		=> titania_url::append_url(titania_url::$current_page_url, array('a' => $this->attention_id)),

			'S_CLOSED'				=> ($this->attention_close_time) ? true : false,
			'S_UNAPPROVED'			=> ($this->attention_type == TITANIA_ATTENTION_UNAPPROVED) ? true : false,
			'S_REPORTED'			=> ($this->attention_type == TITANIA_ATTENTION_REPORTED) ? true : false,
		);

		if ($return)
		{
			return $output;
		}

		phpbb::$template->assign_vars($output);
	}

	public function get_reason_string()
	{
		switch ((int) $this->attention_type)
		{
			case TITANIA_ATTENTION_REPORTED :
				return phpbb::$user->lang['REPORTED'];
			break;

			case TITANIA_ATTENTION_UNAPPROVED :
				return phpbb::$user->lang['UNAPPROVED'];
			break;
		}
	}
}
