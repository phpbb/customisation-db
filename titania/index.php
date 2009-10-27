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
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

// Get the category_id
$category = request_var('c', '');
$category_ary = explode('-', $category);

$category_id = ($category_ary) ? (int) array_pop($category_ary) : (int) $category;

$action = request_var('action', '');

switch ($action)
{
	/**
	* Rate something & remove a rating from something
	*/
	case 'rate' :
		$rating = new titania_rating();
		$rating->determine_rating();
	break;

	/**
	* Default (display category/contrib list)
	*/
	default :
		if ($category_id)
		{
			titania::add_lang('contributions');
			titania_display_contribs('category', $category_id);

			phpbb::$template->assign_vars(array(
				'U_CREATE_CONTRIBUTION'		=> (phpbb::$auth->acl_get('titania_contrib_submit')) ? titania::$url->build_url('contributions/create') : '',
			));
		}
		else
		{
			$categories = titania::$cache->get_categories();

			$tag = new titania_tag();

			foreach ($categories as $cat_id => $row)
			{
				$tag->__set_array($row);

				if (!phpbb::$config['titania_display_empty_cats'] && !$tag->tag_items)
				{
					continue;
				}

				phpbb::$template->assign_block_vars('categories', $tag->assign_display(true));
			}
		}
	break;
}

titania::page_header('CUSTOMISATION_DATABASE');

titania::page_footer(true, 'index_body.html');
