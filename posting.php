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

namespace phpbb\titania;

use phpbb\exception\http_exception;
use phpbb\titania\message\message;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class posting
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\titania\message\message */
	protected $message;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	/** @var \phpbb\titania\attachment\operator */
	protected $attachments;

	/**
	* Contrib type of parent
	*
	* @var int
	*/
	public $parent_type = 0;

	/** @var \titania_contribution */
	protected $contrib;

	/** @var string */
	protected $template_file;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\template\template $template
	 * @param controller\helper $controller_helper
	 * @param message $message
	 * @param access $access
	 * @param subscriptions $subscriptions
	 * @param \phpbb\titania\attachment\operator $attachments
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\template\template $template, controller\helper $controller_helper, message $message, access $access, subscriptions $subscriptions, \phpbb\titania\attachment\operator $attachments)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->template = $template;
		$this->controller_helper = $controller_helper;
		$this->message = $message;
		$this->access = $access;
		$this->subscriptions = $subscriptions;
		$this->attachments = $attachments;
	}

	/**
	 * Act on a posting action.
	 *
	 * @param \titania_contribution|null	$contrib
	 * @param string $action
	 * @param int $topic_id
	 * @param string $template_body
	 * @param bool $parent_id
	 * @param bool $parent_url
	 * @param bool $post_type
	 * @param bool $s_post_action
	 * @return JsonResponse|RedirectResponse|Response
	 * @throws \LogicException
	 */
	public function act($contrib, $action, $topic_id, $template_body, $parent_id = false, $parent_url = false, $post_type = false, $s_post_action = false)
	{
		$this->user->add_lang_ext('phpbb/titania', 'posting');
		$this->contrib = $contrib;
		$this->template_file = $template_body;
		$post_id = $this->request->variable('p', 0);

		switch ($action)
		{
			case 'post' :
				if ($parent_id === false || $parent_url == false || $post_type === false)
				{
					throw new \LogicException('Must send parent_id, parent_url, and new post type to allow posting new topics');
				}
				$s_post_action = ($s_post_action === false) ? $this->controller_helper->get_current_url() : $s_post_action;

				return $this->post($parent_id, $parent_url, $post_type, $s_post_action);
			break;

			case 'edit' :
			case 'quick_edit' :
			case 'delete' :
			case 'undelete' :
			case 'report' :
				return $this->{$action}($post_id);
			break;

			case 'quote' :
				return $this->reply($topic_id, $post_id);
			break;

			case 'sticky_topic' :
			case 'unsticky_topic' :
				return $this->toggle_sticky($topic_id);
			break;

			case 'reply' :
			case 'lock_topic' :
			case 'unlock_topic' :
			case 'delete_topic' :
			case 'undelete_topic' :
				return $this->{$action}($topic_id);
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
	 * @param int $parent_id		The parent_id
	 * @param array $parent_params	The parameters of the parent's url
	 * @param int $post_type		Post Type
	 * @param string $s_post_action	URL to the current page to submit to
	 * @return Response
	 */
	public function post($parent_id, $parent_params, $post_type, $s_post_action)
	{
		if (!$this->auth->acl_get('u_titania_topic'))
		{
			return $this->controller_helper->needs_auth();
		}

		// Setup the post object we'll use
		$post = new \titania_post($post_type);
		$post->topic->parent_id = $parent_id;
		$post->topic->topic_url = serialize($parent_params);

		// Some more complicated permissions for stickes in support
		$is_moderator = $this->auth->acl_get('u_titania_mod_post_mod');
		$can_moderate_own = $this->auth->acl_get('u_titania_post_mod_own');
		$is_author = false;

		if ($this->is_topic_moderatable($post_type))
		{
			$is_author = $this->get_contrib($parent_id)->is_author();
		}
		if ($is_author && $post_type == TITANIA_QUEUE_DISCUSSION)
		{
			$post->topic->topic_category = $this->contrib->contrib_type;
		}

		// Load the message object
		$this->setup_message($post,
			array(
				'sticky_topic'	=> $post_type == TITANIA_SUPPORT && ($is_moderator || $is_author),
				'lock_topic'	=> $is_moderator || ($is_author && $can_moderate_own),
			),
			array(
				'display_captcha'	=> !$this->user->data['is_registered'],
			)
		);

		// Call our common posting handler
		$response = $this->common_post('post', $post, $this->message);

		if ($response)
		{
			return $response;
		}

		// Common stuff
		$this->template->assign_vars(array(
			'S_POST_ACTION'		=> $s_post_action,
			'L_POST_A'			=> $this->user->lang['POST_TOPIC'],
		));

		return $this->controller_helper->render(
			$this->template_file,
			'NEW_TOPIC'
		);
	}

	/**
	 * Reply to an existing topic
	 *
	 * @param int $topic_id				Topic id
	 * @param int|bool $quote_post_id	Optional post id to quote. Defaults to false.
	 * @return Response|JsonResponse
	 */
	public function reply($topic_id, $quote_post_id = false)
	{
		if (!$this->auth->acl_get('u_titania_post'))
		{
			return $this->controller_helper->needs_auth();
		}

		// Load the stuff we need
		$topic = $this->load_topic($topic_id);

		$post = new \titania_post($topic->topic_type, $topic);

		// Check permissions
		if (!$post->acl_get('reply'))
		{
			return $this->controller_helper->needs_auth();
		}

		// Quoting?
		if ($quote_post_id !== false && $post->post_text == '')
		{
			$post->post_text = $this->get_quote($quote_post_id);
		}

		$can_lock = false;
		$topic_moderatable = $this->is_topic_moderatable($topic->topic_type);

		if ($topic_moderatable)
		{
			$is_author = $this->get_contrib($post->topic->parent_id)->is_author();
			$can_lock = $this->auth->acl_get('u_titania_mod_post_mod') ||
				($this->auth->acl_get('u_titania_post_mod_own') && $is_author);
		}

		// Load the message object
		$this->setup_message($post,
			array(
				'lock_topic'				=> $can_lock,
			),
			array(
				'display_captcha'			=> !$this->user->data['is_registered'],
				'subject_default_override'	=> 'Re: ' . $post->topic->topic_subject,
			)
		);

		// Call our common posting handler
		$response = $this->common_post('reply', $post, $this->message);

		if ($response)
		{
			return $response;
		}

		// Setup the sort tool
		$topic_sort = \posts_overlord::build_sort();
		$topic_sort->set_defaults(false, false, 'd');

		// Display the posts for review
		\posts_overlord::display_topic($post->topic, $topic_sort);

		// Common stuff
		$this->template->assign_vars(array(
			'S_POST_ACTION'		=> $topic->get_url('reply'),
			'L_POST_A'			=> $this->user->lang['POST_REPLY'],

			'S_DISPLAY_REVIEW'	=> true,
		));

		return $this->controller_helper->render(
			$this->template_file,
			'POST_REPLY'
		);
	}

	/**
	 * Get quote for a post.
	 *
	 * @param int $post_id
	 * @return string
	 */
	protected function get_quote($post_id)
	{
		$text = '';
		$quote = $this->load_post($post_id);
		$quoted_post_is_accessible = $this->access->get_level() <= min(
				$quote->post_access,
				$quote->topic->topic_access
			);
		$can_quote_post =
			$this->auth->acl_get('u_titania_mod_post_mod') ||
			($quote->post_approved &&
				(!$quote->post_deleted || $quote->post_deleted == $this->user->data['user_id'])
			);

		// Permission check
		if ($quoted_post_is_accessible && $can_quote_post)
		{
			$for_edit = $quote->generate_text_for_edit();
			$quote_username = \users_overlord::get_user($quote->post_user_id, '_username', true);

			$text = '[quote="' . $quote_username . '"]' . $for_edit['text'] . '[/quote]';
		}
		return $text;
	}

	/**
	 * Quick Edit a post
	 *
	 * @param int $post_id
	 * @return JsonResponse
	 * @throws http_exception	Throws http_exception when form token is invalid.
	 */
	public function quick_edit($post_id)
	{
		$submit = $this->request->is_set_post('submit');
		$full_editor = $this->request->is_set_post('full_editor');

		// AJAX output
		if (!$submit && !$full_editor)
		{
			$this->user->add_lang('viewtopic');

			// Load the stuff we need
			$post = $this->load_post($post_id);

			// Check permissions
			$this->quick_edit_auth_check($post);

			$post_message = $post->post_text;
			message::decode($post_message, $post->post_text_uid);

			add_form_key('postform');

			$this->template
				->assign_vars(array(
					'SUBJECT'		=> $post->post_subject,
					'MESSAGE'		=> $post_message,

					'U_QR_ACTION'	=> $post->get_url('quick_edit'),
				))
				->set_filenames(array(
					'quick_edit'	=> 'posting/quickedit_editor.html'
				));


			return new JsonResponse(array(
				'form' => $this->template->assign_display('quick_edit')
			));
		}

		if ($full_editor)
		{
			return $this->edit($post_id);
		}

		// Load the stuff we need
		$post = $this->load_post($post_id);

		// Check permissions
		$this->quick_edit_auth_check($post);

		if (!check_form_key('postform'))
		{
			throw new http_exception(200, 'FORM_INVALID');
		}

		// Grab some data
		$for_edit = $post->generate_text_for_edit();

		// Set the post text
		$post->post_subject = $this->request->variable('subject', '', true);
		$post->post_text = $this->request->variable('message', '', true);

		// Generate for storage based on previous options
		$post->generate_text_for_storage(
			$for_edit['allow_bbcode'],
			$for_edit['allow_urls'],
			$for_edit['allow_smilies']
		);
		
		// If u_titania_mod_post_mod permission then no edit info
		// Update edit info if user is editing his post, which is not the last within the topic.
		if (!$this->auth->acl_get('u_titania_mod_post_mod') && ($post->topic->topic_last_post_id != $post->post_id))
		{
			$post->post_edit_time = time();
			$post->post_edit_user = $this->user->data['user_id'];
		}
		
		// Submit
		$post->submit();

		// Parse the message
		$message = $post->generate_text_for_display();

		// Parse attachments
		$this->attachments
			->configure($post->post_type, $post->post_id)
			->load()
			->parse_attachments($message)
		;

		return new JsonResponse(array(
			'subject'		=> censor_text($post->post_subject),
			'message'		=> $message,
		));
	}

	/**
	 * Check permissions for quick edit and exit with appropriate error if necessary.
	 *
	 * @param \titania_post $post	Post being edited
	 * @throws http_exception 		Throws http_exception if user is not authorized.
	 */
	protected function quick_edit_auth_check(\titania_post $post)
	{
		// User must be logged in...
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			throw new http_exception(403, $this->user->lang('LOGIN_EXPLAIN_EDIT'));
		}
		// Check permissions
		else if (!$post->acl_get('edit'))
		{
			throw new http_exception(403, $this->user->lang('NO_AUTH'));
		}
	}

	/**
	 * Edit an existing post
	 *
	 * @param int $post_id
	 * @return JsonResponse|Response
	 */
	public function edit($post_id)
	{
		if ($this->request->is_ajax() && !$this->request->header('X-PHPBB-USING-PLUPLOAD', false))
		{
			return $this->quick_edit($post_id);
		}

		if (!$this->auth->acl_get('u_titania_post'))
		{
			return $this->controller_helper->needs_auth();
		}

		// Load the stuff we need
		$post = $this->load_post($post_id);

		// Check permissions
		if (!$post->acl_get('edit'))
		{
			return $this->controller_helper->needs_auth();
		}

		// Some more complicated permissions for stickes in support
		$can_moderate_own = $this->auth->acl_get('u_titania_post_mod_own');
		$is_moderator = $this->auth->acl_get('u_titania_mod_post_mod');
		$is_author = false;

		if ($post->post_type == TITANIA_SUPPORT)
		{
			$is_author = $this->get_contrib($post->topic->parent_id)->is_author();
		}

		$can_lock_topic = $is_moderator || ($is_author && $can_moderate_own);
		$can_sticky = ($is_moderator || $is_author) &&
			$post->post_id == $post->topic->topic_first_post_id;
		$can_lock_post = $is_moderator &&
			$post->post_user_id != $this->user->data['user_id'];

		// Load the message object
		$this->setup_message($post,
			array(
				'lock'			=> $can_lock_post,
				'sticky_topic'	=> $can_sticky,
				'lock_topic'	=> $can_lock_topic,
			)
		);

		// Call our common posting handler
		$response = $this->common_post('edit', $post, $this->message);

		if ($response)
		{
			return $response;
		}

		// Common stuff
		$this->template->assign_vars(array(
			'S_POST_ACTION'		=> $post->get_url('edit'),
			'L_POST_A'			=> $this->user->lang['EDIT_POST'],
		));

		return $this->controller_helper->render(
			$this->template_file,
			'EDIT_POST'
		);
	}

	/**
	 * Report a post
	 *
	 * @param int $post_id
	 * @return Response|RedirectResponse
	 */
	public function report($post_id)
	{
		$this->user->add_lang('mcp');

		// Check permissions
		if (!$this->user->data['is_registered'])
		{
			return $this->controller_helper->needs_auth();
		}

		// Load the stuff we need
		$post = $this->load_post($post_id);

		if ($this->request->is_set_post('cancel'))
		{
			return new RedirectResponse($post->get_url());
		}
		else if ($this->request->is_set_post('confirm') && check_form_key('report'))
		{
			$message = $this->request->variable('report_text', '', true);
			$notify_reporter = $this->request->variable('notify', false);
			$post->report($message, $notify_reporter);

			return new RedirectResponse($post->get_url());
		}

		add_form_key('report');
		$this->template->assign_var('S_CAN_NOTIFY', true);

		return $this->controller_helper->render(
			'posting/report_body.html',
			'REPORT_POST'
		);
	}

	/**
	 * Perform set up for moderating a topic.
	 *
	 * @param int $topic_id
	 * @return null|\titania_topic	Returns topic object or null if
	 * 	user is not authorized to moderate the topic.
	 * @throws \phpbb\exception\http_exception	Throws http_exception if the topic type
	 * 	does not allow moderation of the topic.
	 */
	protected function topic_moderation_setup($topic_id)
	{
		$this->user->add_lang('mcp');

		// Load the stuff we need
		$topic = $this->load_topic($topic_id);

		if (!$this->is_topic_moderatable($topic->topic_type))
		{
			throw new http_exception(200, 'UNSUPPORTED_ACTION');
		}

		// Check permissions
		if (!$this->topic_moderation_auth_check($topic->parent_id))
		{
			return null;
		}
		return $topic;
	}

	/**
	 * Perform basic moderation auth check.
	 *
	 * @param int $contrib_id
	 * @return bool
	 */
	protected function topic_moderation_auth_check($contrib_id)
	{
		$is_moderator = $this->auth->acl_get('u_titania_mod_post_mod');
		$can_moderate_own = $this->auth->acl_get('u_titania_post_mod_own');
		$is_author = false;

		if (!$is_moderator && $can_moderate_own)
		{
			$is_author = $this->get_contrib($contrib_id)->is_author();
		}
		return $is_moderator || ($is_author && $can_moderate_own);
	}

	/**
	 * Delete a post
	 *
	 * @param int $post_id
	 * @return Response
	 */
	public function delete($post_id)
	{
		return $this->common_delete($post_id);
	}

	/**
	 * Undelete a soft deleted post
	 *
	 * @param int $post_id
	 * @return Response
	 */
	public function undelete($post_id)
	{
		return $this->common_delete($post_id, true);
	}

	/**
	 * Sticky a topic
	 *
	 * @param int $topic_id
	 * @return Response|RedirectResponse
	 */
	public function toggle_sticky($topic_id)
	{
		$topic = $this->topic_moderation_setup($topic_id);

		if (!$topic)
		{
			return $this->controller_helper->needs_auth();
		}

		$topic->topic_sticky = !$topic->topic_sticky;
		$topic->submit();

		return new RedirectResponse($topic->get_url());
	}

	/**
	 * Split/merge
	 *
	 * @param int $topic_id
	 * @param string $mode Either split_topic or move_posts
	 * @return Response|RedirectResponse
	 */
	public function split_topic($topic_id, $mode)
	{
		// Auth check
		if (!$this->auth->acl_get('u_titania_mod_post_mod'))
		{
			return $this->controller_helper->needs_auth();
		}

		$this->user->add_lang('mcp');

		$subject = $this->request->variable('subject', '', true);
		$new_topic_id = $this->request->variable('new_topic_id', 0);
		$post_ids = $this->request->variable('post_ids', array(0));
		$submit = $this->request->is_set_post('split');
		$range = $this->request->variable('from', '');
		$topic = $this->load_topic($topic_id);
		$errors = array();
				
		if ($topic->topic_type != TITANIA_SUPPORT)
		{
			return $this->controller_helper->message('SPLIT_NOT_ALLOWED');
		}

		$page_title = ($mode == 'split_topic') ? 'SPLIT_TOPIC' : 'MERGE_POSTS';
		$page_title = $this->user->lang($page_title) . ' - ' . $topic->topic_subject;

		if ($submit)
		{
			// Check for errors
			if (!check_form_key('split_topic'))
			{
				$errors[] = $this->user->lang['FORM_INVALID'];
			}

			if ($mode == 'split_topic' && utf8_clean_string($subject) == '')
			{
				$errors[] = $this->user->lang['NO_SUBJECT'];
			}
			else if ($mode == 'move_posts' && !$new_topic_id)
			{
				$errors[] = $this->user->lang['NO_FINAL_TOPIC_SELECTED'];
			}

			if (empty($post_ids))
			{
				$errors[] = $this->user->lang['NO_POST_SELECTED'];
			}

			if ($new_topic_id == $topic->topic_id)
			{
				$errors[] = $this->user->lang['ERROR_MERGE_SAME_TOPIC'];
			}

			if (empty($errors))
			{
				if ($mode == 'move_posts')
				{
					// Load the topic
					$new_topic = $this->load_topic($new_topic_id);

					if (!$new_topic)
					{
						$errors[] = $this->user->lang['NO_TOPIC'];
					}

					// Only allow support posts to be moved to the same contrib
					if ($new_topic->parent_id != $topic->parent_id || $new_topic->topic_type != TITANIA_SUPPORT)
					{
						$errors[] = $this->user->lang['ERROR_NOT_SAME_PARENT'];
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
					$result = $this->db->sql_query_limit($sql . $sql_extra, 1);
					$first_post = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$first_post)
					{
						$errors[] = $this->user->lang['NO_POST_SELECTED'];
					}
					else
					{
						if ($range == 'from')
						{
							// Get info from last post
							$sql_extra = 'post_time >= ' . (int) $first_post['post_time'] . '
								ORDER BY post_time DESC';

							$result = $this->db->sql_query_limit($sql . $sql_extra, 1);
							$last_post = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);

							$range = array(
								'min' => $first_post['post_time'],
								'max' => $last_post['post_time'],
							);
						}
						else
						{
							$range = false;
						}

						// Create the new \topic with some basic info.
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

						$new_topic = new \titania_topic;
						$new_topic->__set_array($data);
						$new_topic->submit();

						// Use new subject as the first post's subject to avoid issues when it gets approved
						if (!$first_post['post_approved'])
						{
							$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
								SET post_subject = "' . $this->db->sql_escape($subject) . '"
								WHERE post_id = ' . (int) $first_post['post_id'];
							$this->db->sql_query($sql);
						}
					}
				}

				// If there aren't any errors, go ahead and transfer the posts.
				if (empty($errors))
				{
					$new_topic->acquire_posts($topic, $post_ids, $range);
					return new RedirectResponse($new_topic->get_url());
				}
			}
		}

		if (!$submit || !empty($errors))
		{
			$errors = implode('<br />', $errors);

			$this->template->assign_vars(array(
				'ERRORS'		=> $errors,
				'TOPIC_SUBJECT'	=> $topic->topic_subject,
				'S_SPLIT'		=> $mode == 'split_topic',
				'SUBJECT'		=> $subject,
				'TO_ID'			=> $new_topic_id,
			));

			// Setup the sort tool
			$topic_sort = \posts_overlord::build_sort();
			$topic_sort->request();
			$topic_sort->url_parameters = array('action' => $mode);
			// Display topic
			\posts_overlord::display_topic($topic, $topic_sort);
			\posts_overlord::assign_common();

			add_form_key('split_topic');
		}
		return $this->controller_helper->render(
			'posting/split_merge_topic_body.html',
			$page_title
		);
	}

	/**
	 * Lock a topic
	 *
	 * @param int $topic_id
	 * @return Response|RedirectResponse
	 */
	public function lock_topic($topic_id)
	{
		return $this->lock_topic_toggle($topic_id, 'lock');
	}

	/**
	 * Unlock a topic
	 *
	 * @param int $topic_id
	 * @return Response|RedirectResponse
	 */
	public function unlock_topic($topic_id)
	{
		return $this->lock_topic_toggle($topic_id, 'unlock');
	}

	/**
	 * Lock/unlock a topic
	 *
	 * @param int $topic_id
	 * @param string $mode lock|unlock
	 * @return Response|RedirectResponse
	 */
	public function lock_topic_toggle($topic_id, $mode)
	{
		$topic = $this->topic_moderation_setup($topic_id);

		if (!$topic)
		{
			return $this->controller_helper->needs_auth();
		}

		if (confirm_box(true))
		{
			$topic->topic_locked = $mode == 'lock';
			$topic->submit();
		}
		else
		{
			$title = ($mode == 'lock') ? 'LOCK_TOPIC' : 'UNLOCK_TOPIC';
			confirm_box(false, $title);
		}

		return new RedirectResponse($topic->get_url());
	}

	/**
	 * Delete a topic
	 *
	 * @param int $topic_id
	 * @param bool $hard_delete Hard delete or just soft delete?
	 * @return Response|RedirectResponse
	 */
	public function delete_topic($topic_id, $hard_delete = false)
	{
		$topic = $this->topic_moderation_setup($topic_id);

		if (!$topic)
		{
			return $this->controller_helper->needs_auth();
		}

		if (confirm_box(true))
		{
			if ($hard_delete)
			{
				$parent_url = $topic->get_parent_url();
				$topic->delete();

				return new RedirectResponse($parent_url);
			}
			else
			{
				$topic->soft_delete();

				return new RedirectResponse($topic->get_url());
			}
		}
		else
		{
			$title = ($hard_delete) ? 'HARD_DELETE_TOPIC' : 'SOFT_DELETE_TOPIC';
			confirm_box(false, $title);
		}

		return new RedirectResponse($topic->get_url());
	}

	/**
	 * Undelete a topic
	 *
	 * @param int $topic_id
	 * @return Response|RedirectResponse
	 */
	public function undelete_topic($topic_id)
	{
		$topic = $this->topic_moderation_setup($topic_id);

		if (!$topic)
		{
			return $this->controller_helper->needs_auth();
		}

		if (confirm_box(true))
		{
			$topic->undelete();
		}
		else
		{
			confirm_box(false, 'UNDELETE_TOPIC');
		}

		return new RedirectResponse($topic->get_url());
	}

	/**
	 * Common posting stuff for post/reply/edit
	 *
	 * @param string $mode
	 * @param \titania_post $post
	 * @return JsonResponse|RedirectResponse|Response|null
	 */
	protected function common_post($mode, $post)
	{
		$this->user->add_lang('posting');

		// Submit check...handles running $post->post_data() if required
		$submit = $this->message->submit_check();
		$is_reply = $mode == 'edit' || $mode == 'reply';
		$post_attachments = $this->message->has_attachments();

		// Ensure that post_attachment remains valid when the user doesn't submit the post after deleting all attachments
		if ($mode == 'edit' && $post->post_attachment && empty($post_attachments))
		{
			$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
				SET post_attachment = 0
				WHERE post_id = ' . (int) $post->post_id;
			$this->db->sql_query($sql);
		}

		if ($this->message->is_plupload_request())
		{
			return new JsonResponse(
				$this->message->get_plupload_response_data()
			);
		}

		if ($submit)
		{
			$error = $post->validate();

			if (($validate_form_key = $this->message->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}

			// @todo use permissions for captcha
			if (!$this->user->data['is_registered'] && ($validate_captcha = $this->message->validate_captcha()) !== false)
			{
				$error[] = $validate_captcha;
			}

			$error = array_merge($error, $this->message->error);

			if (sizeof($error))
			{
				$this->template->assign_var('ERROR', implode('<br />', $error));
			}
			else
			{
				// Force Queue Discussion topics to always be stickies
				if ($post->post_type == TITANIA_QUEUE_DISCUSSION)
				{
					$post->topic->topic_sticky = true;
				}

				// Does the post need approval?  Never for the Queue Discussion or Queue. Do not set again in edit mode, otherwise this causes problems when the post has been approved.
				if (!$this->auth->acl_get('u_titania_post_approved') && $post->post_type != TITANIA_QUEUE_DISCUSSION && $post->post_type != TITANIA_QUEUE && $mode != 'edit')
				{
					$post->post_approved = false;
				}

				$post->post_attachment = !empty($post_attachments);
				$post->parent_contrib_type = $this->parent_type;
				$post->submit();

				$this->message->submit($post->post_access);

				// Did they want to subscribe?
				if ($this->request->is_set_post('notify') && $this->user->data['is_registered'])
				{
					$this->subscriptions->subscribe(TITANIA_TOPIC, $post->topic->topic_id);
				}

				// Unapproved posts will get a notice
				if (!$post->topic->get_postcount())
				{
					return $this->controller_helper->message(
						$this->user->lang['POST_STORED_MOD'] . '<br /><br />' .
						$this->user->lang(
							'RETURN_INDEX',
							'<a href="' . $post->topic->get_parent_url() . '">',
							'</a>'
						)
					);
				}
				else if (!$post->post_approved)
				{
					return $this->controller_helper->message(
						$this->user->lang['POST_STORED_MOD'] . '<br /><br />' .
						$this->user->lang(
							'RETURN_TOPIC',
							'<a href="' . $post->topic->get_url() . '">',
							'</a>'
						)
					);
				}
				else
				{
					// Send out subscription notifications
					if ($mode == 'post' || $mode == 'reply')
					{
						$this->send_notifications($post, $mode);
					}
				}

				return new RedirectResponse($post->get_url());
			}
		}
		else if (!empty($this->message->error))
		{
			$this->template->assign_var('ERROR', implode('<br />', $this->message->error));
		}

		// Do we subscribe to actual topic?
		$is_subscribed 	= $is_reply && $this->subscriptions->is_subscribed(TITANIA_TOPIC, $post->topic->topic_id);
		$can_subscribe = $this->user->data['is_registered'] && !$is_subscribed;

		$this->template->assign_vars(array(
			'S_NOTIFY_ALLOWED'	=> $can_subscribe,
			'S_NOTIFY_CHECKED'	=> ($can_subscribe && $this->user->data['user_notify'] && $post->post_type == TITANIA_SUPPORT) ? ' checked=checked' : '',
		));

		$topic_access_level = access::PUBLIC_LEVEL;

		if ($is_reply)
		{
			// If this is the first post, we'll allow lowering the access level, otherwise the topic access level is the minimum that can be set
			$topic_access_level = ($post->post_id == $post->topic->topic_first_post_id) ? access::PUBLIC_LEVEL : $post->topic->topic_access;
		}
		$this->message->display($topic_access_level);
	}

	// Common delete/undelete code
	protected function common_delete($post_id, $undelete = false)
	{
		$this->user->add_lang('posting');

		// Load the stuff we need
		$post = $this->load_post($post_id);

		// Check permissions
		if ((!$undelete && !$post->acl_get('delete')) || ($undelete && !$post->acl_get('undelete')))
		{
			return $this->controller_helper->needs_auth();
		}

		if (confirm_box(true))
		{
			if (!$undelete)
			{

				// Delete the post
				if ($this->request->is_set_post('hard_delete') || $post->post_deleted)
				{
					if (!$this->auth->acl_get('u_titania_post_hard_delete'))
					{
						return $this->controller_helper->needs_auth();
					}

					$post->hard_delete();

					// Try to redirect to the next or previous post
					$redirect_post_id = \posts_overlord::next_prev_post_id($post->topic_id, $post->post_id);
					if ($redirect_post_id)
					{
						return new RedirectResponse($post->topic->get_url(false, array(
							'p' => $redirect_post_id,
							'#' => "p$redirect_post_id",
						)));
					}

					return new RedirectResponse($post->topic->get_parent_url());
				}
				else
				{
					$post->soft_delete();

					if ($this->auth->acl_get('u_titania_mod_post_mod'))
					{
						// They can see the post, redirect back to it
						return new RedirectResponse($post->get_url());
					}
					else
					{
						// They cannot see the post, try to redirect to the next or previous post
						$redirect_post_id = \posts_overlord::next_prev_post_id($post->topic_id, $post->post_id);
						if ($redirect_post_id)
						{
							return new RedirectResponse($post->topic->get_url(false, array(
								'p'	=> $redirect_post_id,
								'#'	=> "p$redirect_post_id",
							)));
						}
					}
				}

				return new RedirectResponse($post->topic->get_url());
			}
			else
			{
				$post->undelete();

				return new RedirectResponse($post->get_url());
			}
		}
		else
		{
			$s_hard_delete = !$undelete && !$post->post_deleted && $this->auth->acl_get('u_titania_post_hard_delete');
			$this->template->assign_var('S_HARD_DELETE', $s_hard_delete);

			confirm_box(false, ((!$undelete) ? 'DELETE_POST' : 'UNDELETE_POST'), '', 'posting/delete_confirm.html');
		}

		return new RedirectResponse($post->get_url());
	}

	/**
	 * Quick load a post
	 *
	 * @param int $post_id
	 * @return \titania_post
	 * @throws \OutOfBoundsException	Throws exception if the post does not exist.
	 */
	public function load_post($post_id)
	{
		$post = new \titania_post;
		$post->post_id = $post_id;

		if ($post->load() === false)
		{
			throw new \OutOfBoundsException($this->user->lang('NO_POST'));
		}

		$post->topic = $this->load_topic($post->topic_id);

		return $post;
	}

	/**
	 * Quick load a topic
	 *
	 * @param int $topic_id
	 * @return \titania_topic
	 * @throws \OutOfBoundsException	Throws exception if the topic does not exist.
	 */
	public function load_topic($topic_id)
	{
		\topics_overlord::load_topic($topic_id);
		$topic = \topics_overlord::get_topic_object($topic_id);

		if ($topic === false)
		{
			throw new \OutOfBoundsException($this->user->lang('NO_TOPIC'));
		}

		return $topic;
	}

	/**
	 * Send notification for new post.
	 *
	 * @param \titania_post $post	New post
	 * @param string $mode			Post mode: post|reply
	 */
	protected function send_notifications(\titania_post $post, $mode)
	{
		$is_support_topic = $post->post_type == TITANIA_SUPPORT &&
			is_object($this->contrib) &&
			$this->contrib->contrib_id == $post->topic->parent_id &&
			$this->contrib->contrib_name
		;

		$template = 'subscribe_notify';
		$topic_params = array();
		$email_vars = array(
			'NAME'	=> htmlspecialchars_decode($post->topic->topic_subject),
		);

		if ($is_support_topic)
		{
			$email_vars['CONTRIB_NAME']	= $this->contrib->contrib_name;
		}

		if ($mode == 'reply')
		{
			$object_type	= array(TITANIA_TOPIC);
			$object_id		= array($post->topic_id);
			$topic_params	= array(
				'view'	=> 'unread',
				'#'		=> 'unread',
			);

			if ($is_support_topic)
			{
				// Support topic reply
				$object_id[]	= $post->topic->parent_id;
				$object_type[]	= TITANIA_SUPPORT;
				$template 		.= '_contrib';
			}
		}
		else
		{
			$object_type	= $post->post_type;
			$object_id		= $post->topic->parent_id;
			$template		.= ($is_support_topic) ? '_forum_contrib' : '_forum';
		}

		$email_vars['U_VIEW'] = $post->topic->get_url(false, $topic_params);
		$this->subscriptions->send_notifications(
			$object_type,
			$object_id,
			"$template.txt",
			$email_vars,
			$post->post_user_id
		);
	}

	/**
	 * Get contribution.
	 *
	 * @param int $contrib_id
	 * @return \titania_contribution
	 */
	protected function get_contrib($contrib_id)
	{
		$contrib = $this->contrib;

		// Load the contrib parent if not loaded.
		if (!is_object($contrib) || !$contrib->contrib_id == $contrib_id && $contrib_id)
		{
			$contrib = new \titania_contribution;
			$contrib->load((int) $contrib_id);
		}
		return $contrib;
	}

	/**
	 * Set up message.
	 *
	 * @param \titania_post $post
	 * @param array $auth
	 * @param array $settings
	 */
	protected function setup_message(\titania_post $post, array $auth = array(), array $settings = array())
	{
		$this->message
			->set_parent($post)
			->set_auth(array_merge(array(
				'bbcode'		=> $this->auth->acl_get('u_titania_bbcode'),
				'smilies'		=> $this->auth->acl_get('u_titania_smilies'),
				'attachments'	=> $this->auth->acl_get('u_titania_post_attach'),
			), $auth))
			->set_settings($settings)
		;
	}

	/**
	 * Check whether the topic can be moderated.
	 *
	 * @param int $type			Topic type
	 * @return bool
	 */
	protected function is_topic_moderatable($type)
	{
		return in_array($type, array(TITANIA_QUEUE_DISCUSSION, TITANIA_SUPPORT));
	}
}
