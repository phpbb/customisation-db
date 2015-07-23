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
 * Class to abstract attention items
 * @package Titania
 */
class titania_attention extends \phpbb\titania\entity\database_base
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

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

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
			'notify_reporter'				=> array('default' => 0),
		));

		$this->db = phpbb::$container->get('dbal.conn');
		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$this->path_helper = phpbb::$container->get('path_helper');
		$this->subscriptions = phpbb::$container->get('phpbb.titania.subscriptions');
		$this->phpbb_root_path = \phpbb::$root_path;
		$this->php_ext = \phpbb::$php_ext;
	}

	/**
	* Check user's auth.
	*
	* @return bool
	*/
	public function check_auth()
	{
		return false;
	}

	public function submit()
	{
		// Subscriptions
		if (!$this->attention_id)
		{
			$u_view = $this->controller_helper->route('phpbb.titania.manage.attention.redirect', array(
				'type'	=> $this->attention_type,
				'id'	=> $this->attention_object_id,
			));
			$email_vars = array(
				'NAME'		=> $this->attention_title,
				'U_VIEW'	=> $this->path_helper->strip_url_params($u_view, 'sid'),
			);
			$this->subscriptions->send_notifications(
				TITANIA_ATTENTION,
				0,
				'subscribe_notify.txt',
				$email_vars,
				$this->attention_poster_id
			);
		}

		parent::submit();
	}

	/**
	* Close the attention
	*/
	public function close()
	{
		if (!$this->is_open())
		{
			return;
		}
		$this->attention_close_time = titania::$time;
		$this->attention_close_user = phpbb::$user->data['user_id'];

		$this->submit();
	}

	/**
	* Get attention title.
	*
	* @return string
	*/
	public function get_title()
	{
		return $this->attention_title;
	}

	/**
	* Get the URL for the item needing attention
	*
	* @return string the built url
	*/
	public function get_url()
	{
		return '';
	}

	/**
	* Get URL to attention report.
	*
	* @param bool|string $action	Optional actional to link to.
	* @param array $params			Additional parameters to add.
	*
	* @return string
	*/
	public function get_report_url($action = false, $params = array())
	{
		$controller = 'phpbb.titania.manage.attention.item';
		$params['id'] = $this->attention_id;

		if ($action)
		{
			$controller .= '.action';
			$params['action'] = $action;
		}

		return $this->controller_helper->route($controller, $params);
	}

	/**
	* Check whether attention item is a report.
	*
	* @return bool
	*/
	public function is_report()
	{
		return !in_array($this->attention_type, array(
			TITANIA_ATTENTION_UNAPPROVED,
		));
	}

	/**
	* Check whether attention item is still open.
	*
	* @return bool
	*/
	public function is_open()
	{
		return empty($this->attention_close_time);
	}

	/**
	* Assign the details for the attention object
	*
	* @param bool $return True to return the data, false to display it
	*/
	public function assign_details($return = false)
	{
		// Does the item need handling? This only applies to "reports" - those which
		// only need a simple close/delete action.
		$needs_handling = $this->is_open() && $this->is_report();
		$action_param = array('hash' => generate_link_hash('attention_action'));

		$output = array(
			'ATTENTION_ID'			=> $this->attention_id,
			'ATTENTION_TYPE'		=> $this->attention_type,
			'ATTENTION_TIME'		=> phpbb::$user->format_date($this->attention_time),
			'ATTENTION_POST_TIME'	=> phpbb::$user->format_date($this->attention_post_time),
			'ATTENTION_CLOSE_TIME'	=> ($this->attention_close_time) ? phpbb::$user->format_date($this->attention_close_time) : '',
			'ATTENTION_TITLE'		=> $this->attention_title,
			'ATTENTION_REASON'		=> $this->get_lang_string('reason'),
			'ATTENTION_DESCRIPTION'	=> $this->attention_description,

			'CLOSED_LABEL'			=> $this->get_lang_string('closed'),
			'CLOSED_BY_LABEL'		=> $this->get_lang_string('closed_by'),
			'OBJECT_LABEL'			=> $this->get_lang_string('object'),

			'U_CLOSE'				=> ($needs_handling) ? $this->get_report_url('close', $action_param) : false,
			'U_DELETE'				=> ($needs_handling) ? $this->get_report_url('delete', $action_param) : false,
			'U_VIEW_ATTENTION'		=> $this->get_url(),
			'U_VIEW_DETAILS'		=> $this->get_report_url(),

			'S_CLOSED'				=> !$this->is_open(),
			'S_REPORTED'			=> $this->is_report(),
		);

		$output = array_merge($output, $this->get_extra_details());

		if ($return)
		{
			return $output;
		}

		phpbb::$template->assign_vars($output);
	}

	/**
	* Get extra details to assign to the template
	*/
	public function get_extra_details()
	{
		return array();
	}

	/**
	* The report has been handled, so close it.
	*/
	public function report_handled()
	{
		if (!$this->is_open())
		{
			return;
		}
		$this->close();

		// Send notification to reporter
		if ($this->notify_reporter)
		{
			$this->notify_reporter_closed();
		}
	}

	/**
	* Notify reporter of the report being closed
	*/
	public function notify_reporter_closed()
	{
		$message_vars = array(
			'ATTENTION_TITLE'	=> htmlspecialchars_decode(censor_text($this->attention_title)),
			'CLOSER_NAME'		=> htmlspecialchars_decode(phpbb::$user->data['username']),
		);

		$this->notify_user($this->attention_poster_id, 'report_closed', $message_vars);
	}

	/**
	* Send an individual a notification.
	* @todo This should probably be moved somewhere else so it can be reused.
	*
	* @param int $user_id
	* @param string $email_template
	* @param array $message_vars Additional variables for email message.
	*/
	public function notify_user($user_id, $email_template, $message_vars)
	{
		if ($user_id == ANONYMOUS)
		{
			return;
		}

		phpbb::_include('functions_messenger', false, 'messenger');

		$lang_path = phpbb::$user->lang_path;
		phpbb::$user->set_custom_lang_path(titania::$config->language_path);

		$messenger = new messenger(false);

		users_overlord::load_users(array($user_id));

		$messenger->template($email_template, users_overlord::get_user($user_id, 'user_lang'));

		$messenger->to(users_overlord::get_user($user_id, 'user_email'), users_overlord::get_user($user_id, '_username'));

		$messenger->assign_vars(array_merge($message_vars, array(
			'USERNAME'		=> htmlspecialchars_decode(users_overlord::get_user($user_id, '_username')),
		)));

		$messenger->send();

		phpbb::$user->set_custom_lang_path($lang_path);
		// This gets reset when $template->_tpl_load() gets called
		phpbb::$user->theme['template_inherits_id'] = 1;
	}

	/**
	* Create inline diff for two versions of a report description.
	*
	* @return string Returns diff or original description if the description if there aren't two versions to compare.
	*/
	public function get_description_diff()
	{
		// Get rid of <br /> tags as they seem to interfere with the diff engine
		// \n is sufficient to represent line breaks
		$temp = str_replace('<br />', '', $this->attention_description);
		$split_pos = strpos($temp, '>>>>>>>>>>');

		if ($split_pos !== false)
		{
			if (!class_exists('diff_engine'))
			{
				include($this->phpbb_root_path . 'includes/diff/engine.' . $this->php_ext);
				include($this->phpbb_root_path . 'includes/diff/diff.' . $this->php_ext);
				include($this->phpbb_root_path . 'includes/diff/renderer.' . $this->php_ext);
			}

			$old = substr($temp, 0, $split_pos);
			$new = substr($temp, $split_pos + 10);

			$diff = new diff($old, $new);
			$renderer = new diff_renderer_inline();
			// <pre> is used to display the diff, so get rid of \n to get remove double line spacing
			$desc_diff = str_replace("\n", '', html_entity_decode($renderer->get_diff_content($diff)));

			return $desc_diff;
		}

		return $this->attention_description;
	}
}
