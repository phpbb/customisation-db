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
* Class to titania faq.
* @package Titania
*/
class titania_faq extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table		= CUSTOMISATION_CONTRIB_FAQ_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'faq_id';

	/**
	 * Text parsed for storage
	 *
	 * @var bool
	 */
	private $text_parsed_for_storage = false;
	
	/*
	 * Type of contrib in class instance
	 *
	 * @var string
	 */
	private $contrib_type;
	
	/**
	 * Constructor class for titania faq
	 *
	 * @param int $faq_id
	 */
	public function __construct($faq_id = false, $contrib_type)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'faq_id'		=> array('default' => 0),
			'contrib_id' 		=> array('default' => 0),
			'parent_id' 		=> array('default' => 0),
			'contrib_version' 	=> array('default' => '', 'max' => 15),
			'faq_order_id' 		=> array('default' => 0),
			'faq_subject' 		=> array('default' => '', 'max' => 255),
			'faq_text' 		=> array('default' => ''),
			'faq_text_bitfield'	=> array('default' => '', 'readonly' => true),
			'faq_text_uid'		=> array('default' => '', 'readonly' => true),
			'faq_text_options'	=> array('default' => 7, 'readonly' => true)
		));

		if ($faq_id !== false)
		{
			$this->faq_id = $faq_id;
		}
		
		switch ($contrib_type)
		{
			case CONTRIB_TYPE_MOD:
				$this->contrib_type = 'mod';
			break;
			
			case CONTRIB_TYPE_STYLE:
				$this->contrib_type = 'style';
			break;
			
			case CONTRIB_TYPE_SNIPPET:
				$this->contrib_type = 'snippet';
			break;
		}
	}
	
	/**
	 * Update data or submit new faq
	 *
	 * @return void
	 */
	public function submit()
	{
		// Nobody parsed the text for storage before. Parse text with lowest settings.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(true, true, false);
		}
		
		parent::submit();
	}

	/**
	 * Get faq data from the database
	 *
	 * @return void
	 */
	public function load()
	{
		parent::load();

		$this->text_parsed_for_storage = true;
	}
		
	/**
	 * Parse text to store in database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 *
	 * @return void
	 */
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		$faq_text = $this->faq_text;
		$faq_text_uid = $this->faq_text_uid;
		$faq_text_bitfield = $this->faq_text_bitfield;
		$faq_text_options = $this->faq_text_options;

		generate_text_for_storage($faq_text, $faq_text_uid, $faq_text_bitfield, $faq_text_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->faq_text = $faq_text;
		$this->faq_text_uid = $faq_text_uid;
		$this->faq_text_bitfield = $faq_text_bitfield;
		$this->faq_text_options = $faq_text_options;

		$this->text_parsed_for_storage = true;
	}

	/**
	 * Parse text for display
	 *
	 * @return string text content from database for display
	 */
	private function generate_text_for_display()
	{
		return generate_text_for_display($this->faq_text, $this->faq_text_uid, $this->faq_text_bitfield, $this->faq_text_options);
	}

	/**
	 * Parse text for edit
	 *
	 * @return string text content from database for editing
	 */
	private function generate_text_for_edit()
	{
		$return = generate_text_for_edit($this->faq_text, $this->faq_text_uid, $this->faq_text_options);
		$this->faq_text = $return['text'];
	}

	/**
	 * Getter function for faq_text
	 *
	 * @param bool $editable
	 *
	 * @return string generate_text_for edit if editable is true, or display if false
	 */
	public function get_faq_text($editable = false)
	{
		// Text needs to be from database or parsed for database.
		if (!$this->text_parsed_for_storage)
		{
			$this->generate_text_for_storage(true, true, false);
		}

		if ($editable)
		{
			$this->generate_text_for_edit();
		}
		else
		{
			$this->generate_text_for_display();
		}
		
		return $this->faq_text;
	}

	/**
	 * Setter function for faq_text
	 *
	 * @param string $text
	 * @param string $uid
	 * @param string $bitfield
	 * @param int $flags
	 *
	 * @return void
	 */
	public function set_faq_text($text, $uid = false, $bitfield = false, $flags = false)
	{
		$this->faq_text = $text;
		$this->text_parsed_for_storage = false;

		if ($uid !== false)
		{
			$this->faq_text_uid = $uid;
		}

		if ($bitfield !== false)
		{
			$this->faq_text_bitfield = $bitfield;
		}

		if ($flags !== false)
		{
			$this->faq_text_options = $flags;
		}
	}
	
	/*
	 * Submit FAQ
	 */
	public function submit_faq($contrib_id, $action)
	{
		global $template, $db, $user, $titania;
		
		$submit = (isset($_POST['submit'])) ? true : false;
		
		$errors = array();
		
		if ($submit)
		{
			$this->faq_subject 	= utf8_normalize_nfc(request_var('subject', '', true));
			$text 			= utf8_normalize_nfc(request_var('text', '', true));
			
			if (empty($this->faq_subject))
			{
				$errors[] = $user->lang['SUBJECT_EMPTY'];
			}
			
			if (empty($text))
			{
				$errors[] = $user->lang['TEXT_EMPTY'];
			}
			
			if (!sizeof($errors))
			{
				$sql = 'SELECT contrib_version
					FROM ' . CUSTOMISATION_CONTRIBS_TABLE . "
					WHERE contrib_id = $contrib_id";
				$result = $db->sql_query($sql);
				$contrib = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				
				$this->contrib_version 	= $contrib['contrib_version'];
				$this->contrib_id 	= $contrib_id;
				
				$this->set_faq_text($text);

				$this->submit();
				
				$message = ($action == 'edit') ? $user->lang['FAQ_EDITED'] : $user->lang['FAQ_CREATED'];
				$message .= '<br /><br />' . sprintf($user->lang['RETURN_FAQ'], '<a href="' . append_sid($titania->page, "id=faq&amp;mode=view&amp;faq={$this->faq_id}") . '">', '</a>');
				$message .= '<br /><br />' . sprintf($user->lang['RETURN_FAQ_LIST'], '<a href="' . append_sid($titania->page, "id=faq&amp;mode=view&amp;mod=$contrib_id") . '">', '</a>');				
				
				trigger_error($message);
			}
		}

		if ($action == 'edit')
		{
			$this->load();
		}
		
		$template->assign_vars(array(
			'U_ACTION'		=> $titania->page . "?id=faq&amp;mode=view&amp;action=$action&amp;{$this->contrib_type}=$contrib_id&amp;faq={$this->faq_id}",
			
			'S_EDIT_FAQ'		=> true,
			
			'L_EDIT_FAQ'		=> ($action == 'edit') ? $user->lang['EDIT_FAQ'] : $user->lang['CREATE_FAQ'],
			
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : false,
			
			'FAQ_SUBJECT'		=> $this->faq_subject,
			'FAQ_TEXT'		=> $this->get_faq_text(true),
		));
	}
	
	/*
	 * Delete FAQ
	 */
	public function delete_faq()
	{
		$submit = (isset($_POST['submit'])) ? true : false;
		
		if ($submit)
		{
			if (confirm_box(true))
			{
				$this->delete($this->faq_id);
				
				return true;
			}
			return false;
		}
		else
		{
			confirm_box(false, 'DELETE_FAQ', build_hidden_fields(array(
				'submit'	=> true,
				'faq'		=> $faq_id
			)));
		}	
	}
	
	/*
	 * FAQ manage
	 */
	public function manage_list()
	{
	
	}
	
	/*
	 * FAQ details
	 */
	public function faq_details()
	{
		global $template, $db, $user, $titania, $auth;
		
		$sql_ary = array(
			'SELECT'	=> 'f.*, c.contrib_name, c.contrib_version as current_contrib_version, c.contrib_author_id',
			'FROM'		=> array(CUSTOMISATION_CONTRIB_FAQ_TABLE => 'f'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_CONTRIBS_TABLE => 'c'),
					'ON'	=> 'c.contrib_id = f.contrib_id',
				),
			),
			'WHERE'		=> 'f.faq_id = ' . $this->faq_id
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		
		if (!$row)
		{
			return false;
		}
		
		$db->sql_freeresult($result);
		
		$template->assign_vars(array(
			'FAQ_SUBJECT'		=> $row['faq_subject'],
			'FAQ_TEXT'		=> generate_text_for_display($row['faq_text'], $row['faq_text_uid'], $row['faq_text_bitfield'], $row['faq_text_options']),
			'CONTRIB_VERSION' 	=> $row['contrib_version'],

			'U_FAQ_LIST'		=> append_sid($titania->page, 'id=faq&amp;mode=view&amp;' . $this->contrib_type . '=' . $row['contrib_id']),
			'U_EDIT_FAQ'		=> ($user->data['user_id'] == $row['contrib_author_id'] || $auth->acl_get('a_') || $auth->acl_get('m_')) ? append_sid($titania->page, 'id=faq&amp;mode=view&amp;action=edit&amp;' . $this->contrib_type . '=' . $row['contrib_id'] . '&amp;faq=' . $row['faq_id']) : false,
			
			'L_CONTRIB_VERSION'	=> $user->lang[strtoupper($this->contrib_type) . '_VERSION'],
		));
		
		return true;
	}
	
	/**
	 * FAQ list
	 *
	 * @param int $contrib_id
	 */
	public function faq_list($contrib_id)
	{
		global $db, $template, $titania, $user, $auth;

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
			'a' => array('SORT_FAQ_SUBJECT',		'f.faq_subject'),		
			'b' => array('SORT_CONTRIB_VERSION',		'f.contrib_version', 'default' => true),
		));

		$sort->sort_request(false);		
		
		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();
		
		$sql_ary = array(
			'SELECT'	=> 'f.*',
			'FROM'		=> array(
					CUSTOMISATION_CONTRIB_FAQ_TABLE	=> 'f'
			),		
			'WHERE'		=> 'f.contrib_id = ' . $contrib_id,
			'ORDER_BY'	=> $sort->get_order_by()
		);
		
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		$results = 0;
		
		while ($row = $db->sql_fetchrow($result))
		{
			$results++;
			
			strip_bbcode($row['faq_text'], $row['faq_text_uid']);
			
			$template->assign_block_vars('faq', array(
				'U_FAQ'			=> append_sid($titania->page, 'id=faq&amp;mode=view&amp;faq=' . $row['faq_id']),
				
				'SUBJECT'		=> $row['faq_subject'],
				'TEXT'			=> (utf8_strlen($row['faq_text']) > 120) ? utf8_substr($row['faq_text'], 0, 117) . '...' : $row['faq_text'],
				'CONTRIB_VERSION'	=> $row['contrib_version'],
			));
		}
		$db->sql_freeresult($result);
		
		if (!$results)
		{
			return false;
		}
		
		$pagination->sql_total_count($sql_ary, 'f.faq_id', $results);
		
		$pagination->set_params(array(
			'sk'	=> $sort->get_sort_key(false),
			'sd'	=> $sort->get_sort_dir(false),
		));
		
		// Build a pagination
		$pagination->build_pagination(append_sid($titania->page, 'id=faq&amp;mode=view&amp;' . $this->contrib_type . '=' . $contrib_id));
		
		// informations about contrib
		$sql = 'SELECT contrib_name, contrib_version, contrib_author_id
			FROM ' . CUSTOMISATION_CONTRIBS_TABLE . '
			WHERE contrib_id = ' . $contrib_id;
		$result = $db->sql_query($sql);
		$contrib = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
			
			'CONTRIB_NAME'		=> $contrib['contrib_name'],
			'CONTRIB_VERSION'	=> $contrib['contrib_version'],
			
			'U_CREATE_FAQ'		=> ($user->data['user_id'] == $contrib['contrib_author_id'] || $auth->acl_get('a_') || $auth->acl_get('m_')) ? append_sid($titania->page, 'id=faq&amp;mode=view&amp;action=create&amp;' . $this->contrib_type . '=' . $contrib_id) : false,

			'L_CONTRIB_VERSION'	=> $user->lang[strtoupper($this->contrib_type) . '_VERSION'],
		));
		
		return true;
	}
}

?>