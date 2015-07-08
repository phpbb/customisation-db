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

namespace phpbb\titania\controller;

use phpbb\exception\http_exception;
use phpbb\titania\access;
use phpbb\titania\user\helper as user_helper;

class search
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\sort */
	protected $sort;

	/** @var \phpbb\titania\access */
	protected $access;

	/** @var \phpbb\titania\search\manager */
	protected $manager;

	/** @var \phpbb\titania\search\driver\driver_interface */
	protected $engine;

	/** @var string */
	protected $posts_table;

	/** @var string */
	protected $faq_table;

	/** @var string */
	protected $contribs_table;

	const SEARCH_ALL = 0;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\request\request_interface $request
	 * @param helper $helper
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\sort $sort
	 * @param access $access
	 * @param \phpbb\titania\search\manager $manager
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\request\request_interface $request, \phpbb\titania\controller\helper $helper, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\sort $sort, \phpbb\titania\access $access, \phpbb\titania\search\manager $manager)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->template = $template;
		$this->user = $user;
		$this->cache = $cache;
		$this->request = $request;
		$this->helper = $helper;
		$this->ext_config = $ext_config;
		$this->display = $display;
		$this->sort = $sort;
		$this->access = $access;
		$this->manager = $manager;
		$this->posts_table = TITANIA_POSTS_TABLE;
		$this->faq_table = TITANIA_CONTRIB_FAQ_TABLE;
		$this->contribs_table = TITANIA_CONTRIBS_TABLE;
	}

	/**
	* Display general search form.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function general()
	{
		$this->setup();

		// Output search types
		foreach ($this->search_types as $value => $name)
		{
			$this->template->assign_block_vars('types', array(
				'NAME'		=> $this->user->lang($name),
				'VALUE'		=> $value,
			));
		}

		$this->template->assign_vars(array(
			'S_SEARCH_ACTION'	=> $this->helper->route('phpbb.titania.search.results'),
		));
		$this->display->assign_global_vars();

		return $this->helper->render('search_body.html', $this->user->lang['SEARCH']);
	}

	/**
	* Display search page for contributions.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function contributions()
	{
		$this->setup();
		$this->user->add_lang_ext('phpbb/titania', 'contributions');

		$this->display->generate_category_select(false, false, false);

		// Display the list of phpBB versions available
		foreach ($this->cache->get_phpbb_versions() as $version => $name)
		{
			$this->template->assign_block_vars('phpbb_versions', array(
				'VERSION'		=> $name,
			));
		}

		$this->template->assign_vars(array(
			'S_SEARCH_ACTION'	=> $this->helper->route('phpbb.titania.search.contributions.results'),
		));
		$this->display->assign_global_vars();

		return $this->helper->render('find_contribution.html', $this->user->lang['SEARCH']);
	}

	/**
	* Display general results.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function general_results()
	{
		$search_type = $this->request->variable('type', 0);
		$contrib_id = $this->request->variable('contrib', 0);

		$this->common_results();
		$this->generate_general_query($search_type, $contrib_id);

		$this->template->assign_vars(array(
			'U_SEARCH'			=> $this->helper->route('phpbb.titania.search'),
		));

		return $this->show_results($this->helper->route('phpbb.titania.search.results'));
	}

	/**
	* Display contribution results
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function contribution_results()
	{
		$versions = $this->request->variable('versions', array(''));
		$categories = $this->request->variable('c', array(0));
		$search_subcategories = $this->request->variable('sc', false);

		$this->common_results();
		$this->generate_contrib_query($versions, $categories, $search_subcategories);

		$this->template->assign_vars(array(
			'U_SEARCH'			=> $this->helper->route('phpbb.titania.search.contributions'),
		));

		return $this->show_results($this->helper->route('phpbb.titania.search.contributions.results'));
	}

	/**
	* Common handler for displaying general and contrib results.
	*
	* @return null
	*/
	public function common_results()
	{
		$this->setup();

		$keywords		= $this->request->variable('keywords', '', true);
		$search_fields	= $this->request->variable('sf', '');
		$author			= $this->request->variable('author', '', true);
		$author_id		= $this->request->variable('u', 0);

		if ($author)
		{
			$author_id = $this->get_author_id($author);

			if (!$author_id)
			{
				throw new http_exception(
					200,
					'NO_USER'
				);
			}
		}

		if ($author === '' && $keywords === '')
		{
			throw new http_exception(
				200,
				'NO_SEARCH_TERMS'
			);
		}

		$this->engine->new_search_query();
		$this->generate_main_query($search_fields, $keywords, $author_id);
	}

	/**
	* Perform search and output results.
	*
	* @param string $sort_url		Base sort url.
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function show_results($sort_url)
	{
		// Setup the sort tool
		$this->sort
			->set_defaults($this->config['posts_per_page'])
			->request()
		;

		// Do the search
		$results = $this->query_index();

		// Grab the users
		\users_overlord::load_users($results['user_ids']);

		$this->display->assign_global_vars();

		$this->assign_doc_vars($results['documents']);
		$this->assign_result_vars($this->sort->total);

		$parameters = array();
		$expected_parameters = array(
			'versions'		=> array(array(''), false),
			'c'				=> array(array(0), false),
			'sc'			=> array(false, false),
			'keywords'		=> array('', true),
			'sf'			=> array('', false),
			'author'		=> array('', true),
			'u'				=> array(0, false),
			'type'			=> array(0, false),
			'contrib'		=> array(0, false),
		);

		foreach ($expected_parameters as $name => $properties)
		{
			if ($this->request->is_set($name))
			{
				list($default_value, $multibyte) = $properties;

				$value = $this->request->variable($name, $default_value, $multibyte);

				// Clean up URL by not including default values.
				if ($value !== $default_value)
				{
					$parameters[$name] = $value;
				}
			}
		}
		$this->sort->build_pagination($sort_url, $parameters);

		return $this->helper->render('search_results.html', $this->user->lang['SEARCH']);
	}

	/**
	* Generate main query shared by general and contribution search.
	*
	* @param string $search_fields		Fields to search: titleonly|msgonly|
	* @param string $keywords			Search keywords
	* @param int $author_id				Author id
	*
	* @return null
	*/
	protected function generate_main_query($search_fields, $keywords, $author_id)
	{
		// Keywords specified?
		if ($keywords)
		{
			// Query fields
			$search_title = $search_text = true;

			switch ($search_fields)
			{
				case 'titleonly' :
					$search_text = false;
					break;

				case 'msgonly' :
					$search_title = false;
					break;
			}
			$this->engine->set_keywords($keywords, $search_title, $search_text);
		}

		// Author specified?
		if ($author_id)
		{
			$this->engine->where_equals('author', $author_id);
		}
	}

	/**
	* Generate query for general search.
	*
	* @param int $type			Search object type.
	* @param int $contrib_id	Contrib id to search.
	*
	* @return null
	*/
	protected function generate_general_query($type, $contrib_id)
	{
		// Fall back to search all if the search type doesn't exist
		if (!isset($this->search_types[$type]))
		{
			$type = self::SEARCH_ALL;
		}

		// Search all
		if ($type == self::SEARCH_ALL)
		{
			$this->generate_search_all_query();
		}
		else
		{
			$this->engine->set_type($type);
		}

		// Contrib specified?
		if ($contrib_id)
		{
			$this->engine->where_equals('parent_id', $contrib_id);
		}
	}

	/**
	* Generate query for contribution search.
	*
	* @param array $versions			Supported phpBB versions to limit search to.
	* @param array $categories			Categories to filter by.
	* @param bool $search_subcategories	Whether to search a category children.
	*
	* @return null
	*/
	protected function generate_contrib_query($versions, $categories, $search_subcategories)
	{
		if (!empty($categories) && (sizeof($categories) != 1 || $categories[0] != 0))
		{
			// Grab the children
			if ($search_subcategories)
			{
				foreach ($categories as $category_id)
				{
					$categories = array_merge(
						$categories,
						array_keys($this->cache->get_category_children($category_id))
					);
				}
			}

			$this->engine->where_in_set('categories', $categories);
		}

		if (!empty($versions))
		{
			$this->engine->where_in_set('phpbb_versions', $versions);
		}
		$this->engine->set_type(TITANIA_CONTRIB);
	}

	/**
	* Generate query for searching all content.
	*
	* @return null
	*/
	protected function generate_search_all_query()
	{
		$contrib_types = array_keys(\titania_types::$types);

		$restrictions = array(
			TITANIA_SUPPORT		=> $contrib_types,
			TITANIA_CONTRIB		=> $contrib_types,
			TITANIA_FAQ			=> $contrib_types,
		);

		// Enforce permissions on the results to ensure that we don't leak posts to users who don't have access to the originating queues.
		$access_queue_discussion = \titania_types::find_authed('queue_discussion');
		$access_validation_queue = \titania_types::find_authed('view');

		if (!empty($access_validation_queue))
		{
			$restrictions[TITANIA_QUEUE] = $access_validation_queue;
		}

		if (!empty($access_queue_discussion))
		{
			$restrictions[TITANIA_QUEUE_DISCUSSION] = $access_queue_discussion;
		}
		$this->engine->set_granular_type_restrictions($restrictions);
	}

	/**
	* Get author id from given username.
	*
	* @param string $author		Author's username.
	* @return int Return's user id or 0 if user was not found.
	*/
	protected function get_author_id($author)
	{
		$user = user_helper::get_user_ids_from_list($this->db, $author);

		return (int) array_shift($user['ids']);
	}

	/**
	* Perform common set up tasks.
	*
	* @return null
	*/
	protected function setup()
	{
		// Add common lang
		$this->user->add_lang('search');
		$this->user->add_lang_ext('phpbb/titania', 'search');
		$this->manager->set_active_driver();

		if (!$this->manager->search_enabled())
		{
			throw new http_exception(200, 'SEARCH_UNAVAILABLE');
		}
		$this->engine = $this->manager->get_active_driver();

		// Available Search Types
		$this->search_types = array(
			TITANIA_CONTRIB		=> 'CONTRIBUTION_NAME_DESCRIPTION',
			TITANIA_FAQ			=> 'CONTRIB_FAQ',
		);

		if ($this->ext_config->support_in_titania)
		{
			$this->search_types[TITANIA_SUPPORT] = 'CONTRIB_SUPPORT';
		}
	}

	/**
	* Assign document variables to template.
	*
	* @param array $documents		Documents
	* @return null
	*/
	protected function assign_doc_vars($documents)
	{
		foreach ($documents as $document)
		{
			$this->template->assign_block_vars('searchresults', array(
				'POST_AUTHOR_FULL'	=> ($document['author']) ? \users_overlord::get_user($document['author'], '_full') : false,
				'POST_DATE'			=> ($document['date']) ? $this->user->format_date($document['date']) : false,
				'POST_SUBJECT'		=> censor_text($document['title']),
				'MESSAGE'			=> generate_text_for_display(
					$document['text'],
					$document['text_uid'],
					$document['text_bitfield'],
					$document['text_options']
				),
				'U_VIEW_POST'		=> $this->get_document_url($document['type'], $document['url']),
			));
		}
	}

	/**
	* Get document URL.
	*
	* @param int $type			Document object type.
	* @param string $params		Serialized array of parameters.
	*
	* @return string
	*/
	protected function get_document_url($type, $params)
	{
		$params = unserialize($params);

		switch ($type)
		{
			case TITANIA_FAQ:
				$controller = 'phpbb.titania.contrib.faq.item';
			break;

			case TITANIA_QUEUE:
				$controller = 'phpbb.titania.queue.item';
			break;

			case TITANIA_SUPPORT:
			case TITANIA_QUEUE_DISCUSSION:
				$controller = 'phpbb.titania.contrib.support.topic';
			break;

			case TITANIA_CONTRIB:
				$controller = 'phpbb.titania.contrib';
			break;

			default:
				return '';
		}

		return $this->helper->route($controller, $params);
	}

	/**
	* Assign result page template variables.
	*
	* @param int $match_count		Number of matches found.
	* @return null
	*/
	protected function assign_result_vars($match_count)
	{
		$this->template->assign_vars(array(
			'SEARCH_WORDS'		=> $this->request->variable('keywords', '', true),
			'SEARCH_MATCHES'	=> $this->user->lang('FOUND_SEARCH_MATCHES', $match_count),

			'U_SEARCH_WORDS'	=> $this->helper->get_current_url(),
			'S_IN_SEARCH'		=> true,
			'S_SEARCH_ACTION'	=> $this->helper->get_current_url(),
		));
	}

	/**
	 * Query search index.
	 *
	 * @return array
	 */
	protected function query_index()
	{
		// For those without moderator permissions do not display unapproved stuff
		if (!$this->auth->acl_get('m_'))
		{
			$this->engine->where_equals('approved', 1);
		}

		// Don't worry about authors level access...no search page that can search where a
		// person would have authors access
		if (!$this->access->is_team())
		{
			$this->engine->where_equals('access_level', access::PUBLIC_LEVEL);
		}

		$this->engine->set_limit($this->sort->start, $this->sort->limit);

		$results = $this->engine->search();
		$contribs = $faqs = $posts = array();

		$this->sort->total = $results['total'];

		foreach ($results['documents'] as $data)
		{
			switch ($data['type'])
			{
				case TITANIA_CONTRIB :
					$contribs[] = $data['id'];
					break;

				case TITANIA_SUPPORT :
				case TITANIA_QUEUE_DISCUSSION :
				case TITANIA_QUEUE :
					$posts[] = $data['id'];
				break;

				case TITANIA_FAQ :
					$faqs[] = $data['id'];
					break;
			}
		}

		// Get additional data not included in result.
		if ($results['documents'])
		{
			$results['documents'] = $this->get_contribs($contribs, $results['documents']);
			$results['documents'] = $this->get_posts($posts, $results['documents']);
			$results['documents'] = $this->get_faqs($faqs, $results['documents']);
		}
		return $results;
	}

	/**
	 * Get additional post data.
	 *
	 * @param array $ids
	 * @param array $documents
	 * @return array
	 */
	protected function get_posts(array $ids, array $documents)
	{
		if (!$ids)
		{
			return $documents;
		}

		$sql = 'SELECT post_id AS id, post_type, topic_id, post_subject AS title, post_text AS text, post_text_uid AS text_uid,
				post_text_bitfield AS text_bitfield, post_text_options AS text_options,
				post_url AS url
			FROM ' . $this->posts_table . '
			WHERE ' . $this->db->sql_in_set('post_id', $ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id = $row['post_type'] . '_' . $row['id'];
			$row['url'] = serialize(array_merge(unserialize($row['url']), array(
				'topic_id' 	=> $row['topic_id'],
				'p'			=> $row['id'],
				'#'			=> 'p' . $row['id'],
			)));
			$documents[$id] = array_merge($documents[$id], $row);
		}
		$this->db->sql_freeresult($result);

		return $documents;
	}

	/**
	 * Get additional contrib data.
	 *
	 * @param array $ids
	 * @param array $documents
	 * @return array
	 */
	protected function get_contribs(array $ids, array $documents)
	{
		if (!$ids)
		{
			return $documents;
		}

		$sql = 'SELECT contrib_id AS id, contrib_name AS title, contrib_name_clean, contrib_type, contrib_desc AS text,
				contrib_desc_uid AS text_uid, contrib_desc_bitfield AS text_bitfield,
				contrib_desc_options AS text_options
			FROM ' . $this->contribs_table . '
			WHERE ' . $this->db->sql_in_set('contrib_id', $ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id = TITANIA_CONTRIB . '_' . $row['id'];
			$row['url'] = serialize(array(
				'contrib_type'	=> \titania_types::$types[$row['contrib_type']]->url,
				'contrib'		=> $row['contrib_name_clean'],
			));
			$documents[$id] = array_merge($documents[$id], $row);
		}
		$this->db->sql_freeresult($result);

		return $documents;
	}

	/**
	 * Get additional FAQ data.
	 *
	 * @param array $ids
	 * @param array $documents
	 * @return array
	 */
	protected function get_faqs(array $ids, array $documents)
	{
		if (!$ids)
		{
			return $documents;
		}

		$sql = 'SELECT f.faq_id AS id, f.faq_subject AS title, c.contrib_name_clean, c.contrib_type, f.faq_text AS text,
				f.faq_text_uid AS text_uid, f.faq_text_bitfield AS text_bitfield,
				f.faq_text_options AS text_options
			FROM ' . $this->contribs_table . ' c, ' .
				$this->faq_table . ' f
			WHERE ' . $this->db->sql_in_set('f.faq_id', $ids) . '
				AND f.contrib_id = c.contrib_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$id = TITANIA_FAQ . '_' . $row['id'];
			$row['url'] = serialize(array(
				'contrib_type'	=> \titania_types::$types[$row['contrib_type']]->url,
				'contrib'		=> $row['contrib_name_clean'],
				'id'			=> $row['id'],
			));
			$documents[$id] = array_merge($documents[$id], $row);
		}
		$this->db->sql_freeresult($result);

		return $documents;
	}
}
