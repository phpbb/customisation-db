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

namespace phpbb\titania\controller\contribution;

class faq extends base
{
	/** @var \phpbb\titania\tracking */
	protected $tracking;

	/** @var \phpbb\titania\sort */
	protected $sort;

	/** @var \titania_faq */
	protected $faq;

	/** @var int */
	protected $id;

	/** @var bool */
	protected $is_moderator;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\controller\helper $helper
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\access $access
	 * @param \phpbb\titania\tracking $tracking
	 * @param \phpbb\titania\sort $sort
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\cache\service $cache, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\access $access, \phpbb\titania\tracking $tracking, \phpbb\titania\sort $sort)
	{
		parent::__construct($auth, $config, $db, $template, $user, $helper, $request, $cache, $ext_config, $display, $access);

		$this->tracking = $tracking;
		$this->sort = $sort;
	}

	/**
	* Display FAQ item.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_item($contrib_type, $contrib, $id)
	{
		$this->setup($contrib_type, $contrib);
		$this->load_item($id);

		if ($this->faq->faq_access < $this->access->get_level())
		{
			return $this->helper->needs_auth();
		}

		// Increase a FAQ views counter
		$this->faq->increase_views_counter();

		// Tracking
		$this->tracking->track(TITANIA_FAQ, $this->id);

		$message = $this->faq->generate_text_for_display();

		// Grab attachments
		$attachments = new \titania_attachment(TITANIA_FAQ, $this->id);
		$attachments->load_attachments();
		$parsed_attachments = $attachments->parse_attachments($message);

		foreach ($parsed_attachments as $attachment)
		{
			$this->template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment,
			));
		}

		$this->template->assign_vars(array(
			'FAQ_SUBJECT'			=> $this->faq->faq_subject,
			'FAQ_TEXT'				=> $message,
			'FAQ_VIEWS'				=> $this->faq->faq_views,

			'S_DETAILS'				=> true,
			'S_ACCESS_TEAMS'		=> $this->access->is_team($this->faq->faq_access),
			'S_ACCESS_AUTHORS'		=> $this->access->is_author($this->faq->faq_access),

			'U_CANONICAL'			=> $this->faq->get_url(),
			'U_EDIT_FAQ'			=> ($this->check_auth('edit')) ? $this->faq->get_url('edit') : false,
		));
		$this->assign_vars();

		return $this->helper->render(
			'contributions/contribution_faq.html',
			$this->faq->faq_subject . ' - ' . $this->contrib->contrib_name
		);
	}

	/**
	* Display FAQ item list.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_list($contrib_type, $contrib)
	{
		$this->setup($contrib_type, $contrib);

		// Setup the sort tool
		$this->sort
			->set_url($this->contrib->get_url('faq'))
			->set_defaults($this->config['topics_per_page'])
			->request()
		;

		// Define permissions here so we don't have to check these in a loop.
		$auth = array(
			'move'		=> $this->check_auth('move'),
			'edit'		=> $this->check_auth('edit'),
			'delete'	=> $this->check_auth('delete'),	
			'create'	=> $this->check_auth('create'),
		);

		// Output items.
		foreach ($this->get_items() as $id => $data)
		{
			$this->assign_item_row_vars($data, $auth);
		}

		$this->template->assign_vars(array(
			'S_LIST'					=> true,

			'U_CANONICAL'				=> $sort->build_canonical(),
			'U_CREATE_FAQ'				=> ($auth['create']) ? $this->faq->get_url('create') : false,
		));
		$this->assign_vars();

		return $this->helper->render(
			'contributions/contribution_faq.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['FAQ_LIST']
		);
	}

	/**
	* Delegates requested action to appropriate method.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	* @param int $id					FAQ item id.
	* @param string $action				Action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function item_action($contrib_type, $contrib, $id, $action)
	{
		if (!in_array($action, array('create', 'edit', 'delete', 'move_up', 'move_down')))
		{
			return $this->helper->error('INVALID_ACTION');
		}

		$this->setup($contrib_type, $contrib);

		if ($action != 'create')
		{
			$this->load_item($id);
		}

		if (!$this->check_auth($action))
		{
			return $this->helper->needs_auth();
		}
		$this->assign_vars();

		return $this->{$action}();
	}

	/**
	* Edit action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function edit()
	{
		if ($this->common_post())
		{
			redirect($this->faq->get_url());
		}

		$this->template->assign_vars(array(
			'L_POST_A'			=> $this->user->lang['EDIT_FAQ'],
			'S_POST_ACTION'		=> $this->faq->get_url('edit', $this->id),
		));

		return $this->helper->render('contributions/contribution_faq.html', 'EDIT_FAQ');
	}

	/**
	* Create action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function create()
	{
		if ($this->common_post())
		{
			$this->faq->set_left_right_ids();
			redirect($this->faq->get_url());
		}

		$this->template->assign_vars(array(
			'L_POST_A'			=> $this->user->lang['CREATE_FAQ'],
			'S_POST_ACTION'		=> $this->faq->get_url('create'),
		));

		return $this->helper->render('contributions/contribution_faq.html', 'CREATE_FAQ');
	}

	/**
	* Common handler for edit/create action.
	*
	* @return bool Returns true if item was submitted.
	*/
	protected function common_post()
	{
		// Load the message object
		$this->message = new \titania_message($this->faq);
		$this->message->set_auth(array(
			'bbcode'		=> $this->auth->acl_get('u_titania_bbcode'),
			'smilies'		=> $this->auth->acl_get('u_titania_smilies'),
			'attachments'	=> true,
		));

		// Submit check...handles running $this->faq->post_data() if required
		$submit = $this->message->submit_check();
		$error = $this->message->error;

		if ($submit)
		{
			$error = array_merge($error, $this->faq->validate());

			if (($validate_form_key = $this->message->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}

			if (empty($error))
			{
				$this->faq->submit();
				$this->message->submit($this->id);

				return true;
			}
		}

		$this->template->assign_vars(array(
			'S_EDIT'		=> true,
			'ERROR_MSG'		=> (!empty($error)) ? implode('<br />', $error) : false,
		));
		$this->message->display();

		return false;
	}

	/**
	* Move up item.
	*
	* @return null
	*/
	protected function move_up()
	{
		return $this->move('up');
	}

	/**
	* Move down item.
	*
	* @return null
	*/
	protected function move_down()
	{
		return $this->move('down');
	}

	/**
	* Move item.
	*
	* @param string $direction		Direction: up|down
	* @return null
	*/
	protected function move($direction)
	{
		$this->faq->move("move_$direction");
		redirect($this->contrib->get_url('faq'));
	}

	/**
	* Delete action.
	*
	* @return null
	*/
	protected function delete()
	{
		if (confirm_box(true))
		{
			$this->faq->delete();
		}
		else
		{
			confirm_box(false, 'DELETE_FAQ', $this->faq->get_url('delete'));
		}

		redirect($this->contrib->get_url('faq'));
	}

	/**
	* Load FAQ item.
	*
	* @param int $id		FAQ id.
	* @throws \Exception	Throws exception if no item found.
	* @return null
	*/
	protected function load_item($id)
	{
		$this->id = (int) $id;
		$this->faq->load($this->id);

		if (!$this->faq->faq_id || $this->faq->contrib_id !== $this->contrib->contrib_id)
		{
			throw new \Exception($this->user->lang['FAQ_NOT_FOUND']);
		}
	}

	/**
	* Do some common set up tasks.
	*
	* @param string $contrib_type	Contrib type URL identifier.
	* @param string $contrib		Contrib name clean.
	* @return null
	*/
	protected function setup($contrib_type, $contrib)
	{
		$this->user->add_lang_ext('phpbb/titania', 'faq');
		$this->load_contrib($contrib_type, $contrib);
		$this->faq = new \titania_faq;
		$this->faq->contrib = $this->contrib;
		$this->is_moderator = $this->auth->acl_get('u_titania_mod_faq_mod');
	}

	/**
	* Check user's authorization.
	*
	* @param bool|string $action	Optional action to check auth for.
	* @return bool Returns true if authorized. False otherwise.
	*/
	protected function check_auth($action = false)
	{
		$action_auth = ($action && strpos($action, 'move') !== 0) ? $this->auth->acl_get('u_titania_faq_' . $action) : true;

		return $this->is_moderator || ($this->is_author && $action_auth);
	}

	/**
	* Get contribution's FAQ items limited by the $sort options.
	*
	* @return array Returns FAQ item data matching the sort options.
	*/
	protected function get_items()
	{
		$items = array();

		$sql_ary = array(
			'SELECT' => 'f.*',
			'FROM'		=> array(
				TITANIA_CONTRIB_FAQ_TABLE => 'f',
			),
			'WHERE' => 'f.contrib_id = ' . (int) $this->contrib->contrib_id . '
				AND f.faq_access >= ' . $this->access->get_level(),
			'ORDER_BY'	=> 'f.left_id ASC',
		);

		// Main SQL Query
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);

		// Handle pagination
		if ($this->sort->sql_count($sql_ary, 'faq_id'))
		{
			$this->sort->build_pagination($this->contrib->get_url('faq'));

			// Get the data
			$result = $this->db->sql_query_limit($sql, $this->sort->limit, $this->sort->start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$items[$row['faq_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			// Grab the tracking info
			$this->tracking->get_tracks(TITANIA_FAQ, array_keys($items));
		}

		return $items;
	}

	/**
	* Assign template variables for a given FAQ item.
	*
	* @param array $data	Item data.
	* @param array $auth	Array in form of array(edit => (bool), delete => (bool), move => (bool))
	*	specifying user's permissions.
	* @return null
	*/
	protected function assign_item_row_vars($data, $auth)
	{
		$this->faq->__set_array($data);

		// @todo probably should setup an edit time or something for better read tracking in case it was edited
		$folder_img = $folder_alt = '';
		$unread = $this->tracking->get_track(TITANIA_FAQ, $data['faq_id'], true) === 0;
		$this->display->topic_folder_img($folder_img, $folder_alt, 0, $unread);

		$this->template->assign_block_vars('faqlist', array(
			'U_FAQ'							=> $this->faq->get_url(),

			'SUBJECT'						=> $data['faq_subject'],
			'VIEWS'							=> $data['faq_views'],

			'FOLDER_STYLE'					=> $folder_img,
			'FOLDER_IMG'					=> $this->user->img($folder_img, $folder_alt),
			'FOLDER_IMG_SRC'				=> $this->user->img($folder_img, $folder_alt, false, '', 'src'),
			'FOLDER_IMG_ALT'				=> $this->user->lang[$folder_alt],
			'FOLDER_IMG_ALT'				=> $this->user->lang[$folder_alt],
			'FOLDER_IMG_WIDTH'				=> $this->user->img($folder_img, '', false, '', 'width'),
			'FOLDER_IMG_HEIGHT'				=> $this->user->img($folder_img, '', false, '', 'height'),

			'U_MOVE_UP'						=> ($auth['move']) ? $this->faq->get_url('move_up') : false,
			'U_MOVE_DOWN'					=> ($auth['move']) ? $this->faq->get_url('move_down') : false,
			'U_EDIT'						=> ($auth['edit']) ? $this->faq->get_url('edit') : false,
			'U_DELETE'						=> ($auth['delete']) ? $this->faq->get_url('delete') : false,

			'S_ACCESS_TEAMS'				=> $this->access->is_team($data['faq_access']),
			'S_ACCESS_AUTHORS'				=> $this->access->is_author($data['faq_access']),
		));
	}

	/**
	* Assign common template variables.
	*
	* @return null
	*/
	protected function assign_vars()
	{
		$this->contrib->assign_details(true);
		$this->display->assign_global_vars();
		$this->generate_navigation('faq');
		$this->generate_breadcrumbs();
	}
}
