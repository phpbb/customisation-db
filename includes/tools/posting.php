<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

class titania_posting
{
	/**
	* Contrib type of parent
	*
	* @var int
	*/
	public $parent_type = 0;

	/** @var \titania_contrib */
	protected $contrib;

	public function act($contrib, $action, $topic_id, $template_body, $parent_id = false, $parent_url = false, $post_type = false, $s_post_action = false)
	{
		$this->contrib = $contrib;
		$post_id = phpbb::$request->variable('p', 0);

		switch ($action)
		{
			case 'post' :
				if ($parent_id === false || $parent_url == false || $post_type === false)
				{
					throw new exception('Must send parent_id, parent_url, and new post type to allow posting new topics');
				}

				return $this->post($parent_id, $parent_url, $post_type, (($s_post_action === false) ? titania_url::$current_page_url : $s_post_action));
			break;

			case 'quote' :
				return $this->reply($topic_id, $post_id);
			break;

			case 'reply' :
				return $this->reply($topic_id);
			break;

			case 'edit' :
				return $this->edit($post_id);
			break;

			case 'quick_edit' :
				return $this->quick_edit($post_id);
			break;

			case 'delete' :
			case 'undelete' :
			case 'report' :
				return $this->$action($post_id);
			break;

			case 'sticky_topic' :
			case 'unsticky_topic' :
				return $this->toggle_sticky($topic_id);
			break;

			case 'lock_topic' :
			case 'unlock_topic' :
			case 'delete_topic' :
			case 'undelete_topic' :
				return $this->$action($topic_id);
			break;

			case 'split_topic' :
			case 'move_posts' :
				return $this->split_topic($topic_id, $action);
			break;

			case 'hard_delete_topic' :
				return $this->delete_topic($topic_id, true);
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
			return array('needs_auth' => true);
		}

		// Setup the post object we'll use
		$post_object = new titania_post($post_type);
		$post_object->topic->parent_id = $parent_id;
		$post_object->topic->topic_url = serialize($parent_url);

		// Some more complicated permissions for stickes in support
		$can_sticky = phpbb::$auth->acl_get('u_titania_mod_post_mod');
		if ($post_type == TITANIA_SUPPORT)
		{
			if (is_object($this->contrib) && $this->contrib->contrib_id == $parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$can_sticky = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $parent_id);
				if ($this->contrib->is_author || $this->contrib->is_active_coauthor)
				{
					$can_sticky = true;
				}
			}
		}
		else if ($post_type == TITANIA_QUEUE_DISCUSSION)
		{
			if (is_object($this->contrib) && $this->contrib->contrib_id == $parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$post_object->topic->topic_category = $this->contrib->contrib_type;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $parent_id)
			{
				$contrib = new titania_contribution();
				$contrib->load((int) $parent_id);
				if ($this->contrib->is_author || $this->contrib->is_active_coauthor)
				{
					$post_object->topic->topic_category = $this->contrib->contrib_type;
				}
			}
		}

		// Load the message object
		$message_object = new titania_message($post_object);
		$message_object->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'sticky_topic'	=> $can_sticky,
			'lock_topic'	=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && is_object($this->contrib) && $this->contrib->contrib_id == $parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)) ? true : false,
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

		return array('title' => 'NEW_TOPIC');
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
			return array('needs_auth' => true);
		}

		// Load the stuff we need
		$topic = $this->load_topic($topic_id);

		$post_object = new titania_post($topic->topic_type, $topic);

		// Check permissions
		if (!$post_object->acl_get('reply'))
		{
			return array('needs_auth' => true);
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
			'lock_topic'	=> (phpbb::$auth->acl_get('u_titania_mod_post_mod') || (phpbb::$auth->acl_get('u_titania_post_mod_own') && is_object($this->contrib) && $this->contrib->contrib_id == $post_object->topic->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)) ? true : false,
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
			'S_POST_ACTION'		=> $topic->get_url('reply'),
			'L_POST_A'			=> phpbb::$user->lang['POST_REPLY'],

			'S_DISPLAY_REVIEW'	=> true,
		));
		return array('title' => 'POST_REPLY');
	}

	/**
	* Quick Edit a post
	*
	* @param mixed $post_id
	*/
	public function quick_edit($post_id)
	{
		$submit = phpbb::$request->is_set_post('submit');
		$full_editor = phpbb::$request->is_set_post('full_editor');

		// AJAX output
		if (!$submit && !$full_editor)
		{
			phpbb::$user->add_lang('viewtopic');

			// Load the stuff we need
			$post_object = $this->load_post($post_id);

			// Check permissions
			$this->quick_edit_auth_check($post_object);

			$post_message = $post_object->post_text;
			titania_decode_message($post_message, $post_object->post_text_uid);

			add_form_key('postform');

			phpbb::$template->assign_vars(array(
				'SUBJECT'		=> $post_object->post_subject,
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

			$result = array('result' => 'success', 'content' => phpbb::$template->assign_display('quick_edit'));
			$this->json_output($result);
		}

		if ($full_editor)
		{
			$this->edit($post_id);

			return;
		}

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		// Check permissions
		$this->quick_edit_auth_check($post_object);

		if (!check_form_key('postform'))
		{
			$result = array('result' => 'invalid_token', 'content' => phpbb::$user->lang['FORM_INVALID']);
			$this->json_output($result);
		}

		// Grab some data
		$for_edit = $post_object->generate_text_for_edit();

		// Set the post text
		$post_object->post_subject = utf8_normalize_nfc(phpbb::$request->variable('subject', '', true));
		$post_object->post_text = utf8_normalize_nfc(phpbb::$request->variable('message', '', true));

		// Generate for storage based on previous options
		$post_object->generate_text_for_storage($for_edit['allow_bbcode'], $for_edit['allow_urls'], $for_edit['allow_smilies']);
		
		// If u_titania_mod_post_mod permission then no edit info
		// Update edit info if user is editing his post, which is not the last within the topic.
		if (!phpbb::$auth->acl_get('u_titania_mod_post_mod') && ($post_object->topic->topic_last_post_id != $post_object->post_id))
		{
			$post_object->post_edit_time = time();
			$post_object->post_edit_user = phpbb::$user->data['user_id'];
		}
		
		// Submit
		$post_object->submit();

		// Load attachments
		$attachments = new titania_attachment($post_object->post_type, $post_object->post_id);
		$attachments->load_attachments();

		// Parse the mesage
		$message = $post_object->generate_text_for_display();
		$parsed_attachments = $attachments->parse_attachments($message);

		// echo the message (returned to the JS to display in the place of the old message)
		$content =  '<span>' . censor_text($post_object->post_subject) . '</span>' . $message;
		$result = array('result' => 'success', 'content' => $content);
		$this->json_output($result);
	}

	/**
	*  Check permissions for quick edit and exit with appropriate error if necessary.
	*/
	private function quick_edit_auth_check($post_object)
	{
		$result = array();

		// User must be logged in...
		if (phpbb::$user->data['user_id'] == ANONYMOUS)
		{
			$result = array('result' => 'error', 'content' => phpbb::$user->lang['LOGIN_EXPLAIN_EDIT']);
		}
		// Check permissions
		else if (!$post_object->acl_get('edit'))
		{
			$result = array('result' => 'error', 'content' => phpbb::$user->lang['NO_AUTH']);
		}

		if (!empty($result))
		{
			$this->json_output($result);
		}
	}

	/**
	* Output array in json format and exit
	*
	* @param array $data Data to output
	*/
	private function json_output($data)
	{
		echo json_encode($data);

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
			return array('needs_auth' => true);
		}

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		// Check permissions
		if (!$post_object->acl_get('edit'))
		{
			return array('needs_auth' => true);
		}

		// Some more complicated permissions for stickes in support
		$can_sticky = $can_lock = phpbb::$auth->acl_get('u_titania_mod_post_mod');
		if ($post_object->post_type == TITANIA_SUPPORT)
		{
			if (is_object($this->contrib) && $this->contrib->contrib_id == $post_object->topic->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$can_sticky = $can_lock = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $post_object->topic->parent_id)
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
			'lock_topic'	=> ($can_lock || (phpbb::$auth->acl_get('u_titania_post_mod_own') && (phpbb::$auth->acl_get('u_titania_post_mod_own') && is_object($this->contrib) && $this->contrib->contrib_id == $post_object->topic->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor))) ? true : false,
			'attachments'	=> phpbb::$auth->acl_get('u_titania_post_attach'),
		));

		// Call our common posting handler
		$this->common_post('edit', $post_object, $message_object);

		// Common stuff
		phpbb::$template->assign_vars(array(
			'S_POST_ACTION'		=> $post_object->get_url('edit'),
			'L_POST_A'			=> phpbb::$user->lang['EDIT_POST'],
		));
		return array('title' => 'EDIT_POST');
	}

	/**
	* Delete a post
	*
	* @param int $post_id
	*/
	public function delete($post_id)
	{
		return $this->common_delete($post_id);
	}

	/**
	* Undelete a soft deleted post
	*
	* @param int $post_id
	*/
	public function undelete($post_id)
	{
		return $this->common_delete($post_id, true);
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
			return array('needs_auth' => true);
		}

		// Load the stuff we need
		$post_object = $this->load_post($post_id);

		if (phpbb::$request->is_set_post('cancel'))
		{
			redirect($post_object->get_url());
		}
		else if (phpbb::$request->is_set_post('confirm') && check_form_key('report'))
		{
			$message = phpbb::$request->variable('report_text', '', true);
			$notify_reporter = phpbb::$request->variable('notify', false);
			$post_object->report($message, $notify_reporter);

			// Notifications

			redirect($post_object->get_url());
		}

		add_form_key('report');
		phpbb::$template->assign_var('S_CAN_NOTIFY', true);

		return array('title' => 'REPORT_POST', 'template' => 'posting/report_body.html');
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
			if (is_object($this->contrib) && $this->contrib->contrib_id == $topic_object->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $topic_object->parent_id)
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
			return array('needs_auth' => true);
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
			if (is_object($this->contrib) && $this->contrib->contrib_id == $topic_object->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $topic_object->parent_id)
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
			return array('needs_auth' => true);
		}

		if (confirm_box(true))
		{
			$topic_object->topic_locked = true;
			$topic_object->submit();

			redirect($topic_object->get_url());
		}
		else
		{
			confirm_box(false, 'LOCK_TOPIC');
		}

		redirect($topic_object->get_url());
	}

	/**
	* Split/merge
	*
	* @param int $topic_id
	* @param string $mode Either split_topic or move_posts
	*/
	public function split_topic($topic_id, $mode)
	{
		// Auth check
		if (!phpbb::$auth->acl_get('u_titania_mod_post_mod'))
		{
			return array('needs_auth' => true);
		}

		titania::add_lang('posting');
		phpbb::$user->add_lang('mcp');

		$subject = utf8_normalize_nfc(request_var('subject', '', true));
		$new_topic_id = request_var('new_topic_id', 0);
		$post_ids = request_var('post_ids', array(0));
		$submit = (isset($_POST['split'])) ? true : false;
		$range = request_var('from', '');
		$topic = $this->load_topic($topic_id);
		$errors = array();
				
		if ($topic->topic_type != TITANIA_SUPPORT)
		{
			trigger_error('SPLIT_NOT_ALLOWED');
		}

		$page_title = ($mode == 'split_topic') ? 'SPLIT_TOPIC' : 'MERGE_POSTS';
		$page_title = phpbb::$user->lang[$page_title] . ' - ' . $topic->topic_subject;

		if ($submit)
		{
			// Check for errors
			if (!check_form_key('split_topic'))
			{
				$errors[] = phpbb::$user->lang['FORM_INVALID'];
			}

			if ($mode == 'split_topic' && utf8_clean_string($subject) == '')
			{
				$errors[] = phpbb::$user->lang['NO_SUBJECT'];
			}
			else if ($mode == 'move_posts' && !$new_topic_id)
			{
				$errors[] = phpbb::$user->lang['NO_FINAL_TOPIC_SELECTED'];
			}

			if (empty($post_ids))
			{
				$errors[] = phpbb::$user->lang['NO_POST_SELECTED'];
			}

			if ($new_topic_id == $topic->topic_id)
			{
				$errors[] = phpbb::$user->lang['ERROR_MERGE_SAME_TOPIC'];
			}

			if (empty($errors))
			{
				if ($mode == 'move_posts')
				{
					// Load the topic
					$new_topic = $this->load_topic($new_topic_id);

					if (!$new_topic)
					{
						$errors[] = phpbb::$user->lang['NO_TOPIC'];
					}

					// Only allow support posts to be moved to the same contrib
					if ($new_topic->parent_id != $topic->parent_id || $new_topic->topic_type != TITANIA_SUPPORT)
					{
						$errors[] = phpbb::$user->lang['ERROR_NOT_SAME_PARENT'];
					}

					// Ensure that we don't have a range
					$range = false;
				}
				else
				{
					$sql_extra = 'post_id = ' . (int) $post_ids[0];

					// Get info from first post
					$sql = 'SELECT post_id, post_access, post_approved, post_time
						FROM ' . TITANIA_POSTS_TABLE . '
						WHERE post_type = ' . TITANIA_SUPPORT . ' 
							AND topic_id = ' . (int) $topic->topic_id . '
							AND ';
					$result = phpbb::$db->sql_query_limit($sql . $sql_extra, 1);
					$first_post = phpbb::$db->sql_fetchrow($result);
					phpbb::$db->sql_freeresult($result);

					if (!$first_post)
					{
						$errors[] = phpbb::$user->lang['NO_POST_SELECTED'];
					}
					else
					{
						if ($range == 'from')
						{
							// Get info from last post
							$sql_extra = 'post_time >= ' . (int) $first_post['post_time'] . '
								ORDER BY post_time DESC';

							$result = phpbb::$db->sql_query_limit($sql . $sql_extra, 1);
							$last_post = phpbb::$db->sql_fetchrow($result);
							phpbb::$db->sql_freeresult($result);

							$range = array('min' => $first_post['post_time'], 'max' => $last_post['post_time']);
						}
						else
						{
							$range = false;
						}

						// Create the new topic with some basic info.
						$data = array(
							'parent_id'						=> $topic->parent_id,
							'topic_type'					=> $topic->topic_type,
							'topic_access'					=> $first_post['post_access'],
							'topic_approved'				=> 1, // This will be adjusted later on.
							'topic_status'					=> ITEM_UNLOCKED,
							'topic_time'					=> $first_post['post_time'],
							'topic_subject'					=> $subject,
							'topic_url'						=> $topic->topic_url,
						);

						$new_topic = new titania_topic();
						$new_topic->__set_array($data);
						$new_topic->submit();

						// Use new subject as the first post's subject to avoid issues when it gets approved
						if (!$first_post['post_approved'])
						{
							$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . ' SET post_subject = "' . phpbb::$db->sql_escape($subject) . '" WHERE post_id = ' . (int) $first_post['post_id'];
							phpbb::$db->sql_query($sql);
						}
					}
				}

				// If there aren't any errors, go ahead and transfer the posts.
				if (empty($errors))
				{
					$new_topic->acquire_posts($topic, $post_ids, $range);
					redirect($new_topic->get_url());
				}
			}
		}

		if (!$submit || !empty($errors))
		{
			$errors = implode('<br />', $errors);

			phpbb::$template->assign_vars(array(
				'ERRORS'		=> $errors,
				'TOPIC_SUBJECT'	=> $topic->topic_subject,
				'S_SPLIT'		=> ($mode == 'split_topic') ? true : false,
				'SUBJECT'		=> $subject,
				'TO_ID'			=> $new_topic_id,
			));

			// Setup the sort tool
			$topic_sort = posts_overlord::build_sort();
			$topic_sort->request();
			$topic_sort->url_parameters = array('action' => $mode);
			// Display topic
			posts_overlord::display_topic($topic, $topic_sort);
			posts_overlord::assign_common();

			add_form_key('split_topic');
		}
		return array('title' => $page_title, 'template' => 'posting/split_merge_topic_body.html');
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
			if (is_object($this->contrib) && $this->contrib->contrib_id == $topic_object->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $topic_object->parent_id)
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
			return array('needs_auth' => true);
		}

		if (confirm_box(true))
		{
			$topic_object->topic_locked = false;
			$topic_object->submit();

			redirect($topic_object->get_url());
		}
		else
		{
			confirm_box(false, 'UNLOCK_TOPIC');
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
			if (is_object($this->contrib) && $this->contrib->contrib_id == $topic_object->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $topic_object->parent_id)
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
			return array('needs_auth' => true);
		}

		if (confirm_box(true))
		{
			if ($hard_delete)
			{
				$parent_url = $topic_object->get_parent_url();
				$topic_object->delete();

				redirect($parent_url);
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
				confirm_box(false, 'HARD_DELETE_TOPIC');
			}
			else
			{
				confirm_box(false, 'SOFT_DELETE_TOPIC');
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
			if (is_object($this->contrib) && $this->contrib->contrib_id == $topic_object->parent_id && $this->contrib->is_author || $this->contrib->is_active_coauthor)
			{
				$is_authed = true;
			}
			else if (!is_object($this->contrib) || !$this->contrib->contrib_id == $topic_object->parent_id)
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
			return array('needs_auth' => true);
		}

		if (confirm_box(true))
		{
			$topic_object->undelete();

			redirect($topic_object->get_url());
		}
		else
		{
			confirm_box(false, 'UNDELETE_TOPIC');
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

		$post_attachments = $message_object->attachments->get_attachments();

		// Ensure that post_attachment remains valid when the user doesn't submit the post after deleting all attachments
		if ($mode == 'edit' && $post_object->post_attachment && empty($post_attachments))
		{
			$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . ' SET post_attachment = 0 WHERE post_id = ' . (int) $post_object->post_id;
			phpbb::$db->sql_query($sql);
		}

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

				// Does the post need approval?  Never for the Queue Discussion or Queue. Do not set again in edit mode, otherwise this causes problems when the post has been approved.
				if (!phpbb::$auth->acl_get('u_titania_post_approved') && $post_object->post_type != TITANIA_QUEUE_DISCUSSION && $post_object->post_type != TITANIA_QUEUE && $mode != 'edit')
				{
					$post_object->post_approved = false;
				}

				$post_object->post_attachment = (!empty($post_attachments)) ? true : false;
				$post_object->parent_contrib_type = $this->parent_type;
				$post_object->submit();

				$message_object->submit($post_object->post_access);

				// Did they want to subscribe?
				if (phpbb::$request->is_set_post('notify') && phpbb::$user->data['is_registered'])
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
					// Send out subscription notifications
					if ($mode == 'post' || $mode == 'reply')
					{
						$is_support_topic = $post_object->post_type == TITANIA_SUPPORT &&
							is_object($this->contrib) &&
							$this->contrib->contrib_id == $post_object->topic->parent_id &&
							$this->contrib->contrib_name
						;

						$template = 'subscribe_notify';
						$topic_params = array();
						$email_vars = array(
							'NAME'	=> htmlspecialchars_decode($post_object->topic->topic_subject),
						);

						if ($is_support_topic)
						{
							$email_vars['CONTRIB_NAME']	= $this->contrib->contrib_name;
						}

						if ($mode == 'reply')
						{
							$object_type	= array(TITANIA_TOPIC);
							$object_id		= array($post_object->topic_id);
							$topic_params	= array(
								'view'	=> 'unread',
								'#'		=> 'unread',
							);

							if ($is_support_topic)
							{
								// Support topic reply
								$object_id[]	= $post_object->topic->parent_id;
								$object_type[]	= TITANIA_SUPPORT;
								$template 		.= '_contrib';
							}
						}
						else
						{
							$object_type	= $post_object->post_type;
							$object_id		= $post_object->topic->parent_id;
							$template		.= ($is_support_topic) ? '_forum_contrib' : '_forum';
						}

						$email_vars['U_VIEW'] = $post_object->topic->get_url(false, $topic_params);
						titania_subscriptions::send_notifications(
							$object_type,
							$object_id,
							"$template.txt",
							$email_vars,
							$post_object->post_user_id
						);
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

		$topic_access_level = TITANIA_ACCESS_PUBLIC;

		if ($mode == 'edit' || $mode == 'reply')
		{
			// If this is the first post, we'll allow lowering the access level, otherwise the topic access level is the minimum that can be set
			$topic_access_level = ($post_object->post_id == $post_object->topic->topic_first_post_id) ? TITANIA_ACCESS_PUBLIC : $post_object->topic->topic_access;
		}
		$message_object->display($topic_access_level);
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
			return array('needs_auth' => true);
		}

		if (confirm_box(true))
		{
			if (!$undelete)
			{
				$redirect_post_id = false;

				// Delete the post
				if (phpbb::$request->is_set_post('hard_delete') || $post_object->post_deleted)
				{
					if (!phpbb::$auth->acl_get('u_titania_post_hard_delete'))
					{
						return array('needs_auth' => true);
					}

					$post_object->hard_delete();

					// Try to redirect to the next or previous post
					$redirect_post_id = posts_overlord::next_prev_post_id($post_object->topic_id, $post_object->post_id);
					if ($redirect_post_id)
					{
						redirect($post_object->topic->get_url(false, array(
							'p' => $redirect_post_id,
							'#' => "p$redirect_post_id",
						)));
					}

					redirect($post_object->topic->get_parent_url());
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
							redirect($post_object->topic->get_url(false, array(
								'p'	=> $redirect_post_id,
								'#'	=> "p$redirect_post_id",
							)));
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

			confirm_box(false, ((!$undelete) ? 'DELETE_POST' : 'UNDELETE_POST'), '', 'posting/delete_confirm.html');
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
