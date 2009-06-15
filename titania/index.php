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

$current_category = display_categories($cat_id);

if ($cat_id != 0)
{
	display_contribs($cat_id);
}

titania::page_header('CUSTOMISATION_DATABASE');

titania::page_footer(true, 'index_body.html');
