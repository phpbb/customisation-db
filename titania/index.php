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
				$object->get_rating();
				$redirect = $object->get_url();

				if (!$object || !$object->author_id)
				{
					trigger_error('AUTHOR_NOT_FOUND');
				}
			break;

			case 'contrib' :
				$object = new titania_contribution();
				$object->load($id);
				$object->get_rating();
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

		$result = ($value == -1) ? $object->rating->delete_rating() : $object->rating->add_rating($value);
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

		// Get the revision, contribution, attachment, and queue
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
		if (!titania_types::$types[$contrib->contrib_type]->acl_get('view'))
		{
			titania::needs_auth();
		}
		$revision_attachment = new titania_attachment(TITANIA_CONTRIB);
		$revision_attachment->attachment_id = $revision->attachment_id;
		if (!$revision_attachment->load())
		{
			trigger_error('ERROR_NO_ATTACHMENT');
		}
		$queue = $revision->get_queue();

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
				$uid = $bitfield = $flags = false;
				generate_text_for_storage($mpv_results, $uid, $bitfield, $flags, true, true, true);

				// Add the MPV Results to the queue
				$queue->mpv_results = $mpv_results;
				$queue->mpv_results_bitfield = $bitfield;
				$queue->mpv_results_uid = $uid;
				$queue->submit();
			}
		}
		else if ($action == 'automod')
		{
			$new_dir_name = $contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version));

			// Start up the machine
			$contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);

			// Automod testing time
			$details = '';
			$automod_results = array();
			$sql = 'SELECT row_id, phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
				WHERE revision_id = ' . $revision->revision_id;
			$result = phpbb::$db->sql_query($sql);
			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				$version_string = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' .$row['phpbb_version_revision'];
				$phpbb_path = $contrib_tools->automod_phpbb_files($version_string);

				if ($phpbb_path === false)
				{
					$error = array_merge($error, $contrib_tools->error);
					continue;
				}

				phpbb::$template->assign_vars(array(
					'PHPBB_VERSION'		=> $version_string,
					'TEST_ID'			=> $row['row_id'],
				));

				$test_results = '';
				$contrib_tools->automod($phpbb_path, $details, $test_results);

				$automod_results[] = $test_results;
			}
			phpbb::$db->sql_freeresult($result);

			$automod_results = implode('', $automod_results);

			// Update the queue with the results
			$queue->automod_results = $automod_results;
			$queue->submit();
		}

		redirect(titania_url::build_url('manage/queue', array('queue' => titania_types::$types[$queue->queue_type]->url, 'q' => $queue->queue_id)));
	break;

	case 'all' :
		// Setup the sort tool
		$sort = new titania_sort();
		$sort->set_sort_keys(contribs_overlord::$sort_by);
		$sort->default_key = 't';
		$sort->default_dir = 'd';

		contribs_overlord::display_contribs('all', false, $sort);

		titania::page_header('CUSTOMISATION_DATABASE');
		titania::page_footer(true, 'all_contributions.html');
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
			// Breadcrumbs
			$category_object = new titania_category;
			$categories_ary = titania::$cache->get_categories();

			// Parents
			foreach (array_reverse(titania::$cache->get_category_parents($category_id)) as $row)
			{
				$category_object->__set_array($categories_ary[$row['category_id']]);
				titania::generate_breadcrumbs(array(
					((isset(phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']])) ? phpbb::$user->lang[$categories_ary[$row['category_id']]['category_name']] : $categories_ary[$row['category_id']]['category_name'])	=> titania_url::build_url($category_object->get_url()),
				));
			}

			// Self
			$category_object->__set_array($categories_ary[$category_id]);
			titania::generate_breadcrumbs(array(
				((isset(phpbb::$user->lang[$categories_ary[$category_id]['category_name']])) ? phpbb::$user->lang[$categories_ary[$category_id]['category_name']] : $categories_ary[$category_id]['category_name'])	=> titania_url::build_url($category_object->get_url()),
			));
			unset($categories_ary, $category_object);

			contribs_overlord::display_contribs('category', $category_id);
		}

		phpbb::$template->assign_vars(array(
			'U_CREATE_CONTRIBUTION'		=> (phpbb::$auth->acl_get('u_titania_contrib_submit')) ? titania_url::build_url('author/' . phpbb::$user->data['username_clean'] . '/create') : '',
		));
	break;
}

titania::page_header('CUSTOMISATION_DATABASE');
titania::page_footer(true, 'index_body.html');
