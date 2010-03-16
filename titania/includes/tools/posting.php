<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
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

class titania_posting
{
	/**
	* Attachments Extension Group
	*
	* @var string|bool Constant for the extension group or bool false to not allow attachments
	*/
	public $attachments_group;

	public function _construct($attachments_group = false)
	{
		$this->attachments_group = $attachments_group;
	}

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

			case 'delete' :
				$this->delete(request_var('p', 0));
			break;

			case 'undelete' :
				$this->undelete(request_var('p', 0));
			break;

			case 'report' :
				$this->report(request_var('p', 0));
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
			'lock_topic'	=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && $post_object->topic->topic_first_post_user_id == phpbb::$user->data['user_id'])) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));
		$message_object->set_settings(array(
			'display_captcha'			=> (!phpbb::$user->data['is_registered']) ? true : false,
			'attachments_group'			=> $this->attachments_group,
		));

		// Call our common posting handler
		$this->common_post($post_object, $message_object);

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
			'lock_topic'	=> (phpbb::$auth->acl_gets(array('u_titania_mod_post_mod', 'u_titania_post_mod_own'))) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));
		$message_object->set_settings(array(
			'display_captcha'			=> (!phpbb::$user->data['is_registered']) ? true : false,
			'subject_default_override'	=> 'Re: ' . $post_object->topic->topic_subject,
			'attachments_group'			=> $this->attachments_group,
		));

		// Call our common posting handler
		$this->common_post($post_object, $message_object);

		// Common stuff
		phpbb::$template->assign_vars(array(
			'S_POST_ACTION'		=> $post_object->topic->get_url('reply', titania_url::$current_page_url),
			'L_POST_A'			=> phpbb::$user->lang['POST_REPLY'],
		));
		titania::page_header('POST_REPLY');
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
		$can_sticky = phpbb::$auth->acl_get('u_titania_mod_post_mod');
		if ($post_object->post_type == TITANIA_SUPPORT)
		{
			if (is_object(titania::$contrib) && titania::$contrib->contrib_id == $post_object->topic->parent_id && titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
			{
				$can_sticky = true;
			}
			else if (!is_object(titania::$contrib) || !titania::$contrib->contrib_id == $post_object->topic->parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $post_object->topic->parent_id);
				if (titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
				{
					$can_sticky = true;
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
			'lock_topic'	=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && $post_object->topic->topic_first_post_user_id == phpbb::$user->data['user_id'])) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));
		$message_object->set_settings(array(
			'attachments_group'			=> $this->attachments_group,
		));

		// Call our common posting handler
		$this->common_post($post_object, $message_object);

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
			//phpbb::$template->assign_var('S_CAN_NOTIFY', true);

			titania::confirm_box(false, 'REPORT_POST', '', array(), 'posting/report_body.html');
		}

		redirect($post_object->get_url());
	}

	/**
	* Common posting stuff for post/reply/edit
	*
	* @param mixed $post_object
	* @param mixed $message_object
	*/
	private function common_post($post_object, $message_object)
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

				$post_object->submit();

				$message_object->submit($post_object->post_access);

				redirect($post_object->get_url());
			}
		}

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
				$redirect_post_id = posts_overlord::next_prev_post_id($post_object->topic_id, $post_object->post_id);

				// Delete the post (let's not allow hard deleting for now)
				$post_object->soft_delete();

				// try a nice redirect, back to the position where the post was deleted from
				if ($redirect_post_id)
				{
					redirect(titania_url::append_url($post_object->topic->get_url(), array('p' => $redirect_post_id, '#p' => $redirect_post_id)));
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
			titania::confirm_box(false, ((!$undelete) ? 'DELETE_POST' : 'UNDELETE_POST'));
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