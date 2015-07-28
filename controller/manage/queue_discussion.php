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

namespace phpbb\titania\controller\manage;

class queue_discussion extends base
{
	/** @var \phpbb\titania\tracking */
	protected $tracking;

	const ALL_TYPES = 0;

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
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\tracking $tracking
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\tracking $tracking)
	{
		parent::__construct($auth, $config, $db, $template, $user, $cache, $helper, $request, $ext_config, $display);

		$this->tracking = $tracking;
	}

	/**
	* List available queue discussion types.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function list_types()
	{
		// Get all types that the user has permission to view.
		$types = \titania_types::find_authed('queue_discussion');

		if (empty($types))
		{
			return $this->helper->needs_auth();
		}

		// Redirect to specific type if user only has access to one.
		if (sizeof($types) == 1)
		{
			redirect($this->get_type_url($this->get_type_from_id($types[0])));
		}

		$counts = $this->get_type_topic_counts($types);

		foreach ($types as $id)
		{
			$type = $this->get_type_from_id($id);
			$this->template->assign_block_vars('categories', array(
				'U_VIEW_CATEGORY'	=> $this->get_type_url($type),
				'CATEGORY_NAME'		=> $type->lang,
				'CATEGORY_CONTRIBS' => $counts[$id],
			));
		}

		$this->display->assign_global_vars();
		$this->generate_navigation('queue_discussion');
		$this->template->assign_vars(array(
			'S_QUEUE_LIST'	=> true,
		));

		return $this->helper->render('manage/queue.html', 'QUEUE_DISCUSSION');
	}

	/**
	* Display queue discussion type.
	*
	* @param string $queue_type 	Queue type URL identifier.
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_type($queue_type)
	{
		$type = $this->load_type($queue_type);

		if (!$type->acl_get('queue_discussion'))
		{
			return $this->helper->needs_auth();
		}

		// Mark all topics read
		if ($this->request->variable('mark', '') == 'topics')
		{
			$this->tracking->track(TITANIA_QUEUE_DISCUSSION, self::ALL_TYPES);
		}

		$this->display->assign_global_vars();
		$this->generate_navigation('queue_discussion');

		// Add to Breadcrumbs
		$this->display->generate_breadcrumbs(array(
			$type->lang	=> $this->get_type_url($type),
		));

		\topics_overlord::display_forums_complete('queue_discussion', false, array('topic_category' => $type->id));

		// Mark all topics read
		$this->template->assign_var('U_MARK_TOPICS', $this->get_type_url($type, array('mark' => 'topics')));

		return $this->helper->render('manage/queue_discussion.html', 'QUEUE_DISCUSSION');
	}

	/**
	* Get topic counts for the given queue discussion types.
	*
	* @param array $types		Type id's.
	* @return array Returns array in the form of array(id => count).
	*/
	protected function get_type_topic_counts($types)
	{
		$counts = array_fill_keys($types, 0);

		$sql = 'SELECT topic_category, COUNT(topic_id) AS cnt
			FROM ' . TITANIA_TOPICS_TABLE . '
			WHERE topic_type = ' . TITANIA_QUEUE_DISCUSSION . '
				AND ' . $this->db->sql_in_set('topic_category', $types) . '
			GROUP BY topic_category';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[(int) $row['topic_category']] = (int) $row['cnt'];
		}
		$this->db->sql_freeresult();

		return $counts;
	}

	/**
	* Load type from URL identifier.
	*
	* @param string $type		Queue type URL identifier.
	* @return \titania_type
	*/
	protected function load_type($type)
	{
		$type_id = \titania_types::type_from_url($type);
		return $this->get_type_from_id($type_id);
	}

	/**
	* Get type class from id.
	*
	* @return \titania_type
	*/
	protected function get_type_from_id($id)
	{
		return \titania_types::$types[$id];
	}

	/**
	* Get URL for a type.
	*
	* @param \titania_type $type		Queue type class.
	* @param array $params 				Additional parameters to add to URL.
	*
	* @return string Returns generated URL.
	*/
	protected function get_type_url($type, $params = array())
	{
		$params['queue_type'] = $type->url;

		return $this->helper->route('phpbb.titania.queue_discussion.type', $params);
	}
}
