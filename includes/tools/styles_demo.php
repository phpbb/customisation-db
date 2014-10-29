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

/**
 * Class that handles displaying styles demo
 *
 * @package Titania
 */
class titania_styles_demo
{
	/**
	* Default style to be displayed
	*
	* @var int
	*/	
	public $default_style;

	/**
	* Major phpBB version to limit style list to (30, 31, etc)
	*
	* @var int
	*/
	public $phpbb_branch;

	/**
	* Style data
	*
	* @var array
	*/		
	public $styles = array();

	/**
	* Revisions for all styles
	*
	* @var array
	*/		
	private $revisions = array();

	/**
	* Constructor.
	*
	* @param int $phpbb_version phpBB branch to limit style list to (30, 31, etc)
	* @param int $default_style Default style to display
	*/
	public function __construct($phpbb_branch, $default_style = false)
	{
		$this->phpbb_branch = $phpbb_branch;
		$this->default_style = $default_style;
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
				TITANIA_CONTRIBS_TABLE => 'c',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'c.contrib_user_id = u.user_id',
				), array(
					'FROM'	=> array(TITANIA_ATTACHMENTS_TABLE => 's'),
					'ON'	=> 'c.contrib_id = s.object_id
						AND s.is_preview = 1
						AND s.is_orphan = 0
						AND object_type = ' . TITANIA_SCREENSHOT,
				), array (
					'FROM'	=> array(TITANIA_REVISIONS_TABLE => 'r'),
					'ON'	=> 'c.contrib_id = r.contrib_id
						AND	r.revision_submitted = 1
						AND r.revision_status = ' . TITANIA_REVISION_APPROVED,	
				), array(
					'FROM'	=> array(TITANIA_CONTRIB_IN_CATEGORIES_TABLE => 'cic'),
					'ON'	=> 'c.contrib_id = cic.contrib_id',
				), array(
					'FROM'	=> array(TITANIA_CATEGORIES_TABLE => 'cat'),
					'ON'	=> 'cic.category_id = cat.category_id',
				), array(
					'FROM'	=> array(TITANIA_REVISIONS_PHPBB_TABLE => 'rp'),
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

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_array);
		$result = phpbb::$db->sql_query($sql, 3600);
		$style = new titania_contribution;

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$style->__set('contrib_demo', $row['contrib_demo']);

			if ($style->get_demo_url($this->phpbb_branch))
			{
				$this->styles[$row['contrib_id']] = array_merge($row, array('coauthors' => '', 'phpbb_versions' => array()));
				$this->revisions[] = $row['revision_id'];	
			} 
		}
		phpbb::$db->sql_freeresult($result);

		if (!sizeof($this->styles))
		{
			trigger_error('NO_STYLES');
		}
		elseif ($this->default_style && !isset($this->styles[$this->default_style]))
		{
			trigger_error('NO_DEMO');
		}

		$sql = 'SELECT contrib_id, attachment_id, revision_license
			FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('revision_id', $this->revisions);
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']] += array(
				'attachment_id'		=> $row['attachment_id'],
				'revision_license'	=> $row['revision_license'],
			);
		}
		phpbb::$db->sql_freeresult($result);

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
	*/		
	private function get_author_profile($data)
	{
		users_overlord::$users[$data['user_id']] = $data;

		return users_overlord::get_user($data['user_id'], '_titania', false);	
	}

	/**
	* Get all coauthors for each style
	*/	
	private function get_coauthors()
	{
		$sql = 'SELECT a.contrib_id, u.user_id, u.username, u.username_clean, u.user_colour 
			FROM ' . TITANIA_CONTRIB_COAUTHORS_TABLE . ' a 
			LEFT JOIN ' . USERS_TABLE . ' u ON a.user_id = u.user_id 
			WHERE a.active = 1 AND ' . phpbb::$db->sql_in_set('a.contrib_id', array_keys($this->styles));
		$result = phpbb::$db->sql_query($sql, 3600);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']]['coauthors'] .= ', ' . $this->get_author_profile($row);
		}
		phpbb::$db->sql_freeresult($result);
	}
	
	/**
	* Get phpBB version for each style
	*/	
	private function get_phpbb_versions()
	{
		$sql = 'SELECT contrib_id, phpbb_version_branch, phpbb_version_revision 
			FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . ' 
			WHERE revision_validated = 1 AND ' . phpbb::$db->sql_in_set('revision_id', $this->revisions) . '
			ORDER BY phpbb_version_revision ASC';
	
		$result = phpbb::$db->sql_query($sql, 3600);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']]['phpbb_versions'] = array('branch' => $row['phpbb_version_branch'], 'revision' => $row['phpbb_version_revision']);
		}
		phpbb::$db->sql_freeresult($result);
	}

	/**
	* Get the immediate siblings of a style
	*
	* @param int $contrib_id Contrib id
	* @param string $direction Sibling to fetch; either prev or next
	*/		
	private function sibling($contrib_id, $direction = 'next')
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
			$style = new titania_contribution();
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
		
		phpbb::$template->assign_vars(array(
			'U_PREV'	=> $prev['url'],
			'PREV_ID'	=> $prev['id'],
			'U_NEXT'	=> $next['url'],
			'NEXT_ID'	=> $next['id'],
		));

		$category = '';
		$style = new titania_contribution();
		$style->set_type(TITANIA_TYPE_STYLE);
		$style->options = array('demo' => true);
		$file = new titania_attachment(TITANIA_CONTRIB);

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
				$parameters = array();

				if ($data['thumbnail'])
				{
					$parameters = array(
						'mode'	=> 'view',
						'thumb'	=> 1,
					);
				}

				$preview_img = $file->get_url($data['thumb_id'], $parameters);		
			}

			$authors = $this->get_author_profile(array(
				'username_clean'	=> $data['username_clean'],
				'username'			=> $data['username'],
				'user_id'			=> $data['contrib_user_id'],
				'user_colour'		=> $data['user_colour']
			));
			$authors .= $data['coauthors'];

			$data['category_name'] = (isset(phpbb::$user->lang[$data['category_name']])) ? phpbb::$user->lang[$data['category_name']] : $data['category_name'];
			$phpbb_version = $data['phpbb_versions']['branch'][0] . '.' . $data['phpbb_versions']['branch'][1] . '.' . $data['phpbb_versions']['revision'];
			$current_phpbb_version = $data['phpbb_versions']['branch'][0] . '.' . $data['phpbb_versions']['branch'][1] . '.' . titania::$config->phpbb_versions[$data['phpbb_versions']['branch']]['latest_revision'];

			$vars = array(
				'AUTHORS'		=> $authors,
				'CATEGORY'		=> ($category != $data['category_name']) ? $data['category_name'] : false,
				'ID'			=> $id,
				'IFRAME'		=> $style->get_demo_url($this->phpbb_branch),
				'LICENSE'		=> ($data['revision_license']) ? $data['revision_license'] : phpbb::$user->lang['UNKNOWN'],
				'NAME'			=> $data['contrib_name'],
				'PHPBB_VERSION'	=> $phpbb_version,
				'PREVIEW'		=> $preview_img,
				'S_OUTDATED'	=> phpbb_version_compare($phpbb_version, $current_phpbb_version, '<'),
				'U_DEMO'		=> $style->get_demo_url($this->phpbb_branch, true),
				'U_DOWNLOAD'	=> $file->get_url($data['attachment_id']),
				'U_VIEW'		=> $style->get_url(),
			);

			if ($this->default_style == $id)
			{
				phpbb::$template->assign_vars($vars);
			}
		
			$category = $data['category_name'];
			
			phpbb::$template->assign_block_vars('stylerow', $vars);
			unset($this->styles[$id], $vars, $this->coauthors[$id]);
		}
	}	
}
