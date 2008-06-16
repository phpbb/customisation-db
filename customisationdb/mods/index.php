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
define('IN_PHPBB', true);
define('IN_TITANIA', true);
define('TITANIA_ROOT', './../includes/titania/');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include(TITANIA_ROOT . 'common.' . $phpEx);

$mode = request_var('mode', '');

switch ($mode)
{
	case 'detail':

		$page_title = 'MOD_DETAILS';
		$template_body = 'mods/mod_detail.html';
	break;

	case 'reviews':

		$page_title = 'MOD_REVIEWS';
		$template_body = 'mods/mod_reviews.html';
	break;

	case 'list':
	default:

		$page_title = $tag_type . '_LIST';
		$template_body = 'mods/mod_list.html';
	break;
}

// Output page
page_header($user->lang[$page_title]);

$template->set_filenames(array(
	'body' => $template_body,
));

page_footer();

?>