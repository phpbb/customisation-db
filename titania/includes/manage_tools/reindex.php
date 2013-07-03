<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

class reindex
{
	function display_options()
	{
		return 'REINDEX';
	}

	function run_tool()
	{
		$section = request_var('section', 0);
		$start = request_var('start', 0);
		$limit = (titania::$config->search_backend == 'solr') ? 250 : 100;
		$total = 0;

		$sync = new titania_sync;

		switch ($section)
		{
			case 0 :
				titania_search::truncate();

				$display_message = 'TRUNCATING_SEARCH';
			break;

			case 1 :
				$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE;
				phpbb::$db->sql_query($sql);
				$total = phpbb::$db->sql_fetchfield('cnt');
				phpbb::$db->sql_freeresult();

				$sync->contribs('index', false, $start, $limit);

				$display_message = 'INDEXING_CONTRIBS';
			break;

			case 2 :
				$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . TITANIA_POSTS_TABLE;
				phpbb::$db->sql_query($sql);
				$total = phpbb::$db->sql_fetchfield('cnt');
				phpbb::$db->sql_freeresult();

				$sync->posts('index', $start, $limit);

				$display_message = 'INDEXING_POSTS';
			break;

			case 3 :
				$sql = 'SELECT COUNT(faq_id) AS cnt FROM ' . TITANIA_CONTRIB_FAQ_TABLE;
				phpbb::$db->sql_query($sql);
				$total = phpbb::$db->sql_fetchfield('cnt');
				phpbb::$db->sql_freeresult();

				$sync->faqs('index', $start, $limit);

				$display_message = 'INDEXING_FAQ';
			break;

			case 4 :
				trigger_back('DONE');
			break;
		}

		if (($start + $limit) >= $total)
		{
			// Move to the next step
			meta_refresh(0, titania_url::build_url('manage/administration', array('t' => 'reindex', 'section' => ($section + 1), 'submit' => 1, 'hash' => generate_link_hash('manage'))));
		}
		else
		{
			// Move to the next step
			meta_refresh(0, titania_url::build_url('manage/administration', array('t' => 'reindex', 'section' => $section, 'start' => ($start + $limit), 'submit' => 1, 'hash' => generate_link_hash('manage'))));
		}

		$display_message = phpbb::$user->lang[$display_message];
		$section_status = (($start + $limit) < $total) ? sprintf(phpbb::$user->lang['SECTION_STATUS'], ($start + $limit), $total) : phpbb::$user->lang['DONE'];

		trigger_error(sprintf(phpbb::$user->lang['REINDEX_STATUS'], $display_message, $section, $section_status));
	}
}
