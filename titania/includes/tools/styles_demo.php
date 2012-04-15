<?php
/**
*
* @package Titania
* @copyright (c) 2012 phpBB Customisation Database Team
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
	public $phpbb_version;

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
	* @param int $default_style Default style to display
	* @param int $phpbb_version Major phpBB version to limit style list to (30, 31, etc)
	*/	
	public function __construct($default_style = false, $phpbb_version = false)
	{
		$this->default_style = $default_style;
		$this->phpbb_version = $phpbb_version;
	}

	/**
	* Load styles
	*/		
	public function load_styles()
	{
		$sql_array = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_name, c.contrib_name_clean, c.contrib_user_id, c.contrib_demo, 
								s.attachment_id AS thumb_id, s.thumbnail, r.revision_id, r.attachment_id, r.revision_license, u.username, 
									u.username_clean, u.user_colour, cat.category_name',
			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE => 'c',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'c.contrib_user_id = u.user_id',		
				), array(
					'FROM'	=> array(TITANIA_ATTACHMENTS_TABLE => 's'),
					'ON'	=> 'c.contrib_id = s.object_id AND s.is_preview = 1 AND s.is_orphan = 0 AND object_type = ' . TITANIA_SCREENSHOT,
				), array (
					'FROM'	=> array(TITANIA_REVISIONS_TABLE => 'r'),
					'ON'	=> 'c.contrib_id = r.contrib_id AND	r.revision_submitted = 1 AND c.contrib_last_update = r.validation_date AND r.revision_status = ' . TITANIA_REVISION_APPROVED,	
				), array(
					'FROM'	=> array(TITANIA_CONTRIB_IN_CATEGORIES_TABLE => 'cic'),
					'ON'	=> 'c.contrib_id = cic.contrib_id',
				), array(
					'FROM'	=> array(TITANIA_CATEGORIES_TABLE => 'cat'),
					'ON'	=> 'cic.category_id = cat.category_id',
				),
			),
			'WHERE'		=>  'c.contrib_visible = 1 AND c.contrib_type = ' . TITANIA_TYPE_STYLE . ' AND cat.category_options & ' . TITANIA_CAT_FLAG_DEMO . ' AND c.contrib_status =' . TITANIA_CONTRIB_APPROVED . '
								AND c.contrib_demo <> ""' . (($this->phpbb_version) ? ' AND rp.phpbb_version_branch = ' . (int) $this->phpbb_version : ''),

			'GROUP_BY'	=> 'c.contrib_id',
			'ORDER_BY'	=> 'cat.left_id, c.contrib_name ASC',
		);

		// Do we have a limit on the phpBB version?
		if ($this->phpbb_version)
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(TITANIA_REVISIONS_PHPBB_TABLE => 'rp'),
				'ON'	=> 'c.contrib_id = rp.contrib_id AND r.revision_id = rp.revision_id'
			); 
		}

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_array);
		$result = phpbb::$db->sql_query($sql, 3600);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$this->styles[$row['contrib_id']] = array_merge($row, array('coauthors' => '', 'phpbb_versions' => ''));
			$this->revisions[] = $row['revision_id']; 
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
			$this->styles[$row['contrib_id']]['phpbb_versions'] = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' . $row['phpbb_version_revision'];
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
			$style->contrib_name_clean = $this->styles[$sibling]['contrib_name_clean'];
			$style->contrib_type = TITANIA_TYPE_STYLE;
						
			return array('url' => $style->get_url('demo'), 'id' => $this->styles[$sibling]['contrib_id']);
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
		
		foreach ($this->styles as $id => $data)
		{			
			$style = new titania_contribution();
			$style->contrib_id = $id;
			$style->contrib_name_clean = $data['contrib_name_clean'];
			$style->contrib_type = TITANIA_TYPE_STYLE;
			
			$preview_img = false;
			
			if (isset($data['thumb_id']))
			{
				$preview_img = titania_url::build_url('download', array('id' => $data['thumb_id']));
				$parameters = ($data['thumbnail']) ? array('mode' => 'view', 'thumb' => 1) : false;
				$preview_img = titania_url::append_url($preview_img, $parameters);			
			}
			
			$authors = $this->get_author_profile(array(
				'username_clean'	=> $data['username_clean'],
				'username'			=> $data['username'],
				'user_id'			=> $data['contrib_user_id'],
				'user_colour'		=> $data['user_colour']
			));
			$authors .= $data['coauthors'];

			$data['category_name'] = (isset(phpbb::$user->lang[$data['category_name']])) ? phpbb::$user->lang[$data['category_name']] : $data['category_name'];

			$vars = array(
				'AUTHORS'		=> $authors,
				'CATEGORY'		=> ($category != $data['category_name']) ? $data['category_name'] : false,
				'ID'			=> $id,
				'IFRAME'		=> $data['contrib_demo'],
				'LICENSE'		=> ($data['revision_license']) ? $data['revision_license'] : phpbb::$user->lang['UNKNOWN'],
				'NAME'			=> $data['contrib_name'],
				'PHPBB_VERSION'	=> $data['phpbb_versions'],
				'PREVIEW'		=> $preview_img,
				'U_DEMO'		=> $style->get_url('demo'),
				'U_DOWNLOAD'	=> titania_url::build_url('download', array('id' => $data['attachment_id'])),
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
