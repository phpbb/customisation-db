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

use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\ext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class contribution extends base
{
	/** @var \phpbb\titania\tracking */
	protected $tracking;

	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\controller\helper $helper
	 * @param type_collection $types
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\access $access
	 * @param \phpbb\titania\tracking $tracking
	 * @param \phpbb\titania\subscriptions $subscriptions
	 * @param \phpbb\path_helper $path_helper
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request $request, \phpbb\titania\cache\service $cache, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\access $access, \phpbb\titania\tracking $tracking, \phpbb\titania\subscriptions $subscriptions, \phpbb\path_helper $path_helper)
	{
		parent::__construct($auth, $config, $db, $template, $user, $helper, $types, $request, $cache, $ext_config, $display, $access);

		$this->tracking = $tracking;
		$this->subscriptions = $subscriptions;
		$this->path_helper = $path_helper;
	}

	/**
	* Delegates requested page to appropriate method.
	*
	* @param string $contrib_type	Contrib type URL identifier.
	* @param string $contrib		Contrib name clean.
	* @param string $page			Requested page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function base($contrib_type, $contrib, $page)
	{
		$this->load_contrib($contrib_type, $contrib);

		$page = ($page) ?: 'details';

		if (!in_array($page, array('report', 'details', 'queue_discussion', 'rate', 'feed')))
		{
			return $this->helper->error('NO_PAGE', 404);
		}

		$this->display->assign_global_vars();
		$this->generate_navigation($page);
		$this->generate_breadcrumbs();

		return $this->{$page}();
	}

	/**
	 * Feed for the contribution revisions (new releases)
	 * @return Response
	 * @throws \Exception
	 */
	protected function feed()
	{
		if (!$this->config['feed_overall'])
		{
			// Don't proceed if feeds are disabled
			trigger_error('NO_FEED_ENABLED');
		}

		$sql = 'SELECT r.*, c.*, u.username_clean
 			FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_CONTRIBS_TABLE . ' c, ' . USERS_TABLE . ' u
			WHERE r.contrib_id = ' . (int) $this->contrib->contrib_id . '
			AND r.revision_status = ' . ext::TITANIA_REVISION_APPROVED . '
				AND r.revision_submitted = 1
				AND c.contrib_status = ' . ext::TITANIA_CONTRIB_APPROVED . '
				AND r.contrib_id = c.contrib_id
				AND u.user_id = c.contrib_user_id
			ORDER BY r.validation_date DESC';

		$result = $this->db->sql_query($sql);

		$rows = [];
		$feed_updated_time = false;

		while ($row = $this->db->sql_fetchrow($result))
		{
			$feed_rows = [];
			$feed_rows['item_date'] = date(\DateTime::ATOM, $row['validation_date']);

			// Get the most recent time
			if (!$feed_updated_time)
			{
				$feed_updated_time = $row['validation_date'];
			}

			// Make the name including the version
			$feed_rows['item_title'] = $row['contrib_name'] . ' ' . $row['revision_version'];

			if ($row['revision_name'])
			{
				// Include the code name if it's supplied
				$feed_rows['item_title'] .= ' (' . $row['revision_name'] . ')';
			}

			$feed_rows['item_author'] = $row['username_clean'];
			$feed_rows['item_description'] = $this->user->lang('FEED_NEW_VERSION', $row['revision_version'], $row['contrib_name']);

			// Download link; strip the session id out
			$feed_rows['item_link'] = ($row['attachment_id']) ? $this->path_helper->strip_url_params($this->helper->route('phpbb.titania.download', array('id' => $row['attachment_id'])), 'sid') : '';

			$rows[] = $feed_rows;
		}

		$this->db->sql_freeresult($result);

		// Generic feed information
		$this->template->assign_vars(array(
			'SELF_LINK'				=> $this->contrib->get_url('feed'),
			'FEED_LINK'				=> $this->contrib->get_url(),
			'FEED_TITLE'			=> $this->user->lang('FEED_CDB', $this->config['sitename'], $this->contrib->contrib_name),
			'FEED_SUBTITLE'			=> $this->config['site_desc'],
			'FEED_UPDATED'			=> date(\DateTime::ATOM),
			'FEED_LANG'				=> $this->user->lang('USER_LANG'),
			'FEED_AUTHOR'			=> $this->config['sitename'],
		));

		$this->template->assign_block_vars_array('feed', $rows);

		// Put it in our custom xml
		$content = $this->helper->render('feed.xml.twig');

		// Return the response
		$response = $content;
		$response->headers->set('Content-Type', 'application/atom+xml');
		$response->setCharset('UTF-8');
		$response->setLastModified(new \DateTime('@' . $feed_updated_time));

		if (!empty($this->user->data['is_bot']))
		{
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}

	/**
	* Report page.
	*
	* @return null
	*/
	protected function report()
	{
		// Check permissions
		if (!$this->user->data['is_registered'])
		{
			return $this->helper->needs_auth();
		}

		$this->user->add_lang_ext('phpbb/titania', 'posting');
		$this->user->add_lang('mcp');

		if ($this->request->is_set_post('cancel'))
		{
			return new RedirectResponse($this->contrib->get_url());
		}
		else if ($this->request->is_set_post('confirm') && check_form_key('report'))
		{
			$message = $this->request->variable('report_text', '', true);
			$notify_reporter = $this->request->variable('notify', false);
			$this->contrib->report($message, $notify_reporter);

			return new RedirectResponse($this->contrib->get_url());
		}

		add_form_key('report');
		$this->template->assign_vars(array(
			'S_CAN_NOTIFY'	=> true,
			'MESSAGE_TEXT'	=> $this->user->lang('REPORT_CONTRIBUTION_CONFIRM')
		));

		return $this->helper->render(
			'posting/report_body.html',
			'REPORT_CONTRIBUTION'
		);
	}

	/**
	* Details page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function details()
	{
		$this->contrib->get_download();
		$this->contrib->get_revisions();
		$this->contrib->get_screenshots();
		$this->contrib->get_rating();

		$this->contrib->assign_details();

		if (!$this->user->data['is_bot'])
		{
			$this->contrib->increase_view_counter();
		}

		// Set tracking
		$this->tracking->track(ext::TITANIA_CONTRIB, $this->contrib->contrib_id);

		// Subscriptions
		$this->subscriptions->handle_subscriptions(
			ext::TITANIA_CONTRIB,
			$this->contrib->contrib_id,
			$this->contrib->get_url(),
			'SUBSCRIBE_CONTRIB'
		);

		// Canonical URL
		$this->template->assign_var('U_CANONICAL', $this->contrib->get_url());

		return $this->helper->render(
			'contributions/contribution_details.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['CONTRIB_DETAILS']
		);
	}

	/**
	* Styles demo page.
	*
	* @param string $contrib_type		Contrib type URL identifier
	* @param string $contrib			Contrib name clean
	* @param string $branch				Branch - examples: 3.0 3.1
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function demo($contrib_type, $contrib, $branch)
	{
		$this->load_contrib($contrib_type, $contrib);
		$can_use_demo =
			$this->contrib->contrib_status == ext::TITANIA_CONTRIB_APPROVED &&
			$this->contrib->contrib_type == ext::TITANIA_TYPE_STYLE &&
			$this->contrib->options['demo']
		;

		$branch = (int) $branch[0] . $branch[2];
		$demo_url = $this->contrib->get_demo_url($branch);

		if (!$can_use_demo || !$demo_url)
		{
			return $this->helper->error('NO_DEMO', 404);
		}

		$this->display->assign_global_vars();
		$this->generate_breadcrumbs();
		$demo = $this->contrib->type->get_demo()->configure(
			$branch,
			$this->contrib->contrib_id
		);
		$demo->load_styles();
		$demo->assign_details();

		$this->template->assign_var('U_SITE_HOME', $this->config['site_home_url'] ?: $this->ext_config->site_home_url);

		$title = $this->contrib->contrib_name .
			' - [' . $this->ext_config->phpbb_versions[$branch]['name'] . '] ' .
			$this->user->lang['CONTRIB_DEMO'];

		return $this->helper->render('contributions/demo.html', $title);
	}

	/**
	* Queue discussion topic redirect.
	*
	* @return Returns \Symfony\Component\HttpFoundation\Response if no
	*	topic was found, otherwise redirects to topic.
	*/
	protected function queue_discussion()
	{
		$sql = 'SELECT *
			FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE topic_type = ' . ext::TITANIA_QUEUE_DISCUSSION . '
			AND parent_id = ' . $this->contrib->contrib_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			$topic = new \titania_topic;
			$topic->__set_array($row);

			redirect($topic->get_url());
		}

		return $this->helper->error('NO_QUEUE_DISCUSSION_TOPIC', 404);
	}

	/**
	* Rating action.
	*
	* @return \Symfony\Component\HttpFoundation\Response|RedirectResponse|JsonResponse
	*/
	protected function rate()
	{
		$rating_value = $this->request->variable('value', -1.0);
		$rating = $this->contrib->get_rating();

		$result = ($rating_value == -1) ? $rating->delete_rating() : $rating->add_rating($rating_value);

		if ($result)
		{
			if ($this->request->is_ajax())
			{
				$rating->load_user_rating();
				$rating_string = $rating->get_rating_string($this->contrib->get_url('rate'));
				$rating_count = $this->contrib->contrib_rating_count + (($rating_value == -1) ? -1 : 1);

				return new JsonResponse(array(
					'rating'	=> $rating_string,
					'count'		=> $rating_count,
				));
			}
			return new RedirectResponse($this->contrib->get_url());
		}

		return $this->helper->error('BAD_RATING');
	}

	/**
	* Redirect to contribution from given contrib id.
	*
	* @param int $id
	* @return null
	*/
	public function redirect_from_id($id)
	{
		$this->load_contrib(false, (int) $id);

		redirect($this->contrib->get_url());
	}

	/**
	 * Version check.
	 *
	 * @param string $contrib_type		Contrib type URL identifier
	 * @param string $contrib			Contrib name clean
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function version_check($contrib_type, $contrib)
	{
		$this->load_contrib($contrib_type, $contrib);
		$this->contrib->get_download();
		$branches = array();

		ksort($this->contrib->download);

		foreach ($this->contrib->download as $branch => $download)
		{
			$version = $download['revision_version'];

			if (!preg_match('#^(\d+\.\d+)#', $version))
			{
				continue;
			}

			$branch = substr_replace($branch, '.', 1, 0);

			$empty_sid = '';

			$branches[$branch] = array(
				'current'		=> $version,
				'download'		=> $this->helper->route('phpbb.titania.download', array(
					'id' => $download['attachment_id'],
				), true, $empty_sid),
				'announcement'	=> $this->helper->route('phpbb.titania.contrib', array(
					'page'			=> '',
					'contrib_type'	=> $contrib_type,
					'contrib'		=> $contrib,
				), true, $empty_sid),
				'eol'			=> null,
				'security'		=> false,
			);
		}
		$versions = array(
			'stable'	=> $branches,
		);

		return new \Symfony\Component\HttpFoundation\JsonResponse($versions);
	}
}
