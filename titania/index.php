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

$cat_id = request_var('c', 0);
$action = request_var('action', '');

switch ($action)
{
	/**
	* Rate something & remove a rating from something
	*/
	case 'rate' :
		$type = request_var('type', '');
		$id = request_var('id', 0);

		switch ($type)
		{
			case 'author' :
				titania::load_object('author');
				$object = new titania_author($id);
				$redirect = titania_sid('authors/index', 'u=' . $id);

				if (!$object)
				{
					trigger_error('AUTHOR_NOT_FOUND');
				}
			break;

			case 'contrib' :
				titania::load_object('contribution');
				$object = new titania_contribution($id);
				$redirect = titania_sid('contributions/index', 'c=' . $id);

				if (!$object)
				{
					trigger_error('CONTRIB_NOT_FOUND');
				}
			break;

			default :
				trigger_error('BAD_RATING');
			break;
		}

		titania::load_object('rating');
		$rating = new titania_rating($type, $object);
		$rating->load();

		$value = request_var('value', 0.0);
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
		$current_category = display_categories($cat_id);

		if ($cat_id != 0)
		{
			display_contribs($cat_id);
		}
	break;
}

titania::page_header('CUSTOMISATION_DATABASE');

titania::page_footer(true, 'index_body.html');
