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
	require(TITANIA_ROOT . 'includes/class_base_db_object.' . PHP_EXT);
}

/**
 * Class to abstract contributions.
 * @package Titania
 */
abstract class titania_contribution extends titania_database_object
{
	/**
	 * Database table to be used for the contribution object
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_CONTRIBS_TABLE;

	/**
	 * sql-in-set field for the contribution object
	 *
	 * @var unknown_type
	 */
	protected $sql_id_field		= 'contrib_id';

	/**
	 * description parsed for storage
	 *
	 * @var bool
	 */
	private $description_parsed_for_storage = false;

	/**
	 * Constructor class for the contribution object
	 *
	 * @param int $contrib_id
	 */
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

	/**
	 * submit data for storing into the database
	 *
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->description_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		parent::submit();
	}

	/**
	 * load function to load description parsed text
	 *
	 */
	public function load()
	{
		parent::load();

		$this->description_parsed_for_storage = true;
	}

	/**
	 * Generate text for storing description into the database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 */
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

	/**
	 * Parse description for display
	 *
	 * @return unknown
	 */
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->contrib_description, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options);
	}

	/**
	 *  Parse description for edit
	 *
	 * @return unknown
	 */
	private function generate_text_for_edit()
	{
		return generate_text_for_edit($this->contrib_description, $this->contrib_desc_uid, $this->contrib_desc_options);
	}

	/**
	 * Get the author as an object
	 *
	 * @return unknown
	 */
	public function get_author()
	{
		if (!class_exists('titania_author'))
		{
			require(TITANIA_ROOT . 'includes/class_author.' . PHP_EXT);
		}

		$author = new titania_author($this->contrib_author_id);
		$author->load();

		return $author;
	}

	/**
	 * Get the download as an object
	 *
	 * @param unknown_type $validated
	 * @return unknown
	 */
	public function get_download($validated = true)
	{
		if (!class_exists('titania_download'))
		{
			require(TITANIA_ROOT . 'includes/class_download.' . PHP_EXT);
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