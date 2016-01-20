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

namespace phpbb\titania\manage\tool\category;

use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\titania\cache\service as cache;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\manage\tool\base;

class fix_tree extends base
{
	/** @var db_driver_interface */
	protected $db;

	/** @var cache */
	protected $cache;

	/** @var string */
	protected $categories_table;

	/** @var bool */
	protected $staggered = false;

	/**
	 * Constructor
	 *
	 * @param db_driver_interface $db
	 * @param cache $cache
	 * @param ext_config $ext_config
	 */
	public function __construct(db_driver_interface $db, cache $cache, ext_config $ext_config)
	{
		$this->db = $db;
		$this->cache = $cache;
		$table_prefix = $ext_config->__get('table_prefix');
		$this->categories_table = $table_prefix . 'categories';
	}

	/**
	 * Run tool.
	 *
	 * @return array
	 */
	public function run()
	{
		$i = 1;
		$changes_made = $this->fix_tree(
			$i,
			'category_id',
			$this->categories_table
		);
		$message = ($changes_made) ? 'LEFT_RIGHT_IDS_FIX_SUCCESS' : 'LEFT_RIGHT_IDS_NO_CHANGE';

		// Purge the cache so the next time a page with modules is viewed it is not getting
		// an old version from the cache
		$this->cache->purge();

		return $this->get_result($message, null, false);
	}

	/**
	 * Fix tree.
	 *
	 * @param int $i
	 * @param string $pkey
	 * @param string $table
	 * @param int $parent_id
	 * @param array $where
	 * @return bool
	 */
	protected function fix_tree(&$i, $pkey, $table, $parent_id = 0, $where = array())
	{
		$changes_made = false;

		$sql = 'SELECT *
			FROM ' . $table . '
			WHERE parent_id = ' . (int) $parent_id .
			((!empty($where)) ? ' AND ' . implode(' AND ', $where) : '') . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// First we update the left_id for this module
			if ($row['left_id'] != $i)
			{
				$this->db->sql_query('
					UPDATE ' . $table . '
					SET ' . $this->db->sql_build_array('UPDATE', array('left_id' => $i)) . "
					WHERE $pkey = {$row[$pkey]}"
				);
				$changes_made = true;
			}
			$i++;

			// Then we go through any children and update their left/right id's
			$changes_made = $this->fix_tree($i, $pkey, $table, $row[$pkey], $where) || $changes_made;

			// Then we come back and update the right_id for this module
			if ($row['right_id'] != $i)
			{
				$this->db->sql_query('
					UPDATE ' . $table . '
					SET ' . $this->db->sql_build_array('UPDATE', array('right_id' => $i)) . "
					WHERE $pkey = {$row[$pkey]}"
				);
				$changes_made = true;
			}
			$i++;
		}
		$this->db->sql_freeresult($result);

		return $changes_made;
	}
}
