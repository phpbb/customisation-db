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

namespace phpbb\titania\url\legacy;

use phpbb\titania\url\url;

class rerouter
{
	protected $separator = '-';
	protected $separator_replacement = '%96';

	/**
	* Get data for new URL from old one.
	*
	* @param string $url		 Example: download/id_2
	* @return mixed Returns null if no route matches the given URL or array in
	* 	form of array(
	*		'route'		=> (string),
	*		'params'	=> (array),
	* 	) 
	*/
	public function get_url_data($url)
	{
		$types = array(
			'style',
			'mod',
			'bbcode',
			'official_tool',
			'converter',
			'translation',
			'bridge',
		);
		$pages = array(
			'index',
			'download',
			'manage',
			'authors',
			'contributions',
			'support',
			'search',
			'find-contribution',
		);

		$base = '';
		$params = array();
		$this->split_base_params($base, $params, $url);

		$base = trim($base, '/');
		$this->base_parts = explode('/', $base);
		$page = $this->base_parts[0];
		$page = (!$page) ? 'index' : $page;

		$this->url = new url();
		$this->url->set_params($params);

		if (in_array($page, $types))
		{
			$this->contribution();
		}
		else if (in_array($page, $pages))
		{
			$page = str_replace('-', '_', $page);
			$this->{$page}();
		}
		else if (sizeof($this->base_parts) <= 3)
		{
			$this->category();
		}

		if ($this->url->get_route())
		{
			return array(
				'route'		=> $this->url->get_route(),
				'params'	=> $this->url->get_params(),
			);
		}
		return null;
	}

	/**
	* Reroute index URL.
	*
	* @return null
	*/
	protected function index()
	{
		// Category handling has the potential of causing a redirect loop.
		// We only catch the index if it has one of the sort parameters.
		if ($this->url->has_param('start') || $this->url->has_param('sk') || $this->url->has_param('sd'))
		{
			$this->url->set_route('index');
		}
	}

	/**
	* Reroute category URL.
	*
	* @return null
	*/
	protected function category()
	{
		// Category handling has the potential of causing a redirect loop.
		// We only catch the category if it has one of the sort parameters.
		if ($this->url->has_param('start') || $this->url->has_param('sk') || $this->url->has_param('sd'))
		{
			$categories = array_pad($this->base_parts, 3, '');
			$this->url->set_route('category')
				->add_params(array(
					'category1'	=> $categories[0],
					'category2' => $categories[1],
					'category3'	=> $categories[2]
				));
		}
	}

	/**
	* Reroute author URL.
	*
	* @return null
	*/
	protected function authors()
	{
		$parts = array_pad($this->base_parts, 3, '');
		list($section, $author, $page) = $parts;

		$this->url->set_route('author')
			->add_params(array(
				'author'	=> $author,
				'page'		=> $page,
			));
	}

	/**
	* Reroute download URL.
	*
	* @return null
	*/
	protected function download()
	{
		$this->url->set_route('download');
	}

	/**
	* Reroute search results URL.
	*
	* @return null
	*/
	protected function search()
	{
		$this->url->set_route('search.results');
	}

	/**
	* Reroute contribution search results URL.
	*
	* @return null
	*/
	protected function find_contribution()
	{
		$this->url->set_route('search.contributions.results');
	}

	/**
	* Reroute manage URL.
	*
	* @return null
	*/
	protected function manage()
	{
		$pages = array(
			'queue',
			'attention',
			'queue_discussion',
		);
		$page = (!empty($this->base_parts[1])) ? $this->base_parts[1] : false;

		if (in_array($page, $pages))
		{
			$this->{$page}();
		}
	}

	/**
	* Reroute attention URL.
	*
	* @return null
	*/
	protected function attention()
	{
		$this->url->set_route('manage.attention');

		if ($this->url->has_param('a'))
		{
			$this->url->append_route('item')
				->rename_param('a', 'id');
		}
		else if ($this->url->has_param('id') && $this->url->has_param('type'))
		{
			$this->url->append_route('redirect');
		}
	}

	/**
	* Reroute queue URL.
	*
	* @return null
	*/
	protected function queue()
	{
		$this->url->set_route('queue');

		if ($this->url->has_param('q'))
		{
			$this->url->append_route('item')
				// Remove topic title.
				->remove_nth_param(2)
				->rename_param('q', 'id')
				->remove_param('t');

			if ($this->url->has_param('action'))
			{
				$this->url->append_route('action');
			}
		}
		else if ($this->url->has_param('queue'))
		{
			$this->url->append_route('type')
				->rename_param('queue', 'queue_type');
		}
	}

	/**
	* Reroute queue discussion URL.
	*
	* @return null
	*/
	protected function queue_discussion()
	{
		$this->url->set_route('queue_discussion');

		if ($this->url->has_param('queue'))
		{
			$this->url->append_route('type')
				->rename_param('queue', 'queue_type');
		}
	}

	/**
	* Reroute contribution URL.
	*
	* @return null
	*/
	protected function contribution()
	{
		$parts = array_pad($this->base_parts, 3, '');
		list($contrib_type, $contrib, $page) = $parts;
		$params = array();

		$this->url->set_route('contrib')
			->add_params(array(
				'contrib_type'	=> $contrib_type,
				'contrib'		=> $contrib,
			));

		$pages = array(
			'support',
			'faq',
			'demo',
			'revision',
			'revision_edit',
		);

		if (in_array($page, $pages))
		{
			$this->{"contribution_$page"}();
		}
	}

	/**
	* Reroute contrib demo URL.
	*
	* @return null
	*/
	protected function contribution_demo()
	{
		$this->url->append_route('demo')
			->set_param('branch', '3.0');
	}

	/**
	* Reroute revision URL.
	*
	* @return null
	*/
	protected function contribution_revision()
	{
		if ($this->url->has_param('repack'))
		{
			$this->url->append_route('revision.repack')
				->rename_param('repack', 'id');
		}
	}

	/**
	* Reroute revision edit URL.
	*
	* @return null
	*/
	protected function contribution_revision_edit()
	{
		if ($this->url->has_param('revision'))
		{
			$this->url->append_route('revision.edit')
				->rename_param('revision', 'id');
		}
	}

	/**
	* Reroute contrib support URL.
	*
	* @return null
	*/
	protected function contribution_support()
	{
		$this->url->append_route('support');

		if ($this->url->param_equals('action', 'post'))
		{
			$this->url->append_route('post_topic');
		}
		else if ($this->url->has_param('t'))
		{
			$this->url->append_route('topic')
				// Remove topic title.
				->remove_nth_param(1)
				->rename_param('t', 'topic_id')
			;

			if ($this->url->has_param('action'))
			{
				$this->url->append_route('action');
			}
		}
	}

	/**
	* Reroute contrib FAQ URL.
	*
	* @return null
	*/
	protected function contribution_faq()
	{
		$this->url->append_route('faq');

		if ($this->url->has_param('f'))
		{
			$this->url->append_route('item')
				->rename_param('f', 'id');

			if ($this->url->has_param('action'))
			{
				$this->url->append_route('action');
			}
		}
		else if ($this->url->param_equals('action', 'create'))
		{
			$this->url->append_route('create');
		}
	}

	/**
	* Reroute all contributions URL.
	*
	* @return null
	*/
	protected function contributions()
	{
		$this->url->set_route('all_contribs');
	}

	/**
	* Reroute all support URL.
	*
	* @return null
	*/
	protected function support()
	{
		$this->url->set_route('support')
			->set_param('type', $this->base_parts[1]);
	}

	/**
	 * Unbuild a url (used from the indexer)
	 *
	 * @param string $base The base (send $url param here and we'll just update it properly)
	 * @param string $params The params
	 * @param string|bool $url The url to unbuild from storage (can send it through $base optionally and leave as false)
	 */
	public function split_base_params(&$base, &$params, $url = false)
	{
		$base = ($url !== false) ? $url : $base;
		$params = array();

		if (substr($base, -1) != '/')
		{
			$params = substr($base, (strrpos($base, '/') + 1));
			$base = substr($base, 0, (strrpos($base, '/') + 1));
			$params = $this->split_params($params);
		}
	}

	/**
	 * Split up the parameters (from a string to an array, used for the search page from the indexer)
	 *
	 * @param string $params
	 */
	public function split_params($params)
	{
		$new_params = array();

		if (strpos($params, '#') !== false)
		{
			$new_params['#'] = substr($params, (strpos($params, '#') + 1));
			$params = substr($params, 0, strpos($params, '#'));
		}

		foreach (explode($this->separator, $params) as $section)
		{
			// Overwrite the sid_ with the ?sid= so we can use the current session.
			if ((strlen($section) == 37) && (strpos($section, '?sid=') === 0))
			{
				$section = 'sid_' . substr($section, 5);
			}

			$parts = explode('_', $section, 2);
			if (sizeof($parts) == 2)
			{
				if (strpos(urldecode($parts[0]), '[]'))
				{
					$parts[0] = str_replace('[]', '', urldecode($parts[0]));

					if (!isset($new_params[$parts[0]]))
					{
						$new_params[$parts[0]] = array();
					}

					$new_params[$parts[0]][] = urldecode(str_replace(
						$this->separator_replacement,
						$this->separator,
						$parts[1]
					));
				}
				else
				{
					$new_params[$parts[0]] = $parts[1];
				}
			}
			else if (sizeof($parts) == 1)
			{
				$new_params[] = $parts[0];
			}
		}

		return $new_params;
	}
}
