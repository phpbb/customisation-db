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

namespace phpbb\titania\contribution\style\demo;

use phpbb\exception\http_exception;

/**
 * Class that handles displaying styles demo
 */
class demo
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\controller\helper */
	protected $controller_helper;

	/** @var string */
	protected $users_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $attachments_table;

	/** @var string */
	protected $revisions_table;

	/** @var string */
	protected $contrib_in_categories_table;

	/** @var string */
	protected $categories_table;

	/** @var string */
	protected $revisions_phpbb_table;

	/** @var string */
	protected $contrib_coauthors_table;

	/**
	 * Default style to be displayed
	 *
	 * @var int
	 */
	protected $default_style;

	/**
	 * Major phpBB version to limit style list to (30, 31, etc)
	 *
	 * @var int
	 */
	protected $phpbb_branch;

	/**
	 * Style data
	 *
	 * @var array
	 */
	protected $styles = array();

	/**
	 * Revisions for all styles
	 *
	 * @var array
	 */
	protected $revisions = array();

	/**
	 * Construct
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\controller\helper $controller_helper
	 * @param string $users_table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\template\template $template, \phpbb\titania\config\config $ext_config, \phpbb\titania\controller\helper $controller_helper, $users_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->ext_config = $ext_config;
		$this->controller_helper = $controller_helper;
		$this->users_table = $users_table;
		$this->contribs_table = TITANIA_CONTRIBS_TABLE;
		$this->attachments_table = TITANIA_ATTACHMENTS_TABLE;
		$this->revisions_table = TITANIA_REVISIONS_TABLE;
		$this->contrib_in_categories_table = TITANIA_CONTRIB_IN_CATEGORIES_TABLE;
		$this->categories_table = TITANIA_CATEGORIES_TABLE;
		$this->revisions_phpbb_table = TITANIA_REVISIONS_PHPBB_TABLE;
		$this->contrib_coauthors_table = TITANIA_CONTRIB_COAUTHORS_TABLE;
	}

	/**
	 * Set configuration options
	 *
	 * @param int $phpbb_branch			phpBB branch to limit style list to (30, 31, etc)
	 * @param int|bool $default_style	(Optional) Default style to display
	 * @return $this
	 */
	public function configure($phpbb_branch, $default_style = false)
	{
		$this->phpbb_branch = $phpbb_branch;
		$this->default_style = $default_style;

		return $this;
	}

	/**
	 * Load styles
	 */
	public function load_styles()
	{
		$sql_array = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_name, c.contrib_name_clean, c.contrib_user_id, c.contrib_demo, 
							s.attachment_id AS thumb_id, s.thumbnail, MAX(r.revision_id) AS revision_id,
							u.username, u.username_clean, u.user_colour, cat.category_name',
			'FROM'		=> array(
				$this->contribs_table => 'c',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->users_table => 'u'),
					'ON'	=> 'c.contrib_user_id = u.user_id',
				), array(
					'FROM'	=> array($this->attachments_table => 's'),
					'ON'	=> 'c.contrib_id = s.object_id
						AND s.is_preview = 1
						AND s.is_orphan = 0
						AND object_type = ' . TITANIA_SCREENSHOT,
				), array (
					'FROM'	=> array($this->revisions_table => 'r'),
					'ON'	=> 'c.contrib_id = r.contrib_id
						AND	r.revision_submitted = 1
						AND r.revision_status = ' . TITANIA_REVISION_APPROVED,
				), array(
					'FROM'	=> array($this->contrib_in_categories_table => 'cic'),
					'ON'	=> 'c.contrib_id = cic.contrib_id',
				), array(
					'FROM'	=> array($this->categories_table => 'cat'),
					'ON'	=> 'cic.category_id = cat.category_id',
				), array(
					'FROM'	=> array($this->revisions_phpbb_table => 'rp'),
					'ON'	=> 'c.contrib_id = rp.contrib_id AND r.revision_id = rp.revision_id',
				),
			),
			'WHERE'		=>  'c.contrib_visible = 1
								AND c.contrib_type = ' . TITANIA_TYPE_STYLE . '
								AND cat.category_options & ' . TITANIA_CAT_FLAG_DEMO . '
								AND c.contrib_status =' . TITANIA_CONTRIB_APPROVED . '
								AND c.contrib_demo <> ""
								AND rp.phpbb_version_branch = ' . (int) $this->phpbb_branch,

			'GROUP_BY'	=> 'c.contrib_id',
			'ORDER_BY'	=> 'cat.left_id, c.contrib_name ASC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql, 3600);
		$style = new \titania_contribution;

		while ($row = $this->db->sql_fetchrow($result))
		{
			$style->__set('contrib_demo', $row['contrib_demo']);

			if ($style->get_demo_url($this->phpbb_branch))
			{
				$this->styles[$row['contrib_id']] = array_merge($row, array(
					'coauthors'			=> '',
					'phpbb_versions'	=> array(),
				));
				$this->revisions[] = $row['revision_id'];
			}
		}
		$this->db->sql_freeresult($result);

		if (empty($this->styles))
		{
			throw new http_exception(200, 'NO_STYLES');
		}
		elseif ($this->default_style && !isset($this->styles[$this->default_style]))
		{
			throw new http_exception(404, 'NO_DEMO');
		}

		$sql = 'SELECT contrib_id, attachment_id, revision_license
			FROM ' . $this->revisions_table . '
			WHERE ' . $this->db->sql_in_set('revision_id', $this->revisions);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']] += array(
				'attachment_id'		=> $row['attachment_id'],
				'revision_license'	=> $row['revision_license'],
			);
		}
		$this->db->sql_freeresult($result);

		// Get coauthors and phpBB versions for the styles
		$this->get_coauthors();
		$this->get_phpbb_versions();

		// If we have no default style, we use the first in the list
		if (!$this->default_style)
		{
			$indexes = array_keys($this->styles);
			$this->default_style = $this->styles[$indexes[0]]['contrib_id'];
		}

		return true;
	}

	/**
	 * Get author profile
	 *
	 * @param array $data Data to generate username_string - user_id, username, username_clean, user_colour
	 * @return array
	 */
	protected function get_author_profile($data)
	{
		\users_overlord::$users[$data['user_id']] = $data;

		return \users_overlord::get_user($data['user_id'], '_titania', false);
	}

	/**
	 * Get all coauthors for each style
	 */
	protected function get_coauthors()
	{
		$sql = 'SELECT a.contrib_id, u.user_id, u.username, u.username_clean, u.user_colour 
			FROM ' . $this->contrib_coauthors_table . ' a
			LEFT JOIN ' . $this->users_table . ' u ON a.user_id = u.user_id
			WHERE a.active = 1 AND ' . $this->db->sql_in_set('a.contrib_id', array_keys($this->styles));
		$result = $this->db->sql_query($sql, 3600);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']]['coauthors'] .= ', ' . $this->get_author_profile($row);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Get phpBB version for each style
	 */
	protected function get_phpbb_versions()
	{
		$sql = 'SELECT contrib_id, phpbb_version_branch, phpbb_version_revision 
			FROM ' . $this->revisions_phpbb_table . '
			WHERE revision_validated = 1 AND ' . $this->db->sql_in_set('revision_id', $this->revisions) . '
			ORDER BY phpbb_version_revision ASC';

		$result = $this->db->sql_query($sql, 3600);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']]['phpbb_versions'] = array('branch' => $row['phpbb_version_branch'], 'revision' => $row['phpbb_version_revision']);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Get the immediate siblings of a style
	 *
	 * @param int $contrib_id Contrib id
	 * @param string $direction Sibling to fetch; either prev or next
	 * @return array|bool
	 */
	protected function sibling($contrib_id, $direction = 'next')
	{
		// Map out the order of the styles and determine where we're at
		$indexes = array_keys($this->styles);
		$position = array_search($contrib_id, $indexes);

		// Are we at the beginning or end already?
		if (($position == 0 && $direction == 'prev') || ($position == (sizeof($indexes) -1) && $direction == 'next'))
		{
			return false;
		}

		$direction = ($direction == 'prev') ? -1 : 1;
		$sibling = $indexes[$position + $direction];

		if (isset($this->styles[$sibling]['contrib_demo']))
		{
			$style = new \titania_contribution();
			$style->set_type(TITANIA_TYPE_STYLE);
			$style->__set_array(array(
				'contrib_name_clean'	=> $this->styles[$sibling]['contrib_name_clean'],
				'contrib_demo'			=> $this->styles[$sibling]['contrib_demo'],
			));
			$style->options = array('demo' => true);

			return array(
				'url'	=> $style->get_demo_url($this->phpbb_branch, true),
				'id'	=> $this->styles[$sibling]['contrib_id'],
			);
		}

		return false;
	}

	/**
	 * Assign variables to the template
	 */
	public function assign_details()
	{
		if (!sizeof($this->styles))
		{
			return false;
		}

		// Get siblings
		$prev = $this->sibling($this->default_style, 'prev');
		$next = $this->sibling($this->default_style, 'next');

		$this->template->assign_vars(array(
			'U_PREV'	=> $prev['url'],
			'PREV_ID'	=> $prev['id'],
			'U_NEXT'	=> $next['url'],
			'NEXT_ID'	=> $next['id'],
		));

		$category = '';
		$style = new \titania_contribution;
		$style->set_type(TITANIA_TYPE_STYLE);
		$style->options = array('demo' => true);

		foreach ($this->styles as $id => $data)
		{
			$style->__set_array(array(
				'contrib_id'			=> $id,
				'contrib_name_clean'	=> $data['contrib_name_clean'],
				'contrib_demo'			=> $data['contrib_demo'],
			));

			$preview_img = false;

			if (isset($data['thumb_id']))
			{
				$parameters = array('id' => $data['thumb_id']);

				if ($data['thumbnail'])
				{
					$parameters += array(
						'mode'	=> 'view',
						'thumb'	=> 1,
					);
				}

				$preview_img = $this->controller_helper->route(
					'phpbb.titania.download',
					$parameters
				);
			}

			$authors = $this->get_author_profile(array(
				'username_clean'	=> $data['username_clean'],
				'username'			=> $data['username'],
				'user_id'			=> $data['contrib_user_id'],
				'user_colour'		=> $data['user_colour']
			));
			$authors .= $data['coauthors'];

			$data['category_name'] = $this->user->lang($data['category_name']);
			$phpbb_version = $data['phpbb_versions']['branch'][0] . '.' .
				$data['phpbb_versions']['branch'][1] . '.' .
				$data['phpbb_versions']['revision']
			;
			$current_phpbb_version = $data['phpbb_versions']['branch'][0] . '.' .
				$data['phpbb_versions']['branch'][1] . '.' .
				$this->ext_config->phpbb_versions[$data['phpbb_versions']['branch']]['latest_revision']
			;

			$vars = array(
				'AUTHORS'		=> $authors,
				'CATEGORY'		=> ($category != $data['category_name']) ? $data['category_name'] : false,
				'ID'			=> $id,
				'IFRAME'		=> $style->get_demo_url($this->phpbb_branch),
				'LICENSE'		=> ($data['revision_license']) ? $data['revision_license'] : $this->user->lang['UNKNOWN'],
				'NAME'			=> $data['contrib_name'],
				'PHPBB_VERSION'	=> $phpbb_version,
				'PREVIEW'		=> $preview_img,
				'S_OUTDATED'	=> phpbb_version_compare($phpbb_version, $current_phpbb_version, '<'),
				'U_DEMO'		=> $style->get_demo_url($this->phpbb_branch, true),
				'U_DOWNLOAD'	=> $this->controller_helper->route('phpbb.titania.download', array(
					'id' => $data['attachment_id'],
				)),
				'U_VIEW'		=> $style->get_url(),
			);

			if ($this->default_style == $id)
			{
				$this->template->assign_vars($vars);
			}

			$category = $data['category_name'];

			$this->template->assign_block_vars('stylerow', $vars);
			unset($this->styles[$id], $vars, $this->coauthors[$id]);
		}
	}
}
