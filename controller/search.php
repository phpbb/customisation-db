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

class search
{
	/** @var \phpbb\config\config */
	protected $config;

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

	/** @var \titania_search */
	protected $engine;

	const SEARCH_ALL = 0;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\cache\service $cache
	* @param \phpbb\request\request_interface
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\titania\config\config $ext_config
	* @param \phpbb\titania\display $display
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\request\request $request, \phpbb\titania\controller\helper $helper, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->cache = $cache;
		$this->request = $request;
		$this->helper = $helper;
		$this->ext_config = $ext_config;
		$this->display = $display;
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

		\titania::_include('functions_posting', 'generate_category_select');
		generate_category_select(false, false, false);

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
		$this->initialise_engine();

		$keywords		= $this->request->variable('keywords', '', true);
		$search_fields	= $this->request->variable('sf', '');
		$author			= $this->request->variable('author', '', true);
		$author_id		= $this->request->variable('u', 0);

		if ($author)
		{
			$author_id = $this->get_author_id($author);
		}

		// Initialize the query
		$this->query = $this->engine->create_find_query();
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
		$sort = new \titania_sort();
		$sort->set_defaults($this->config['posts_per_page']);
		$sort->request();

		// Do the search
		$results = $this->engine->custom_search($this->query, $sort);

		// Grab the users
		\users_overlord::load_users($results['user_ids']);

		$this->display->assign_global_vars();
		$this->assign_doc_vars($results['documents']);
		$this->assign_result_vars($sort->total);

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
		$sort->build_pagination($sort_url, $parameters);

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
		// Query fields
		$query_fields = array();
		switch ($search_fields)
		{
			case 'titleonly' :
				$query_fields[] = 'title';
			break;

			case 'msgonly' :
				$query_fields[] = 'text';
			break;

			default:
				$query_fields[] = 'title';
				$query_fields[] = 'text';
			break;
		}

		// Keywords specified?
		if ($keywords)
		{
			$this->engine->clean_keywords($keywords);

			$qb = new \ezcSearchQueryBuilder();
			$qb->parseSearchQuery($this->query, $keywords, $query_fields);
			unset($qb);
		}

		// Author specified?
		if ($author_id)
		{
			$this->query->where($this->query->eq('author', $author_id));
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
			$this->query->where($this->query->eq('type', $type));
		}

		// Contrib specified?
		if ($contrib_id)
		{
			$this->query->where($this->query->eq('parent_id', $contrib_id));
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

			$this->query->where($this->engine->in_set($this->query, 'categories', $categories));
		}

		if (!empty($versions))
		{
			$this->query->where($this->engine->in_set($this->query, 'phpbb_versions', $versions));
		}

		$this->query->where($this->query->eq('type', TITANIA_CONTRIB));
	}

	/**
	* Generate query for searching all content.
	*
	* @return null
	*/
	protected function generate_search_all_query()
	{
		$query_or_clauses = array($this->engine->in_set(
			$this->query,
			'type',
			array(TITANIA_SUPPORT, TITANIA_CONTRIB, TITANIA_FAQ)
		));

		// Enforce permissions on the results to ensure that we don't leak posts to users who don't have access to the originating queues.
		$access_queue_discussion = \titania_types::find_authed('queue_discussion');
		$access_validation_queue = \titania_types::find_authed('view');

		if (!empty($access_validation_queue))
		{
			$query_or_clauses[] = $this->query->lAnd(
				$this->query->eq('type', TITANIA_QUEUE),
				$this->engine->in_set($this->query, 'parent_contrib_type', $access_validation_queue)
			);
		}

		if (!empty($access_queue_discussion))
		{
			$query_or_clauses[] = $this->query->lAnd(
				$this->query->eq('type', TITANIA_QUEUE_DISCUSSION),
				$this->engine->in_set($this->query, 'parent_contrib_type', $access_queue_discussion)
			);
		}

		$this->query->where($this->query->lOr($query_or_clauses));
	}

	/**
	* Get author id from given username.
	*
	* @param string $author		Author's username.
	* @return int Return's user id or 0 if user was not found.
	*/
	protected function get_author_id($author)
	{
		\titania::_include('functions_posting', 'get_author_ids_from_list');

		$missing = array();
		get_author_ids_from_list($author, $missing);

		return (int) array_shift($author);
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

		if (!$this->ext_config->search_enabled)
		{
			throw new \Exception($this->user->lang['SEARCH_UNAVAILABLE']);
		}

		$this->engine = new \titania_search;

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
	* Initialise search engine.
	*
	* @throws \Exception	Throws exception if search is unavailable.
	* @return null
	*/
	protected function initialise_engine()
	{
		// Setup the search tool and make sure it is working
		$this->engine->initialize();

		if (\titania_search::$do_not_index)
		{
			// Solr service is down
			throw new \Exception($this->user->lang['SEARCH_UNAVAILABLE']);
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
				'POST_AUTHOR_FULL'	=> ($document->author) ? \users_overlord::get_user($document->author, '_full') : false,
				'POST_DATE'			=> ($document->date) ? $this->user->format_date($document->date) : false,
				'POST_SUBJECT'		=> censor_text($document->title),
				'MESSAGE'			=> generate_text_for_display(
					$document->text,
					$document->text_uid,
					$document->text_bitfield,
					$document->text_options
				),
				'U_VIEW_POST'		=> $this->get_document_url($document->type, $document->url),
				'S_POST_REPORTED'	=> $document->reported,
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
}
