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
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);

require(TITANIA_ROOT . 'includes/class_contrib.' . PHP_EXT);

$titania->add_lang('mods/titania_mods');

$mode = request_var('mode', '');
$tag_type = 'MOD';

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
		$mods = new titania_contribution();
		$mods->page = TITANIA_ROOT . 'mods/index.' . PHP_EXT;

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