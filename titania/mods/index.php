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

$mode 		= request_var('mode', '');
$action 	= request_var('action', '');
$contrib_id = request_var('contrib_id', 0);

$submit		= (isset($_POST['submit'])) ? true : false;

$tag_type = 'MOD';

switch ($mode)
{
	case 'details':
		$page_title = 'MOD_DETAILS';
		$template_body = 'mods/mod_detail.html';

		require(TITANIA_ROOT . 'includes/class_contrib_mod.' . PHP_EXT);

		try
		{
			$mod = new titania_modification($contrib_id);
			$mod->load();

			$author = $mod->get_author();
		}
		catch (NoDataFoundException $e)
		{
			trigger_error('CONTRIB_NOT_FOUND');
		}
	break;
	
	case 'faq':
		require(TITANIA_ROOT . 'includes/class_faq.' . PHP_EXT);
		
		$faq = new titania_faq(request_var('faq_id', 0));
		
		switch ($action)
		{
			case 'add':
			case 'edit':
				if ($submit)
				{
					$subject 	= utf8_normalize_nfc(request_var('subject', '', true));
					$text 		= utf8_normalize_nfc(request_var('text', '', true));
					
					// @todo
					$faq->submit();
				}
				
				$page_title = $tag_type . '_FAQ_' . strtoupper($action);
				$template_body = 'mods/mod_faq_edit.html';
				
				if ($action == 'edit')
				{
					$faq->load();
				}
				
				// @todo
				
				$template->assign_vars(array(
					'FAQ_SUBJECT'			=> $faq->faq_subject,
					'FAQ_TEXT'				=> $faq->faq_text,
					'FAQ_CONTRIB_VERSION'	=> $faq->contrib_version
				));
			break;
			
			case 'delete':
				// check
				if (confirm_box(true))
				{
					$faq->delete();
					
					// @todo: redirect
				}
				else
				{
					$s_hidden_fields = build_hidden_fields(array(
						'submit'	=> true,
						'faq_id'	=> $faq->faq_id
					));
					
					confirm_box(false, 'FAQ_DELETE', $s_hidden_fields);
				}
			break;
			
			case 'details':
				$page_title = $tag_type . '_FAQ_DETAILS';
				$template_body = 'mods/mod_faq_details.html';
				
				// @todo			
			break;
			
			default:
				$page_title = $tag_type . '_FAQ_LIST';
				$template_body = 'mods/mod_faq_list.html';
				
				// @todo				
				$faq->faqs_list($contrib_id);
			break;
		}
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

