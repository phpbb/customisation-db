<?php
/**
*
* @package titania
* @version $Id: index.php 77 2008-08-25 09:11:31Z HighwayofLife $
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

$user->add_lang(array('titania_contrib', 'titania_authors'));

$mode = request_var('mode', '');
$tag_type = 'AUTHOR';

switch ($mode)
{
	case 'profile':
		$page_title = 'AUTHOR_PROFILE';
		$template_body = 'authors/author_profile.html';

		require(TITANIA_ROOT . 'includes/class_author.' . PHP_EXT);

		try
		{
			$author = new titania_author(request_var('author_id', 0));
			$author->load();
		}
		catch (NoDataFoundException $e)
		{
			trigger_error('AUTHOR_NOT_FOUND');
		}
		
		/**
		* @TODO
		* Send author data to the template
		**/
		
	break;

	case 'list':
	default:

		$titania->page = TITANIA_ROOT . 'authors/index.' . PHP_EXT;

		$page_title = $tag_type . '_LIST';
		$template_body = 'authors/author_list.html';
		
		/**
		* @TODO
		* Send authors to template
		* Uses $titania->author_list()
		**/
		
	break;

}

// Output page
$titania->page_header($user->lang[$page_title]);

$template->set_filenames(array(
	'body' => $template_body,
));

$titania->page_footer();

?>