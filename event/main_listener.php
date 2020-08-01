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

namespace phpbb\titania\event;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\event\data;
use phpbb\auth\auth;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\titania\controller\helper;
use phpbb\titania\ext;
use phpbb\user;
use phpbb\request\request;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\access;
use s9e\TextFormatter\Configurator\Items\TemplateDocument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	/** @var request  */
	protected $request;

	/** @var db_driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var access */
	protected $access;

	/** @var type_collection */
	protected $types;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $ext_root_path;

	/** @var bool */
	protected $in_titania;

	/**
	 * Constructor
	 *
	 * @param request $request
	 * @param db_driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 * @param \phpbb\titania\controller\helper $controller_helper
	 * @param access $access
	 * @param type_collection $types
	 * @param string $phpbb_root_path phpBB root path
	 * @param string $ext_root_path Titania root path
	 * @param string $php_ext PHP file extension
	 */
	public function __construct(request $request, db_driver_interface $db, user $user, template $template, language $language, helper $controller_helper, access $access, type_collection $types, $phpbb_root_path, $ext_root_path, $php_ext)
	{
		$this->request = $request;
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->language = $language;
		$this->controller_helper = $controller_helper;
		$this->access = $access;
		$this->types = $types;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->ext_root_path = $ext_root_path;
		$this->in_titania = false;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'							=> 'add_permissions',
			'kernel.request'							=> array(array('startup', -1)),
			'core.page_header_after'					=> 'overwrite_template_vars',
            'core.text_formatter_s9e_configure_after'	=> 'inject_bbcode_code_lang',

			// Check whether a user is removed from a team
			'core.group_delete_user_after'				=> 'remove_users_from_subscription',

			// Check whether a user is deleted
			'core.acp_users_overview_before'			=> 'user_delete',
			'core.delete_user_before'				=> 'remove_contributions',

			// Include quoted text when private messaging
			'core.ucp_pm_compose_predefined_message'	=> 'quote_text_upon_pm',
		);
	}

	public function add_permissions($event)
	{
		$event['categories'] = array_merge($event['categories'], array(
			'titania'			=> 'ACL_CAT_TITANIA',
			'titania_moderate'	=> 'ACL_CAT_TITANIA_MODERATE',
		));

		$event['permissions'] = array_merge($event['permissions'], array(
			// Common
			'u_titania_contrib_submit'	=> array('lang' => 'ACL_U_TITANIA_CONTRIB_SUBMIT', 'cat' => 'titania'),
			'u_titania_faq_create'		=> array('lang' => 'ACL_U_TITANIA_FAQ_CREATE', 'cat' => 'titania'),
			'u_titania_faq_edit'		=> array('lang' => 'ACL_U_TITANIA_FAQ_EDIT', 'cat' => 'titania'),
			'u_titania_faq_delete'		=> array('lang' => 'ACL_U_TITANIA_FAQ_DELETE', 'cat' => 'titania'),
			'u_titania_rate'			=> array('lang' => 'ACL_U_TITANIA_RATE', 'cat' => 'titania'),
			'u_titania_topic'			=> array('lang' => 'ACL_U_TITANIA_TOPIC', 'cat' => 'titania'),
			'u_titania_post'			=> array('lang' => 'ACL_U_TITANIA_POST', 'cat' => 'titania'),
			'u_titania_post_approved'	=> array('lang' => 'ACL_U_TITANIA_POST_APPROVED', 'cat' => 'titania'),
			'u_titania_post_edit_own'	=> array('lang' => 'ACL_U_TITANIA_POST_EDIT_OWN', 'cat' => 'titania'),
			'u_titania_post_delete_own'	=> array('lang' => 'ACL_U_TITANIA_POST_DELETE_OWN', 'cat' => 'titania'),
			'u_titania_post_mod_own'	=> array('lang' => 'ACL_U_TITANIA_POST_MOD_OWN', 'cat' => 'titania'),
			'u_titania_post_attach'		=> array('lang' => 'ACL_U_TITANIA_POST_ATTACH', 'cat' => 'titania'),
			'u_titania_bbcode'			=> array('lang' => 'ACL_U_TITANIA_BBCODE', 'cat' => 'titania'),
			'u_titania_smilies'			=> array('lang' => 'ACL_U_TITANIA_SMILIES', 'cat' => 'titania'),

			'u_titania_post_hard_delete' => array('lang' => 'ACL_U_TITANIA_POST_HARD_DELETE', 'cat' => 'titania'),

			// Moderation
			'u_titania_mod_author_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_AUTHOR_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_contrib_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_CONTRIB_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_faq_mod'		=> array('lang' => 'ACL_U_TITANIA_MOD_FAQ_MOD', 'cat' => 'titania_moderate'),
			'u_titania_mod_rate_reset'	=> array('lang' => 'ACL_U_TITANIA_MOD_RATE_RESET', 'cat' => 'titania_moderate'),
			'u_titania_mod_post_mod'	=> array('lang' => 'ACL_U_TITANIA_MOD_POST_MOD', 'cat' => 'titania_moderate'),

			'u_titania_mod_style_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_MODERATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_style_clr'				=> array('lang' => 'ACL_U_TITANIA_MOD_STYLE_CLR', 'cat' => 'titania_moderate'),

			'u_titania_mod_modification_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_modification_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_MODIFICATION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_translation_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_translation_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_TRANSLATION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_converter_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_converter_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_CONVERTER_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_bbcode_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_queue'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bbcode_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BBCODE_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_bridge_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_queue'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_bridge_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_BRIDGE_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_official_tool_moderate' => array('lang' => 'ACL_U_TITANIA_MOD_OFFICIAL_TOOL_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_mod_extension_queue_discussion'	=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_QUEUE_DISCUSSION', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_queue'				=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_QUEUE', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_validate'			=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_VALIDATE', 'cat' => 'titania_moderate'),
			'u_titania_mod_extension_moderate'			=> array('lang' => 'ACL_U_TITANIA_MOD_EXTENSION_MODERATE', 'cat' => 'titania_moderate'),

			'u_titania_admin' => array('lang' => 'ACL_U_TITANIA_ADMIN', 'cat' => 'titania_moderate'),
		));
	}

	public function startup($event)
	{
		if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST ||
			strpos($event->getRequest()->attributes->get('_controller'), 'phpbb.titania') !== 0)
		{
			return;
		}
		$this->in_titania = true;

		require($this->ext_root_path . 'common.' . $this->php_ext);
	}

	public function overwrite_template_vars($event)
	{
		if ($this->user->data['is_registered'] && !$this->user->data['is_bot'])
		{
			$this->user->add_lang_ext('phpbb/titania', 'common');

			$u_my_contribs = $this->controller_helper->route('phpbb.titania.author', array(
				'author'	=> urlencode($this->user->data['username_clean']),
				'page'		=> 'contributions',
			));

			$this->template->assign_vars(array(
				'U_MY_CONTRIBUTIONS'		=> $this->controller_helper->get_real_url($u_my_contribs),
			));
		}

		if (!$this->in_titania)
		{
			return;
		}

		$this->template->assign_vars(array(
			'U_FAQ'		=> $this->controller_helper->route('phpbb.titania.faq'),
			'U_SEARCH'	=> $this->controller_helper->route('phpbb.titania.search'),
			'S_BODY_CLASS'	=> 'customisation-database',
		));

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$this->template->assign_vars(array(
				'U_LOGIN_LOGOUT'	=> append_sid(
					"{$this->phpbb_root_path}ucp.{$this->php_ext}",
					array(
						'mode'		=> 'login',
						'redirect'	=> urlencode($this->controller_helper->get_current_url()),
					)
				),
			));
		}
	}

    /**
     * Reads the value for the lang attribute passed to each code BBCode, e.g. [code=diff] or [code lang=diff],
     * and adds it as a class attribute to the code element, e.g. <code class="diff">
     *
     * @param data $event
     */
    public function inject_bbcode_code_lang(data $event)
    {
        $tag = $event['configurator']->tags['CODE'];

        /** @var TemplateDocument $dom */
        $dom = $tag->template->asDOM();

        foreach ($dom->getElementsByTagName('code') as $code)
        {
        	/** @var \DOMElement $code */
			$code->setAttribute('class', '{@lang}');
		}

        $dom->saveChanges();
    }

	/**
	 * Remove users from queue subscription if they are removed from a team and subsequently lose all queue permissions
	 * @param $event
	 */
	public function remove_users_from_subscription($event)
	{
		if (!defined('TITANIA_WATCH_TABLE'))
		{
			// Include Titania so we can access the constants
			require($this->ext_root_path . 'common.' . $this->php_ext);
		}

		$queue_permissions = array(
			'u_titania_mod_extension_queue',
			'u_titania_mod_bridge_queue',
			'u_titania_mod_bbcode_queue',
			'u_titania_mod_converter_queue',
			'u_titania_mod_translation_queue',
			'u_titania_mod_modification_queue',
			'u_titania_mod_style_queue'
		);

		$remove_user_ids = array();

		foreach ($event['user_id_ary'] as $user_id)
		{
			// For every user removed from the group, check if they should still receive emails
			$auth = new auth();

			$user_data = $auth->obtain_user_data($user_id);
			$auth->acl($user_data);

			$has_queue_access = false;

			foreach ($queue_permissions as $permission)
			{
				// If the user has access to any of the private queues, then it can be assumed they
				// will still be eligible to receive emails.
				if ($auth->acl_get($permission))
				{
					$has_queue_access = true;
					break;
				}
			}

			if (!$has_queue_access)
			{
				// Remove queue subscriptions
				$remove_user_ids[] = (int) $user_id;
			}
		}

		// Only continue if there are users to remove from the Titania queue subscriptions
		if (count($remove_user_ids))
		{
			// Remove subscription
			$sql = 'DELETE FROM ' . TITANIA_WATCH_TABLE . '
					WHERE ' . $this->db->sql_in_set('watch_user_id', $remove_user_ids) . '
						AND watch_object_type = ' . ext::TITANIA_QUEUE;

			$this->db->sql_query($sql);
		}
	}

	/**
	 * When a user sends a PM from a Titania support topic, include the post as quoted text
	 * @param $event
	 */
	public function quote_text_upon_pm($event)
	{
		if (!defined('TITANIA_POSTS_TABLE'))
		{
			// Include Titania so we can access the constants
			require($this->ext_root_path . 'common.' . $this->php_ext);
		}

		// Check that this user has access to the Titania support post ID so that people can't just randomly
		// stick an ID in the URL and see the post contents
		$titania_post_id = $this->request->variable('titania_msg_id', 0);
		$access = $this->access;

		$sql_ary = array(
			'SELECT' => 'p.post_id, p.post_text, p.post_subject, p.post_time, p.post_access, t.topic_access, 
				t.topic_id, t.parent_id, u.user_id, u.username, c.contrib_name_clean, c.contrib_type',

			'FROM' => array(
				TITANIA_POSTS_TABLE => 'p',
				TITANIA_TOPICS_TABLE => 't',
				TITANIA_CONTRIBS_TABLE => 'c',
				USERS_TABLE => 'u',
			),

			'WHERE' => 'p.post_approved = 1
						AND t.topic_approved = 1
						AND t.topic_type = ' . ext::TITANIA_SUPPORT . '
						AND p.post_id = ' . $titania_post_id . ' 
						AND t.topic_id = p.topic_id
						AND u.user_id = p.post_user_id
						AND t.parent_id = c.contrib_id',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);

		$contrib = new \titania_contribution;

		if ($row && $contrib->load((int) $row['parent_id'], $row['contrib_type']) && $contrib->is_visible())
		{
			if ($this->access->is_public() && ($contrib->is_active_coauthor || $contrib->is_author))
			{
				// If the current user is the author of the contribution they are trying to quote/PM from
				// then set their user level to author
				$this->access->set_level(access::AUTHOR_LEVEL);
			}

			// Now do a check to make sure their access level is sufficient.
			// This is to make sure authors can't see posts that are teams-only, and that the public can't see
			// any posts that are author-only.
			if ($row['post_access'] >= $access->get_level() && $row['topic_access'] >= $access->get_level())
			{
				// Add the subject of the Titania post to the PM
				$event['message_subject'] = $row['post_subject'];

				// If "Re:" isn't already on the subject line, add it for the private message
				if (strpos($event['message_subject'], 'Re: ') !== 0)
				{
					$event['message_subject'] = 'Re: ' . $event['message_subject'];
				}

				// Add the quote to the message
				// Format like: /foo/bar?p=123#p123
				$post_url = sprintf('%1$s?p=%2$d#p%2$d', $this->controller_helper->route('phpbb.titania.contrib.support.topic', array(
					'contrib_type' => $this->types->get($row['contrib_type'])->url,
					'contrib' => $row['contrib_name_clean'],
					'topic_id' => $row['topic_id']
				)), (int) $row['post_id']);

				$post_url = sprintf('[url=%s]%s[/url]', $post_url, $this->user->lang('FWD_SUBJECT', $row['post_subject']));

				// No indenting here because it would appear in the send PM box
				$event['message_text'] = sprintf('%s
[quote=%s time=%d user_id=%d]%s[/quote]', $post_url, $row['username'], $row['post_time'], $row['user_id'], $row['post_text']);
			}
		}
	}

	/**
	 * Append our message to inform the admin that deleting the user will also delete things in Titania
	 * @param $event
	 */
	public function user_delete($event)
	{
		// Add our message to the end of the existing one
		$this->language->add_lang('info_acp_titania', 'phpbb/titania');
		$stored_lang = $this->user->lang;
		$stored_lang['CONFIRM_OPERATION'] .= ' ' . $stored_lang['CONFIRM_DELETE_USER_OPERATION'];
		$this->user->lang = $stored_lang;
	}

	/**
	 * Automatically remove topics, posts and contributions when user is deleted
	 * @param $event
	 */
	public function remove_contributions($event)
	{
		if (!defined('TITANIA_CONTRIBS_TABLE'))
		{
			// Include Titania so we can access the constants
			require($this->ext_root_path . 'common.' . $this->php_ext);
		}

		$event_data = $event->get_data();

		// Get rid of the contribs that only have unapproved revisions.
		// We'll keep anything that has an approved revision
		$this->delete_user_contribs_with_unapproved_revisions($event_data);

		// Delete Titania topics and posts by the user(s)
		if ($event_data['mode'] === 'remove')
		{
			// Handle the topics
			$this->delete_user_titania_topics($event_data);

			// Handle the posts
			$this->delete_user_titania_posts($event_data);
		}

// TODO: remove debug code
die('-- STOP HERE --');
	}

	/**
	 * Delete contributions by the selected users that only contain unapproved revisions
	 * @param $event_data
	 */
	private function delete_user_contribs_with_unapproved_revisions($event_data)
	{
		// Delete contributions without any approved revisions
		$sql_array = array(
			'SELECT'	=> 'c.contrib_id, COUNT(r.revision_id) AS approved_revisions',
			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE => 'c',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TITANIA_REVISIONS_TABLE => 'r'),
					'ON'	=> 'c.contrib_id = r.contrib_id AND revision_status = ' . ext::TITANIA_REVISION_APPROVED,
				),
			),

			'WHERE'		=>  $this->db->sql_in_set('c.contrib_user_id', $event_data['user_ids']),

			'GROUP_BY'	=> 'c.contrib_id',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$contrib_id = (int) $row['contrib_id'];
			$approved_revisions = (int) $row['approved_revisions'];

			// If there are no approved revisions, then delete the contribution
			if ($approved_revisions === 0)
			{
				$contrib = new \titania_contribution;

				if ($contrib->load($contrib_id))
				{
					// Delete now
					$contrib->delete();
				}
			}

// TODO: remove debug code
echo $contrib_id . ': '. $approved_revisions . '<br />';
		}

		$this->db->sql_freeresult($result);
	}

	/**
	 * Delete Titania topics by the selected users
	 * @param $event_data
	 */
	private function delete_user_titania_topics($event_data)
	{
		// Delete topics by the user in Titania
		$topic = new \titania_topic();

		$sql = 'SELECT * FROM ' . TITANIA_TOPICS_TABLE . '
				WHERE ' . $this->db->sql_in_set('topic_first_post_user_id', $event_data['user_ids']);

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Delete each topic created by the to-be deleted user
			$topic->__set_array($row);
			$topic->delete();
		}

		$this->db->sql_freeresult($result);
	}

	/**
	 * Delete Titania posts by the selected users
	 * @param $event_data
	 */
	private function delete_user_titania_posts($event_data)
	{
		// Delete posts by the user in Titania
		$post = new \titania_post();

		$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . '
				WHERE ' . $this->db->sql_in_set('post_user_id', $event_data['user_ids']);

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$post->__set_array($row);
			$post->delete();
		}

		$this->db->sql_freeresult($result);
	}
}
