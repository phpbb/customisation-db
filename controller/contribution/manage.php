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

class manage extends base
{
	/** @var array */
	protected $settings;

	/** @var \titania_message */
	protected $message;

	/** @var \titania_attachment */
	protected $screenshot;

	/** @var \titania_attachment */
	protected $colorizeit_sample;

	/** @var bool */
	protected $is_moderator;

	/** @var bool */
	protected $can_edit_demo;

	/** @var bool */
	protected $use_colorizeit;

	/** @var array */
	protected $status_list;

	/**
	* Manage contribution.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function manage($contrib_type, $contrib)
	{
		$this->setup($contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		if (confirm_box(true) && $this->check_auth('change_author'))
		{
			$this->change_author();
		}

		$old_settings = $this->contrib->__get_array();
		$old_settings['categories'] = array_keys($this->contrib->category_data);

		$this->settings = array(
			'permalink'			=> $this->request->variable('permalink', $this->contrib->contrib_name_clean, true),
			'status'			=> $this->request->variable('contrib_status', (int) $this->contrib->contrib_status),
			'categories'		=> $this->request->variable('contrib_category', array(0)),
			'demo'				=> $this->request->variable('demo_url', '', true),
			'coauthors'			=> array(
				'active'		=> $this->request->variable('active_coauthors', '', true),
				'nonactive'		=> $this->request->variable('nonactive_coauthors', '', true),
			),
			'new_author'		=> $this->request->variable('change_owner', '', true),
			'limited_support'	=> $this->request->variable('limited_support', false),
			'custom'			=> $this->request->variable('custom', array('' => ''), true),
		);
		$this->load_message();
		$this->load_screenshot();

		$submit	= $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');
		$error = $this->screenshot->error;

		if ($preview || $submit || $this->screenshot->uploaded)
		{
			$this->contrib->post_data($this->message);
			$this->contrib->__set_array(array(
				'contrib_demo'				=> ($this->can_edit_demo) ? $this->settings['demo'] : $this->contrib->contrib_demo,
				'contrib_limited_support'	=> $this->settings['limited_support'], 
			));
		}

		// ColorizeIt sample
		if ($this->use_colorizeit)
		{
			$this->load_colorizeit();
			$error = array_merge($error, $this->colorizeit_sample->error);

			if ($this->colorizeit_sample->uploaded)
			{
				$this->contrib->post_data($this->message);
			}
		}

		$this->handle_screenshot_action();

		if ($preview)
		{
			$this->preview();
		}
		else if ($submit)
		{
			if (($form_key_error = $this->message->validate_form_key()) !== false)
			{
				$error[] = $form_key_error;
			}
			else if ($this->request->is_set_post('delete') && $this->check_auth('delete'))
			{
				// Handle the deletion routine
				$this->contrib->delete();
				redirect($this->helper->route('phpbb.titania.index'));
			}

			$this->contrib->post_data($this->message);

			$authors = $this->contrib->get_authors_from_usernames(array(
				'active_coauthors'		=> $this->settings['coauthors']['active'],
				'nonactive_coauthors'	=> $this->settings['coauthors']['nonactive'],
				'new_author'			=> $this->settings['new_author'],
			));
			$author_username = \users_overlord::get_user($this->contrib->contrib_user_id, 'username', true);
			$authors['author'] = array($author_username => $this->contrib->contrib_user_id);

			$error = array_merge($error, $this->contrib->validate(
				$this->settings['categories'],
				$authors,
				$this->contrib->contrib_name_clean
			));

			// Did we succeed or have an error?
			if (empty($error))
			{
				$this->submit($authors, $old_settings);
			}
		}

		$this->assign_vars($error);

		return $this->helper->render(
			'contributions/contribution_manage.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['MANAGE_CONTRIBUTION']
		);
	}

	/**
	* Trigger author change confirmation.
	*
	* @param string $username		New author's username.
	* @param int $user_id			New author's user id.
	*
	* @return null
	*/
	protected function confirm_author_change($username, $user_id)
	{
		$s_hidden_fields = array(
			'submit'			=> true,
			'change_owner_id'	=> $user_id,
		);
		$author_profile = '<a href="' .  \phpbb::append_sid('memberlist', 'mode=viewprofile&amp;u=' . $user_id) . '">' . $username . '</a>';

		confirm_box(
			false,
			$this->user->lang('CONTRIB_CONFIRM_OWNER_CHANGE', $author_profile),
			build_hidden_fields($s_hidden_fields)
		);
	}

	/**
	* Change contribution author.
	*
	* @return null
	*/
	protected function change_author()
	{
		$new_author = $this->request->variable('change_owner_id', 0);

		if ($new_author && $new_author !== ANONYMOUS)
		{
			$this->contrib->set_contrib_user_id($new_author);

			$this->contrib->load($this->contrib->contrib_id); // Reload the contrib (to make sure the authors list is updated)
			// Update the release topic and reindex the contrib
			$this->contrib->update_release_topic();
			$this->contrib->index();
			$this->cache->destroy('sql', TITANIA_CONTRIBS_TABLE);

			redirect($this->contrib->get_url());
		}
	}

	/**
	* Handle screenshot delete/move action.
	*
	* @return null
	*/
	protected function handle_screenshot_action()
	{
		// Screenshots
		$attach_id	= $this->request->variable('a', 0);
		$action		= $this->request->variable('action', '');
		$link_hash	= $this->request->variable('hash', '');

		if ($attach_id && $action && check_link_hash($link_hash, 'attach_manage'))
		{
			if ($action == 'delete_attach')
			{
				// The delete() method will check if the attachment is part of the screenshot/clr_sample array
				$this->screenshot->delete($attach_id);
				if (isset($this->colorizeit_sample))
				{
					$this->colorizeit_sample->delete($attach_id);
				}
			}
			else if ($action == 'attach_up' || $action == 'attach_down')
			{
				$move_attach = ($action == 'attach_up') ? 'up' : 'down';
				$original_order = $this->screenshot->generate_order();
				$this->screenshot->generate_order(false, $attach_id, $move_attach);
				$this->screenshot->submit(TITANIA_ACCESS_PUBLIC, $original_order);
			}
		}
	}

	/**
	* Common setup tasks.
	*
	* @param string $contrib		Contrib name clean.
	* @return null
	*/
	protected function setup($contrib)
	{
		$this->load_contrib($contrib);
		$this->is_moderator = $this->contrib->type->acl_get('moderate');
		$this->use_colorizeit = strlen($this->ext_config->colorizeit) && $this->contrib->type->acl_get('colorizeit');
		$this->can_edit_demo = $this->ext_config->can_modify_style_demo_url || \titania_types::$types[TITANIA_TYPE_STYLE]->acl_get('moderate')
				|| $this->contrib->contrib_type != TITANIA_TYPE_STYLE;

		$this->status_list = array(
			TITANIA_CONTRIB_NEW					=> 'CONTRIB_NEW',
			TITANIA_CONTRIB_APPROVED			=> 'CONTRIB_APPROVED',
			TITANIA_CONTRIB_DOWNLOAD_DISABLED	=> 'CONTRIB_DOWNLOAD_DISABLED',
			TITANIA_CONTRIB_CLEANED				=> 'CONTRIB_CLEANED',
			TITANIA_CONTRIB_HIDDEN				=> 'CONTRIB_HIDDEN',
			TITANIA_CONTRIB_DISABLED			=> 'CONTRIB_DISABLED',
		);
	}

	/**
	* Check user's authorization.
	*
	* @param bool|string $action	Check auth for specific action. Defaults to false.
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth($action = false)
	{
		$action_auth = true;

		if ($action !== false)
		{
			switch ($action)
			{
				case 'change_author':
					$action_auth = $this->is_moderator || $this->user->data['user_id'] == $this->contrib->contrib_user_id;
				break;

				case 'delete':
					$action_auth = $this->auth->acl_get('u_titania_admin');
				break;

				default:
					$action_auth = false;
			}	
		}
		$is_author_editable = !in_array($this->contrib->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED));

		return $action_auth && ($this->is_moderator || 
			($this->is_author && $is_author_editable && $this->auth->acl_get('u_titania_post_edit_own')));
	}

	/**
	* Load screenshot handler.
	*
	* @return null
	*/
	protected function load_screenshot()
	{
		$this->screenshot = new \titania_attachment(TITANIA_SCREENSHOT, $this->contrib->contrib_id);
		$this->screenshot->load_attachments(false, false ,true);
		$this->screenshot->upload(175, true);
	}

	/**
	* Load ColorizeIt handler.
	*
	* @return null
	*/
	protected function load_colorizeit()
	{
		$this->colorizeit_sample = new \titania_attachment(TITANIA_CLR_SCREENSHOT, $this->contrib->contrib_id);
		$this->colorizeit_sample->load_attachments();
		$this->colorizeit_sample->upload();
	}

	/**
	* Load message object.
	*
	* @return null
	*/
	protected function load_message()
	{
		$this->message = new \titania_message($this->contrib);
		$this->message->set_auth(array(
			'bbcode'		=> $this->auth->acl_get('u_titania_bbcode'),
			'smilies'		=> $this->auth->acl_get('u_titania_smilies'),
			'edit_subject'	=> $this->is_moderator,
		));
		$this->message->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
			'subject_name'		=> 'name',
		));
	}

	/**
	* Handle contribution settings preview.
	*
	* @return null
	*/
	protected function preview()
	{
		$this->message->preview();
		$attach_order = $this->request->variable('attach_order', array(0));

		// Preserve the attachment order when preview is hit just in case it has been modified
		if (!empty($attach_order))
		{
			$this->screenshot->generate_order(array_flip($attach_order));
		}
	}

	/**
	* Submit contribution.
	*
	* @param array $authors
	* @param array $old_settings
	*
	* @return null
	*/
	protected function submit($authors, $old_settings)
	{
		$this->submit_screenshots();
		$this->submit_colorizeit();

		// Update contrib_status/permalink if we can moderate. only if contrib_status is valid and permalink altered
		if ($this->is_moderator)
		{
			if (array_key_exists($this->settings['status'], $this->status_list))
			{
				$this->contrib->change_status($this->settings['status']);
			}

			if ($this->settings['permalink'] != $this->contrib->contrib_name_clean)
			{
				$this->contrib->change_permalink($this->settings['permalink']);
			}
		}
		else
		{
			// Check for changes in the description or categories to file a report
			$this->create_change_report($old_settings);
		}

		// Create relations
		$this->contrib->put_contrib_in_categories($this->settings['categories']);
		// Submit the changes
		$this->contrib->submit();
		// Set the coauthors
		$this->contrib->set_coauthors($authors['active_coauthors'], $authors['nonactive_coauthors'], true);
		// Update the release topic
		$this->contrib->update_release_topic();

		$new_author = $authors['new_author'];

		if ($new_author)
		{
			$this->confirm_author_change(key($new_author), array_shift($new_author));
		}

		redirect($this->contrib->get_url());
	}

	/**
	* Submit screenshots.
	*
	* @return null
	*/
	protected function submit_screenshots()
	{
		$original_order = $this->screenshot->generate_order();
		$new_order = $this->request->variable('attach_order', array(0));
		$this->screenshot->generate_order(array_flip($new_order));

		// Submit screenshots
		$this->screenshot->submit(TITANIA_ACCESS_PUBLIC, $original_order);
	}

	/**
	* Submit ColorizeIt settings.
	*
	* @return null
	*/
	protected function submit_colorizeit()
	{
		// ColorizeIt stuff
		if ($this->use_colorizeit)
		{
			$this->colorizeit_sample->submit();
			$contrib_clr_colors = $this->request->variable('change_colors', $this->contrib->contrib_clr_colors);
			$this->contrib->__set('contrib_clr_colors', $contrib_clr_colors);
		}
	}

	/**
	* Assign template variables.
	*
	* @param array $error		Array containing any errors found.
	* @return null
	*/
	protected function assign_vars($error)
	{
		\titania::_include('functions_posting', 'generate_type_select');

		// ColorizeIt
		if ($this->use_colorizeit)
		{
			$this->assign_colorizeit_vars();
		}

		foreach ($this->status_list as $status => $lang)
		{
			$this->template->assign_block_vars('status_select', array(
				'NAME'				=> $this->user->lang($lang),
				'VALUE'				=> $status,
				'S_SELECTED'		=> $status == $this->contrib->contrib_status,
			));
		}

		$coauthors = $this->get_coauthor_usernames();

		$this->template->assign_vars(array(
			'S_CONTRIB_APPROVED'		=> $this->contrib->contrib_status == TITANIA_CONTRIB_APPROVED,
			'S_POST_ACTION'				=> $this->contrib->get_url('manage'),
			'S_EDIT_SUBJECT'			=> $this->is_moderator,
			'S_DELETE_CONTRIBUTION'		=> $this->check_auth('delete'),
			'S_IS_OWNER'				=> $this->contrib->is_author,
			'S_IS_MODERATOR'			=> $this->is_moderator,
			'S_CAN_EDIT_STYLE_DEMO'		=> $this->can_edit_demo,
			'S_CAN_EDIT_CONTRIB'		=> $this->auth->acl_get('u_titania_contrib_submit'),
			'S_LIMITED_SUPPORT'			=> $this->settings['limited_support'],

			'CONTRIB_PERMALINK'			=> $this->settings['permalink'],
			'CONTRIB_TYPE'				=> $this->contrib->contrib_type,
			'SCREENSHOT_UPLOADER'		=> ($this->auth->acl_get('u_titania_contrib_submit')) ? $this->screenshot->parse_uploader('posting/attachments/simple.html', 'titania_attach_order_compare') : false,
			'ERROR_MSG'					=> (!empty($error)) ? implode('<br />', $error) : false,
			'ACTIVE_COAUTHORS'			=> implode("\n", $coauthors['active']),
			'NONACTIVE_COAUTHORS'		=> implode("\n", $coauthors['nonactive']),
		));

		generate_category_select(array_keys($this->contrib->category_data));
		$this->message->display();
		$this->contrib->assign_details();
		$this->display->assign_global_vars();
		$this->generate_navigation('manage');
		$this->generate_breadcrumbs();
	}

	/**
	* Assign ColorizeIt template variables.
	*
	* @return null
	*/
	protected function assign_colorizeit_vars()
	{
		$clr_testsample = $test_sample = '';

		if ($this->contrib->has_colorizeit(true) || is_array($this->contrib->clr_sample))
		{
			$sample_url = $this->colorizeit_sample->get_url($this->contrib->clr_sample['attachment_id']);
			$test_sample = 'http://' . $this->ext_config->colorizeit_url .
				'/testsample.html?sub=' . $this->ext_config->colorizeit . '&amp;sample=' . urlencode($sample_url);
		}
		$this->template->assign_vars(array(
			'MANAGE_COLORIZEIT'         => $this->ext_config->colorizeit,
			'CLR_SCREENSHOTS'           => $this->colorizeit_sample->parse_uploader('posting/attachments/simple.html'),
			'CLR_COLORS'                => htmlspecialchars($this->contrib->contrib_clr_colors),
			'U_TESTSAMPLE'              => $test_sample,
		));
	}

	/**
	* Get coauthor usernames.
	*
	* @return array
	*/
	protected function get_coauthor_usernames()
	{
		$coauthors = array(
			'active'	=> array(),
			'nonactive'	=> array(),
		);

		foreach ($this->contrib->coauthors as $data)
		{
			$user = \users_overlord::get_user($data['user_id']);

			// Make sure user still exists.
			if ($user['user_id'] == $data['user_id'])
			{
				$status = ($data['active']) ? 'active' : 'nonactive';
				$coauthors[$status][] = $user['username'];
			}
		}

		return $coauthors;
	}

	/**
	* Create attention reports for changes made.
	*
	* @return null
	*/
	protected function create_change_report($old_settings)
	{
		$description_change = $this->get_description_change($old_settings);
		$category_change = $this->get_category_change($old_settings['categories']);

		if ($description_change)
		{
			$this->contrib->report($description_change, false, TITANIA_ATTENTION_DESC_CHANGED);
		}

		if ($category_change)
		{
			$this->contrib->report($category_change, false, TITANIA_ATTENTION_CATS_CHANGED);
		}
	}

	/**
	* Generate report message for a change in description.
	*
	* @return string
	*/
	protected function get_description_change($old_settings)
	{
		$contrib = new \titania_contrib;
		$contrib->__set_array($old_settings);
		$old_description = $contrib->generate_text_for_edit();
		$old_description = $old_description['text'];

		$description = $this->contrib->generate_text_for_edit();
		$description = $description['text'];

		return ($old_description !== $description) ? "$old_description>>>>>>>>>>$description" : '';
	}

	/**
	* Generate report message for a change in categories.
	*
	* @param array $old_categories		Array containing old category id's.
	*
	* @return string
	*/
	protected function get_category_change($old_categories)
	{
		$new_categories = $this->settings['categories'];

		sort($old_categories);
		sort($new_categories);

		if ($old_categories === $new_categories)
		{
			return '';
		}

		$categories = $this->cache->get_categories();
		$old_names = $new_names = array();

		foreach ($old_categories as $id)
		{
			$old_names[] = $this->user->lang($categories[$id]['category_name']);
		}
		foreach ($new_categories as $id)
		{
			$new_names[] = $this->user->lang($categories[$id]['category_name']);
		}

		return implode("\n", $old_names) . '>>>>>>>>>>' . implode("\n", $new_names);
	}
}
