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

class all_contribs
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\tracking */
	protected $tracking;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	const ALL_CONTRIBS = 0;
	/**
	* Constructor
	*
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\request\request_interface $request
	* @param \phpbb\titania\display $display
	* @param \phpbb\titania\tracking $tracking
	* @param \phpbb\path_helper $path_helper
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request_interface $request, \phpbb\titania\display $display, \phpbb\titania\tracking $tracking, \phpbb\path_helper $path_helper)
	{
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->display = $display;
		$this->tracking = $tracking;
		$this->path_helper = $path_helper;
	}

	/**
	* Display contributions from all contribution types.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function display_all_contributions()
	{
		// Mark all contribs read
		if ($this->request->variable('mark', '') == 'contribs')
		{
			$this->tracking->track(TITANIA_CONTRIB, self::ALL_CONTRIBS);
		}
		$this->template->assign_vars(array(
			'U_MARK_TOPICS'			=> $this->path_helper->append_url_params($this->helper->get_current_url(), array('mark' => 'contribs')),
			'L_MARK_TOPICS_READ'	=> $this->user->lang['MARK_CONTRIBS_READ'],
		));

		$this->list_contributions();
		$this->display->assign_global_vars();

		return $this->helper->render('all_contributions.html', 'CUSTOMISATION_DATABASE');
	}

	/**
	* Load and output contributions.
	*
	* @return null
	*/
	protected function list_contributions()
	{
		$data = \contribs_overlord::display_contribs('all', false);

		// Canonical URL
		$data['sort']->set_url($this->helper->route('phpbb.titania.all_contribs'));
		$this->template->assign_var('U_CANONICAL', $data['sort']->build_canonical());
	}
}
