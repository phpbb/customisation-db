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
set_include_path(get_include_path() . ';' . TITANIA_ROOT . 'includes/library/');

// Using the phpBB ezcomponents loader
if (!class_exists('phpbb_ezcomponents_loader'))
{
	include(TITANIA_ROOT . 'includes/library/ezcomponents/loader.' . PHP_EXT);
}
$loader = new phpbb_ezcomponents_loader();
$loader->load_component('search');
unset($loader);

include('Zend/Search/Lucene.' . PHP_EXT);

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

	public static function index($data)
	{
		self::initialize();

		if (is_object($data))
		{
			self::$index->index($data);

			return true;
		}
		else if (is_array($data) && isset($data['id']))
		{
			$id = (int) $data['id'];
			$title = ((isset($data['title'])) ? $data['title'] : '');
			$text = ((isset($data['text'])) ? $data['text'] : '');
			$date = (int) ((isset($data['date'])) ? $data['date'] : '');
			$url = ((isset($data['url'])) ? $data['url'] : '');
			$type = ((isset($data['type'])) ? $data['type'] : '');

			$article = new titania_article($id, $title, $text, $date, $url, $type);

			self::$index->index($article);

			return true;
		}

		return false;
	}

	public static function search($query)
	{
		$q = self::$index->createFindQuery('titania_article');
		$qb = new  ezcSearchQueryBuilder();
		$qb->parseSearchQuery( $q, $query, array( 'text', 'title' ) );

		$r = self::$index->find(  $q );

		foreach( $r->documents  as $res )
		{
			echo $res->document->title, "\n";
		}
	}
}

class titania_article implements ezcBasePersistable, ezcSearchDefinitionProvider
{
	public $id;
	public $title;
	private $text;
	private $date;
	private $url;
	private $type;

	function __construct($id = null, $title = null, $text = null, $date = null, $url = null, $type = null)
	{
		$this->id = $id;
		$this->title = $title;
		$this->text = $text;
		$this->date = $date;
		$this->url = $url;
		$this->type = $type;
	}

	function getState()
	{
		$state = array(
			'id'		=> $this->id,
			'title'		=> $this->title,
			'text'		=> $this->text,
			'date'		=> $this->date,
			'url'		=> $this->url,
			'type'		=> $this->type,
		);
		return $state;
	}

	function setState(array $state)
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

		$doc->fields['id']		= new ezcSearchDefinitionDocumentField('id', ezcSearchDocumentDefinition::INT);
		$doc->fields['title']	= new ezcSearchDefinitionDocumentField('title', ezcSearchDocumentDefinition::TEXT, 2, true, false, true);
		$doc->fields['text']	= new ezcSearchDefinitionDocumentField('text', ezcSearchDocumentDefinition::TEXT, 1, true, false, true);
		$doc->fields['date']	= new ezcSearchDefinitionDocumentField('date', ezcSearchDocumentDefinition::DATE);
		$doc->fields['url']		= new ezcSearchDefinitionDocumentField('url', ezcSearchDocumentDefinition::STRING);
		$doc->fields['type']	= new ezcSearchDefinitionDocumentField('type', ezcSearchDocumentDefinition::STRING, 0, true, false, false);

		return $doc;
	}
}