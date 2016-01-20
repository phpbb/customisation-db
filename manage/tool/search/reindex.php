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

namespace phpbb\titania\manage\tool\search;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\manage\tool\base;
use phpbb\titania\search\manager as search_manager;
use phpbb\titania\sync;
use phpbb\user;
use Symfony\Component\Console\Helper\ProgressHelper;

class reindex extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var ext_config */
	protected $ext_config;

	/** @var sync */
	protected $sync;

	/** @var search_manager */
	protected $search_manager;

	/** @var string */
	protected $contrib_faq_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $posts_table;

	/** @var array */
	protected $steps = array(
		'truncate',
		'contribs',
		'posts',
		'faqs'
	);

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param user $user
	 * @param ext_config $ext_config
	 * @param sync $sync
	 * @param search_manager $search_manager
	 */
	public function __construct(db_driver_interface $db, user $user, ext_config $ext_config, sync $sync, search_manager $search_manager)
	{
		$this->db = $db;
		$this->user = $user;
		$this->ext_config = $ext_config;
		$this->sync = $sync;
		$this->search_manager = $search_manager;
		$table_prefix = $this->ext_config->__get('table_prefix');
		$this->contrib_faq_table = $table_prefix . 'contrib_faq';
		$this->contribs_table = $table_prefix . 'contribs';
		$this->posts_table = $table_prefix . 'posts';

		$limit = ($this->ext_config->search_backend == 'solr') ? 250 : 100;
		$this->set_limit($limit);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_total()
	{
		$total = 0;

		switch ($this->get_step())
		{
			case 'contribs' :
				$total = $this->get_contrib_count();
			break;

			case 'posts' :
				$total = $this->get_post_count();
			break;

			case 'faqs' :
				$total = $this->get_faq_count();
			break;
		}
		return $total;
	}

	/**
	 * Get contribution count.
	 *
	 * @return int
	 */
	protected function get_contrib_count()
	{
		$sql = 'SELECT COUNT(contrib_id) AS cnt
			FROM ' . $this->contribs_table;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Get FAQ count.
	 *
	 * @return int
	 */
	protected function get_faq_count()
	{
		$sql = 'SELECT COUNT(faq_id) AS cnt
			FROM ' . $this->contrib_faq_table;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Get post count.
	 *
	 * @return int
	 */
	protected function get_post_count()
	{
		$sql = 'SELECT COUNT(post_id) AS cnt
			FROM ' . $this->posts_table;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('cnt', $result);
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Run tool
	 *
	 * @param ProgressHelper|null $progress
	 * @return array
	 */
	public function run($progress = null)
	{
		$total = $this->get_total();

		switch ($this->get_step())
		{
			case 'truncate' :
				$this->search_manager->truncate();

				$display_message = 'TRUNCATING_SEARCH';
			break;

			case 'contribs' :
				$this->sync->contribs('index', false, $this->start, $this->limit);

				$display_message = 'INDEXING_CONTRIBS';
			break;

			case 'posts' :
				$this->sync->posts('index', $this->start, $this->limit);

				$display_message = 'INDEXING_POSTS';
			break;

			case 'faqs' :
				$this->sync->faqs('index', $this->start, $this->limit);

				$display_message = 'INDEXING_FAQ';
			break;
		}
		$next_batch = $this->start + $this->limit;

		if ($total >= $next_batch)
		{
			if ($progress)
			{
				$progress->advance($this->limit);
			}
			return $this->get_result(
				$display_message,
				$total,
				$next_batch,
				$this->get_step()
			);
		}
		$next_step = $this->get_next_step();
		$next_batch = ($next_step) ? 0 : false;

		return $this->get_result(
			$display_message,
			$total,
			$next_batch,
			$next_step
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.search.reindex';
	}
}
