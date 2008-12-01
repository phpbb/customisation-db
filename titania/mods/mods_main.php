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
* mods_main
* MODs Categories
* @package mods
*/
class mods_main extends titania_object
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

		$user->add_lang(array('titania_contrib', 'titania_mods'));

		$category	= request_var('category', 0);
		$submit		= isset($_POST['submit']) ? true : false;

		$form_key	= 'mods_main';
		add_form_key($form_key);

		switch ($mode)
		{
			case 'details':
				$this->tpl_name = 'mods/mod_details';
				$this->page_title = 'MODS_DETAILS';

				$found = $this->mod_details($category, $mod_id);

				if (!$found)
				{
					titania::error_box('ERROR', $user->lang['MOD_NOT_FOUND'], ERROR_ERROR);

					$mode = ($category) ? 'list' : 'categories';
					$this->main($id, $mode);
					return;
				}
			break;

			case 'list':
			case 'search':
				// Set desired template
				$this->tpl_name = 'mods/mod_list';
				$this->page_title = 'MODS_LIST';

				$found = $this->mod_list($category);

				if (!$found)
				{
					$categories = $cache->get_categories(TAG_TYPE_MOD_CATEGORY);
					titania::error_box('ERROR', sprintf($user->lang['NO_MODS'], $categories[$category]['name']), ERROR_ERROR);
					$this->main($id, 'categories');
					return;
				}
			break;

			case 'categories':
			default:
				// Set desired template
				$this->tpl_name = 'mods/mod_categories';
				$this->page_title = 'MODS_CATEGORIES';

				$this->mod_categories();
			break;
		}
	}

	/**
	 * MOD Categories
	 */
	public function mod_categories()
	{
		global $cache, $template, $user;

		$categories = $cache->get_categories(TAG_TYPE_MOD_CATEGORY);

		foreach ($categories as $row)
		{
			$template->assign_block_vars('category', array(
				'U_CATEGORY'=> append_sid($this->page, 'mode=list&amp;category=' . $row['id']),
				'ID'		=> $row['id'],
				'TITLE'		=> $row['name'],
				'DESC'		=> $row['desc'],
			));
		}
	}

	/**
	 * MOD list and search results
	 */
	public function mod_list($category)
	{
		global $db, $template, $user;

		include_once(TITANIA_ROOT . 'includes/class_sort.' . PHP_EXT);
		include_once(TITANIA_ROOT . 'includes/class_pagination.' . PHP_EXT);

		$sort = new sort();
		$sort->set_sort_keys(array(
			array('SORT_AUTHOR',		'a.author_username_clean', 'default' => true),
			array('SORT_TIME_ADDED',	'c.contrib_release_date'),
			array('SORT_TIME_UPDATED',	'c.contrib_update_date'),
			array('SORT_DOWNLOADS',		'c.contrib_downloads'),
			array('SORT_RATING',		'c.contrib_rating'),
			array('SORT_CONTRIB_NAME',	'c.contrib_name'),
		));

		$sort->sort_request(false);

		$pagination = new pagination();
		$start = $pagination->set_start();
		$limit = $pagination->set_limit();

		$results = 0;

		$sql_ary = array(
			'SELECT'	=> 'c.*, a.author_id, a.author_username, u.user_colour',
			'FROM'		=> array(CUSTOMISATION_CONTRIBS_TABLE => 'c'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(CUSTOMISATION_CONTRIB_TAGS_TABLE => 't'),
					'ON'	=> 't.contrib_id = c.contrib_id',
				),
				array(
					'FROM'	=> array(CUSTOMISATION_AUTHORS_TABLE => 'a'),
					'ON'	=> 'a.author_id = c.contrib_author_id',
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = a.user_id',
				),
			),
			'WHERE'		=> 't.tag_id = ' . $category . '
							AND c.contrib_status = ' .  STATUS_APPROVED,
			'ORDER_BY'	=> $sort->get_order_by(),
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query_limit($sql, $limit, $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$results++;

			$profile_url = append_sid(TITANIA_ROOT . 'authors/index.' . PHP_EXT, 'mode=profile');

			$template->assign_block_vars('contrib', array(
				'ID'			=> $row['contrib_id'],
				'U_CONTRIB'		=> append_sid($this->page, 'mode=details&amp;mod=' . $row['contrib_id']),
				'TITLE'			=> $row['contrib_name'],
				'DESC'			=> $row['contrib_description'],
				'RATING'		=> round($row['contrib_rating'], 2),
				'DOWNLOADS'		=> $row['contrib_downloads'],
				'ADDED'			=> $user->format_date($row['contrib_release_date']),
				'UPDATED'		=> $user->format_date($row['contrib_update_date']),
				'VERSION'		=> $row['contrib_version'],
				'AUTHOR'		=> sprintf($user->lang['AUTHOR_BY'], get_username_string('full', $row['author_id'], $row['author_username'], $row['user_colour'], false, $profile_url)),
			));
		}
		$db->sql_freeresult($result);

		if (!$results)
		{
			return false;
		}

		$pagination->sql_total_count($sql_ary, 'c.contrib_id', $results);

		$pagination->set_params(array(
			'sk'		=> $sort->get_sort_key(false),
			'sd'		=> $sort->get_sort_dir(false),
			'category'	=> $category,
		));

		$pagination->build_pagination($this->u_action);

		$template->assign_vars(array(
			'S_MODE_SELECT'		=> $sort->get_sort_key_list(),
			'S_ORDER_SELECT'	=> $sort->get_sort_dir_list(),
		));

		return true;
	}

	private function mod_details($category)
	{

	}
}