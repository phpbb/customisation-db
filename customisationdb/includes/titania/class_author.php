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
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('titania_database_object'))
{
	require($phpbb_root_path . 'includes/titania/class_base_db_object.' . $phpEx);
}

/**
* Class to abstract titania authors.
* @package Titania
*/
class titania_author extends titania_database_object
{
	// SQL settings
	protected $sql_table		= CDB_AUTHORS_TABLE;
	protected $sql_id_field		= 'author_id';

	// Constructor
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
		));

		if ($author_id !== false)
		{
			$this->author_id = $author_id;
		}
	}

	// Special setter methods overwriting the default magic methods.
	public function set_author_username($value)
	{
		$this->author_username			= $value;
		$this->author_username_clean	= utf8_clean_string($value);
	}

	public function set_author_email($value)
	{
		$lower = strtolower($value);

		$this->author_email			= $lower;
		$this->author_email_hash	= crc32($lower) . strlen($lower);
	}
}