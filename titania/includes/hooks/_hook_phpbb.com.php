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

function phpbb_com_header($page_title)
{
	// Setup the phpBB.com header
	include(TITANIA_ROOT . '../../vars.' . PHP_EXT);
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/');
	phpbb::$template->set_filenames(array(
		'phpbb_com_header'		=> 'overall_header.html',
	));
	phpbb::$template->assign_display('phpbb_com_header', 'PHPBB_COM_HEADER', false);

	titania::set_custom_template();
}