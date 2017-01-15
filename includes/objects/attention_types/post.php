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

use phpbb\titania\ext;

class titania_attention_post extends titania_attention
{
	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	/**
	 * Post object for the source post.
	 *
	 * @var object
	 */
	public $post;

	/**
	 * Contrib object for the parent in which the post resides.
	 *
	 * @var object
	 */
	public $contrib;

	/**
	* {@inheritDoc}
	*/
	public function check_auth()
	{
		return phpbb::$auth->acl_get('u_titania_mod_post_mod');
	}

	/**
	* Set up the post object.
	*/
	public function load_source_object()
	{
		if (!is_object($this->post))
		{
			$this->post = new titania_post();
			$this->post->load($this->attention_object_id);
		}

		return (is_object($this->post)) ? true : false;
	}

	/**
	* Set up the contrib object.
	*/
	public function load_contrib_object()
	{
		if (!is_object($this->contrib))
		{
			$this->load_topic_object();
			$this->contrib = new titania_contribution();
			$this->contrib->load($this->post->topic->parent_id);
		}
	}

	/**
	* Set up the topic object.
	*/
	public function load_topic_object()
	{
		if (!$this->post->topic->topic_id)
		{
			$this->post->topic->topic_id = $this->post->topic_id;
			$this->post->topic->load();
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function get_title()
	{
		return $this->post->post_subject;
	}

	public function get_lang_string($label)
	{
		$labels = array('object' => 'POST');

		switch ((int) $this->attention_type)
		{
			case ext::TITANIA_ATTENTION_REPORTED :
				$labels = array_merge($labels, array(
					'reason'	=> 'REPORTED',
					'closed'	=> 'CLOSED',
					'closed_by'	=> 'CLOSED_BY',
				));
			break;

			case ext::TITANIA_ATTENTION_UNAPPROVED :
				$labels = array_merge($labels, array(
					'reason'	=> 'NEW_UNAPPROVED_POST',
					'closed'	=> 'APPROVED',
					'closed_by'	=> 'APPROVED_BY',
				));
			break;
		}
		return phpbb::$user->lang[$labels[$label]];
	}

	/**
	* Report has been handled. Update report flags for topic and post and close the report.
	*/
	public function report_handled()
	{
		if (!$this->load_source_object() || !$this->is_open())
		{
			return;
		}

		$this->close();

		// Check for any open reports.
		$sql = 'SELECT COUNT(attention_id) AS cnt FROM ' . TITANIA_ATTENTION_TABLE . '
			WHERE attention_object_id = ' . (int) $this->post->post_id . '
				AND attention_object_type = ' . ext::TITANIA_POST . '
				AND attention_close_time = 0';
		phpbb::$db->sql_query($sql);
		$open_reports = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		// If there aren't any open reports, deal with the report flags
		if (!$open_reports)
		{
			$this->post->post_reported = false;
			$this->post->submit();

			$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE . '
				WHERE topic_id = ' . $this->post->topic_id . '
					AND post_reported = 1';
			phpbb::$db->sql_query($sql);
			$cnt = phpbb::$db->sql_fetchfield('cnt');
			phpbb::$db->sql_freeresult();

			if (!$cnt)
			{
				$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
					SET topic_reported = 0
					WHERE topic_id = ' . $this->post->topic_id;
				phpbb::$db->sql_query($sql);
			}
		}

		// Send notification to reporter
		if ($this->notify_reporter)
		{
			$this->notify_reporter_closed();
		}
	}

	/**
	* Disapprove the post/topic
	*/
	public function disapprove($reason_id, $explanation)
	{
		if (!$this->load_source_object() || !$this->is_open() || $this->is_report())
		{
			return false;
		}

		$sql = 'SELECT reason_title, reason_description
			FROM ' . REPORTS_REASONS_TABLE . '
			WHERE reason_id = ' . (int) $reason_id;
		$result = phpbb::$db->sql_query($sql);
		$reason = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$reason || ($reason['reason_title'] == 'other' && utf8_clean_string($explanation) == ''))
		{
			return 'reason_empty';
		}

		if ($reason['reason_title'] != 'other')
		{
			if (isset(phpbb::$user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['reason_title'])]))
			{
				$reason['reason_description'] = phpbb::$user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['reason_title'])];
			}

			// @todo Get description string in poster's language
			$explanation = $reason['reason_description'];
		}

		// Notify poster about disapproval
		$message_vars = array('REASON' => $explanation);
		$this->notify_poster('disapproved', $message_vars);

		// Delete the post
		$this->post->delete();

		return true;
	}

	/**
	* Approve the post/topic.
	*/
	public function approve()
	{
		if (!$this->load_source_object() || !$this->is_open() || $this->is_report())
		{
			return;
		}

		$this->approve_post();

		// We're approving a topic
		if ($this->post->topic->topic_first_post_id == $this->post->post_id)
		{
			$this->approve_topic();
		}

		$this->close();
	}

	/**
	* Approve post.
	*/
	protected function approve_post()
	{
		$this->post->post_approved = 1;

		// Increment the user's postcount if we must
		if (!$this->post->post_deleted && in_array($this->post->post_type, titania::$config->increment_postcount))
		{
			phpbb::update_user_postcount($this->post->post_user_id);
		}

		$this->post->submit();

		// Load z topic
		$this->load_topic_object();

		// Update topics posted table
		$this->post->topic->update_posted_status('add', $this->post->post_user_id);

		// Update first/last post?
		if ($this->post->topic->topic_first_post_time > $this->post->post_time)
		{
			$this->post->topic->sync_first_post();
		}
		if ($this->post->topic->topic_last_post_time < $this->post->post_time)
		{
			$this->post->topic->sync_last_post();
		}

		$this->post->topic->submit();

		// Notify poster of approval.
		$message_vars = array(
			'U_VIEW_TOPIC'	=> $this->path_helper->strip_url_params($this->post->topic->get_url(), 'sid'),
			'U_VIEW_POST'	=> $this->path_helper->strip_url_params($this->post->get_url(), 'sid'),
		);
		$this->notify_poster('approved', $message_vars);

		// Subscriptions?
		if ($this->post->topic->topic_first_post_id != $this->post->post_id && $this->post->topic->topic_last_post_id == $this->post->post_id)
		{
			$u_view = $this->post->topic->get_url(false, array('view' => 'unread', '#' => 'unread'));
			$message_vars = array(
				'U_VIEW' => $this->path_helper->strip_url_params($u_view, 'sid'),
			);
			$object_type = array(ext::TITANIA_TOPIC, ext::TITANIA_SUPPORT);
			$object_id = array($this->post->topic_id, $this->post->topic->parent_id);

			$this->send_notifications($object_type, $object_id, 'subscribe_notify_contrib', $message_vars);
		}
	}

	/**
	* Mark the topic as approved.
	*/
	protected function approve_topic()
	{
		$this->load_topic_object();

		$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
			SET topic_approved = 1
			WHERE topic_id = ' . $this->post->topic_id;
		phpbb::$db->sql_query($sql);

		// Subscriptions
		if ($this->post->topic->topic_last_post_id == $this->post->post_id)
		{
			$message_vars = array('U_VIEW' => $this->post->topic->get_url());

			$this->send_notifications($this->post->post_type, $this->post->topic->parent_id, 'subscribe_notify_forum_contrib', $message_vars);
		}
	}

	/**
	* Send notifications.
	*
	* @param int|array $object_type
	* @param int|array $object_id
	* @param string $email_template
	* @param array $message_vars
	*/
	public function send_notifications($object_type, $object_id, $email_template, $message_vars)
	{
		$this->load_contrib_object();

		$message_vars = array_merge($message_vars, array(
			'NAME'			=> $this->post->topic->topic_subject,
			'CONTRIB_NAME'	=> $this->contrib->contrib_name,
		));

		$this->subscriptions->send_notifications(
			$object_type,
			$object_id,
			$email_template,
			$message_vars,
			$this->post->post_user_id
		);
	}

	/**
	* Notify poster of approval/disapproval
	*
	* @param string $action Action taken - either "approve" or "disapproved"
	* @param array $message_vars Additional variables for email message.
	*/
	protected function notify_poster($action, $message_vars = array())
	{
		if (!$this->load_source_object())
		{
			return;
		}
		$this->load_topic_object();

		// Are we approving/disapproving a topic or post?
		$prefix = ($this->post->post_id == $this->post->topic->topic_first_post_id && $this->post->post_id == $this->post->topic->topic_last_post_id) ? 'topic_' : 'post_';
		// Either post_approved, topic_approved, post_disapproved, or topic_disapproved
		$email_template = $prefix . $action;

		$message_vars = array_merge($message_vars, array(
			'POST_SUBJECT'	=> htmlspecialchars_decode(censor_text($this->post->post_subject)),
			'TOPIC_TITLE'	=> htmlspecialchars_decode(censor_text($this->post->topic->topic_subject)),
		));

		$this->notify_user($this->post->post_user_id, $email_template, $message_vars);
	}

	/**
	* Assign details for the source post.
	*/
	public function assign_source_object_details()
	{
		users_overlord::load_users(array($this->post->post_user_id, $this->post->post_edit_user, $this->post->post_delete_user));
		users_overlord::assign_details($this->post->post_user_id, 'POSTER_', true);
		$this->load_contrib_object();

		$action_param = array('hash' => generate_link_hash('attention_action'));

		phpbb::$template->assign_vars(array(
			'OBJECT_TYPE'		=> $this->get_lang_string('object'),
			'PARENT'			=> $this->contrib->contrib_name,
			'U_PARENT'			=> $this->contrib->get_url(),

			'POST_SUBJECT'		=> censor_text($this->post->post_subject),
			'POST_DATE'			=> phpbb::$user->format_date($this->post->post_time),
			'POST_TEXT'			=> $this->post->generate_text_for_display(),
			'EDITED_MESSAGE'	=> ($this->post->post_edited) ? sprintf(phpbb::$user->lang['EDITED_MESSAGE'], users_overlord::get_user($this->post->post_edit_user, '_full'), phpbb::$user->format_date($this->post->post_edited)) : '',
			'DELETED_MESSAGE'	=> ($this->post->post_deleted != 0) ? sprintf(phpbb::$user->lang['DELETED_MESSAGE'], users_overlord::get_user($this->post->post_delete_user, '_full'), phpbb::$user->format_date($this->post->post_deleted), $this->post->get_url('undelete')) : '',
			'POST_EDIT_REASON'	=> censor_text($this->post->post_edit_reason),

			'U_APPROVE'			=> (!$this->post->post_approved) ? $this->get_report_url('approve', $action_param) : false,
			'U_DISAPPROVE'		=> (!$this->post->post_approved) ? $this->get_report_url('disapprove', $action_param) : false,
			'U_VIEW'			=> $this->post->get_url(),
			'U_EDIT'			=> $this->post->get_url('edit'),

			'SECTION_NAME'		=> '<a href="' . $this->post->get_url() . '">' . censor_text($this->post->post_subject) . '</a> - ' . phpbb::$user->lang['ATTENTION'],
			'S_UNAPPROVED'		=> !$this->post->post_approved,
		));
	}
}
