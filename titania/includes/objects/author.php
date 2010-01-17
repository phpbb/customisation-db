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
	protected $sql_table		= TITANIA_AUTHORS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'user_id';

	/**
	 * Description parsed for storage
	 *
	 * @var bool
	 */
	private $description_parsed_for_storage = false;

	/**
	 * Rating of this author
	 *
	 * @var titania_rating
	 */
	public $rating;

	/**
	 * Constructor class for titania authors
	 *
	 * @param int $author_id
	 */
	public function __construct($user_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'user_id'				=> array('default' => 0),
			'phpbb_user_id'			=> array('default' => 0),

			'author_realname'		=> array('default' => '',	'max' => 255),
			'author_website'		=> array('default' => '',	'max' => 200),
			'author_rating'			=> array('default' => 0.0),
			'author_rating_count'	=> array('default' => 0),

			'author_contribs'		=> array('default' => 0),
			'author_visible'		=> array('default' => TITANIA_AUTHOR_VISIBLE),

			'author_desc'			=> array('default' => ''),
			'author_desc_bitfield'	=> array('default' => ''),
			'author_desc_uid'		=> array('default' => ''),
			'author_desc_options'	=> array('default' => 7),
		));

		// Load the count for different types
		foreach (titania_types::$types as $type)
		{
			$this->object_config[$type->author_count] = array('default' => 0);
		}

		if ($user_id !== false)
		{
			$this->user_id = (int) $user_id;
		}
	}

	/**
	* Load Author
	*
	* @param mixed $user The user name/user id to load, false to use the already given user_id
	*/
	public function load($user = false)
	{
		if ($user === false)
		{
			$sql_where = 'u.user_id = ' . $this->user_id;
		}
		else
		{
			if (!is_numeric($user))
			{
				$sql_where = 'u.username_clean = \'' . phpbb::$db->sql_escape(utf8_clean_string($user)) . '\'';
			}
			else
			{
				$sql_where = 'u.user_id = ' . (int) $user;
			}
		}

		$sql_ary = array(
			'SELECT' => 'a.*, u.*', // Don't change to *!
			'FROM'		=> array(
				USERS_TABLE => 'u',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->sql_table => 'a'),
					'ON'	=> 'a.user_id = u.user_id'
				),
			),
			'WHERE'		=> $sql_where
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query($sql);

		if(!($this->sql_data = phpbb::$db->sql_fetchrow($result)))
		{
			return false;
		}

		$this->__set_array($this->sql_data);

		return true;
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

	public function validate()
	{
		$error = array();

		if ($this->author_website && !preg_match('#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i', $this->author_website))
		{
			$error[] = phpbb::$user->lang['WRONG_DATA_WEBSITE'];
		}

		return $error;
	}

	/**
	* Submit data in the post_data format (from includes/tools/message.php)
	*
	* @param object $message The message object
	*/
	public function post_data($message)
	{
		$post_data = $message->request_data();

		$this->__set_array(array(
			'author_desc'		=> $post_data['message'],
		));

		$this->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);
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
		generate_text_for_storage($this->author_desc, $this->author_desc_uid, $this->author_desc_bitfield, $this->author_desc_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->description_parsed_for_storage = true;
	}

	/**
	 * Parse description for display
	 *
	 * @return string
	 */
	public function generate_text_for_display()
	{
		return generate_text_for_display($this->author_desc, $this->author_desc_uid, $this->author_desc_bitfield, $this->author_desc_options);
	}

	/**
	 * Parse description for edit
	 *
	 * @return string
	 */
	public function generate_text_for_edit()
	{
		return array_merge(generate_text_for_edit($this->author_desc, $this->author_desc_uid, $this->author_desc_options), array(
			'object_type'	=> TITANIA_AUTHOR,
			'object_id'		=> $this->user_id,
		));
	}

	/**
	 * Get the rating as an object
	 *
	 * @return titania_rating
	 */
	public function get_rating()
	{
		if ($this->rating)
		{
			return $this->rating;
		}

		$this->rating = new titania_rating('author', $this);
		$this->rating->load();

		return $this->rating;
	}

	/**
	* Get username string
	*
	* @param mixed $mode Can be titania (for full with author view page for link), profile (for getting an url to the profile), username (for obtaining the username), colour (for obtaining the user colour), full (for obtaining a html string representing a coloured link to the users profile) or no_profile (the same as full but forcing no profile link)
	*
	* @return string username string
	*/
	public function get_username_string($mode = 'titania')
	{
		if ($mode == 'titania')
		{
			return '<a href="' . $this->get_url() . '">' . get_username_string('no_profile', $this->user_id, $this->username, $this->user_colour) . '</a>';
		}

		return get_username_string($mode, $this->user_id, $this->username, $this->user_colour);
	}

	/**
	 * Get profile url
	 *
	 * @param string $page The page we are on (Ex: faq/support/details)
	 *
	 * @return string
	 */
	public function get_url($page = '')
	{
		if ($page)
		{
			return titania_url::build_url('author/' . $this->username_clean . '/' . $page);
		}

		return titania_url::build_url('author/' . $this->username_clean);
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
			return phpbb::append_sid('memberlist', 'mode=viewprofile&amp;u=' . $this->user_id);
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

	/**
	 * Get correct website url
	 *
	 * @return string
	 */
	public function get_website_url()
	{
		if (!$this->author_website || strpos($this->author_website, 'http://') !== false)
		{
			return $this->author_website;
		}

		return 'http://' . $this->author_website;
	}

	/**
	 * Passes details to the template
	 *
	 * @param bool $return True if you want the data prepared for output and returned as an array, false to output to the template
	 */
	public function assign_details($return = false, $row= false, $revert = true)
	{
		// Set special data to display
		if ($row !== false)
		{
			if ($revert)
			{
				$backup = $this->object_data;
			}
			$this->__set_array($row);
		}

		$vars = array(
			'AUTHOR_NAME'					=> $this->username,
			'AUTHOR_NAME_FULL'				=> $this->get_username_string(),
			'AUTHOR_REALNAME'				=> $this->author_realname,
			'AUTHOR_WEBSITE'				=> $this->get_website_url(),
			'AUTHOR_WEBSITE_LINK'			=> '<a href="' . $this->get_website_url() . '">' . $this->get_website_url() . '</a>',

			'AUTHOR_RATING'					=> $this->author_rating,
			'AUTHOR_RATING_STRING'			=> (isset($this->rating)) ? $this->rating->get_rating_string() : '',
			'AUTHOR_RATING_COUNT'			=> $this->author_rating_count,

			'AUTHOR_CONTRIBS'				=> $this->author_contribs,

            'AUTHOR_DESC'                   => $this->generate_text_for_display(),

			'U_MANAGE_AUTHOR'				=> (phpbb::$user->data['user_id'] == $this->user_id  || phpbb::$auth->acl_get('titania_author_mod')) ? $this->get_url('manage') : '',
			'U_AUTHOR_PROFILE'				=> $this->get_url(),
			'U_AUTHOR_PROFILE_PHPBB'		=> $this->get_phpbb_profile_url(),
			'U_AUTHOR_PROFILE_PHPBB_COM'	=> $this->get_phpbb_com_profile_url(),
			'U_AUTHOR_CONTRIBUTIONS'		=> $this->get_url('contributions'),
		);

		// Output the count for different types
		foreach (titania_types::$types as $type)
		{
			phpbb::$template->assign_block_vars('type_counts', array(
				'NAME'	=> (isset(phpbb::$user->lang[strtoupper($type->author_count)])) ? phpbb::$user->lang[strtoupper($type->author_count)] : strtoupper($type->author_count),
				'VALUE'	=> $this->{$type->author_count}
			));
		}

		/* @todo: automatically display the common author data too...
		if (isset($this->sql_data))
		{
			$vars = array_merge($vars, assign_user_details($this->sql_data));
		}*/

		// Revert data
		if ($revert && $row !== false)
		{
			$this->__set_array($backup);
			unset($backup);
		}

		if ($return)
		{
			return $vars;
		}

		phpbb::$template->assign_vars($vars);
	}

}