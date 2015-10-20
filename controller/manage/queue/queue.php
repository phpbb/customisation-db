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

namespace phpbb\titania\controller\manage\queue;

use \phpbb\titania\contribution\type\collection as type_collection;

class queue extends \phpbb\titania\controller\manage\base
{
	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	protected $type;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\controller\helper $helper
	 * @param type_collection $types
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\subscriptions $subscriptions
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request_interface $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\subscriptions $subscriptions)
	{
		parent::__construct($auth, $config, $db, $template, $user, $cache, $helper, $types, $request, $ext_config, $display);

		$this->subscriptions = $subscriptions;
	}

	/**
	* Display queue.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_queue($queue_type)
	{
		$this->set_type($this->types->type_from_url($queue_type));

		if (!$this->type->acl_get('view'))
		{
			return $this->helper->needs_auth();
		}

		$tag = $this->request->variable('tag', 0);

		// Subscriptions
		if (!$tag)
		{
			$this->subscriptions->handle_subscriptions(
				TITANIA_QUEUE,
				$this->type->id,
				$this->helper->get_current_url(),
				'SUBSCRIBE_QUEUE'
			);
		}
		else
		{
			$this->subscriptions->handle_subscriptions(
				TITANIA_QUEUE_TAG,
				$tag,
				$this->helper->get_current_url(),
				'SUBSCRIBE_CATEGORY'
			);
		}

		\queue_overlord::display_queue($this->type->id, $tag);
		\queue_overlord::display_categories($this->type->id, $tag);

		$this->display->assign_global_vars();
		$this->generate_navigation('queue');

		// Add to Breadcrumbs
		$this->display->generate_breadcrumbs(array(
			$this->type->lang => $this->get_queue_url($this->type->id),
		));

		return $this->helper->render('manage/queue.html', 'VALIDATION_QUEUE');
	}

	/**
	* List all available queues. If user has access to only one queue, he will be
	*	redirected to it.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function list_queues()
	{
		// We need to select the queue if they only have one that they can access, else display the list
		$authed_types = $this->types->find_authed('view');

		if (empty($authed_types))
		{
			return $this->helper->needs_auth();
		}
		else if (sizeof($authed_types) == 1)
		{
			// Redirect the user to the only queue that he has access to.
			redirect($this->get_queue_url($authed_types[0]));
		}

		$counts = $this->get_item_counts($authed_types);

		foreach ($authed_types as $queue_type)
		{
			$this->template->assign_block_vars('categories', array(
				'U_VIEW_CATEGORY'	=> $this->get_queue_url($queue_type),
				'CATEGORY_NAME'		=> $this->types->get($queue_type)->lang,
				'CATEGORY_CONTRIBS' => $counts[$queue_type],
			));
		}

		$this->template->assign_vars(array(
			'S_QUEUE_LIST'	=> true,
		));

		$this->display->assign_global_vars();
		$this->generate_navigation('queue');

		return $this->helper->render('manage/queue.html', 'VALIDATION_QUEUE');
	}

	/**
	* Set queue contribution type object.
	*
	* @param int $type		Contrib type id.
	* @return null
	*/
	protected function set_type($type)
	{
		$this->type = $this->types->get($type);
	}

	/**
	* Get queue item counts.
	*
	* @param array $types	Types to fetch counts for.
	* @return array
	*/
	protected function get_item_counts($types)
	{
		$counts = array_fill_keys($types, 0);

		$sql = 'SELECT queue_type, COUNT(queue_id) AS cnt
			FROM ' . TITANIA_QUEUE_TABLE . '
			WHERE queue_status > 0
				AND ' . $this->db->sql_in_set('queue_type', $types) . '
			GROUP BY queue_type';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[$row['queue_type']] = (int) $row['cnt'];
		}
		$this->db->sql_freeresult($result);

		return $counts;
	}

	/**
	* Get URL for a queue type.
	*
	* @param int $type		Contrib type id.
	* @return string
	*/
	protected function get_queue_url($type)
	{
		return $this->helper->route('phpbb.titania.queue.type', array(
			'queue_type' => $this->types->get($type)->url,
		));
	}
}
