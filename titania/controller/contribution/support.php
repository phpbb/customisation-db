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

class support extends base
{
	/**
	* Handle topic action.
	*
	* @param string $contrib_type	Contrib type URL identifier.
	* @param string $contrib		Contrib name clean.
	* @param int $topic_id			Topic id.
	* @param string $action			Action.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function topic_action($contrib_type, $contrib, $topic_id, $action)
	{
		$this->load_contrib($contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		// Handle replying/editing/etc
		$posting_helper = new \titania_posting();
		$posting_helper->parent_type = $this->contrib->contrib_type;

		$result = $posting_helper->act(
			$this->contrib,
			$action,
			$topic_id,
			'contributions/contribution_support_post.html',
			$this->contrib->contrib_id,
			array(
				'contrib_type'	=> $this->contrib->type->url,
				'contrib'		=> $this->contrib->contrib_name_clean,
			),
			TITANIA_SUPPORT,
			$this->helper->get_current_url()
		);

		$this->assign_vars();

		$template_file = (!empty($result['template'])) ? $result['template'] : 'contributions/contribution_support_post.html';

		return $this->helper->render($template_file, $result['title']);
	}

	/**
	* Display topic.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	* @param int $topic_id				Topic id.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_topic($contrib_type, $contrib, $topic_id)
	{
		// Load the contrib item
		$this->load_contrib($contrib);
		$this->load_topic($topic_id);

		if (!$this->check_auth(true))
		{
			return $this->helper->needs_auth();
		}

		$this->user->add_lang('viewforum');

		// Subscriptions
		\titania_subscriptions::handle_subscriptions(TITANIA_TOPIC, $topic_id, $this->topic->get_url(), 'SUBSCRIBE_TOPIC');
		\posts_overlord::display_topic_complete($this->topic);

		$this->template->assign_vars(array(
			'U_CANONICAL'	=> $this->topic->get_url(),
			'U_POST_REPLY'	=> ($this->auth->acl_get('u_titania_post')) ? $this->topic->get_url('reply') : '',
		));
		$this->assign_vars();

		return $this->helper->render(
			'contributions/contribution_support.html',
			censor_text($this->topic->topic_subject) . ' - ' . $this->contrib->contrib_name
		);
	}

	/**
	* Display support page.
	*
	* @param string $contrib_type	Contrib type URL identifier.
	* @param string $contrib		Contrib name clean.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_support($contrib_type, $contrib)
	{
		$this->load_contrib($contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$this->user->add_lang('viewforum');

		// Subscriptions
		\titania_subscriptions::handle_subscriptions(
			TITANIA_SUPPORT,
			$this->contrib->contrib_id,
			$this->contrib->get_url('support'),
			'SUBSCRIBE_SUPPORT'
		);

		// Mark all topics read
		if ($this->request->variable('mark', '') == 'topics')
		{
			\titania_tracking::track(TITANIA_SUPPORT, $this->contrib->contrib_id);
		}

		$can_post_topic = $this->ext_config->support_in_titania && $this->auth->acl_get('u_titania_topic');
		$data = \topics_overlord::display_forums_complete('support', $this->contrib);
		$data['sort']->set_url($this->contrib->get_url('support'));

		$this->template->assign_vars(array(
			'U_POST_TOPIC'			=> ($can_post_topic) ? $this->contrib->get_url('posting') : '',
			// Mark all topics read
			'U_MARK_TOPICS'			=> $this->contrib->get_url('support', array('mark' => 'topics')),

			// Canonical URL
			'U_CANONICAL'			=> $data['sort']->build_canonical(),

			'S_DISPLAY_SEARCHBOX'	=> true,
			'S_SEARCHBOX_ACTION'	=> $this->helper->route('phpbb.titania.search', array(
				'type'		=> TITANIA_SUPPORT,
				'contrib'	=> $this->contrib->contrib_id,
			)),
		));
		$this->assign_vars();

		return $this->helper->render(
			'contributions/contribution_support.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['CONTRIB_SUPPORT']
		);
	}

	/**
	* Load topic object.
	*
	* @param int $id		Topic id.
	* @throws \Exception	Throws exception if no topic found.
	* @return null
	*/
	protected function load_topic($id)
	{
		\topics_overlord::load_topic($id, true);
		$this->topic = \topics_overlord::get_topic_object($id);

		if ($this->topic === false || $this->topic->parent_id !== $this->contrib->contrib_id)
		{
			throw new \Exception($this->user->lang['NO_TOPIC']);
		}
	}

	/**
	* Check user's authorization.
	*
	* @param bool $topic		Additionally check topic auth.
	* @return bool
	*/
	protected function check_auth($topic = false)
	{
		if (!$this->ext_config->support_in_titania && \titania::$access_level == TITANIA_ACCESS_PUBLIC)
		{
			return false;
		}

		if ($topic)
		{
			$can_view_queue_discussion = $this->is_author && $this->contrib->type->acl_get('queue_discussion');

			if ($this->topic->topic_access < \titania::$access_level ||
				($this->topic->topic_type == TITANIA_QUEUE_DISCUSSION && !$can_view_queue_discussion))
			{
				return false;
			}
		}

		return true;
	}

	/**
	* Assign common variables.
	*
	* @return null
	*/
	protected function assign_vars()
	{
		$this->contrib->assign_details(true);
		$this->display->assign_global_vars();
		$this->generate_navigation('support');
		$this->generate_breadcrumbs();
	}
}
