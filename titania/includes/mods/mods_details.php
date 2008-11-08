<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* mods_details
* Class for Details module
* @package details
*/
class mods_details extends titania_object
{
	public $p_master;
	public $u_action;

	/**
	 * Constructor
	 */
	public function __construct($p_master)
	{
		global $user;

		$this->p_master = $p_master;

		$this->page = $user->page['script_path'] . $user->page['page_name'];
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $user, $template, $cache;

		$user->add_lang(array('titania_mods'));

		$mod_id	= request_var('mod', 0);
		$submit	= isset($_POST['submit']) ? true : false;

		$form_key = 'mods_details';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'styles':
			break;

			case 'translations':
			break;

			case 'email':
			break;

			case 'changes':
			break;

			case 'preview':
			break;

			case 'screenshots':
			break;

			case 'details':
			default:
				$this->tpl_name = 'mods/mod_details';
				$this->page_title = 'MODS_DETAILS';

				$this->mod_details($mod_id);
			break;
		}
	}

	public function mod_details($mod_id)
	{
		global $db, $template, $user;

		$sql_ary = array(
			'SELECT'	=> 'c.*, a.author_id, a.author_username, u.user_colour',
			'FROM'		=> array(CUSTOMISATION_CONTRIBS_TABLE => 'c'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.author_id = c.contrib_author_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = a.user_id',
				),
			),
			'WHERE'		=> 'c.contrib_id = ' . (int) $mod_id . '
							AND c.contrib_status = ' .  STATUS_APPROVED,
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchfield($result);
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'MOD_ID'		=> $row['contrib_id'],
			'MOD_TITLE'		=> $row['contrib_name'],
			'MOD_DESC'		=> $row['contrib_description'],
			'RATING'		=> round($row['contrib_rating'], 2),
			'DOWNLOADS'		=> $row['contrib_downloads'],
			'ADDED'			=> $user->format_date($row['contrib_release_date']),
			'UPDATED'		=> $user->format_date($row['contrib_update_date']),
			'VERSION'		=> $row['contrib_version'],
			'AUTHOR'		=> sprintf($user->lang['AUTHOR_BY'], get_username_string('full', $row['author_id'], $row['author_username'], $row['user_colour'], false, $profile_url)),
		));
	}
}