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
* Class to abstract contribution reviews.
* @package Titania
*/
abstract class titania_contribution extends titania_database_object
{
	// SQL settings
	protected $sql_table		= CDB_CONTRIBS_TABLE;
	protected $sql_id_field		= 'contrib_id';

	// Additional attributes
	private $description_parsed_for_storage = false;

	// Constructor
	public function __construct($contrib_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'contrib_id'					=> array('default' => 0),
			'contrib_type'					=> array('default' => 0),
			'contrib_name'					=> array('default' => '',	'max' => 255),

			'contrib_description'			=> array('default' => ''),
			'contrib_desc_bitfield'			=> array('default' => '',	'readonly' => true),
			'contrib_desc_uid'				=> array('default' => '',	'readonly' => true),
			'contrib_desc_options'			=> array('default' => 7,	'readonly' => true),

			'contrib_status'				=> array('default' => 0),
			'contrib_version'				=> array('default' => '',	'max' => 15),

			'contrib_revision'				=> array('default' => 0),
			'contrib_validated_revision'	=> array('default' => 0),

			'contrib_author_id'				=> array('default' => 0),
			'contrib_maintainer'			=> array('default' => 0),

			'contrib_downloads'				=> array('default' => 0),
			'contrib_views'					=> array('default' => 0),

			'contrib_phpbb_version'			=> array('default' => 0),
			'contrib_release_date'			=> array('default' => 0),
			'contrib_update_date'			=> array('default' => 0),
			'contrib_visibility'			=> array('default' => 0),

			'contrib_rating'				=> array('default' => 0.0),
			'contrib_rating_count'			=> array('default' => 0),

			'contrib_demo'					=> array('default' => '',	'max' => 255,	'multibyte' => false),
		));

		if ($contrib_id === false)
		{
			$this->contrib_id = time();
		}
		else
		{
			$this->contrib_id = $review_id;
		}
	}

	// Update data or submit new contrib
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->description_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		parent::submit();
	}

	// Get review data from the database
	public function load()
	{
		parent::load();

		$this->description_parsed_for_storage = true;
	}
	
	// Parse description for db
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		$contrib_description = $this->contrib_description;
		$contrib_desc_uid = $this->contrib_desc_uid;
		$contrib_desc_bitfield = $this->contrib_desc_bitfield;
		$contrib_desc_options = $this->contrib_desc_options;

		generate_text_for_storage($contrib_description, $contrib_desc_uid, $contrib_desc_bitfield, $contrib_desc_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->contrib_description = $contrib_description;
		$this->contrib_desc_uid = $contrib_desc_uid;
		$this->contrib_desc_bitfield = $contrib_desc_bitfield;
		$this->contrib_desc_options = $contrib_desc_options;

		$this->text_parsed_for_storage = true;
	}

	// Parse description for display
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->contrib_description, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options);
	}

	// Parse description for edit
	private function generate_text_for_edit()
	{
		return generate_text_for_edit($this->contrib_description, $this->contrib_desc_uid, $this->contrib_desc_options);
	}

	// Get the author as an object
	public function get_author()
	{
		if (!class_exists('titania_author'))
		{
			require($phpbb_root_path . 'includes/titania/class_author.' . $phpEx);
		}

		$author = new titania_author($this->contrib_author_id);
		$author->load();

		return $author;
	}

	// Get the download as an object
	public function get_download($validated = true)
	{
		if (!class_exists('titania_download'))
		{
			require($phpbb_root_path . 'includes/titania/class_download.' . $phpEx);
		}

		$revision_id = ($validated) ? $this->contrib_validated_revision : $this->contrib_revision;

		$download = new titania_download();
		$download->load($revision_id);

		return $download;
	}


	// Get revision object
	/*public function get_revision($validated = true)
	{
		if (!class_exists('titania_revision'))
		{
			require($phpbb_root_path . 'includes/titania/class_revision.' . $phpEx);
		}

		$revision_id = ($validated) ? $this->contrib_validated_revision : $this->contrib_revision;

		$revision = new titania_revision($revision_id);
		$revision->load();

		return $revision;
	}*/
}