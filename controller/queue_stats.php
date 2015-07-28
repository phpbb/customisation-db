<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\titania\controller;

use phpbb\titania\date;

class queue_stats
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\queue\stats */
	protected $stats;

	/** @var Object Contribution type */
	protected $type;

	/**
	* Constructor
	*
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\request\request_interface $request
	* @param \phpbb\titania\config\config $ext_config
	* @param \phpbb\titania\display $display
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\queue\stats $stats)
	{
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->ext_config = $ext_config;
		$this->display = $display;
		$this->stats = $stats;
	}

	/**
	* Display queue stats.
	*
	* @param string $contrib_type	Contribution type URL value.
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_stats($contrib_type)
	{
		$this->user->add_lang_ext('phpbb/titania', array('queue_stats', 'contributions'));
		$this->set_type($contrib_type);

		if (!$this->stats_supported())
		{
			return $this->helper->error('NO_QUEUE_STATS');
		}

		$this->stats->set_queue_type($this->type->id);

		if (!$this->generate_stats())
		{
			return $this->helper->error('NO_QUEUE_STATS');
		}
		$this->generate_history();
		$this->display->assign_global_vars();

		$page_title = $this->user->lang['QUEUE_STATS'] . ' - ' . $this->type->langs;

		$this->display->generate_breadcrumbs(array(
			$page_title	=> $this->helper->route('phpbb.titania.queue_stats', array('contrib_type' => $this->type->url)),
		));

		return $this->helper->render('queue_stats_body.html', $page_title);
	}

	/**
	* Set the contribution type to generate stats for.
	*
	* @param string $type	Contribution type URL value.
	* @return null
	*/
	protected function set_type($type)
	{
		$type = \titania_types::type_from_url($type);
		$this->type = ($type) ? \titania_types::$types[$type] : false;
	}

	/**
	* Check whether queue stats are supported.
	*
	* @return bool
	*/
	protected function stats_supported()
	{
		return $this->type && $this->ext_config->use_queue && $this->type->use_queue;
	}

	/**
	* Generate queue stats
	*
	* @return bool Returns true if there have been any revisions validated. False otherwise.
	*/
	protected function generate_stats()
	{
		$total_revs_denied		= $this->stats->get_queue_item_count(TITANIA_QUEUE_DENIED);
		$total_revs_approved	= $this->stats->get_queue_item_count(TITANIA_QUEUE_APPROVED);
		$total_revs_validated	= $total_revs_denied + $total_revs_approved;

		if (!$total_revs_validated)
		{
			return false;
		}

		$current_time = time();
		$year_ago = new \DateTime('midnight tomorrow', $this->user->timezone);
		$year_ago->modify('-1 year')->setTimezone(new \DateTimezone('UTC'));

		$validated_cache_ttl	= round($total_revs_validated / 1000) * 86400;
		$validated_statuses		= array(TITANIA_QUEUE_DENIED, TITANIA_QUEUE_APPROVED);
		$closed_statuses		= array(TITANIA_QUEUE_CLOSED, TITANIA_QUEUE_DENIED, TITANIA_QUEUE_APPROVED, TITANIA_QUEUE_HIDE);

		$oldest_unvalidated_rev	= $this->stats->get_oldest_revision_time(false, $closed_statuses);
		$oldest_validated_rev	= $this->stats->get_oldest_revision_time($validated_statuses);
		$unvalidated_avg_wait	= $this->stats->get_average_wait(0, 0, true, false, $closed_statuses);
		$validated_avg_wait		= $this->stats->get_average_wait($year_ago->getTimestamp(), 0, false, $validated_statuses, false, $validated_cache_ttl);
		$revisions_in_queue		= $this->stats->get_queue_item_count(false, $closed_statuses);

		$this->template->assign_vars(array(
			'DENIED_RATIO'					=> round(($total_revs_denied / $total_revs_validated) * 100),
			'APPROVED_RATIO'				=> round(($total_revs_approved / $total_revs_validated) * 100),
			'AVG_PAST_VALIDATION_TIME'		=> $this->user->lang('AVG_PAST_VALIDATION_TIME', $validated_avg_wait),
			'AVG_CURRENT_QUEUE_WAIT'		=> $this->user->lang('AVG_CURRENT_QUEUE_WAIT', $unvalidated_avg_wait),
			'OLDEST_UNVALIDATED_REV'		=> $this->user->lang('OLDEST_UNVALIDATED_REV', date::format_time_delta(
				$this->user,
				$oldest_unvalidated_rev,
				$current_time
			)),
			'NUM_REVISIONS_IN_QUEUE'		=> $this->user->lang('NUM_REVISIONS_IN_QUEUE', $revisions_in_queue),
			'SINCE_X_VALIDATED_REVS'		=> $this->user->lang(
				'SINCE_X_VALIDATED_REVS',
				$this->user->format_date($oldest_validated_rev, 'd M Y'),
				$total_revs_validated,
				$total_revs_denied,
				$total_revs_approved
			),
			'S_QUEUE_ACTIVE'				=> ($revisions_in_queue),
		));
		return true;
	}

	/**
	* Generate day-by-day history
	*
	* @return null
	*/
	protected function generate_history()
	{
		$history_start = new \DateTime('midnight tomorrow', $this->user->timezone);
		$history_start->modify('-30 days');
		$history_end = new \DateTime();

		$history = $this->stats->get_queue_history($history_start, $history_end);
		$this->stats->assign_history_display($history);
	}
}
