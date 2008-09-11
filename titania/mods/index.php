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

$user->add_lang(array('titania_contrib', 'titania_mods'));

$mode = request_var('mode', '');
$tag_type = 'MOD';

switch ($mode)
{
	case 'details':
	
		$page_title = 'MOD_DETAILS';
		$template_body = 'mods/mod_detail.html';

		require(TITANIA_ROOT . 'includes/class_contrib_mod.' . PHP_EXT);

		try
		{
			$mod = new titania_modification(request_var('contrib_id', 0));
			$mod->load();

			$author = $mod->get_author();
		}
		catch (NoDataFoundException $e)
		{
			trigger_error('CONTRIB_NOT_FOUND');
		}

	break;
	
	case 'faq':
	
		$page_title = 'MOD_FAQ';
		$template_body = 'mods/mod_faq.html';
	
	break;

	case 'reviews':
	
		$page_title = 'MOD_REVIEWS';
		$template_body = 'mods/mod_reviews.html';

	break;

	case 'list':

		$titania->page = TITANIA_ROOT . 'mods/index.' . PHP_EXT;

		$page_title = $tag_type . '_LIST';
		$template_body = 'mods/mod_list.html';

	break;

	case 'categories':
	default:

		$page_title = $tag_type . '_CATEGORIES';
		$template_body = 'mods/mod_categories.html';

	break;
}

// Output page
$titania->page_header($user->lang[$page_title]);

$template->set_filenames(array(
	'body' => $template_body,
));

$titania->page_footer();

