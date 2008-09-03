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

$user->add_lang('titania_contrib');

$mode = request_var('mode', '');
$tag_type = 'STYLE';

switch ($mode)
{
	case 'details':
		$page_title = 'STYLE_DETAILS';
		$template_body = 'styles/styles_detail.html';
	break;

	case 'list':
		$titania->page = TITANIA_ROOT . 'styles/index.' . PHP_EXT;

		$page_title = $tag_type . '_LIST';
		$template_body = 'styles/styles_list.html';
	break;

	case 'categories':
	default:
		$page_title = $tag_type . '_CATEGORIES';
		$template_body = 'styles/styles_categories.html';
	break;
}

// Output page
$titania->page_header($user->lang[$page_title]);

$template->set_filenames(array(
	'body' => $template_body,
));

$titania->page_footer();

