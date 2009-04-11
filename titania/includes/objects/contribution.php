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

			'contrib_phpbb_version'			=> array('default' => 3),
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

		return sprintf(phpbb::$user->lang['DOWNLOADS_PER_DAY'], $this->contrib_downloads / ($time_elapsed / $day_seconds));
	}

	/**
	 * Gets phpBB version from integer
	 *	20 => 2.0.x
	 *	30 => 3.0.x
	 *	2 => 2.0.x
	 *	3 => 3.0.x
	 *	32 => 3.2.x
	 *	etc
	 *
	 * @return string
	 */
	public function get_phpbb_version()
	{
		$version = (string) $this->contrib_phpbb_version;

		if (!isset($version{1}))
		{
			$version{1} = 0;
		}

		return sprintf('%u.%u.x', $version{0}, $version{1});
	}

	/**
	* Immediately increases the view counter for this contribution
	*
	* @return void
	*/
	private function increase_view_counter()
	{
		$sql = 'UPDATE ' . $this->sql_table . '
			SET contrib_views = contrib_views + 1
			WHERE contrib_id = ' . $this->contrib_id;
		phpbb::$db->sql_query($sql);

		$this->contrib_views = $this->contrib_views + 1;
	}

	/**
	 * Recommend contribution to a friend
	 *
	 * @return bool		true if mail sent
	 */
	public function email_friend()
	{
		phpbb::$user->add_lang('memberlist');

		if (!phpbb::$config['email_enable'])
		{
			titania::error_box('ERROR', 'EMAIL_DISABLED', ERROR_ERROR, HEADER_SERVICE_UNAVAILABLE);

			return false;
		}

		if (!phpbb::$user->data['is_registered'] || phpbb::$user->data['is_bot'] || !phpbb::$auth->acl_get('u_sendemail'))
		{
			if (phpbb::$user->data['user_id'] == ANONYMOUS)
			{
				login_box(titania::$page, 'ERROR_CONTRIB_EMAIL_FRIEND');
			}

			titania::error_box('ERROR', 'ERROR_CONTRIB_EMAIL_FRIEND', ERROR_ERROR, HEADER_FORBIDDEN);

			return false;
		}

		// Are we trying to abuse the facility?
		if (titania::$time - phpbb::$user->data['user_emailtime'] < phpbb::$config['flood_interval'])
		{
			titania::trigger_error('FLOOD_EMAIL_LIMIT', E_USER_NOTICE, HEADER_SERVICE_UNAVAILABLE);
		}

		$name		= utf8_normalize_nfc(request_var('name', '', true));
		$email		= request_var('email', '');
		$email_lang	= request_var('lang', phpbb::$config['default_lang']);
		$message	= utf8_normalize_nfc(request_var('message', '', true));
		$cc			= (isset($_POST['cc_email'])) ? true : false;
		$submit		= (isset($_POST['submit'])) ? true : false;

		add_form_key('contrib_email');

		phpbb::$template->assign_vars(array(
			'S_LANG_OPTIONS'	=> language_select($email_lang),
			'S_POST_ACTION'		=> append_sid(titania::$page, array('id' => 'email', 'contrib_id' => $this->contrib_id)),
		));

		$error = array();

		if ($submit)
		{
			if (!check_form_key('contrib_email'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!$email || !preg_match('/^' . get_preg_expression('email') . '$/i', $email))
			{
				$error[] = 'EMPTY_ADDRESS_EMAIL';
			}

			if (!$name)
			{
				$error[] = 'EMPTY_NAME_EMAIL';
			}

			if (!empty($error))
			{
				titania::error_box('ERROR', $error, ERROR_ERROR);

				return false;
			}

			if (!class_exists('messenger'))
			{
				require PHPBB_ROOT_PATH . 'includes/functions_messenger.' . PHP_EXT;
			}

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_emailtime = ' . titania::$time . '
				WHERE user_id = ' . (int) phpbb::$user->data['user_id'];
			$result = phpbb::$db->sql_query($sql);

			$mail_to_users = array();
			$mail_to_users[] = array(
				'email_lang'	=> $email_lang,
				'email'			=> $email,
				'name'			=> $name,
			);

			// Ok, now the same email if CC specified, but without exposing the users email address
			if ($cc)
			{
				$mail_to_users[] = array(
					'email_lang'	=> phpbb::$user->data['user_lang'],
					'email'			=> phpbb::$user->data['user_email'],
					'name'			=> phpbb::$user->data['username'],
				);
			}

			$lang_path = phpbb::$user->lang_path;
			phpbb::$user->set_custom_lang_path(titania::$config->language_path);

			$messenger = new messenger(false);

			foreach ($mail_to_users as $row)
			{
				$messenger->template('contrib_recommend', $row['email_lang']);
				$messenger->replyto(phpbb::$user->data['user_email']);
				$messenger->to($row['email'], $row['name']);

				$messenger->headers('X-AntiAbuse: Board servername - ' . phpbb::$config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . (int) phpbb::$user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . phpbb::$user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . phpbb::$user->ip);

				$messenger->assign_vars(array(
					'BOARD_CONTACT'	=> phpbb::$config['board_contact'],
					'TO_USERNAME'	=> htmlspecialchars_decode($name),
					'FROM_USERNAME'	=> htmlspecialchars_decode(phpbb::$user->data['username']),
					'MESSAGE'		=> htmlspecialchars_decode($message),

					'CONTRIB_TITLE'	=> htmlspecialchars_decode($this->contrib_name),
					'U_CONTRIB'		=> append_sid(titania::$page, array('contrib_id' => $this->contrib_id, 'id' => 'details'), true, ''),
				));

				$messenger->send(NOTIFY_EMAIL);
			}

			phpbb::$user->set_custom_lang_path($lang_path);

			titania::error_box('SUCCESS', 'EMAIL_SENT', ERROR_SUCCESS);

			return true;
		}

		return false;
	}

	/**
	 * Passes common variables to the template
	 *
	 * @return void
	 */
	public function assign_common()
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
			// General urls
			'U_CONTRIB_DETAILS'			=> append_sid(titania::$page, array('id' => 'details', 'contrib_id' => $this->contrib_id)),
			'U_CONTRIB_SUPPORT'			=> append_sid(titania::$page, array('id' => 'support', 'contrib_id' => $this->contrib_id)),
			'U_CONTRIB_SCREENSHOTS'		=> append_sid(titania::$page, array('id' => 'screenshots', 'contrib_id' => $this->contrib_id)),
			'U_CONTRIB_FAQ'				=> append_sid(titania::$page, array('id' => 'faq', 'contrib_id' => $this->contrib_id)),

			// Contribution data
			'CONTRIB_TITLE'				=> $this->contrib_name,
		));
	}

	/**
	 * Passes details to the template
	 *
	 * @return void
	 */
	public function assign_details()
	{
		phpbb::$template->assign_vars(array(
			// Author data
			'AUTHOR_NAME'				=> $this->author->author_username,
			'AUTHOR_REALNAME'			=> $this->author->author_realname,

			'U_AUTHOR_PROFILE'				=> $this->author->get_profile_url(),
			'U_AUTHOR_PROFILE_PHPBB'		=> $this->author->get_phpbb_profile_url(),
			'U_AUTHOR_PROFILE_PHPBB_COM'	=> $this->author->get_phpbb_com_profile_url(),

			// Contribution data
			'CONTRIB_DESC'				=> $this->generate_text_for_display(),

			'CONTRIB_VIEWS'				=> $this->contrib_views,
			'CONTRIB_DOWNLOADS'			=> $this->contrib_downloads, // Total downloads
			'CONTRIB_DOWNLOADS_PER_DAY'	=> $this->get_downloads_per_day(),

			'CONTRIB_RATING'			=> sprintf(phpbb::$user->lang['RATING_OUT_OF_FIVE'], $this->contrib_rating),
			'CONTRIB_RATINGS'			=> $this->contrib_rating_count,

			'CONTRIB_VERSION'			=> $this->contrib_version,
			'CONTRIB_PHPBB_VERSION'		=> $this->get_phpbb_version(),

			'CONTRIB_RELEASE_DATE'		=> phpbb::$user->format_date($this->contrib_release_date),
			'CONTRIB_UPDATE_DATE'		=> ($this->contrib_update_date > $this->contrib_release_date) ? phpbb::$user->format_date($this->contrib_update_date) : '',

			'U_CONTRIB_DOWNLOAD'		=> $this->get_download_url(),

			// Download data
			'DOWNLOAD_SIZE'				=> get_formatted_filesize($this->download->filesize),
			'DOWNLOAD_CHECKSUM'			=> $this->download->download_hash,
			'DOWNLOAD_COUNT'			=> $this->download->download_count, // Revision downloads
		));

		if (!phpbb::$user->data['is_bot'])
		{
			$this->increase_view_counter();
		}
	}
}
