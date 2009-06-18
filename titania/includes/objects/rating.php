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
	protected $sql_table			= TITANIA_RATINGS_TABLE;

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
	 * The rating item primary key field (ex: user_id for rating authors)
	 *
	 * @var string
	 */
	protected $object_column		= '';

	/**
	 * Object ID
	 * The rating item ID (ex: user_id for rating authors)
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
	 * @param string $type The type of rating ('author', 'contrib')
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
				$this->rating_type_id = TITANIA_RATING_AUTHOR;
				$this->cache_table = TITANIA_AUTHORS_TABLE;
				$this->cache_rating = 'author_rating';
				$this->cache_rating_count = 'author_rating_count';
				$this->object_column = 'user_id';
			break;

			case 'contrib' :
				$this->rating_type_id = TITANIA_RATING_CONTRIB;
				$this->cache_table = TITANIA_CONTRIBS_TABLE;
				$this->cache_rating = 'contrib_rating';
				$this->cache_rating_count = 'contrib_rating_count';
				$this->object_column = 'contrib_id';
			break;
		}

		// Get the rating, rating count, and item id
		$this->rating = $object->{$this->cache_rating};
		$this->rating_count = $object->{$this->cache_rating_count};
		$this->object_id = $object->{$this->object_column};
	}

	/**
	* Get the current user's rating
	*/
	public function load()
	{
		if (!phpbb::$user->data['is_registered'] || phpbb::$user->data['is_bot'])
		{
			return;
		}

		$sql = 'SELECT * FROM ' . $this->sql_table . '
			WHERE rating_type_id = ' . (int) $this->rating_type_id . '
				AND rating_user_id = ' . (int) phpbb::$user->data['user_id'] . '
				AND rating_object_id = ' . (int) $this->object_id;
		$result = phpbb::$db->sql_query($sql);
		$this->sql_data = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($this->sql_data)
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
		$can_rate = (phpbb::$user->data['is_registered'] && phpbb::$auth->acl_get('titania_rate') && !$this->rating_id) ? true : false;
		$rate_url = titania_sid('rate', 'id=' . $this->object_id);

		// If it has not had any ratings yet, give it 1/2 the max for the rating
		if ($this->rating_count == 0)
		{
			$this->rating = round(titania::$config->max_rating / 2, 1);
		}

		// Go through and build the rating string
		$final_code = '<span id="rating_' . $this->object_id . '">';
		for ($i = 1; $i <= titania::$config->max_rating; $i++)
		{
			// Title will be $i/max if they've not rated it, rating/max if they have
			$title = $i . '/' . titania::$config->max_rating;

			$final_code .= ($can_rate) ? '<a href="' . $rate_url . '&amp;value=' . $i . '">' : '';
			$final_code .= '<img id="' . $this->object_id . '_' . $i . '" ';
			if ($this->rating_id && $i <= $this->rating) // If they have rated, show their own rating in green stars
			{
				$final_code .= 'src="' . titania::$theme_path . '/images/star_green.gif" ';
			}
			else if (!$this->rating_id && $i <= round($this->rating)) // Round because we only have full stars ATM, orange stars for the average rating (if the user has not rated)
			{
				$final_code .= 'src="' . titania::$theme_path . '/images/star_orange.gif" ';
			}
			else // show the rest in grey stars
			{
				$final_code .= 'src="' . titania::$theme_path . '/images/star_grey.gif" ';
			}
			$final_code .= ($can_rate) ? "onmouseover=\"ratingHover('{$i}', '{$this->object_id}')\"  onmouseout=\"ratingUnHover('{$this->rating}', '{$this->object_id}')\"  onmousedown=\"ratingDown('{$i}', '{$this->object_id}')\"" : '';
			$final_code .= ' alt="' . $title . '" title="' . $title . '" />';
			$final_code .= ($can_rate) ? '</a>' : '';
		}

		// If they have rated already we will add the remove rating icon at the end
		if ($this->rating_id)
		{
			$final_code .= ' <a href="' . $rate_url . '&amp;value=remove"><img id="' . $this->object_id . '_remove" src="' . titania::$theme_path . '/images/star_remove.gif"  alt="' . phpbb::$user->lang['REMOVE_RATING'] . '" title="' . phpbb::$user->lang['REMOVE_RATING'] . '" /></a>';
		}

		$final_code .= '</span>';

		return $final_code;
	}

	public function assign_common()
	{
		phpbb::$template->assign_vars(array(
			'UA_GREY_STAR_SRC'		=> titania::$theme_path . '/images/star_grey.gif',
			'UA_GREEN_STAR_SRC'		=> titania::$theme_path . '/images/star_green.gif',
			'UA_RED_STAR_SRC'		=> titania::$theme_path . '/images/star_red.gif',
			'UA_ORANGE_STAR_SRC'	=> titania::$theme_path . '/images/star_orange.gif',

			'UA_MAX_RATING'			=> titania::$config->max_rating,
		));
	}

	/**
	* Add a Rating for an item
	*
	* @param mixed $rating The rating
	*/
	public function add_rating($rating)
	{
		if (!phpbb::$user->data['is_registered'] || !phpbb::$auth->acl_get('titania_rate'))
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
			WHERE rating_type_id = ' . (int) $this->rating_type_id . '
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
			WHERE rating_type_id = ' . (int) $this->rating_type_id . '
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
		if (!phpbb::$auth->acl_get('titania_rate_reset'))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->sql_table . '
			WHERE rating_type_id = ' . (int) $this->rating_type_id . '
				AND rating_object_id = ' . (int) $this->object_id;
		phpbb::$db->sql_query($sql);

		$sql = 'UPDATE ' . $this->cache_table . ' SET ' .
			$this->cache_rating . ' = 0, ' .
			$this->cache_rating_count . ' = 0
			WHERE ' . $this->object_column . ' = ' . $this->object_id;
		phpbb::$db->sql_query($sql);
	}
}