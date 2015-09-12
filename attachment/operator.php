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

namespace phpbb\titania\attachment;

use phpbb\titania\access;

class operator
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\titania\attachment\attachment */
	protected $entity;

	/** @var string */
	protected $attachments_table;

	/** @var array */
	protected $attachments = array();

	/** @var int */
	protected $object_type;

	/** @var int */
	protected $object_id;

	/** @var array */
	protected $custom_order = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 * @param attachment $attachment
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\template\template $template, attachment $attachment)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->template = $template;
		$this->entity = $attachment;
		$this->attachments_table = TITANIA_ATTACHMENTS_TABLE;
	}

	/**
	 * Configure operator.
	 *
	 * @param int $object_type	Parent object type
	 * @param int $object_id	Parent object id
	 * @return $this
	 */
	public function configure($object_type, $object_id)
	{
		$this->object_type = (int) $object_type;
		$this->object_id = (int) $object_id;

		return $this;
	}

	/**
	 * Get parent object type.
	 *
	 * @return int
	 */
	public function get_object_type()
	{
		return $this->object_type;
	}

	/**
	 * Set parent object id.
	 *
	 * @param int $id
	 * @return $this
	 */
	public function set_object_id($id)
	{
		$this->object_id = (int) $id;
		return $this;
	}

	/**
	 * Get attachment object.
	 *
	 * @param int $id		Attachment id.
	 * @return attachment|null
	 */
	public function get($id)
	{
		return (isset($this->attachments[$id])) ? $this->attachments[$id] : null;
	}

	/**
	 * Append attachment object to set.
	 *
	 * @param attachment $attachment
	 * @return $this
	 */
	public function set(attachment $attachment)
	{
		$this->attachments[$attachment->get_id()] = $attachment;
		return $this;
	}

	/**
	 * Remove attachment object from set.
	 *
	 * @param int $id	Attachment id
	 * @return $this
	 */
	public function remove($id)
	{
		unset($this->attachments[$id]);

		return $this;
	}

	/**
	 * Get count of loaded attachments.
	 *
	 * @return int
	 */
	public function get_count()
	{
		return sizeof($this->attachments);
	}

	/**
	 * Get all attachments.
	 *
	 * @return array
	 */
	public function get_all()
	{
		return $this->attachments;
	}

	/**
	 * Get all attachment id's.
	 *
	 * @return array
	 */
	public function get_all_ids()
	{
		return array_map('intval', array_keys($this->attachments));
	}

	/**
	 * Clear all loaded attachments.
	 *
	 * @return $this
	 */
	public function clear_all()
	{
		$this->attachments = array();
		return $this;
	}

	/**
	 * Delete the given attachments.
	 * This will delete the attachments from the database and remove
	 * the files from the filesystem.
	 *
	 * @param array $ids	Attachment id's
	 * @return $this
	 */
	public function delete(array $ids)
	{
		foreach ($ids as $id)
		{
			$attach = $this->get($id);

			if ($attach)
			{
				$attach->delete();
				$this->remove($id);
			}
		}
		return $this;
	}

	/**
	 * Delete all loaded attachments.
	 * This will delete the attachments from the database and remove
	 * the files from the filesystem.
	 *
	 * @return $this
	 */
	public function delete_all()
	{
		foreach ($this->attachments as $attach)
		{
			$attach->delete();
		}
		$this->clear_all();

		return $this;
	}

	/**
	 * Store multiple attachments.
	 *
	 * @param array $attachments (should be the row directly from the attachments table)
	 * @return $this
	 */
	public function store(array $attachments)
	{
		foreach ($attachments as $row)
		{
			$this->attachments[(int) $row['attachment_id']] = $this->get_new_entity($row);
		}
		return $this;
	}

	/**
	 * Get new attachment entity.
	 *
	 * @param array $data
	 * @return attachment
	 */
	public function get_new_entity(array $data)
	{
		$attachment = clone $this->entity;
		$attachment->__set_array($data);
		return $attachment;
	}

	/**
	 * Load the attachments from the database from the ids and store them in $this->attachments
	 *
	 * @param array|bool $attachment_ids
	 * @param bool $include_orphans			False (default) to not include orphans, true to include orphans
	 * @param int|bool $user_id				User id owner to limit attachments to. Defaults to false.
	 *
	 * @return $this
	 */
	public function load($attachment_ids = false, $include_orphans = false, $user_id = false)
	{
		// Do not load if we do not have an object_id or an empty array of attachment_ids
		if (!$this->object_id && empty($attachment_ids))
		{
			return $this;
		}

		$sql = 'SELECT *
			FROM ' . $this->attachments_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND object_id = ' . (int) $this->object_id .
			(($user_id) ? ' AND attachment_user_id = ' . (int) $user_id : '') .
			(($attachment_ids !== false) ? ' AND ' . $this->db->sql_in_set(
				'attachment_id',
				array_map('intval', $attachment_ids)
			) : '') .
			((!$include_orphans) ? ' AND is_orphan = 0' : '');
		$result = $this->db->sql_query($sql);
		$this->store($this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		return $this;
	}

	/**
	 * Load attachments by id.
	 *
	 * Note that this will not check the object type or id.
	 * 
	 * @param array $ids
	 * @return $this
	 */
	public function load_from_ids(array $ids)
	{
		if (!$ids)
		{
			return $this;
		}
		$sql = 'SELECT *
			FROM ' . $this->attachments_table . '
			WHERE ' . $this->db->sql_in_set('attachment_id', array_map('intval', $ids));
		$result = $this->db->sql_query($sql);
		$this->store($this->db->sql_fetchrowset($result));
		$this->db->sql_freeresult($result);

		return $this;
	}

	/**
	 * Load the attachments from the database from the ids
	 *
	 * @param array $object_ids Array of object_ids to load
	 * @param bool $include_orphans False (default) to not include orphans, true to include orphans
	 *
	 * @return array of attachments in array(object_id => array(attachment rows))
	 */
	public function load_attachments_set($object_ids, $include_orphans = false)
	{
		$attachments_set = array();

		$sql = 'SELECT *
			FROM ' . $this->attachments_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND ' . $this->db->sql_in_set('object_id', array_map('intval', $object_ids)) .
			((!$include_orphans) ? ' AND is_orphan = 0' : '');
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$attachments_set[$row['object_id']][] = $row;
		}
		$this->db->sql_freeresult($result);

		return $attachments_set;
	}

	/**
	 * Submit all attachments.
	 *
	 * @param int $access
	 * @param array $comments
	 */
	public function submit($access = access::PUBLIC_LEVEL, $comments = array())
	{
		if (!$this->get_count())
		{
			return;
		}
		// Update access and is_orphan
		$sql_ary = array(
			'object_id'			=> $this->object_id, // needed when items are attached during initial creation.
			'attachment_access'	=> $access,
			'is_orphan'			=> 0,
		);

		$sql = 'UPDATE ' . $this->attachments_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE ' . $this->db->sql_in_set('attachment_id', $this->get_all_ids());
		$this->db->sql_query($sql);

		foreach ($this->get_all() as $id => $attach)
		{
			$attach->__set_array($sql_ary);
			$update = array();

			if (isset($comments[$id]) && $this->get('attachment_comment') != $comments[$id])
			{
				$update['attachment_comment'] = $comments[$id];
			}
			if (isset($this->custom_order[$id]) && $attach->get('attachment_order') != $this->custom_order[$id])
			{
				$update['attachment_order'] = (int) $this->custom_order[$id];
			}
			if ($update)
			{
				$attach->submit($update);
			}
		}
	}

	/**
	 * Set the given attachment image as preview.
	 *
	 * @param int $id	Attachment id
	 * @return bool
	 */
	public function set_preview($id)
	{
		$attach = $this->get($id);

		if ($attach)
		{
			return $attach->set_preview();
		}
		return false;
	}

	/**
	 * Get preview attachment
	 *
	 * @return attachment|null
	 */
	public function get_preview()
	{
		// Do not load if we do not have an object_id
		if (!$this->object_id)
		{
			return false;
		}

		// Find attachment with is_preview = 1
		$sql = 'SELECT *
			FROM ' . $this->attachments_table . '
			WHERE object_type = ' . (int) $this->object_type . '
				AND object_id = ' . (int) $this->object_id . '
				AND is_orphan = 0
				AND is_preview = 1';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return ($row) ? $this->get_new_entity($row) : null;
	}

	/**
	 * General attachment parsing
	 * From phpBB (includes/functions_content.php)
	 *
	 * @param string &$message The message
	 * @param string $tpl The template file to use
	 * @param array $preview_comments true if previewing from the posting page
	 * @param string|bool $template_block If not false we will output the parsed attachments to this template block
	 * @param bool $custom_sort Whether attachments have a custom order.
	 *
	 * @return array the parsed attachments
	 */
	public function parse_attachments(&$message, $tpl = 'common/attachment.html', $preview_comments = array(), $template_block = false, $custom_sort = false)
	{
		if (empty($this->attachments))
		{
			return array();
		}

		$this->user->add_lang('viewtopic');

		if ($tpl !== false && !isset($this->template->filename['titania_attachment_tpl']))
		{
			$this->template->set_filenames(array(
				'titania_attachment_tpl'	=> $tpl,
			));
		}
		$this->sort($custom_sort);

		$compiled_attachments = array();
		$total_attachments = 0;

		foreach ($this->attachments as $id => $attach)
		{
			// We need to reset/empty the _file block var, because this function might be called more than once
			$this->template->destroy_block_vars('_file');
			$comment = $attach->get('attachment_comment');

			if (isset($preview_comments[$id]))
			{
				$comment = $preview_comments[$id];
			}
			$vars = $attach->get_display_vars($comment);

			// If a template block is specified, output to that also
			if ($template_block)
			{
				$this->template->assign_block_vars($template_block, $vars);
			}

			if ($attach->is_preview() && $attach->is_type(TITANIA_SCREENSHOT))
			{
				$this->template->assign_block_vars('preview', $vars);
			}

			if ($tpl !== false)
			{
				$this->template->assign_block_vars('_file', $vars);

				$compiled_attachments[] = $this->template->assign_display('titania_attachment_tpl');
			}
			$total_attachments++;
		}

		$tpl_size = sizeof($compiled_attachments);
		$unset_tpl = array();

		// For inline attachments
		if ($message)
		{
			preg_match_all(
				'#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#',
				$message,
				$matches,
				PREG_PATTERN_ORDER
			);

			$replace = array();
			foreach ($matches[0] as $num => $capture)
			{
				// Flip index if we are displaying the reverse way
				$index = ($this->config['display_order']) ? ($tpl_size - ($matches[1][$num] + 1)) : $matches[1][$num];
				$replace['from'][] = $matches[0][$num];

				if (isset($compiled_attachments[$index]))
				{
					$replace['to'][] = $compiled_attachments[$index];
				}
				else
				{
					$replaced['to'][] = $this->user->lang(
						'MISSING_INLINE_ATTACHMENT',
						$matches[2][array_search($index, $matches[1])]
					);
				}

				$unset_tpl[] = $index;
			}

			if (isset($replace['from']))
			{
				$message = str_replace($replace['from'], $replace['to'], $message);
			}

			$unset_tpl = array_unique($unset_tpl);

			// Needed to let not display the inlined attachments at the end of the post again
			foreach ($unset_tpl as $index)
			{
				unset($compiled_attachments[$index]);
			}
		}

		return $compiled_attachments;
	}

	/**
	 * Set custom order array.
	 *
	 * @return $this
	 */
	protected function set_order()
	{
		$this->custom_order = array_flip(array_keys($this->attachments));
		return $this;
	}

	/**
	 * Move the order of an attachment.
	 *
	 * @param int $id			Attachment id
	 * @param string $direction	Direction: up|down
	 * @return $this
	 */
	public function change_order($id, $direction)
	{
		if (isset($this->attachments[$id]))
		{
			$this->sort(true);
			$order = $this->custom_order;
			$current = $order[$id];

			// Can we move from the current position? If so, set the new order for the attachment and its sibling
			if (($direction == 'up' && $current !== 0) || ($direction == 'down' && $current !== ($this->get_count() - 1)))
			{
				$sibling_index = $current + (($direction == 'up') ? -1 : 1);
				$sibling_id = array_search($sibling_index, $order);
				$order[$id] = $sibling_index;
				$order[$sibling_id] = $current;
				$this->sort(true, $order, true);
			}
		}
		return $this;
	}

	/**
	 * Sort the attachment set.
	 *
	 * @param bool $custom_sort		Whether the set uses a custom order.
	 * @param array $order			Optional order to use instead of attachment_order
	 * 		values when $custom_sort is true, in form of array(
	 * 			index => attachment_id
	 * 		)
	 * @param bool $force			Force resort if already sorted
	 * @return $this
	 */
	public function sort($custom_sort = false, array $order = array(), $force = false)
	{
		// Sort correctly
		if ($custom_sort)
		{
			// Do not bother sorting if already sorted.
			if ($this->custom_order && !$force)
			{
				return $this;
			}

			if (empty($order))
			{
				uasort($this->attachments, array($this, 'order_comparison'));
			}
			else
			{
				$sorted = array();
				ksort($order);

				foreach ($order as $id)
				{
					if (isset($this->attachments[$id]))
					{
						$sorted[$id] = $this->get($id);
					}
				}
				$unsorted = array_diff_key($this->attachments, $sorted);
				$this->attachments = $sorted + $unsorted;
			}
			$this->set_order();
		}
		else
		{
			if ($this->config['display_order'])
			{
				// Ascending sort
				ksort($this->attachments);
			}
			else
			{
				// Descending sort
				krsort($this->attachments);
			}
		}
		return $this;
	}

	/**
	 * Get maximum index for a custom ordered set.
	 *
	 * @return int
	 */
	public function get_max_custom_index()
	{
		$max_index = 0;

		if ($this->get_count())
		{
			// Get the max value of the attachment_order field in the array
			$temp = $this->attachments;
			uasort($temp, array($this, 'order_comparison'));
			$last = array_pop($temp);
			$max_index = (int) $last->get('attachment_order');
		}
		return $max_index;
	}

	/**
	 * Compare the order of two attachments.
	 *
	 * @param attachment $attach1
	 * @param attachment $attach2
	 * @return int
	 */
	public function order_comparison($attach1, $attach2)
	{
		if ($attach1->get('attachment_order') == $attach2->get('attachment_order'))
		{
			return 0;
		}
		else
		{
			return ($attach1->get('attachment_order') > $attach2->get('attachment_order')) ? 1 : -1;
		}
	}

	/**
	 * Get fixed indices that do not change based on
	 * how the attachment set is ordered.
	 *
	 * @return array
	 */
	public function get_fixed_indices()
	{
		$attachments = $this->get_all();
		krsort($attachments);

		return array_keys($attachments);
	}
}
