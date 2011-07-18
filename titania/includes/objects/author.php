<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_message_object'))
{
	require TITANIA_ROOT . 'includes/core/object_message.' . PHP_EXT;
}

/**
* Class to abstract titania authors.
* @package Titania
*/
class titania_author extends titania_message_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_AUTHORS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'author_id';

	/**
	 * Object type (for message tool)
	 *
	 * @var string
	 */
	protected $object_type = TITANIA_AUTHOR;

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
			'author_id'				=> array('default' => 0),
			'user_id'				=> array('default' => 0),
			'phpbb_user_id'			=> array('default' => 0),

			'author_realname'		=> array('default' => '',	'max' => 255),
			'author_website'		=> array('default' => '',	'max' => 200),
			'author_rating'			=> array('default' => 0.0),
			'author_rating_count'	=> array('default' => 0),

			'author_contribs'		=> array('default' => 0),
			'author_visible'		=> array('default' => TITANIA_AUTHOR_VISIBLE),

			'author_desc'			=> array('default' => '',	'message_field' => 'message'),
			'author_desc_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'author_desc_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'author_desc_options'	=> array('default' => 7,	'message_field' => 'message_options'),
		));

		// Load the count for different types
		foreach (titania_types::$types as $type)
		{
			if (isset($type->author_count))
			{
				$this->object_config[$type->author_count] = array('default' => 0);
			}
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
				$sql_where = 'u.username_clean = \'' . phpbb::$db->sql_escape($user) . '\'';
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

		// Store in the users overlord as well
		users_overlord::$users[$this->user_id] = $this->sql_data;

		return true;
	}

	/**
	 * Submit data for storing into the database
	 *
	 * @return bool
	 */
	public function submit()
	{
		if (!$this->user_id)
		{
			throw new exception('No user_id!');
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

		if (phpbb::$user->data['user_id'] == $this->user_id)
		{
			$this->rating->cannot_rate = true;
		}

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
		return users_overlord::get_user($this->user_id, '_' . $mode);
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
			return titania_url::build_url(users_overlord::get_user($this->user_id, '_unbuilt_titania_profile') . '/' . $page);
		}

		return users_overlord::get_user($this->user_id, '_titania_profile');
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
			'AUTHOR_NAME_FULL'				=> $this->get_username_string(),
			'AUTHOR_REALNAME'				=> $this->author_realname,
			'AUTHOR_WEBSITE'				=> $this->get_website_url(),
			'AUTHOR_WEBSITE_LINK'			=> '<a href="' . $this->get_website_url() . '">' . $this->get_website_url() . '</a>',

			'AUTHOR_RATING'					=> ($this->author_id) ? $this->author_rating : '',
			'AUTHOR_RATING_STRING'			=> ($this->author_id && isset($this->rating)) ? $this->rating->get_rating_string() : '',
			'AUTHOR_RATING_COUNT'			=> ($this->author_id) ? $this->author_rating_count : '',

			'AUTHOR_CONTRIBS'				=> $this->author_contribs,

            'AUTHOR_DESC'                   => $this->generate_text_for_display(),

			'U_AUTHOR_PROFILE'				=> $this->get_url(),
			'U_AUTHOR_PROFILE_PHPBB'		=> $this->get_phpbb_profile_url(),
			'U_AUTHOR_PROFILE_PHPBB_COM'	=> $this->get_phpbb_com_profile_url(),
			'U_AUTHOR_CONTRIBUTIONS'		=> $this->get_url('contributions'),
		);

		// Add to it the common user details
		if (isset(users_overlord::$users[$this->user_id]))
		{
			$vars = array_merge(users_overlord::assign_details($this->user_id), $vars);
		}

		// Output the count for different types
		$type_list = array();
		foreach (titania_types::$types as $type)
		{
			if (!isset($type->author_count))
			{
				// Figure out the counts some other way
				$sql_ary = array(
					'SELECT'	=> 'COUNT(*) AS contrib_cnt, cat.category_name, cat.category_name_clean',

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE => 'c',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_CONTRIB_COAUTHORS_TABLE => 'ca'),
							'ON'	=> 'ca.contrib_id = c.contrib_id',
						),
						array(
							'FROM'	=> array(TITANIA_CATEGORIES_TABLE => 'cat'),
							'ON'	=> 'cat.category_type = c.contrib_type',
						),
					),

					'WHERE'		=> "c.contrib_visible = 1 AND c.contrib_type = {$type->id} AND (c.contrib_user_id = {$this->user_id}
									OR ca.user_id = {$this->user_id})",
				);
				$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
				$result = phpbb::$db->sql_query($sql);
				$type_row = phpbb::$db->sql_fetchrow($result);
				phpbb::$db->sql_freeresult($result);
				$contrib_cnt = (int) $type_row['contrib_cnt'];
				$cat_name = $type_row['category_name'];
				$cat_name_clean = $type_row['category_name_clean'];

				if ($contrib_cnt > 0)
				{
					if ($contrib_cnt == 1)
					{
						$type_list[] = (isset(phpbb::$user->langs['AUTHOR_' . strtoupper($cat_name_clean) . '_ONE'])) ? phpbb::$user->lang['AUTHOR_' . strtoupper($cat_name_clean) . '_ONE'] : '1 ' . $cat_name;
					}
					else
					{
						$type_list[] = (isset(phpbb::$user->lang['AUTHOR_' . strtoupper($cat_name_clean)])) ? sprintf(phpbb::$user->lang['AUTHOR_' . strtoupper($cat_name_clean)], $contrib_cnt) : $contrib_cnt . ' ' . $cat_name;
					}
				}
				continue;
			}

			if ($this->{$type->author_count} > 0)
			{
				if ($this->{$type->author_count} == 1)
				{
					$type_list[] = (isset(phpbb::$user->lang[strtoupper($type->author_count) . '_ONE'])) ? phpbb::$user->lang[strtoupper($type->author_count) . '_ONE'] : '{' . strtoupper($type->author_count) . '_ONE}';
				}
				else
				{
					$type_list[] = (isset(phpbb::$user->lang[strtoupper($type->author_count)])) ? sprintf(phpbb::$user->lang[strtoupper($type->author_count)], $this->{$type->author_count}) : '{' . strtoupper($type->author_count) . '}';
				}
			}
		}
		$vars['AUTHOR_CONTRIB_LIST'] = implode($type_list, ', ');

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