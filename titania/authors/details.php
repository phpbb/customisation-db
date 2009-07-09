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
if (!defined('IN_TITANIA'))
{
	exit;
}

$submit = (isset($_POST['submit'])) ? true : false;

add_form_key('titania_author');

if ($submit && !check_form_key('titania_author'))
{
        trigger_error('INVALID_FORM');
}

titania::$author->load();

switch ($page)
{
        case 'edit':
                $page_title = 'EDIT_AUTHOR';
                $tpl_name = 'authors/author_edit.html';
                
                $errors = array();
                
                if ($submit)
                {
                        titania::$author->author_realname       = utf8_normalize_nfc(request_var('realname', '', true));
                        titania::$author->author_website        = request_var('website', '');
                        titania::$author->author_desc           = utf8_normalize_nfc(request_var('message', ''));
                        
                        if (!titania::$author->author_desc)
                        {
                                $errors[] = phpbb::$user->lang['NO_DESC'];
                        }
                        
                        if (!sizeof($errors))
                        {
                                // updating the data
                                titania::$author->submit();
                                
                                // redirecting to the details page
                                $redirect_url = titania::$author->get_url();
                                meta_refresh(3, $redirect_url);
                                
                                titania::error_box('SUCCESS', 'AUTHOR_DATA_UPDATED', TITANIA_SUCCESS);
                        }
                }
                
                $template->assign_vars(array(
                        'S_POST_ACTION'         => titania::$author->get_url('edit'),
                        
                        'ERROR_MSG'             => (sizeof($errors)) ? implode('<br />', $errors) : false,
                        
                        'AUTHOR_REALNAME'       => titania::$author->author_realname,
                        'AUTHOR_WEBSITE'        => titania::$author->author_website,
                        'MESSAGE'               => titania::$author->generate_text_for_edit(),
                ));
        break;

        default:
                $page_title = 'AUTHOR_DETAILS';
                $tpl_name = 'authors/author_details.html';
                
                titania::$author->get_rating();
                titania::$author->assign_details();
        break;
}


titania::page_header($page_title);
titania::page_footer(true, $tpl_name);