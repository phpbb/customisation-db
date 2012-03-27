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

load_contrib();

if (!titania::$contrib->contrib_demo || titania::$contrib->contrib_status != TITANIA_CONTRIB_APPROVED || titania::$contrib->contrib_type != TITANIA_TYPE_STYLE || !titania::$contrib->integrate_demo)
{
	trigger_error('NO_DEMO');
}

titania::page_header(phpbb::$user->lang['STYLES_DEMO'] . ' - ' . titania::$contrib->contrib_name);

$demo = new titania_styles_demo(titania::$contrib->contrib_id);
$demo->load_styles();
$demo->assign_details();

titania::page_footer(false, 'contributions/demo.html');