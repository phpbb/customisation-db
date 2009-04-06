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
* Class to abstract titania authors.
* @package Titania
*/
class titania_author extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_AUTHORS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'author_id';

	/**
	 * Constructor class for titania authors
	 *
	 * @param int $author_id
	 */
	public function __construct($author_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'author_id'				=> array('default' => 0),
			'user_id'				=> array('default' => 0),
			'phpbb_user_id'			=> array('default' => 0),

			'author_username'		=> array('default' => '',	'max' => 255),
			'author_username_clean'	=> array('default' => '',	'max' => 255,	'readonly' => true),
			'author_realname'		=> array('default' => '',	'max' => 255),
			'author_website'		=> array('default' => '',	'max' => 200),
			'author_email'			=> array('default' => '',	'multibyte' => false),
			'author_email_hash'		=> array('default' => 0,	'readonly' => true),
			'author_rating'			=> array('default' => 0.0),
			'author_rating_count'	=> array('default' => 0),

			'author_contribs'		=> array('default' => 0),
			'author_snippets'		=> array('default' => 0),
			'author_mods'			=> array('default' => 0),
			'author_styles'			=> array('default' => 0),
			'author_visible'		=> array('default' => AUTHOR_VISIBLE),
		));

		if ($author_id !== false)
		{
			$this->author_id = $author_id;
		}
	}

	/**
	 * Add rating
	 *
	 * @return void
	 */
	public function add_rating($rating)
	{
		$points_current = $this->author_rating * $this->author_rating_count;

		$this->author_rating_count = $this->author_rating_count + 1;
		$this->author_rating = ($points_current + $rating) / $this->author_rating_count;

		$this->update();
	}

	/**
	 * Special setter methods overwriting the default magic methods.
	 *
	 * @param string $value
	 */
	public function set_author_username($value)
	{
		$this->author_username			= $value;
		$this->author_username_clean	= utf8_clean_string($value);
	}

	/**
	 * set author e-mail
	 *
	 * @param string $value
	 */
	public function set_author_email($value)
	{
		$lower = strtolower($value);

		$this->author_email			= $lower;
		$this->author_email_hash	= crc32($lower) . strlen($lower);
	}

	/**
	 * Get profile url
	 *
	 * @return string
	 */
	public function get_profile_url()
	{
		return append_sid(TITANIA_ROOT . 'authors/index.' . PHP_EXT, 'a=' . $this->author_id);
	}

	/**
	 * Get phpBB profile url
	 *
	 * @return string
	 */
	public function get_phpbb_profile_url()
	{
		if ($this->user_id)
		{
			return append_sid(PHPBB_ROOT_PATH . 'memberlist.' . PHP_EXT, 'u=' . $this->user_id);
		}

		return '';
	}

	/**
	 * Get phpBB.com profile url
	 *
	 * @return string
	 */
	public function get_phpbb_com_profile_url()
	{
		if (titania::$config->phpbbcom_profile && $this->phpbb_user_id)
		{
			return sprintf(titania::$config->phpbbcom_viewprofile_url, $this->phpbb_user_id);
		}

		return '';
	}
}