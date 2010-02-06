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
* Class to abstract titania queue
* @package Titania
*/
class titania_queue extends titania_message_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_QUEUE_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'queue_id';

	/**
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = TITANIA_QUEUE;

	/**
	* Unread
	*
	* @var bool
	*/
	public $unread = true;

	public function __construct()
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'queue_id'				=> array('default' => 0),
			'revision_id'			=> array('default' => 0),
			'contrib_id'			=> array('default' => 0),
			'contrib_name_clean'	=> array('default' => ''),
			'submitter_user_id'		=> array('default' => (int) phpbb::$user->data['user_id']),
			'queue_topic_id'		=> array('default' => 0),

			'queue_type'			=> array('default' => 0),
			'queue_status'			=> array('default' => TITANIA_QUEUE_NEW), // Uses either TITANIA_QUEUE_NEW or one of the tags for the queue status from the DB
			'queue_submit_time'		=> array('default' => titania::$time),
			'queue_close_time'		=> array('default' => 0),

			'queue_notes'			=> array('default' => '',	'message_field' => 'message'),
			'queue_notes_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'queue_notes_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'queue_notes_options'	=> array('default' => 7,	'message_field' => 'message_options'),

			'mpv_results'			=> array('default' => ''),
			'mpv_results_bitfield'	=> array('default' => ''),
			'mpv_results_uid'		=> array('default' => ''),
			'automod_results'		=> array('default' => ''),
		));
	}

	public function submit()
	{
		$sql = 'SELECT contrib_name_clean, contrib_type FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . (int) $this->contrib_id;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		$this->contrib_name_clean = $row['contrib_name_clean'];
		$this->queue_type = $row['contrib_type'];
	}

	public function assign_details($return = false)
	{
		$folder_img = $folder_alt = '';
		$this->folder_img($folder_img, $folder_alt);

		$output = array(
			'U_VIEW_TOPIC'				=> phpbb::append_sid('viewtopic', 't=' . $this->queue_topic_id),
			'U_VIEW_CONTRIB'			=> titania_url::build_url(titania_types::$types[$this->queue_type]->url . '/' . $this->contrib_name_clean . '/'),

			'S_UNREAD'					=> ($this->unread) ? true : false,

			'FOLDER_IMG'				=> phpbb::$user->img($folder_img, $folder_alt),
			'FOLDER_IMG_SRC'			=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
			'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'FOLDER_IMG_ALT'			=> phpbb::$user->lang[$folder_alt],
			'FOLDER_IMG_WIDTH'			=> phpbb::$user->img($folder_img, '', false, '', 'width'),
			'FOLDER_IMG_HEIGHT'			=> phpbb::$user->img($folder_img, '', false, '', 'height'),
		);

		if ($return)
		{
			return $output;
		}

		phpbb::$template->assign_vars($output);
	}

	/**
	* Generate topic status
	*/
	public function folder_img(&$folder_img, &$folder_alt)
	{
		titania::_include('functions_display', 'titania_topic_folder_img');

		titania_topic_folder_img($folder_img, $folder_alt, 0, $this->unread);
	}
}