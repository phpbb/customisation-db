<?php
/**
 *
 * @package titania
 * @version $Id: index.php 199 2009-04-11 19:54:15Z bantu $
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
require TITANIA_ROOT . 'includes/objects/style.' . PHP_EXT;

titania::add_lang(array('contribution', 'contribution_style'));

$id			= request_var('id', '');
$mode		= request_var('mode', '');
$contrib_id	= request_var('contrib_id', 0);

switch ($id)
{
	case 'details':
	case 'email':
	case 'faq':
	case 'screenshots':
	case 'support':
		$style = new titania_style($contrib_id);

		if (!$style->load() || $style->contrib_type != CONTRIB_TYPE_STYLE)
		{
			titania::trigger_error('ERROR_CONTRIB_NOT_FOUND', E_USER_NOTICE, HEADER_NOT_FOUND);
		}
	break;

	default:
	break;
}

switch ($id)
{
	case 'details':
		$page_title = 'CONTRIB_DETAILS';
		$template_body = 'contributions/contribution_details.html';

		$style->assign_common();
		$style->assign_details();
	break;

	case 'email':
		$page_title = 'MOD_EMAIL';
		$template_body = 'contributions/contribution_email.html';

		$style->assign_common();

		if ($style->email_friend())
		{
			// e-mail sent
			$template_body = 'contributions/contribution_details.html';

			$style->assign_details();
		}
	break;

	case 'faq':
		$page_title = 'CONTRIB_FAQ';
		$template_body = 'contributions/contribution_faq.html';

		if (!class_exists('titania_faq'))
		{
			require TITANIA_ROOT . 'includes/objects/faq.' . PHP_EXT;
		}

		$style->assign_common(); // @dev

		titania_faq::faq_list($contrib_id);
	break;

	case 'list':
		$page_title = 'STYLE_LIST';
		$template_body = 'styles/styles_list.html';
	break;

	case 'categories':
	default:
		$id = 'categories';

		$page_title = 'STYLE_CATEGORIES';
		$template_body = 'styles/styles_categories.html';
	break;
}

// Output page
titania::page_header($page_title);

phpbb::$template->assign_vars(array(
	'S_IS_STYLE'	=> true,
	'S_MODE'		=> $mode,
	'S_MODE_ID'		=> $id,
));

phpbb::$template->set_filenames(array(
	'body' => $template_body,
));

titania::page_footer();

