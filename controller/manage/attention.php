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

namespace phpbb\titania\controller\manage;

class attention extends base
{
	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\controller\helper $helper
	 * @param \phpbb\request\request_interace $request
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\subscriptions $subscriptions
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\subscriptions $subscriptions)
	{
		parent::__construct($auth, $config, $db, $template, $user, $cache, $helper, $request, $ext_config, $display);

		$this->subscriptions = $subscriptions;
	}

	/**
	* Display attention item.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_item($id)
	{
		$this->user->add_lang('mcp');
		$this->load_item($id);

		if (!$this->check_auth(true))
		{
			return $this->helper->needs_auth();
		}

		// Display the current attention items
		$options = array(
			'attention_object_id'		=> $this->attention->attention_object_id,
			'exclude_attention_types'	=> TITANIA_ATTENTION_UNAPPROVED,
		);
		\attention_overlord::display_attention_list($options);

		// Display the old (closed) attention items
		$options = array_merge($options, array(
			'only_closed'				=> true,
			'template_block'			=> 'attention_closed',
			'exclude_attention_types'	=> false,
		));
		\attention_overlord::display_attention_list($options);

		$this->attention->assign_source_object_details();
		$this->display->assign_global_vars();
		$this->generate_navigation('attention');

		return $this->helper->render(
			'manage/attention_details.html',
			censor_text($this->attention->get_title()) . ' - ' . $this->user->lang['ATTENTION']
		); 
	}

	/**
	* Delegates the requested action to the appropriate method.
	*
	* @param int $id			Attention item id.
	* @param string $action		Action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function item_action($id, $action)
	{
		if (!in_array($action, array('approve', 'disapprove', 'close', 'delete')))
		{
			return $this->helper->error('INVALID_ACTION', 404);
		}

		$this->user->add_lang('mcp');
		$this->load_item($id);

		if (!$this->check_auth(true))
		{
			return $this->helper->needs_auth();
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'attention_action'))
		{
			redirect($this->attention->get_report_url());
		}

		return $this->{$action}();
	}

	/**
	* Display attention item list.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_list()
	{
		$this->user->add_lang('mcp');

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$type = $this->request->variable('type', '');
		$closed = $this->request->variable('closed', false);
		$open = $this->request->variable('open', false) || !$closed;

		switch ($type)
		{
			case 'reported' :
				$type = TITANIA_ATTENTION_REPORTED;
			break;

			case 'unapproved' :
				$type = TITANIA_ATTENTION_UNAPPROVED;
			break;

			default :
				$type = false;
			break;
		}

		$options = array(
			'attention_type'	=> $type,
			'display_closed'	=> $closed,
			'only_closed'		=> !$open && $closed,
		);
		\attention_overlord::display_attention_list($options);

		$this->template->assign_vars(array(
			'S_ACTION'			=> $this->helper->route('phpbb.titania.manage.attention'),
			'S_OPEN_CHECKED'	=> $open,
			'S_CLOSED_CHECKED'	=> $closed,
		));

		// Subscriptions
		$this->subscriptions->handle_subscriptions(
			TITANIA_ATTENTION,
			0,
			$this->helper->route('phpbb.titania.manage.attention')
		);

		$this->display->assign_global_vars();
		$this->generate_navigation('attention');

		return $this->helper->render('manage/attention.html', $this->user->lang['ATTENTION']);
	}

	/**
	* Redirect to the first item available for the given
	* object type and id.
	*
	* @return null
	*/
	public function redirect_to_item()
	{
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$type = $this->request->variable('type', 0);
		$id = $this->request->variable('id', 0);

		if ($type && $id)
		{
			$this->load_item(false, $type, $id);

			redirect($this->attention->get_report_url());
		}
		redirect($this->helper->route('phpbb.titania.manage.attention'));
	}

	/**
	* Approve action.
	*
	* @return null
	*/
	protected function approve()
	{
		$this->attention->approve();
		redirect($this->attention->get_report_url());
	}

	/**
	* Disapprove action.
	*
	* @return null
	*/
	protected function disapprove()
	{
		if (!$this->attention->is_open() || $this->attention->is_report())
		{
			redirect($this->helper->route('phpbb.titania.manage.attention'));
		}

		$disapprove_reason = $this->request->variable('disapprove_reason', 0);
		$disapprove_explain = $this->request->variable('disapprove_explain', '', true);
		$result = false;

		if (confirm_box(true))
		{
			$result = $this->attention->disapprove($disapprove_reason, $disapprove_explain);
		}

		if (!$result || $result === 'reason_empty')
		{
			if ($result)
			{
				$this->template->assign_var('ADDITIONAL_MSG', $this->user->lang['NO_REASON_DISAPPROVAL']);

				// Make sure we can reuse the confirm box
				$this->request->overwrite('confirm_key', null, \phpbb\request\request_interface::REQUEST);
				$this->request->overwrite('confirm_key', null, \phpbb\request\request_interface::POST);
				$this->request->overwrite('confirm', null, \phpbb\request\request_interface::POST);
			}

			\phpbb::_include('functions_display', 'display_reasons');
			display_reasons($disapprove_reason);

			confirm_box(false, 'DISAPPROVE_ITEM', build_hidden_fields(array('disapprove' => true)), 'manage/disapprove_body.html');
		}

		redirect($this->helper->route('phpbb.titania.manage.attention'));
	}

	/**
	* Close action.
	*
	* @return null
	*/
	protected function close()
	{
		$this->attention->report_handled();

		redirect($this->attention->get_report_url());
	}

	/**
	* Delete action.
	*
	* @return null
	*/
	protected function delete()
	{
		$this->attention->report_handled();
		$this->attention->delete();

		redirect($this->helper->route('phpbb.titania.manage.attention'));
	}

	/**
	* Load attention item.
	*
	* @param int $attention_id		Attention id.
	* @param int $object_type		Object type.
	* @param int $object_id			Object id.
	* @throws \Exception			Throws exception if no item found.
	*
	* @return null
	*/
	protected function load_item($attention_id, $object_type = false, $object_id = false)
	{
		$this->attention = \attention_overlord::get_attention_object($attention_id, $object_type, $object_id);

		if (!$this->attention)
		{
			throw new \Exception($this->user->lang['NO_ATTENTION_ITEM']);
		}

		if (!$this->attention->load_source_object())
		{
			$this->attention->delete();

			$error = array(
				TITANIA_POST	=> 'NO_POST',
				TITANIA_CONTRIB	=> 'NO_CONTRIB',
			);

			throw new \Exception($error[$this->attention->attention_object_type]);
		}
	}

	/**
	* Check user's authorization.
	*
	* @param bool $item		Check auth for attention item.
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth($item = false)
	{
		if ($item)
		{
			return $this->attention->check_auth();
		}

		return $this->auth->acl_gets(
			'u_titania_mod_contrib_mod',
			'u_titania_mod_post_mod'
		) ||
		\titania_types::find_authed('moderate');
	}
}
