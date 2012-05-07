<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

global $phpbb_root_path;

/**
* Configuration needed!
*
* Set the titania root path here
*/
define('TITANIA_ROOT', $phpbb_root_path . '../customise/db/');

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* mcp_titania
* Handling the titania stuff (proxy for some stuff in titania/manage/
*/
class mcp_titania
{
	var $p_master;
	var $u_action;

	function mcp_titania(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $phpbb_root_path;

		define('PHPBB_INCLUDED', true);
		define('USE_PHPBB_TEMPLATE', true);

		define('IN_TITANIA', true);
		if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
		require TITANIA_ROOT . 'common.' . PHP_EXT;

		// Need a few hacks to be used from within phpBB
		titania_url::decode_url(titania::$config->phpbb_script_path);
		titania::$hook->register(array('titania_url', 'build_url'), 'titania_outside_build_url', 'standalone');
		titania::$hook->register(array('titania_url', 'append_url'), 'titania_outside_build_url', 'standalone');
		titania::$hook->register(array('titania', 'page_header'), 'titania_outside_page_header', 'standalone');
		titania::$hook->register(array('titania', 'page_footer'), 'titania_outside_page_footer', 'standalone');
		titania::$hook->register('titania_generate_text_for_display', 'titania_outside_generate_text_for_display', 'standalone');

		titania::add_lang('manage');

		$this->p_master->assign_tpl_vars(phpbb::append_sid('mcp'));

		phpbb::$template->assign_vars(array(
			'L_TITLE'		=> phpbb::$user->lang['ATTENTION'],
			'L_EXPLAIN'		=> '',
		));

		include(TITANIA_ROOT . 'manage/attention.' . PHP_EXT);
	}
}

function titania_outside_generate_text_for_display(&$hook, $text, $uid, $bitfield, $flags)
{
	return generate_text_for_display($text, $uid, $bitfield, $flags);
}

function titania_outside_build_url(&$hook, $base, $params = array())
{
	if ($base == 'manage/attention' || $base == titania_url::$current_page || strpos($base, 'mcp.' . PHP_EXT))
	{
		return phpbb::append_sid('mcp', array_merge(array('i' => 'titania', 'mode' => 'attention'), $params));
	}
}

function titania_outside_page_header(&$hook, $page_title)
{
	page_header($page_title);

	return true;
}

function titania_outside_page_footer(&$hook, $run_cron)
{
	page_footer(false);

	return true;
}
