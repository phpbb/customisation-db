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
	* Rerun the MPV or Automod test for the queue
	*/
	case 'mpv' :
	case 'automod' :
		$revision_id = request_var('revision', 0);
		titania::add_lang('contributions');

		$revision = new titania_revision(false, $revision_id);
		if (!$revision->load())
		{
			trigger_error('NO_REVISION');
		}
		$contrib = new titania_contribution();
		if (!$contrib->load($revision->contrib_id))
		{
			trigger_error('CONTRIB_NOT_FOUND');
		}
		$revision->contrib = $contrib;
		if (!titania_types::$types[$contrib->contrib_type]->acl_get('validate'))
		{
			trigger_error('NO_AUTH');
		}
		$revision_attachment = new titania_attachment(TITANIA_CONTRIB);
		$revision_attachment->attachment_id = $revision->attachment_id;
		if (!$revision_attachment->load())
		{
			trigger_error('ERROR_NO_ATTACHMENT');
		}

		$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
		$download_package = titania_url::build_url('download', array('id' => $revision_attachment->attachment_id));

		if ($action == 'mpv')
		{
			// Start up the machine
			$contrib_tools = new titania_contrib_tools($zip_file);

			// Run MPV
			$mpv_results = $contrib_tools->mpv($download_package);

			if ($mpv_results === false)
			{
				// Too lazy to write another one...teams only anyways
				trigger_error('MPV_TEST_FAILED');
			}
			else
			{
				// Add the MPV Results to the queue topic
				$pattern = '#' . str_replace(array('[', ']', '%s'), array('\[', '\]', '([^"]+)'), phpbb::$user->lang['MPV_TEST_FAILED_QUEUE_MSG']) . '#';
				$redirect = $revision->queue_topic(false, $mpv_results, $pattern);

				redirect($redirect);
			}
		}
		else if ($action == 'automod')
		{
			exit;
			$new_dir_name = $contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version));

			// Start up the machine
			$contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);

			//$contrib_tools->restore_root();
			//$contrib_tools->replace_zip();

			// Prepare the phpbb files for automod
			$phpbb_path = $contrib_tools->automod_phpbb_files($revision->phpbb_version);

			// Automod test
			$details = $results = '';
			$contrib_tools->automod($phpbb_path, $details, $results);
			//var_dump($details);
			//echo '<br /><br /><br />';
			echo $results;
			exit;

		}
	break;

	/**
	* Default (display category/contrib list)
	*/
	default :
		titania::_include('functions_display', 'titania_display_categories');

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

		titania_display_categories($category_id);

		if ($category_id != 0)
		{
			titania_display_contribs('category', $category_id);
		}

		phpbb::$template->assign_vars(array(
			'U_CREATE_CONTRIBUTION'		=> (phpbb::$auth->acl_get('u_titania_contrib_submit')) ? titania_url::build_url('author/' . phpbb::$user->data['username_clean'] . '/create') : '',
		));
	break;
}

titania::page_header('CUSTOMISATION_DATABASE');

titania::page_footer(true, 'index_body.html');
