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

namespace phpbb\titania\controller\manage\queue;

class item extends \phpbb\titania\controller\manage\base
{
	/** @var \phpbb\titania\tags */
	protected $tags;

	/** @var \phpbb\titania\message\message */
	protected $message;

	/** @var \phpbb\titania\posting */
	protected $posting;

	/** @var int */
	protected $id;

	/** @var \titania_queue */
	protected $queue;

	/** @var \titania_contribution */
	protected $contrib;

	/** @var \titania_revision */
	protected $revision;

	/** @var bool */
	protected $is_author;

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
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\tags $tags
	 * @param \phpbb\titania\message\message $message
	 * @param \phpbb\titania\posting $posting
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\tags $tags, \phpbb\titania\message\message $message, \phpbb\titania\posting $posting)
	{
		parent::__construct($auth, $config, $db, $template, $user, $cache, $helper, $request, $ext_config, $display);

		$this->tags = $tags;
		$this->message = $message;
		$this->posting = $posting;
	}

	/**
	* Display queue item.
	*
	* @param int $id		Queue item id.
	* @return \Symfony\Component\HttpFoundation\Response
	*/	
	public function display_item($id)
	{
		$this->load_item($id);

		// Check auth
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		// Display the main queue item
		$data = \queue_overlord::display_queue_item($this->id);

		// Display the posts in the queue (after the posting helper acts)
		\posts_overlord::display_topic_complete($data['topic']);

		$this->display->assign_global_vars();
		$this->generate_navigation('queue');

		$tag = $this->request->variable('tag', 0);

		if ($tag)
		{
			// Add tag to Breadcrumbs
			$this->display->generate_breadcrumbs(array(
				$this->tags->get_tag_name($tag)	=> $this->queue->get_url(false, array('tag' => $tag)),
			));
		}

		return $this->helper->render('manage/queue.html', \queue_overlord::$queue[$this->id]['topic_subject']);
	}

	/**
	* Delegates requested action to appropriate method.
	*
	* @param int $id		Queue item id.
	* @param string $action	Requested action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function item_action($id, $action)
	{
		$this->load_item($id);

		if (!$this->check_auth($action))
		{
			return $this->helper->needs_auth();
		}

		$this->display->assign_global_vars();
		$this->generate_navigation('queue');

		$valid_action = false;
		$check_actions = array(
			'in_progress',
			'no_progress',
			'tested',
			'not_tested',
			'rebuild',
		);

		if (in_array($action, $check_actions) && !check_link_hash($this->request->variable('hash', ''), 'queue_action'))
		{
			return $this->helper->error('PAGE_REQUEST_INVALID');
		}

		// Only allow these actions to run if the queue item is still open.
		if ($this->queue->queue_status > 0)
		{
			$valid_action = true;

			switch ($action)
			{
				case 'in_progress':
					$this->queue->in_progress();
				break;

				case 'no_progress':
					$this->queue->no_progress();
				break;

				case 'tested':
					$this->queue->change_tested_mark(true);
				break;

				case 'not_tested':
					$this->queue->change_tested_mark(false);
				break;

				case 'move':
					$this->move();
				break;

				case 'allow_author_repack' :
					return $this->allow_author_repack();
				break;

				case 'approve':
					return $this->approve();
				break;

				case 'deny':
					return $this->deny();
				break;

				default:
					$valid_action = false;
			}
		}

		switch ($action)
		{
			case 'rebuild':
				$this->queue->update_first_queue_post();
			break;

			case 'reply':
			case 'quote':
			case 'edit':
			case 'quick_edit':
			case 'delete':
			case 'undelete':
				return $this->posting($action);
			break;

			default:
				if (!$valid_action)
				{
					return $this->helper->error('INVALID_ACTION', 404);
				}
		}

		redirect($this->queue->get_url());
	}

	/**
	* Load queue item.
	*
	* @param int $id		Queue item id.
	* @throws \Exception	Throws exception if no item found.
	* @return null
	*/
	protected function load_item($id)
	{
		$this->id = (int) $id;
		$this->queue = \queue_overlord::get_queue_object($this->id, true);

		if (!$this->queue)
		{
			throw new \Exception($this->user->lang['NO_QUEUE_ITEM']);
		}

		$this->contrib = \contribs_overlord::get_contrib_object($this->queue->contrib_id, true);
		$this->revision = $this->queue->get_revision();
		$this->is_author = $this->contrib->is_author || $this->contrib->is_active_coauthor || $this->contrib->is_coauthor;
	}

	/**
	* Check user's authorization.
	*
	* @param bool|string	Optional action to check auth for.
	* @return bool Returns true if user is authorized, false otherwise.
	*/
	protected function check_auth($action = false)
	{
		if (!$this->contrib->type->acl_get('view'))
		{
			return false;
		}

		switch ($action)
		{
			case 'approve':
				// Do not allow to approve your own contributions, except for founders...
				if (!$this->ext_config->allow_self_validation && $this->user->data['user_type'] != USER_FOUNDER && $this->is_author)
				{
					return false;
				}

			case 'allow_author_repack':
			case 'deny' :
				return $this->contrib->type->acl_get('validate');
			break;

			default:
				return true;
		}
	}

	/**
	 * Posting action.
	 *
	 * @param string $action
	 * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	protected function posting($action)
	{
		$this->posting->parent_type = $this->queue->queue_type;

		return $this->posting->act(
			$this->contrib,
			$action,
			$this->queue->queue_topic_id,
			'manage/queue_post.html',
			$this->id,
			false,
			TITANIA_QUEUE,
			$this->helper->get_current_url()
		);
	}

	/**
	* Allow author to repack revision action.
	*
	* @return null
	*/
	protected function allow_author_repack()
	{
		$topic = $this->queue->get_queue_discussion_topic();
		$post = new \titania_post(TITANIA_QUEUE_DISCUSSION, $topic);
		$post->__set_array(array(
			'post_subject'		=> 'Re: ' . $post->topic->topic_subject,
		));

		// Load the message object
		$message_object = $this->get_message($post);

		// Submit check...handles running $post->post_data() if required
		$submit = $message_object->submit_check();

		if ($submit)
		{
			$this->queue->allow_author_repack = true;
			$repack_url = $this->contrib->get_url('revision', array(
				'page'	=> 'repack',
				'id' 	=> $this->queue->revision_id,
			));

			$for_edit = $post->generate_text_for_edit();
			$post->post_text = $for_edit['message'] . "\n\n
				[url=" . $repack_url . ']' .
				$this->user->lang['AUTHOR_REPACK_LINK'] . '[/url]';
			$post->generate_text_for_storage($for_edit['allow_bbcode'], $for_edit['allow_smilies'], $for_edit['allow_urls']);
			$post->submit();

			$this->queue->submit();

			$this->queue->topic_reply('QUEUE_REPLY_ALLOW_REPACK');	
			$this->queue->submit();

			redirect($this->queue->get_url());
		}

		$message_object->display();

		// Common stuff
		$this->template->assign_vars(array(
			'S_POST_ACTION'		=> $this->helper->get_current_url(),
			'L_POST_A'			=> $this->user->lang['DISCUSSION_REPLY_MESSAGE'],
		));

		return $this->helper->render('manage/queue_post.html', 'DISCUSSION_REPLY_MESSAGE');
	}

	/**
	* Move action.
	*
	* @return null
	*/
	protected function move()
	{
		$tags = $this->cache->get_tags(TITANIA_QUEUE);

		if (check_link_hash($this->request->variable('hash', ''), 'quick_actions') || confirm_box(true))
		{
			$new_tag = $this->request->variable('id', 0);

			if (!isset($tags[$new_tag]))
			{
				return $this->helper->error('NO_TAG');
			}

			$this->queue->move($new_tag, $this->tags);
		}
		else
		{
			// Generate the list of tags we can move it to
			$extra = '<select name="id">';
			foreach ($tags as $tag_id => $row)
			{
				$extra .= '<option value="' . $tag_id . '">' . $this->user->lang($row['tag_field_name']) . '</option>';
			}
			$extra .= '</select>';
			$this->template->assign_var('CONFIRM_EXTRA', $extra);

			confirm_box(false, 'MOVE_QUEUE');
		}
	}

	/**
	* Approve action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function approve()
	{
		$public_notes = $this->request->variable('public_notes', '', true);

		if ($this->validate('approve'))
		{
			$this->queue->approve($public_notes);

			// Reload contribution with new data.
			$this->contrib->load();
			$this->contrib->type->approve($this->contrib, $this->queue);
			$this->cache->destroy('sql', TITANIA_CONTRIBS_TABLE);
			redirect($this->queue->get_url());
		}

		if ($this->contrib->type->update_public)
		{
			$this->template->assign_vars(array(
				'PUBLIC_MESSAGE'			=> $public_notes,
				'S_PUBLIC_NOTES'			=> true,
			));

			if ($this->request->is_set_post('preview'))
			{
				$this->template->assign_vars(array(
					'PUBLIC_PREVIEW_SUBJECT'	=> 'Re: ' . $this->contrib->contrib_name,
					'PUBLIC_PREVIEW_MESSAGE'	=> $this->get_public_notes_preview($public_notes),
				));
			}
		}

		return $this->helper->render(
			'manage/queue_validate.html',
			$this->user->lang['APPROVE_QUEUE'] . ' - ' . $this->contrib->contrib_name
		);
	}

	/**
	* Deny action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function deny()
	{
		if ($this->validate('deny'))
		{
			$this->queue->deny();
			$this->contrib->type->deny($this->contrib, $this->queue);
			redirect($this->queue->get_url());
		}

		return $this->helper->render(
			'manage/queue_validate.html',
			$this->user->lang['DENY_QUEUE'] . ' - ' . $this->contrib->contrib_name
		);
	}

	/**
	* Common approval/denial message handler.
	*
	* @param string $action			Action: approve|deny
	* @return bool Returns true if message was submmited properly.
	*/
	protected function validate($action)
	{
		$this->queue->message_fields_prefix = 'message_validation';
		$message = $this->get_message($this->queue);
		$error = array();

		if ($message->submit_check())
		{
			// Check form key
			if (($form_key_error = $message->validate_form_key()) !== false)
			{
				$error[] = $form_key_error;
			}

			if (empty($error))
			{
				return true;
			}
		}

		$message->display();
		$this->contrib->type->display_validation_options($action);
		$this->display_topic_review();

		$this->template->assign_vars(array(
			'ERROR'						=> implode('<br />', $error),
			'L_TOPIC_REVIEW'			=> $this->user->lang['QUEUE_REVIEW'],
			'TOPIC_TITLE'				=> $this->contrib->contrib_name,
			'PAGE_TITLE_EXPLAIN'		=> $this->user->lang[strtoupper($action) . '_QUEUE_CONFIRM'],
			'S_CONFIRM_ACTION'			=> $this->queue->get_url($action),
		));

		return false;
	}

	/**
	* Get public notes preview.
	*
	* @param string $notes
	* @return string
	*/
	protected function get_public_notes_preview($notes)
	{
		$preview = $this->user->lang(
			$this->contrib->type->update_public,
			$this->revision->revision_version
		);

		if ($notes)
		{
			$preview .= $this->user->lang($this->contrib->type->update_public . '_NOTES', $notes);
		}
		$uid = $bitfield = $options = false;
		generate_text_for_storage($preview, $uid, $bitfield, $options, true, true, true);
		return generate_text_for_display($preview, $uid, $bitfield, $options);
	}

	/**
	* Get message object.
	*
	* @param mixed $object		Parent object receiving the message.
	* @return \phpbb\titania\message\message
	*/
	protected function get_message($object)
	{
		$this->message
			->set_parent($object)
			->set_auth(array(
				'bbcode'		=> $this->auth->acl_get('u_titania_bbcode'),
				'smilies'		=> $this->auth->acl_get('u_titania_smilies'),
			))
			->set_settings(array(
				'display_subject'	=> false,
			))
		;

		return $this->message;
	}

	/**
	* Display queue topic review.
	*
	* @return null
	*/
	protected function display_topic_review()
	{
		// Setup the sort tool
		$topic_sort = \posts_overlord::build_sort();
		$topic_sort->set_defaults(false, false, 'd');

		// Load the topic
		$topic = new \titania_topic;
		$topic->load($this->queue->queue_topic_id);

		// Display the posts for review
		\posts_overlord::display_topic($topic, $topic_sort);
	}
}
