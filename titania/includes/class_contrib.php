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
	 * Description parsed for storage
	 *
	 * @var bool
	 */
	private $description_parsed_for_storage = false;

	/**
	 * Current viewing page location
	 *
	 * @var string
	 */
	public $page;

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
	 * Submit data for storing into the database
	 *
	 * @return void
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
	 * Load function to load description parsed text
	 *
	 * @return void
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
	 * Get the author as an object
	 *
	 * @return titania_author
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
	 * @param bool $validated
	 *
	 * @return titania_download
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

	/**
	 * Function to list contribs for the selected type.
	 *
	 * @todo Hard-coding many actions, will then need to seperate these into their own functions/classes to be dynamically generated and scaleable
	 *
	 * @param string $contrib_type
	 */
	public function contrib_list($contrib_type)
	{
		global $db, $template, $user;

		// set an upper and lowercase contrib_type as well need each in multiple occurences.
		$l_contrib_type = strtolower($contrib_type);
		$u_contrib_type = strtoupper($contrib_type);

		if (!defined('CONTRIB_TYPE_' . $u_contrib_type))
		{
			trigger_error('NO_CONTRIB_TYPE');
		}

		$submit = isset($_REQUEST['submit']) ? true : false;

		if (!class_exists('sort'))
		{
			include(TITANIA_ROOT . 'includes/class_sort.' . PHP_EXT);
		}

		if (!class_exists('pagination'))
		{
			include(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);
		}

		$sort = new sort();
		$sort->set_sort_keys(array(
			'a'	=> array('SORT_AUTHOR',			'a.author_username_clean', 'default' => true),
			'b'	=> array('SORT_TIME_ADDED',		'c.contrib_release_date'),
			'c'	=> array('SORT_TIME_UPDATED',	'c.contrib_update_date'),
			'd'	=> array('SORT_DOWNLOADS',		'c.contrib_downloads'),
			'e'	=> array('SORT_RATING',			'c.contrib_rating'),
			'f'	=> array('SORT_CONTRIB_NAME',	'c.contrib_name'),
		));

		$sort->sort_request(false);

		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		// select the list of contribs
		$sql_ary = array(
			'SELECT'	=> 'a.author_id, a.author_username, c.*',
			'FROM'		=> array(
				CUSTOMISATION_CONTRIBS_TABLE => 'c',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_AUTHORS_TABLE => 'a'),
					'ON'	=> 'c.contrib_author_id = a.author_id'
				),
			),
			'WHERE'		=> 'contrib_status = ' . STATUS_APPROVED . '
						AND contrib_type = ' . constant('CONTRIB_TYPE_' . $u_contrib_type),
			'ORDER_BY'	=> $sort->get_order_by(),
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars($l_contrib_type, array(
				$u_contrib_type . '_ID'		=> $row['contrib_id'],
			));
		}
		$db->sql_freeresult($result);

		$pagination->sql_total_count($sql_ary, 'c.contrib_id');

		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(),
			'sd'	=> $sort->get_sort_dir(),
			'mode'	=> $mode,
		));

		$pagination->build_pagination(self::page);

		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));
	}

	// Get revision object
	/*public function get_revision($validated = true)
	{
		if (!class_exists('titania_revision'))
		{
			require(TITANIA_ROOT . 'includes/class_revision.' . PHP_EXT);
		}

		$revision_id = ($validated) ? $this->contrib_validated_revision : $this->contrib_revision;

		$revision = new titania_revision($revision_id);
		$revision->load();

		return $revision;
	}*/
}
