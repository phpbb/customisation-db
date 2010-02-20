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

	public function act($template_body, $contrib = false, $post_type = false, $s_post_action = false)
	{
		$action = request_var('action', '');

		switch ($action)
		{
			case 'post' :
				if ($contrib === false || $post_type === false)
				{
					throw new exception('Must send contrib object and new post type');
				}

				$this->post($contrib, $post_type, (($s_post_action === false) ? titania_url::$current_page_url : $s_post_action));

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
		}
	}

	/**
	* Post a new topic
	*
	* @param object $contrib Contrib object
	* @param int $post_type Post Type
	* @param string $s_post_action URL to the current page to submit to
	*/
	public function post($contrib, $post_type, $s_post_action)
	{
		// Setup the post object we'll use
		$post_object = new titania_post($post_type);
		$post_object->topic->contrib = $contrib;

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'sticky_topic'	=> ($post_object->post_id == $post_object->topic->topic_first_post_id || $post_object->contrib->is_author || $post_object->contrib->is_active_coauthor) ? true : false,
			'lock_topic'	=> (phpbb::$auth->acl_get('m_titania_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && $post_object->topic->topic_first_post_user_id == phpbb::$user->data['user_id'])) ? true : false,
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
	* @param mixed $post_type
	* @param mixed $topic_id
	*/
	public function reply($topic_id)
	{
		// Load the stuff we need
		$topic = new titania_topic();
		$topic->topic_id = (int) $topic_id;

		if ($topic->load() === false)
		{
			trigger_error('NO_TOPIC');
		}

		$topic->contrib = new titania_contribution;
		if (!$topic->contrib->load($topic->contrib_id))
		{
			trigger_error('NO_CONTRIB');
		}

		$post_object = new titania_post($topic->topic_type, $topic);

		// @todo check permissions/auth level/etc

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'lock_topic'	=> (phpbb::$auth->acl_gets(array('m_titania_post_mod', 'u_titania_post_mod_own'))) ? true : false,
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
		// Load the stuff we need
		$post_object = new titania_post();
		$post_object->post_id = (int) $post_id;

		if ($post_object->load() === false)
		{
			trigger_error('NO_POST');
		}

		$post_object->topic = new titania_topic();
		$post_object->topic->topic_id = $post_object->topic_id;

		if ($post_object->topic->load() === false)
		{
			trigger_error('NO_TOPIC');
		}

		$post_object->topic->contrib = new titania_contribution;
		if (!$post_object->topic->contrib->load($post_object->topic->contrib_id))
		{
			trigger_error('NO_CONTRIB');
		}

		// @todo check permissions/auth level/etc

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'lock'			=> ($post_object->post_user_id != phpbb::$user->data['user_id'] && phpbb::$auth->acl_get('m_titania_post_mod')) ? true : false,
			'sticky_topic'	=> ($post_object->post_id == $post_object->topic->topic_first_post_id || $post_object->topic->contrib->is_author || $post_object->topic->contrib->is_active_coauthor) ? true : false,
			'lock_topic'	=> (phpbb::$auth->acl_get('m_titania_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && $post_object->topic->topic_first_post_user_id == phpbb::$user->data['user_id'])) ? true : false,
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
		phpbb::$user->add_lang('posting');

		// Load the stuff we need
		$post_object = new titania_post();
		$post_object->post_id = (int) $post_id;

		if ($post_object->load() === false)
		{
			trigger_error('NO_POST');
		}

		$post_object->topic = new titania_topic();
		$post_object->topic->topic_id = (int) $topic_id;

		if ($post_object->topic->load() === false)
		{
			trigger_error('NO_TOPIC');
		}

		$post_object->topic->contrib = new titania_contribution;
		$post_object->topic->contrib->load($post_object->topic->contrib_id);

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

				redirect($post_object->topic->get_url(false));
			}
			else
			{
				$post_object->undelete();

				redirect($post_object->get_url(false, true));
			}
		}
		else
		{
			titania::confirm_box(false, ((!$undelete) ? 'DELETE_POST' : 'UNDELETE_POST'), $post_object->get_url($action));
		}
		redirect($post_object->get_url(false, true));
	}
}