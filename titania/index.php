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
if ($category_ary)
{
	$category_id = array_pop($category_ary);
}
else
{
	$category_id = (int) $category;
}

$action = request_var('action', '');

switch ($action)
{
	/**
	* Rate something & remove a rating from something
	*/
	case 'rate' :
		$type = request_var('type', '');
		$id = request_var('id', 0);
		$value = request_var('value', -1.0);

		switch ($type)
		{
			case 'author' :
				$object = new titania_author();
				$object->load($id);
				$redirect = $object->get_url();

				if (!$object)
				{
					trigger_error('AUTHOR_NOT_FOUND');
				}
			break;

			case 'contrib' :
				$object = new titania_contribution();
				$object->load($id);
				$redirect = $object->get_url();

				if (!$object)
				{
					trigger_error('CONTRIB_NOT_FOUND');
				}
			break;

			default :
				trigger_error('BAD_RATING');
			break;
		}

		$rating = new titania_rating($type, $object);
		$rating->load();

		$result = ($value == -1) ? $rating->delete_rating() : $rating->add_rating($value);
		if ($result)
		{
			redirect($redirect);
		}
		else
		{
			trigger_error('BAD_RATING');
		}
	break;

	/**
	* Default (display category/contrib list)
	*/
	default :
		titania::_include('functions_display', 'titania_display_categories');

		titania_display_categories($category_id);

		if ($category_id != 0)
		{
			titania_display_contribs('category', $category_id);
		}

		phpbb::$template->assign_vars(array(
			'U_CREATE_CONTRIBUTION'		=> (phpbb::$auth->acl_get('titania_contrib_submit')) ? titania::$url->build_url('contributions/create') : '',
		));
	break;
}

titania::page_header('CUSTOMISATION_DATABASE');

titania::page_footer(true, 'index_body.html');
