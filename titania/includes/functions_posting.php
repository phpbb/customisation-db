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
* Display posting bbcode/smilies
*
* @param mixed $display _options for the item, see get_posting_options
* @param bool $auth_bbcode Are they allowed to post BBCode?
* @param bool $auth_smilies Are they allowed to post Smilies?
* @param bool $auth_flash Are they allowed to post Flash?
*/
function display_posting_bbcode_smilies($options = 7, $auth_bbcode = true, $auth_smilies = true, $auth_flash = false)
{
	$bbcode_status = (phpbb::$config['allow_bbcode'] && $auth_bbcode) ? true : false;
	$smilies_status = (phpbb::$config['allow_smilies'] && $auth_smilies) ? true : false;
	$img_status = ($bbcode_status) ? true : false;
	$url_status = (phpbb::$config['allow_post_links'] && $bbcode_status) ? true : false;
	$flash_status = ($auth_flash && $bbcode_status) ? true : false;

	$enable_bbcode = ($bbcode_status && ($options & OPTION_FLAG_BBCODE)) ? true : false;
	$enable_smilies = ($smilies_status && ($options & OPTION_FLAG_SMILIES)) ? true : false;
	$enable_magic_url = ($url_status && ($options & OPTION_FLAG_LINKS)) ? true : false;

	if ($bbcode_status)
	{
		if (!function_exists('display_custom_bbcodes'))
		{
			include(PHPBB_ROOT_PATH . 'includes/functions_display.' . PHP_EXT);
		}

		// Build custom bbcodes array
		display_custom_bbcodes();

		if ($smilies_status)
		{
			// Generate smiley listing
			titania_generate_smilies();
		}
	}

	phpbb::$template->assign_vars(array(
		// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
		'S_BBCODE_CHECKED'			=> ($enable_bbcode) ? '' : ' checked="checked"',
		'S_SMILIES_CHECKED'			=> ($enable_smilies) ? '' : ' checked="checked"',
		'S_MAGIC_URL_CHECKED'		=> ($enable_magic_url) ? '' : ' checked="checked"',

		// To show the Options: section on the bottom left
		'BBCODE_STATUS'				=> sprintf(phpbb::$user->lang[(($bbcode_status) ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF')], '<a href="' . append_sid(titania::$absolute_board . 'faq.' . PHP_EXT, 'mode=bbcode') . '">', '</a>'),
		'IMG_STATUS'				=> ($img_status) ? phpbb::$user->lang['IMAGES_ARE_ON'] : phpbb::$user->lang['IMAGES_ARE_OFF'],
		'FLASH_STATUS'				=> ($flash_status) ? phpbb::$user->lang['FLASH_IS_ON'] : phpbb::$user->lang['FLASH_IS_OFF'],
		'SMILIES_STATUS'			=> ($smilies_status) ? phpbb::$user->lang['SMILIES_ARE_ON'] : phpbb::$user->lang['SMILIES_ARE_OFF'],
		'URL_STATUS'				=> ($url_status) ? phpbb::$user->lang['URL_IS_ON'] : phpbb::$user->lang['URL_IS_OFF'],

		// To show the option to turn each off while posting
		'S_BBCODE_ALLOWED'			=> $bbcode_status,
		'S_SMILIES_ALLOWED'			=> $smilies_status,
		'S_LINKS_ALLOWED'			=> $url_status,

		// To show the BBCode buttons for each on top
		'S_BBCODE_IMG'				=> $img_status,
		'S_BBCODE_URL'				=> $url_status,
		'S_BBCODE_FLASH'			=> $flash_status,
		'S_BBCODE_QUOTE'			=> true,
	));
}

/**
* Fill smiley templates (or just the variables) with smilies
*/
function titania_generate_smilies()
{
	$display_link = false;
	$sql = 'SELECT smiley_id
		FROM ' . SMILIES_TABLE . '
		WHERE display_on_posting = 0';
	$result = phpbb::$db->sql_query_limit($sql, 1, 0, 3600);

	if ($row = phpbb::$db->sql_fetchrow($result))
	{
		$display_link = true;
	}
	phpbb::$db->sql_freeresult($result);

	$last_url = '';

	$sql = 'SELECT *
		FROM ' . SMILIES_TABLE . '
			WHERE display_on_posting = 1
		ORDER BY smiley_order';
	$result = phpbb::$db->sql_query($sql, 3600);

	$smilies = array();
	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		if (empty($smilies[$row['smiley_url']]))
		{
			$smilies[$row['smiley_url']] = $row;
		}
	}
	phpbb::$db->sql_freeresult($result);

	if (sizeof($smilies))
	{
		foreach ($smilies as $row)
		{
			phpbb::$template->assign_block_vars('smiley', array(
				'SMILEY_CODE'	=> $row['code'],
				'A_SMILEY_CODE'	=> addslashes($row['code']),
				'SMILEY_IMG'	=> titania::$absolute_board . phpbb::$config['smilies_path'] . '/' . $row['smiley_url'],
				'SMILEY_WIDTH'	=> $row['smiley_width'],
				'SMILEY_HEIGHT'	=> $row['smiley_height'],
				'SMILEY_DESC'	=> $row['emotion'])
			);
		}
	}

	if ($display_link)
	{
		phpbb::$template->assign_vars(array(
			'S_SHOW_SMILEY_LINK' 	=> true,
			'U_MORE_SMILIES' 		=> append_sid(titania::$absolute_board . 'posting.' . PHP_EXT, 'mode=smilies&amp;f=' . $forum_id))
		);
	}
}