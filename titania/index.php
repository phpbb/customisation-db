<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

$action = phpbb::$request->variable('action', '');

switch ($action)
{
	case 'login':
		if (phpbb::$user->data['user_id'] != ANONYMOUS)
		{
			redirect(titania_url::build_url(''));
		}

		phpbb::login_box(titania_url::build_url(''));
	break;

	case 'logout':
		if (phpbb::$user->data['user_id'] != ANONYMOUS)
		{
			phpbb::$user->session_kill();
			phpbb::$user->session_begin();
		}

		redirect(titania_url::build_url(''));
	break;

	/**
	* Rate something & remove a rating from something
	*/
	case 'rate' :
		$type = phpbb::$request->variable('type', '');
		$id = phpbb::$request->variable('id', 0);
		$value = phpbb::$request->variable('value', -1.0);

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
	* Display all contributions
	*/
	case 'contributions' :
		// Mark all contribs read
		if (phpbb::$request->variable('mark', '') == 'contribs')
		{
			titania_tracking::track(TITANIA_CONTRIB, 0);
		}
		phpbb::$template->assign_vars(array(
			'U_MARK_TOPICS'			=> titania_url::append_url(titania_url::$current_page_url, array('mark' => 'contribs')),
			'L_MARK_TOPICS_READ'	=> phpbb::$user->lang['MARK_CONTRIBS_READ'],
		));

		$data = contribs_overlord::display_contribs('all', false);

		// Canonical URL
		$data['sort']->set_url('contributions');
		phpbb::$template->assign_var('U_CANONICAL', $data['sort']->build_canonical());

		titania::page_header('CUSTOMISATION_DATABASE');
		titania::page_footer(true, 'all_contributions.html');
	break;

	case 'stats' :
		titania::add_lang(array('queue_stats', 'contributions'));

		$contrib_type = request_var('type', '');
		$contrib_type = titania_types::type_from_url($contrib_type);

		if (!$contrib_type || !titania::$config->use_queue || !titania_types::$types[$contrib_type]->use_queue)
		{
			trigger_error('NO_QUEUE_STATS');
		}

		titania::_include('functions_display', 'create_calendar_ary');
		$stats = new titania_queue_stats($contrib_type);

		$total_revs_denied		= $stats->get_queue_item_count(TITANIA_QUEUE_DENIED);
		$total_revs_approved	= $stats->get_queue_item_count(TITANIA_QUEUE_APPROVED);
		$total_revs_validated	= $total_revs_denied + $total_revs_approved;

		if (!$total_revs_validated)
		{
			trigger_error('NO_QUEUE_STATS');
		}

		$validated_start_time	= offset_time_by_days(titania::$time, -365);
		$validated_cache_ttl	= round($total_revs_validated / 1000) * 86400;
		$validated_statuses		= array(TITANIA_QUEUE_DENIED, TITANIA_QUEUE_APPROVED);
		$closed_statuses		= array(TITANIA_QUEUE_CLOSED, TITANIA_QUEUE_DENIED, TITANIA_QUEUE_APPROVED, TITANIA_QUEUE_HIDE);

		$oldest_unvalidated_rev	= $stats->get_oldest_revision_time(false, $closed_statuses);
		$oldest_validated_rev	= $stats->get_oldest_revision_time($validated_statuses);
		$unvalidated_avg_wait	= $stats->get_average_wait(0, 0, true, false, $closed_statuses);
		$validated_avg_wait		= $stats->get_average_wait($validated_start_time, 0, false, $validated_statuses, false, $validated_cache_ttl);
		$revisions_in_queue		= $stats->get_queue_item_count(false, $closed_statuses);

		phpbb::$template->assign_vars(array(
			'DENIED_RATIO'					=> round(($total_revs_denied / $total_revs_validated) * 100),
			'APPROVED_RATIO'				=> round(($total_revs_approved / $total_revs_validated) * 100),
			'AVG_PAST_VALIDATION_TIME'		=> phpbb::$user->lang('AVG_PAST_VALIDATION_TIME', $validated_avg_wait),
			'AVG_CURRENT_QUEUE_WAIT'		=> phpbb::$user->lang('AVG_CURRENT_QUEUE_WAIT', $unvalidated_avg_wait),
			'OLDEST_UNVALIDATED_REV'		=> phpbb::$user->lang('OLDEST_UNVALIDATED_REV', format_time_delta($oldest_unvalidated_rev, titania::$time)),
			'NUM_REVISIONS_IN_QUEUE'		=> phpbb::$user->lang('NUM_REVISIONS_IN_QUEUE', $revisions_in_queue),
			'SINCE_X_VALIDATED_REVS'		=> phpbb::$user->lang('SINCE_X_VALIDATED_REVS', phpbb::$user->format_date($oldest_validated_rev, 'd M Y'), $total_revs_validated, $total_revs_denied, $total_revs_approved),
			'S_QUEUE_ACTIVE'				=> ($revisions_in_queue) ? true : false,
		));

		$history_start = offset_time_by_days(titania::$time, -30, true);

		$history = $stats->get_queue_history($history_start,  titania::$time);
		$stats->assign_history_display($history);
		$page_title = phpbb::$user->lang['QUEUE_STATS'] . ' - ' . titania_types::$types[$contrib_type]->langs;

		titania::generate_breadcrumbs(array(
			$page_title	=> titania_url::$current_page_url,
		));

		titania::page_header($page_title);
		titania::page_footer(true, 'queue_stats_body.html');

	break;

	/**
	* Default (display category/contrib list)
	*/
	default :
		titania::_include('functions_display', 'titania_display_categories');

		// Get the category_id
		$category = phpbb::$request->variable('c', '');
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

		$categories_ary = false;
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

			// Get the child categories we want to select the contributions from
			$child_categories = array_keys(titania::$cache->get_category_children($category_id));

			$type_id = $category_object->category_type;

			// If the category is the top most parent, we'll try to get the type from the first child
			if (!$type_id && sizeof($child_categories))
			{
				$type_id = $categories_ary[$child_categories[0]]['category_type'];
			}
			$type_url = ($type_id && titania_types::$types[$type_id]->use_queue) ? titania_types::$types[$type_id]->url : false;

			phpbb::$template->assign_vars(array(
				'CATEGORY_ID'			=> $category_id,

				'S_DISPLAY_SEARCHBOX'	=> true,
				'S_SEARCHBOX_ACTION'	=> titania_url::build_url('find-contribution'),
				'U_QUEUE_STATS'			=> ($type_url) ? titania_url::build_url('queue-stats/' . $type_url . '/') : '',
			));

			$sort = false;

			// If there are categories we are listing as well, only show 10 by default
			if (sizeof($child_categories))
			{
				// Setup the sort tool to only display the 10 most recent
				$sort = contribs_overlord::build_sort();
				$sort->set_defaults(10);
			}

			// Include the current category in the ones selected
			$child_categories[] = $category_id;

			$data = contribs_overlord::display_contribs('category', $child_categories, $sort);

			// Canonical URL
			$data['sort']->set_url($category_object->get_url());
			phpbb::$template->assign_var('U_CANONICAL', $data['sort']->build_canonical());
		}
		else
		{
			// Mark all contribs read
			if (phpbb::$request->variable('mark', '') == 'contribs')
			{
				titania_tracking::track(TITANIA_CONTRIB, 0);
			}

			phpbb::$template->assign_vars(array(
				'CATEGORY_ID'			=> 0,

				'U_MARK_FORUMS'			=> titania_url::append_url(titania_url::$current_page_url, array('mark' => 'contribs')),
				'L_MARK_FORUMS_READ'	=> phpbb::$user->lang['MARK_CONTRIBS_READ'],

				'S_DISPLAY_SEARCHBOX'	=> true,
				'S_SEARCHBOX_ACTION'	=> titania_url::build_url('find-contribution'),
			));

			// Setup the sort tool to only display the 10 most recent
			$sort = contribs_overlord::build_sort();
			$sort->set_defaults(10);

			$data = contribs_overlord::display_contribs('all', 0, $sort);

			// Canonical URL
			$data['sort']->set_url('');
			phpbb::$template->assign_var('U_CANONICAL', $data['sort']->build_canonical());
		}

		phpbb::$template->assign_vars(array(
			'U_CREATE_CONTRIBUTION'		=> (phpbb::$auth->acl_get('u_titania_contrib_submit')) ? titania_url::build_url('author/' . htmlspecialchars_decode(phpbb::$user->data['username_clean']) . '/create') : '',
			'S_HAS_CONTRIBS'			=> ($categories_ary && $categories_ary[$category_id]['category_type']) ? true : false,
		));

		if ($category_id != 0)
		{
			$category_name = (isset(phpbb::$user->lang[$category_object->category_name])) ? phpbb::$user->lang[$category_object->category_name] : $category_object->category_name;
			titania::page_header($category_name . ' - ' . phpbb::$user->lang['CUSTOMISATION_DATABASE']);
			titania::page_footer(true, 'index_body.html');
		}
	break;
}

titania::page_header('CUSTOMISATION_DATABASE');
titania::page_footer(true, 'index_body.html');
