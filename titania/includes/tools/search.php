<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

// Include library in include path (for Zend)
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(TITANIA_ROOT . 'includes/library/'));
titania::_include('library/Zend/Search/Lucene', false, 'Zend_Search_Lucene');

// Using the phpBB ezcomponents loader
titania::_include('library/ezcomponents/loader', 'phpbb_ezcomponents_loader');
$loader = new phpbb_ezcomponents_loader();
$loader->load_component('search');
unset($loader);

class titania_search
{
	/**
	* Path to store (for the Zend Search index files)
	*/
	const store_path = 'store/search/';

	/**
	* Holds the indexer
	*/
	private static $index = false;

	/**
	* Initialize the Search
	*/
	public static function initialize()
	{
		if (self::$index === false)
		{
			// Initialize the ezc/Zend Search class
			$handler = new ezcSearchZendLuceneHandler(TITANIA_ROOT . self::store_path);
			$manager = new ezcSearchEmbeddedManager;
			self::$index = new ezcSearchSession($handler, $manager);
		}
	}

	/**
	* Index an item
	*
	* @param mixed $object_type The object_type (what this is set to is not entirely important, but must be the same for all items of that type)
	* @param int $object_id The object_id of an item (there can only be one of each id per object_type)
	* @param array $data Array of data (see titania_article)
	* @param bool $update True to update an existing item, false to index
	*/
	public static function index($object_type, $object_id, $data, $update = false)
	{
		self::initialize();

		$data['id'] = $object_type . '_' . $object_id;
		$data['type'] = $object_type;

		$article = new titania_article();

		// Set some defaults
		$data = array_merge(array(
			'access_level'	=> TITANIA_ACCESS_PUBLIC,
			'approved'		=> true,
			'reported'		=> false,
		), $data);

		$article->setState($data);

		if ($update)
		{
			self::$index->update($article);
		}
		else
		{
			self::$index->index($article);
		}

		unset($article);
	}

	/**
	* Faster way to index multiple items
	*
	* @param array $data 2 dimensional array containing an array of the data needed to index.  In the array for each item be sure to specify object_type and object_id
	*/
	public static function mass_index($data)
	{
		self::initialize();

		self::$index->beginTransaction();

		foreach ($data as $row)
		{
			$object_type = $row['object_type'];
			$object_id = $row['object_id'];
			unset($row['object_type'], $row['object_id']);

			self::index($object_type, $object_id, $row);
		}

		self::$index->commit();
	}

	/**
	* Update an item (shortcut for self::index($object_type, $object_id, $data, true))
	*
	* @param mixed $object_type The object_type (what this is set to is not entirely important, but must be the same for all items of that type)
	* @param int $object_id The object_id of an item (there can only be one of each id per object_type)
	* @param array $data Array of data (see titania_article)
	*/
	public static function update($object_type, $object_id, $data)
	{
		self::index($object_type, $object_id, $data, true);
	}

	/**
	* Delete an item
	*
	* @param mixed $object_type The object_type (what this is set to is not entirely important, but must be the same for all items of that type)
	* @param int $object_id The object_id of an item (there can only be one of each id per object_type)
	*/
	public static function delete($object_type, $object_id)
	{
		self::initialize();

		self::$index->deleteById($object_type . '_' . $object_id, $object_type);
	}

	/**
	* Truncate the entire search or a specific type
	*
	* @param mixed $object_type The object_type you would like to remove, false to truncate the entire search index
	*/
	public static function truncate($object_type = false)
	{
		self::initialize();

		$query = self::$index->createDeleteQuery('titania_article');

		if ($object_type !== false)
		{
			$query->where(
				$query->eq('type', $object_type)
			);
		}

		self::$index->delete($query);
	}

	/**
	* Perform a normal search
	*
	* @param string $search_query The user input for a search query
	* @param object|bool $pagination The pagination class
	* @param array $fields The fields to search
	*
	* @return The documents of the result
	*/
	public static function search($search_query, $pagination = false, $fields = array('text', 'title'))
	{
		self::initialize();

		$query = self::$index->createFindQuery('titania_article');
		$qb = new ezcSearchQueryBuilder();
		$qb->parseSearchQuery($query, $search_query, $fields);
		unset($qb);

		return self::custom_search($query, $pagination);
	}

	public static function author_search($user_id, $pagination = false)
	{
		self::initialize();

		$query = self::$index->createFindQuery('titania_article');
		$qb = new ezcSearchQueryBuilder();
		$qb->parseSearchQuery($query, $user_id, array('author'));
		unset($qb);

		return self::custom_search($query, $pagination);
	}

	/**
	* Perform a custom search (must build a createFindQuery for the query)
	*
	* @param object $query self::$index->createFindQuery
	* @param object|bool $pagination The pagination class
	*
	* @return The documents of the result
	*/
	public static function custom_search($query, $pagination = false)
	{
		self::initialize();

		if ($pagination === false)
		{
			// Setup the pagination tool
			$pagination = new titania_pagination();
			$pagination->default_limit = phpbb::$config['posts_per_page'];
			$pagination->request();
		}

		$query->offset = $pagination->start;
		$query->limit = $pagination->limit;

		$results = self::$index->find($query);

		return $results->documents;
	}
}

class titania_article implements ezcBasePersistable, ezcSearchDefinitionProvider
{
	public $id;
	public $title;
	public $text;
	public $date;
	public $author;
	public $url;
	public $type;
	public $access_level;
	public $approved;
	public $reported;

	public function __construct() {}

	public function getState()
	{
		$state = array(
			'id'			=> $this->id,
			'title'			=> $this->title,
			'text'			=> $this->text,
			'author'		=> (int) $this->author,
			'date'			=> (int) $this->date,
			'url'			=> $this->url,
			'type'			=> $this->type,
			'access_level'	=> (int) $this->access_level,
			'approved'		=> (int) $this->approved,
			'reported'		=> (int) $this->reported,
		);
		return $state;
	}

	public function setState(array $state)
	{
		foreach ($state as $key => $value)
		{
			$this->$key = $value;
		}
	}

	static public function getDefinition()
	{
		$doc = new ezcSearchDocumentDefinition( __CLASS__ );

		$doc->idProperty = 'id';

		$doc->fields['id']				= new ezcSearchDefinitionDocumentField('id', ezcSearchDocumentDefinition::TEXT);
		$doc->fields['type']			= new ezcSearchDefinitionDocumentField('type', ezcSearchDocumentDefinition::STRING, 0, true, false, false);

		$doc->fields['title']			= new ezcSearchDefinitionDocumentField('title', ezcSearchDocumentDefinition::TEXT, 2, true, false, true);
		$doc->fields['text']			= new ezcSearchDefinitionDocumentField('text', ezcSearchDocumentDefinition::TEXT, 1, true, false, true);
		$doc->fields['author']			= new ezcSearchDefinitionDocumentField('author', ezcSearchDocumentDefinition::INT);
		$doc->fields['date']			= new ezcSearchDefinitionDocumentField('date', ezcSearchDocumentDefinition::INT);
		$doc->fields['url']				= new ezcSearchDefinitionDocumentField('url', ezcSearchDocumentDefinition::STRING);

		$doc->fields['access_level']	= new ezcSearchDefinitionDocumentField('access_level', ezcSearchDocumentDefinition::INT);
		$doc->fields['approved']		= new ezcSearchDefinitionDocumentField('approved', ezcSearchDocumentDefinition::INT);
		$doc->fields['reported']		= new ezcSearchDefinitionDocumentField('reported', ezcSearchDocumentDefinition::INT);

		return $doc;
	}
}