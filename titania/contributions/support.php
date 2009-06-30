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

$action = request_var('action', '');

switch ($action)
{
	case 'post' :
		titania::page_header('CONTRIB_SUPPORT_ADD');
		titania::page_footer(true, 'contributions/contribution_support_post.html');
	break;

	case 'edit' :
		titania::page_header('CONTRIB_SUPPORT_EDIT');
		titania::page_footer(true, 'contributions/contribution_support_post.html');
	break;

	case 'delete' :
		if (confirm_box(true))
		{

		}
		else
		{
			confirm_box(false, 'CONTRIB_SUPPORT_DELETE');
		}
		redirect(titania::$contrib->get_url() . '/support');
	break;

	default :
		phpbb::$user->add_lang('viewforum');

		titania_display_forums('contrib', titania::$contrib);

		titania::page_header('CONTRIB_SUPPORT');
		titania::page_footer(true, 'contributions/contribution_support.html');
	break;
}