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
use phpbb\request\request_interface;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\manage\tool\base;
use phpbb\titania\versions;
use phpbb\user;
use Symfony\Component\Console\Helper\ProgressHelper;

class update_release_topics extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var type_collection */
	protected $types;

	/** @var user */
	protected $user;

	/** @var string */
	protected $attachments_table;

	/** @var string */
	protected $contribs_table;

	/** @var string */
	protected $revisions_table;

	/** @var string */
	protected $phpbb_topics_table;

	/** @var string */
	protected $users_table;

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param type_collection $types
	 * @param user $user
	 * @param ext_config $ext_config
	 * @param string $phpbb_topics_table
	 * @param string $users_table
	 */
	public function __construct(db_driver_interface $db, type_collection $types, user $user, ext_config $ext_config, $phpbb_topics_table, $users_table)
	{
		$this->db = $db;
		$this->types = $types;
		$this->user = $user;
		$table_prefix = $ext_config->__get('table_prefix');
		$this->attachments_table = $table_prefix . 'attachments';
		$this->contribs_table = $table_prefix . 'contribs';
		$this->revisions_table = $table_prefix . 'revisions';
		$this->phpbb_topics_table = $phpbb_topics_table;
		$this->users_table = $users_table;
	}

	/**
	 * Get count that we'll be processing.
	 *
	 * @return int
	 */
	public function get_total()
	{
		if ($this->total === null)
		{
			$types = $this->get_applicable_types();

			if (!empty($types))
			{

				$sql = 'SELECT COUNT(contrib_id) AS cnt
					FROM ' . $this->contribs_table . '
					WHERE ' . $this->db->sql_in_set('contrib_status', array(
							TITANIA_CONTRIB_APPROVED,
							TITANIA_CONTRIB_DOWNLOAD_DISABLED,
						)) . '
						AND ' . $this->db->sql_in_set('contrib_type', $types);
				$result = $this->db->sql_query($sql);
				$this->total = (int) $this->db->sql_fetchfield('cnt', $result);
				$this->db->sql_freeresult($result);
			}
			else
			{
				$this->total = 0;
			}
		}

		return $this->total;
	}

	/**
	 * Get batch to process.
	 *
	 * @param array $types
	 * @return array
	 */
	protected function get_batch(array $types)
	{
		// Grab our batch
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_user_id, c.contrib_type, c.contrib_name,
				c.contrib_name_clean, c.contrib_desc, c.contrib_desc_uid, c.contrib_release_topic_id,
				t.topic_first_post_id, u.user_id, u.username, u.username_clean, u.user_colour',

			'FROM'		=> array(
				$this->contribs_table	=> 'c',
				$this->users_table		=> 'u',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->phpbb_topics_table	=> 't'),
					'ON'	=> 't.topic_id = c.contrib_release_topic_id',
				),
			),

			'GROUP_BY'	=> 'c.contrib_id',

			'WHERE'		=> $this->db->sql_in_set('c.contrib_status', array(
					TITANIA_CONTRIB_APPROVED,
					TITANIA_CONTRIB_DOWNLOAD_DISABLED,
				)) . '
				AND u.user_id = c.contrib_user_id
				AND ' . $this->db->sql_in_set('contrib_type', $types),

			'ORDER_BY'	=> 'c.contrib_id DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query_limit($sql, $this->limit, $this->start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Update contrib release topics.
	 *
	 * @param array $row	Contrib data array
	 */
	protected function update_contrib(array $row)
	{
		\users_overlord::$users[$row['user_id']] = $row;

		$contrib = new \titania_contribution;
		$contrib->__set_array($row);
		$contrib->set_type($row['contrib_type']);

		$contrib->author = new \titania_author;
		$contrib->author->__set_array($row);

		// Update the release topic
		$contrib->update_release_topic();
	}

	/**
	 * Get types that use release topics.
	 *
	 * @return array
	 */
	protected function get_applicable_types()
	{
		$types = array();

		foreach ($this->types->get_all() as $id => $class)
		{
			if ($class->forum_robot && $class->forum_database)
			{
				$types[] = $id;
			}
		}
		return $types;
	}


	/**
	 * Run the tool
	 *
	 * @param ProgressHelper|null $progress
	 * @return array
	 */
	public function run($progress = null)
	{
		$types = $this->get_applicable_types();

		if (empty($types))
		{
			return $this->get_result('UPDATE_RELEASE_TOPICS_COMPLETE', null, false);
		}

		$total = $this->get_total();
		$batch = $this->get_batch($types);

		$this->user->add_lang_ext('phpbb/titania', 'contributions');

		foreach ($batch as $row)
		{
			$this->update_contrib($row);

			if ($progress)
			{
				$progress->advance();
			}
		}
		$next_batch = $this->start + $this->limit;

		if ($next_batch >= $total)
		{
			return $this->get_result('UPDATE_RELEASE_TOPICS_COMPLETE', $total, false);
		}

		return $this->get_result(
			$this->user->lang('UPDATE_RELEASE_TOPICS_PROGRESS', $next_batch, $total),
			$total,
			$next_batch
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_route()
	{
		return 'phpbb.titania.manage.contrib.update_release_topics';
	}
}
