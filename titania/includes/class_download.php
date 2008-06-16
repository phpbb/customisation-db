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
if (!defined('IN_PHPBB') || !defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_database_object'))
{
	require(TITANIA_ROOT . 'includes/titania/class_base_db_object.' . $phpEx);
}

/**
* Class to abstract titania downloads.
* @package Titania
*/
class titania_download extends titania_database_object
{
	// SQL settings
	protected $sql_table		= CUSTOMISATION_DOWNLOADS_TABLE;
	protected $sql_id_field		= 'download_id';

	// Constructor
	public function __construct($download_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'download_id'			=> array('default' => 0),
			'revision_id'			=> array('default' => 0),

			'download_type'			=> array('default' => 0),
			'download_status'		=> array('default' => 0),

			'filesize'				=> array('default' => 0),
			'filetime'				=> array('default' => 0),

			'physical_filename'		=> array('default' => '',	'max' => 255),
			'real_filename'			=> array('default' => '',	'max' => 255),

			'download_count'		=> array('default' => 0),

			'extension'				=> array('default' => '', 'max' => 100),
			'mimetype'				=> array('default' => '', 'max' => 100),

			'download_url'			=> array('default' => '',	'max' => 255	'multibyte' => false),
			'download_hash'			=> array('default' => '',	'max' => 32,	'readonly' => true),

			'thumbnail'				=> array('default' => false, 'type' => 'int'),
		));

		if ($download_id === false)
		{
			$this->filetime = time();
		}
		else
		{
			$this->download_id = $download_id;
		}
	}

	// Allows to load data identified by revision_id
	public function load($revision_id = false)
	{
		if ($revision_id === false)
		{
			parent::load();
		}
		else
		{
			$identifier = $this->sql_id_field;

			$this->sql_id_field = 'revision_id';
			$this->revision_id = $revision_id;

			parent::load();

			$this->sql_id_field = $identifier;
		}
	}
}