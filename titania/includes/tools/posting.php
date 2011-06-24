<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

class titania_posting
{
	public function act($template_body, $parent_id = false, $parent_url = false, $post_type = false, $s_post_action = false)
	{
		$action = request_var('action', '');

		switch ($action)
		{
			case 'post' :
				if ($parent_id === false || $parent_url == false || $post_type === false)
				{
					throw new exception('Must send parent_id, parent_url, and new post type to allow posting new topics');
				}

				$this->post($parent_id, $parent_url, $post_type, (($s_post_action === false) ? titania_url::$current_page_url : $s_post_action));

				titania::page_footer(true, $template_body);
			break;

			case 'quote' :
				$this->reply(request_var('t', 0), request_var('p', 0));

				titania::page_footer(true, $template_body);
			break;

			case 'reply' :
				$this->reply(request_var('t', 0));

				titania::page_footer(true, $template_body);
			break;

			case 'edit' :
				$this->edit(request_var('p', 0));

				titania::page_footer(true, $template_body);
			break;

			case 'quick_edit' :
				$this->quick_edit(request_var('p', 0));

				titania::page_footer(true, $template_body);
			break;

			case 'delete' :
			case 'undelete' :
			case 'report' :
				$this->$action(request_var('p', 0));
			break;

			case 'sticky_topic' :
			case 'unsticky_topic' :
				$this->toggle_sticky(request_var('t', 0));
			break;

			case 'lock_topic' :
			case 'unlock_topic' :
			case 'delete_topic' :
			case 'undelete_topic' :
				$this->$action(request_var('t', 0));
			break;

			case 'hard_delete_topic' :
				$this->delete_topic(request_var('t', 0), true);
			break;
		}
	}

	/**
	* Post a new topic
	*
	* @param $parent_id The parent_id
	* @param $parent_url The url of the parent
	* @param int $post_type Post Type
	* @param string $s_post_action URL to the current page to submit to
	*/
	public function post($parent_id, $parent_url, $post_type, $s_post_action)
	{
		if (!phpbb::$auth->acl_get('u_titania_topic'))
		{
			titania::needs_auth();
		}

		// Setup the post object we'll use
		$post_object = new titania_post($post_type);
		$post_object->topic->parent_id = $parent_id;
		$post_object->topic->topic_url = titania_url::unbuild_url($parent_url);

		// Some more complicated permissions for stickes in support
		$can_sticky = phpbb::$auth->acl_get('u_titania_mod_post_mod');
		if ($post_type == TITANIA_SUPPORT)
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$can_sticky = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $parent_id);
				if (titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
				{
					$can_sticky = true;
				}
			}
		}
		else if ($post_type == TITANIA_QUEUE_DISCUSSION)
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$post_object->topic->topic_category = titania::$contrib->contrib_type;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $parent_id);
				if (titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
				{
					$post_object->topic->topic_category = titania::$contrib->contrib_type;
				}
			}
		}

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'sticky_topic'	=> $can_sticky,
			'lock_topic'	=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && is_object(titania::$contrib) && titania::$contrib->contrib_id == $parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));
		$message_object->set_settings(array(
			'display_captcha'			=> (!phpbb::$user->data['is_registered']) ? true : false,
		));

		// Call our common posting handler
		$this->common_post('post', $post_object, $message_object);

		// Common stuff
		phpbb::$template->assign_vars(array(
			'S_POST_ACTION'		=> $s_post_action,
			'L_POST_A'			=> phpbb::$user->lang['POST_TOPIC'],
		));
		titania::page_header('NEW_TOPIC');
	}

	/**
	* Reply to an existing topic
	*
	* @param mixed $topic_id
	*/
	public function reply($topic_id, $quote_post_id = false)
	{
		if (!phpbb::$auth->acl_get('u_titania_post'))
		{
			titania::needs_auth();
		}

		// Load the stuff we need
		$topic = $this->load_topic($topic_id);

		$post_object = new titania_post($topic->topic_type, $topic);

		// Check permissions
		if (!$post_object->acl_get('reply'))
		{
			titania::needs_auth();
		}

		// Quoting?
		if ($quote_post_id !== false && $post_object->post_text == '')
		{
			$quote = $this->load_post($quote_post_id);

			// Permission check
			if (titania::$access_level <= min($quote->post_access, $quote->topic->topic_access) && (phpbb::$auth->acl_get('u_titania_mod_post_mod') || ($quote->post_approved && (!$quote->post_deleted || $quote->post_deleted == phpbb::$user->data['user_id']))))
			{
				$for_edit = $quote->generate_text_for_edit();

				$post_object->post_text = '[quote="' . users_overlord::get_user($quote->post_user_id, '_username', true) . '"]' . $for_edit['text'] . '[/quote]';
			}
		}

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'lock_topic'	=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && is_object(titania::$contrib) && titania::$contrib->contrib_id == $post_object->topic->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));
		$message_object->set_settings(array(
			'display_captcha'			=> (!phpbb::$user->data['is_registered']) ? true : false,
			'subject_default_override'	=> 'Re: ' . $post_object->topic->topic_subject,
		));

		// Call our common posting handler
		$this->common_post('reply', $post_object, $message_object);


		// Setup the sort tool
		$topic_sort = posts_overlord::build_sort();
		$topic_sort->set_defaults(false, false, 'd');

		// Display the posts for review
		posts_overlord::display_topic($post_object->topic, $topic_sort);

		// Common stuff
		phpbb::$template->assign_vars(array(
			'S_POST_ACTION'		=> $post_object->topic->get_url('reply', titania_url::$current_page_url),
			'L_POST_A'			=> phpbb::$user->lang['POST_REPLY'],

			'S_DISPLAY_REVIEW'	=> true,
		));
		titania::page_header('POST_REPLY');
	}

	/**
	* Quick Edit a post
	*
	* @param mixed $post_id
	*/
	public function quick_edit($post_id)
	{
		$submit = isset($_POST['submit']) ? true : false;
		$full_editor = isset($_POST['full_editor']) ? true : false;

		// AJAX output
		if (!$submit && !$full_editor)
		{
			phpbb::$user->add_lang('viewtopic');

			// Load the stuff we need
			$post_object = $this->load_post($post_id);

			// Check permissions
			if (!$post_object->acl_get('edit'))
			{
				echo phpbb::$user->lang['NO_AUTH'];

				garbage_collection();
				exit_handler();
			}

			$post_message = $post_object->post_text;
			titania_decode_message($post_message, $post_object->post_text_uid);

			add_form_key('postform');

			phpbb::$template->assign_vars(array(
				'MESSAGE'		=> $post_message,

				'U_QR_ACTION'	=> $post_object->get_url('quick_edit'),
			));

			phpbb::$template->set_filenames(array(
				'quick_edit'	=> 'posting/quickedit_editor.html'
			));

			// application/xhtml+xml not used because of IE
			header('Content-type: text/html; charset=UTF-8');

			header('Cache-Control: private, no-cache="set-cookie"');
			header('Expires: 0');
			header('Pragma: no-cache');

			phpbb::$template->display('quick_edit');

			garbage_collection();
			exit_handler();
		}

		if ($full_editor || !check_form_key('postform'))
		{
			$this->edit($post_id);

			return;
		}

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		// Check permissions
		if (!$post_object->acl_get('edit'))
		{
			titania::needs_auth();
		}

		// Grab some data
		$for_edit = $post_object->generate_text_for_edit();

		// Set the post text
		$post_object->post_text = utf8_normalize_nfc(request_var('message', '', true));

		// Generate for storage based on previous options
		$post_object->generate_text_for_storage($for_edit['allow_bbcode'], $for_edit['allow_urls'], $for_edit['allow_smilies']);

		// Submit
		$post_object->submit();

		// Load attachments
		$attachments = new titania_attachment($post_object->post_type, $post_object->post_id);
		$attachments->load_attachments();

		// Parse the mesage
		$message = $post_object->generate_text_for_display();
		$parsed_attachments = $attachments->parse_attachments($message);

		// echo the message (returned to the JS to display in the place of the old message)
		echo $message;

		garbage_collection();
		exit_handler();
	}

	/**
	* Edit an existing post
	*
	* @param int $post_id
	*/
	public function edit($post_id)
	{
		if (!phpbb::$auth->acl_get('u_titania_post'))
		{
			titania::needs_auth();
		}

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		// Check permissions
		if (!$post_object->acl_get('edit'))
		{
			titania::needs_auth();
		}

		// Some more complicated permissions for stickes in support
		$can_sticky = $can_lock = phpbb::$auth->acl_get('u_titania_mod_post_mod');
		if ($post_object->post_type == TITANIA_SUPPORT)
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $post_object->topic->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$can_sticky = $can_lock = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $post_object->topic->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $post_object->topic->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$can_sticky = $can_lock = true;
				}
			}
		}

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'lock'			=> ($post_object->post_user_id != phpbb::$user->data['user_id'] && phpbb::$auth->acl_get('u_titania_mod_post_mod')) ? true : false,
			'sticky_topic'	=> ($post_object->post_id == $post_object->topic->topic_first_post_id && $can_sticky) ? true : false,
			'lock_topic'	=> ($can_lock || (phpbb::$auth->acl_get('u_titania_post_mod_own') && (phpbb::$auth->acl_get('u_titania_post_mod_own') && is_object(titania::$contrib) && titania::$contrib->contrib_id == $post_object->topic->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor))) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));

		// Call our common posting handler
		$this->common_post('edit', $post_object, $message_object);

		// Common stuff
		phpbb::$template->assign_vars(array(
			'S_POST_ACTION'		=> $post_object->get_url('edit', false, titania_url::$current_page_url),
			'L_POST_A'			=> phpbb::$user->lang['EDIT_POST'],
		));
		titania::page_header('EDIT_POST');
	}

	/**
	* Delete a post
	*
	* @param int $post_id
	*/
	public function delete($post_id)
	{
		$this->common_delete($post_id);
	}

	/**
	* Undelete a soft deleted post
	*
	* @param int $post_id
	*/
	public function undelete($post_id)
	{
		$this->common_delete($post_id, true);
	}

	/**
	* Report a post
	*
	* @param int $post_id
	*/
	public function report($post_id)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		// Check permissions
		if (!phpbb::$user->data['is_registered'])
		{
			titania::needs_auth();
		}

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		if (titania::confirm_box(true))
		{
			$message = utf8_normalize_nfc(request_var('report_text', '', true));
			$post_object->report($message);

			// Notifications

			redirect($post_object->get_url());
		}
		else
		{
			//phpbb::$template->assign_var('S_CAN_NOTIFY', ((phpbb::$user->data['is_registered']) ? true : false));

			titania::confirm_box(false, 'REPORT_POST', '', array(), 'posting/report_body.html');
		}

		redirect($post_object->get_url());
	}

	/**
	* Sticky a topic
	*
	* @param int $topic_id
	*/
	public function toggle_sticky($topic_id)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		// Load the stuff we need
		$topic_object = $this->load_topic($topic_id);

		// Auth check
		$is_authed = false;
		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$is_authed = true;
		}
		else if (phpbb::$auth->acl_get('u_titania_post_mod_own'))
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $topic_object->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $topic_object->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $topic_object->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$is_authed = true;
				}
			}
		}

		// Check permissions
		if (!$is_authed)
		{
			titania::needs_auth();
		}

		$topic_object->topic_sticky = ($topic_object->topic_sticky) ? false : true;
		$topic_object->submit();

		redirect($topic_object->get_url());
	}

	/**
	* Lock a topic
	*
	* @param int $topic_id
	*/
	public function lock_topic($topic_id)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		// Load the stuff we need
		$topic_object = $this->load_topic($topic_id);

		// Auth check
		$is_authed = false;
		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$is_authed = true;
		}
		else if (phpbb::$auth->acl_get('u_titania_post_mod_own'))
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $topic_object->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $topic_object->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $topic_object->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$is_authed = true;
				}
			}
		}

		// Check permissions
		if (!$is_authed)
		{
			titania::needs_auth();
		}

		if (titania::confirm_box(true))
		{
			$topic_object->topic_locked = true;
			$topic_object->submit();

			redirect($topic_object->get_url());
		}
		else
		{
			titania::confirm_box(false, 'LOCK_TOPIC');
		}

		redirect($topic_object->get_url());
	}

	/**
	* Unlock a topic
	*
	* @param int $topic_id
	*/
	public function unlock_topic($topic_id)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		// Load the stuff we need
		$topic_object = $this->load_topic($topic_id);

		// Auth check
		$is_authed = false;
		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$is_authed = true;
		}
		else if (phpbb::$auth->acl_get('u_titania_post_mod_own'))
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $topic_object->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $topic_object->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $topic_object->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$is_authed = true;
				}
			}
		}

		// Check permissions
		if (!$is_authed)
		{
			titania::needs_auth();
		}

		if (titania::confirm_box(true))
		{
			$topic_object->topic_locked = false;
			$topic_object->submit();

			redirect($topic_object->get_url());
		}
		else
		{
			titania::confirm_box(false, 'UNLOCK_TOPIC');
		}

		redirect($topic_object->get_url());
	}

	/**
	* Delete a topic
	*
	* @param int $topic_id
	* @param bool $hard_delete Hard delete or just soft delete?
	*/
	public function delete_topic($topic_id, $hard_delete = false)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		// Load the stuff we need
		$topic_object = $this->load_topic($topic_id);

		// Auth check
		$is_authed = false;
		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$is_authed = true;
		}
		else if (!$hard_delete && phpbb::$auth->acl_get('u_titania_post_mod_own'))
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $topic_object->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $topic_object->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $topic_object->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$is_authed = true;
				}
			}
		}

		// Check permissions
		if (!$is_authed)
		{
			titania::needs_auth();
		}

		if (titania::confirm_box(true))
		{
			if ($hard_delete)
			{
				$base = $append = false;
				titania_url::split_base_params($base, $append, $topic_object->topic_url);

				$topic_object->delete();

				redirect(titania_url::build_url($base, $append));
			}
			else
			{
				$topic_object->soft_delete();

				redirect($topic_object->get_url());
			}
		}
		else
		{
			if ($hard_delete)
			{
				titania::confirm_box(false, 'HARD_DELETE_TOPIC');
			}
			else
			{
				titania::confirm_box(false, 'SOFT_DELETE_TOPIC');
			}
		}

		redirect($topic_object->get_url());
	}

	/**
	* Undelete a topic
	*
	* @param int $topic_id
	*/
	public function undelete_topic($topic_id)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		// Load the stuff we need
		$topic_object = $this->load_topic($topic_id);

		// Auth check
		$is_authed = false;
		if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			$is_authed = true;
		}
		else if (phpbb::$auth->acl_get('u_titania_post_mod_own'))
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $topic_object->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $topic_object->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $topic_object->parent_id);
				if ($contrib->is_author || $contrib->is_active_coauthor)
				{
					$is_authed = true;
				}
			}
		}

		// Check permissions
		if (!$is_authed)
		{
			titania::needs_auth();
		}

		if (titania::confirm_box(true))
		{
			$topic_object->undelete();

			redirect($topic_object->get_url());
		}
		else
		{
			titania::confirm_box(false, 'UNDELETE_TOPIC');
		}

		redirect($topic_object->get_url());
	}

	/**
	* Common posting stuff for post/reply/edit
	*
	* @param mixed $post_object
	* @param mixed $message_object
	*/
	private function common_post($mode, $post_object, $message_object)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('posting');

		// Submit check...handles running $post->post_data() if required
		$submit = $message_object->submit_check();

		if ($submit)
		{
			$error = $post_object->validate();

			if (($validate_form_key = $message_object->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}

			// @todo use permissions for captcha
			if (!phpbb::$user->data['is_registered'] && ($validate_captcha = $message_object->validate_captcha()) !== false)
			{
				$error[] = $validate_captcha;
			}

			$error = array_merge($error, $message_object->error);

			if (sizeof($error))
			{
				phpbb::$template->assign_var('ERROR', implode('<br />', $error));
			}
			else
			{
				// Force Queue Discussion topics to always be stickies
				if ($post_object->post_type == TITANIA_QUEUE_DISCUSSION)
				{
					$post_object->topic->topic_sticky = true;
				}

				// Does the post need approval?  Never for the Queue Discussion or Queue
				if (!phpbb::$auth->acl_get('u_titania_post_approved') && $post_object->post_type != TITANIA_QUEUE_DISCUSSION && $post_object->post_type != TITANIA_QUEUE)
				{
					$post_object->post_approved = false;
				}

				$post_object->submit();

				$message_object->submit($post_object->post_access);

				// Did they want to subscribe?
				if (isset($_POST['notify']) && phpbb::$user->data['is_registered'])
				{
					titania_subscriptions::subscribe(TITANIA_TOPIC, $post_object->topic->topic_id);
				}

				// Unapproved posts will get a notice
				if (!$post_object->topic->get_postcount())
				{
					phpbb::$user->add_lang('posting');

					trigger_error(phpbb::$user->lang['POST_STORED_MOD'] . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . $post_object->topic->get_parent_url() . '">', '</a>'));
				}
				else if (!$post_object->post_approved)
				{
					phpbb::$user->add_lang('posting');
					trigger_error(phpbb::$user->lang['POST_STORED_MOD'] . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_TOPIC'], '<a href="' . $post_object->topic->get_url() . '">', '</a>'));
				}
				else
				{
					// Subscriptions
					if ($mode == 'reply')
					{
						if ($post_object->post_type == TITANIA_SUPPORT && is_object(titania::$contrib) && titania::$contrib->contrib_id == $post_object->topic->parent_id && titania::$contrib->contrib_name)
						{
							// Support topic reply
							$email_vars = array(
								'NAME'			=> htmlspecialchars_decode($post_object->topic->topic_subject),
								'U_VIEW'		=> titania_url::append_url($post_object->topic->get_url(), array('view' => 'unread', '#' => 'unread')),
								'CONTRIB_NAME'	=> titania::$contrib->contrib_name,
							);
							titania_subscriptions::send_notifications(array(TITANIA_TOPIC, TITANIA_SUPPORT), array($post_object->topic_id, $post_object->topic->parent_id), 'subscribe_notify_contrib.txt', $email_vars, $post_object->post_user_id);
						}
						else
						{
							$email_vars = array(
								'NAME'		=> htmlspecialchars_decode($post_object->topic->topic_subject),
								'U_VIEW'	=> titania_url::append_url($post_object->topic->get_url(), array('view' => 'unread', '#' => 'unread')),
							);
							titania_subscriptions::send_notifications(TITANIA_TOPIC, $post_object->topic_id, 'subscribe_notify.txt', $email_vars, $post_object->post_user_id);
						}
					}
					else if ($mode == 'post')
					{
						if ($post_object->post_type == TITANIA_SUPPORT && is_object(titania::$contrib) && titania::$contrib->contrib_id == $post_object->topic->parent_id && titania::$contrib->contrib_name)
						{
							// New support topic
							$email_vars = array(
								'NAME'			=> htmlspecialchars_decode($post_object->topic->topic_subject),
								'U_VIEW'		=> $post_object->topic->get_url(),
								'CONTRIB_NAME'	=> titania::$contrib->contrib_name,
							);
							titania_subscriptions::send_notifications($post_object->post_type, $post_object->topic->parent_id, 'subscribe_notify_forum_contrib.txt', $email_vars, $post_object->post_user_id);
						}
						else
						{
							$email_vars = array(
								'NAME'		=> htmlspecialchars_decode($post_object->topic->topic_subject),
								'U_VIEW'	=> $post_object->topic->get_url(),
							);
							titania_subscriptions::send_notifications($post_object->post_type, $post_object->topic->parent_id, 'subscribe_notify_forum.txt', $email_vars, $post_object->post_user_id);
						}
					}
				}

				redirect($post_object->get_url());
			}
		}
		else if (sizeof($message_object->error))
		{
			phpbb::$template->assign_var('ERROR', implode('<br />', $message_object->error));
		}

		// Do we subscribe to actual topic?
		$is_subscribed 	= (($mode == 'edit' || $mode == 'reply') && titania_subscriptions::is_subscribed(TITANIA_TOPIC, $post_object->topic->topic_id)) ? true : false;

		phpbb::$template->assign_vars(array(
			'S_NOTIFY_ALLOWED'	=> (phpbb::$user->data['is_registered'] && !$is_subscribed) ? true : false,
			'S_NOTIFY_CHECKED'	=> (phpbb::$user->data['is_registered'] && !$is_subscribed && phpbb::$user->data['user_notify'] && $post_object->post_type == TITANIA_SUPPORT) ? ' checked=checked' : '',
		));

		$message_object->display();
	}

	// Common delete/undelete code
	private function common_delete($post_id, $undelete = false)
	{
		titania::add_lang('posting');
		phpbb::$user->add_lang('posting');

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		// Check permissions
		if ((!$undelete && !$post_object->acl_get('delete')) || ($undelete && !$post_object->acl_get('undelete')))
		{
			titania::needs_auth();
		}

		if (titania::confirm_box(true))
		{
			if (!$undelete)
			{
				$redirect_post_id = false;

				// Delete the post
				if (isset($_POST['hard_delete']) || $post_object->post_deleted)
				{
					if (!phpbb::$auth->acl_get('u_titania_post_hard_delete'))
					{
						titania::needs_auth();
					}

					$post_object->hard_delete();

					// Try to redirect to the next or previous post
					$redirect_post_id = posts_overlord::next_prev_post_id($post_object->topic_id, $post_object->post_id);
					if ($redirect_post_id)
					{
						redirect(titania_url::append_url($post_object->topic->get_url(), array('p' => $redirect_post_id, '#p' => $redirect_post_id)));
					}

					redirect(titania_url::build_url($post_object->topic->topic_url));
				}
				else
				{
					$post_object->soft_delete();

					if (phpbb::$auth->acl_get('u_titania_mod_post_mod'))
					{
						// They can see the post, redirect back to it
						redirect($post_object->get_url());
					}
					else
					{
						// They cannot see the post, try to redirect to the next or previous post
						$redirect_post_id = posts_overlord::next_prev_post_id($post_object->topic_id, $post_object->post_id);
						if ($redirect_post_id)
						{
							redirect(titania_url::append_url($post_object->topic->get_url(), array('p' => $redirect_post_id, '#p' => $redirect_post_id)));
						}
					}
				}

				redirect($post_object->topic->get_url());
			}
			else
			{
				$post_object->undelete();

				redirect($post_object->get_url());
			}
		}
		else
		{
			phpbb::$template->assign_var('S_HARD_DELETE', ((!$undelete && !$post_object->post_deleted && phpbb::$auth->acl_get('u_titania_post_hard_delete')) ? true : false));

			titania::confirm_box(false, ((!$undelete) ? 'DELETE_POST' : 'UNDELETE_POST'), '', array(), 'posting/delete_confirm.html');
		}

		redirect($post_object->get_url());
	}

	/**
	* Quick load a post
	*
	* @param mixed $post_id
	* @return object
	*/
	public function load_post($post_id)
	{
		$post = new titania_post();
		$post->post_id = $post_id;

		if ($post->load() === false)
		{
			trigger_error('NO_POST');
		}

		$post->topic = $this->load_topic($post->topic_id);

		return $post;
	}

	/**
	* Quick load a topic
	*
	* @param mixed $topic_id
	* @return object
	*/
	public function load_topic($topic_id)
	{
		topics_overlord::load_topic($topic_id);
		$topic = topics_overlord::get_topic_object($topic_id);

		if ($topic === false)
		{
			trigger_error('NO_TOPIC');
		}

		return $topic;
	}
}
