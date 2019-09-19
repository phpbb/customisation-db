<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

use phpbb\titania\ext;

/**
* Class to abstract titania authors.
* @package Titania
*/
class titania_author extends \phpbb\titania\entity\message_base
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
	protected $object_type = ext::TITANIA_AUTHOR;

	/**
	 * Rating of this author
	 *
	 * @var titania_rating
	 */
	public $rating;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\titania\contribution\type\collection */
	protected $types;

	/** Author visibility */
	const HIDDEN = 0;
	const VISIBLE = 1;

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

			'author_website'		=> array('default' => '',	'max' => 200),
			'author_rating'			=> array('default' => 0.0),
			'author_rating_count'	=> array('default' => 0),

			'author_contribs'		=> array('default' => 0),
			'author_visible'		=> array('default' => self::VISIBLE),

			'author_desc'			=> array('default' => '',	'message_field' => 'message'),
			'author_desc_bitfield'	=> array('default' => '',	'message_field' => 'message_bitfield'),
			'author_desc_uid'		=> array('default' => '',	'message_field' => 'message_uid'),
			'author_desc_options'	=> array('default' => 7,	'message_field' => 'message_options'),
		));

		$this->db = phpbb::$container->get('dbal.conn');
		$this->controller_helper = phpbb::$container->get('phpbb.titania.controller.helper');
		$this->types = phpbb::$container->get('phpbb.titania.contribution.type.collection');

		// Load the count for different types
		foreach ($this->types->get_all() as $type)
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
		$this->rating->load_user_rating();

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
	 * @param array $parameters Extra parameters.
	 *
	 * @return string
	 */
	public function get_url($page = '', $parameters = array())
	{
		$parameters += array(
			'author'	=> users_overlord::get_user($this->user_id, '_unbuilt_titania_profile'),
			'page'		=> $page,
		);

		return $this->controller_helper->route('phpbb.titania.author', $parameters);
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
		return $this->author_website;
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
			'AUTHOR_NAME'					=> $this->get_username_string('username'),
			'AUTHOR_NAME_FULL'				=> $this->get_username_string(),
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
		foreach ($this->types->get_all() as $type)
		{
			if (!isset($type->author_count))
			{
				// Figure out the counts some other way
				$valid_statuses = array(ext::TITANIA_CONTRIB_APPROVED, ext::TITANIA_CONTRIB_DOWNLOAD_DISABLED);

				$sql_ary = array(
					'SELECT'	=> 'COUNT(*) AS contrib_cnt',

					'FROM'		=> array(
						TITANIA_CONTRIBS_TABLE => 'c',
					),

					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array(TITANIA_CONTRIB_COAUTHORS_TABLE => 'ca'),
							'ON'	=> 'ca.contrib_id = c.contrib_id AND ca.user_id = ' . $this->user_id,
						),
					),

					'WHERE'		=> phpbb::$db->sql_in_set('c.contrib_status', $valid_statuses) . " AND c.contrib_visible = 1 AND c.contrib_type = {$type->id} AND (c.contrib_user_id = {$this->user_id}
									OR ca.user_id = {$this->user_id})",
				);
				$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
				$result = phpbb::$db->sql_query($sql);
				$contrib_cnt = phpbb::$db->sql_fetchfield('contrib_cnt');
				phpbb::$db->sql_freeresult($result);
			}
			else
			{
				$contrib_cnt = $this->{$type->author_count};
			}

			if ($contrib_cnt > 0)
			{
				$lang_key = 'AUTHOR_' . strtoupper($type->name) . 'S';

				if ($contrib_cnt == 1)
				{
					$type_list[] = (isset(phpbb::$user->lang[$lang_key . '_ONE'])) ? phpbb::$user->lang[$lang_key . '_ONE'] : '{' . $lang_key . '_ONE}';
				}
				else
				{
					$type_list[] = (isset(phpbb::$user->lang[$lang_key])) ? sprintf(phpbb::$user->lang[$lang_key], $contrib_cnt) : '{' . $lang_key . '}';
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
