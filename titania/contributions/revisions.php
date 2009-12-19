<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2009 Customisation Database Team
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

// Setup basic variables and objects
titania::add_lang('revisions');

$action 	= request_var('action', '');

// Load contrib information
load_contrib();

class titania_revisons_page
{
	public static function main($action)
	{

		$submit		= isset($_POST['submit']) ? true : false;
		$form_key	= 'revisions';
		$revision	= new titania_revision(request_var('r', 0));
		$revision->attachment = new titania_attachments(TITANIA_DOWNLOAD_CONTRIB, 'revisions');

		switch ($action)
		{
			case 'create':
			case 'edit':

				if ($submit)
				{
					$error = array();

					$revision->request_data();

					// Check form key
					if (!check_form_key($form_key))
					{
						// @todo If a user does not submit the form in time, we should send back fresh form toekens, or
						// we can have the form disaper after a certain length of time to avoid this. However we still
						// must check this for security if we use JS to hide or disable the form after a length of time
						// has passed.
						$error[] = phpbb::$user->lang['FORM_INVALID'];
					}

					if (sizeof($error))
					{
						phpbb::$template->assign_var('ERROR', implode('<br />', $error));
					}
					else
					{
						$revision->submit();
						$revision->attachment->update_orphans($revision->revision_id, $revision->attachment_id);

						// Setup response.
						phpbb::$template->set_filenames(array(
							'revisions'		=> 'contributions/contribution_revisions_list.html',
						));

						$revision->display($revision->revision_id);

						phpbb::$template->assign_var('S_AJAX', true);

						$error['html'] 	= phpbb::$template->assign_display('revisions');
						$error['id'] 	= $revision->revision_id;

					}
				}

				// Set header for JSON response.
				header('Content-type: application/json');

				// We dont want the page_header to run.
				define('HEADER_INC', true);

				// Set up the template.
				phpbb::$template->set_filenames(array(
					'body'		=> 'common/json_response.html',
				));

				phpbb::$template->assign_vars(array(
					'JSON'				=> json_encode($error),
				));

				titania::page_header();
				titania::page_footer(false);
			break;

			case 'delete':

				if (titania::confirm_box(true))
				{
					$revision->delete();

					titania::error_box('SUCCESS', 'Revision Deleted');

					self::main('display');
				}
				else
				{
					titania::confirm_box(false, 'DELETE_REVISION', titania::$contrib->get_url('revisions'), array('action'	=> 'delete', 'r' => $revision->revision_id));
				}

			break;

			case 'display':
			default:

				titania::page_header('REVISIONS');

				add_form_key($form_key);

				$can_manage = true;

				// For now we will only check basic permisions. Must be an anther or team member to manage revisions.
				if (!titania::$contrib->is_author || titania::$access_level > TITANIA_ACCESS_TEAMS)
				{
					$can_manage = false;
				}

				$revision->display();

				phpbb::$template->assign_vars(array(
					'CAN_MANAGE'			=> $can_manage,
					'U_SUBMIT_REVISION'		=> $revision->get_url('create'),
				));

				// Setup uploader.
				$revision->attachment->display_uploader(array('on_complete' => 'revision_upload_complete'), array('object_type' => 'revisions'));
			break;
		}

		phpbb::$template->assign_vars(array(
			'CONTRIB_NAME'		=> titania::$contrib->contrib_name,
		));

		titania::page_footer(false, 'contributions/contribution_revisions.html');
	}
}

// Run page.
titania_revisons_page::main($action);