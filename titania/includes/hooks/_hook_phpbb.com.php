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

titania::$hook->register('titania_page_header', 'phpbb_com_header');
titania::$hook->register('titania_page_footer', 'phpbb_com_footer');

function phpbb_com_header($page_title)
{
	phpbb::$template->assign_vars(array(
		'S_BODY_CLASS'		=> 'customise customisation-database',
	));

	// Setup the phpBB.com header
	include(TITANIA_ROOT . '../../vars.' . PHP_EXT);
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/');
	phpbb::$template->set_filenames(array(
		'phpbb_com_header'		=> 'overall_header.html',
		'phpbb_com_footer'		=> 'overall_footer.html',
	));
	phpbb::$template->assign_display('phpbb_com_header', 'PHPBB_COM_HEADER', false);
	phpbb::$template->assign_display('phpbb_com_footer', 'PHPBB_COM_FOOTER', false);

	titania::set_custom_template();
}

function phpbb_com_footer($run_cron, $template_body)
{
	// Setup the phpBB.com footer
	include(TITANIA_ROOT . '../../vars.' . PHP_EXT);
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/');
	phpbb::$template->set_filenames(array(
		'phpbb_com_footer'		=> 'overall_footer.html',
	));
	phpbb::$template->assign_display('phpbb_com_footer', 'PHPBB_COM_FOOTER', false);

	titania::set_custom_template();
}