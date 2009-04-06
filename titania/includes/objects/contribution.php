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
 * Class to abstract contributions.
 * @package Titania
 */
class titania_contribution extends titania_database_object
{
	/**
	 * Database table to be used for the contribution object
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_CONTRIBS_TABLE;

	/**
	 * Primary sql identifier for the contribution object
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'contrib_id';

	/**
	 * Author of this contribution
	 *
	 * @var titania_author
	 */
	protected $author;

	/**
	 * Download for this contribution
	 *
	 * @var titania_download
	 */
	protected $download;

	/**
	 * Description parsed for storage
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

			'contrib_status'				=> array('default' => STATUS_NEW),
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

		if ($contrib_id !== false)
		{
			$this->contrib_id = $contrib_id;
		}
	}

	/**
	 * Submit data for storing into the database
	 *
	 * @return bool
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->description_parsed_for_storage)
		{
			$this->generate_text_for_storage(false, false, false);
		}

		return parent::submit();
	}

	/**
	 * Load function to load description parsed text
	 *
	 * @return bool
	 */
	public function load()
	{
		$status = parent::load();

		if ($status)
		{
			$this->description_parsed_for_storage = true;
		}

		return $status;
	}

	/**
	 * Generate text for storing description into the database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 *
	 * @return void
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
	 * @return string
	 */
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->contrib_description, $this->contrib_desc_uid, $this->contrib_desc_bitfield, $this->contrib_desc_options);
	}

	/**
	 * Parse description for edit
	 *
	 * @return string
	 */
	private function generate_text_for_edit()
	{
		return generate_text_for_edit($this->contrib_description, $this->contrib_desc_uid, $this->contrib_desc_options);
	}

	/**
	 * Add rating
	 *
	 * @return void
	 */
	public function add_rating($rating)
	{
		$points_current = $this->contrib_rating * $this->contrib_rating_count;

		$this->contrib_rating_count = $this->contrib_rating_count + 1;
		$this->contrib_rating = ($points_current + $rating) / $this->contrib_rating_count;

		$this->update();
	}

	/**
	 * Get the author as an object
	 *
	 * @return titania_author
	 */
	public function get_author()
	{
		if (!class_exists('titania_author'))
		{
			require TITANIA_ROOT . 'includes/objects/author.' . PHP_EXT;
		}

		$author = new titania_author($this->contrib_author_id);
		$author->load();

		return $author;
	}

	/**
	 * Get the download as an object
	 *
	 * @param bool $validated
	 *
	 * @return titania_download
	 */
	public function get_download($validated = true)
	{
		if (!class_exists('titania_download'))
		{
			require TITANIA_ROOT . 'includes/objects/download.' . PHP_EXT;
		}

		$revision_id = ($validated) ? $this->contrib_validated_revision : $this->contrib_revision;

		$download = new titania_download();
		$download->load($revision_id);

		return $download;
	}

	/*
	 * Get download URL
	 *
	 * @return string
	 */
	public function get_download_url()
	{
		return append_sid(TITANIA_ROOT . 'download/file.' . PHP_EXT, 'contrib_id=' . $this->contrib_id);
	}

	/**
	 * Get downloads per day
	 *
	 * @return string
	 */
	public function get_downloads_per_day()
	{
		static $day_seconds = 86400; // 24 * 60 * 60

		// Cannot calculate anything without release date
		// No point in showing this if there were no downloads 
		if (!$this->contrib_release_date || !$this->contrib_downloads)
		{
			return '';
		}

		$time_elapsed = titania::$time - $this->contrib_release_date;

		// The release was just today, show nothing.
		if ($time_elapsed <= $day_seconds)
		{
			return '';
		}

		return sprintf('%.2f', $this->contrib_downloads / ($time_elapsed / $day_seconds));
	}

	/**
	 * Passes details to the template
	 *
	 * @return void
	 */
	public function show_details()
	{
		if (!$this->author)
		{
			$this->author = $this->get_author();
		}

		if (!$this->download)
		{
			$this->download = $this->get_download();
		}

		phpbb::$template->assign_vars(array(
			// Author data
			'AUTHOR_NAME'				=> $this->author->author_username,
			'AUTHOR_REALNAME'			=> $this->author->author_realname,

			'U_AUTHOR_PROFILE'				=> $this->author->get_profile_url(),
			'U_AUTHOR_PROFILE_PHPBB'		=> $this->author->get_phpbb_profile_url(),
			'U_AUTHOR_PROFILE_PHPBB_COM'	=> $this->author->get_phpbb_com_profile_url(),

			// Contribution data
			'CONTRIB_NAME'				=> $this->contrib_name,
			'CONTRIB_DESC'				=> $this->generate_text_for_display(),
			'CONTRIB_TYPE'				=> $this->contrib_type,

			'CONTRIB_VIEWS'				=> $this->contrib_views,
			'CONTRIB_DOWNLOADS'			=> $this->contrib_downloads, // Total downloads
			'CONTRIB_DOWNLOADS_PER_DAY'	=> $this->get_downloads_per_day(),

			'CONTRIB_RATING'			=> $this->contrib_rating,
			'CONTRIB_RATINGS'			=> $this->contrib_rating_count,

			'CONTRIB_VERSION'			=> $this->contrib_version,
			'CONTRIB_PHPBB_VERSION'		=> $this->contrib_phpbb_version,

			'CONTRIB_RELEASE_DATE'		=> phpbb::$user->format_date($this->contrib_release_date),
			'CONTRIB_UPDATE_DATE'		=> phpbb::$user->format_date($this->contrib_update_date),

			'U_CONTRIB_DOWNLOAD'		=> $this->get_download_url(),

			// Download data
			'DOWNLOAD_SIZE'				=> get_formatted_filesize($this->download->filesize),
			'DOWNLOAD_CHECKSUM'			=> $this->download->download_hash,
			'DOWNLOAD_COUNT'			=> $this->download->download_count, // Revision downloads
		));
	}
}
