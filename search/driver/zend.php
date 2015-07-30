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

namespace phpbb\titania\search\driver;

use phpbb\titania\access;

class zend extends base
{
	/** @var string */
	protected $ext_root_path;

	/** @var string */
	protected $php_ext;

	/** @var \ezcSearchDeleteQuery|\ezcSearchFindQuery */
	protected $query;

	/** @var \ezcSearchSession */
	protected $client;

	const store_path = 'store/search/';

	/**
	 * Constructor
	 *
	 * @param string $ext_root_path
	 * @param string $php_ext
	 */
	public function __construct($ext_root_path, $php_ext)
	{
		$this->ext_root_path = $ext_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Initialise search engine.
	 *
	 * @throws \Exception
	 */
	public function initialise()
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . realpath($this->ext_root_path . 'includes/library/'));

		if (!class_exists('\Zend_Search_Lucene'))
		{
			require($this->ext_root_path . 'includes/library/Zend/Search/Lucene.' . $this->php_ext);
		}
		$this->load_search_component();

		if (!is_writable($this->ext_root_path . self::store_path))
		{
			throw new \Exception(self::store_path . ' must be writable to use the Zend Lucene Search');
		}

		$handler = new \ezcSearchZendLuceneHandler($this->ext_root_path . self::store_path);
		$manager = new \ezcSearchEmbeddedManager;

		$this->client = new \ezcSearchSession($handler, $manager);

		return $this;
	}

	/**
	 * Load search component classes.
	 */
	protected function load_search_component()
	{
		if (!class_exists('\phpbb_ezcomponents_loader'))
		{
			require($this->ext_root_path . 'includes/library/ezcomponents/loader.' . $this->php_ext);
		}

		\phpbb_ezcomponents_loader::load_component('search');
	}

	/**
	 * @{inheritDoc}
	 */
	public function search()
	{
		$search_results = $this->client->find($this->query);

		$results = array(
			'user_ids'		=> array(),
			'documents'		=> array(),
			'total'			=> $search_results->resultCount,
		);

		foreach ($search_results->documents as $result)
		{
			$doc = $result->document;
			$results['user_ids'][] = $doc->author;
			$results['documents'][] = $doc->get_state();
		}

		return $results;
	}

	/**
	 * @{inheritDoc}
	 */
	public function delete($object_type, $object_id)
	{
		$this->client->deleteById($object_type . '_' . $object_id, '\phpbb\titania\search\document');
	}

	/**
	 * @{inheritDoc}
	 */
	public function index($object_type, $object_id, $data)
	{
		$data['id'] = $object_type . '_' . $object_id;
		$data['type'] = $object_type;

		$article = new \phpbb\titania\search\document();

		// Set some defaults
		$data = array_merge(array(
			'access_level'	=> access::PUBLIC_LEVEL,
			'approved'		=> true,
			'reported'		=> false,
		), $data);

		$article->setState($data);

		// Run the update routine instead of the index, this way we should not ever run into issues with duplication
		$this->client->update($article);
	}

	/**
	 * @{inheritDoc}
	 */
	public function mass_index($data)
	{
		$this->client->beginTransaction();

		foreach ($data as $row)
		{
			$object_type = $row['object_type'];
			$object_id = $row['object_id'];
			unset($row['object_type'], $row['object_id']);

			$this->index($object_type, $object_id, $row);
		}

		$this->client->commit();
	}

	/**
	 * @{inheritDoc}
	 */
	public function truncate($object_type = false)
	{
		$this->query = $this->client->createDeleteQuery('\phpbb\titania\search\document');

		if ($object_type !== false)
		{
			$this->query->where(
				$this->query->eq('type', $object_type)
			);
		}

		$this->client->delete($this->query);
	}

	/**
	 * @{inheritDoc}
	 */
	public function clean_string($string)
	{
		return str_replace('|', ' or ', $string);
	}

	/**
	 * @{inheritDoc}
	 */
	public function new_search_query()
	{
		$this->query = $this->client->createFindQuery('\phpbb\titania\search\document');
		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function where_in_set($field, array $values)
	{
		$this->query->where($this->in_set($field, $values));

		return $this;
	}

	/**
	 * Get query clause for in set structure.
	 *
	 * @param string $field
	 * @param array $values
	 * @return string
	 * @throws \Exception		Throws exception for empty set
	 */
	protected function in_set($field, array $values)
	{
		if (empty($values))
		{
			throw new \Exception('No values specified for search in set');
		}

		$set = array();
		foreach ($values as $item)
		{
			$set[] = $this->query->eq($field, $item);
		}
		return $this->query->lOr($set);
	}

	/**
	 * @{inheritDoc}
	 */
	public function where_equals($property, $value)
	{
		$this->query->where($this->query->eq($property, $value));
		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function set_keywords($keywords, $search_title, $search_text)
	{
		$keywords = $this->clean_string($keywords);
		$fields = array();

		if ($search_title)
		{
			$fields[] = 'title';
		}
		if ($search_text)
		{
			$fields[] = 'text';
		}
		$qb = new \ezcSearchQueryBuilder;
		$qb->parseSearchQuery($this->query, $keywords, $fields);

		return $this;
	}

	/**
	 * {inheritDoc}
	 */
	public function set_granular_type_restrictions(array $restrictions)
	{
		$clauses = array();

		foreach ($restrictions as $search_type => $contrib_types)
		{
			if (empty($contrib_types))
			{
				continue;
			}
			$clauses[] = $this->query->lAnd(
				$this->query->eq('type', $search_type),
				$this->in_set('parent_contrib_type', $contrib_types)
			);
		}
		$this->query->where($this->query->lOr($clauses));

		return $this;
	}

	/**
	 * @{inheritDoc}
	 */
	public function set_limit($start, $per_page)
	{
		$this->query->offset = $start;
		$this->query->limit = $per_page;
		return $this;
	}
}
