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

/*
 * This is a temporary function
 *
 * @param array $selected
 * @return void
 */
function generate_category_select($selected = false)
{
	if (!is_array($selected))
	{
		$selected = array($selected);
	}
	
	$sql = 'SELECT *
		FROM ' . TITANIA_CATEGORIES_TABLE . '
		WHERE category_visible = 1';
	$result = phpbb::$db->sql_query($sql);

	while ($category = phpbb::$db->sql_fetchrow($result))
	{
		phpbb::$template->assign_block_vars('category_select', array(
			'S_IS_SELECTED'		=> (in_array($category['category_id'], $selected)) ? true : false,
			
			'VALUE'				=> $category['category_id'],
			'NAME'				=> (isset(phpbb::$user->lang[$category['category_name']])) ? phpbb::$user->lang[$category['category_name']] : $category['category_name'],
		));
	}
	phpbb::$db->sql_freeresult($result);
}

/*
 * Create a select with the contrib types
 *
 * @param array $selected
 * @return void
 */
function generate_type_select($selected = false)
{
	phpbb::$template->assign_block_vars('type_select', array(
		'S_IS_SELECTED'		=> ($selected) ? false : true,
		
		'VALUE'				=> 0,
		'NAME'				=> phpbb::$user->lang['SELECT_CONTRIB_TYPE'],
	));
	
	$select_items = array(
		TITANIA_TYPE_MOD 		=> 'MODIFICATION',
		TITANIA_TYPE_STYLE		=> 'STYLE',
		TITANIA_TYPE_SNIPPET	=> 'SNIPPET',
		TITANIA_TYPE_LANG_PACK	=> 'LANGUAGE_PACK',
	);
	
	foreach ($select_items as $key => $lang_key)
	{
		phpbb::$template->assign_block_vars('type_select', array(
			'S_IS_SELECTED'		=> ($key === $selected) ? true : false,
			
			'VALUE'				=> $key,
			'NAME'				=> phpbb::$user->lang[$lang_key],
		));
	}
}
	
/**
* Generate the _options flag from the given settings
*
* @param bool $bbcode
* @param bool $smilies
* @param bool $url
* @return int options flag
*/
function get_posting_options($bbcode, $smilies, $url)
{
	return (($bbcode) ? OPTION_FLAG_BBCODE : 0) + (($smilies) ? OPTION_FLAG_SMILIES : 0) + (($url) ? OPTION_FLAG_LINKS : 0);
}

/**
* Reverses the posting options
*
* @param int $options The given posting options
* @param bool $bbcode
* @param bool $smilies
* @param bool $url
*/
function reverse_posting_options($options, &$bbcode, &$smilies, &$url)
{
	$bbcode = ($options & OPTION_FLAG_BBCODE) ? true : false;
	$smilies = ($options & OPTION_FLAG_SMILIES) ? true : false;
	$url = ($options & OPTION_FLAG_LINKS) ? true : false;
}

/*
 * Create select with Titania's accesses
 *
 * @param integer $default
 * @return string
 */
function titania_access_select($default = false)
{
	if (titania::$access_level == TITANIA_ACCESS_PUBLIC)
	{
		return '';
	}

	$access_types = array(
		TITANIA_ACCESS_TEAMS 	=> 'ACCESS_TEAMS',
		TITANIA_ACCESS_AUTHORS 	=> 'ACCESS_AUTHORS',
		TITANIA_ACCESS_PUBLIC 	=> 'ACCESS_PUBLIC',
	);

	if (!$default)
	{
		$default = TITANIA_ACCESS_PUBLIC;
	}

	$s_options = '';

	foreach ($access_types as $type => $lang_key)
	{
		if (titania::$access_level > $type)
		{
			continue;
		}

		$selected = ($default == $type) ? ' selected="selected"' : '';
		$s_options .= '<option value="' . $type . '"' . $selected . '>' . phpbb::$user->lang[$lang_key] . '</option>';
	}

	return $s_options;
}