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

namespace phpbb\titania\manage\tool\contribution;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\ext;
use phpbb\titania\manage\tool\base;
use phpbb\user;
use Symfony\Component\Console\Helper\ProgressBar;

class resync_count extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var type_collection */
	protected $types;

	/** @var user */
	protected $user;

	/** @var string */
	protected $authors_table;

	/** @var string */
	protected $categories_table;

	/** @var string */
	protected $contrib_coauthors_table;

	/** @var string */
	protected $contribs_table;

	/** @var array */
	protected $valid_statuses = array(
		ext::TITANIA_CONTRIB_APPROVED,
		ext::TITANIA_CONTRIB_DOWNLOAD_DISABLED,
	);

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param type_collection $types
	 * @param user $user
	 * @param ext_config $ext_config
	 */
	public function __construct(db_driver_interface $db, type_collection $types, user $user, ext_config $ext_config)
	{
		$this->db = $db;
		$this->types = $types;
		$this->user = $user;
		$table_prefix = $ext_config->__get('table_prefix');
		$this->authors_table = $table_prefix . 'authors';
		$this->categories_table = $table_prefix . 'categories';
		$this->contrib_coauthors_table = $table_prefix . 'contrib_coauthors';
		$this->contribs_table = $table_prefix . 'contribs';
	}

	/**
	 * Run the tool
	 *
	 * @param int $prev_contrib				Previous contrib that was resynced
	 * @param ProgressBar|null $progress
	 * @return array
	 */
	public function run($prev_contrib = 0, $progress = null)
	{
		$defaults = $this->get_defaults();
		$total = $this->get_total();

		// Reset counts to 0
		if ($this->start == 0)
		{
			$this->reset_counts($defaults);
		}

		$batch = $this->get_batch();

		foreach ($batch as $row)
		{
			if ($prev_contrib != $row['contrib_id'])
			{
				$this->update_contrib($row);
			}

			$type_count = '';

			// Does the type have a field in the authors table for storing the type total?
			if (isset($this->types->get($row['contrib_type'])->author_count))
			{
				$count_name = $this->types->get($row['contrib_type'])->author_count;
				$type_count = ", {$count_name} = {$count_name} +1";
			}

			// Update owner's count
			if ($prev_contrib != $row['contrib_id'])
			{
				$this->increase_author_count($type_count, $row['contrib_user_id']);
			}
			// Update coauthor's count
			if (isset($row['user_id']))
			{
				$this->increase_author_count($type_count, $row['user_id']);
			}
			if ($progress)
			{
				$progress->advance();
			}

			$prev_contrib = $row['contrib_id'];
		}

		$next_batch = $this->start + $this->limit;

		if ($next_batch >= $total)
		{
			$result = $this->get_result('RESYNC_CONTRIB_COUNT_COMPLETE', $total, false);
		}
		else
		{
			$result = $this->get_result(
				$this->user->lang('TOOL_PROGRESS_TOTAL', $next_batch, $total),
				$total,
				$next_batch
			);
		}
		$result['prev_contrib'] = $prev_contrib;

		return $result;
	}

	/**
	 * Get default contrib type field count values.
	 *
	 * @return array
	 */
	protected function get_defaults()
	{
		$defaults = array('author_contribs' => 0);

		foreach ($this->types->get_all() as $id => $class)
		{
			if (isset($class->author_count))
			{
				$defaults[$class->author_count] = 0;
			}
		}
		return $defaults;
	}

	/**
	 * Result category and author counts.
	 *
	 * @param array $defaults
	 * @return $this
	 */
	protected function reset_counts(array $defaults)
	{
		$this->db->sql_query(
			'UPDATE ' . $this->categories_table . '
				SET category_contribs = 0'
		);
		$this->db->sql_query(
			'UPDATE ' . $this->authors_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $defaults)
		);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_total()
	{
		if ($this->total === null)
		{
			$sql_ary = array(
				'SELECT'    => 'COUNT(c.contrib_id) AS cnt',

				'FROM'      => array(
					$this->contribs_table => 'c',
				),

				'LEFT_JOIN' => array(
					array(
						'FROM' => array($this->contrib_coauthors_table => 'ca'),
						'ON'   => 'ca.contrib_id = c.contrib_id',
					),
				),

				'WHERE'     => 'c.contrib_visible = 1
					AND ' . $this->db->sql_in_set('c.contrib_status', $this->valid_statuses),
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_ary);
			$result = $this->db->sql_query($sql);
			$this->total = (int) $this->db->sql_fetchfield('cnt', $result);
			$this->db->sql_freeresult($result);
		}

		return $this->total;
	}

	/**
	 * Get batch of rows to process.
	 *
	 * @return array
	 */
	protected function get_batch()
	{
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_type, c.contrib_status, c.contrib_user_id, ca.user_id',

			'FROM'		=> array(
				$this->contribs_table => 'c',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->contrib_coauthors_table => 'ca'),
					'ON'	=> 'ca.contrib_id = c.contrib_id',
				),
			),

			'WHERE'		=> 'c.contrib_visible = 1
				AND ' .  $this->db->sql_in_set('c.contrib_status', $this->valid_statuses),

			'ORDER_BY'	=> 'c.contrib_id',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Update contrib
	 *
	 * @param array $row	Contrib data
	 */
	protected function update_contrib($row)
	{
		// Update category count
		$contrib = new \titania_contribution;
		$contrib->contrib_id = $row['contrib_id'];
		$contrib->contrib_status = $row['contrib_status'];
		$contrib->set_type($row['contrib_type']);
		$contrib->update_category_count();
	}

	/**
	 * Increase author count.
	 *
	 * @param string $type_count
	 * @param int $user_id
	 */
	protected function increase_author_count($type_count, $user_id)
	{
		$sql = 'UPDATE ' . $this->authors_table . '
			SET author_contribs = author_contribs +1' . $type_count . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.contrib.resync_count';
	}
}
