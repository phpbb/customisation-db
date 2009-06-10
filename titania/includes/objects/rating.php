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

if (!class_exists('titania_database_object'))
{
	require TITANIA_ROOT . 'includes/core/object_database.' . PHP_EXT;
}

/**
* Class to abstract titania ratings.
* @package Titania
*/
class titania_rating extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table			= CDB_RATINGS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field			= 'rating_id';

	/**
	 * Rating Type
	 * Check Rating Type Constants
	 *
	 * @var int
	 */
	protected $rating_type_id		= 0;

	/**
	 * Cache Table
	 * Rating table where the cache will be stored
	 *
	 * @var string
	 */
	protected $cache_table			= '';

	/**
	 * Cache Rating
	 * The field to update with the rating value cache
	 *
	 * @var string
	 */
	protected $cache_rating			= '';

	/**
	 * Cache Rating Count
	 * The field to update with the rating count cache
	 *
	 * @var string
	 */
	protected $cache_rating_count	= '';

	/**
	 * Object column
	 * The rating item primary key field (ex: author_id for rating authors)
	 *
	 * @var string
	 */
	protected $object_column		= '';

	/**
	 * Object ID
	 * The rating item ID (ex: author_id for rating authors)
	 *
	 * @var int
	 */
	protected $object_id			= 0;

	/**
	 * Rating
	 * The rating of the item
	 *
	 * @var decimal
	 */
	protected $rating				= 0;

	/**
	 * Rating Count
	 * The number of ratings for the item
	 *
	 * @var int
	 */
	protected $rating_count			= 0;

	/**
	 * Constructor class for titania authors
	 *
	 * @param string $type The type of rating
	 * @param object $object The object we will be rating (author/contrib object)
	 */
	public function __construct($type, $object)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'rating_id'				=> array('default' => 0),
			'rating_type_id'		=> array('default' => 0),
			'rating_user_id'		=> array('default' => 0),
			'rating_object_id'		=> array('default' => 0),
			'rating_value'			=> array('default' => 0.0),
		));

		switch($type)
		{
			case 'author' :
				$this->rating_type_id = RATING_AUTHOR;
				$this->cache_table = CDB_AUTHORS_TABLE;
				$this->cache_rating = 'author_rating';
				$this->cache_rating_count = 'author_rating_count';
				$this->object_column = 'author_id';
			break;

			case 'contrib' :
				$this->rating_type_id = RATING_CONTRIB;
				$this->cache_table = CDB_CONTRIBS_TABLE;
				$this->cache_rating = 'contrib_rating';
				$this->cache_rating_count = 'contrib_rating_count';
				$this->object_column = 'contrib_id';
			break;
		}

		// Get the rating, rating count, and item id
		$this->rating = $object->{$this->cache_rating};
		$this->rating_count = $object->{$this->cache_rating_count};
		$this->object_id = $object->{$this->object_column};

		// Get the current user's rating (if any)
		$this->get_rating();
	}

	/**
	* Get the current user's rating
	*/
	public function get_rating()
	{
		if (!phpbb::$user->data['is_registered'])
		{
			return;
		}

		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE rating_type_id = ' . (int) $this->rating_type . '
			AND rating_user_id = ' . (int) phpbb::$user->data['user_id'] . '
			AND rating_object_id = ' . (int) $this->object_id;
		$result = phpbb::$db->sql_query($sql);
		$this->sql_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($row)
		{
			foreach ($this->sql_data as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

	/**
	* Get rating string
	*
	* @return string The rating string ready for output
	*/
	public function get_rating_string()
	{
	}

	/**
	* Add a Rating for an item
	*
	* @param mixed $rating The rating
	*/
	public function add_rating($rating)
	{
		if (!phpbb::$user->data['is_registered'] || !phpbb::$auth->acl_get('cdb_rate'))
		{
			return;
		}

		if ($rating < 0 || $rating > titania::$config['max_rating'])
		{
			return false;
		}

		$this->rating_value = $rating;

		parent::submit();

		// Resync the cache table
		$cnt = $total = 0;
		$sql = 'SELECT rating_value FROM ' . $this->sql_table . '
			WHERE rating_type_id = ' . (int) $this->rating_type . '
			AND rating_object_id = ' . (int) $this->object_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$cnt++;
			$total += $row['rating_value'];
		}
		phpbb::$db->sql_freeresult($result);

		$sql = 'UPDATE ' . $this->cache_table . ' SET ' .
			$this->cache_rating . ' = ' . round($total / $cnt, 2) . ', ' .
			$this->cache_rating_count . ' = ' . $cnt . '
			WHERE ' . $this->object_column . ' = ' . $this->object_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	* Delete the user's own rating
	*/
	public function delete_rating()
	{
		if (!phpbb::$user->data['is_registered'] || !$this->rating_id)
		{
			return;
		}

		parent::delete();

		// Resync the cache table
		$cnt = $total = 0;
		$sql = 'SELECT rating_value FROM ' . $this->sql_table . '
			WHERE rating_type_id = ' . (int) $this->rating_type . '
			AND rating_object_id = ' . (int) $this->object_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$cnt++;
			$total += $row['rating_value'];
		}
		phpbb::$db->sql_freeresult($result);

		$sql = 'UPDATE ' . $this->cache_table . ' SET ' .
			$this->cache_rating . ' = ' . round($total / $cnt, 2) . ', ' .
			$this->cache_rating_count . ' = ' . $cnt . '
			WHERE ' . $this->object_column . ' = ' . $this->object_id;
		phpbb::$db->sql_query($sql);
	}

	/**
	* Reset the rating for this object
	*/
	public function reset_rating()
	{
		if (!phpbb::$auth->acl_get('cdb_rate_reset'))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE rating_type_id = ' . (int) $this->rating_type . '
			AND rating_object_id = ' . (int) $this->object_id;
		phpbb::$db->sql_query($sql);

		$sql = 'UPDATE ' . $this->cache_table . ' SET ' .
			$this->cache_rating . ' = 0, ' .
			$this->cache_rating_count . ' = 0
			WHERE ' . $this->object_column . ' = ' . $this->object_id;
		phpbb::$db->sql_query($sql);
	}
}