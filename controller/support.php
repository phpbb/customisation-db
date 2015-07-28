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

class support
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interace */
	protected $request;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\tracking */
	protected $tracking;

	const ALL_SUPPORT = 0;

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param helper $helper
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\tracking $tracking
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\display $display, \phpbb\titania\tracking $tracking)
	{
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->display = $display;
		$this->tracking = $tracking;
	}

	/**
	* Display support topics from all contributions or of a specific type.
	*
	* @param string $type	Contribution type's string identifier
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_topics($type)
	{
		$type_id = $this->get_type_id($type);

		if ($type_id === false)
		{
			return $this->helper->error('NO_PAGE', 404);
		}

		if ($type == 'all')
		{
			// Mark all topics read
			if ($this->request->variable('mark', '') == 'topics')
			{
				$this->tracking->track(TITANIA_ALL_SUPPORT, self::ALL_SUPPORT);
			}

			// Mark all topics read
			$this->template->assign_var('U_MARK_TOPICS', $this->helper->route('phpbb.titania.support', array('type' => 'all', 'mark' => 'topics')));
		}

		$this->display->assign_global_vars();
		$u_all_support = $this->helper->route('phpbb.titania.support', array('type' => 'all'));

		$this->template->assign_var('U_ALL_SUPPORT', $u_all_support);

		// Generate the main breadcrumbs
		$this->display->generate_breadcrumbs(array(
			'ALL_SUPPORT'	=> $u_all_support,
		));

		// Links to the support topic lists
		foreach (\titania_types::$types as $id => $class)
		{
			$this->template->assign_block_vars('support_types', array(
				'U_SUPPORT'		=> $this->helper->route('phpbb.titania.support', array('type' => $class->url)),

				'TYPE_SUPPORT'	=> $class->langs,
			));
		}

		$data = \topics_overlord::display_forums_complete('all_support', false, array('contrib_type' => $type_id));

		// Canonical URL
		$data['sort']->set_url($this->helper->route('phpbb.titania.support', array('type' => $type)));
		$this->template->assign_var('U_CANONICAL', $data['sort']->build_canonical());

		return $this->helper->render('all_support.html', 'CUSTOMISATION_DATABASE');
	}

	/**
	* Get type id from url string identifier
	*
	* @param string $type Contribution type's string identifier
	* @return int|bool Returns the type's id or false if no type matches.
	*/
	protected function get_type_id($type)
	{
		return ($type == 'all') ? self::ALL_SUPPORT : \titania_types::type_from_url($type);
	}
}
